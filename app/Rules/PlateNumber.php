<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PlateNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^[A-Za-z0-9\s\-ĂăÂâÎîȘșŞşȚțŢţ]+$/u', $value)) {
            $fail('Numărul de înmatriculare poate conține doar litere, cifre, spații și caracterul -.');

            return;
        }

        $normalized = User::normalizePlateNumber($value);

        if ($normalized === '') {
            $fail('Câmpul număr de înmatriculare este obligatoriu.');

            return;
        }

        if (strlen($normalized) > 15) {
            $fail('Numărul de înmatriculare trebuie să aibă maximum 15 caractere alfanumerice.');
        }
    }
}
