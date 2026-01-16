<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // public function show(Request $request)
    // {
    //     $user = $request->user()->load(['roles', 'metas']);
        
    //     // Agregar URL completa del avatar si existe
    //     if ($user->avatar) {
    //         $user->avatar_url = asset('storage/' . $user->avatar);
    //     } else {
    //         $user->avatar_url = null;
    //     }

    //     return response()->json([
    //         'ok' => true,
    //         'message' => 'Perfil obtenido exitosamente',
    //         'error' => null,
    //         'data' => $user,
    //     ]);
    // }

    public function updateName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->save();

        return response()->json([
            'ok' => true,
            'message' => 'Nombre actualizado exitosamente',
            'error' => null,
            'data' => $user,
        ]);
    }

    
    public function updateEmail(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'required|string', // Requerir contraseña actual por seguridad
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'ok' => false,
                'message' => 'Contraseña incorrecta',
                'error' => 'La contraseña proporcionada no es correcta',
                'data' => null,
            ], 401);
        }

        $user->email = $request->email;
        $user->save();

        return response()->json([
            'ok' => true,
            'message' => 'Email actualizado exitosamente',
            'error' => null,
            'data' => $user,
        ]);
    }


    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'ok' => false,
                'message' => 'Contraseña actual incorrecta',
                'error' => 'La contraseña actual proporcionada no es correcta',
                'data' => null,
            ], 401);
        }


        $user->password = Hash::make($request->new_password);
        $user->save();

        //* aqui se puede revocar los tokens para eliminar la sessiones
        // $user->tokens()->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Contraseña actualizada exitosamente',
            'error' => null,
            'data' => null,
        ]);
    }


    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Guardar nuevo avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $avatarPath;
        $user->save();

        $user->avatar_url = asset('storage/' . $avatarPath);

        return response()->json([
            'ok' => true,
            'message' => 'Avatar actualizado exitosamente',
            'error' => null,
            'data' => [
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }


    public function deleteAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Avatar eliminado exitosamente',
            'error' => null,
            'data' => null,
        ]);
    }


    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Actualizar nombre si se proporciona
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        // Actualizar email si se proporciona
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // Actualizar avatar si se proporciona
        if ($request->hasFile('avatar')) {
            // Eliminar avatar anterior si existe
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        // Agregar URL completa del avatar
        if ($user->avatar) {
            $user->avatar_url = asset('storage/' . $user->avatar);
        } else {
            $user->avatar_url = null;
        }

        return response()->json([
            'ok' => true,
            'message' => 'Perfil actualizado exitosamente',
            'error' => null,
            'data' => $user,
        ]);
    }
}
