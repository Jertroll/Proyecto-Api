<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table='_productos';
    protected $fillable=['id,nombre,precio,descripcion,talla,estado,imagen'];
    
    public function carrito(){
        return $this->belongsToMany(Carrito::class, 'producto_carrito', 'producto_id', 'carrito_id')
                    ->withPivot('cantidad'); // Incluir la cantidad de cada producto en el carrito
    }
 
}