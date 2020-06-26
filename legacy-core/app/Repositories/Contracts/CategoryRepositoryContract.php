<?php

namespace App\Repositories\Contracts;

use App\Models\Category;

interface CategoryRepositoryContract
{
    public function index();
    public function getHierarchy();
    public function create(array $input);
    public function show($id);
    public function update(array $input);
    public function associate(array $categories, $object);
}
