<?php

use App\Models\LotteryModality;
use App\Services\Lottery\CombinationValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('accepts a valid quina combination', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 33, 80]);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

it('rejects a quina combination with less than 5 numbers', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 33]);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('A combinação deve ter pelo menos 5 números.');
});

it('rejects a quina combination with number above allowed range', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 33, 81]);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Os números devem estar entre 1 e 80.');
});

it('rejects repeated numbers when modality does not allow repetition', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 17, 80]);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('A combinação não pode conter números repetidos.');
});

it('accepts a valid mega sena combination', function () {
    $modality = LotteryModality::factory()->megaSena()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 33, 45, 60]);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

it('rejects mega sena number above range', function () {
    $modality = LotteryModality::factory()->megaSena()->create();

    $result = app(CombinationValidator::class)->validate($modality, [1, 5, 17, 33, 45, 61]);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Os números devem estar entre 1 e 60.');
});