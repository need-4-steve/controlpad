<?php
namespace App\Repositories\Contracts;

interface ReservedItemRepositoryContract
{
    public function create($line, $user_id);
    public function createAvailable($line, $user_id);
    public function update($line, $user_id, $reserved_item);
    public function updateAvailable($line, $user_id, $reserved_item);
}
