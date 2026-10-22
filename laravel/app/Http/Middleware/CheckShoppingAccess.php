<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckShoppingAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->isCustomer()) {
            abort(403, 'Chỉ khách hàng mới được sử dụng chức năng mua hàng.');
        }

        return $next($request);
    }
}
