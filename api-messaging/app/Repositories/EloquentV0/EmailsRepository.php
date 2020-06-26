<?php

namespace App\Repositories\EloquentV0;

use App\Repositories\Interfaces\EmailsRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use App\Email;
use App\EmailLog;
use Carbon\Carbon;

class EmailsRepository implements EmailsRepositoryInterface
{
    public function index($orgId)
    {
        $email = Email::where('org_id', $orgId)->get();
        if($email === null) {
            return 'Not found';
        }
        return $email;
    }

    public function show($title, $orgId)
    {
        $email = Email::where('title', $title)->where('org_id', $orgId)->first();
        return $email;
    }

    public function updateEmail($title, $request)
    {
        $email = Email::where('title', $title)->where('org_id', $request['org_id'])->first();
        if($email === null) {
          return false;
        }
        if($email['standard']) {
          unset($request['title']);
        }
        return $email->update($request);
    }

    public function create($request)
    {
        return Email::create(
            ['subject' => $request->subject,
              'body' => $request->body,
              'title' => $request->title,
              'send_email' => $request->send_email,
              'display_name' =>$request->display_name,
              'org_id' => $request->user->org_id]);
    }

    public function delete($title, $orgId)
    {
        $email = Email::where('title', $title)->where('standard', 0)->where('org_id', $orgId)->delete();
        return $email;

    }

    public function logEmailIndex($type, $request) : Paginator
    {
      switch ($type) {
          case 'all':
              $logs = EmailLog::where('org_id', $request['org_id']);
              return $this->handleStandardParamsAndPaginate($logs, $request);
          case 'success':
              $logs = EmailLog::where('success', true)->where('org_id', $request['org_id']);
              return $this->handleStandardParamsAndPaginate($logs, $request);
          case 'failure':
              $logs = EmailLog::where('success', false)->where('org_id', $request['org_id']);
              return $this->handleStandardParamsAndPaginate($logs, $request);
          default:
              return EmailLog::all();
      }
    }

    public function removeEmailLogs()
    {
        $emailLogs = EmailLog::where('created_at', '<', Carbon::now()->subDays(90))->delete();
    }

    private function handleStandardParamsAndPaginate(Builder $query, array $params) : Paginator
    {
        $inOrder = 'asc';
        if (isset($params['in_order'])) {
            $inOrder = $params['in_order'];
        }
        if (isset($params['start_date']) && isset($params['end_date'])) {
            $query->whereBetween('created_at', [$params['start_date'], $params['end_date']]);
        }
        if (isset($params['sort_by'])) {
              $query->orderBy($params['sort_by'], $inOrder);
          }
        // paginate
        if (isset($params['per_page'])) {
            $results = $query->simplePaginate($params['per_page']);
        } else {
            $results = $query->simplePaginate(200);
        }
        return $results;
    }
}
