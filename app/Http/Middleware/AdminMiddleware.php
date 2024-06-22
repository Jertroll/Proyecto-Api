<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\JwtAuth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $jwt = new JwtAuth();
        $token = $request->header('ElPerroCR'); 
        $logged = $jwt->checkToken($token, true);

       // return response()->json($logged); Retorna el Json para comprobar datos

       $rolAdmin = "admin";


       /*
         if ($logged && $logged->rol === 'admin') {

            return $next($request);

        } else {
            return response()->json([
                'status' => 401,
                'message' => 'No tiene privilegios para acceso al recurso, debes ser'
            ], 401);
        }
       */
       if (!is_bool($logged) && $logged->rol == $rolAdmin) {
        return $next($request);
    } else {
        $response = [
            "status" => 403,
            "message" => "El usuario no tiene la autorización para realizar esta acción. Se requiere ser un " . $rolAdmin,
        ];
        return response()->json($response, 403);
    }

    }

}