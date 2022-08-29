<?php


namespace App\Article\Url;

use App\Models\Site;
use Psr\Http\Message\UriInterface;

abstract class ArticleUrl
{
    protected string $name = '';
    protected array $validHosts = [ ];
    protected array $subUrls = [];
    protected array $ignoredPaths = [];
    protected UriInterface $url;

    public function __construct(UriInterface $url) {
        $this->url = $url;
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
}
