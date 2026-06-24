<?php

use App\Models\User;
use App\Models\Devices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/get_token', function (Request $request) {
    $devices=Devices::where('uuid', $request->uuid)->first();

    if (! $devices) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    $customer_name=$devices->customer_name;

    //delete previous tokens
    $devices->tokens()->delete();
    $token = $devices->createToken($customer_name);

    return response()->json([
        'token' => $token->plainTextToken
    ]);
});


Route::post('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/test', function (Request $request) {
    return ['name'=>[
        'ali',
        'hassan'
    ]];
});
