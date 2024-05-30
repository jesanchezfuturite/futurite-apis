<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GoogleAdsController;

Route::get('/google-ads/authenticate', [CampaignsController::class, 'authenticate'])->name('google.ads.authenticate');
Route::get('/google-ads/callback', [CampaignsController::class, 'authenticate'])->name('google.ads.callback');
Route::get('/google-ads/campaigns', [CampaignsController::class, 'getCampaigns'])->name('google.ads.campaigns');

Route::get('/', function () {
    return view('welcome');
});
