<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;
    protected $table='carritos';
    protected $fillable=['idCarrito,user_id'];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function productos(){
        return $this->belongsToMany(Producto::class, 'producto_carrito',  'carrito_id','producto_id');
    }
}
