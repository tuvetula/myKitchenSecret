<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(!Auth::user()->is_admin){

            Log::info('The user '.Auth::user()->name.' '.Auth::user()->first_name.' with id '.Auth::id().' tried to access to unauthorized route');

            return response()->json([
                'success' => false,
                'message' => 'forbidden access'
            ],Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
