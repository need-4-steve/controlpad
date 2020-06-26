<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\AuthRepository;

class RoleController extends Controller
{
    protected $roleRepo;
    protected $settings;
    protected $authRepo;

    /**
     * RoleController constructor.
     *
     * @param RoleRepository $roleRepo
     * @param AuthRepository $authRepo
     */
    public function __construct(
        RoleRepository $roleRepo,
        AuthRepository $authRepo
    ) {
        $this->roleRepo = $roleRepo;
        $this->settings = app('globalSettings');
        $this->authRepo = $authRepo;
    }

    /**
     * Return an index of roles depending on the auth user and
     * the setting on whether or not a rep can create product.
     * This is used for the product edit/create page.
     *
     * @return Response
     */
    public function getIndex()
    {
        if ($this->settings->getGlobal('reseller_create_product', 'show') && !$this->authRepo->isOwnerAdmin()) {
            $roles = [
                $this->roleRepo->find(3)
            ];
        } else {
            $roles = $this->roleRepo->all();
        }
        return response()->json($roles, HTTP_SUCCESS);
    }

    /**
     * Finds roles creatable by admin.
     *
     * @return Response
     */
    public function adminCreatableRoles()
    {
        $roles = $this->roleRepo->adminCreatableRoles();
        return response()->json($roles, HTTP_SUCCESS);
    }
}
