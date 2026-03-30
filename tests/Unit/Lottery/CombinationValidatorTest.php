<?php

use App\Models\LotteryModality;
use App\Services\Lottery\CombinationValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('aceita uma combinação quina válida', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 33, 80]);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

it('rejeita uma combinação quina com menos de 5 números', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 33]);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('A combinação deve ter pelo menos 5 números.');
});

it('rejeita uma combinação de quina com número acima do intervalo permitido', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 33, 81]);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Os números devem estar entre 1 e 80.');
});

it('Rejeita números repetidos quando a modalidade não permite repetição.', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 17, 80]);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('A combinação não pode conter números repetidos.');
});

it('aceita uma combinação mega sena válida', function () {
    $modality = LotteryModality::factory()->megaSena()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 33, 45, 60]);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

it('rejeita número mega sena acima do intervalo', function () {
    $modality = LotteryModality::factory()->megaSena()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 33, 45, 61]);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Os números devem estar entre 1 e 60.');
});