<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AdsController;
use App\Http\Controllers\AtcController;
use App\Http\Controllers\GoogleAdsController;
use App\Http\Controllers\GoogleController;
/*
Route::get('/google-ads/authenticate', [CampaignsController::class, 'authenticate'])->name('google.ads.authenticate');
Route::get('/google-ads/callback', [CampaignsController::class, 'callback'])->name('google.ads.callback');
Route::get('/google-ads/campaigns', [CampaignsController::class, 'getCampaigns'])->name('google.ads.campaigns');
Route::get('/google-ads/accounts', [CampaignsController::class, 'getAccounts'])->name('google.ads.accounts');
*/

Route::get('/google-ads/callback', [GoogleAdsController::class, 'handleGoogleAdsCallback']);

Route::get('/google-ads/update', [CampaignsController::class, 'getCampaignsByCustomerId']);

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/general', [App\Http\Controllers\HomeController::class, 'general'])->name('general');

Route::get('/google-ads/customers', [GoogleController::class, 'listCustomers']);

/* rutas de las herramientas internas */
Route::get('/ads/config', [AdsController::class, 'listClients'])->name('ads.config');



/* rutas ajax */
Route::post('/ads/config-customers-json', [AdsController::class, 'listCustomersJson']);
Route::post('/ads/unlink-customer', [AdsController::class, 'unlinkCustomersJson']);
Route::post('/ads/relate-customer', [AdsController::class, 'relateCustomersJson']);

Route::get('/ads/client-stats-json', [AdsController::class, 'getClientStats']);


Route::get('auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback']);



/*ruta atc*/
Route::post('/atc/hook', [AtcController::class, 'processData']);
