<?php

namespace App\Article\Category;

use App\Models\Category;

class CategoryManager
{
    protected Category $categoryModel;

    public function __construct(Category $categoryModel) {
        $this->categoryModel = $categoryModel;
    }

    public static function addCategories(array $categories) {
        foreach ($categories as $category) {
            $categoryChain = new CategoryChain($category);
            if($categoryChain->isValidCategoryChain()) {
                $categoryChain->makeAllExist();
            }
            else
                $a=1;
        }
    }
}
