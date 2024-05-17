<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Producto;
use App\Models\Carrito;

class ProductoController extends Controller
{
  
    public function index(){
        $data=Producto::all();
        $response=array(
            "status"=>200,
            "message"=>"Todos los registro de los productos",
            "data"=>$data
        );
        return response()->json($response,200);

    }
 
    public function store(Request $request)
    {
        $jsonData = $request->json()->all(); // Obtener el JSON del cuerpo de la solicitud
        $data = $jsonData['data'] ?? null; // Extraer los datos del campo 'data'
    
        if ($data) {
            $data = array_map('trim', $data);
            $rules = [
                'id' => 'required',
                'nombre' => 'required',
                'precio' => 'required',
                'descripcion' => 'required',
                'talla' => 'required',
                'estado' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!in_array($value, ['disponible', 'no disponible'])) {
                            $fail($attribute . ' no es válido. El estado debe ser "disponible" o "no disponible".');
                        }
                    },
                ],
                'imagen' => 'required',
            ];
            $isValid = \validator($data, $rules);
            if (!$isValid->fails()) {
                $producto = new Producto();
                $producto->id = $data['id'];
                $producto->nombre = $data['nombre'];
                $producto->precio = $data['precio'];
                $producto->descripcion = $data['descripcion'];
                $producto->talla = $data['talla'];
                $producto->estado = $data['estado'];
                $producto->imagen = $data['imagen'];
                $producto->save();
                $response = array(
                    'status' => 201,
                    'message' => 'Producto agregado',
                    'producto' => $producto
                );
            } else {
                $response = array(
                    'status' => 406,
                    'message' => 'Datos inválidos',
                    'errors' => $isValid->errors()
                );
            }
        } else {
            $response = array(
                'status' => 400,
                'message' => 'No se encontró el objeto data'
            );
        }
        return response()->json($response, $response['status']);
    }
    
    public function show($id){
        $data=producto::find($id);
        if(is_object($data)){
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
    $jsonData = $request->json()->all(); // Obtener el JSON del cuerpo de la solicitud
    $data = $jsonData['data'] ?? null; // Extraer los datos del campo 'data'

    if ($data === null) {
        $response = array(
            'status' => 400,
            'message' => 'Datos no proporcionados o incorrectos',
        );
    } else {
        $rules = [
            'nombre' => 'required',
            'precio' => 'required',
            'descripcion' => 'required',
            'talla' => 'required',
            'estado' => 'required',
            'imagen' => 'required'
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
                    $producto->nombre = $data['nombre'];
                    $producto->precio = $data['precio'];
                    $producto->descripcion = $data['descripcion'];
                    $producto->talla = $data['talla'];
                    $producto->estado = $data['estado'];
                    $producto->imagen = $data['imagen'];
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

    public function uploadImage(Request $request){
        $isValid=\Validator::make($request->all(),['file0'=>'required|image|mimes:jpg,png,jpeg,svg']);
        if(!$isValid->fails()){
            $image=$request->file('file0');
            $filename=\Str::uuid().".".$image->getClientOriginalExtension();
            \Storage::disk('productos')->put($filename,\File::get($image));
            $response=array(
                'status'=>201,
                'message'=>'Imagen guardada',
                'filename'=>$filename,
            );
        }else{
            $response=array(
                'status'=>406,
                'message'=>'Error: no se encontro el archivo',
                'errors'=>$isValid->errors(),
            );
        }
        return response()->json($response,$response['status']);
    }
    public function getImage($filename){
        if(isset($filename)){
            $exist=\Storage::disk('productos')->exists($filename);
            if($exist){
                $file=\Storage::disk('productos')->get($filename);
                return new Response($file,200);
            }else{
                $response=array(
                    'status'=>404,
                    'message'=>'Imagen no existe',
                );
            }
        }else{
            $response=array(
                'status'=>406,
                'message'=>'No se definió el nombre de la imagen',
            );
        }
        return response()->json($response,$response['status']);
    }


}
