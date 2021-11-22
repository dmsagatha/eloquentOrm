<?php

use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Buscar un Post por su ID
 */
Route::get('find/{id}', function (int $id) {
    return Post::find($id);
});

/**
 * Buscar un Post por su ID o retorna un 404
 */
Route::get('find-or-fail/{id}', function (int $id) {
    try {
        return Post::findOrFail($id);
    } catch (ModelNotFoundException $exception) { // catch (Exception $exception) {
        return $exception->getMessage();
    }
});

/**
 * Buscar un Post por su ID y seleccionar columnas o retorna un 404
 */
Route::get('find-or-fail-with-columns/{id}', function (int $id) {
    return Post::findOrFail($id, ['id', 'title']);
});

/**
 * Buscar un Post por su Slug o retorna un 404
 */
Route::get('find-by-slug/{slug}', function (string $slug) {
    // return Post::where("slug", $slug)->firstOrFail();
    // return Post::whereSlug($slug)->firstOrFail();

    // Mejor opción
    return Post::firstWhere("slug", $slug);
});

/**
 * Buscar muchos Posts por un array de ID's
 */
Route::get('find-many', function () {
    //return Post::whereIn("id", [1, 2, 3])->get();

    // Mejor opción
    //return Post::find([1, 2, 3]);
    return Post::find([1, 2, 3], ["id", "title"]);
});
