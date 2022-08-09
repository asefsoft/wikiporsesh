<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperArticleVideo
 */
class ArticleVideo extends Model
{
    use HasFactory;

    public function article() : Relation {
        return $this->belongsTo(Article::class);
    }
}
