<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Lottery\ModalityController;
use App\Http\Controllers\Lottery\GameController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::prefix('lottery')->group(function () {
    Route::get('/modalities', [ModalityController::class, 'index'])->name('lottery.modalities.index');

    Route::get('/modalities/{modality}', [ModalityController::class, 'show'])
        ->name('lottery.modalities.show');

    Route::post('/modalities/{modality}/generate', [ModalityController::class, 'generate']);

    Route::post('/modalities/{modality}/generate-smart', [ModalityController::class, 'generateSmart'])
        ->name('lottery.modalities.generate-smart');

    Route::post('/modalities/{modality}/analyze', [ModalityController::class, 'analyze']);

     Route::post('/modalities/{modality}/import-spreadsheet', [ModalityController::class, 'importSpreadsheet'])
        ->name('lottery.modalities.import-spreadsheet');

    Route::get('/modalities/{modality}/play', [GameController::class, 'play'])
        ->name('lottery.modalities.play');

    Route::get('/modalities/{modality}/history', [ModalityController::class, 'history'])
        ->name('lottery.modalities.history');
    
    Route::get('/modalities/{modality}/combination-history', [ModalityController::class, 'combinationHistory'])
        ->name('lottery.modalities.combination-history');
    
    Route::get('/modalities/{modality}/combination-history', [ModalityController::class, 'combinationHistory'])
        ->name('lottery.combination-history');

    Route::delete('/modalities/{modality}/combination-history/{item}', [ModalityController::class, 'destroyCombinationHistory'])
        ->name('lottery.combination-history.destroy');
    
    Route::delete('/modalities/{modality}/combination-history', [ModalityController::class, 'clearCombinationHistory'])
        ->name('lottery.combination-history.clear');
    
     Route::post('/modalities/{modality}/sync-results', [ModalityController::class, 'syncResults'])
        ->name('lottery.modalities.sync-results');
});