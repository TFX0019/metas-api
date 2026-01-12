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
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idmeta')->constrained('metas')->onDelete('cascade');
            $table->string('tarea');
            $table->enum('tipo', ['positivo', 'negativo']);
            $table->integer('puntaje')->default(0);
            $table->enum('estado', ['pendiente', 'cumplido', 'no cumplido'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
