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

Route::get('/', 'Auth\LoginController@getIndex');

Route::get('/end-point/google-auth', 'Auth\EndpointController@getGoogleAuth');
Route::get('/end-point/facebook-auth', 'Auth\EndpointController@getFacebookAuth');
Route::get('/end-point/twitter-auth', 'Auth\EndpointController@getTwitterAuth');
Route::get('/end-point/linkedin-auth', 'Auth\EndpointController@getLinkedinAuth');

Route::get('/auth/google-login', 'Auth\LoginController@getGoogleLogin');
Route::get('/auth/facebook-login', 'Auth\LoginController@getFacebookLogin');
Route::get('/auth/twitter-login', 'Auth\LoginController@getTwitterLogin');
Route::get('/auth/linkedin-login', 'Auth\LoginController@getLinkedinLogin');

Route::get('/info', 'InfoController@getIndex');
Route::get('/logout', 'Auth\LoginController@getLogout');