<?php

namespace App\Translate;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Collection;

class AllCategoriesAutoTranslator {

    use TranslatableComponent;

    protected Collection $categories;
    private int $failedTranslate = 0;
    private int $delayBetweenTranslate = 30; // in sec
    private int $categoriesToBeTranslate = 0;
    private int $categoriesTranslated = 0;
    private int $stepsSkipped = 0;

    private bool $isDebugging = false;

    public function __construct() {
        $this->delayBetweenTranslate = ifProduction(30, 25);
    }

    public function start() {
        $this->translateCategories();
    }

    private function shouldTranslateCategory(Category $category, &$textToBeTranslate) : bool {
        $enText = $category->getAttribute("name_en");
        $faText = $category->getAttribute("name_fa");
        $textToBeTranslate = $enText;
        return !empty($enText)  && $enText == $faText;
    }

    private function translateCategories() {

        $this->categories = Category::all();
        $this->categoriesToBeTranslate = count($this->categories);

        // do translate all cats
        foreach ($this->categories as $category) {

            if($this->shouldTranslateCategory($category, $text)) {
                $this->doTranslateCategory($text, $category);
            }

            if(! $this->shouldContinue())
                break;
        }

    }

    private function shouldContinue() : bool {
        return $this->failedTranslate <= 3;
    }

    public function wasSuccessful() : bool {
        return $this->failedTranslate == 0;
    }

    public function getStatusText() : string {

        if($this->categoriesToBeTranslate == 0)
            return "There was nothing to be translated.";

        return sprintf("Translated %s/%s categories.",
            $this->categoriesTranslated, $this->categoriesToBeTranslate
        );
    }


    private function doTranslateCategory($text, Category $category) {
        if ($this->translateText($text, $err)) {

            $category->name_fa = $text;
            $category->save();

            $this->categoriesTranslated++;

            // delay
            sleep($this->getSleepTime());
        }
        else {
            $this->failedTranslate ++;
            logError(sprintf("Could not translate category id %s `%s` %s",
                $category->id, $category->name_en, $err));
        }
    }

    private function getSleepTime() : int{

        if(!empty($this->isDebugging))
            return 0;

        return $this->delayBetweenTranslate + rand(1, 7);
    }

}
