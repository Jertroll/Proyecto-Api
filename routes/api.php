<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DetalleFacturaController;
use App\Http\Controllers\DetalleCompraController;
use App\Http\Controllers\CarritoController;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserMiddleware;

Route::prefix('v1')->group(function () {
    Route::post('/agregarCarrito', [CarritoController::class, 'addProductToCart'])->middleware(ApiAuthMiddleware::class);
    // Rutas públicas de productos
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/{id}', [ProductoController::class, 'show']);
    Route::post('/producto/upload', [ProductoController::class, 'uploadImage']);
    Route::get('/producto/getimage/{filename}', [ProductoController::class, 'getImage']);
    Route::put('/producto/{id}/update-imagen', [ProductoController::class, 'updateImagen'])->name('producto.update-imagen');

    Route::resource('/producto', ProductoController::class, ['except' => ['create', 'edit']]);
      Route::post('/login', [UserController::class, 'login']);
      Route::post('/user/register', [UserController::class, 'store']);
      //Route::get('/user/getidentity', [UserController::class, 'getIdentity'])->middleware(ApiAuthMiddleware::class);


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

    // Rutas protegidas por autenticación y roles
    Route::middleware(ApiAuthMiddleware::class)->group(function () {
        // Rutas específicas para admin
        Route::prefix('admin')->middleware(AdminMiddleware::class)->group(function () {
            Route::get('/user/getidentity', [UserController::class, 'getIdentity']);
            Route::resource('/user', UserController::class)->except(['create', 'edit']);
            Route::resource('/carrito', CarritoController::class)->except(['create', 'edit']);
            Route::resource('/bill', BillController::class)->except(['create', 'edit']);
            Route::resource('/compra', CompraController::class)->except(['create', 'edit']);
            Route::resource('/detalleFactura', DetalleFacturaController::class)->except(['create', 'edit']);
            Route::post('/producto/upload', [ProductoController::class, 'uploadImage']);
            Route::get('/producto/getimage/{filename}', [ProductoController::class, 'getImage']);
            Route::post('/carrito/{id}/vaciarCarrito', [CarritoController::class, 'vaciarCarrito']);
        });

        // Rutas específicas para cliente
        Route::prefix('client')->middleware(UserMiddleware::class)->group(function () {
            Route::get('/bill/{idFactura}', [BillController::class, 'show']);
            Route::get('/bills', [BillController::class, 'index']);
            Route::get('/carrito/{id}', [CarritoController::class, 'show']);
            Route::put('/carrito/{id}', [CarritoController::class, 'update']);
            Route::post('/carrito/store', [CarritoController::class, 'store']);
            Route::get('/user/{id}', [UserController::class, 'show']);
            Route::put('/user/{id}', [UserController::class, 'update']);
            Route::get('/productos', [ProductoController::class, 'index']);
            Route::get('/productos/{id}', [ProductoController::class, 'show']);
            Route::get('/compras', [CompraController::class, 'index']);
            Route::get('/compras/{idCompra}', [CompraController::class, 'show']);
            Route::post('/compras', [CompraController::class, 'store']);
            Route::put('/compras/{idCompra}', [CompraController::class, 'update']);
            Route::get('/detalle_facturas', [DetalleFacturaController::class, 'index']);
            Route::get('/detalle_facturas/{idDetalleFactura}', [DetalleFacturaController::class, 'show']);
        });
    });
});
