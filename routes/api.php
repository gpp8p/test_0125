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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('register', 'JWTAuthController@register');
    Route::post('login', 'JWTAuthController@login');
    Route::post('logout', 'JWTAuthController@logout');
    Route::post('refresh', 'JWTAuthController@refresh');
    Route::get('profile', 'JWTAuthController@profile');

});


Route::group([
    'middleware' => 'api',
    'prefix' => 'shan',
], function($router){
    Route::get('testGet','testController@returnRequest');
    Route::post('testPost', 'testController@returnPost');
    Route::post('fileUpload', 'FileUploadController@recieveFile')->name('fileUpload');
    Route::get('getLayout', 'cardInstanceController@getLayoutById');
    Route::post('/saveCardOnly', 'cardInstanceController@saveCardOnly')->name('saveCardOnly');
    Route::post('/saveCardParameters','cardInstanceController@saveCardParameters')->name('saveCardParameters');
    Route::post('/saveCardContent','cardInstanceController@saveCardContent')->name('saveCardContent');
    Route::get('/getCardDataById', 'cardInstanceController@getCardDataById')->name('getCardDataById');
});

