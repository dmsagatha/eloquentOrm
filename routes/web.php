<?php

use App\Models\Post;
use App\Models\Billing;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
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

/**
 * Buscar un Usuario y cargar el número de Posts que tiene
 */
Route::get("/with-count-posts/{id}", function (int $id) {
  return User::select(["id", "name", "email"])
    ->withCount("posts")    // Genera la variable posts_count
    // ->toSql();              // Consulta
    ->findOrFail($id);
});

/**
 * Actualizar registros
 */
Route::get("/update/{id}", function (int $id) {
  // En lugar de hacer lo siguiente
  //$post = Post::findOrFail($id);
  //$post->title = "Post actualizado";
  //$post->save();
  //return $post;

  // hacer lo siguiente
  return Post::findOrFail($id)->update([
    "title" => "Post actualizado de nuevo...",
  ]);
});

/**
 * Actualizar un Post existente por su Slug o lo crea si no existe
 */
Route::get("/update-or-create/{slug}", function (string $slug) {
  /* en lugar de
  $post = Post::whereSlug($slug)->first();
  if ($post) {
      $post->update([
          "user_id" => User::all()->random(1)->first()->id,
          "title" => "Post de pruebas",
          "content" => "haciendo algunas pruebas",
      ]);
  } else {
      $post = Post::create([
          "user_id" => User::all()->random(1)->first()->id,
          "title" => "Post de pruebas",
          "content" => "haciendo algunas pruebas",
      ]);
  }
  return $post;
  */

  // haz lo siguiente
  return Post::updateOrCreate(
    [
      "slug" => $slug,
    ],
    [
      "user_id" => User::all()->random(1)->first()->id,
      "category_id" => Category::all()->random(1)->first()->id,
      "title" => "Post de pruebas",
      "content" => "Nuevo contenido del post actualizado...."
    ],
  );
});

/**
 * Eliminar un Post y sus Tags relaconados si existe
 */
Route::get("/delete-with-tags/{id}", function (int $id) {
  try {
    DB::beginTransaction();

    $post = Post::findOrFail($id);
    $post->tags()->detach();        // Eliminado físico | Desvincular
    $post->delete();

    DB::commit();

    return $post;
  } catch (Exception $exception) {
    DB::rollBack();
    return $exception->getMessage();
  }
});

/**
 * Buscar un Post o retorna un 404, pero si existe darle Like
 */
Route::get("/like/{id}", function (int $id) {
  // en lugar de
  // $post = Post::findOrFail($id);
  // $post->likes++;
  // $post->save();

  // haz lo siguiente
  return Post::findOrFail($id)->increment("likes", 20, [
    "title" => "Post con muchos likes",
  ]);
});

/**
 * Buscar un Post o retorna un 404, pero si existe darle Dislike
 */
Route::get("/dislike/{id}", function (int $id) {
  // en lugar de
  // $post = Post::findOrFail($id);
  // $post->dislikes++;
  // $post->save();

  // haz lo siguiente
  return Post::findOrFail($id)->increment("dislikes");    // decrement
});

/**
 * Procesos complejos basados en Chuncks | Trozos
 */
Route::get("/chunk/{amount}", function (int $amount) {
  Post::chunk($amount, function (Collection $chunk) {
  });
});

/**
 * Crear un Usuario y su información de pago, 
 * si existe el usuario lo utiliza
 * si existe el método de pago lo actualiza
 */
Route::get("/create-with-relation", function () {
  try {
    DB::beginTransaction();

    $user = User::firstOrCreate(
      ["name" => "cursosdesarrolloweb"],
      [
        "name" => "cursosdesarrolloweb",
        "age" => 40,
        "email" => "eloquent@cursosdesarrolloweb.es",
        "password" => bcrypt("password"),
      ]
    );

    // Información de pago
    /* $user->billing()->updateOrCreate(
      [
        "user_id" => $user->id,
        "credit_card_number" => "123456789"
      ]
    ); */
    Billing::updateOrCreate(
      ["user_id" => $user->id],
      [
        "user_id" => $user->id,
        "credit_card_number" => "123456789"
      ]
    );

    DB::commit();

    return $user
      ->load("billing:id,user_id,credit_card_number");
  } catch (Exception $exception) {
    DB::rollBack();
    return $exception->getMessage();
  }
});

/**
 * Actualizar un Post y sus relaciones
 */
Route::get("/update-with-relation/{id}", function (int $id) {
  $post = Post::findOrFail($id);
  $post->title = "Post actualizado con relaciones";
  $post->tags()->attach(Tag::all()->random(1)->first()->id);    // Adjuntar
  $post->save();    //  $post->push();
});

/**
 * Post que tengan mas de 2 Etiquetas relacionadas
 */
Route::get("/has-two-tags-or-more", function () {
  return Post::select(["id", "title"])
      ->withCount("tags")
      ->has("tags", ">=", 3)
      ->get();
});

/**
 * Buscar un Post y cargar sus Etiquetas ordenadas pro nombre ascendente
 * 
 * Adicionar relación sortedTags al modelo Post
 */
Route::get("/with-tags-sorted/{id}", function (int $id) {
  //return Post::with("tags:id,tag")    // No esta ordenado
  return Post::with("sortedTags:id,tag")
      ->find($id);
});

/**
 * Buscar todos los Posts que tengan Etiquetas whereHas()
 */
Route::get("/with-where-has-tags", function () {
  return Post::select(["id", "title"])
      ->with("tags:id,tag")
      ->whereHas("tags")
      //->whereDoesHave("tags")       // No tienen Tags
      ->get();
});

/**
 * Scope para buscar todos los Posts que tengan Etiquetas whereHas()
 */
Route::get("/scope-with-where-has-tags", function () {
  return Post::whereHasTagsWithTags()->get();
});

/**
 * Buscar un Post y cargar su Autor de forma automática y sus Etiquetas
 * con tola la información
 * 
 * Adicionar protected $with = ['user:id,name,email',] en el modelo Post
 */
Route::get("/autoload-user-from-post-with-tags/{id}", function (int $id) {
    return Post::with("tags:id,tag")->findOrFail($id);
});

/**
 * Post con atributos personalizados
 * 
 * Adicionar en el modelo Post
 * getTitleWithAuthorAttribute
 * protected $appends = ["title_with_author"];
 */
Route::get("/custom-attributes/{id}", function (int $id) {
  return Post::with("user:id,name")->findOrFail($id);
});

/**
 * Buscar Posts por fecha de creación, válidar formato Y-m-d
 *
 * Adicionar en el modelo Post
 * protected $casts = ["created_at" => "datetime:Y-m-d"];
 * http://127.0.0.1:8000/by-created-at/2021-11-06
 */
Route::get("/by-created-at/{date}", function (string $date) {
    return Post::whereDate("created_at", $date)
        ->get();
});

/**
 * Buscar Posts por día y mes de creación
 *
 * http://127.0.0.1:8000/by-created-at-month-day/05/08
 */
Route::get("/by-created-at-month-day/{day}/{month}", function (int $day, int $month) {
    return Post::whereMonth("created_at", $month)
        ->whereDay("created_at", $day)
        ->get();
});

/**
 * Buscar Posts en un rango de fechas
 *
 * http://127.0.0.1:8000/between-by-created-at/2021-08-01/2021-08-05
 */
Route::get("/between-by-created-at/{start}/{end}", function (string $start, string $end) {
  return Post::whereBetween("created_at", [$start, $end])->get();
});