<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperSite
 */
class Site extends Model
{
    public $guarded = [];

    public function articles() : Relation {
        return $this->hasMany(Article::class, 'site_id');
    }
}
