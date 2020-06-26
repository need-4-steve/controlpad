<?php

namespace App\Repositories\Contracts;

use App\Models\InvoiceRepository;

interface InvoiceRepositoryContract
{
    public function find($token);
    public function create(array $inputs = [], array $itemsPivot = []);
}
