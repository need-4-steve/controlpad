<?php

namespace App\Repositories\EloquentV0;

use App\Repositories\Interfaces\ReservationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use App\Models\Reservation;
use CPCommon\Pid\Pid;
use Carbon\Carbon;
use DB;

class ReservationRepository implements ReservationInterface
{
    const RESERVE_QUERY = "INSERT INTO reservations(inventory_id, group_pid, transaction_id, reservation_pid, quantity, expires_at)".
        " (SELECT IF(((available.avail - reserved.res) > 0), ?, NULL), ?, ?, ?, IF((available.avail - reserved.res) > ?, ?, (available.avail - reserved.res)), ?".
        " FROM (SELECT COALESCE(SUM(reserved.quantity), 0) AS res FROM reservations AS reserved WHERE inventory_id = ? AND expires_at > NOW()) AS reserved,".
        " (SELECT COALESCE(SUM(inventories.quantity_available), 0) AS avail FROM inventories WHERE inventories.id = ? AND inventories.deleted_at IS NULL) AS available".
        ")";

    const RESERVE_TRANSACTION_QUERY = "INSERT INTO reservations(inventory_id, group_pid, transaction_id, reservation_pid, quantity, expires_at) ".
        " (SELECT IF(((available.avail - reserved.res - ?) >= 0), ?, NULL), ?, ?, ?, ?, ?".
        " FROM (SELECT COALESCE(SUM(reserved.quantity), 0) AS res FROM reservations AS reserved WHERE inventory_id = ? AND expires_at > NOW()) AS reserved,".
        " (SELECT COALESCE(SUM(inventories.quantity_available), 0) AS avail FROM inventories WHERE inventories.id = ? AND inventories.deleted_at IS NULL) AS available".
        ")";

    public function index(Request $params)
    {
        $per_page = ($params->has('per_page') && $params['per_page'] <= 200) ? $params['per_page'] : 200;
        return Reservation::simplePaginate($per_page);
    }

    public function show($reservationGroupId)
    {
        return Reservation::where('group_pid', $reservationGroupId)->get();
    }

    public function create($reservationRequest)
    {
        $errors = [];
        $reservationGroupId = Pid::create();
        $reservations = null;
        DB::transaction(
            function () use ($reservationRequest, &$errors, &$reservationGroupId, &$reservations) {
                $this->insertReservations($reservationRequest, $errors, $reservationGroupId, $reservations);
            }
        );
        return ["reservation_group_id" => $reservationGroupId, "errors" => $errors, "reservations" => $reservations];
    }

    public function update($reservationGroupId, $reservationRequest)
    {
        $errors = [];
        $reservations = null;
        DB::transaction(
            function () use ($reservationRequest, &$errors, &$reservationGroupId, &$reservations) {
                // Clear old reservations
                $deleted = Reservation::where('group_pid', $reservationGroupId)->delete();
                if ($deleted < 1) {
                    abort(404, 'Reservation group not found.');
                }
                $this->insertReservations($reservationRequest, $errors, $reservationGroupId, $reservations);
            }
        );
        return ['errors' => $errors, 'reservations' => $reservations];
    }

