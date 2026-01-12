<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarea extends Model
{
    use HasFactory;

    protected $fillable = [
        'idmeta',
        'tarea',
        'tipo',
        'puntaje',
        'estado',
    ];

    protected $casts = [
        'puntaje' => 'integer',
    ];

    public function meta(): BelongsTo {
        return $this->belongsTo(Meta::class, 'idmeta');
    }
}
