<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use Illuminate\Http\Request;

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
