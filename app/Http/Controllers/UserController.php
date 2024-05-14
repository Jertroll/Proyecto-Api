<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bill;
use Illuminate\Http\Request;


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
    
    public function show($id){
        $data=User::find($id);
        if(is_object($data)){
            $data=$data->load('user'); //No es user es otro en el que use la relacion
            $response=array(
                'status'=>200,
                'message'=>'Datos de la categoria',
                'category'=>$data
            );
        }else{
            $response=array(
                'status'=>404,
                'message'=>'Recurso no encontrado'                
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
                $user->password=hash('sha256',$data['password']);

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
                        'nombre' => 'required',
                        'apellido' =>'required',
                        'telefono' =>'required',
                        'direccion'=>'required',
                        'cedula' =>'required',
                        'email'=>'required',
                        'password'=>'required'

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
                            $user = User::find($id);
        
                            if ($user) {
                                $user->update($data);
        
                                $response = array(
                                    'status' => 200,
                                    'message' => 'Datos actualizados satisfactoriamente',
                                );
                            } else {
                                $response = array(
                                    'status' => 400,
                                    'message' => 'El user$user no existe',
                                );
                            }
                        } else {
                            $response = array(
                                'status' => 400,
                                'message' => 'El ID del user$user no es válido',
                            );
                        }
                    }
                }
        
                return response()->json($response, $response['status']);
        }
    }

    public function destroy($id){
        if(isset($id)){
            $deleted=User::where('id',$id)->delete();
            if($deleted){
                 $response=array(
                     'status'=>200,
                     'message'=>'Usuario eliminada',                    
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
    

    
}
