<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ImportController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['throttle:5000,1'],
], function () {
    Route::post('imports', [ImportController::class, 'store'])->name('imports.store');
});
