<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;

class CombinationValidator
{
    /**
     * @return array{valid: bool, errors: array<int, string>}
     */
    public function validate(LotteryModality $modality, array $numbers): array
    {
        $errors = [];

        if (count($numbers) < $modality->bet_min_count) {
            $errors[] = "A combinação deve ter pelo menos {$modality->bet_min_count} números.";
        }

        if (count($numbers) > $modality->bet_max_count) {
            $errors[] = "A combinação deve ter no máximo {$modality->bet_max_count} números.";
        }

        foreach ($numbers as $number) {
            if (!is_int($number)) {
                $errors[] = 'Todos os números devem ser inteiros.';
                break;
            }

            if ($number < $modality->min_number || $number > $modality->max_number) {
                $errors[] = "Os números devem estar entre {$modality->min_number} e {$modality->max_number}.";
                break;
            }
        }

        if (!$modality->allows_repetition && count($numbers) !== count(array_unique($numbers))) {
            $errors[] = 'A combinação não pode conter números repetidos.';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }
}