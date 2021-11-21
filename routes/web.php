<?php

use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Buscar un Post por su ID
 */
Route::get('buscar/{id}', function (int $id) {
    return Post::find($id);
});
