<?php

use GuzzleHttp\Client;
use Illuminate\Http\Request;

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
Route::get('/organization/{organizationId}/{categoryId?}', 'HomeController@organization');
Route::get('/branch/{id}/{category_id?}', 'HomeController@branch');
Route::get('/search', 'HomeController@search');
Route::get('/contacts', 'HomeController@contacts');

// change city
Route::get('/utils/changecity/{city_id}', 'UtilsController@changeCity');
Route::get('/utils/changecategory/{category_id}', 'UtilsController@changeCategory');

// Authentication Routes...
Route::get('login', 'Auth\AuthController@showLoginForm');
Route::post('login', 'Auth\AuthController@login');
Route::get('logout', 'Auth\AuthController@logout');
// Route::auth();

// Password Reset Routes...
Route::get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');
Route::post('password/email', 'Auth\PasswordController@sendResetLinkEmail');
Route::post('password/reset', 'Auth\PasswordController@reset');

/**
 * Seeding routes
 */
Route::group(['prefix' => 'seed', 'middleware' => 'auth'], function() 
{
	Route::get('/gis', 'ParserController@gis');
	
	// Route::get('/search/{query}', 'SeedController@search');
	// Route::get('/compare/{category_id?}/{name?}', 'SeedController@compare');
	Route::get('/check/{table}', 'SeedController@check');
	
	Route::get('/category/{src}', 'SeedController@category');
	// Route::get('/city/{src}', 'SeedController@city');
	// Route::get('/organization/{src}', 'SeedController@organization');

	// Route::get('/excel/{filename}', "SeedController@excel");
	// Route::get('/parse', 'SeedController@parse');
	// Route::get('/parse2/{limit}', 'ParserController@parser');
});

/**
 * Admin
 */
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function() 
{
	Route::get('/', 'AdminController@index');

	// Cities
	Route::get('/cities', 'CitiesController@index');
	Route::get('/cities/create', 'CitiesController@create');
	Route::post('/cities', 'CitiesController@store');
	Route::get('/cities/{id}/edit', 'CitiesController@edit');
	Route::put('/cities/{id}', 'CitiesController@update');
	Route::get('/cities/{id}/remove', 'CitiesController@destroy');

	// Categories / Subcategories
	Route::get('/categories/{id}/children', 'CategoriesController@children');
	Route::get('/categories/{id}/editchild', 'CategoriesController@editChild');
	Route::put('/categories/{id}/updatechild', 'CategoriesController@updateChild');
	Route::get('/categories/{id}/removechild', 'CategoriesController@destroyChild');
	Route::get('/categories/{parent_id}/createchild', 'CategoriesController@createChild');
	Route::post('/categories/{parent_id}/storechild', 'CategoriesController@storeChild');

	// Categories
	Route::get('/categories', 'CategoriesController@index');
	Route::get('/categories/create', 'CategoriesController@create');
	Route::post('/categories', 'CategoriesController@store');
	Route::get('/categories/{id}/edit', 'CategoriesController@edit');
	Route::put('/categories/{id}', 'CategoriesController@update');
	Route::get('/categories/{id}/remove', 'CategoriesController@destroy');

	// Organizations / Branches
	Route::get('/organizations/{organization_id}/createbranch', 'BranchesController@create');
	Route::post('/organizations/{organization_id}/storebranch', 'BranchesController@store');
	Route::get('/branches/{branch_id}/edit', 'BranchesController@edit');
	Route::put('/branches/{branch_id}', 'BranchesController@update');
	Route::get('/branches/{branch_id}/gallery', 'BranchesController@editGallery');
	Route::get('/branches/{branch_id}/remove', 'BranchesController@destroy');
	 
	// Organizations
	Route::get('/topten', 'OrganizationsController@topTen');
	Route::get('/organizations', 'OrganizationsController@index')->name('admin.organizations.index');
	Route::get('/organizations/create', 'OrganizationsController@create');
	Route::post('/organizations', 'OrganizationsController@store');
	Route::get('/organizations/{id}/edit', 'OrganizationsController@edit');
	Route::put('/organizations/{id}', 'OrganizationsController@update');
	Route::get('/organizations/{id}/remove', 'OrganizationsController@destroy');

	Route::get('/organizations-no-category', 'OrganizationsController@indexNoCategory');

	// Media manager
	Route::get('/mediamanager', 'MediaManagerController@index');
	Route::post('/mediamanager/upload/icon', 'MediaManagerController@uploadIcon');
	Route::post('/mediamanager/delete/icon', 'MediaManagerController@deleteIcon');
	Route::post('/mediamanager/upload/photo/', 'MediaManagerController@uploadPhoto');
	Route::post('/mediamanager/update/photo', 'MediaManagerController@updatePhoto');
	Route::post('/mediamanager/deletebyid/photo', 'MediaManagerController@deletePhotoById');
	Route::post('/mediamanager/delete/photo', 'MediaManagerController@deletePhoto');

	// Offers 
	Route::resource('offers', 'OffersController');

	// Subscriptions
	Route::resource('subscriptions', 'SubscriptionsController');
});


