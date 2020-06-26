<?php

namespace App\Repositories\Contracts;

interface OrderRepositoryContract
{
    public function buildOrderIndexQuery($request);
}
