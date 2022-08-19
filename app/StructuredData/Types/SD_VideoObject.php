<?php

namespace App\StructuredData\Types;

use App\StructuredData\StructuredData;

class SD_VideoObject extends StructuredData {

    public string $name = '';
    public string $thumbnailUrl = '';
    public string $contentUrl = '';
    public string $embedUrl = '';
    public string $uploadDate = '';
    public string $description = '';


    public function process() {
        $this->setPropertyValuesByName(['name', 'thumbnailUrl', 'contentUrl', 'embedUrl','description',
            'uploadDate'
        ]);
    }
}
