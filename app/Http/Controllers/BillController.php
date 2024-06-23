<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\Compra;

class BillController extends Controller
{
    // Obtener todos los registros de factura
    public function index()
    {
        $Bills = Bill::all();
        
        $response = [
            "status" => 200,
            "message" => "Todos los registros de las Bills",
            "data" => $Bills
        ];

        return response()->json($response, 200);
    }

    // Crear una nueva factura
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
            'nomTienda' => 'required|string',
            'fechaEmision' => 'required|date',
            'idCompra' => 'required|integer|exists:compra,idCompra',
        ]);
    
        $compra = Compra::with('detalles')->findOrFail($data['idCompra']);
    
        // Verificar que la compra tenga detalles
        if (is_null($compra->detalles) || $compra->detalles->isEmpty()) {
            return response()->json(['status' => 400, 'message' => 'No se encontraron detalles para esta compra'], 400);
        }
    
        $subTotal = 0;
        foreach ($compra->detalles as $detalle) {
            $subTotal += $detalle->cantidad * $detalle->precioUnitario;
        }

        
        $impuesto = $subTotal * 0.13; 

        $total = $subTotal + $impuesto;
    
        $bill= Bill::create([
            'idUsuario' => $userId,
            'nomTienda' => $data['nomTienda'],
            'fechaEmision' => $data['fechaEmision'],
            'idCompra' => $compra->idCompra,
            'subTotal' => $subTotal,
            'total' => $total
        ]);
    
        return response()->json([
            'status' => 201,
            'message' => 'Factura creada satisfactoriamente',
            'bill' => $bill,
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
    
        try {
            $compra = Compra::with('detalles.producto')->findOrFail($data['idCompra']);
            $subTotal = $this->calcularSubTotal($compra);
            $impuesto = ($subTotal - $descuento) * 0.12;
            $total = $subTotal - $descuento + $impuesto;
            $bill = Bill::create([
                'idCompra' => $compra->idCompra,
                'fechaEmision' => now(),
                'subTotal' => $subTotal,
                'descuento' => $descuento,
                'impuesto' => $impuesto,
                'total' => $total,
            ]);
    
            return response()->json([
                'status' => 201,
                'message' => 'Factura creada satisfactoriamente',
                'bill' => $bill,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Ocurrió un error al procesar la solicitud',
                'error' => $e->getMessage(),
            ], 500);
        }
    }    

    // Calcular el subtotal de la compra
    private function calcularSubTotal($compra)
    {
        $subtotal = 0;

        foreach ($compra->carrito->productos as $producto) {
            $precio = $producto->precio;
            $cantidad = $producto->pivot->cantidad;
            $subtotal += $precio * $cantidad;
        }

        return $subtotal;
    }


}
