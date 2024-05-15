<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductoCarritoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware; 

Route::get('/User', function(){
return 'Obteniendo lista de usuarios';
});

Route::get('/User/{id}', function(){
return 'Obteniendo un usuario';
 });
   
Route::post('/User', function(){
 return 'Creando un usuario';
});

Route::put('/User/{id}', function(){
 return 'Actualizando un usuario';
 });

 Route::delete('/User/{id}', function(){
return 'Eliminando un usuario';
});




Route::prefix('v1')->group(
function(){
    //rutas especificas
    Route::get('/bill/{bill}', [BillController::class, 'show']);
    Route::delete('/bill/{bill}', [BillController::class, 'destroy']);

    Route::post('/user/login',[UserController::class,'login']);
    Route::get('/user/getidentity',[UserController::class,'getIdentity'])->middleware(ApiAuthMiddleware::class);
    //rutas automaticas Restful
    Route::resource('/bill',BillController::class);
    Route::resource('/producto',ProductoController::class);
    Route::resource('/carrito',CarritoController::class);
    Route::resource('/user',UserController::class); //Se excluyen porque son obsolutas por temas de seguridad 
    Route::resource('/compra',CompraController::class);
}

);
