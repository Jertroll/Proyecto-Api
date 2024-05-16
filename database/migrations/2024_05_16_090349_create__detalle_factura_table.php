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
        Schema::create('detalle_facturas', function (Blueprint $table) {
            $table->id('idDetalleFactura')->primary()->autoIncrement();
            $table->unsignedBigInteger('idFactura');
            $table->foreign('idFactura')->references('idFactura')->on('facturas');
            $table->DateTime('fechaHora');
            $table->integer('total');
            $table->decimal('impuesto', 10, 2);
            $table->decimal('totalPagar', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_factura');
    }
};
