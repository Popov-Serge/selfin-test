<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/docs', 'docs');

Route::get('/openapi.yaml', function () {
    return response(
        file_get_contents(base_path('openapi.yaml')),
        200,
        ['Content-Type' => 'application/yaml; charset=UTF-8'],
    );
});
