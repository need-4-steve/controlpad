<?php

namespace App\Repositories\Eloquent;

use App\Models\CustomEmail;
use App\Models\EmailLogs;
use App\Models\User;
use App\Repositories\Contracts\EmailRepositoryContract;
use App\Repositories\Eloquent\UserSettingsRepository;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use Carbon\Carbon;

class EmailRepository implements EmailRepositoryContract
{
    /* @var \App\Repositories\Eloquent\OrderRepository */
    protected $orderRepo;

    use CommonCrudTrait;

    public function __construct(UserSettingsRepository $userSettingsRepo)
    {
        $this->userSettingsRepo = $userSettingsRepo;
    }

    public function customEmailIndex()
    {
        $emails = CustomEmail::all();
        return $emails;
    }

    public function update($title, $request)
    {
        $email = CustomEmail::where('title', $title)->first();
        $email->update($request);
        return $email;
    }

    public function emailLogs($request, $paginate = true)
    {
        $role_id = $request['status'];
        $timezone = $this->userSettingsRepo->getUserTimeZone(auth()->user()->id);
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)
                                    ->startOfDay()
                                    ->setTimezone('UTC')
                                    ->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)
                                    ->endOfDay()
                                    ->setTimezone('UTC')
                                    ->format('Y-m-d H:i:s');
        $emailLogs = EmailLogs::whereBetween('email_log.created_at', [$request['start_date'], $request['end_date']])
            ->select('email_log.*', 'users.id as user_id')
            ->join('users', function ($join) use ($role_id) {
                $join->on('users.email', '=', 'email_log.to');
                if ($role_id != 'all') {
                    $join->where('users.role_id', $role_id);
                }
            });
        if (!empty($request['search_term'])) {
            $emailLogs = $emailLogs
                ->where(function ($query) use ($request) {
                    $query->where('subject', 'LIKE', "%" . $request['search_term'] . "%")
                        ->orWhere('to', 'LIKE', "%" . $request['search_term'] . "%");
                });
        }
        if (empty($request['order'])) {
            $request['order'] = 'DESC';
        }
        if (!empty($request['column'])) {
            $emailLogs = $emailLogs->orderBy($request['column'], $request['order']);
        } else {
            $emailLogs = $emailLogs->orderBy('created_at', 'DESC');
        }
        if ($paginate) {
            return $emailLogs->paginate($request['per_page']);
        } else {
            return $emailLogs->get();
        }
    }
    public function removeEmailLogs()
    {
        $emailLogs = EmailLogs::where('updated_at', '<', Carbon::now()->subDays(90))->delete();
    }

    public function emailShow($title)
    {
        $email = CustomEmail::where('title', $title)->first();
        return $email;
    }
}
