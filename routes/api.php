<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatchController;
use App\Http\Controllers\Api\RelicController;
use App\Http\Controllers\Api\RelicInventoryController;
use App\Http\Controllers\Api\UserItemsController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\GachaController;

// Patches
Route::get('/patches/{patch}', [PatchController::class, 'show']);

// Relics
Route::get('/relics/relic/{selectedItem}', [RelicController::class, 'getRelicData'])->name('api.relic.show');
Route::get('/relics/planar/{selectedItem}', [RelicController::class, 'getPlanarData'])->name('api.planar.show');

// User Profile Pic
Route::post('/profile/upload', [UserController::class, 'uploadProfile'])
    ->name('profile.upload')
    ->middleware('web');

Route::post('/profile/update', [UserController::class, 'updateProfile'])
    ->name('profile.update')
    ->middleware('web');

// User Preferences
Route::post('/user/update', [UserController::class, 'updateUserPreferences'])->name('user-preferences.update')->middleware('web');
Route::post('/user/featured', [UserController::class, 'updateFeaturedItem'])->name('user.featured.update')->middleware('web');

// Email Verification
Route::post('/email/verification', [UserController::class, 'sendVerificationEmail'])->name('email-verification')->middleware('web');

// User Inventory
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/inventory', [
        RelicInventoryController::class,
        'index'
    ])->name('inventory.index');

    Route::post('/relics/generate', [
        RelicInventoryController::class,
        'generateRelics'
    ])->name('relics.generate');

    Route::get('/items', [
        UserItemsController::class,
        'index'
    ])->name('items.index');

    Route::patch('/inventory/relics/{userRelic}', [
        RelicInventoryController::class,
        'update'
    ])->name('inventory.relics.update');

    Route::patch('/inventory/characters/lightcone', [
        UserItemsController::class,
        'equipLightcone'
    ])->name('inventory.lightcone.equip');

    Route::patch('/inventory/characters/lightcone/superimpose', [
        UserItemsController::class,
        'superimposeLightcone'
    ])->name('inventory.lightcone.superimpose');

    Route::patch('/inventory/characters/relics', [
        UserItemsController::class,
        'saveRelics'
    ])->name('inventory.relics.save');

    Route::patch('/inventory/characters/eidolon', [
        UserItemsController::class,
        'activateEidolon'
    ])->name('inventory.eidolon.activate');
});

// Gacha
Route::post('/gacha/pull', [GachaController::class, 'submitPull'])->name('gacha.submitPull')->middleware('web');