<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperArticle
 */
class Article extends Model
{
    use HasFactory;

    public $guarded = [];

    public $dates = [
        'edited_at',
        'published_at',
        'last_crawled_at'
    ];

    public function sections() : HasMany {
        return $this->hasMany(ArticleSection::class)->orderBy('order');
    }

    public function steps() : Relation {
        return $this->hasMany(ArticleStep::class)->orderBy('order');
    }

    public function author() : Relation {
        return $this->belongsTo(User::class);
    }

    public function site() : Relation {
        return $this->belongsTo(Site::class);
    }

    public function url() : Relation {
        return $this->belongsTo(Url::class);
    }

    public function categories() : BelongsToMany {
        return $this->belongsToMany(Category::class,
            'article_categories', 'article_id', 'category_id'
        );
    }

    public function videos() : Relation {
        return $this->hasMany(ArticleVideo::class);
    }



}
