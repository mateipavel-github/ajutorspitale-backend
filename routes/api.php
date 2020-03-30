<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::get('/requests', 'RequestController');
Route::apiResource('requests', 'HelpRequestController');
Route::apiResource('changeRequests', 'HelpRequestChangeController');

Route::apiResource('metadata', 'MetadataController');

/*
Route::middleware('auth:api')->get('/requests', function (Request $request) {

    return $request->all();

    //return $request->user();
});
*/
