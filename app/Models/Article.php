<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperArticle
 */
class Article extends Model
{
    public $guarded = [];

    public $dates = [
        'edited_at',
        'published_at'
    ];

}
