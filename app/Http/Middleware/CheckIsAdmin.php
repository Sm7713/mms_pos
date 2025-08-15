<?php

namespace App\Http\Middleware;

use App\MyHelper\ApiResponce;
use Closure;
use Illuminate\Http\Request;

class CheckIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->user() && $request->user()->setting){
            return $next($request);
        }else{
            return ApiResponce::sendResponce(403,'Your Account n\'t Admin');
        }
    }
}
