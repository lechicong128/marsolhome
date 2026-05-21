<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,...$roles): Response
    {
       if (Auth::guard('admin')->check()){
           $request->_locale = session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang);
           App::setLocale($request->_locale);
           $request->merge(['_locale' => $request->_locale]);
           if (empty($roles)) {
               return redirect('admin/dashboard');
           } else {
               $request->user = Auth::guard('admin')->user();
               return $next($request);
           }
       } else {
           return redirect('admin/login');
       }
    }
}
