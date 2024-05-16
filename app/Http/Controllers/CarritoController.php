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
        $carrito = Carrito::find($id);
        
        if($carrito){
            $carrito->load('productos'); // Carga los productos directamente a través de la relación
    
            $response = [
                'status' => 200,
                'message' => 'Datos del carrito',
                'data' => $carrito,
            ];
        } else {
            $response = [
                'status' => 404,
                'message' => 'Recurso no encontrado',
            ];
        }
        
        return response()->json($response, $response['status']);
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
                '_productos.*.cantidad' => 'required|numeric|min:1',
            ];
    
            $isValid = \validator($data, $rules);
    
            if (!$isValid->fails()) {
                $carrito = Carrito::find($id);
    
                if ($carrito) {
                    foreach ($data['_productos'] as $producto) {
                        $carrito->productos()->updateExistingPivot($producto['id'], ['cantidad' => $producto['cantidad']]);
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
    
}
