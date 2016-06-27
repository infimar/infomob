<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/index', function () {
    return view('layouts.frontend.index');
});
Route::get('/category', function () {
    return view('layouts.frontend.category');
});
Route::get('/organization', function () {
    return view('layouts.frontend.organization');
});
Route::auth();

Route::get('/home', 'HomeController@index');

Route::auth();

Route::get('/home', 'HomeController@index');

/**
 * Seeding routes
 */
Route::group(['prefix' => 'seed'], function() {
	Route::get('/', 'SeedController@index');
	Route::get('/search/{query}', 'SeedController@search');
	Route::get('/compare/{category_id?}/{name?}', 'SeedController@compare');
	Route::get('/check/{table}', 'SeedController@check');
	Route::get('/category/{src}', 'SeedController@category');
	Route::get('/city/{src}', 'SeedController@city');
	Route::get('/organization/{src}', 'SeedController@organization');

	Route::get('/excel/{filename}', "SeedController@excel");
});
Route::auth();

Route::get('/home', 'HomeController@index');
