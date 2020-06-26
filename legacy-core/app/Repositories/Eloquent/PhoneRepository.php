<?php

namespace App\Repositories\Eloquent;

use App\Models\Phone;
use App\Models\User;

class PhoneRepository
{
    public function createOrUpdate(array $input, int $user_id)
    {
        $phone = Phone::firstOrNew([
            'phonable_id' => $user_id,
            'phonable_type' => User::class
        ]);
        $phone->number = $input['number'];
        return $phone->save();
    }
}
