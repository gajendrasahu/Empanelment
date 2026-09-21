<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class EmailPasswordRequired implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return empty($value) || !empty(request()->input('emailpw'));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'EMAIL PASSWORD REQUIRED.';
    }
}
