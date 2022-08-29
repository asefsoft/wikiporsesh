<?php

namespace App\Article\Category;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryItem
{
    protected string $categoryName; // category slug
    protected bool $hasChild = false;
    protected bool $hasParent = false;
    protected bool $hasParentName = false;
    protected bool $isRoot = false;
    protected bool $isExistOnDB = false;
    protected Category|null $categoryDB;
    protected Category|null $parentCategoryDB;
    protected string|null $parentName;

    public function __construct(string $categoryName, string $parentName = null) {

        $this->categoryName = $categoryName;
        $this->parentName = $parentName;
        $this->hasParentName = !empty($parentName);

        $this->fetchCategoryData();

    }

    private function fetchCategoryData() {
        $this->categoryDB = Category::where('slug', Str::slug($this->categoryName))->first();

        $this->isExistOnDB = !empty($this->categoryDB);

        $this->isRoot = ! $this->hasParentName;

        if($this->isExistOnDB) {
            $this->isRoot = empty($this->categoryDB->parent_category_id);
            $this->hasParent = ! $this->isRoot;
            $this->hasChild = $this->categoryDB->total_sub_categories > 0;// todo: its not ok now
        }

    }

    // get parent cat by its name
    public function getParentCategoryDB() : Category|null {
        $parent = null;
        if($this->hasParentName) {
            $parent = Category::where('slug', Str::slug($this->parentName))->first();

            if(empty($parent))
                throw new \Exception("Parent category is not exist on DB! category: " . $this->parentName);
        }

        return $parent;
    }

    // if category is not exist then create it
    public function makeItExist() : bool {
        if($this->isExistOnDB)
            return true;

        $this->parentCategoryDB = $this->getParentCategoryDB();

        try {
            Category::create(
                [
                    'parent_category_id' => $this->parentCategoryDB?->id ?? null,
                    'name_fa' => $this->categoryName,
                    'name_en' => $this->categoryName,
                    'slug' => Str::slug($this->categoryName),
                ]
            );

            // re-get data from db and update class properties
            $this->fetchCategoryData();
            return true;

        } catch (\Exception $e) {
            logException($e, "Category:makeItExist");
            return false;
        }

    }

    public function getCategoryName(): string {
        return $this->categoryName;
    }

    public function hasChild(): bool {
        return $this->hasChild;
    }

    public function hasParent(): bool {
        return $this->hasParent;
    }

    public function isRoot(): bool {
        return $this->isRoot;
    }

    public function isExistOnDB(): bool {
        return $this->isExistOnDB;
    }

    public function getCategoryDB(): Category {
        return $this->categoryDB;
    }



}
