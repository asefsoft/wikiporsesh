<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperArticleSection
 */
class ArticleSection extends Model
{
    use HasFactory;

    public $guarded = [];

    public function steps() : Relation {
        return $this->hasMany(ArticleStep::class, 'section_id')->orderBy('order');
    }

    public function article() : Relation {
        return $this->belongsTo(Article::class);
    }

    public function isSingleStep() : bool {
        return count($this->steps) == 1;
    }
}
