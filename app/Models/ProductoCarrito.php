<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoCarrito extends Model
{
    use HasFactory;
    protected $table='producto_carrito';
    protected $fillable=['id,cantidad,producto_id,carrito_id'];

    public function productos(){
        return $this->belongsToMany(Producto::class, 'producto_carrito',  'carrito_id','producto_id');
    }
    
    public function carrito(){
        return $this->belongsToMany(Carrito::class, 'producto_carrito',  'carrito_id','producto_id')
        ->withPivot('cantidad');
    }
}
