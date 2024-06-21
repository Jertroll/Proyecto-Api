<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\JwtAuth;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Support\Facades\Log;

class CarritoController extends Controller
{
    
    public function index(){
        $data=Carrito::all();
        $response=array(
            "status"=>200,
            "message"=>"Todos los registro de carritos",
            "data"=>$data
        );
        return response()->json($response,200);
    
    }
    public function obtenerProductosCarrito(Request $request) {
        $token = $request->header('bearertoken');
        
        if (!$token) {
            return response()->json([
                'status' => 401,
                'message' => 'Token inválido o no proporcionado.'
            ], 401);
        }
        
        $jwt = new JwtAuth();
        $userId = $jwt->checkToken($token, true)->iss;
    
        // Verificar si el carrito pertenece al usuario
        $carrito = Carrito::where('user_id', $userId)->first();
    
        if ($carrito) {
            // Obtener todos los productos en el carrito del usuario
            $productos = $carrito->productos()->get();
    
            return response()->json([
                'status' => 200,
                'message' => 'Productos en el carrito obtenidos correctamente',
                'data' => $productos,
            ], 200);
        } else {
            // El carrito no pertenece al usuario, devolver un error
            return response()->json([
                'status' => 403,
                'message' => 'No estás autorizado para ver este carrito.',
            ], 403);
        }
    }
    public function show($id){
        $data=Carrito::find($id);
        if(is_object($data)){
            $response=array(
                'status'=>200,
                'message'=>'Datos del carrito',
                'data'=>$data,
            );
        }else{
            $response=array(
                'status'=>404,
                'message'=>'Recurso no encontrado',
            );
        }
        return response()->json($response,$response['status']);
    }

    
    public function store(Request $request){
        \Log::info('Encabezados recibidos:', $request->headers->all());
        \Log::info('Cuerpo de la solicitud:', $request->all());
    
        $bearerToken = $request->header('bearertoken');
        if (!$bearerToken) {
            return response()->json(['status' => 400, 'message' => 'Token no proporcionado'], 400);
        }
         // Validar el token y obtener el user_id
         $jwt = new JwtAuth();
         $decodedToken = $jwt->checkToken($bearerToken, true);
 
         // Verifica que el token haya sido decodificado correctamente
         if (!$decodedToken || !isset($decodedToken->iss)) {
             return response()->json(['status' => 400, 'message' => 'Token inválido'], 400);
         }
 
         $userId = $decodedToken->iss;
        // Buscar el carrito del usuario
        $carrito = Carrito::where(['user_id' => $userId])->first();
    
        if ($carrito) {
            $response = [
                'status' => 200,
                'Category' => $carrito
            ];
        } else {
            $carrito = new Carrito();
            $carrito->user_id = $userId; // Asegúrate de asignar el user_id aquí
            $carrito->save();
    
            $response = [
                'status' => 201,
                'message' => 'Carrito creado',
                'category' => $carrito
            ];
        }
    
        return response()->json($response, $response['status']);
    }
    

