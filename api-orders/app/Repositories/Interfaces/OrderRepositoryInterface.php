<?php

namespace App\Repositories\Interfaces;

/**
 * Order Repository Interface
 */
interface OrderRepositoryInterface
{
    public function index($request);
    public function orderById($id, array $params);
}
