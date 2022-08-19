<?php

namespace App\Article\Factory;

use App\Article\CrawlDetail\ArticleDetail;
use App\StructuredData\StructuredData;
use App\StructuredData\Types\SD_HowTo;
use App\StructuredData\Types\SD_BreadcrumbList;
use App\StructuredData\Types\UnknownStructuredData;

class StructuredDataFactory {

    public static function make(object|array $structuredData, ?ArticleDetail $articleDetail = null, ?StructuredData $parent = null) : StructuredData {

        $typeName = self::getSDTypeName($structuredData);

        $className = "App\StructuredData\Types\SD_" . $typeName;

        if(class_exists($className)) {
            return new $className($structuredData, $articleDetail, $parent);
        }

        return new UnknownStructuredData($structuredData);

    }

    // use this when there is no $structuredData and want to
    // create it manually and dynamically by its name and data
    public static function makeByName($typeName, array $data, ?ArticleDetail $articleDetail = null, ?StructuredData $parent = null) : StructuredData {
        $structuredData = (object) ["@type"=>$typeName];
        foreach ($data as $key => $value) {
            $structuredData->$key = $value;
        }

        return static::make($structuredData, $articleDetail, $parent);
    }

    public static function getSDTypeName(object $structuredData) {
        return $structuredData->{"@type"} ?? null;
    }
}
