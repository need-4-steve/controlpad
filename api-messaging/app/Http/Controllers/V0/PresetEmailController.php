<?php

namespace App\Http\Controllers\V0;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Interfaces\EmailsRepositoryInterface;
use App\Services\EmailService;
use App\Email;

class PresetEmailController extends Controller
{
  private $emailsRepo;
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(EmailsRepositoryInterface $emailsRepo, EmailService $varables)
  {
      $this->emailsRepo = $emailsRepo;
      $this->varables = $varables;
  }

  public function index(Request $request)
  {
      $orgId = $request->user->orgId;
      $email = $this->emailsRepo->index($orgId);
      if($email === null) {
          return response()->json('Not found', 404);
      }
      return response()->json($email, 200);

  }

  public function show(Request $request, $title)
  {
      $orgId = $request->user->orgId;
      if (!$orgId) {
        $request = $request->all();
        $orgId = $request['user']['org_id'];
      }
      $email = $this->emailsRepo->show($title, $orgId);
      if($email === null) {
          return response()->json('Not found', 404);
      }
      return response()->json($this->emailsRepo->show($title, $orgId),200);
  }

  public function updateEmail(Request $request, $title)
  {
      $orgId = $request->user->orgId;
      $request = $request->all();
      $request['org_id'] = $orgId;
      $email = $this->emailsRepo->updateEmail($title, $request);
      if(!$email) {
        return response()->json('Email did not update', 404);
      } else {
        return response()->json('Success',200);
      }
  }

  public function showExampleEmail(Request $request, $title)
  {
      $request = $request->all();
      $info = $this->varables->buildExample($title, $request);
      $varables = $this->varables->getVariables($title);
      $content = $this->varables->parseText($info, $varables, $request);
      return response()->json(['content' => $content, 'varables' =>$varables], 200);

  }
}
