<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;
    protected $table='carritos';
    protected $fillable=['idCarrito,_productos,cantidad,user_id'];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function productos(){
        return $this->belongsToMany(Producto::class, 'producto_carrito', 'carrito_id', 'producto_id')
                    ->withPivot('cantidad'); // Incluir la cantidad de cada producto en el carrito
    }
    public function compra()
    {
        return $this->hasMany(Compra::class, 'idCarrito');
    }
}
