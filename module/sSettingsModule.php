<?php

use Seiger\sSettings\Controllers\sSettingsController;

echo app(sSettingsController::class)
    ->index()
    ->render();
