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
        if(empty($locale)){
            $_locale = 'vi';
            $this->request->merge(['_locale' => $_locale]);
        }
        else {
            $_locale = $locale;
            $this->request->merge(['_locale' => $_locale]);
        }

//        $_locale = 'vi';
//        if($locale == 'vi') {
//            $_locale = 'vi';
//            $this->request->merge(['_locale' => 'vi']);
//        }
//        else if($locale == 'en') {
//            $_locale = 'en';
//            $this->request->merge(['_locale' => 'en']);
//        }
//        else if($locale == 'cn') {
//            $_locale = 'cn';
//            $this->request->merge(['_locale' => 'cn']);
//        }
//        else if($locale == 'th') {
//            $_locale = 'th';
//            $this->request->merge(['_locale' => 'th']);
//        }
//        else if($locale == 'kr') {
//            $_locale = 'kr';
//            $this->request->merge(['_locale' => 'kr']);
//        }
        App::setLocale($_locale);

        $vsession = $this->request->header('vsession');
        if(!empty($vsession)){
            $this->request->merge(['vsession' => $vsession]);
        }
        else if(!empty($this->request->vsession)){
            $vsession = $this->request->vsession;
            $this->request->merge(['vsession' => $vsession]);
        }

        $isweb = $this->request->header('isweb');
        if(!empty($isweb)){
            $this->request->merge(['isweb' => $isweb]);
        }
        else if(!empty($this->request->isweb)){
            $isweb = $this->request->isweb;
            $this->request->merge(['isweb' => $isweb]);
        }
    }
}
