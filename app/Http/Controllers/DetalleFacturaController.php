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

    public function store(Request $request)
    {
        
        $data = $request->input('data', null);

        if (!$data) {
            return response()->json(['status' => 400, 'message' => 'No se encontró el objeto data'], 400);
        }

        $validator = \Validator::make($data, [
            'idFactura' => 'required',
            'impuesto' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 406, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 406);
        }

        try {
            $detalleFactura = new DetalleFactura();
            $detalleFactura->fill($data);
        
            $billController = new BillController();
            // Calcular el total a pagar
            $totalPagar = $billController->calcularTotalPagar($data['idFactura'], $data['impuesto']);
        
            // Asignar el total calculado al campo totalPagar
            $detalleFactura->totalPagar = $totalPagar;
        
            $detalleFactura->save();
  
            return response()->json(['status' => 201, 'message' => 'Detalle de factura creado', 'detalleFactura' => $detalleFactura], 201);
        } catch (\Exception $e) {
            
            return response()->json(['status' => 500, 'message' => 'Error al crear el detalle de factura: ' . $e->getMessage()], 500);
        }
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

public function destroy($id)
{
    try {
        $detalle = DetalleFactura::find($id);
        if (!$detalle) {
            return response()->json(['status' => 404, 'message' => 'Detalle Factura no encontrada'], 404);
        }

        $detalle->delete();

        return response()->json(['status' => 200, 'message' => 'Detalle Factura eliminada con éxito'], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 500, 'message' => 'Error al eliminar la Detalle Factura: ' . $e->getMessage()], 500);
    }
}

}
