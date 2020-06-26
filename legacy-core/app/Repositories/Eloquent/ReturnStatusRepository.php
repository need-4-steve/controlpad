<?php

namespace App\Repositories\Eloquent;

use App\Models\ReturnStatus;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

class ReturnStatusRepository
{
    use CommonCrudTrait;

    /**
     * Create a new instances of ReturnStatusRepository
     *
     * @param array $inputs
     * @return bool|ReturnStatusRepository
     */
    public function create(array $inputs = [])
    {
        //
    }
}
