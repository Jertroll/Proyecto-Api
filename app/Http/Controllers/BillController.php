<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\Compra;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Log;

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

    // Obtener una Bill por su ID
    public function show($idBill)
    {
        $Bill = Bill::find($idBill);

        if ($Bill) {
            return response()->json([
                'status' => 200,
                'message' => 'Datos de la Bill',
                'Bill' => $Bill
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Bill no encontrada'
            ], 404);
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



//Listar Factura con detalle
public function getUserBills(Request $request)
{
    try {
        // Verificar la presencia del token en los encabezados de la solicitud
        \Log::info('Encabezados recibidos:', $request->headers->all());
        \Log::info('Cuerpo de la solicitud:', $request->all());
        $bearerToken = $request->header('bearertoken');
        \Log::info('Datos recibidos para crear compra:', $request->all());
        if (!$bearerToken) {
            return response()->json([
                'status' => 401,
                'message' => 'Token no proporcionado.',
            ], 401);
        }

        // Validar el token JWT
        $jwt = new JwtAuth();
        $decodedToken = $jwt->checkToken($bearerToken, true);
        if (!$decodedToken || !isset($decodedToken->iss)) {
            return response()->json([
                'status' => 400,
                'message' => 'Token inválido.',
            ], 400);
        }

        // Obtener el ID del usuario desde el token decodificado
        $userId = $decodedToken->iss;

        // Obtener las facturas del usuario, incluyendo la compra y sus detalles
        $bills = Bill::where('idUsuario', $userId)
                      ->with(['compra.detalles']) // Cargar las compras y sus detalles
                      ->get();

        // Retornar las facturas con sus detalles en formato JSON
        return response()->json([
            'status' => 200,
            'message' => 'Facturas obtenidas con éxito.',
            'data' => $bills,
        ], 200);
    } catch (\Exception $e) {
        // Manejar cualquier excepción y retornar un mensaje de error
        return response()->json([
            'status' => 500,
            'message' => 'Error al obtener las facturas del usuario.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}


