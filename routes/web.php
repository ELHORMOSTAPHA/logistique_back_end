<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    //welcome api response
    return json_encode([
        'message' => 'Welcome to MoLogistic API',
        'version' => '1.0.0',
        'status' => 'success',
        'data' => [
            'name' => 'm-automotiv API',
            'description' => 'm-automotiv API is a API for managing your m-automotiv needs',
            'version' => '1.0.0',
        ]
    ]);
});
