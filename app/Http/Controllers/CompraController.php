<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compra;
use App\Models\Carrito;
use App\Models\User;
use Illuminate\Support\Facades\Log; 

class CompraController extends Controller
{
    public function index()
    {
        // Añadir un log para verificar que el método se está llamando
        Log::info('CompraController@index called');
        
        $compras = Compra::with('user', 'carrito.productos')->get();

        // Verificar si se obtienen datos de compras
        Log::info('Compras retrieved:', ['compras' => $compras->toArray()]);

        if ($compras->isEmpty()) {
            return response()->json([
                "status" => 404,
                "message" => "No se encontraron registros de compras",
                "data" => []
            ], 404);
        }

        // Iterar sobre cada compra y obtener la lista de productos asociada
        foreach ($compras as $compra) {
            $productos = $compra->carrito->productos->map(function($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => $producto->precio,  // Añade aquí cualquier otro atributo que desees mostrar
                ];
            });
            
            // Asignar la lista de productos formateada a un nuevo atributo en la compra
            $compra->lista_productos = $productos;
        }
        
        // Formatear la respuesta para incluir solo los atributos deseados de las compras
        $response = [
            "status" => 200,
            "message" => "Todos los registros de compras",
            "data" => $compras->map(function($compra) {
                return [
                    'id' => $compra->id,
                    'user_id' => $compra->user_id,
                    'user_name' => $compra->user->name,  // Suponiendo que el nombre del usuario está en el campo 'name'
                    'fecha' => $compra->fecha,
                    'total' => $compra->total,
                    'lista_productos' => $compra->lista_productos,
                    // Añade aquí cualquier otro atributo de la compra que desees mostrar
                ];
            })
        ];

        return response()->json($response, 200);
    }


    
    

    
    

    public function show($idCompra)
    {
        $compra = Compra::with('user', 'carrito')->findOrFail($idCompra);
        
        return response()->json($compra);

        $response=array(
            "status"=>200,
            "message"=>"Todos los registro de los productos",
            "data"=>$data
        );
        return response()->json($response,200);

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
            
            'idUsuario' => 'required',
            'idCarrito' => 'required',
            'estadoCompra' => 'required',
            
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
        $compra->save();

        return response()->json([
            'status' => 200,
            'message' => 'Compra actualizada',
            'estadoCompra' => $compra->estadoCompra,
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 500, 'message' => 'Error al actualizar la compra: ' . $e->getMessage()], 500);
    }
}

public function calcularTotal($idCompra)
{
    $compra = Compra::find($idCompra);

    // Verificar si se encontró la compra
    if (!$compra) {

        return null;
    }

    // Obtener los productos del carrito asociado a la compra
    $productos = $compra->carrito->productos;

    $total = 0;
    // Iterar sobre los productos y calcular el subtotal
    foreach ($productos as $producto) {
        $precio = $producto->precio;
        $cantidad = $producto->pivot->cantidad;
        $subtotal = $precio * $cantidad;
        $total += $subtotal;
    }

    return $total;
}

}
