<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
  use HasFactory;

    protected $fillable = [
        "user_id",
        "category_id",
        "title",
        "slug",
        "likes",
        "dislikes",
        "content",
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class)->withDefault([
            "id" => -1,
            "name" => "No existe",
        ]);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany {
        return $this->belongsToMany(Tag::class);
    }

    public function setTitleAttribute(string $title) {
        $this->attributes["title"] = $title;
        $this->attributes["slug"] = Str::slug($title);
    }
}
