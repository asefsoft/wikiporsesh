<?php

namespace App\Models;

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

    public static function getUrlHash($url, $extra_param=''): string {
        $url = VideoUrlFactory::get_cleaned_url($url);
        $url = parse_url($url);

        $str_id = sprintf("%s%s%s",
            $url['host'],
            $url['path'] ?? '',
            $extra_param);

        return hash('sha256', $str_id);
    }

    public static function isUrlCrawled($url, $extra_param='', $should_has_video = false, &$video = null): bool {
        $hash = static::getUrlHash($url, $extra_param);
        $url_db = \App\Url::where('always_crawl', '=', 0)->where('hash', $hash)->first();

        // no url found
        if(!$url_db instanceof Url)
            return false;

        // if we should check existences of video (url belong to video)
        if($should_has_video) {
            $video = $url_db->video;
            return $video instanceof VideoInfo;
        }

        return true;
    }

    public static function saveNewUrl($url, $content = '', $extra_param='') {
        if(! $url instanceof UriInterface)
            $url = new Uri($url);

        $hash = Url::getUrlHash($url, $extra_param);

        $cr_url = Url::firstOrNew(['hash'=>$hash]);
        $cr_url->hash = $hash;
        $cr_url->hostname = Str::limit($url->getHost(),50,'');
        $cr_url->path = Str::limit($url->getPath(),800,'');
        $cr_url->query = Str::limit($url->getQuery(),400,'');
        $cr_url->date = now();
        $cr_url->useful = true;
        $cr_url->is_crawled = true;
        $url_was_exist = $cr_url->exists;
        if (!$url_was_exist) {
            $cr_url->total_crawled = 0;
            $cr_url->always_crawl = false;
        }
        else
            $cr_url->total_crawled++; // new crawl then update counter

        // save html content
        if($content!='') {
            if (!$url_was_exist)
                $cr_url->save();

            $cr_url->content()->updateOrCreate([], [
                'date' => now(),
                'content' => $content
            ]);
        }

        $cr_url->save();

        return $cr_url;
    }

    public static function getByUrl($url, $extra_param='') {
        return static::where('hash', static::getUrlHash($url, $extra_param))->first();
    }

    public function getFullUrl($secure = true)
    {
        $query = !empty($this->query) ? "?" . $this->query : "";

        if(!Str::endsWith($this->path, '/'))
            $this->path = $this->path . "/";

        // remove multiple slashes
        $this->path = preg_replace('~/+~', '/', $this->path);

        return sprintf("http%s://%s%s%s", $secure ? "s" : "", $this->hostname, $this->path, $query);
    }
}
