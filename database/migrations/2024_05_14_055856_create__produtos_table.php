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
        Schema::create('_productos', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('nombre');
            $table->integer('precio');
            $table->text('descripcion')->nullable(); 
            $table->string('talla')->nullable(); 
            $table->enum('estado', ['disponible', 'no disponible'])->default('disponible'); 
            $table->string('imagen')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_productos');
    }
};
