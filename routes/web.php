<?php




Route::get('/', function () {

    //Rutas de Productos

    
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::post('/producto/store', [ProductoController::class, 'store']);
    Route::get('/producto/show/{id}', [ProductoController::class, 'show']);
    Route::delete('/producto/destroy/{id}', [ProductoController::class, 'delete']);
    Route::put('/producto/update/{id}', [ProductoController::class, 'update']);
    
});
