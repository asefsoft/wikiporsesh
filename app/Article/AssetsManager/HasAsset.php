<?php

namespace App\Article\AssetsManager;

interface HasAsset {
    public function isAssetLocal() : bool;
    public function getAssetType() : AssetType;
}
