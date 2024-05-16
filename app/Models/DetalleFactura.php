<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Bill;

class DetalleFactura extends Model
{
    use HasFactory;
    protected $fillable = [
        'idDetalleFactura',
        'idFactura',
        'fecha',
        'totalHora',
        'impuesto',
        'totalPagar',
    ];

    protected $primaryKey = 'idDetalleFactura';
    public $timestamps = false;

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'idFactura');
    }

}
