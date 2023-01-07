<?php

namespace App\Models;

use App\Article\AssetsManager\AssetTrackerTrait;
use App\Article\AssetsManager\HasAssetTracker;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperArticle
 */
class Article extends Model implements HasAssetTracker
{
    use HasFactory, AssetTrackerTrait;

    public $guarded = [];

    public $dates = [
        'edited_at',
        'published_at',
        'last_crawled_at'
    ];

    public function sections() : HasMany {
        return $this->hasMany(ArticleSection::class)->orderBy('order');
    }

    public function steps() : Relation {
        return $this->hasMany(ArticleStep::class)->orderBy('order');
    }

    public function author() : Relation {
        return $this->belongsTo(User::class);
    }

    public function site() : Relation {
        return $this->belongsTo(Site::class);
    }

    public function url() : Relation {
        return $this->belongsTo(Url::class);
    }

    public function categories() : BelongsToMany {
        return $this->belongsToMany(Category::class,
            'article_categories', 'article_id', 'category_id'
        );
    }

    public function videos() : Relation {
        return $this->hasMany(ArticleVideo::class);
    }

    public function scopeTranslateDesignated(Builder $query) : Builder {
        return $query
                ->where('is_translate_designated', 1)
                ->where('auto_translated_percent', '<', 100);
    }


    public function updatePercentTranslated($skippedSteps = 0) {
        $totalSteps = count($this->steps);
        $totalTranslated = $this->steps()->autoTranslated()->count() + $skippedSteps;
        $percent = $totalSteps == 0 ? 0 : max((int)(($totalTranslated/$totalSteps) * 100), 100);
        $this->auto_translated_percent = $percent;
        $this->save();
    }

    public function getCategoryLinks($class = '', $limit = 1): string {
        $links = '';

        foreach ($this->categories as $index => $category) {

            $links .= sprintf("<a href='%s' class='%s'>%s</a>\n",
                route('category-display', $category->slug), $class, $category->name_fa
            );

            if($index +1 >= $limit)
                break;
        }

        return $links;
    }

    public function getCategoriesBreadcrumb(): array {
        $bread = [];

        foreach ($this->categories as $category) {
            $bread[] = $category->getAllParentCategories('Object');
        }

        return $bread;
    }


    public function getArticleDisplayUrl() : string {
        return route('article-display', $this->slug);
    }

    // source site which url is crawled from
    public function getArticleSourceUrl() : string {
        return $this->url->getFullUrl();
    }

    public function hasEqualSectionAndSteps() : bool {
        return $this->total_sections == $this->total_steps;
    }

    public function getStepType() : string {
        return match (strtolower($this->steps_type)) {
            default => "روش",
            "step" => "مرجله",
            "parts" => "قسمت",
            "sections" => "بخش",
        };
    }

    //for admin panel
    public function getBriefInfoOfArticle(): string {
        $limit = 80;
        $lines = [];
        if(!empty($this->tips_fa))
            $lines[] = "<strong>Tips:</strong> " . strLimit($this->tips_fa, $limit);
        if(!empty($this->warnings_fa))
            $lines[] = "<strong>Warning:</strong>: " . strLimit($this->warnings_fa, $limit);

        $info = sprintf("
<div style='direction: ltr'>
    <strong>ID:</strong> %s,
    <strong>Step Type:</strong> %s<br>\n
    <strong>Desc:</strong> %s<br>\n%s</div>
",
            $this->id,
            $this->steps_type,
            strLimit($this->description_fa, $limit),
            implode("<br>\n", $lines)
        );
        return $info;
    }

    public function getSourceSiteUrl(): ?string {
        return $this->url?->getFullUrl();
    }

}
