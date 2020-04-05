<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

/* just for testing purposes */
Route::group(['prefix' => 'user'], function() {
    Route::get('/', function() {
        return response()->json(Auth::guard('api')->user());
    });
});

/* requests */
Route::middleware("auth:api")->resource('requests', 'Api\v1\HelpRequestController', ['except' => ['store']]);
Route::put('requests', 'Api\v1\HelpRequestController@store');

Route::middleware("auth:api")->post('requests/mass-assign-to-user', 'Api\v1\HelpRequestController@massAssignToCurrentUser');

Route::middleware("auth:api")->resource('changeRequests', 'Api\v1\HelpRequestChangeController', ['except' => ['store']]);
Route::put('changeRequests', 'Api\v1\HelpRequestChangeController@store');

Route::get('metadata', 'Api\v1\MetadataController@index');
Route::middleware("auth:api")->put('metadata', 'Api\v1\MetadataController@store');
Route::middleware("auth:api")->delete('metadata', 'Api\v1\MetadataController@softDelete');

Route::middleware("auth:api")->resource('users', 'Api\v1\UserController', ['except' => ['delete']]);
Route::middleware("auth:api")->delete('users/{user}', 'Api\v1\UserController@softDelete');

Route::middleware("auth:api")->resource('request-notes', 'Api\v1\HelpRequestNoteController');

Route::get('metadata/medical-units', 'Api\v1\MetadataController@medicalUnits');

// these routes should be removed after import
// Route::prefix('import')->group(function () {
//     Route::get('need-types', 'Api\v1\ImportController@need_types');
//     Route::get('form-responses', 'Api\v1\ImportController@form_responses');
// });
