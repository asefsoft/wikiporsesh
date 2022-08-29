<?php

namespace App\Article\Category;

use App\Models\Article;
use App\Models\Category;

class CategoryManager
{
    protected Category $categoryModel;

    public function __construct(Category $categoryModel) {
        $this->categoryModel = $categoryModel;
    }

    public static function addCategories(array $categories) : array {
        $categoryChains = [];
        foreach ($categories as $category) {
            $categoryChain = new CategoryChain($category);
            if($categoryChain->isValidCategoryChain()) {
                $categoryChain->makeAllExist();
                $categoryChains[] = $categoryChain;
            }
            else
                $a=1;
        }

        return $categoryChains;
    }

    public static function addCategoriesToArticle(array $categories, Article $article) {
        $articleCategoryIDs = [];

        // first make sure all categories and sub categories are existed
        $processedCategories= static::addCategories($categories);

        // then extract last category id on each chain
        /** @var CategoryChain $categoryChain */
        foreach ($processedCategories as $categoryChain) {
            $lastChild = $categoryChain->getLastChild();

            if(!$lastChild->isExistOnDB()) {
                logError("Error on attaching category to article. category is not exist on db: " . $lastChild->getCategoryName());
                continue;
            }

            $categoryDB = $lastChild->getCategoryDB();
            $articleCategoryIDs = $categoryDB->id;
        }

        // sync article categories
        $article->categories()->sync($articleCategoryIDs);
    }
}
