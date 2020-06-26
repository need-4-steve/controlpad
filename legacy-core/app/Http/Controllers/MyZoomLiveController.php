<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ZoomUser;
use App\Services\Zoom\ZoomService;

class MyZoomLiveController extends Controller
{
    public function __construct()
    {
        $this->zoomService = new ZoomService;
    }

    public function onWooComSubEvent(Request $request)
    {
        if ($request->headers->get('x-wc-webhook-event') != null && $request->headers->get('x-wc-webhook-resource') != null &&
            $request->headers->get('x-wc-webhook-resource') == 'subscription') {
            // Update a zoom user account base on subscription status
            //$events = ['created', 'updated', 'deleted', 'switch']
            $data = $request->all();
            if (!empty(env('ZOOM_WEBHOOK_SECRET')) && !empty($request->headers->get('x-wc-webhook-signature'))) {
                $sig = base64_encode(hash_hmac('sha256', json_encode($data), env('ZOOM_WEBHOOK_SECRET'), true));
                if ($sig != $request->headers->get('x-wc-webhook-signature')) {
                    app('log')->error('WooCom hook not authenticated', ['message' => 'WooCom hook not authenticated', 'body' => $data]);
                    return response('', 401);
                }
            }
            if (!empty($data['status'])) {
                switch ($data['status']) {
                    case 'active':
                        // Add user to master account
                        $active = true;
                        break;
                    case 'cancelled':
                        // Remove user from master account
                        $active = false;
                        break;
                    default:
                        app('log')->error('Zoom sub status not handled', ['message' => 'Zoom sub status not handle', 'body' => $data]);
                    case 'pending':
                    case 'on-hold':
                        return response('', 200);
                }
            } else {
                // No status to operate on, so ignore
                return response('', 200);
            }
            if (empty($data['customer_id']) || empty($data['billing']['email'])) {
                app('log')->error('WooCom hook missing required data', ['message' => 'WooCom hook missing required data', 'body' => $data]);
                return response('', 400);
            }
            if ($active) {
                // Find or create a user that is in woocom
                $user = User::firstOrCreate(
                    [
                        'email' => $data['billing']['email']
                    ],
                    [
                        'first_name' => $data['billing']['first_name'],
                        'last_name' => $data['billing']['last_name']
                    ]
                );
                $user->woocom_customer_id = $data['customer_id'];
                $this->zoomService->createZoomUser($user);
            } else {
                $zoomUser = ZoomUser::where('woocom_customer_id', '=', $data['customer_id'])->first();
                if ($zoomUser == null) {
                    app('log')->error('ZoomUser missing for subscription deactivate', ['body' => $data]);
                } else {
                    $this->zoomService->deleteZoomUser((object)['id' => $zoomUser->user_id]);
                }
            }
        }
        return response('', 200);
    }
}
