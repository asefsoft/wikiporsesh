<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperContent
 */
class Content extends Model
{
    public $timestamps = false;

    public $guarded = [];

    protected $dates = [
        'date',
    ];

}
