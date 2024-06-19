<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/ads/config', [App\Http\Controllers\AdsController::class, 'listClients'])->name('ads.config');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/config', [App\Http\Controllers\ConfigController::class, 'listCustomers'])->name('config.listcostumers');

Route::get('/google-ads/customers', [GoogleController::class, 'listCustomers']);
