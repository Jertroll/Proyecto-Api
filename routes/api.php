<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillController;


/** Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */

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

    //rutas automaticas Restful
    Route::resource('/bill',BillController::class);


}

);