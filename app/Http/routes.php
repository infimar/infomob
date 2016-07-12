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

// index
Route::get('/', 'HomeController@index');

Route::get('/category/{slug}', 'HomeController@category');

Route::get('/organization/{organizationId}/{categoryId}', 'HomeController@organization');

Route::get('/branch/{id}/{category_id}', 'HomeController@branch');

// change city
Route::get('/utils/changecity/{city_id}', 'HomeController@changeCity');

// Authentication Routes...
Route::get('login', 'Auth\AuthController@showLoginForm');
Route::post('login', 'Auth\AuthController@login');
Route::get('logout', 'Auth\AuthController@logout');

// Password Reset Routes...
Route::get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');
Route::post('password/email', 'Auth\PasswordController@sendResetLinkEmail');
Route::post('password/reset', 'Auth\PasswordController@reset');

/**
 * Seeding routes
 */
Route::group(['prefix' => 'seed'], function() 
{
	Route::get('/', 'SeedController@index');
	
	Route::get('/search/{query}', 'SeedController@search');
	Route::get('/compare/{category_id?}/{name?}', 'SeedController@compare');
	Route::get('/check/{table}', 'SeedController@check');
	
	Route::get('/category/{src}', 'SeedController@category');
	Route::get('/city/{src}', 'SeedController@city');
	Route::get('/organization/{src}', 'SeedController@organization');

	Route::get('/excel/{filename}', "SeedController@excel");
	Route::get('/parse', 'SeedController@parse');
});

/**
 * Admin
 */
Route::group(['prefix' => 'admin', 'middleware' => 'web'], function() 
{
	Route::get('/', 'AdminController@index');

	// Categories / Subcategories
	Route::get('/categories/{id}/children', 'CategoriesController@children');
	Route::get('/categories/{id}/removechild', 'CategoriesController@destroyChild');
	Route::get('/categories/{id}/editchild', 'CategoriesController@editChild');
	Route::put('/categories/{id}/updatechild', 'CategoriesController@updateChild');
	Route::get('/categories/{parent_id}/createchild', 'CategoriesController@createChild');
	Route::post('/categories/{parent_id}/storechild', 'CategoriesController@storeChild');

	// Categories
	Route::get('/categories', 'CategoriesController@index');
	Route::get('/categories/create', 'CategoriesController@create');
	Route::post('/categories', 'CategoriesController@store');
	Route::get('/categories/{id}/remove', 'CategoriesController@destroy');
	Route::get('/categories/{id}/edit', 'CategoriesController@edit');
	Route::put('/categories/{id}', 'CategoriesController@update');

	// Organizations / Branches
	Route::get('/organizations/{organization_id}/createbranch', 'BranchesController@create');
	Route::post('/organizations/{organization_id}/storebranch', 'BranchesController@store');
	Route::get('/branches/{branch_id}/edit', 'BranchesController@edit');
	 
	// Organizations
	Route::get('/organizations', 'OrganizationsController@index');
	Route::get('/organizations/create', 'OrganizationsController@create');
	Route::post('/organizations', 'OrganizationsController@store');
	Route::get('/organizations/{id}/remove', 'OrganizationsController@destroy');
	Route::get('/organizations/{id}/edit', 'OrganizationsController@edit');
	Route::put('/organizations/{id}', 'OrganizationsController@update');

});

/**
 * Api routes
 */
Route::group(['prefix' => 'api'], function() 
{
	Route::get('/searchautocomplete', 'ApiController@searchAutoComplete');
	
	Route::get('/branch', 'ApiController@getBranch');
	Route::get('/photos', 'ApiController@getPhotos');
	
	Route::get('/organization', 'ApiController@getOrganization');
	Route::get('/organizations', 'ApiController@getOrganizations');
	
	Route::get('/branches/{city_id}/{ids}', 'ApiController@getBranches');
	Route::get('/favorites/{ids}', 'ApiController@getFavorites');
	
	Route::get('/categories/{city_id?}', 'ApiController@getCategories');
	Route::get('/subcategories/{parent_id}/{city_id?}', 'ApiController@getSubcategories');
	
	Route::get('/services/{city_id}', 'ApiController@getServices');
	
	// TODO: remove before deployment
	//Route::get('/seed', 'ApiController@seed');
});