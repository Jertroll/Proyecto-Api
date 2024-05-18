<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\BillController;

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductoCarritoController;
use App\Http\Controllers\DetalleFacturaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware; 
use App\Http\Controllers\CarritoController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserMiddleware;

Route::prefix('v1')->group(function () {
   
      Route::get('/productos', [ProductoController::class,'index']); 
      Route::get('/productos/{id}',[ProductoController::class,'show']); 

      Route::post('/user/login', [UserController::class, 'login']);

      //rutas automaticas Restful Admin
      Route::group(['prefix' => '/admin'], function () {
       
        Route::get('/user/getidentity', [UserController::class, 'getIdentity'])->middleware(ApiAuthMiddleware::class);
        Route::resource('/user', UserController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]); //Se excluyen porque son obsolutas por temas de seguridad 
        Route::resource('/producto', ProductoController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);
        Route::resource('/carrito', CarritoController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);
        Route::resource('/compra', CompraController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);
        Route::resource('/bill', BillController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);
        Route::resource('/detalleFactura', DetalleFacturaController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);

        Route::post('/producto/upload',[ProductoController::class,'uploadImage'])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);
        Route::get('/producto/getimage/{filename}',[ProductoController::class,'getImage'])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);


        Route::post('/carrito/{id}/vaciarCarrito', [CarritoController::class, 'vaciarCarrito'])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);
    }); //admin prefijo -------------




    

  
    //rutas especificas

    Route::group(['prefix' => '/client'], function () {

        //Bill
        Route::get('/bill/{idFactura}', [BillController::class, 'show'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::get('/bill/{idFactura}', [BillController::class, 'index'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);

        //Carrito
       // Route::post('/carrito/{id}/addProductToCart', [CarritoController::class, 'addProductToCart'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]); // Agregar producto al carrito
       // Route::post('/carrito/{id}/removeProductFromCart', [CarritoController::class, 'removeProductFromCart'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);


 
        Route::get('/carrito/{id}', [CarritoController::class, 'show'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::put('/carrito/{id}', [CarritoController::class, 'update'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::post('/carrito/store', [CarritoController::class, 'store'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);

        //Usuario
        
        Route::get('/user/getidentity', [UserController::class, 'getIdentity'])->middleware(ApiAuthMiddleware::class);
        Route::get('/user/{id}', [UserController::class, 'show'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::put('/user/{id}', [UserController::class, 'update'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::post('/user/{id}', [UserController::class, 'store'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);

           //Producto Generales, pueden ver los productos sin necesidad de estas registrado en la apgina



        //compra
        Route::get('/compras', [CompraController::class, 'index'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::get('/compras/{idCompra}', [CompraController::class, 'show'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::post('/compras', [CompraController::class, 'store'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::put('/compras/{idCompra}', [CompraController::class, 'update'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);

        //detalle factura
        Route::get('/detalle_facturas', [DetalleFacturaController::class, 'index'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::get('/detalle_facturas/{idDetalleFactura}', [DetalleFacturaController::class, 'show'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
    }); //prefijo client --------------



}); //V1 prefijo ----------------
