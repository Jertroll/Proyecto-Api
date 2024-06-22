<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;
    protected $table='compra';

    protected $fillable = [
        'idCompra',
        'idUsuario',
        'idCarrito',
        'estadoCompra',
        'fecha',
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
    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'idCompra');
    }
    public function bill()
    {
        return $this->hasOne(Bill::class, 'idCompra');
    }

}
