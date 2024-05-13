<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table='_producto';
    protected $fillable=['codigo,nombre,precio,descripcion,talla,estado,imagen'];

}
