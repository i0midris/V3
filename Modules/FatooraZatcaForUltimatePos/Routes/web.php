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

use Modules\FatooraZatcaForUltimatePos\Http\Controllers\DownloadXmlController;
use Modules\FatooraZatcaForUltimatePos\Http\Controllers\ResendController;
use Modules\FatooraZatcaForUltimatePos\Http\Controllers\SettingsController;
use Modules\FatooraZatcaForUltimatePos\Http\Controllers\VerifyController;

Route::prefix('zatca')
    ->as('zatca.')
    ->middleware(['web', 'setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])
    ->group(function (): void {
        Route::resource('settings', SettingsController::class)->only(['index', 'store']);
        Route::resource('verify', VerifyController::class)->only(['store']);
        Route::post('transactions/{transaction}/resend', ResendController::class)->name('resend');
        Route::get('transactions/{transaction}/download-xml', DownloadXmlController::class)->name('download-xml');
    });
