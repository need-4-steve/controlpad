<?php

namespace App\Http\Controllers\V0;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendEmailRequest;
use App\Http\Requests\EmailRequest;
use App\Jobs\SendMail;
use App\Repositories\Interfaces\EmailsRepositoryInterface;
use App\Services\EmailService;


class EmailsController extends Controller
{
  public function __construct(EmailService $emailService, EmailsRepositoryInterface $emailsRepo)
  {
      $this->emailService = $emailService;
      $this->emailRepo  = $emailsRepo;
  }
  public function sendEmail(Request $request)
  {
      $this->validateRequest(new SendEmailRequest, $request);
      $orgId = $request->user->orgId;
      $request = $request->all();
      if (!$orgId) {
        $orgId = $request['user']['org_id'];
      }
      $request['orgId'] = $orgId;
      if ($request['type'] === 'custom') {
          dispatch((new SendMail($request))->onQueue($org_id, 'default'));
      } else {
          $varables = $this->emailService->getVariables($request['title']);
          $info = $this->emailService->getUserInfo($request);
          $content = $this->emailService->parseText($info, $varables, $request);
          dispatch((new SendMail($content))->onQueue('default'));
      }
      return response()->json('Success', 200);
  }

  public function emailVariables($title)
  {
      $varables = $this->emailService->getVariables($title);
      if (!$varables) {
        return response()->json('Title not found', 404);
      }
      return response()->json($varables, 200);
  }

  public function create(Request $request)
  {
      $this->validateRequest(new EmailRequest, $request);
      $org_id = $request->user->orgId;
      $email = $this->emailRepo->create($request);
      return response()->json($email, 200);
  }

  public function delete(Request $request, $title)
  {
      $orgId = $request->user->orgId;
      return response()->json($this->emailRepo->delete($title, $orgId));
  }

  public function emailLogs($type, Request $request)
  {
      $orgId = $request->user->orgId;
      $request = $request->all();
      $request['org_id'] = $orgId;
      return response()->json($this->emailRepo->logEmailIndex($type, $request), 200);
  }

  public function removeOldlogs()
  {
      return response()->json($this->emailRepo->removeEmailLogs(), 200);
  }
}
