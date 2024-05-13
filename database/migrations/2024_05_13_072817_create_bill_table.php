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
        Schema::create('bills', function (Blueprint $table) {
            $table->string('idFactura')->primary();
            $table->integer('idUsuario');
            $table->string('nomTienda');
            $table->date('fechaEmision');
            $table->string('metodoPago');
            $table->decimal('totalPagar', 10, 2);
            $table->integer('idDetalleFactura');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill');
    }
};
