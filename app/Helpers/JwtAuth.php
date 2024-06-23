<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Models\User;

class JwtAuth{
    private $key;
    function __construct(){
        $this->key="ELperrocr"; //Llave privada
    }
    public function getToken($email,$password){
        $user=User::where(['email'=>$email])->first();
        //var_dump($user);
        if(is_object($user) && password_verify($password,$user->password)){
            /*Payload Llave publica*/
            $token=array(
                'iss'=>$user->id,
                'nombre'=>$user->nombre,
                'apellido'=>$user->apellido,
                'telefono'=>$user->telefono,
                'direccion'=>$user->direccion,
                'cedula'=>$user->cedula,
                'rol'=>$user->rol,
                'email'=>$user->email,
                'imagen'=>$user->imagen,
                'iat'=>time(),
                'exp'=>time()+(20000)
            );
            $data=JWT::encode($token,$this->key,'HS256');
        }else{
            $data=array(
                'status'=>401,
                'message'=>'Datos de autenticación incorrectos'
            );
        }
        return $data;
    }

    public function checkToken($jwt,$getId=false){
        $authFlag=false;
        if(isset($jwt)){
            try{
                $decoded=JWT::decode($jwt,new Key($this->key,'HS256'));
            }catch(\DomainException $ex){
                $authFlag=false;
            }catch(ExpiredException $ex){
                $authFlag=false;
            }
            if(!empty($decoded)&&is_object($decoded)&&isset($decoded->iss)){
                $authFlag=true;
            }
            if($getId && $authFlag){
                return $decoded;
            }
        }
        return $authFlag;
    }
    
}