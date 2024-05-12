<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $fillable = [
        'idFactura',
        'nombreVendedor',
        'idUsuario',
        'nomTienda',
        'dirTienda',
        'idContacto',
        'idRes',
        'fechaEmision',
        'metodoPago',
        'terYcon',
        'costEnvio',
        'totalPagar',
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
}
