<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Producto;
use App\Models\Carrito;

class ProductoController extends Controller
{
   /* 
     * Metodo GET para obtener todos los registros
     */
    public function index(){
        $data=Producto::all();
        $response=array(
            "status"=>200,
            "message"=>"Todos los registro de los productos",
            "data"=>$data
        );
        return response()->json($response,200);

    }
    /**
     * Metodo POST para crear un registro
     */
    public function store(Request $request){
        $data_input=$request->input('data',null);
        if($data_input){
            $data=json_decode($data_input,true);
            $data=array_map('trim',$data);
            $rules=[
                'id'=>'required',
                'nombre'=>'required',
                'precio'=>'required',
                'descripcion'=>'required',
                'talla'=>'required',
                'estado'=>'required',
                'imagen'=>'required',
            ];
            $isValid=\validator($data,$rules);
            if(!$isValid->fails()){
                $producto=new Producto();
                $producto->id=$data['id'];
                $producto->nombre=$data['nombre'];
                $producto->precio=$data['precio'];
                $producto->descripcion=$data['descripcion'];
                $producto->talla=$data['talla'];
                $producto->estado=$data['estado'];
                $producto->imagen=$data['imagen'];
                $producto->save();
                $response=array(
                    'status'=>201,
                    'message'=>'producto agregado',
                    'producto'=>$producto
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
    public function show($id){
        $data=producto::find($id);
        if(is_object($data)){
            $data=$data->load('posts');
            $response=array(
                'status'=>200,
                'message'=>'Datos del producto',
                'producto'=>$data
            );
        }else{
            $response=array(
                'status'=>404,
                'message'=>'Recurso no encontrado'                
            );
        }
        return response()->json($response,$response['status']);
    }
    public function destroy($id){
        if(isset($id)){
           $deleted=producto::where('id',$id)->delete();
           if($deleted){
                $response=array(
                    'status'=>200,
                    'message'=>'Producto eliminado',                    
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
                                    'message' => 'El producto no existe',
                                );
                            }
                        } else {
                            $response = array(
                                'status' => 400,
                                'message' => 'El ID del producto no es válido',
                            );
                        }
                    }
                }
        
                return response()->json($response, $response['status']);
        }
    }



}
