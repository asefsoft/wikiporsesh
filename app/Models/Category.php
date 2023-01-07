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

    public function parentsRecursive() : Relation {
        return $this->hasMany(Category::class, 'id', 'parent_category_id')
                    ->with('parentsRecursive');
    }

    public function getCategoryUrl(): string {
        return route('category-display', $this->slug);
    }

    public function getAllSubCategories(string $field = 'id'): array {
        $cat = $this->load('childrenRecursive');
        $all = self::getAllNestedRelations($cat, $field, 'childrenRecursive');
        return $all;
    }

    public function getAllParentCategories(string $field = 'id'): array {
        $cat = $this->load('parentsRecursive');
        $all = self::getAllNestedRelations($cat, $field, 'parentsRecursive');
        return array_reverse($all); // parents should be reverse
    }

    // get all child relations on a category
    public static function getAllNestedRelations(Category $category, string $field = 'id',
                             string $relationName = 'childrenRecursive', $outputFormat = 'simple') : array {

        // return a field value of whole category object?
        $fieldValue = $field == "Object" ? $category : $category->getAttribute($field);
        $all       = [$fieldValue];

        $hasRelation = $category->{$relationName} && count($category->{$relationName}) > 0;

        if($hasRelation) {
            foreach ($category->{$relationName} as $childCategory) {
                $nestedChild = static::getAllNestedRelations($childCategory, $field, $relationName, $outputFormat);
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
