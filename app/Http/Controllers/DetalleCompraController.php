<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use Illuminate\Http\Request;
use App\Models\Compra;
use App\Models\Producto;

class DetalleCompraController extends Controller
{
    // Listar todos los detalles de compra
    public function index()
    {
        $detalles = DetalleCompra::all();
        return response()->json($detalles);
    }

    // Mostrar un detalle de compra específico
    public function show($id)
    {
        $detalle = DetalleCompra::find($id);
        if (is_null($detalle)) {
            return response()->json(['message' => 'Detalle de compra no encontrado'], 404);
        }
        return response()->json($detalle);
    }
    public function store(Request $request)
    {
        \Log::info('Datos recibidos para crear detalle de compra:', $request->all());
        
        $validatedData = $request->validate([
            'idCompra' => 'required|integer|exists:compra,idCompra',
            'detalles' => 'required|array',
            'detalles.*.idProducto' => 'required|integer|exists:_productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        $detallesCompra = [];
        
        foreach ($validatedData['detalles'] as $detalle) {
            $producto = Producto::findOrFail($detalle['idProducto']);
            $precioUnitario = $producto->precio;
            $subTotal = $precioUnitario * $detalle['cantidad'];

            $detalleCompra = DetalleCompra::create([
                'idCompra' => $validatedData['idCompra'],
                'idProducto' => $detalle['idProducto'],
                'cantidad' => $detalle['cantidad'],
                'precioUnitario' => $precioUnitario,
                'subTotal' => $subTotal,
            ]);

            $detallesCompra[] = $detalleCompra;
        }

        return response()->json([
            'status' => 201,
            'message' => 'Detalle(s) de compra creados exitosamente',
            'detallesCompra' => $detallesCompra,
        ], 201);
    }


    // Actualizar un detalle de compra existente
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'idCompra' => 'integer|exists:compras,id',
            'idProducto' => 'integer|exists:productos,id',
            'cantidad' => 'integer|min:1',
            'precioUnitario' => 'numeric|min:0',
            'subTotal' => 'numeric|min:0',
        ]);

        $detalle = DetalleCompra::find($id);
        if (is_null($detalle)) {
            return response()->json(['message' => 'Detalle de compra no encontrado'], 404);
        }

        $detalle->update($validatedData);
        return response()->json($detalle);
    }

    // Eliminar un detalle de compra
    public function destroy($id)
    {
        $detalle = DetalleCompra::find($id);
        if (is_null($detalle)) {
            return response()->json(['message' => 'Detalle de compra no encontrado'], 404);
        }

        $detalle->delete();
        return response()->json(['message' => 'Detalle de compra eliminado'], 204);
    }
}
