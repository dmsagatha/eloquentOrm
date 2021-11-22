<?php

use App\Models\Post;
use App\Models\Billing;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
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

/**
 * POSTS PAGINADOS CON SELECCIÓN DE COLUMNAS
 */
Route::get("/paginated/{perPage}", function (int $perPage = 10) {
  return Post::paginate($perPage, ["id", "title"]);
});

/**
 * POSTS PAGINADOS MANUALMENTE CON OFFSET/LIMIT
 *
 * http://127.0.0.1:8000/manual-pagination/2 -> primera página
 * http://127.0.0.1:8000/manual-pagination/2/2 -> segunda página
 */
Route::get("/manual-pagination/{perPage}/{offset?}", function (int $perPage, int $offset = 0) {
  return Post::offset($offset)->limit($perPage)->get();
});

/**
 *  CREA UN POST
 */
Route::get("/create", function () {
  $user = User::all()->random(1)->first()->id;

  return Post::create([
    "user_id" => $user,
    "category_id" => Category::all()->random(1)->first()->id,
    "title" => "Post para el usuario {$user}",
    "content" => "Nuevo post de pruebas",
  ]);
});

/**
 * CREA UN POST O SI EXISTE RETORNARLO
 */
Route::get("/first-or-create", function () {
  return Post::firstOrCreate(
    ["title" => "Nuevo post para ordenación"],
    [
      "user_id" => User::all()->random(1)->first()->id,
      "category_id" => Category::all()->random(1)->first()->id,
      "title" => "Nuevo post para ordenación",
      "content" => "cualquier cosa",
    ]
  );
});

/**
 * BUSCA UN POST Y CARGA SU AUTOR, CATEGORÍA Y TAGS CON TODA LA INFORMACIÓN
 */
Route::get("/with-relations/{id}", function (int $id) {
  return Post::with("user", "category", "tags")->find($id);
});

/**
 * BUSCA UN POST Y CARGA SU AUTOR, CATEGORÍA Y TAGS CON TODA LA INFORMACIÓN UTILIZANDO LOAD
 */
Route::get("/with-relations-using-load/{id}", function (int $id) {
  $post = Post::findOrFail($id);
  $post->load("user", "category", "tags");

  return $post;
});

/**
 * Buscar un Post y cargar su Autor, Categoría y Etiquetas con
 * selección de columnas relacionadas
 * Tema: Cargando relaciones con selección de columnas [Tip]
 */
Route::get("/with-relations-and-columns/{id}", function (int $id) {
  return Post::select(['id', 'title', 'user_id', 'category_id'])
      ->with([
        'user:id,name,email',
        // 'user.billing',
        'user.billing:id,user_id,credit_card_number',
        'tags:id,tag',
        'category:id,name'
      ])
      ->find($id);
});