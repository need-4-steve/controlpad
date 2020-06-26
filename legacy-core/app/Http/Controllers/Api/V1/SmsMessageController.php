<?php namespace App\Http\Controllers\Api\V1;

use Response;
use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Models\User;
use App\Models\Lead;
use DB;

class SmsMessageController extends Controller
{

    /**
    * This gets all the sms messages.
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $smsMessages = SmsMessage::all();
        foreach ($smsMessages as $smsMessage) {
            if (strtotime($smsMessage['created_at']) >= (time() - Cache::get('settings.new_time_frame'))) {
                $smsMessage['new'] = 1;
            }
        }
        return Response::json($smsMessages);
    }
}
