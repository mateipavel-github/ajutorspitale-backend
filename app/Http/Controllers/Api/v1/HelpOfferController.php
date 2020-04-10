<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\v1\PostingController;

class HelpOfferController extends PostingController
{
    public function __construct() {
        parent::setPostingType('offer');
    }
}
