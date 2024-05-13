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
        'idDetalleFactura'
    ];

    protected $primaryKey = 'idFactura';

    public $timestamps = false;

    /**
     * Get the user that owns the invoice.
     */
    public function User(){
        return $this->hasMany('App\Models\User');
    }
}
