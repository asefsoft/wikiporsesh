<?php


namespace App\Article;


use App\Tools\Tools;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait FilterByQueryString {

    public static function ApplyFilters(Builder &$query) {
        $filterNames = ['is_featured', 'is_translate_designated', 'is_popular', 'is_auto_translated', 'is_edited', 'is_published'];
        foreach ($filterNames as $filter) {
            if(request()->has($filter) && request($filter) == 'true') {
                $query = match ($filter) {
                    'is_featured' => $query->where('is_featured', 1),
                    'is_translate_designated' => $query->where('is_translate_designated', 1),
                    'is_popular' => $query->where('source_views', '>', 300_000),
                    'is_auto_translated' => $query->where('auto_translated_percent', '>', 80),
                    'is_edited' => $query->whereNotNull('edited_at'),
                    'is_published' => $query->whereNotNull('published_at'),
                };
            }
        }
        return $query;
    }

}
