<?php

namespace App\Http\Controllers;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Bill;
use Illuminate\Http\Request;

use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(){
        $data=User::all();
        $response=array(
            "status"=>200,
            "message"=>"Todos los registro de Usuario",
            "data"=>$data
        );
        return response()->json($response,200);

    }
    
    public function show($id)
    {
        $data = User::find($id);
        if (is_object($data)) {
            $response = [
                'status' => 200,
                'message' => 'Datos del usuario',
                'user' => $data
            ];
        } else {
            $response = [
                'status' => 404,
                'message' => 'Usuario no encontrado'
            ];
        }
    
        return response()->json($response, $response['status']);
    }
    

    public function store(Request $request){
        $data_input=$request->input('data',null);
        if($data_input){
            $data=json_decode($data_input,true);
            $data=array_map('trim',$data);
            $rules=[
                'nombre'=>'required|alpha',
                'apellido'=>'required', 
                'telefono'=>'required', 
                'direccion'=>'required', 
                'cedula'=>'required', 
                'rol'=>'required' ,
                'email'=>'required|email|unique:users',
                'password'=>'required'
                             
            ];
            $isValid=\validator($data,$rules);
            if(!$isValid->fails()){
                $user=new User();
                $user->nombre=$data['nombre'];
                $user->apellido=$data['apellido'];
                $user->telefono=$data['telefono'];
                $user->direccion=$data['direccion'];
                $user->cedula=$data['cedula'];
                $user->rol=$data['rol'];
                $user->email=$data['email'];
                $user->password=password_hash($data['password'],PASSWORD_DEFAULT);
                $user->imagen=$data['imagen'];

                $user->save();
                
                $response=array(
                    'status'=>201,
                    'message'=>'Usuario creado',
                    'user'=>$user
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



      

    public function update(Request $request, $id)
{
    // Obtener los datos directamente del request
    $data = $request->all();

    // Validar que los datos no estén vacíos
    if (empty($data)) {
        return response()->json([
            'status' => 400,
            'message' => 'Datos no proporcionados o incorrectos',
        ], 400);
    }

    // Reglas de validación
    $rules = [
        'nombre' => 'required',
        'apellido' => 'required',
        'telefono' => 'required',
        'direccion' => 'required',
        'cedula' => 'required',
        'email' => 'required|email',
    ];

    // Validar los datos
    $validator = \Validator::make($data, $rules);

    if ($validator->fails()) {
        return response()->json([
            'status' => 406,
            'message' => 'Datos enviados no cumplen con las reglas establecidas',
            'errors' => $validator->errors(),
        ], 406);
    }

    // Verificar si el ID del usuario es válido
    if (empty($id)) {
        return response()->json([
            'status' => 400,
            'message' => 'El ID del usuario no es válido',
        ], 400);
    }

    // Buscar el usuario por ID
    $user = User::find($id);

    if ($user) {
        // Actualizar los datos del usuario
        $user->nombre = $data['nombre'];
        $user->apellido = $data['apellido'];
        $user->telefono = $data['telefono'];
        $user->direccion = $data['direccion'];
        $user->cedula = $data['cedula'];
        $user->email = $data['email'];
        $user->rol = $data['rol'];
        $user->imagen = $data['imagen'];

        
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Datos actualizados satisfactoriamente',
        ], 200);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'El usuario no existe',
        ], 400);
    }
}




    
    public function login(Request $request){
      //  \Log::info('Datos recibidos:', $request->all());
        $data_input=$request->input('data',null);
        $data=json_decode($data_input,true);
        $data=array_map('trim',$data);
        $rules=['email'=>'required','password'=>'required'];
        $isValid=\validator($data,$rules);
        if(!$isValid->fails()){
            $jwt=new JwtAuth();
            $response=$jwt->getToken($data['email'],$data['password']);
            return response()->json($response);
        }else{
            $response=array(
                'status'=>406,
                'message'=>'Error en la validación de los datos',
                'errors'=>$isValid->errors(),
            );
            return response()->json($response,406);
        }

    }


     public function getIdentity(Request $request){
        $jwt=new JwtAuth();
        $token=$request->header('ElPerroCR');
        if(isset($token)){
            $response=$jwt->checkToken($token,true);
        }else{
            $response=array(
                'status'=>404,
                'message'=>'token (ElPerroCR) no encontrado',
            );
        }
        return response()->json($response);
    }

    public function destroy($id) {
        if (isset($id)) {
            $deleted = user::where('id', $id)->delete();
            if ($deleted) {
                $response = array(
                    'status' => 200,
                    'message' => 'Usuario eliminado',
                );
            } else {
                $response = array(
                    'status' => 400,
                    'message' => 'No se pudo eliminar el recurso, compruebe que exista'
                );
            }
        } else {
            $response = array(
                'status' => 406,
                'message' => 'Falta el identificador del recurso a eliminar'
            );
        }
        return response()->json($response, $response['status']);
    }

    public function uploadImage(Request $request){
        $image=$request->file('file0');
        $isValid=\Validator::make($request->all(),['file0'=>'required|image|mimes:jpg,png,jpeg,svg']);
        if(!$isValid->fails()){
            $filename=\Str::uuid().".".$image->getClientOriginalExtension();
            \Storage::disk('usuarios')->put($filename,\File::get($image));
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
            $exist=\Storage::disk('usuarios')->exists($filename);
            if($exist){
                $file=\Storage::disk('usuarios')->get($filename);
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
    public function updateImagen(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,png,jpeg,svg'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 400);
        }
        $user = User::findOrFail($id);
        $image = $request->file('file0');
        $filename = Str::uuid() . "." . $image->getClientOriginalExtension();

        Storage::disk('usuarios')->put($filename, \File::get($image));

        // Eliminar la imagen anterior si existe
        if ($user->imagen) {
            Storage::disk('user')->delete($user->imagen);
        }

        $user->imagen = $filename;
        $user->save();
        $response = [
            'status' => 200,
            'message' => 'Imagen actualizada exitosamente',
            'filename' => $filename
        ];

        return response()->json($response, 200);
    }
    
}
