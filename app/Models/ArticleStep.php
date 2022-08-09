<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperArticleStep
 */
class ArticleStep extends Model
{
    use HasFactory;

    public $guarded = [];

    public function article() : Relation {
        return $this->belongsTo(Article::class);
    }

    public function section() : Relation {
        return $this->belongsTo(ArticleSection::class);
    }
}
