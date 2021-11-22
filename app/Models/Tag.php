<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
  use HasFactory;

    protected $fillable = [
        "tag",
    ];
    
    /**
     * Ocultar pivot de los resultados de la array
     */
    protected $hidden = [
      'pivot',
    ];

    public function posts(): BelongsToMany {
        return $this->belongsToMany(Post::class);
    }
}
