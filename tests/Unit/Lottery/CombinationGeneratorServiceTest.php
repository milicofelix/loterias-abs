<?php

use App\Models\LotteryModality;
use App\Services\Lottery\CombinationGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('gera uma combinação quina válida com tamanho padrão', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $numbers = app(CombinationGeneratorService::class)->generate($quina);

    expect($numbers)->toHaveCount(5)
        ->and($numbers)->toBe(array_values(array_unique($numbers)))
        ->and($numbers)->toBeSorted()
        ->and(min($numbers))->toBeGreaterThanOrEqual(1)
        ->and(max($numbers))->toBeLessThanOrEqual(80);
});

it('Gera uma combinação mega sena válida com tamanho padrão.', function () {
    $mega = LotteryModality::factory()->megaSena()->create();

    $numbers = app(CombinationGeneratorService::class)->generate($mega);

    expect($numbers)->toHaveCount(6)
        ->and($numbers)->toBe(array_values(array_unique($numbers)))
        ->and($numbers)->toBeSorted()
        ->and(min($numbers))->toBeGreaterThanOrEqual(1)
        ->and(max($numbers))->toBeLessThanOrEqual(60);
});

it('pode gerar uma combinação com contagem personalizada dentro dos limites da modalidade', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $numbers = app(CombinationGeneratorService::class)->generate($quina, [
        'count' => 7,
    ]);

    expect($numbers)->toHaveCount(7)
        ->and($numbers)->toBe(array_values(array_unique($numbers)))
        ->and($numbers)->toBeSorted();
});

it('Lança uma exceção quando a contagem solicitada for inferior ao mínimo da modalidade.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    app(CombinationGeneratorService::class)->generate($quina, [
        'count' => 4,
    ]);
})->throws(InvalidArgumentException::class, 'A quantidade deve estar entre 5 e 15.');

it('Lança uma exceção quando a quantidade de solicitações for maior que o máximo permitido pela modalidade.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    app(CombinationGeneratorService::class)->generate($quina, [
        'count' => 16,
    ]);
})->throws(InvalidArgumentException::class, 'A quantidade deve estar entre 5 e 15.');

it('pode impor restrições de número par', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $numbers = app(CombinationGeneratorService::class)->generate($quina, [
        'count' => 5,
        'min_even' => 2,
        'max_even' => 2,
    ]);

    $evenCount = count(array_filter($numbers, fn (int $number) => $number % 2 === 0));

    expect($numbers)->toHaveCount(5)
        ->and($evenCount)->toBe(2);
});

it('Lança uma exceção quando até mesmo as restrições são impossíveis.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    app(CombinationGeneratorService::class)->generate($quina, [
        'count' => 5,
        'min_even' => 4,
        'max_even' => 2,
    ]);
})->throws(InvalidArgumentException::class, 'As restrições de números pares são inválidas.');

it('pode evitar longas sequências consecutivas', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $numbers = app(CombinationGeneratorService::class)->generate($quina, [
        'count' => 5,
        'avoid_consecutive_run_greater_than' => 2,
    ]);

    $longestRun = 1;
    $currentRun = 1;

    for ($i = 1; $i < count($numbers); $i++) {
        if ($numbers[$i] === $numbers[$i - 1] + 1) {
            $currentRun++;
            $longestRun = max($longestRun, $currentRun);
        } else {
            $currentRun = 1;
        }
    }

    expect($longestRun)->toBeLessThanOrEqual(2);
});