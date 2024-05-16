c<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('compra', function (Blueprint $table) {
            $table->BigInteger('idCompra')->primary();
            $table->unsignedBigInteger('idUsuario');
            $table->unsignedBigInteger('idCarrito');
            $table->text('ListaProduc');
            $table->string('estadoCompra');
            $table->date('fecha');
            $table->time('hora');
            $table->integer('total');
            
            // Claves foráneas
            $table->foreign('idUsuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('idCarrito')->references('id')->on('carritos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra');
    }
};
