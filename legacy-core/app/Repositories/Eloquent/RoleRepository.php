<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

class RoleRepository implements RoleRepositoryContract
{
    use CommonCrudTrait;

    /**
     * Finds roles creatable by admin.
     *
     * @return Role
     */
    public function adminCreatableRoles()
    {
        if (auth()->user()->hasRole('Superadmin')) {
            return Role::whereIn('name', ['Customer', 'Admin', 'Superadmin'])->get();
        }
        return Role::whereIn('name', ['Customer', 'Admin'])->get();
    }

    /**
     * Finds a role by name.
     *
     * @return Role
     */
    public function findIdByName(string $name)
    {
        $role = Role::where('name', $name)->first();
        if ($role) {
            return $role->id;
        }
        return null;
    }

    /**
     * Returns all roles but sets role checked to true if a product
     * has that role. Used for the product edit/create page.
     *
     * @return Role
     */
    public function findCheckedRoles($allRoles, $productRoles)
    {
        foreach ($allRoles as $singleRole) {
            $checked = false;
            foreach ($productRoles as $role) {
                if ($singleRole->id === $role->id) {
                    $singleRole->checked = true;
                    $checked = true;
                }
            }
            if ($checked === false) {
                $singleRole->checked = false;
            }
        }
        // unsetting roles that are not relevant

        return $allRoles;
    }
}
