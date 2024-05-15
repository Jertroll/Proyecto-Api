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
        Schema::create('producto_carrito', function (Blueprint $table) {

            $table->id(); 
            $table->integer('cantidad');
            
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('carrito_id');

            $table->foreign('producto_id')->references('id')->on('_productos');
            $table->foreign('carrito_id')->references('id')->on('carritos');

            $table->timestamps(); 
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
