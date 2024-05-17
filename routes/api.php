<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductoCarritoController;
use App\Http\Controllers\DetalleFacturaController;
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


    //Bill
    Route::get('/bill/{bill}', [BillController::class, 'show']);
    Route::delete('/bill/{bill}', [BillController::class, 'destroy']);
    //Carrito
    Route::post('/carrito/{id}/addProductToCart', [CarritoController::class, 'addProductToCart']); // Agregar producto al carrito
    Route::post('/carrito/{id}/removeProductFromCart', [CarritoController::class, 'removeProductFromCart']); //Elimina 1 producto de un carrito ya existente
    Route::post('/carrito/{id}/vaciarCarrito', [CarritoController::class, 'vaciarCarrito']);

    //Usuario
    Route::post('/user/login',[UserController::class,'login']);
    Route::get('/user/getidentity',[UserController::class,'getIdentity'])->middleware(ApiAuthMiddleware::class);
  
    //Producto
    Route::post('/producto/upload',[ProductoController::class,'uploadImage']);
    Route::get('/producto/getimage/{filename}',[ProductoController::class,'getImage']);


    //compra
    
  
    //rutas automaticas Restful

    Route::resource('/compra',CompraController::class,['except'=>['create','edit']]);
    Route::resource('/user',UserController::class,['except'=>['create','edit']])->middleware(ApiAuthMiddleware::class); //Se excluyen porque son obsolutas por temas de seguridad 
    Route::resource('/bill',BillController::class,['except'=>['create','edit']]);
    Route::resource('/producto',ProductoController::class,['except'=>['create','edit']]);
    Route::resource('/carrito',CarritoController::class,['except'=>['create','edit']]);
    Route::resource('/detalleFactura',DetalleFacturaController::class,['except'=>['create','edit']]);
}

);
