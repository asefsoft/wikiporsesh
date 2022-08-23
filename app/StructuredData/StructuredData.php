<?php

namespace App\StructuredData;

use App\Article\CrawlDetail\ArticleDetail;
use App\Article\Factory\StructuredDataFactory;
use App\StructuredData\Types\UnknownStructuredData;

abstract class StructuredData {
    protected bool $isSupportedSD = false;
    protected string $typeName = '';
    protected ?ArticleDetail $articleDetail;
    protected ?StructuredData $parent;
    protected int $totalOverallSteps = 0;
    protected int $totalCurrentSectionSteps = 0;

    public object $structuredData;

    public function __construct(object $structuredData, ?ArticleDetail $articleDetail = null, ?StructuredData $parent = null) {
        $this->structuredData = $structuredData;
        $this->typeName = StructuredDataFactory::getSDTypeName($structuredData);
        $this->isSupportedSD = ! $this instanceof UnknownStructuredData;
        $this->articleDetail = $articleDetail;
        $this->parent = $parent;

        $this->process();

    }

    abstract public function process();


    // automatically fill values of structured data, base on its name and type
    protected function setPropertyValuesByName(array $propertyNames) {

        foreach ($propertyNames as $propertyName) {

            // if it is Structured Data type then we try to make it
            if(is_array($propertyName) && $propertyName['type'] == 'SD_TYPE') {
                $propertyName        = $propertyName['name'];
                $type      = $this->structuredData->$propertyName ?? null;
                if(!empty($type))
                    $this->$propertyName = StructuredDataFactory::make($type, $this->articleDetail, $this);
                continue;
            }

            // else, it is simple data type
            try {
                $propertyType = gettype($this->$propertyName);
            } catch (\Exception $e) {
                $a=1;
            }

            $value = null;

            try {
                switch ($propertyType) {
                    case 'string':
                    case 'integer':
                    case 'double':
                    case 'array':
                        $value = $this->structuredData->$propertyName ?? null;
                }


                if (! empty($value)) {
                    $valueType = gettype($value);
                    //if we expect value be string, but we get array then we use first array item
                    if ($valueType == 'array' && $propertyType != $valueType) {
                        $this->$propertyName = $value[0];
                    }
                    else
                        $this->$propertyName = $value;
                }

            } catch (\Exception $e) {
                $a=1;
            }
        }
    }

    public function getArticleDetail() : ArticleDetail {
        return $this->articleDetail;
    }

    protected function addStepNumber() {

        // if it is root element
        // update 'overall' just for root element
        if(empty($this->parent))
            $this->totalOverallSteps++;
        else {
            // and update 'current section' just for middle elements
            $this->totalCurrentSectionSteps ++;
            $this->parent->addStepNumber();
        }
    }

    // recursively go up tp reach root element and return its total overall steps
    protected function getRootElementOverallStepsCount() : int {
        return $this->parent?->getRootElementOverallStepsCount() ?? $this->totalOverallSteps;
    }
}
