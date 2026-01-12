<?php

namespace App\Http\Controllers;

use App\Models\Lectura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LecturaController extends Controller
{
    public function index()
    {
        $lecturas = Lectura::all();
        return response()->json($lecturas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = $request->file('imagen')->store('lecturas', 'public');
        }

        $lectura = Lectura::create([
            'titulo' => $request->titulo,
            'contenido' => $request->contenido,
            'imagen' => $imagenPath,
        ]);

        return response()->json([
            'message' => 'Lectura creada exitosamente',
            'lectura' => $lectura,
        ], 201);
    }

    public function show(Lectura $lectura)
    {
        return response()->json($lectura);
    }

    public function update(Request $request, Lectura $lectura)
    {
        $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'contenido' => 'sometimes|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($lectura->imagen) {
                Storage::disk('public')->delete($lectura->imagen);
            }
            $lectura->imagen = $request->file('imagen')->store('lecturas', 'public');
        }

        $lectura->update($request->only(['titulo', 'contenido']));

        return response()->json([
            'message' => 'Lectura actualizada exitosamente',
            'lectura' => $lectura,
        ]);
    }

    public function destroy(Lectura $lectura)
    {
        if ($lectura->imagen) {
            Storage::disk('public')->delete($lectura->imagen);
        }

        $lectura->delete();

        return response()->json([
            'message' => 'Lectura eliminada exitosamente',
        ]);
    }

    public function marcarLeida(Request $request, Lectura $lectura)
    {
        $user = $request->user();
        
        if (!$user->lecturas()->where('idlectura', $lectura->id)->exists()) {
            $user->lecturas()->attach($lectura->id);
        }

        return response()->json([
            'message' => 'Lectura marcada como leída',
        ]);
    }

    public function misLecturas(Request $request)
    {
        $lecturas = $request->user()->lecturas;
        return response()->json($lecturas);
    }
}
