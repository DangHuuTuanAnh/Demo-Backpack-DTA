<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/home','BlogController@index')->name('home.index');

Route::get('resize', 'ImageController@index');

Route::post('resize/resize_image', 'ImageController@store')->name('resizeimage');

Route::get('create','ImageController@create')->name('test');
Route::post('create','ImageController@store');

//Route::get('summernote-image-upload',array('as'=>'summernote.getForm','uses'=>'ImageController@getSummernote')) ;
//Route::post('summernote-image-upload',array('as'=>'summernote.postForm','uses'=>'ImageController@postSummernote')) ;

Route::get('test-summernote','ImageController@getSummernote')->name('summernote.getForm');
Route::post('test','ImageController@postSummernote')->name('summernote.postForm');

Route::get('abc','BlogController@index')->name('blog.index');




//Auth::routes();

//Route::group(['middleware' => 'web', 'prefix' => config('backpack.base.route_prefix')], function () {
//    Route::auth();
//    Route::get('logout', 'Auth\LoginController@logout');
//});

//Route::get('/home', 'HomeController@index')->name('home');

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => [
        config('backpack.base.web_middleware', 'web'),
        config('backpack.base.middleware_key', 'admin'),
    ],
    'namespace'  => 'Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');
    Route::crud('role', 'RoleCrudController');
    Route::crud('permission', 'PermissionCrudController');
    Route::crud('category', 'CategoryCrudController');
    Route::crud('tag', 'TagCrudController');
});
