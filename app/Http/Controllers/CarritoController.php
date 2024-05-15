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
            "message"=>"Todos los registro de los productos",
            "data"=>$data
        );
        return response()->json($response,200);
    
    }
    public function show($id){
        $data=Carrito::find($id);
        if(is_object($data)){
            $data=$data->load('user');
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
        $data_input=$request->input('data',null);
        if($data_input){
            $data=json_decode($data_input,true);
            array_walk_recursive($data, function (&$value) {
                $value = trim($value);
            });
            $rules=[
                
                'user_id'=>'required'

            ];
            $isValid=\validator($data,$rules);
            if(!$isValid->fails()){
                $idPro=[];
                $carrito=new Carrito();
                $carrito->user_id=$data['user_id'];
                /*$jwt=new JwtAuth();
                $carrito->user_id=$jwt->checkToken($request->header('bearertoken'),true)->iss;*/
                if (isset($data['_productos']) && is_array($data['_productos'])) {
                    $carrito->producto()->attach($data['_productos']);
                }
                $carrito->save();
                $response=array(
                    'status'=>201,
                    'message'=>'Carrito creado',
                    'category'=>$carrito
                );
            }else{
                $response=array(
                    'status'=>406,
                    'message'=>'Datos inválidos',
                    'errors'=>$isValid->errors()
                );
            }
        }else{
            $response=array(
                'status'=>400,
                'message'=>'No se encontró el objeto data'                
            );
        }
        return response()->json($response,$response['status']);
    }
}
