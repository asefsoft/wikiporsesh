<?php

namespace App\StructuredData\Types;

use App\StructuredData\StructuredData;

class SD_aggregateRating extends StructuredData {

    public int $bestRating = 0;
    public int $ratingCount = 0;
    public int $ratingValue = 0;

    public function process() {
        $this->setPropertyValuesByName(['bestRating', 'ratingCount', 'ratingValue']);
    }
}
