<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// API Documentation routes
Route::get('/docs', function () {
    return view('scribe.index');
});

Route::get('/docs.postman', function () {
    return response()->file(storage_path('app/scribe/collection.json'));
})->name('scribe.postman');

Route::get('/docs.openapi', function () {
    return response()->file(storage_path('app/scribe/openapi.yaml'));
})->name('scribe.openapi');
