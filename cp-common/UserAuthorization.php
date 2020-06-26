<?php

namespace CPCommon;

use GuzzleHttp\Client;

class UserAuthorization
{
    public $authenticated = false;

    public $id = null;

    public $pid = null;

    public $role = null;

    public $permissions = [];

    public $expires = null;

    public function __construct(
        $authenticated,
        $decryptedToken = array(
            "sub" => null,
            "role" => null,
            "exp" => null,
            "orgId" => null,
            "userPid" => null
        )
    ) {
        $this->id = $decryptedToken['sub'];
        $this->role = $decryptedToken['role'];
        $this->expires = $decryptedToken['exp'];
        $this->orgId = $decryptedToken['orgId'];
        $this->pid = $decryptedToken['userPid'];
        $this->authenticated = $authenticated;
    }

    public function getOwnerID()
    {
        $adminRoles = ['Superadmin', 'Admin'];
        if (in_array($this->role, $adminRoles)) {
            return 1;
        }
        return $this->id;
    }

    public function hasRole(array $roles) : bool
    {
        if (in_array($this->role, $roles)) {
            return true;
        }
        return false;
    }

    public function assertAnyRole(array $roles)
    {
        if (!in_array($this->role, $roles)) {
            abort(403);
        }
    }

    public function hasAnyPermission($roles) : bool
    {
        if (in_array($this->role, $roles)) {
            return true;
        }
        return false;
    }

    public function assertAnyPermission($roles)
    {
        if (!in_array($this->role, $roles)) {
            abort(403);
        }
    }
}
