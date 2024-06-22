<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use Illuminate\Http\Request;
use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\Log;

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
    public function obtenerDetalles($idCompra)
    {
        try {
            $detalles = DetalleCompra::where('idCompra', $idCompra)->get();
            
            // Agregar log para ver los detalles obtenidos
            \Log::info('Detalles obtenidos para idCompra ' . $idCompra . ': ' . $detalles);
            
            return response()->json(['data' => $detalles], 200);
        } catch (\Exception $e) {
            \Log::error('Error al obtener detalles de la compra: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener los detalles de la compra', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // Verificar y logear los datos recibidos
        Log::info('Datos recibidos para crear detalle de compra:', $request->all());

        // Decodificar el JSON si viene dentro de un campo 'data'
        $data = $request->json()->all();

        // Validar los datos decodificados
        $validatedData = Validator::make($data, [
            'idCompra' => 'required|integer|exists:compra,idCompra',
            'idProducto' => 'required|integer|exists:_productos,id',
            'cantidad' => 'required|integer|min:1',
            'precioUnitario' => 'required|integer',
            'subTotal' => 'required|integer',
        ])->validate();

        // Procesar los detalles de compra
        $detalleCompra = DetalleCompra::create([
            'idCompra' => $validatedData['idCompra'],
            'idProducto' => $validatedData['idProducto'],
            'cantidad' => $validatedData['cantidad'],
            'precioUnitario' => $validatedData['precioUnitario'],
            'subTotal' => $validatedData['subTotal'],
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Detalle de compra creado exitosamente',
            'detalleCompra' => $detalleCompra,
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
