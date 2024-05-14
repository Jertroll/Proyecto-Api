<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoCarrito extends Model
{
    use HasFactory;
    protected $table = 'producto_carrito';
    
    protected $fillable = ['carrito_id', 'producto_id']; 
    
    // Si necesitas acceder a los modelos relacionados, puedes definir las relaciones aquí
    
    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }
    
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
