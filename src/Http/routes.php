<?php

use Illuminate\Support\Facades\Route;
use Seiger\sSettings\Controllers\sSettingsController;

Route::middleware('mgr')->prefix('ssettings/')->name('sSettings.')->group(function () {
    Route::get('/', [sSettingsController::class, 'index'])->name('index');
    Route::get('configure', [sSettingsController::class, 'configure'])->name('configure');
    Route::get('assets/{file}', [sSettingsController::class, 'asset'])
        ->where('file', '[A-Za-z0-9._-]+')
        ->name('asset');
});
