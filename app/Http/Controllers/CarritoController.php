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
        array_walk_recursive($data, function (&$value) { //recorre el data 
            $value = trim($value); // elimina los espacios en blanco trim
        });

        $rules = [
            'user_id' => 'required',
            '_productos.*.id' => 'required',
            '_productos.*.cantidad' => 'required|numeric|min:1',
        ];
        //Validar reglas 
        $isValid = \validator($data, $rules);
        //  Creacion del carrito
        if (!$isValid->fails()) {
            $carrito = new Carrito();
            $carrito->user_id = $data['user_id'];
            $carrito->save();

            if (isset($data['_productos']) && is_array($data['_productos'])) {
                /*foreach ($data['_productos'] as $producto) {
                    $carrito->productos()->attach($producto['id'], ['cantidad' => $producto['cantidad']]);
                }*/
                foreach ($data['_productos'] as $producto) {
                    // Adjuntar cada producto al carrito con su cantidad respectiva
                    $carrito->productos()->attach($producto['id'], ['cantidad' => $producto['cantidad'], 'carrito_id' => $carrito->id]);
                }
                
            }

            /*$carrito->save();*/

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
           {
                $dataInput = $request->input('data', null);
                $data = json_decode($dataInput, true);
        
                if (empty($data)) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Datos no proporcionados o incorrectos',
                    );
                } else {
                    $rules = [
                        
                        'nombre'=>'required',
                        'precio'=>'required',
                        'descripcion'=>'required',
                        'talla'=>'required',
                        'estado'=>'required',
                        'imagen'=>'required'

                    ];
        
                    $valid = \validator($data, $rules);
        
                    if ($valid->fails()) {
                        $response = array(
                            'status' => 406,
                            'message' => 'Datos enviados no cumplen con las reglas establecidas',
                            'errors' => $valid->errors(),
                        );
                    } else {
                        if (!empty($id)) {
                            $producto = Producto::find($id);
        
                            if ($producto) {
                                $producto->nombre=$data['nombre'];
                                $producto->precio=$data['precio'];
                                $producto->descripcion=$data['descripcion'];
                                $producto->talla=$data['talla'];
                                $producto->estado=$data['estado'];
                                $producto->imagen=$data['imagen'];
                                $producto->save();
        
                                $response = array(
                                    'status' => 200,
                                    'message' => 'Datos actualizados satisfactoriamente',
                                );
                            } else {
                                $response = array(
                                    'status' => 400,
                                    'message' => 'El carrito no existe',
                                );
                            }
                        } else {
                            $response = array(
                                'status' => 400,
                                'message' => 'El ID del carrito no es válido',
                            );
                        }
                    }
                }
        
                return response()->json($response, $response['status']);
        }
    }

    public function addProductToCart(Request $request, $id){
        $producto_id = $request->input('producto_id');
        $cantidad = $request->input('cantidad');
    
        // Validar los datos
        $rules = [
            'producto_id' => 'required|numeric', // Ajusta el nombre del campo según tu necesidad
            'cantidad' => 'required|numeric|min:1',
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
                // Verificar si el producto ya está en el carrito
                $productoExistente = $carrito->productos()->where('producto_id', $producto_id)->first();
    
                if ($productoExistente) {
                    // Si el producto ya está en el carrito, actualizar la cantidad
                    $productoExistente->pivot->cantidad += $cantidad;
                    $productoExistente->pivot->save();
                } else {
                    // Si el producto no está en el carrito, agregarlo con la cantidad especificada
                    $carrito->productos()->attach($producto_id, ['cantidad' => $cantidad]);
                }
    
                // Respuesta de éxito
                $response = [
                    'status' => 200,
                    'message' => 'Producto agregado al carrito satisfactoriamente',
                ];
            }
        }
    
        return response()->json($response, $response['status']);
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
    

}
