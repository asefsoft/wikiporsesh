<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperAssetTracker
 */
class AssetTracker extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function trackable() : Relation {
        return $this->morphTo();
    }
}
