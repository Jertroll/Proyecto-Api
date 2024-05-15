<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductoCarrito;

class ProductoCarritoController extends Controller
{
    public function index()
    {
        $productosCarrito = ProductoCarrito::all();

        $response = [
            'status' => 200,
            'message' => 'Todos los registros de productos en carritos',
            'data' => $productosCarrito
        ];

        return response()->json($response, 200);
    }

    public function show($id)
    {
        $productoCarrito = ProductoCarrito::find($id);

        if ($productoCarrito) {
            $response = [
                'status' => 200,
                'message' => 'Datos del producto en carrito',
                'data' => $productoCarrito
            ];
        } else {
            $response = [
                'status' => 404,
                'message' => 'Producto en carrito no encontrado'
            ];
        }

        return response()->json($response, $response['status']);
    }
}
