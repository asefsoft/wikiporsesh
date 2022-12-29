<?php

namespace App\Article\AssetsManager;

use App\Models\AssetTracker;
use Illuminate\Database\Eloquent\Model;

class AssetStorage {

    protected bool $hasError = false;
    protected bool $updatedSuccessfully = false;
    protected string $errorMessage = '';

    public function __construct(
        protected Model| HasAssetTracker $assetStore,
        protected string $storeField)
    {

    }

    // save data to asset model
    public function update(mixed $assetData) : bool {
        $dataBeforeSave = $this->assetStore->getAttribute($this->storeField);

        $this->assetStore->setAttribute($this->storeField, $assetData);

        try {
            $this->assetStore->save();
            $this->updatedSuccessfully = true;

            // save old asset url on the tracker
            $this->assetStore->trackAsset($this->storeField, $dataBeforeSave);

            return true;
        }
        catch (\Exception $e) {
            $this->hasError = true;
            $this->errorMessage = $e->getMessage();
            logException($e, sprintf("updateAssetStore: %s > %s", $this->storeField, $assetData));
            return false;
        }
    }

    public function getTargetFieldData() {
        return $this->assetStore->getAttribute($this->storeField);
    }

    // we use this when asset already replaced with local, but we need to original asset url
    public function getTrackedAssetData() : string {
        $tracker = $this->assetStore->getTrackedAsset($this->storeField);
        return $tracker->asset_url;
    }

    public function hasError() : bool {
        return $this->hasError;
    }

    public function getErrorMessage() : string {
        return $this->errorMessage;
    }

    public function isUpdatedSuccessfully() : bool {
        return $this->updatedSuccessfully;
    }

    public function getModel() : Model {
        return $this->assetStore;
    }
}
