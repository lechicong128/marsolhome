<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public $request;
    public $user;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $lang = session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang ?? 'vi');
        App::setLocale($lang);
    }
}
