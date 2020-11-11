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

Route::get('/', function () {
    return view('panel');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('inicio');
Route::get('/panel','SincroController@panel')->name('panel');
Route::get('/procesos/{nombre?}','SincroController@procesos')->name('proceso');
Route::get('/stock', function () {
    return view('stock');
});
Route::get('/precios', function () {
    return view('precios');
});

Route::get('/articulos', function () {
    return view('articulos');
});
