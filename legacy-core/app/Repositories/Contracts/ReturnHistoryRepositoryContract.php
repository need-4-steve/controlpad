<?php

namespace App\Repositories\Contracts;

use App\Models\ReturnHistory;

interface ReturnHistoryRepositoryContract
{
    public function create($status, array $inputs = []);
    public function update(ReturnHistory $returnHistory, array $inputs = []);
}
