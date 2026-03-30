<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('mega-sena')->group(function () {
    Route::get('/', [MegaSenaController::class, 'index'])->name('mega-sena.index');
    Route::get('/create', [MegaSenaController::class, 'create'])->name('mega-sena.create');
    Route::post('/store', [MegaSenaController::class, 'store'])->name('mega-sena.store');
    Route::get('/edit/{id}', [MegaSenaController::class, 'edit'])->name('mega-sena.edit');
    Route::put('/update/{id}', [MegaSenaController::class, 'update'])->name('mega-sena.update');
    Route::delete('/destroy/{id}', [MegaSenaController::class, 'destroy'])->name('mega-sena.destroy');
    Route::get('/aposta', [MegaSenaController::class, 'aposta'])->name('mega-sena.aposta');
    Route::get('/buscar/{numero}', [MegaSenaController::class, 'buscarPorConcurso'])->name('mega-sena.buscar');
    Route::middleware('auth')->get('/minhas-apostas', [ApostaController::class, 'minhasApostas'])->name('mega-sena.minhas-apostas');
});

// 🔒 Rotas protegidas (fora do prefixo)
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/apostas', [ApostaController::class, 'index'])->name('apostas.index');
    Route::post('/apostas', [ApostaController::class, 'store'])->name('apostas.store');
});

Route::middleware(['auth'])->prefix('sorteios')->group(function () {
    Route::get('/',        [SorteioController::class, 'index'])->name('sorteios.index');
    Route::get('/create',  [SorteioController::class, 'create'])->name('sorteios.create');
    Route::post('/',       [SorteioController::class, 'store'])->name('sorteios.store');
});

