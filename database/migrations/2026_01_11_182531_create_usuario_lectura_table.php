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
        Schema::create('usuario_lectura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idusuario')->constrained('users')->onDelete('cascade');
            $table->foreignId('idlectura')->constrained('lecturas')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['idusuario', 'idlectura']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_lectura');
    }
};
