<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idusuario')->constrained('users')->onDelete('cascade');
            $table->foreignId('idrol')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['idusuario', 'idrol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
    }
};
