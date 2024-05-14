<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ProductoCarrito;


class ProductoCarritoController extends Controller
{
    public function index(){
        
        $data=ProductoCarrito::with('producto','carrito')->get();
            // Inicializar un array para almacenar los productos agrupados por carrito
    $productosAgrupados = [];

    // Iterar sobre cada producto y agruparlo por el ID del carrito
    foreach ($data as $producto) {
        $carritoId = $producto->carrito_id;

        // Si aún no existe una entrada para este carrito, crear una nueva entrada en el array
        if (!isset($productosAgrupados[$carritoId])) {
            $productosAgrupados[$carritoId] = [];
        }

        // Agregar el producto al array del carrito correspondiente
        $productosAgrupados[$carritoId][] = $producto;
    }

        
        $response=array(
            "status"=>200,
            "message"=>"Todos los registro de los productos",
            "data"=>$data
        );
        return response()->json($response,200);
    
    }
    public function show($id){
        $data=ProductoCarrito::find($id);
        if(is_object($data)){
            $data=$data->load('user');
            $response=array(
                'status'=>200,
                'message'=>'Datos del Carrito y sus productos',
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
            $data=array_map('trim',$data);
            $rules=[
                
                'producto_id'=>'required',
                'carrito_id'=>'required'

            ];
            $isValid=\validator($data,$rules);
            if(!$isValid->fails()){
                $productoCarrito=new ProductoCarrito();
                $productoCarrito->producto_id=$data['producto_id'];
                $productoCarrito->carrito_id=$data['carrito_id'];
                $productoCarrito->regions()->attach($carrito_id);
                $response=array(
                    'status'=>201,
                    'message'=>'Carrito creado',
                    'category'=>$productoCarrito
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
