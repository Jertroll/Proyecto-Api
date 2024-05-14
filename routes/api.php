<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProductoCarritoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    //rutas automaticas Restful
    Route::resource('/bill',BillController::class,['except'=>['create','edit']]);
    Route::resource('/producto',ProductoController::class,['except'=>['create','edit']]);
    Route::resource('/carrito',CarritoController::class,['except'=>['create','edit']]);
    Route::resource('/productocarrito',ProductoCarritoController::class,['except'=>['create','edit']]);
    Route::resource('/user',UserController::class,['except'=>['create','edit']]); //Se excluyen porque son obsolutas por temas de seguridad 
}

);
