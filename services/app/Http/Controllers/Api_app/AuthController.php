<?php

namespace App\Http\Controllers\Api_app;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class AuthController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
//        $_locale = $this->request->header('locale');
//        if(empty($_locale)) {
//            $locale = $request->_locale;
//        }
//        else {
//            $locale = $this->request->_locale;
//        }
        $locale = getLocale($request);

        $_locale = 'vi';
        if($locale == 'vi') {
            $_locale = 'vi';
        }
        else if($locale == 'en') {
            $_locale = 'en';
        }
        else if($locale == 'cn') {
            $_locale = 'cn';
        }
        else if($locale == 'th') {
            $_locale = 'th';
        }
        else if($locale == 'kr' || $locale == 'ko') {
            $_locale = 'ko';
        }
        App::setLocale($_locale);
    }
}
