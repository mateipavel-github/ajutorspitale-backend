<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\HelpRequestExport;

class ExportController extends Controller
{
    public function helpRequests(Request $request) {
        return (new HelpRequestExport)->download('requests.csv');
    }

}
