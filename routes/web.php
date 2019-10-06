<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index');
Route::get('/get-records', 'HomeController@getRecords');
Route::post('/import-csv', 'HomeController@importCsv');
Route::post('/calculate-records', 'HomeController@calculateRecords');
Route::post('/save-record', 'HomeController@saveRecord');
