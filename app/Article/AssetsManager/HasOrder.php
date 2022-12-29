<?php

namespace App\Article\AssetsManager;

interface HasOrder {
    public function getOrderString() : string;
}
