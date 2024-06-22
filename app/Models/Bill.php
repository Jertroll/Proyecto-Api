<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $table='bills';
    protected $fillable = [
        'id',
        'idUsuario',
        'nomTienda',
        'fechaEmision',
        'idCompra',
        'subTotal',       
        'total'
    ];

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'idUsuario');
    }
    public function Compra()
    {
        return $this->belongsTo(Compra::class, 'idCompra');
    }
    public function detalleFac()
    {
        return $this->hasMany(DetalleFactura::class, 'idDetalleFactura');
}
}
