<?php

use App\Http\Controllers\Api\LotteryApiController;
use App\Http\Controllers\Lottery\ModalityController;
use Illuminate\Support\Facades\Route;

Route::prefix('lottery')->group(function () {
    Route::get('/modalities', [LotteryApiController::class, 'modalities']);
    Route::get('/modalities/{modality}', [LotteryApiController::class, 'modality']);
    Route::get('/modalities/{modality}/history', [LotteryApiController::class, 'history']);
    Route::get('/modalities/{modality}/repeated-combinations', [LotteryApiController::class, 'repeatedCombinations']);

    Route::post('/modalities/{modality}/generate', [ModalityController::class, 'generate']);
    Route::post('/modalities/{modality}/generate-smart', [ModalityController::class, 'generateSmart']);
    Route::post('/modalities/{modality}/analyze', [ModalityController::class, 'analyze']);
});