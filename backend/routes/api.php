<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test',function(){
       return response()->json([
          'message'=>'ok'
       ]);
})->middleware('auth:sanctum');



Route::middleware('auth:sanctum')->get('/user-test', function (Request $request) {
    return response()->json([
        'user' => $request->user()
    ]);
});
