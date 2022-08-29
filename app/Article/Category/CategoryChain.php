<?php

namespace App\Article\Category;

class CategoryChain
{
    protected int $totalChainItems = 0;
    protected array $chainArray;
    protected bool $isValidCategoryChain = true;
    protected array $processedChainArray;

    public function __construct(array $chainArray) {
        $this->chainArray = $chainArray;
        $this->totalChainItems = count($chainArray);
        $this->checkIsValidCategoryChain();

        $this->processChainArray();
    }

    public function getRootName() : string|null {
        return $this->chainArray[0] ?? null;
    }

    public function getLastChildName() : string|null {
        return end($this->chainArray) ?? null;
    }

    private function checkIsValidCategoryChain() {
        $valid = true;

        if($this->totalChainItems == 0)
            $valid = false;

        $this->isValidCategoryChain = $valid;
    }

    public function isValidCategoryChain(): bool {
        return $this->isValidCategoryChain;
    }

    private function processChainArray() {
        $parentCategory = null;
        foreach ($this->chainArray as $categoryName){
            $this->processedChainArray[] = new CategoryItem($categoryName, $parentCategory);
            $parentCategory = $categoryName;
        }
    }


    public function makeAllExist() {
        /** @var CategoryItem $categoryItem */
        foreach ($this->processedChainArray as $categoryItem){
            $categoryItem->makeItExist();
        }
    }

    public function getLastChild() : CategoryItem {
        return end($this->processedChainArray);
    }

}
