<?php

namespace App\Article\AssetsManager;

use App\Models\AssetTracker;
use Illuminate\Database\Eloquent\Relations\Relation;

trait AssetTrackerTrait {

    public function assetTracker(string $fieldName = 'image_url')  : Relation{
        return $this->morphOne(AssetTracker::class, 'trackable')->
        where('field_name', $fieldName);
    }

    public function getTrackedAsset(string $fieldName) : ?AssetTracker {
        return $this->assetTracker($fieldName)->first();
    }

    // save original asset url in the asset tracker table,
    // so we use it for re-download asset etc.
    public function trackAsset(string $fieldName, string $assetUrl) : AssetTracker {

        $assetTracker = $this->getTrackedAsset($fieldName);

        if(empty($assetTracker)) {
            $assetTracker = $this->assetTracker($fieldName)->create([
                'field_name' => $fieldName,
                'asset_url' => $assetUrl,
            ]);
        }
        else {
            $a=1;
        }

        return $assetTracker;
    }
}