/**
 * Seeder
 */
Route::group(['prefix' => 'seeders', 'middleware' => 'auth'], function()
{
	Route::get('/excel', 'ExcelSeeder@index');
	Route::post('/excel/seed', 'ExcelSeeder@seed')->name('seeders.excel.seed');

	Route::get('/mapper', 'SeedController@mapper')->name('seed.mapper');
	Route::post('/mapper/map', 'SeedController@map')->name('seed.map_category');

	Route::get('/parseall', 'SeedController@parseAll')->name('seed.parseall');

	Route::get('/test', 'SeedController@test')->name('seed.test');
});



/**
 * Optimizer
 */
Route::group(['prefix' => 'opt', 'middleware' => 'auth'], function()
{
	Route::get('/categories', 'OptimizerController@categories');
	Route::get('/subcategories', 'OptimizerController@subcategories');
	Route::get('/organizations', 'OptimizerController@organizations');
});

/**
 * Ajax
 */
Route::group(['prefix' => 'ajax', 'middleware' => 'auth'], function()
{
	Route::post('/togglestatus', 'AjaxController@toggleStatus');
	Route::post('/topit', 'AjaxController@topIt');
	Route::post('/branches/makemain', 'AjaxController@makeMainBranch');
	Route::post('/branches/makefeatured', 'AjaxController@makeFeaturedBranch');
	Route::post('/topten/reorder', 'AjaxController@topTenReorder');
	Route::post('/topten/remove', 'AjaxController@removeFromTopTen');
	Route::post('/category/changeparent', 'AjaxController@changeCategoryParent');

	// organizations
	Route::post('/organizations/topten', 'AjaxController@addToTopTen');
	Route::get('/organizations/by_name', 'AjaxController@getOrganizationsByName')->name('ajax.organizations.by_name');
});

/**
 * Api routes
 */
Route::group(['prefix' => 'api'], function() 
{
	Route::get('/contacts', 'ApiController@getContacts');

	Route::get('/cities', 'ApiController@getCities');
	Route::get('/searchautocomplete', 'ApiController@searchAutoComplete');
	
	Route::get('/branch', 'ApiController@getBranch');
	Route::get('/photos', 'ApiController@getPhotos');
	
	Route::get('/organization', 'ApiController@getOrganization');
	Route::get('/organizations', 'ApiController@getOrganizations');
	
	Route::get('/branches/{city_id}/{ids}', 'ApiController@getBranches');
	Route::get('/favorites/{ids}', 'ApiController@getFavorites');
	
	Route::get('/categories/{city_id?}', 'ApiController@getCategories');
	Route::get('/subcategories/{parent_id}/{city_id?}', 'ApiController@getSubcategories');
	
	Route::get('/services/{city_id?}', 'ApiController@getServices');
	
	Route::get('/offers', 'ApiController@getOffers');
	Route::get('/offer', 'ApiController@getOffer');


	// TODO: remove before deployment
	//Route::get('/seed', 'ApiController@seed');
});


Route::get('/test/{minId?}', function($minId = 180346) {
	$now = date("d.m.Y H:i:s", time());
	// dd($now);

	return view('test.test');

});

Route::post('/test-post', function(Request $request) {

	$input = $request->all();
	// dd($input);

	$now = date("d.m.Y H:i:s", time());	

	$base_url = "https://api.rglservice.kz/v2/:promo_id";
	$api_method = "/w/users/:phone/codes/redeem";
	$promo_id = "308838";
	$api_key = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE0OTE4ODg1NTIsIm5iZiI6MTQ5MTg4ODU1MiwicHJvbW9faWQiOiIzMDg4MzgiLCJzb3VyY2UiOiJzaXRlIn0._AjktgHSqK1sAAJ_iAADSnpIgTH6KCRkrAaoZgRi9co";

	/* следующие поля заполняются на основе формы регистрации кода */
	$phone = preg_replace('/\D/', '', $input['user_phone']);
	$post_fields = array(
	    "transaction_id" => uniqid(),
	    "promo_code" => $input['promo_code'],
	    "promo_app_type" => "web",
	    "promo_language" => "ru",
	    "promo_time" => $now,
	    "user_firstname" => $input['user_firstname'],
	    "user_lastname" => $input['user_lastname'],
	    "user_city" => $input['user_city'],
	    // "user_age" => "21",
	    // "user_gender" => "male"
	);

	$url = $base_url . $api_method;
	$url = preg_replace('/:promo_id/', $promo_id, $url);
	$url = preg_replace('/:phone/', $phone, $url);

    // dd($url);
	// dd($post_fields);

	$response = false;
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      	"content-type: application/x-www-form-urlencoded",
      	"api-key: " . $api_key
    ));
    
    $json = curl_exec($ch);
    $errno = curl_errno($ch);
    $http_info = curl_getinfo($ch);
    curl_close($ch);
    
    if (!$errno) {
        $response = json_decode($json, true);        
        if ($response)
        {
            dd($response);

            // TODO: code 200
        }
    } else {
    	// TODO: ERROR
    	dd($errno);
    }
});