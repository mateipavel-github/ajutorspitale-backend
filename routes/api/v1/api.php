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

Route::post('/user/login', 'Api\v1\LoginController@login');

Route::apiResource('requests', 'Api\v1\HelpRequestController');
Route::middleware("auth:api")->post('requests/mass-assign-to-user', 'Api\v1\HelpRequestController@massAssignToCurrentUser');
Route::middleware("auth:api")->apiResource('changeRequests', 'Api\v1\HelpRequestChangeController');

Route::get('metadata', 'Api\v1\MetadataController@index');
Route::middleware("auth:api")->put('metadata', 'Api\v1\MetadataController@store');
