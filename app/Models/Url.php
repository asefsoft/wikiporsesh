<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperUrl
 */
class Url extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $dates = [
        'date'
    ];

    // it maybe better to be has-one relation
    public function articles() : Relation {
        return $this->hasMany(Article::class);
    }

    public function content() : Relation {
        return $this->hasOne(Content::class);
    }
}
