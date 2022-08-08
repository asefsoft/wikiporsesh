<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
