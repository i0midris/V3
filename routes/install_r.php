<?php

use App\Http\Controllers\Install\InstallController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installation Web Routes
|--------------------------------------------------------------------------
|
| Routes related to installation of the software
|
*/

Route::get('/install-start', [InstallController::class, 'index'])->name('install.index');
Route::get('/install/check-server', [InstallController::class, 'checkServer'])->name('install.checkServer');
Route::get('/install/details', [InstallController::class, 'details'])->name('install.details');
Route::post('/install/post-details', [InstallController::class, 'postDetails'])->name('install.postDetails');
Route::post('/install/install-alternate', [InstallController::class, 'installAlternate'])->name('install.installAlternate');
Route::get('/install/success', [InstallController::class, 'success'])->name('install.success');

Route::get('/install/update', [InstallController::class, 'updateConfirmation'])->name('install.updateConfirmation');
Route::post('/install/update', [InstallController::class, 'update'])->name('install.update');
