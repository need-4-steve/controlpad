<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Reservation;
use App\Repositories\Interfaces\ReservationInterface;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected $reservationRepo;

    public function __construct(ReservationInterface $reservationRepo)
    {
        $this->reservationRepo = $reservationRepo;
    }

    public function index(Request $request)
    {
        return response()->json($this->reservationRepo->index($request), 200);
    }

    public function show($reservationGroupId)
    {
        return response()->json([
                'reservation_group_id' => $reservationGroupId,
                'errors' => [],
                'reservations' => $this->reservationRepo->show($reservationGroupId)
            ]);
    }

    public function create(Request $request)
    {
        $this->validate($request, Reservation::$createRules);
        $reservationRequest = $request->only(['inventories','transactions']);
        $reservation = $this->reservationRepo->create($reservationRequest);

        if (!empty($reservation['reservations'])) {
            return response()->json($reservation);
        }
        return response()->json($reservation, 422);
    }

    public function update(Request $request, $reservationGroupId)
    {
        $this->validate($request, Reservation::$createRules);
        $reservationRequest = $request->only(['inventories','transactions']);
        $reservation = $this->reservationRepo->update($reservationGroupId, $reservationRequest);

        if (!empty($reservation['reservations'])) {
            return response()->json($reservation);
        }
        return response()->json($reservation, 422);
    }

    public function refresh($reservationGroupId)
    {
        if ($this->reservationRepo->refresh($reservationGroupId)) {
            return response()->json([
                    'reservation_group_id' => $reservationGroupId,
                    'errors' => [],
                    'reservations' => $this->reservationRepo->show($reservationGroupId)
                ]);
        } else {
            return response()->json(['reservation_group_id' => ['Expired']], 422);
        }
    }

    public function destroy($reservationGroupId)
    {
        return $this->reservationRepo->destroy($reservationGroupId);
    }

    public function transfer(Request $request)
    {
        $this->validate($request, ['reservation_group_id' => 'required', 'user_id' => 'required_with:user_pid']);
        $data = $request->only(['reservation_group_id', 'user_id', 'user_pid']);
        // Append user_pid if needed to allow old api calls to ommit user_pid
        if (isset($data['user_id']) && !isset($data['user_pid'])) {
            $data['user_pid'] = (new \App\Services\User\UserService)->getPidForId($data['user_id']);
        }
        // Make sure reservation isn't expired by refreshing it with an increment
        // Increment makes sure that update count works
        if ($this->reservationRepo->refresh($data['reservation_group_id'], 1) < 1) {
            return response()->json(['reservation_group_id' => ['Expired']], 422);
        }

        return $this->reservationRepo->transfer(
            $data['reservation_group_id'],
            isset($data['user_id']) ? $data['user_id'] : null,
            isset($data['user_pid']) ? $data['user_pid'] : null
        );
    }
}
