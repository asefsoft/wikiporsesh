<?php

namespace App\StructuredData\Types;

use App\StructuredData\StructuredData;

class SD_ImageObject extends StructuredData {
    public string $url = '';
    public int $width = 0;
    public int $height = 0;

    public function process() {
        $this->setPropertyValuesByName(['url', 'width', 'height']);
    }
}
