<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HelpOfferController extends Controller
{
    public function __construct() {
        parent::setPostingType('offer');
    }
}
