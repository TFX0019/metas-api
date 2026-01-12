<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'rol' => 'required|string|max:255|unique:roles,rol',
        ]);

        $rol = Rol::create([
            'rol' => $request->rol,
        ]);

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'rol' => $rol,
        ], 201);
    }

    public function asignarRol(Request $request, User $user)
    {
        $request->validate([
            'rol_id' => 'required|exists:roles,id',
        ]);

        if (!$user->roles()->where('idrol', $request->rol_id)->exists()) {
            $user->roles()->attach($request->rol_id);
        }

        return response()->json([
            'message' => 'Rol asignado exitosamente',
            'user' => $user->load('roles'),
        ]);
    }

    public function removerRol(Request $request, User $user)
    {
        $request->validate([
            'rol_id' => 'required|exists:roles,id',
        ]);

        $user->roles()->detach($request->rol_id);

        return response()->json([
            'message' => 'Rol removido exitosamente',
            'user' => $user->load('roles'),
        ]);
    }
}
