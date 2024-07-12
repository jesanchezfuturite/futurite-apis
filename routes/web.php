<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AdsController;
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

Route::get('/google-ads/customers', [GoogleController::class, 'listCustomers']);

/* rutas de las herramientas internas */
Route::get('/ads/config', [AdsController::class, 'listClients'])->name('ads.config');

/** test reporte marketing */
Route::get('/demo-layout-marketing', function () {
    return view('demo-layout-marketing');
});


/* rutas ajax */
Route::post('/ads/config-customers-json', [AdsController::class, 'listCustomersJson']);
Route::post('/ads/unlink-customer', [AdsController::class, 'unlinkCustomersJson']);
Route::post('/ads/relate-customer', [AdsController::class, 'relateCustomersJson']);

Route::get('/ads/client-stats-json', [AdsController::class, 'getClientStats']);


