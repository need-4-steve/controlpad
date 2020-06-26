<?php namespace Test;

class TestAuthUser
{
    public $id;
    public $pid;
    public $role;

    public function __construct($id, $pid, $role)
    {
        $this->id = $id;
        $this->pid = $pid;
        $this->role = $role;
    }

    public function getOwnerPid()
    {
        return $this->pid;
    }

    public function getOwnerId()
    {
        return $this->id;
    }

    public function hasRole(array $roles)
    {
        return in_array($this->role, $roles);
    }
}
