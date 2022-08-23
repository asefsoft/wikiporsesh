<?php

namespace App\StructuredData\Types;

use App\StructuredData\StructuredData;

class SD_BreadcrumbList extends StructuredData {

    protected array $items = [];
    protected int $totalItems = 0;
    protected bool $hasItems = false;
    protected string $readableText = '';

    public function process() {

        $itemListElement = $this->structuredData->itemListElement ?? [];

        foreach ($itemListElement as $key => $element) {
            // skip first one
            if($key == 0)
                continue;

            $this->items[] = $element->item->name;
        }

        if(count($this->items) > 1) {
            (array_pop($this->items));
        }

        $this->totalItems = count($this->items);
        $this->hasItems = $this->totalItems > 0;

        $this->readableText = implode(' > ', $this->items);
    }

    public function getBreadcrumbItems() : array {
        return $this->items;
    }

    public function getTotalItems() : int {
        return $this->totalItems;
    }

    public function hasItems() : bool {
        return $this->hasItems;
    }

    public function getReadableText() : string {
        return $this->readableText;
    }

    /**
     * @return array
     */
    public function getItems(): array {
        return $this->items;
    }
}
