<?php

namespace App\Models;

use App\Article\AssetsManager\AssetTrackerTrait;
use App\Article\AssetsManager\HasAssetTracker;
use App\Article\AssetsManager\HasOrder;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperArticleStep
 */
class ArticleStep extends Model implements HasOrder, HasAssetTracker
{
    use HasFactory, AssetTrackerTrait;

    public $guarded = [];
    protected $dates = ['auto_translated_at'];

    public function article() : Relation {
        return $this->belongsTo(Article::class);
    }

    public function section() : Relation {
        return $this->belongsTo(ArticleSection::class);
    }

    public function hasImage() : bool {
        return !empty($this->image_url);
    }

    public function hasVideo() : bool {
        return !empty($this->video_url);
    }

    public function scopeNotAutoTranslated(Builder $query) : Builder {
        return $query->whereNull('auto_translated_at');
    }

    public function scopeAutoTranslated(Builder $query) : Builder {
        return $query->whereNotNull('auto_translated_at');
    }

    public function storeTranslatedText($text, $isAutoTranslate) {
        $this->content_fa = $text;

        if($isAutoTranslate)
            $this->setIsAutoTranslated();

        $this->save();
    }

    public function setIsAutoTranslated() {
        $this->auto_translated_at = now();
    }


    // is already translated or edited
    public function isFarsiAndEnglishContentSame() : bool {
        return $this->content_fa == $this->content_en;
    }

    //todo: remove wiki how
    public function getVideoUrl() : string {
        if(str_starts_with($this->video_url, "/"))
            return "https://www.wikihow.com/video" . $this->video_url;
        else
            return $this->video_url;
    }

    public function getOrderString() : string {
        $sectionOrder = $this->section->order ? $this->section->order . "-" : "";
        return sprintf("%s%s", $sectionOrder, $this->order);
    }

}
