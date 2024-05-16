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
        $facturas = Bill::all();
        return response()->json($facturas);
    }

    public function store(Request $request)
{
    $data = $request->input('data', null);
    
        if (!$data) {
            return response()->json(['status' => 400, 'message' => 'No se encontró el objeto data'], 400);
        }
    
        $data = json_decode($data, true);
        $validator = \Validator::make($data, [
            'idFactura' => 'required',
            'idUsuario' => 'required',
            'nomTienda' => 'required',
            'fechaEmision' => 'required',
            'metodoPago' => 'required',
            'totalPagar' => 'required',
            'idCompra' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => 406, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 406);
        }
    
        try {
            $bill = new Bill();
            $bill->fill($data);
        
            // Aquí obtienes el usuario asociado y lo guardas en el campo idUsuario
            $user = User::findOrFail($data['idUsuario']);
            $bill->user()->associate($user);
            $compra = Compra::findOrFail($data['idCompra']);
            $bill->compra()->associate($compra);
        
            $bill->save();
        
            return response()->json(['status' => 201, 'message' => 'Factura creada', 'bill' => $bill], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Error al crear la factura: ' . $e->getMessage()], 500);
        }

    } 
    
    $data = json_decode($data, true);
    $validator = \Validator::make($data, [
        'idFactura' => 'required',
        'idUsuario' => 'required',
        'nomTienda' => 'required',
        'fechaEmision' => 'required',
        'metodoPago' => 'required',
        'totalPagar' => '',
        'idCompra' => 'required',
    ]);
    
    if ($validator->fails()) {
        return response()->json(['status' => 406, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 406);
    }
    
    try {
        $bill = new Bill();
        $bill->fill($data);
      
        
        // Obtener la compra correspondiente al idCompra
        $compra = Compra::findOrFail($data['idCompra']);
       
        // Crear una instancia del controlador de compras para acceder al método calcularTotal()
        $compraController = new CompraController();
        
        // Calcular el total de la compra
        $total = $compraController->calcularTotal($data['idCompra']);

        // Asignar el total a la factura
        $bill->totalPagar = $total;

        // Aquí obtienes el usuario asociado y lo guardas en el campo idUsuario

        $user = User::findOrFail($data['idUsuario']);
        $bill->user()->associate($user);
        $bill->compra()->associate($compra);
        $bill->save();
    
        return response()->json(['status' => 201, 'message' => 'Factura creada', 'bill' => $bill], 201);
    } catch (\Exception $e) {
        return response()->json(['status' => 500, 'message' => 'Error al crear la factura: ' . $e->getMessage()], 500);
    }

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

