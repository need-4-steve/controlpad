<?php

namespace App\Repositories\Contracts;

interface CustomLinksRepositoryContract
{
    public function getIndexByType($types);
    public function create($request);
    public function delete($id);
}
