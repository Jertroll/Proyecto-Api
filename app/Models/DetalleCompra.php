<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    use HasFactory;
    protected $table='detalleCompra';

    protected $fillable = [
        'idCompra',
        'idProducto',
        'cantidad',
        'precioUnitario',
        'subTotal',
    ];

    protected $primaryKey = 'idCompra';

    public $timestamps = false;

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'idCompra');
    }
    public function producto()
    {
        return $this->hasOne(Tour::class,'idProducto');
    }

}