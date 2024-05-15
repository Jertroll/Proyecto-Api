<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;
    protected $fillable = [
        'idCompra',
        'idUsuario',
        'idCarrito',
        'ListaProduc',
        'estadoCompra',
        'fecha',
        'hora',
        'total',
    ];

    protected $primaryKey = 'idCompra';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'idUsuario');
    }
    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'idCarrito');
    }

}
