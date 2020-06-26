<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RepEmail implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $email = app('db')->table('users')->select('email')->where('email', $value)->whereIn('role_id', [5,7,9])->first();
        if (isset($email)) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The email has already been taken.';
    }
}
