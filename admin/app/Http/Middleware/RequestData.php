<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class RequestData
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
        $locale = $request->header('locale') ?? (session('lang') ?? 'vi');
        if (!in_array($locale, ['vi', 'en', 'cn', 'th', 'zh','kr','ja'])) {
            $locale = 'vi';
        }

        $request->_locale = $locale ;
        App::setLocale($locale);

        return $next($request);
    }
}
