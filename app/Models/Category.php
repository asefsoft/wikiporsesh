<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @mixin IdeHelperCategory
 */
class Category extends Model
{
    use HasFactory;

    public $guarded = [];

    // many-to-many relationship
    public function articles() : Relation {
        return $this->belongsToMany(Article::class,
            'article_categories',  'category_id', 'article_id'
        );
    }

    public function childrenRecursive() : Relation {
        return $this->hasMany(Category::class, 'parent_category_id')
                    ->with('childrenRecursive');
    }

    public function getAllSubCategories(string $field = 'id') {
        $cat = $this->with('childrenRecursive')->first();//->pluck($field)->unique();
        $all = self::getAllNestedRelations($cat, $field);
        return $all;
    }

    // get all child relations on a category
    public static function getAllNestedRelations(Category $category, string $field = 'id', $outputFormat = 'simple') : array {
        $fieldValue = $category->getAttribute($field);
        $all       = [$fieldValue];

        $hasRelation = $category->childrenRecursive && count($category->childrenRecursive) > 0;

        if($hasRelation) {
            foreach ($category->childrenRecursive as $childCategory) {
                $nestedChild = static::getAllNestedRelations($childCategory, $field, $outputFormat);
                if(count($nestedChild))
                    $all = array_merge($all,$nestedChild);
            }
        }

        // simple array output
        if ($outputFormat == 'simple')
            return $all;

        // nested array output
        if(count($all))
            return [$fieldValue => $all];
        else
            return [$fieldValue => $fieldValue];
    }

    public static function getAllCategoriesAndSubCategories(array $categoriesID, string $field = 'id') : Collection {
        return Category::whereIn('id', $categoriesID)
                ->with('childrenRecursive') //recursive
                ->get()
                ->map(function (Category $category) use ($field) { //extract the only field we need
                    return self::getAllNestedRelations($category, $field);
                })
                ->flatten()
                ->unique();

    }


}
