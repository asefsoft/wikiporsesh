<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperCategory
 */
class Category extends Model
{
    use HasFactory;

    public $guarded = [];

    // many-to-many relationship
    public function articles() : Relation {
        return $this->belongsToMany(Article::class,
            'article_categories',  'category_id', 'article_id'
        );
    }


}
