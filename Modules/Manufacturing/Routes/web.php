<?php

use Illuminate\Support\Facades\Route;
use Modules\Manufacturing\Http\Controllers\InstallController;
use Modules\Manufacturing\Http\Controllers\ProductionController;
use Modules\Manufacturing\Http\Controllers\RecipeController;
use Modules\Manufacturing\Http\Controllers\SettingsController;

Route::middleware([
    'web',
    'authh',
    'SetSessionData',
    'auth',
    'language',
    'timezone',
    'AdminSidebarMenu',
])->prefix('manufacturing')->group(function (): void {

    // 📦 Installation
    Route::get('/install', [InstallController::class, 'index']);
    Route::post('/install', [InstallController::class, 'install']);
    Route::get('/install/update', [InstallController::class, 'update']);
    Route::get('/install/uninstall', [InstallController::class, 'uninstall']);

    // 🍳 Recipes
    Route::get('/is-recipe-exist/{variation_id}', [RecipeController::class, 'isRecipeExist']);
    Route::get('/ingredient-group-form', [RecipeController::class, 'getIngredientGroupForm']);
    Route::get('/get-recipe-details', [RecipeController::class, 'getRecipeDetails']);
    Route::get('/get-ingredient-row/{variation_id}', [RecipeController::class, 'getIngredientRow']);
    Route::get('/add-ingredient', [RecipeController::class, 'addIngredients']);
    Route::post('/update-recipe-totals', [RecipeController::class, 'updateRecipeTotals']);
    Route::post('/update-product-prices', [RecipeController::class, 'updateRecipeProductPrices']);

    // 🛠️ Custom Production Routes (MUST be before resource route)
    Route::get('/production/create-multiple', [ProductionController::class, 'createMultiple']);
    Route::post('/production/store-multiple', [ProductionController::class, 'storeMultiple']);
    Route::get('/production/print-multiple-pdf', [ProductionController::class, 'printMultiplePdf'])
        ->name('manufacturing.production.printMultiplePdf');

    // 🧾 Price Utility
    Route::get('/get-recipe-price', [ProductionController::class, 'getRecipePrice']);
    Route::get('/ajax-all-recipes', [ProductionController::class, 'ajaxAllRecipes'])->name('manufacturing.ajaxAllRecipes');


    // 📈 Report
    Route::get('/report', [ProductionController::class, 'getManufacturingReport']);
    Route::get('/ajax-recipes', [ProductionController::class, 'ajaxRecipes']);

    // 🧩 Resource Controllers
    Route::resource('/recipe', RecipeController::class)->except(['edit', 'update']);
    Route::resource('/production', ProductionController::class); // Keep after custom
    Route::resource('/settings', SettingsController::class)
        ->only(['index', 'store'])
        ->names([
            'index' => 'manufacturing.settings.index',
            'store' => 'manufacturing.settings.store',
        ]);
});
