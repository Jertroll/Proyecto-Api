<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compra;
use App\Models\Carrito;
use App\Models\User;

class CompraController extends Controller
{
    public function index()
    {
        // Obtener todas las compras con las relaciones de usuario, carrito y productos cargadas
        $compras = Compra::with('user', 'carrito.productos')->get();
    
        // Iterar sobre cada compra y obtener la lista de productos asociada
        foreach ($compras as $compra) {
            $productos = $compra->carrito->productos;
    
            $compra->ListaProduc = $productos;
        }
    
        // Construir la respuesta JSON
        $response = [
            "status" => 200,
            "message" => "Todos los registros de compras",
            "data" => $compras
        ];

        return response()->json($response, 200);
    }
    

    public function show($idCompra)
    {
        // Obtener la compra con las relaciones de usuario y carrito cargadas
        $compra = Compra::with('user', 'carrito')->findOrFail($idCompra);
        
        // Devolver la compra junto con las relaciones en formato JSON
        return response()->json($compra);
    } 
    public function store(Request $request)
    {
        $data = $request->input('data', null);
        
        if (!$data) {
            return response()->json(['status' => 400, 'message' => 'No se encontró el objeto data'], 400);
        }
        
        $jsonString = '{"key": "value"}';
        $array = json_decode($jsonString, true);

        $validator = \Validator::make($data, [
            'idCompra' => 'required',
            'idUsuario' => 'required',
            'idCarrito' => 'required',
            'estadoCompra' => 'required',
            'total' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => 406, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 406);
        }
        
        try {
            $compra = new Compra();
            $compra->fill($data);
        
            // Asociar el usuario y el carrito
            $compra->idUsuario = $data['idUsuario'];
            $compra->idCarrito = $data['idCarrito'];

            $carrito = Carrito::findOrFail($data['idCarrito']);
            $compra->ListaProduc = $carrito->productos;
            $jsonString = "{\"id\":1,\"nombre\":\"Camiseta\",\"precio\":20,\"descripcion\":\"Camiseta de algodón\",\"talla\":\"M\",\"estado\":\"disponible\",\"imagen\":\"camiseta.jpg\",\"created_at\":\"2024-05-15T02:45:19.000000Z\",\"updated_at\":\"2024-05-15T02:45:19.000000Z\",\"pivot\":{\"carrito_id\":1,\"producto_id\":1,\"cantidad\":2}},{\"id\":4,\"nombre\":\"Vestido\",\"precio\":40,\"descripcion\":\"Vestido elegante\",\"talla\":\"S\",\"estado\":\"disponible\",\"imagen\":\"vestido.jpg\",\"created_at\":\"2024-05-15T02:45:19.000000Z\",\"updated_at\":\"2024-05-15T02:45:19.000000Z\",\"pivot\":{\"carrito_id\":1,\"producto_id\":4,\"cantidad\":6}}";

            // Elimina los caracteres de escape (\)
            $jsonString = stripslashes($jsonString);
            
            // Decodifica la cadena JSON
            $data = json_decode($jsonString, true);
            
            // Ahora, $data contendrá un array de objetos
            
            // Establecer la fecha y la hora automáticamente
            $compra->fecha = date('Y-m-d');
            $compra->hora = date('H:i:s');
        
            $compra->save();
        
            return response()->json(['status' => 201, 'message' => 'Compra creada', 'compra' => $compra], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Error al crear la compra: ' . $e->getMessage()], 500);
        }
    }
    

public function destroy($id)
{
    try {
        $compra = Compra::find($id);
        if (!$compra) {
            return response()->json(['status' => 404, 'message' => 'Compra no encontrada'], 404);
        }

        $compra->delete();

        return response()->json(['status' => 200, 'message' => 'Compra eliminada con éxito'], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 500, 'message' => 'Error al eliminar la compra: ' . $e->getMessage()], 500);
    }
}

public function update(Request $request, $id)
{
    $data = $request->input('data', null);
    
    if (!$data) {
        return response()->json(['status' => 400, 'message' => 'No se encontró el objeto data'], 400);
    }
    
    $jsonString = '{"key": "value"}';
    $array = json_decode($jsonString, true);
    $validator = \Validator::make($data, [
        'estadoCompra' => 'required',
        'total' => 'required',
    ]);
    
    if ($validator->fails()) {
        return response()->json(['status' => 406, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 406);
    }
    
    try {
        $compra = Compra::find($id);
        if (!$compra) {
            return response()->json(['status' => 404, 'message' => 'Compra no encontrada'], 404);
        }
        
        $compra->estadoCompra = $data['estadoCompra'];
        $compra->total = $data['total'];
        
        $compra->save();

        return response()->json([
            'status' => 200,
            'message' => 'Compra actualizada',
            'estadoCompra' => $compra->estadoCompra,
            'total' => $compra->total
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 500, 'message' => 'Error al actualizar la compra: ' . $e->getMessage()], 500);
    }
}


}
