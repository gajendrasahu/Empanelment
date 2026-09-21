<?php

namespace App\Services;

class PasswordService
{
    public function generatePassword(int $length = 10): string
    {
        $length = max(8, min($length, 10)); // Ensure length between 8-10

        $uppercase = chr(rand(65, 90)); // A-Z
        $number = chr(rand(48, 57));    // 0-9
        $specialChars = '!@#$%^&*()_+-=';
        $special = $specialChars[rand(0, strlen($specialChars) - 1)];

        // Remaining characters (lowercase letters)
        $remainingLength = $length - 3;
        $lowercase = '';
        for ($i = 0; $i < $remainingLength; $i++)
		{
            $lowercase .= chr(rand(97, 122)); // a-z
        }

        $password = str_shuffle($uppercase . $number . $special . $lowercase);

        return $password;
    }
}