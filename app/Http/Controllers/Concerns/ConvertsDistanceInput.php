<?php

namespace App\Http\Controllers\Concerns;

trait ConvertsDistanceInput
{
    /**
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    protected function convertDistanceFieldsToKm(array $validated, array $fields): array
    {
        $user = auth()->user();

        foreach ($fields as $field) {
            if (! array_key_exists($field, $validated) || $validated[$field] === null) {
                continue;
            }

            $validated[$field] = $user->displayToKm((float) $validated[$field]);
        }

        return $validated;
    }
}
