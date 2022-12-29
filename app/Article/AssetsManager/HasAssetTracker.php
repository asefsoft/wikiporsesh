<?php

namespace App\Article\AssetsManager;

use App\Models\AssetTracker;
use Illuminate\Database\Eloquent\Relations\Relation;

interface HasAssetTracker {
    public function assetTracker(string $fieldName = 'image_url') : Relation;
    public function getTrackedAsset(string $fieldName) : ?AssetTracker;
    public function trackAsset(string $fieldName, string $assetUrl) : AssetTracker;
}