    private function insertReservations($reservationRequest, &$errors, &$reservationGroupId, &$reservations)
    {
        $expiresAt = Carbon::now()->addMinutes(5);
        if (!empty($reservationRequest['transactions'])) {
            foreach ($reservationRequest['transactions'] as $transaction) {
                try {
                    DB::transaction(
                        function () use ($reservationGroupId, $transaction, $expiresAt) {
                            foreach ($transaction['inventories'] as $inventory) {
                                $this->reserveTransaction(
                                    $inventory['id'],
                                    $inventory['quantity'],
                                    $reservationGroupId,
                                    $transaction['transaction_id'],
                                    $expiresAt
                                );
                            }
                        },
                        5
                    );
                } catch (\Illuminate\Database\QueryException $e) {
                    $this->getTransactionAvailability($transaction);
                    array_push($errors, ['message' => 'Out of inventory', 'transaction' => $transaction]);
                }
            }
        }
        if (!empty($reservationRequest['inventories'])) {
            foreach ($reservationRequest['inventories'] as $inventory) {
                try {
                    $pid = $this->reserve(
                        $inventory['id'],
                        $inventory['quantity'],
                        $reservationGroupId,
                        (!empty($inventory['transaction_id']) ? $inventory['transaction_id'] : null),
                        $expiresAt
                    );
                    $reserved = Reservation::where('reservation_pid', $pid)->first();
                    if ($reserved->quantity != $inventory['quantity']) {
                        array_push($errors, "Not enough inventory. Reserved ".$reserved->quantity."of ".$reserved->inventory_id);
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    array_push($errors, "Out of inventory ".$inventory['id']);
                }
            }
        }
        $reservations = Reservation::where('group_pid', $reservationGroupId)->get();
    }

    // Use pointers to allow direct modification
    private function getTransactionAvailability(&$transaction)
    {
        $inventoryMap = [];
        foreach ($transaction['inventories'] as &$inventory) {
            // Map a pointer to the inventory object so we can update it directly
            $inventoryMap[$inventory['id']] = &$inventory;
        }

        $availables = DB::table('inventories AS i')
          ->selectRaw('i.id, i.quantity_available - COALESCE(SUM(res.quantity), 0) AS quantity')
          ->leftJoin('reservations AS res', function ($join) {
              $join->on('res.inventory_id', '=', 'i.id')
              ->on('res.expires_at', '>', DB::raw('NOW()'));
          })
          ->whereIn('i.id', array_keys($inventoryMap))
          ->groupBy('i.id')
          ->get();

        foreach ($availables as &$available) {
            $inventoryMap[$available->id]['available'] = $available->quantity;
        }
    }

    public function refresh($reservationGroupId, $increment = 0)
    {
        $query = Reservation::where('group_pid', '=', $reservationGroupId)->where('expires_at', '>', Carbon::now());
        if ($increment > 0) {
            return $query->update(['expires_at' => DB::raw('DATE_SUB(`expires_at`, INTERVAL ' . $increment . ' MINUTE)')]);
        } else {
            return $query->update(['expires_at' => Carbon::now()->addMinutes(5)]);
        }
    }

    public function isExpired($reservationGroupId)
    {
        $result = Reservation::selectRaw('(MIN(expires_at) < NOW()) AS expired')->where('group_pid', '=', $reservationGroupId)->first();
        if (!isset($result->expired)) {
            abort(404, 'Reservation not found');
        } else {
            return $result->expired;
        }
    }

    public function destroy($reservationGroupId)
    {
        return Reservation::where('group_pid', $reservationGroupId)->delete();
    }

    public function transfer($reservationGroupId, $userId, $userPid)
    {
        //Create or Increment destination inventory
        $incrementQuery = "INSERT INTO inventories".
            "(item_id, quantity_available, quantity_staged, user_id, owner_id, user_pid, owner_pid)".
            " SELECT i.item_id, r.quantity, 0, ?, ?, ?, ? FROM reservations AS r".
            " JOIN inventories AS i ON r.inventory_id = i.id".
            " JOIN items ON items.id = i.item_id".
            " JOIN products AS p ON p.id = items.product_id AND p.resellable = 1".
            " WHERE r.group_pid = ? AND p.resellable = 1".
            " ON DUPLICATE KEY UPDATE inventories.quantity_available = inventories.quantity_available + r.quantity, updated_at = NOW()";

        //Decrement source inventory
        $decrementQuery = "UPDATE inventories AS i JOIN".
            " (SELECT inventory_id, SUM(quantity) AS qty FROM reservations WHERE group_pid = ? GROUP BY inventory_id) AS r ON i.id = r.inventory_id".
            " SET i.quantity_available = i.quantity_available - r.qty, i.updated_at = NOW()";

        //Record History for increment
        $incrementHistoryQuery = "INSERT INTO inventory_history".
            " (inventory_id, inventory_user_id, item_id, before_quantity_available, after_quantity_available, before_quantity_staged, after_quantity_staged, application, auth_user_id, request_id, request_path, created_at, updated_at)".
            " SELECT i.id, i.user_id, i.item_id, (i.quantity_available - r.qty), i.quantity_available, i.quantity_staged, i.quantity_staged, 'Inventory API', ?, ?, ?, NOW(), NOW()".
            " FROM inventories as io".
            " JOIN inventories as i ON io.item_id = i.item_id".
            " JOIN (SELECT inventory_id, SUM(quantity) AS qty FROM reservations WHERE group_pid = ? GROUP BY inventory_id) AS r ON r.inventory_id = io.id".
            " JOIN items ON items.id = i.item_id".
            " JOIN products AS p ON p.id = items.product_id AND p.resellable = 1".
            " WHERE i.user_id = ? AND p.resellable = 1";

        //Record History for decrement
        $decrementHistoryQuery = "INSERT INTO inventory_history".
            " (inventory_id, inventory_user_id, item_id, before_quantity_available, after_quantity_available, before_quantity_staged, after_quantity_staged, application, auth_user_id, request_id, request_path, created_at, updated_at)".
            " SELECT i.id, i.user_id, i.item_id, (i.quantity_available + r.qty), i.quantity_available, i.quantity_staged, i.quantity_staged, 'Inventory API', ?, ?, ?, NOW(), NOW()".
            " FROM inventories AS i JOIN (SELECT inventory_id, SUM(quantity) AS qty FROM reservations WHERE group_pid = ? GROUP BY inventory_id) AS r ON r.inventory_id = i.id";

        $authId = isset(app('request')->user->id) ? app('request')->user->id : null;
        $requestId = app('request')->headers->get('X-Cp-Request-Id');
        $requestPath = app('request')->path();

        DB::transaction(
            function () use ($incrementQuery, $decrementQuery, $incrementHistoryQuery, $decrementHistoryQuery, $userId, $userPid, $reservationGroupId, $requestId, $requestPath, $authId) {
                if ($userPid !== null) {
                    DB::statement($incrementQuery, [$userId, $userId, $userPid, $userPid, $reservationGroupId]);
                    DB::statement($incrementHistoryQuery, [$authId, $requestId, $requestPath, $reservationGroupId, $userId]);
                }
                DB::statement($decrementQuery, [$reservationGroupId]);
                DB::statement($decrementHistoryQuery, [$authId, $requestId, $requestPath, $reservationGroupId]);
                Reservation::where('group_pid', $reservationGroupId)->delete();
            },
            5
        );
        return "true";
    }

    public function reserve($inventoryId, $quantity, $reservationGroupId, $transactionId = true, $expiresAt = null)
    {
        $pid = Pid::create();
        DB::statement(
            ReservationRepository::RESERVE_QUERY,
            [$inventoryId, $reservationGroupId, $transactionId, $pid, $quantity, $quantity, $expiresAt, $inventoryId, $inventoryId]
        );
        return $pid;
    }

    public function reserveTransaction($inventoryId, $quantity, $reservationGroupId, $transactionId = true, $expiresAt = null)
    {
        $pid = Pid::create();
        DB::statement(
            ReservationRepository::RESERVE_TRANSACTION_QUERY,
            [$quantity, $inventoryId, $reservationGroupId, $transactionId, $pid, $quantity, $expiresAt, $inventoryId, $inventoryId]
        );
        return $pid;
    }
}
