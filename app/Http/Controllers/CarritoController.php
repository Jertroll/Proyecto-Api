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
    public function store(Request $request)
    {
        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            array_walk_recursive($data, function (&$value) {
                $value = trim($value);
            });
            $rules = [
                'user_id' => 'required',
                '_productos.*.id' => 'required', // Asegúrate de que cada producto tenga un ID
                '_productos.*.cantidad' => 'required|numeric|min:1', // Asegúrate de que cada producto tenga una cantidad válida
            ];
            $isValid = \validator($data, $rules);
            if (!$isValid->fails()) {
                $carrito = new Carrito();
                $carrito->user_id = $data['user_id'];
                if (isset($data['_productos']) && is_array($data['_productos'])) {
                    foreach ($data['_productos'] as $producto) {
                        // Adjunta cada producto al carrito con su cantidad respectiva
                        $carrito->productos()->attach($producto['id'], ['cantidad' => $producto['cantidad']]);
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
    
}
