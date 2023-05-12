<?php

namespace App\Article\AssetsManager;

use App\Models\Article;
use Illuminate\Support\Collection;

class AssetsManager {

    protected Article $article;
    protected bool $isAllAssetsLocal = false;
    protected bool $isStepsImagesLocal = false;
    protected bool $isStepsVideosLocal = false;
    protected bool $isArticleImageLocal = false;

    protected int $totalAssets = 0;
    protected int $totalStepsImageAssets = 0;
    protected int $totalStepsVideoAssets = 0;

    protected int $totalLocalAssets = 0;
    protected int $totalLocalStepsImageAssets = 0;
    protected int $totalLocalStepsVideoAssets = 0;

    protected Collection $stepsImageAssets;
    protected Collection $stepsVideoAssets;
    protected Collection $noneLocalAssets;

    public function __construct(Article $article) {
        $this->article = $article;
        $this->stepsImageAssets = collect();
        $this->stepsVideoAssets = collect();
        $this->noneLocalAssets = collect();
        $this->processAssets();
        $this->checkWhetherAssetsAreLocal();
    }

    private function processAssets() {

        // article image
        $site = $this->article->site;
        if(!empty($this->article->image_url)) {
            $this->totalAssets++;
            $asset = new Asset( AssetType::Image, $site, 'article-image' , new AssetStorage($this->article, 'image_url'));

            if($this->isArticleImageLocal = $asset->isAssetLocal())
                $this->totalLocalAssets++;
            else
                $this->noneLocalAssets->add($asset);
        }
        else
            $this->isArticleImageLocal = true; // no article image!

        // all steps
        foreach ($this->article->steps as $step) {

            // step image
            if($step->hasImage()) {
                $this->stepsImageAssets->add(
                    new Asset( AssetType::Image, $site, 'step-image', new AssetStorage($step, 'image_url'))
                );
            }

            // step video
            if($step->hasVideo()) {
                $this->stepsVideoAssets->add(
                    new Asset( AssetType::Video, $site, 'article-video', new AssetStorage($step, 'video_url'))
                );
            }
        }

        // stats
        $this->totalStepsImageAssets = count($this->stepsImageAssets);
        $this->totalStepsVideoAssets = count($this->stepsVideoAssets);
        $this->totalAssets += $this->totalStepsImageAssets + $this->totalStepsVideoAssets;

    }

    // are assets local
    private function checkWhetherAssetsAreLocal() {

        foreach ($this->stepsImageAssets as $stepImage) {
            // if is local then add +1
            $addition = $stepImage->isAssetLocal() == true ? 1 : 0;
            $this->totalLocalAssets += $addition;
            $this->totalLocalStepsImageAssets += $addition;

            if($addition == 0)
                $this->noneLocalAssets->add($stepImage);
        }

        foreach ($this->stepsVideoAssets as $stepVideo) {
            // if is local then add +1
            $addition = $stepVideo->isAssetLocal() == true ? 1 : 0;
            $this->totalLocalAssets += $addition;
            $this->totalLocalStepsVideoAssets += $addition;

            if($addition == 0)
                $this->noneLocalAssets->add($stepVideo);
        }

        // if there is no none-local assets then all assets are local
        $this->isAllAssetsLocal = count($this->noneLocalAssets) == 0;

        $this->isStepsImagesLocal = $this->totalLocalStepsImageAssets == $this->totalStepsImageAssets;
        $this->isStepsVideosLocal = $this->totalLocalStepsVideoAssets == $this->totalStepsVideoAssets;
    }

    public function isAllAssetsLocal() : bool {
        return $this->isAllAssetsLocal;
    }

    public function makeAllAssetsLocal() {

        $allBecameLocal = true;

        /** @var Asset $noneLocalAsset */
        foreach ($this->noneLocalAssets as $noneLocalAsset) {

            // like: 'static/images/2022-12/'
            $storePath = $noneLocalAsset->getStoreDirectory();
            // make sure storage dir is exists
            AssetDownloader::createFolder($storePath);
            $assetUrl = $noneLocalAsset->getOriginalAssetUrl(); // url of asset

            // asset original url not found!!
            if($assetUrl == "")
                continue;

            $extension = pathinfo($assetUrl, PATHINFO_EXTENSION);
            $finalStorePath = $storePath . $noneLocalAsset->getStoreFilename() . '.' . $extension;

            $downloader = new AssetDownloader($assetUrl, $finalStorePath);

            // download images and then convert to webp
            if($noneLocalAsset->getAssetType() == AssetType::Image) {
                $done = $downloader->doDownloadAndConvert();
            }
            else {
                // download videos
                $done = $downloader->doDownload();
            }

            if($done) {
                // update asset url to local path on DB
                $finalUrl = $noneLocalAsset->buildLiveUrl(pathinfo($downloader->getStorePath(), PATHINFO_BASENAME));
                $done = $noneLocalAsset->getAssetStore()->update($finalUrl);

                if($done)
                    $this->totalLocalAssets++;
                else
                    $allBecameLocal = false;
            }
            else {
                $allBecameLocal = false;
            }

        }

        return $allBecameLocal;
    }

    public function getAssetStatusText(): string {
        return sprintf("%s%% (%s/%s) از دارایی ها محلی هستند.", $this->getLocalAssetsPercentage(), $this->totalLocalAssets, $this->totalAssets);
    }

    public function getLocalAssetsPercentage(): int {
        if($this->totalAssets == 0)
            return 0;

        return (int)(($this->totalLocalAssets / $this->totalAssets) * 100);
    }

}
