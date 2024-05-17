<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\JwtAuth;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $jwt = new JwtAuth();
        $token = $request->header('ElPerroCR'); // Asegúrate de que el token esté en el encabezado Authorization
        $logged = $jwt->checkToken($token, true);


        if ($logged && $logged->rol === 'user') {
            return $next($request);
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'No tiene privilegios para acceso al recurso'
            ], 401);
        }
    }
}