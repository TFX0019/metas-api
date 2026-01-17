<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected $appends = ['esta_vencida'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'iduser');
    }

    public function tareas(): HasMany {
        return $this->hasMany(Tarea::class, 'idmeta');
    }

    protected function estaVencida(): Attribute {
        return Attribute::make(
            get: fn () => Carbon::parse($this->fecha_vence)->isPast(),
        );
    }
}
