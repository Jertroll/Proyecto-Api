<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $fillable = [
        'idFactura',
        'idUsuario',
        'nomTienda',
        'fechaEmision',
        'metodoPago',
        'totalPagar',
        'idCompra'
    ];

    protected $primaryKey = 'idFactura';

    public $timestamps = false;

    /**
     * Get the user that owns the invoice.
     */
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
