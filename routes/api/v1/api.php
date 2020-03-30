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

Route::post('/user/login', 'api\v1\LoginController@login');

//Route::get('/requests', 'RequestController');

Route::middleware("auth:api")->apiResource('requests', 'Api\v1\HelpRequestController');
Route::middleware("auth:api")->apiResource('changeRequests', 'Api\v1\HelpRequestChangeController');
Route::middleware("auth:api")->apiResource('metadata', 'Api\v1\MetadataController');

/*
Route::middleware('auth:api')->get('/requests', function (Request $request) {

    return $request->all();

    //return $request->user();
});
*/
