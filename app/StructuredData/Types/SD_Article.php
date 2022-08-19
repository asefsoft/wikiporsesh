<?php

namespace App\StructuredData\Types;

use App\StructuredData\StructuredData;

class SD_Article extends StructuredData {

    public string $name = '';
    public string $datePublished = '';
    public string $dateModified = '';
    public string $description = '';
    public ?SD_ImageObject $image = null;


    public function process() {
        $this->setPropertyValuesByName(['name', 'datePublished', 'dateModified', 'description',
            ['type' => 'SD_TYPE', 'name' => 'image']
        ]);
    }
}
