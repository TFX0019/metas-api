<?php

namespace App\Http\Controllers;

use App\Models\Meta;
use Illuminate\Http\Request;

class MetaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $metas = Meta::where('iduser', $request->user()->id)
            ->with('tareas')
            ->paginate($perPage);

        return response()->json([
            'ok' => true,
            'message' => 'Metas obtenidas exitosamente',
            'error' => null,
            'data' => $metas,
        ]);;
    }

    public function store(Request $request)
    {
        $request->validate([
            'meta' => 'required|string|max:255',
            'puntaje' => 'required|integer|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_vence' => 'required|date|after:fecha_inicio',
        ]);

        $meta = Meta::create([
            'iduser' => $request->user()->id,
            'meta' => $request->meta,
            'puntaje' => $request->puntaje,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_vence' => $request->fecha_vence,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Meta creada exitosamente',
            'error' => null,
            'data' => $meta->load('tareas'),
        ], 201);
    }

    public function show(Request $request, Meta $meta)
    {
        if ($meta->iduser !== $request->user()->id) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado',
                'error' => 'No tienes permiso para ver esta meta',
                'data' => null,
            ], 403);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Meta obtenida exitosamente',
            'error' => null,
            'data' => $meta->load('tareas'),
        ]);
    }

    public function update(Request $request, Meta $meta)
    {
        if ($meta->iduser !== $request->user()->id) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado',
                'error' => 'No tienes permiso para actualizar esta meta',
                'data' => null,
            ], 403);
        }

        $request->validate([
            'meta' => 'sometimes|string|max:255',
            'puntaje' => 'sometimes|integer|min:0',
            'fecha_inicio' => 'sometimes|date',
            'fecha_vence' => 'sometimes|date|after:fecha_inicio',
        ]);

        $meta->update($request->only(['meta', 'puntaje', 'fecha_inicio', 'fecha_vence']));

        return response()->json([
            'ok' => true,
            'message' => 'Meta actualizada exitosamente',
            'error' => null,
            'data' => $meta->load('tareas'),
        ]);
    }

    public function destroy(Request $request, Meta $meta)
    {
        if ($meta->iduser !== $request->user()->id) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado',
                'error' => 'No tienes permiso para eliminar esta meta',
                'data' => null,
            ], 403);
        }

        $meta->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Meta eliminada exitosamente',
            'error' => null,
            'data' => null,
        ]);
    }
}
