<?php

namespace App\Article\AssetsManager;

use App\Article\Factory\ArticleUrlFactory;
use App\Article\Url\ArticleUrl;
use App\Models\Site;

class Asset implements HasAsset {

    protected ArticleUrl $assetUrlValidator;

    public function __construct(
        protected AssetType $assetType,
        protected Site $site,
        protected string $name, // step image, article image ...
        protected AssetStorage $assetStore // using to get/save asset data to model
    ) {
        $this->assetUrlValidator = ArticleUrlFactory::makeBySite($this->assetStore->getTargetFieldData(), $this->site);
    }


    public function isAssetLocal() : bool {
        // if asset url is valid article site then obviously it is not local,
        // and it is hosted on that site
        if($this->assetUrlValidator->isValidArticleSite())
            return false;
        // if this is a relative asset url, so it's not local,
        // and probably it is hosted on target article site
        elseif($this->assetUrlValidator->isRelativePath())
            return false;

        // so when we are here probably asset is hosted in our servers!
        return $this->isAssetActuallyExistsOnLocal();

    }

    // check existence of asset file on the disk
    public function isAssetActuallyExistsOnLocal() : bool {

        if($this->isAssetUrlMatchesOurLocalFolderStructure($assetPath))
            return file_exists(public_path($assetPath)); // check if file really exist

        return false;
    }

    protected function isAssetUrlMatchesOurLocalFolderStructure(?string &$assetPath = '') : bool {
        $assetUrlOnDb = $this->assetStore->getTargetFieldData();

        // extract store path from url
        preg_match("#.*/(static/(videos|images)/(.*))#", $assetUrlOnDb, $matches);

        $isMatches = ! empty($matches[1]);

        if ($isMatches)
            $assetPath = $matches[1];

        return $isMatches;
    }

    // the directory where asset will be store
    public function getStoreDirectory() : string {
        // like: '/var/www/.../static/images/2022-12/'
        return public_path($this->getBaseStorePath());
        //return sprintf("%s/%s/%s/", public_path('static') , $type, date("Y-m"));
    }

    // build url to use it for saving on db
    public function buildLiveUrl($filename) : string {
        return asset(sprintf("%s%s", $this->getBaseStorePath(), $filename));
    }

    protected function getBaseStorePath() : string {
        $type = $this->getAssetType() == AssetType::Image ? 'images' : 'videos';
        return sprintf("static/%s/%s/", $type, date("Y-m"));
    }

    // get asset url on original site
    public function getOriginalAssetUrl() : string {

        // if asset supposed to be local, but it is not then we use tracked asset url
        // which contains original asset url
        if($this->isAssetUrlMatchesOurLocalFolderStructure()) {
            // then we use asset tracker to find original url
            return $this->assetStore->getTrackedAssetData();
        }

        // if asset url is relative then we need to add its host to it
        if($this->assetUrlValidator->isRelativePath())
            return (string)$this->assetUrlValidator->convertRelativeToFullUrl($this->assetType);

        // or just use this
        return (string)$this->assetUrlValidator->getUrl();
    }

    // making a unique id to use it for file name of asset when saving it on disk

    public function getStoreFilename() : string {
        $order = "";
        $model = $this->assetStore->getModel();

        // order number
        if($model instanceof HasOrder) {
            $order = "-" . $model->getOrderString();
        }

        //parent id
        if(($parentId = $model->getAttribute('article_id')) != '')
            $parentId = $parentId . "-";

        return sprintf("%s%s-%s%s", $parentId, $model->id, $this->name, $order);
    }

    public function getAssetType() : AssetType {
        return $this->assetType;
    }

    public function getAssetStore() : AssetStorage {
        return $this->assetStore;
    }

}
