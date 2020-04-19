<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

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
Route::group(['prefix' => 'system'], function() {
    Route::get('/user', function() {
        return [
            'auth'=>response()->json(Auth::guard('api')->user()),
            'request'=>response()->json(request()->user('api'))
        ];
    });
    Route::get('/php', function() {
        phpinfo();
    });
    Route::get('/hash/{str}', function() {
        echo Hash::make(request()->get('str'));
    });
});

/* requests */
Route::middleware("auth:api")->resource('requests', 'Api\v1\HelpRequestController', ['except' => ['store']]);
Route::put('requests', 'Api\v1\HelpRequestController@store');
Route::middleware("auth:api")->put('requests/{id}/add-note', 'Api\v1\HelpRequestController@addNote');
Route::middleware("auth:api")->post('requests/mass-assign-to-user', 'Api\v1\HelpRequestController@massAssignToCurrentUser');

/* offers */
Route::middleware("auth:api")->resource('offers', 'Api\v1\HelpOfferController', ['except' => ['store']]);
Route::put('offers', 'Api\v1\HelpOfferController@store');
Route::middleware("auth:api")->put('offers/{id}/add-note', 'Api\v1\HelpOfferController@addNote');

/* sponsors */
Route::middleware("auth:api")->resource('sponsors', 'Api\v1\SponsorController');
Route::middleware("auth:api")->put('sponsors', 'Api\v1\SponsorController@store');

/* deliveries */
Route::middleware("auth:api")->resource('deliveries', 'Api\v1\DeliveryController');
Route::middleware("auth:api")->put('deliveries', 'Api\v1\DeliveryController@store');
Route::middleware("auth:api")->put('deliveries/{id}/add-note', 'Api\v1\DeliveryController@addNote');

/* delivery planning */
Route::middleware("auth:api")->resource('delivery-plans', 'Api\v1\DeliveryPlanController');
Route::middleware("auth:api")->put('delivery-plans', 'Api\v1\DeliveryPlanController@store');

/* metadata */
Route::get('metadata', 'Api\v1\MetadataController@index');
Route::middleware("auth:api")->put('metadata', 'Api\v1\MetadataController@store');
Route::middleware("auth:api")->delete('metadata/{type}/{id}', 'Api\v1\MetadataController@delete');
Route::get('metadata/medical-units', 'Api\v1\MetadataController@medicalUnits');

/* users */
Route::middleware("auth:api")->resource('users', 'Api\v1\UserController', ['except' => ['delete']]);
Route::middleware("auth:api")->delete('users/{user}', 'Api\v1\UserController@softDelete');

/* stats */
Route::middleware("auth:api")->get('stats/by-county', 'Api\v1\StatsController@byCounty');
Route::middleware("auth:api")->get('stats/all', 'Api\v1\StatsController@all');
Route::get('stats/requests-to-csv', 'Api\v1\StatsController@requestsToCsv');

// // these routes should be removed after import
// // if(env('APP_ENV')==='local') {
//     Route::prefix('import')->group(function () {
//         Route::get('medical-units', 'Api\v1\ImportController@medicalUnits');
//         //Route::get('need-types', 'Api\v1\ImportController@need_types');
//         //Route::get('form-responses', 'Api\v1\ImportController@form_responses');
//     });
// // }
