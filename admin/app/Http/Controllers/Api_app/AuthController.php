<?php

namespace App\Http\Controllers\Api_app;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class AuthController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public $request;
    public $user;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->SaveSession = true;
//        $locale = $this->request->header('locale');
//        if(empty($locale)) {
//            $locale = $request->_locale;
//        }
        $locale = getLocale($request);
        if(empty($locale)){
            $locale = 'vi';
            $this->request->merge(['_locale' => $locale]);
        }
        else {
            $this->request->merge(['_locale' => $locale]);
        }
//        $_locale = 'vn';
//        if($locale == 'vi') {
//            $_locale = 'vi';
//        }
//        else if($locale == 'en') {
//            $_locale = 'en';
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
//        }
        App::setLocale($locale);
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

    protected function Info_To_Token($token = '')
    {
        if (!empty($token)) {
            $data = base64_decode($token);
            $ArrayToken = explode('|||', $data);
            $id = $ArrayToken[0];
            $password = $ArrayToken[1];
            $dateCreate = $ArrayToken[2];
            $player_id = !empty($ArrayToken[3]) ? $ArrayToken[3] : NULL;
            $sign_up_with = $ArrayToken[4];
            $id_sign_up = $ArrayToken[5];

            if (!empty($this->SaveSession)) {
                $user_agent = $this->request->server('HTTP_USER_AGENT');
//                $ip_login = $this->request->server('REMOTE_ADDR');
                $ktToken = DB::table('tbl_session_login')
                    ->where('id_client', $id)
                    ->where('token', $token)
//                    ->where('user_agent', $user_agent)
//                    ->where('player_id', $player_id)
                    ->get()->first();
                if (empty($ktToken)) {
                    return false;
                }
            }


            $ktLogin = DB::table('tbl_clients')
                ->where('id', $id)->first();
            if (!empty($ktLogin)) {
                if (!empty($password) && !empty($ktLogin->password)) {
                    if (decrypt($password) == decrypt($ktLogin->password)) {
                        return $ktLogin->id;
                    } else {
                        return false;
                    }
                } elseif (!empty($sign_up_with)) {
                    if ($ktLogin->sign_up_with == $sign_up_with) {
                        return $ktLogin->id;
                    }
                } else {
                    return false;
                }
            }
        }
        return false;
    }
}
