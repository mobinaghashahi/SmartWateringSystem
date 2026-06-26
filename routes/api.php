<?php

use App\Models\User;
use App\Models\Devices;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//get token by uuid for devices request
Route::post('/get_token', function (Request $request) {
    $devices = Devices::where('uuid', $request->uuid)->first();

    if (!$devices) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    $customer_name = $devices->customer_name;

    //delete previous tokens
    $devices->tokens()->delete();
    $token = $devices->createToken($customer_name);

    return response()->json([
        'token' => $token->plainTextToken
    ]);
});

Route::post('/get_last_watering_status', function (Request $request) {
    $state = State::where('uuid', $request->uuid)->orderBy('command_date', 'desc')->first();
    return response()->json([
        'state' => $state
    ]);
})->middleware('auth:sanctum');

Route::post('/get_watering_history', function (Request $request) {
    $state = State::where('uuid', $request->uuid)->get();
    return response()->json([
        'states' => $state
    ]);
})->middleware('auth:sanctum');

Route::post('/turn_off_watering', function (Request $request) {
    $request->validate([
        'uuid' => 'required|string|exists:devices,uuid',
    ]);
    $uuid=$request->uuid;
    $state = new State();
    $state->watering_state = 0;
    $state->uuid = $uuid;
    $state->save();

    return response()->json([
        'success' => true
    ]);
})->middleware('auth:sanctum');

Route::post('/turn_on_watering', function (Request $request) {
    $request->validate([
        'uuid' => 'required|string|exists:devices,uuid',
    ]);
    $uuid=$request->uuid;
    $state = new State();
    $state->watering_state = 1;
    $state->uuid = $uuid;
    $state->save();

    return response()->json([
        'success' => true
    ]);
})->middleware('auth:sanctum');


Route::post('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/test', function (Request $request) {
    return ['name' => [
        'ali',
        'hassan'
    ]];
});
