<?php

use App\Http\Controllers\UserController;
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
    Route::post('/user/login',[UserController::class,'login']);
    Route::get('/user/getidentity',[UserController::class,'getIdentity'])->middleware(ApiAuthMiddleware::class);
    //rutas automaticas Restful
    Route::resource('/user',UserController::class,['except'=>['create','edit']])->middleware(ApiAuthMiddleware::class); //Se excluyen porque son obsolutas por temas de seguridad 
}

);