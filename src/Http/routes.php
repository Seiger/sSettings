<?php

use Illuminate\Support\Facades\Route;
use Seiger\sSettings\Controllers\sSettingsController;

Route::middleware('mgr')->prefix('ssettings/')->name('sSettings.')->group(function () {
    Route::get('/', [sSettingsController::class, 'index'])->name('index');
    Route::post('/', [sSettingsController::class, 'updateSettings'])->name('update-settings');
    Route::get('configure', [sSettingsController::class, 'configure'])->name('configure');
    Route::post('configure', [sSettingsController::class, 'updateConfigure'])->name('update-configure');
});
