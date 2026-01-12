<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lectura extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'contenido',
        'imagen'
    ];

    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class, 'usuario_lectura', 'idlectura', 'idusuario')
        ->withTimestamps();
    }
}
