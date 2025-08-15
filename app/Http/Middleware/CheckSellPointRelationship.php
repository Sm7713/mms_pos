<?php

namespace App\Http\Middleware;

use App\Models\sellPoint;
use App\Models\User;
use App\MyHelper\ApiResponce;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Finder\Glob;

class CheckSellPointRelationship
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
        // return $request->user()->sellPoint;
       if($request->user() && $request->user()->sellPoint){
           return $next($request);
       }else{
           return ApiResponce::sendResponce(403,'Your Account n\'t Sell Point');
       }
    }
}
