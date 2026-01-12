<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meta extends Model
{
    use HasFactory;

    protected $fillable = [
        'iduser',
        'meta',
        'puntaje',
        'fecha_inicio',
        'fecha_vence',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vence' => 'date',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'iduser');
    }

    public function tareas(): HasMany {
        return $this->hasMany(Tarea::class, 'idmeta');
    }
}
