<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
class Customer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::check() && Auth::user()->role == 1)
        {
            
            return redirect()->route('admin');
        }
        
        elseif (Auth::check() && Auth::user()->role == 0)
        {
            
            return $next($request);
        }
        else {
          
            return redirect()->route('login');
        }
    }
}
