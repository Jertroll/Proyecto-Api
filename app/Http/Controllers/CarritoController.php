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
        $data = $request->input('data', null);
    
        if ($data) {
            array_walk_recursive($data, function (&$value) { // Recorre el data
                $value = trim($value); // Elimina los espacios en blanco
            });
    
            $rules = [
                '_productos' => 'nullable|array', // Permitir que '_productos' sea un arreglo opcional
                '_productos.*.id' => 'sometimes|required|exists:productos,id',
            ];
            // Validar reglas 
            $isValid = \validator($data, $rules);
            // Creación del carrito
            if (!$isValid->fails()) {
                $carrito = new Carrito();
                $jwt = new JwtAuth();
                $carrito->user_id = $jwt->checkToken($request->header('bearertoken'), true)->iss;
                $carrito->save();
    
                if (isset($data['_productos']) && is_array($data['_productos'])) {
                    foreach ($data['_productos'] as $producto) {
                        // Adjuntar cada producto al carrito
                        $carrito->productos()->attach($producto['id'], ['carrito_id' => $carrito->id]);
                    }
                }
    
                $response = [
                    'status' => 201,
                    'message' => 'Carrito creado',
                    'category' => $carrito
                ];
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
    
    
    public function addProductToCart(Request $request, $carritoId) {
    $token = $request->header('Authorization');
    $jwt = new JwtAuth();
    $user = $jwt->checkToken($token, true);
    
    if (!$user) {
        return response()->json([
            'status' => 401,
            'message' => 'Token inválido o no proporcionado.'
        ], 401);
    }
    
    $userId = $user->iss;

    // Verificar si el carrito pertenece al usuario
    $carrito = Carrito::find($carritoId);

    if ($carrito && $carrito->user_id == $userId) {
        $rules = [
            'producto_id' => 'required|numeric|exists:productos,id',
        ];
        
        $productoId = $request->input('producto_id');
      

        $validator = \validator($request->all(), $rules);

        if ($validator->fails()) {
            // Si la validación falla, retorna un mensaje de error
            return response()->json([
                'status' => 406,
                'message' => 'Datos enviados no cumplen con las reglas establecidas',
                'errors' => $validator->errors(),
            ], 406);
        }

        // Verificar si el producto ya está en el carrito
        $productoExistente = $carrito->productos()->where('producto_id', $productoId)->first();

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

    
    public function removeProductFromCart(Request $request, $id){
            $producto_id = $request->input('producto_id');
    
            // Validar los datos
            $rules = [
                'producto_id' => 'required|numeric', // Ajusta el nombre del campo según tu necesidad
            ];
    
            $validator = \validator($request->all(), $rules);
    
            if ($validator->fails()) {
                // Si la validación falla, retorna un mensaje de error
                $response = [
                    'status' => 406,
                    'message' => 'Datos enviados no cumplen con las reglas establecidas',
                    'errors' => $validator->errors(),
                ];
            } else {
                // Buscar el carrito por su ID
                $carrito = Carrito::find($id);
    
                if (!$carrito) {
                    // Si el carrito no existe, retorna un mensaje de error
                    $response = [
                        'status' => 404,
                        'message' => 'El carrito no existe',
                    ];
                } else {
                    // Verificar si el producto está en el carrito
                    $productoExistente = $carrito->productos()->where('producto_id', $producto_id)->first();
    
                    if (!$productoExistente) {
                        // Si el producto no está en el carrito, retorna un mensaje de error
                        $response = [
                            'status' => 404,
                            'message' => 'El producto no está en el carrito',
                        ];
                    } else {
                        // Eliminar el producto del carrito
                        $carrito->productos()->detach($producto_id);
    
                        // Respuesta de éxito
                        $response = [
                            'status' => 200,
                            'message' => 'Producto eliminado del carrito satisfactoriamente',
                        ];
                    }
                }
            }
    
            return response()->json($response, $response['status']);
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

}

