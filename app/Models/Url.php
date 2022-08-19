<?php

namespace App\Models;

use App\Article\Factory\ArticleUrlFactory;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;

/**
 * @mixin IdeHelperUrl
 */
class Url extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $dates = [
        'date'
    ];

    // it maybe better to be has-one relation
    public function articles() : Relation {
        return $this->hasMany(Article::class);
    }

    public function content() : Relation {
        return $this->hasOne(Content::class);
    }

    public static function getUrlHash($url, $extraParam=''): string {
        $url = ArticleUrlFactory::getCleanedUrl($url);
        $url = parse_url($url);

        $str_id = sprintf("%s%s%s",
            $url['host'],
            $url['path'] ?? '',
            $extraParam);

        return hash('sha256', $str_id);
    }

    public static function isUrlCrawled($url, $extraParam='', $shouldHasArticle = false, &$article = null): bool {
        $hash = static::getUrlHash($url, $extraParam);
        $urlDb = Url::where('hash', $hash)->first();

        // no url found
        if(!$urlDb instanceof Url)
            return false;

        // if we should check existences of video (url belong to video)
        if($shouldHasArticle) {
            $article = $urlDb->articles?->first();
            return $article instanceof Article;
        }

        return true;
    }

    public static function saveNewUrl($url, $content = '', $extraParam = '') {
        if(! $url instanceof UriInterface)
            $url = new Uri($url);

        $hash = Url::getUrlHash($url, $extraParam);

        $curUrl = Url::firstOrNew(['hash'=>$hash]);
        $curUrl->hash = $hash;
        $curUrl->hostname = Str::limit($url->getHost(),50,'');
        $curUrl->path = Str::limit($url->getPath(),800,'');
        $curUrl->query = Str::limit($url->getQuery(),200,'');
        $curUrl->date = now();
        $urlWasExist = $curUrl->exists;

        if (! $urlWasExist) {
            $curUrl->total_crawled = 0;
        }
        else
            $curUrl->total_crawled++; // new crawl then update counter

        // save html content
        if($content != '') {
            if (! $urlWasExist)
                $curUrl->save();

            $curUrl->content()->updateOrCreate([], [
                'date' => now(),
                'content' => $content
            ]);
        }

        $curUrl->save();

        return $curUrl;
    }

    public static function getByUrl($url, $extraParam = '') {
        return static::where('hash', static::getUrlHash($url, $extraParam))->first();
    }

    public function getFullUrl($secure = true) : string {
        $query = !empty($this->query) ? "?" . $this->query : "";

        //if(!Str::endsWith($this->path, '/'))
        //    $this->path = $this->path . "/";

        // remove multiple slashes
        $this->path = preg_replace('~/+~', '/', $this->path);

        return sprintf("http%s://%s%s%s", $secure ? "s" : "", $this->hostname, $this->path, $query);
    }
}
