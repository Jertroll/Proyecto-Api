<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Bill;
use App\Models\User;
use App\Models\Compra;
use App\Helpers\JwtAuth;



class BillController extends Controller
{
    public function index()
    {
        $facturas=Bill::with('user','compra')->get();
        $response=array(
            "status"=>200,
            "message"=>"Todos los registro de facturas",
            "data"=>$facturas
        );
        return response()->json($response,200);
    }
    public function show($id)
    {
        try {
            $bill = Bill::with('user', 'compra')->findOrFail($id);
            return response()->json(['status' => 200, 'message' => 'Factura encontrada', 'bill' => $bill], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 404, 'message' => 'Factura no encontrada'], 404);
        }
    }
    

    public function store(Request $request)
    {
        $bearerToken = $request->header('bearertoken');
        if (!$bearerToken) {
            return response()->json(['status' => 400, 'message' => 'Token no proporcionado'], 400);
        }
   
  
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($bearerToken, true);

        if (!$decodedToken || !isset($decodedToken->iss)) {
            return response()->json(['status' => 400, 'message' => 'Token inválido'], 400);
        }
        $userId = $decodedToken->iss;

        $data = $request->validate([
            'nomTienda' => 'required|integer',
            'fechaEmision' => 'required|date',
            'idCompra' => 'required|integer|exists:compra,idCompra',
        ]);

        $compra = Compra::with('detalles')->findOrFail($data['idCompra']);
        $subTotal = 0;
        foreach ($compra->detalles as $detalle) {
            $subTotal += $detalle->cantidad * $detalle->precioUnitario;
        }
        $impuesto = $subTotal * 0.13; // Suponiendo un impuesto del 16%
        $total = $subTotal + $impuesto;
     
        $bill = Bill::create([
            'idUsuario'=>$compra->idUsuario=$userId,
            'nomTienda' =>$request->input('nomTienda'),
            'fechaEmision' => $request->input('fechaEmision'),
            'idCompra'=>$compra->idCompra,
            'subTotal' => $subTotalConDescuento,
            'total' => $total
        ]);
        return response()->json([
            'status' => 201,
            'message' => 'Factura creada satisfactoriamente',
            'factura' => $factura,
        ], 201);

  }

    public function destroy($id)
{
    try {
        $bill = Bill::find($id);
        if (!$bill) {
            return response()->json(['status' => 404, 'message' => 'Factura no encontrada'], 404);
        }

        $bill->delete();

        return response()->json(['status' => 200, 'message' => 'Factura eliminada con éxito'], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 500, 'message' => 'Error al eliminar la factura: ' . $e->getMessage()], 500);
    }
}

    public function update(Request $request, $id)
    {
        $data = $request->input('data', null);
    
        if (!$data) {
            return response()->json(['status' => 400, 'message' => 'No se encontró el objeto data'], 400);
        }
    
        $data = json_decode($data, true);
        $validator = \Validator::make($data, [
            'nomTienda' => 'required',
            'metodoPago' => 'required',
            
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => 406, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 406);
        }
    
        try {
            $bill = Bill::find($id);
            if (!$bill) {
                return response()->json(['status' => 404, 'message' => 'Factura no encontrada'], 404);
            }
    
            $bill->fill($data);
            $bill->save();
    
            return response()->json(['status' => 200, 'message' => 'Factura actualizada', 'bill' => $bill], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Error al actualizar la factura: ' . $e->getMessage()], 500);
        }
     }
     public function calcularTotalPagar($idFactura, $impuesto)
  {
    $factura = Bill::findOrFail($idFactura);

    // Verificar si la factura existe y si su total es válido
    if (!isset($factura->total)) {
        throw new \Exception("El total de la factura no es válido. ID de factura: " . $idFactura);
    }

    // Calcular el total a pagar sumando el total de la factura y el impuesto
    $totalPagar = $factura->total + $impuesto;

    return $totalPagar;
  }

}
