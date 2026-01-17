<?php

namespace App\Http\Controllers;

use App\Models\Meta;
use App\Models\Tarea;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TareaController extends Controller
{
    public function index(Meta $meta, Request $request)
    {
        if ($meta->iduser !== $request->user()->id) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado',
                'error' => 'No tienes permiso para ver estas tareas',
                'data' => null,
            ], 403);
        }

        $tareas = $meta->tareas;
        return response()->json([
            'ok' => true,
            'message' => 'Tareas obtenidas exitosamente',
            'error' => null,
            'data' => $tareas,
        ]);
    }

    public function allTareas(Request $request) {
        $perPage = $request->input('per_page', 15);

        $tareas = Tarea::whereHas('meta', function ($query) use ($request) {
            $query->where('iduser', $request->user()->id);
        })
        ->with('meta:id,meta,puntaje')
        ->paginate($perPage);

        return response()->json([
            'ok' => true,
            'message' => 'Todas las tareas obtenidas exitosamente',
            'error' => null,
            'data' => $tareas,
        ]);
    }

    public function store(Request $request, Meta $meta)
    {
        if ($meta->iduser !== $request->user()->id) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado',
                'error' => 'No tienes permiso para crear tareas en esta meta',
                'data' => null,
            ], 403);
        }

        if ($this->metaEstaVencida($meta)) {
            return response()->json([
                'ok' => false,
                'message' => 'Meta vencida',
                'error' => 'No se pueden crear tareas en una meta que ya ha vencido',
                'data' => null,
            ], 422);
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
            'ok' => true,
            'message' => 'Tarea creada exitosamente',
            'error' => null,
            'data' => $tarea,
        ], 201);
    }

    public function show(Request $request, Meta $meta, Tarea $tarea)
    {
        if ($meta->iduser !== $request->user()->id || $tarea->idmeta !== $meta->id) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado',
                'error' => 'No tienes permiso para ver esta tarea',
                'data' => null,
            ], 403);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Tarea obtenida exitosamente',
            'error' => null,
            'data' => $tarea,
        ]);
    }

    public function update(Request $request, Meta $meta, Tarea $tarea)
    {
        if ($meta->iduser !== $request->user()->id || $tarea->idmeta !== $meta->id) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado',
                'error' => 'No tienes permiso para actualizar esta tarea',
                'data' => null,
            ], 403);
        }

        if ($this->metaEstaVencida($meta)) {
            return response()->json([
                'ok' => false,
                'message' => 'Meta vencida',
                'error' => 'No se pueden modificar tareas de una meta que ya ha vencido',
                'data' => null,
            ], 422);
        }

        $request->validate([
            'tarea' => 'sometimes|string|max:255',
            'tipo' => 'sometimes|in:positivo,negativo',
            'puntaje' => 'sometimes|integer|min:0',
            'estado' => 'sometimes|in:pendiente,cumplido,no cumplido',
        ]);

        $tarea->update($request->only(['tarea', 'tipo', 'puntaje', 'estado']));

        // Actualizar puntos del usuario si la tarea fue completada
        if ($request->has('estado') && $request->estado === 'cumplido') {
            $this->actualizarPuntosUsuario($meta, $tarea);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Tarea actualizada exitosamente',
            'error' => null,
            'data' => $tarea,
        ]);
    }

    public function destroy(Request $request, Meta $meta, Tarea $tarea)
    {
        if ($meta->iduser !== $request->user()->id || $tarea->idmeta !== $meta->id) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado',
                'error' => 'No tienes permiso para eliminar esta tarea',
                'data' => null,
            ], 403);
        }

        if ($this->metaEstaVencida($meta)) {
            return response()->json([
                'ok' => false,
                'message' => 'Meta vencida',
                'error' => 'No se pueden eliminar tareas de una meta que ya ha vencido',
                'data' => null,
            ], 422);
        }

        $tarea->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Tarea eliminada exitosamente',
            'error' => null,
            'data' => null,
        ]);
    }

    // validar si la meta esta vencida
    private function metaEstaVencida(Meta $meta): bool {
        return Carbon::parse($meta->fecha_vence)->isPast();
    }

    // actualizar puntos del usuario si la tarea fue completada
    private function actualizarPuntosUsuario(Meta $meta, Tarea $tarea) {
        $user = $meta->user;
        
        if (in_array($tarea->tipo, ['positivo'])) {
            $user->puntos += $tarea->puntaje;
        } elseif (in_array($tarea->tipo, ['negativo'])) {
            $user->puntos -= $tarea->puntaje;
        }

        $user->save();
    }
}