    public function destroy($id){
        if(isset($id)){
           $deleted=Carrito::where('id',$id)->delete();
           if($deleted){
                $response=array(
                    'status'=>200,
                    'message'=>'Carrito eliminado',                    
                );
           }else{
            $response=array(
                'status'=>400,
                'message'=>'No se pudo eliminar el recurso, compruebe que exista'                
            );
           }
        }else{
            $response=array(
                'status'=>406,
                'message'=>'Falta el identificador del recurso a eliminar'                
            );
        }
        return response()->json($response,$response['status']);
    }
    public function update(Request $request, $id)
    {
        $data = $request->input('data', null);
    
        if (!empty($data)) {
            array_walk_recursive($data, function (&$value) {
                $value = trim($value);
            });
    
            $rules = [
                '_productos.*.id' => 'required',

            ];
    
            $isValid = \validator($data, $rules);
    
            if (!$isValid->fails()) {
                $carrito = Carrito::find($id);
    
                if ($carrito) {
                    foreach ($data['_productos'] as $producto) {
                        // Verificar si el producto existe en el carrito
                        if ($carrito->productos()->where('_productos.id', $producto['id'])->exists()) {
                            // Actualizar la cantidad del producto en el carrito
                            $carrito->productos()->updateExistingPivot($producto['id'], ['cantidad' => $producto['cantidad']]);
                        } else {
                            // El producto no existe en el carrito, puedes manejar esto según tu lógica
                            // Por ejemplo, agregar el producto al carrito o ignorarlo
                        }
                    }
    
                    $response = [
                        'status' => 200,
                        'message' => 'Cantidad de productos actualizada satisfactoriamente',
                    ];
                } else {
                    $response = [
                        'status' => 404,
                        'message' => 'Carrito no encontrado',
                    ];
                }
            } else {
                $response = [
                    'status' => 406,
                    'message' => 'Datos inválidos',
                    'errors' => $isValid->errors()
                ];
            }
        } else {
            $response = [
                'status' => 400,
                'message' => 'No se encontró el objeto data'
            ];
        }
    
        return response()->json($response, $response['status']);
    }
    
    
    public function addProductToCart(Request $request) {
        $token = $request->header('bearertoken');
        
        if (!$token) {
            return response()->json([
                'status' => 401,
                'message' => 'Token inválido o no proporcionado.'
            ], 401);
        }
        
        $jwt = new JwtAuth();
        $userId = $jwt->checkToken($token, true)->iss;
    
        // Verificar si el carrito pertenece al usuario
        $carrito = Carrito::where('user_id', $userId)->first();
    
        if ($carrito) {
            $rules = [
                'producto_id' => 'required|numeric|exists:_productos,id',
            ];
    
            $validator = \Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                // Si la validación falla, retorna un mensaje de error
                return response()->json([
                    'status' => 406,
                    'message' => 'Datos enviados no cumplen con las reglas establecidas',
                    'errors' => $validator->errors(),
                ], 406);
            }
    
            $productoId = $request->input('producto_id');
    
            // Verificar si el producto ya está en el carrito
            $productoExistente = $carrito->productos()->where('producto_id', $productoId)->exists();
    
            if ($productoExistente) {
                // Si el producto ya está en el carrito, devolver un mensaje
                return response()->json([
                    'status' => 409,
                    'message' => 'El producto ya está en el carrito',
                ], 409);
            } else {
                // Si el producto no está en el carrito, agregarlo
                $carrito->productos()->attach($productoId);
    
                // Respuesta de éxito
                return response()->json([
                    'status' => 200,
                    'message' => 'Producto agregado al carrito satisfactoriamente',
                ], 200);
            }
        } else {
            // El carrito no pertenece al usuario, devolver un error
            return response()->json([
                'status' => 403,
                'message' => 'No estás autorizado para modificar este carrito.',
            ], 403);
        }
    }
    

    
    public function removeProductFromCart(Request $request, $productoId)
{
    $token = $request->header('bearertoken');
    
    if (!$token) {
        return response()->json([
            'status' => 401,
            'message' => 'Token inválido o no proporcionado.'
        ], 401);
    }
    
    try {
        // Decodificar el token y obtener el usuario autenticado
        $jwt = new JwtAuth();
        $userId = $jwt->checkToken($token, true)->iss;
        
        // Obtener el carrito del usuario
        $carrito = Carrito::where('user_id', $userId)->first();

        if (!$carrito) {
            return response()->json([
                'status' => 404,
                'message' => 'Carrito no encontrado para este usuario'
            ], 404);
        }

        // Verificar si el producto está en el carrito del usuario
        if (!$carrito->productos()->where('producto_carrito.producto_id', $productoId)->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Producto no encontrado en el carrito'
            ], 404);
        }

        // Eliminar el producto del carrito
        $carrito->productos()->detach($productoId);

        return response()->json([
            'status' => 200,
            'message' => 'Producto eliminado del carrito satisfactoriamente'
        ], 200);
    } catch (\Exception $e) {
        Log::error("Error al eliminar producto del carrito: " . $e->getMessage());
        
        return response()->json([
            'status' => 500,
            'message' => 'Error al eliminar producto del carrito',
            'error' => $e->getMessage()
        ], 500);
    }
}

    
}
