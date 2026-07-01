<?php

use App\Models\User;
use App\Models\Devices;
use App\Models\State;
use App\Models\Device_version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
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


    Route::prefix('watering')
        ->middleware('auth:sanctum')
        ->group(function () {
            Route::post('/get_last_status', function (Request $request) {
                $state = State::where('uuid', $request->uuid)->orderBy('command_date', 'desc')->first();
                return response()->json([
                    'state' => $state
                ]);
            });

            Route::post('/get_history', function (Request $request) {
                $state = State::where('uuid', $request->uuid)->get();
                return response()->json([
                    'states' => $state
                ]);
            });

            Route::post('/turn_off', function (Request $request) {
                $request->validate([
                    'uuid' => 'required|string|exists:devices,uuid',
                ]);
                $uuid = $request->uuid;
                $state = new State();
                $state->watering_state = 0;
                $state->uuid = $uuid;
                $state->save();

                return response()->json([
                    'success' => true
                ]);
            });

            Route::post('/turn_on', function (Request $request) {
                $request->validate([
                    'uuid' => 'required|string|exists:devices,uuid',
                ]);
                $uuid = $request->uuid;
                $state = new State();
                $state->watering_state = 1;
                $state->uuid = $uuid;
                $state->save();

                return response()->json([
                    'success' => true
                ]);
            });
        });
    Route::prefix('update')
        ->middleware('auth:sanctum')
        ->group(function () {

            //Get last version
            Route::post('/get_last_version', function (Request $request) {
                $request->validate([
                    'uuid' => 'required|string|exists:devices,uuid',
                ]);
                $uuid = $request->uuid;

                $device_version = Device_version::where('uuid', $uuid)->get();

                return response()->json(
                     $device_version
                );
            });

            //This API call is made when the update is installed successfully
            Route::post('/installed_update', function (Request $request) {
                $request->validate([
                    'uuid' => 'required|string|exists:devices,uuid',
                ]);
                $uuid = $request->uuid;

                Device_version::where('uuid',$uuid)->update(['update_installed' => 1]);

                return response()->json([
                    'success' => true,
                    'message'=> "Installed update success change."
                    ]);
            });
        });


    Route::post('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});

