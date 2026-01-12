<?php

namespace App\Http\Controllers;

use App\Models\Meta;
use App\Models\Tarea;
use Illuminate\Http\Request;

class TareaController extends Controller
{
    public function index(Meta $meta, Request $request)
    {
        if ($meta->iduser !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $tareas = $meta->tareas;

        return response()->json($tareas);
    }

    public function store(Request $request, Meta $meta)
    {
        if ($meta->iduser !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'tarea' => 'required|string|max:255',
            'tipo' => 'required|in:positivo,negativo',
            'puntaje' => 'required|integer|min:0',
            'estado' => 'sometimes|in:pendiente,cumplido,no cumplido',
        ]);

        $tarea = Tarea::create([
            'idmeta' => $meta->id,
            'tarea' => $request->tarea,
            'tipo' => $request->tipo,
            'puntaje' => $request->puntaje,
            'estado' => $request->estado ?? 'pendiente',
        ]);

        return response()->json([
            'message' => 'Tarea creada exitosamente',
            'tarea' => $tarea,
        ], 201);
    }

    public function show(Request $request, Meta $meta, Tarea $tarea)
    {
        if ($meta->iduser !== $request->user()->id || $tarea->idmeta !== $meta->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($tarea);
    }

    public function update(Request $request, Meta $meta, Tarea $tarea)
    {
        if ($meta->iduser !== $request->user()->id || $tarea->idmeta !== $meta->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'tarea' => 'sometimes|string|max:255',
            'tipo' => 'sometimes|in:a favor,suma puntos,en contra,resta puntos',
            'puntaje' => 'sometimes|integer|min:0',
            'estado' => 'sometimes|in:pendiente,cumplido,no cumplido',
        ]);

        $tarea->update($request->only(['tarea', 'tipo', 'puntaje', 'estado']));

        // Actualizar puntos del usuario si la tarea fue completada
        if ($request->has('estado') && $request->estado === 'cumplido') {
            $this->actualizarPuntosUsuario($meta, $tarea);
        }

        return response()->json([
            'message' => 'Tarea actualizada exitosamente',
            'tarea' => $tarea,
        ]);
    }

    public function destroy(Request $request, Meta $meta, Tarea $tarea)
    {
        if ($meta->iduser !== $request->user()->id || $tarea->idmeta !== $meta->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $tarea->delete();

        return response()->json([
            'message' => 'Tarea eliminada exitosamente',
        ]);
    }

    private function actualizarPuntosUsuario(Meta $meta, Tarea $tarea)
    {
        $user = $meta->user;
        
        if (in_array($tarea->tipo, ['positivo'])) {
            $user->puntos += $tarea->puntaje;
        } elseif (in_array($tarea->tipo, ['negativo'])) {
            $user->puntos -= $tarea->puntaje;
        }

        $user->save();
    }
}
