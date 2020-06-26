<?php

namespace App\Repositories\Contracts;

interface AuthRepositoryContract
{
    public function getOwner();
    public function getOwnerId();
    public function getStoreOwner();
    public function getStoreOwnerId();
}
