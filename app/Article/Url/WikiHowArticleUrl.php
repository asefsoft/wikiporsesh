<?php


namespace App\Article\Url;


use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;

class WikiHowArticleUrl extends ArticleUrl
{
    protected string $name = 'WikiHow';

    protected array $validHosts = [
        'wikihow.com',
        'www.wikihow.com',
    ];

    protected array $ignoredPaths = [
        '/Course/*',
        '/Experts/*',
        '/wikiHow:*',
        '/*-Quiz',
        '/Quizzes',
        '/Pro',
        '/Experts',
        '/Newsletters',
        '/Tech-Help-Pro',
        '/Randomizer',
        '/Special:UserLogin',
        '/Games/*',
    ];

    protected array $extraValidPaths = [
        'Special:'
    ];


    protected string $ignoredPathPattern = '';

    protected function init() {
        $grouped_patterns = [];
        foreach ($this->ignoredPaths as $pattern) {
            $grouped_patterns[] = "(" . str_replace("*",".*", $pattern) . ")";
        }

        $this->ignoredPathPattern = implode("|", $grouped_patterns);
    }


    public function isValidArticleUrl(): bool {
        return $this->isValidArticleSite() &&
               ! $this->hasColon() &&
               ! str_contains(substr($this->url->getPath(), 1), "/") &&
               ! $this->isMainUrl();
    }

    private function hasColon() : bool {
        return str_contains($this->url->getPath(), ":");
    }

    // get wiki how section from it's url
    private function getSection(): bool | string {
        if($this->hasColon()) {
            $parts = explode(":", $this->url->getPath());
            return $parts[0];
        }
        return false;
    }


    function isValidArticleSite(): bool {
        return Str::is($this->validHosts, $this->url->getHost());
    }

    function isMainUrl(): bool  {
        return $this->isValidArticleSite() &&
               in_array($this->url->getPath(),['','/','/Main-Page']);
    }

    public function isValidArticleSubUrl(): bool {
        return Str::startsWith($this->url->getPath(), $this->subUrls);
    }

    public function getName(): string {
        return $this->name;
    }

    public function extractArticleId()
    {
        if(str($this->url->getPath())->startsWith("/"))
            return str($this->url->getPath())->substr(1);


        return $this->url->getPath();
    }

    function getCleanedUrl() : UriInterface {
        if($this->isValidArticleUrl()) {
            $id = $this->extractArticleId();
            $url = sprintf("https://www.wikihow.com/%s", $id);
            return new Uri($url);
        }

        if($this->url->getQuery()!='')
            $a=1;

        return $this->url->withQuery('');
    }

    function getUrlUniqueID(): UriInterface {
        return $this->url;
    }

    function isIgnoredPath() : bool {
        $path = $this->url->getPath();
        $founds = preg_match("~" . $this->ignoredPathPattern . "~", $path, $matches);

        return $founds > 0;
    }

    function isCategoryUrl() : bool {
        return str_starts_with($this->url->getPath(), "/Category:");

    }

    function getSlug() : string {
        return str_replace('/', '', $this->url->getPath());
    }

    function isExtraValidPath(): bool {
        foreach ($this->extraValidPaths as $path) {
            if(str_contains($this->url->getPath(), $path))
                return true;
        }

        return false;
    }
}
