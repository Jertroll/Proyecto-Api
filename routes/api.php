<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\BillController;

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductoCarritoController;
use App\Http\Controllers\DetalleFacturaController;
use App\Http\Controllers\DetalleCompraController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware; 
use App\Http\Controllers\CarritoController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserMiddleware;

Route::prefix('v1')->group(function () {

    Route::resource('/producto', ProductoController::class, ['except' => ['create', 'edit']]);
      Route::post('/login', [UserController::class, 'login']);
      Route::post('/user/register', [UserController::class, 'store']);
     

      Route::get('/productos/{id}',[ProductoController::class,'show']);
      Route::post('/producto/upload',[ProductoController::class,'uploadImage']);
      Route::get('/producto/getimage/{filename}',[ProductoController::class,'getImage']);
      Route::put('/producto/{id}/update-imagen', [ProductoController::class, 'updateImagen'])->name('producto.update-imagen');
      Route::resource('detalleCompra', DetalleCompraController::class, ['except' => ['create', 'edit']]);
      Route::get('/producto/buscar/{nombre}', [ProductoController::class, 'buscarNombre']);

      Route::post('/carrito/store', [CarritoController::class, 'store']);
      Route::post('/agregarCarrito', [CarritoController::class, 'addProductToCart']);
      Route::get('/user/{id}', [UserController::class, 'show']);
      Route::get('/obtenerProductosCarrito',[CarritoController::class, 'obtenerProductosCarrito']);

      Route::resource('/compra', CompraController::class, ['except' => ['create', 'edit']]);
      Route::delete('carrito/{carritoId}/eliminarProductosComprados', [CompraController::class, 'eliminarProductosComprados']);
      Route::delete('carrito/{productoId}/eliminar', [CarritoController::class, 'removeProductFromCart']);
      
      Route::post('/user/upload',[UserController::class,'uploadImage']);
      Route::get('/user/getimage/{filename}',[UserController::class,'getImage']);
      Route::put('/user/{id}/update-imagen', [UserController::class, 'updateImagen'])->name('user.update-imagen');

      //rutas automaticas Restful Admin
      Route::group(['prefix' => '/admin'], function () {
       
        Route::get('/user/getidentity', [UserController::class, 'getIdentity'])->middleware(ApiAuthMiddleware::class);
        Route::resource('/user', UserController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]); //Se excluyen porque son obsolutas por temas de seguridad 
        //Route::resource('/producto', ProductoController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);
        Route::resource('/carrito', CarritoController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);
        Route::resource('/bill', BillController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);
        //Route::resource('/compra', CompraController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);
        Route::resource('/detalleFactura', DetalleFacturaController::class, ['except' => ['create', 'edit']])->middleware([ApiAuthMiddleware::class, AdminMiddleware::class]);


    }); //admin prefijo -------------

    

  
    //rutas especificas

    Route::group(['prefix' => '/client'], function () {

        //Bill
        Route::get('/bill/{idFactura}', [BillController::class, 'show'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::get('/bill/{idFactura}', [BillController::class, 'index'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);

        //Carrito
       
        Route::post('/carrito/{id}/removeProductFromCart', [CarritoController::class, 'removeProductFromCart'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::post('/carrito/{id}/vaciarCarrito', [CarritoController::class, 'vaciarCarrito'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::get('/carritos/{id}', [CarritoController::class, 'show'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::put('/carritos/{id}', [CarritoController::class, 'update'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
       

        //Usuario
        
        Route::get('/user/getidentity', [UserController::class, 'getIdentity'])->middleware(ApiAuthMiddleware::class);
        Route::get('/user/{id}', [UserController::class, 'show'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::put('/user/{id}', [UserController::class, 'update'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);
        Route::post('/user/{id}', [UserController::class, 'store'])->middleware([ApiAuthMiddleware::class, UserMiddleware::class]);

           //Producto
        //Route::post('/producto/upload',[ProductoController::class,'uploadImage']);
        //Route::get('/producto/getimage/{filename}',[ProductoController::class,'getImage']);
        Route::get('/productos/{id}', [ProductoController::class,'index'])->middleware([ApiAuthMiddleware::class,UserMiddleware::class]); 
        Route::get('/productos/{id}',[ProductoController::class,'show'])->middleware([ApiAuthMiddleware::class,UserMiddleware::class]); 

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
