<?php

namespace App\Repositories\Contracts;

use App\Models\Role;

interface RoleRepositoryContract
{
    public function adminCreatableRoles();
    public function findIdByName(string $name);
    public function findCheckedRoles($allRoles, $productRoles);
}
