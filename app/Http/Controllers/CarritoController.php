<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\JwtAuth;
use App\Models\Carrito;
use App\Models\Producto;

class CarritoController extends Controller

{
    
    public function index(){
        $data=Carrito::with('user','productos')->get();
        $response=array(
            "status"=>200,
            "message"=>"Todos los registro de carritos",
            "data"=>$data
        );
        return response()->json($response,200);
    
    }
    
    public function show($id){
        $data=Carrito::find($id);
        if(is_object($data)){
            $data=$data->load('user','productos');
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
        $jwt = new JwtAuth();
        $userId = $jwt->checkToken($bearerToken, true);
    
        $carrito = Carrito::where(['user_id'=>$userId->iss])->first();
    
        if ($carrito) {
            $response = [
                'status' => 201,
                'category' => $carrito
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
    

    
    public function removeProductFromCart(Request $request, $id) {
        // Asegúrate de que 'producto_id' se obtiene del request correctamente
        $producto_id = $request->input('producto_id');
    
        // Validar los datos
        $rules = [
            'producto_id' => 'required|numeric', // Ajusta el nombre del campo según tu necesidad
        ];
    
        // Usar el validador de Laravel
        $validator = \Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            // Si la validación falla, retorna un mensaje de error
            return response()->json([
                'status' => 406,
                'message' => 'Datos enviados no cumplen con las reglas establecidas',
                'errors' => $validator->errors(),
            ], 406);
        } 
    
        // Buscar el carrito por su ID
        $carrito = Carrito::find($id);
    
        if (!$carrito) {
            // Si el carrito no existe, retorna un mensaje de error
            return response()->json([
                'status' => 404,
                'message' => 'El carrito no existe',
            ], 404);
        }
    
        // Verificar si el producto está en el carrito
        $productoExistente = $carrito->productos()->where('producto_id', $producto_id)->first();
    
        if (!$productoExistente) {
            // Si el producto no está en el carrito, retorna un mensaje de error
            return response()->json([
                'status' => 404,
                'message' => 'El producto no está en el carrito',
            ], 404);
        } 
    
        // Eliminar el producto del carrito
        $carrito->productos()->detach($producto_id);
    
        // Respuesta de éxito
        return response()->json([
            'status' => 200,
            'message' => 'Producto eliminado del carrito satisfactoriamente',
        ], 200);
    }
    
    
    
        public function vaciarCarrito(Request $request, $id)
        {
            try {
                $carrito = Carrito::find($id);
                if (!$carrito) {
                    return response()->json(['status' => 404, 'message' => 'Carrito no encontrado'], 404);
                }
        
                // Eliminar todos los productos asociados al carrito
                $carrito->productos()->detach();
        
                return response()->json(['status' => 200, 'message' => 'Carrito vaciado con éxito'], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 500, 'message' => 'Error al vaciar el carrito: ' . $e->getMessage()], 500);
            }
        }

        public function obtenerCarrito(Request $request)
    {
        // Obtener el token del header
        $token = $request->header('Authorization');
        $jwt = new JwtAuth();
        $user = $jwt->checkToken($token, true);

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Token inválido o no proporcionado.'
            ], 401);
        }

        // Obtener el ID del usuario del token decodificado
        $userId = $user->iss;

        // Buscar un carrito existente asociado al usuario
        $carrito = Carrito::where('user_id', $userId)->first();

        if ($carrito) {
            return response()->json([
                'status' => 200,
                'message' => 'Carrito existente encontrado',
                'carrito_id' => $carrito->id
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No existe un carrito asociado a este usuario.'
            ], 404);
        }
    }

}

