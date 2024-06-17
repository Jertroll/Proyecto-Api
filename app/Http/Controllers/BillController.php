<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\Compra;

class BillController extends Controller
{
    // Obtener todos los registros de Bills
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

    // Crear una nueva Bill
    public function store(Request $request)
    {
        $data = $request->validate([
            'idCompra' => 'required|integer|exists:compra,idCompra',
        ]);
        // Obtener la compra
        $compra = Compra::with('carrito.productos')->findOrFail($data['idCompra']);
        $subTotal = $this->calcularSubTotal($compra);
        $impuesto = ($subTotal - $descuento) * 0.12;
        $total = $subTotal - $descuento + $impuesto;
        $Bill = Bill::create([
            'idCompra' => $compra->id,
            'fechaEmision' => now(),
            'subTotal' => $subTotal,
            'descuento' => $descuento,
            'impuesto' => $impuesto,
            'total' => $total,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Bill creada satisfactoriamente',
            'Bill' => $Bill,
        ], 201);
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

    // Eliminar una Bill por su ID
    public function destroy($idBill)
    {
        $Bill = Bill::find($idBill);

        if ($Bill) {
            $Bill->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Bill eliminada correctamente'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Bill no encontrada'
            ], 404);
        }
    }

    // Actualizar una Bill por su ID
    public function update(Request $request, $idBill)
    {
        $data = $request->validate([
            'total' => 'required|numeric',
            'fechaEmision' => 'required|date'
        ]);

        $Bill = Bill::find($idBill);

        if ($Bill) {
            $Bill->update([
                'total' => $data['total'],
                'fechaEmision' => $data['fechaEmision']
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Datos de la Bill actualizados satisfactoriamente',
                'Bill' => $Bill
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Bill no encontrada'
            ], 404);
        }
    }
}
