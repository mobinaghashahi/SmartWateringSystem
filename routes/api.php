<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', function (Request $request) {

    $user = User::where('uuid', $request->uuid)->first();

    if (! $user) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    $token = $user->createToken('api-token');

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
