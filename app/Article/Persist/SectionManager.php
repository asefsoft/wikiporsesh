<?php

namespace App\Article\Persist;

use App\Models\Article;
use App\Models\ArticleSection;
use App\StructuredData\Types\SD_HowToSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SectionManager {

    protected Article $article;
    protected ?Collection $articleSectionsDB;
    protected array $givenArticleSections;
    protected bool $alreadyHasSections;
    protected bool $isPersisted = false;
    protected string $errorMessage = '';

    public function __construct(Article $article, array $givenArticleSections) {
        $this->article              = $article;
        $this->givenArticleSections = $givenArticleSections;
        //get current sections from db
        $this->articleSectionsDB = $article->sections;
        $this->alreadyHasSections = count($this->articleSectionsDB ?? []) > 0;

    }

    public function persist() : bool {

        if(! $this->isDBAndGivenSectionsAreCompatible()){
            //todo: what should we do?
            $this->errorMessage = "current existed article on db is not compatible with new crawled one, sections and steps count mismatched";
            return false;
        }

        try {
            DB::beginTransaction();

            $sectionOrder = 1;
            /** @var SD_HowToSection $section */
            foreach ($this->givenArticleSections as $section) {
                $this->updateOrCreateSection($section, $sectionOrder);
                $sectionOrder ++;
            }

            DB::commit();
            $this->isPersisted = true;
        }
        catch (\Exception $e) {
            DB::rollBack();
            logException($e, 'SectionManager:persist');
            $this->errorMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    // are existed section on DB and given section data have same sections count and same steps count?
    private function isDBAndGivenSectionsAreCompatible() : bool {

        if($this->alreadyHasSections) {
            $dbSections = $this->article->sections;
            $givenSections = $this->givenArticleSections;

            // is sections count compatible
            if(count($dbSections) != count($givenSections))
                return false;

            //check steps count
            $index = 0;
            foreach ($dbSections as $dbSection){
                $dbCount = count($dbSection->steps);
                $givenCount   = count($givenSections[$index]?->steps ?? []);

                // if db steps counts are zero then we assume section is incomplete, and
                // we should complete section, so we won't return false
                if($dbCount != 0 && $dbCount != $givenCount)
                    return false;

                $index++;
            }
        }

        return true;
    }


    private function updateOrCreateSection(SD_HowToSection $section, int $sectionOrder) : void {
        $sectionData = [
            'article_id' => $this->article->id, 'title_en' => $section->name, 'order' => $sectionOrder,
        ];

        if ($this->alreadyHasSections) {
            // update
            $this->articleSectionsDB[$sectionOrder - 1]->update($sectionData);
        }
        else {
            //create new section
            // dont add fa in update but add it on new creation
            $sectionData['title_fa'] = $section->name;
            $this->articleSectionsDB->add(ArticleSection::create($sectionData));
        }

        // persist steps
        $sectionDB = $this->articleSectionsDB[$sectionOrder - 1];
        $stepManager = new StepManager($sectionDB, $section->steps);
        $stepManager->persist();
    }

    public function isPersisted() : bool {
        return $this->isPersisted;
    }

    public function getErrorMessage() : string {
        return $this->errorMessage;
    }


}
