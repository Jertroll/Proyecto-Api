<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(
    function(){
         //RUTAS ESPECIFICAS
        
        //RUTAS AUTOMATICAS Restful
        Route::resource('/producto',ProductoController::class,['except'=>['create','edit']]);  
    }
);
