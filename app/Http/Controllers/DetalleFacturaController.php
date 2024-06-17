<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetalleFactura;
use App\Models\Bill;
use Carbon\Carbon;

class DetalleFacturaController extends Controller
{
    public function index()
    {
        $detalle=DetalleFactura::with('bill')->get();
        $response=array(
            "status"=>200,
            "message"=>"Todos los registro de Los detalles de facturas",
            "data"=>$detalle
        );
        return response()->json($response,200);
   
    }
    
    public function show($id)
    {
        $detalle = DetalleFactura::find($id);
        
        if (!$detalle) {
            return response()->json(['status' => 404, 'message' => 'Detalle de factura no encontrado'], 404);
        }
        
        return response()->json(['status' => 200, 'message' => 'Detalle de factura obtenido con éxito', 'data' => $detalle], 200);
    }

   
    
    public function update(Request $request, $id)
{
    $data = $request->only(['impuesto']);

    // Verificar si el detalle de factura existe
    $detalleFactura = DetalleFactura::find($id);
    if (!$detalleFactura) {
        return response()->json(['status' => 404, 'message' => 'Detalle de factura no encontrado'], 404);
    }

    $detalleFactura->update($data);
    return response()->json(['status' => 200, 'message' => 'Detalle de factura actualizado', 'detalleFactura' => $detalleFactura], 200);
}

}
