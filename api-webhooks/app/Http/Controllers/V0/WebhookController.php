<?php

namespace App\Http\Controllers\V0;

use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Webhook;

class WebhookController extends Controller
{

    public function index(Request $request)
    {
        $this->validate(
            $request,
            [
                'page' => 'sometimes|integer',
                'per_page' => 'sometimes|integer',
                'event' => 'string',
                'active' => 'boolean',
                'suspended' => 'boolean',
                'sort_by' => 'sometimes|string|in:name,-name,event,-event'
            ]
        );
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        if (!$isAdmin) {
            abort(403, 'Admin only');
        }
        $params = $request->only([
            'per_page', 'page', 'active', 'suspended', 'event', 'sort_by'
        ]);
        $perPage = (isset($params['per_page']) ? $params['per_page'] : 25);
        $page = (isset($params['page']) ? $params['page'] : 1);

        $query = Webhook::select('*');
        if (!empty($params['event'])) {
            $query->where('event', '=', $params['event']);
        }
        if (isset($params['active'])) {
            $query->where('active', '=', $params['active']);
        }
        if (isset($params['suspended'])) {
            $query->where('suspend_until', ($params['suspended'] ? '>' : '<='), DB::raw('now()'));
        }
        if (isset($params['sort_by'])) {
            $this->determineSortByOrder($params);
            $inOrder = 'asc';
            if (isset($params['in_order'])) {
                $inOrder = $params['in_order'];
            }
            $query->orderBy($params['sort_by'], $inOrder);
        }
        $webhooks = $query->paginate($perPage, $page);

        return response()->json($webhooks);
    }

    public function show(Request $request, $id)
    {
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        if (!$isAdmin) {
            abort(403, 'Admin only');
        }
        $webhook = Webhook::where('id', $id)->first();
        if ($webhook === null) {
            abort(404, 'Webhook missing');
        }

        return response()->json($webhook);
    }

    public function create(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => 'required|string',
                'event' => 'required|string',
                'url' => 'required|url',
                'config.auth' => 'filled',
                'config.auth.type' => 'required|in:none,sha256',
                'config.auth.secret' => 'required_if:config.auth.type,sha256',
                'active' => 'boolean',
                'suspend_until' => 'nullable|date'
            ]
        );
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        if (!$isAdmin) {
            abort(403, 'Admin only');
        }

        $data = $request->only(
            [
                'name',
                'event',
                'config',
                'url',
                'active',
                'suspend_until',
            ]
        );
        $webhook = Webhook::create($data);

        return response()->json($webhook, 201);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'string',
            'event' => 'string',
            'url' => 'url',
            'config.auth' => 'filled',
            'config.auth.type' => 'required|in:none,sha256',
            'config.auth.secret' => 'required_if:config.auth.type,sha256',
            'active' => 'boolean',
            'suspend_until' => 'nullable|date'
        ]);
        $data = $request->only(['name', 'event', 'url', 'config', 'active', 'suspend_until']);

        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        if (!$isAdmin) {
            abort(403, 'Admin only');
        }

        $webhook = Webhook::where('id', $id)->first();
        if ($webhook == null) {
            abort(404, 'Webhook missing');
        }
        $webhook->update($data);

        return response()->json($webhook);
    }

    public function delete(Request $request, $id)
    {
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        if (!$isAdmin) {
            abort(403, 'Admin only');
        }

        $webhook = Webhook::where('id', $id)->first();
        if ($webhook == null) {
            abort(404, 'Webhook missing');
        }

        $webhook->delete();
        return response(null, 202);
    }

    private function determineSortByOrder(&$params)
    {
        if (strpos($params['sort_by'], '-') === 0) {
            $params['sort_by'] = str_replace('-', '', $params['sort_by']);
            $params['in_order'] = 'desc';
        }
    }
}
