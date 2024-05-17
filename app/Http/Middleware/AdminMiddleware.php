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
        $token = $request->header('ElPerroCR'); // Asegúrate de que el token esté en el encabezado Authorization
        $logged = $jwt->checkToken($token, true);
       // return response()->json($logged); Retorna el Json para comprobar datos

        if ($logged && $logged->rol === 'admin') {
            return $next($request);
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'No tiene privilegios para acceso al recurso'
            ], 401);
        }
    }
}