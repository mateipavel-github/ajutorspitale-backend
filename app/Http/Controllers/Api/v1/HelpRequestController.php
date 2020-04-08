<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\HelpRequest;
use App\Http\Resources\HelpRequestCollection;
use \App\Http\Resources\HelpRequest as HelpRequestResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Log;
use App\MetadataRequestStatusType;
use App\Http\Controllers\Api\v1\PostingController;

class HelpRequestController extends PostingController
{
    public function __construct() {
        parent::setPostingType('request');
    }

}
