<?php namespace App\Http\Middleware\Api;

class OrgMiddleware
{
    public function handle($request, \Closure $next)
    {
        // DISABLED FOR NOW
        // $orgId = request()->headers->get('X-Cp-Org-Id');
        // if (empty($orgId)) {
        //     return response()->json('Bad request', 400);
        //     abort(400);
        // }

        return $next($request);
    }
}
