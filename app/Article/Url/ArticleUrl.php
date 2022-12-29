<?php


namespace App\Article\Url;

use App\Article\AssetsManager\AssetType;
use App\Models\Site;
use Psr\Http\Message\UriInterface;

abstract class ArticleUrl
{
    protected string $name = '';

    // using these variables for converting relative asset urls to full urls
    // these 3 most be defined in target class
    protected ?string $imageAssetPrefix;
    protected ?string $videoAssetPrefix;
    protected ?string $assetHost;

    protected array $validHosts = [ ];
    protected array $subUrls = [];
    protected array $ignoredPaths = [];
    protected array $extraValidPaths = [];
    protected UriInterface $url;

    public function __construct(UriInterface $url) {
        $this->url = $url;
        $this->init();
    }

    protected abstract function init();

    // is url belongs to a page that contain video stream?
    abstract function isValidArticleUrl() : bool;

    // is a url of video site? like {aparat, youtube ...}
    abstract function isValidArticleSite() : bool;

    abstract function isMainUrl() : bool;

    abstract function isValidArticleSubUrl() : bool;

    abstract function isIgnoredPath() : bool;

    abstract function isCategoryUrl() : bool;

    abstract function isExtraValidPath() : bool;

    // remove extra chars of url which does not affect in uniqueness of url
    abstract function getCleanedUrl() : UriInterface;

    abstract function getUrlUniqueID() : UriInterface; // get unique id of article

    abstract function getSlug() : string;

    public function stripParamFromUrl( $url ) : string {
        return strtok($url, '?');
    }

    public function getName() : string {
        return $this->name;
    }

    // site id of our sites table
    public function getSiteId() : int {
        //todo: cache it
        return Site::whereName($this->name)->first()->id;
    }

    public function getUrl() : UriInterface {
        return $this->url;
    }

    public function isRelativePath() : bool {
        return $this->url->getHost() == '';
    }

    // sometimes we have urls without host, in this function we correct them
    public function convertRelativeToFullUrl(?AssetType $assetType = null) : UriInterface {

        if($this->isRelativePath()) {
            $path = $this->getAssetPathWithPrefix($assetType);

            return $this->url->withHost("www.wikihow.com")
                             ->withPath($path)
                             ->withScheme("https");
        }

        // url is not relative!
        return $this->url;
    }

    // if there should be a prefix (like 'video' or 'image') for asset url,
    // then we add it if it's not there
    protected function getAssetPathWithPrefix(?AssetType $assetType) : string {
        $path = $this->url->getPath();

        switch ($assetType) {
            case AssetType::Image:
                $prefix = "/" . $this->imageAssetPrefix . "/";
                break;
            case AssetType::Video:
                $prefix = "/" . $this->videoAssetPrefix . "/";
                break;
        }

        // add prefix if it's not exist
        if(!empty($prefix))
            $path = str_starts_with($path, $prefix) ? $path : $prefix . ltrim($path, '/');

        return $path;
    }



}
