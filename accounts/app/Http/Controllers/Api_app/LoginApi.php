<?php

namespace App\Http\Controllers\Api_app;

use App\Helpers\FilesHelpers;
use App\Models\Clients;
use App\Models\ClientAddress;
use App\Models\Notification;
use App\Models\Promotion;
use App\Models\PromotionCustomer;
use App\Models\CustomerClass;
use App\Models\SettingCustomerClass;
use App\Services\AdminService;
use App\Traits\UploadFile;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use DateTime;
use Illuminate\Support\Facades\Log;

class LoginApi extends AuthController
{
    use UploadFile;

    protected $svAdmin;
    protected $_locale;

    public function __construct(Request $request, AdminService $adminService)
    {
        parent::__construct($request);
        $this->svAdmin = $adminService;
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
        $this->ActiveServer = true;
        $this->_locale = $request->_locale;
    }

    //(1)
    public function sign_up_to_google()
    {
        $dataResult = [
            'result' => false
        ];
        $id_google = $this->request->input('id_google');
        $idToken = $this->request->input('idToken');
        $player_id = $this->request->input('player_id');
        $object_type = $this->request->input('object_type');
        $referral_code = $this->request->input('referral_code', null);
        $vsession = $this->request->input('vsession', null);
        $payload = false;
        if (!empty($this->ActiveServer)) {
            $info = $this->getUserByTokenApple($idToken);

            if (empty($info)) {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_login_fail');
                $dataResult['title'] = lang('notification');
                return response()->json($dataResult, 500);
            }
            if ($info['sub'] == $id_google) {
                $payload = true;
            }

            //            $clientGoogle = new Google_Client(['client_id' => $id_google]);
            //            $payload = $clientGoogle->verifyIdToken($idToken);
        }
        if (!empty($payload) && !empty($id_google) && !empty($idToken)) {
            $data_client = DB::table('tbl_clients')->where([
                'sign_up_with' => 'google',
                'id_sign_up' => $id_google,
            ])->get()->first();
            if (!empty($data_client)) {
                if ($data_client->active == 0) {
                    $dataResult['message'] = lang('tai_khoan_cua_ban_dang_tam_khoa_vui_long_lien_he_hotline_smb_de_duoc_xu_ly');
                    $dataResult['title'] = lang('notification');
                    return response()->json($dataResult);
                }
                $dataResult['result'] = true;
                $dataResult['id'] = $data_client->id;
                $dataResult['event'] = 'login';
                $dataResult['message'] = lang('c_login_success');
                $dataResult['title'] = lang('notification');
                $dataResult['token'] = $this->Create_Token([
                    'password' => $data_client->password,
                    'id' => $data_client->id,
                    'player_id' => !empty($player_id) ? $player_id : null,
                    'sign_up_with' => 'google',
                    'id_sign_up' => $id_google,
                    'is_app' => 1,
                    'vsession' => !empty($vsession) ? $vsession : null,
                ]);
                if (!empty($player_id)) {
                    DB::table('tbl_player_id')->where('player_id', $player_id)->delete();

                    DB::table('tbl_player_id')->insert([
                        'object_id' => $data_client->id,
                        'object_type' => $object_type ?? 'customer',
                        'player_id' => $player_id,
                    ]);
                }
                if (!empty($dataResult['token'])) {
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['result'] = true;
                $dataResult['event'] = 'sign_up';
                $dataResult['message'] = lang('c_login_fail');
                $dataResult['title'] = lang('notification');
                $dataResult['email'] = $info['email'];
                $dataResult['fullname'] = $info['name'];
                $dataResult['idToken'] = $idToken;
                $dataResult['id_apple'] = $id_google;
                $dataResult['player_id'] = $player_id;
                $dataResult['object_type'] = $object_type;
                $dataResult['sign_up_with'] = 'google';
                $dataResult['id_sign_up'] = $id_google;
                $dataResult['referral_code'] = $referral_code;
                $dataResult['avatar'] = !empty($info['picture']) ? $info['picture'] : null;
                $dataResult['is_app'] = $this->request->input('is_app') ?? 1;
                $dataResult['code_introduce'] = $this->request->input('code_introduce') ?? null;
                $dataResult['machines_id'] = $this->request->input('machines_id') ?? null;
                $dataResultNew = $this->sign_up_social_network($dataResult);
                return $dataResultNew;
            }
        } else {
            $dataResult['message'] = lang('c_login_fail');
            $dataResult['title'] = lang('notification');
            return response()->json($dataResult, 500);
        }
    }

    //(1)
    public function sign_up_to_apple()
    {
        $dataResult = [
            'result' => false
        ];
        $id_apple = $this->request->input('id_apple');
        $idToken = $this->request->input('idToken');
        $player_id = $this->request->input('player_id');
        $object_type = $this->request->input('object_type') ?? 'customer';
        $fullname = $this->request->input('fullname') ?? NULL;
        $vsession = $this->request->input('vsession') ?? NULL;
        $payload = true;
        if (!empty($this->ActiveServer)) {
            $info = $this->getUserByTokenApple($idToken);
            if ($info['sub'] == $id_apple) {
                $payload = true;
            }
        }
        if (!empty($payload) && !empty($idToken) && !empty($id_apple)) {
            $data_client = DB::table('tbl_clients')->where([
                'sign_up_with' => 'apple',
                'id_sign_up' => $id_apple,
            ])->get()->first();
            if (!empty($data_client)) {
                $dataResult['result'] = true;
                $dataResult['id'] = $data_client->id;
                $dataResult['event'] = 'login';
                $dataResult['message'] = lang('c_login_success');
                $dataResult['title'] = lang('notification');
                $dataResult['token'] = $this->Create_Token([
                    'password' => $data_client->password,
                    'id' => $data_client->id,
                    'player_id' => !empty($player_id) ? $player_id : null,
                    'sign_up_with' => 'apple',
                    'id_sign_up' => $id_apple,
                    'is_app' => 1,
                    'vsession' => $vsession,
                ]);
                if (!empty($player_id)) {
                    DB::table('tbl_player_id')->where('player_id', $player_id)->delete();

                    DB::table('tbl_player_id')->insert([
                        'object_id' => $data_client->id,
                        'object_type' => $object_type ?? 'customer',
                        'player_id' => $player_id,
                    ]);
                }
                if (!empty($dataResult['token'])) {
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['result'] = true;
                $dataResult['event'] = 'sign_up';
                $dataResult['message'] = lang('c_login_fail');
                $dataResult['title'] = lang('notification');
                $dataResult['email'] = $info['email'] ?? NULL;
                $dataResult['fullname'] = !empty($fullname) ? $fullname : '';
                $dataResult['idToken'] = $idToken;
                $dataResult['id_apple'] = $id_apple;
                $dataResult['player_id'] = $player_id;
                $dataResult['object_type'] = $object_type;
                $dataResult['sign_up_with'] = 'apple';
                $dataResult['id_sign_up'] = $id_apple;
                $dataResult['vsession'] = !empty($vsession) ? $vsession : NULL;
                $dataResult['is_app'] = $this->request->input('is_app') ?? 1;
                $dataResult['code_introduce'] = $this->request->input('code_introduce') ?? null;
                $dataResult['machines_id'] = $this->request->input('machines_id') ?? null;
                $dataResultNew = $this->sign_up_social_network($dataResult);
                return $dataResultNew;
            }
        } else {
            $dataResult['message'] = lang('c_login_fail');
            $dataResult['title'] = lang('notification');
            return response()->json($dataResult, 500);
        }
    }

    //(2)đăng ký
    private function sign_up_social_network($dataPost = array())
    {
        $_locale = $this->_locale;
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $dataResult = [
            'result' => false,
        ];
        if ($dataPost) {
            $data = $dataPost;
//            if (empty($data['fullname'])) {
//                $dataResult['message'] = lang('c_pls_input_fullname');
//                return response()->json($dataResult);
//            }

            if (!empty($data['email'])) {
                $kt_email = DB::table('tbl_clients')
                    ->where('email', $data['email'])
                    ->get()
                    ->first();
                if (!empty($kt_email)) {
                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = lang('c_phone_isset_pls_input_email_other');
                    return response()->json($dataResult);
                }
            }
            $referral_code = !empty($data['referral_code']) ? $data['referral_code'] : null;
//            try {
            $responseRefCode = $this->svAdmin->getOrderRef('client');
            $code = $responseRefCode['reference_no'] ?? time();
            $dataInsert = [
                'code' => $code,
                'phone' => !empty($data['phone']) ? $data['phone'] : null,
                'email' => !empty($data['email']) ? $data['email'] : null,
                'fullname' => !empty($data['fullname']) ? $data['fullname'] : null,
                'lang_default' => !empty($_locale) ? $_locale : 'vi',
            ];
            $apple_test = $this->svAdmin->get_option('apple_test') ?? 0;
            if($apple_test == 1){
                $dataInsert['type_client'] = 2;
                $dataInsert['type_partner'] = 1;
                $dataInsert['is_leader'] = 1;
                $dataInsert['type_leader'] = 1;
            }
            $dataInsert['code_introduce'] = createCodeIntroduce();

            if (!empty($data['password'])) {
                $dataInsert['password'] = encrypt($data['password']);
            }
            if (isset($data['id_sign_up'])) {
                $dataInsert['sign_up_with'] = $data['sign_up_with'];
                $dataInsert['id_sign_up'] = $data['id_sign_up'];
                $data_client_with = DB::table('tbl_clients')->where([
                    'sign_up_with' => $data['sign_up_with'],
                    'id_sign_up' => $data['id_sign_up'],
                ])->get()->first();

                if (!empty($data_client_with)) {
                    $dataResult['result'] = false;
                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = lang('c_error_sign_up_lk');
                    return response()->json($dataResult);
                }
            }

            //                $discountApp = DiscountApp::where('default', 1)->first();
            //                if (!empty($discountApp)) {
            //                    $dataInsert['discount_app_id'] = $discountApp->id;
            //                } else {
            //                    $discountApp = DiscountApp::first();
            //                    $dataInsert['discount_app_id'] = $discountApp->id;
            //                }

            $id = DB::table('tbl_clients')->insertGetId($dataInsert);
            $this->svAdmin->updateOrderRef('client');
            if (!empty($id)) {
                $getClient = Clients::find($id);
                $getClient->referral_code = generateRandomString($id);
                $getClient->save();
            }
            if (!empty($_FILES['avatar'])) {
                FilesHelpers::maybe_create_upload_path('clients/');
                $paste_image = 'clients/' . $id . '/';
                $paste_imageShort = 'clients/' . $id . '/';
                $image_avatar = FilesHelpers::uploadFileData($this->request->file('avatar'), 'avatar', $paste_image,
                    $paste_imageShort);
                if (!empty($image_avatar)) {
                    $avatar = is_array($image_avatar) ? $image_avatar[0] : $image_avatar;
                    if (!empty($avatar)) {
                        DB::table('tbl_clients')->where('id', $id)->update(['avatar' => $avatar]);
                    }
                }
            } elseif (!empty($data['avatar'])) {
                FilesHelpers::maybe_create_upload_path('clients/');
                FilesHelpers::maybe_create_upload_path('clients/' . $id . '/');
                $image_small = $data['avatar'];
                $time = time();
                $paste_img = 'clients/' . $id . '/';
                $copyAvatar = copy($image_small, storage_path('app/public/' . $paste_img) . $time . '.jpg');
                if (!empty($copyAvatar)) {
                    $avatar = $paste_img . $time . '.jpg';
                    DB::table('tbl_clients')->where('id', $id)->update(['avatar' => $avatar]);
                }
            }

            if (!empty($data['phone'])) {
                DB::table('tbl_otp_client')->where('phone', $data['phone'])->delete();
            }

            $dataResult['result'] = true;
            $dataResult['id'] = $id;

            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_login_success');
            $dataResult['token'] = $this->Create_Token([
                'password' => !empty($dataInsert['password']) ? $dataInsert['password'] : null,
                'id' => $id,
                'sign_up_with' => !empty($data['sign_up_with']) ? $data['sign_up_with'] : null,
                'id_sign_up' => !empty($data['id_sign_up']) ? $data['id_sign_up'] : null,
                'vsession' => !empty($data['vsession']) ? $data['vsession'] : null,
                'is_app' => 1
            ]);

            $dataResult['lang_default'] = $dataInsert['lang_default'];

            if (!empty($data['player_id'])) {
                DB::table('tbl_player_id')->where('player_id', $data['player_id'])->delete();

                DB::table('tbl_player_id')->insert([
                    'object_id' => $id,
                    'object_type' => $data['object_type'] ?? 'customer',
                    'player_id' => $data['player_id'],
                ]);
            }

            if (!empty($data['is_app'])) {
                if (!empty($data['code_introduce']) && !empty($data['machines_id'])) {
                    $clientIntroduce = DB::table('tbl_clients')
                        ->where('code_introduce', $data['code_introduce'])->first();
                    if (!empty($clientIntroduce->id)) {
                        $checkRefferal = DB::table('tbl_referral_level')
                            ->where('referral_code', $data['code_introduce'])
                            ->where('machines_id', $data['machines_id'])->first();
                        if (!empty($checkRefferal)) {
                            DB::table('tbl_client_introduce')->insert([
                                'id_client_introduce' => $clientIntroduce->id,
                                'id_client' => $id,
                                'machines_id' => $data['machines_id'],
                            ]);
                            reviewClassClientApi($clientIntroduce->id);
                            // đăng ký xong update customer_id vào khuyến mãi nếu có
                            $dtPromotion = Promotion::where('machines_id', '=', $data['machines_id'])->first();
                            $dtPromotion->customer()->delete();
                            $promotionCustomer = new PromotionCustomer();
                            $promotionCustomer->customer_id = $id;
                            $promotionCustomer->promotion_id = $dtPromotion->id;
                            $promotionCustomer->save();
                            //end
                            DB::table('tbl_referral_level')->where('id', $checkRefferal->id)->update(['status' => 1]);
                        }
                    }
                } elseif (!empty($data['machines_id'])) {
                    $checkRefferal = DB::table('tbl_referral_level')
                        ->where('machines_id', $data['machines_id'])->first();
                    if (!empty($checkRefferal)) {
                        $clientIntroduce = DB::table('tbl_clients')
                            ->where('code_introduce', $checkRefferal->referral_code)->first();
                        if (!empty($clientIntroduce->id)) {
                            DB::table('tbl_client_introduce')->insert([
                                'id_client_introduce' => $clientIntroduce->id,
                                'id_client' => $id,
                                'machines_id' => $data['machines_id'],
                            ]);
                            reviewClassClientApi($clientIntroduce->id);
                            // đăng ký xong update customer_id vào khuyến mãi nếu có
                            $dtPromotion = Promotion::where('machines_id', '=', $data['machines_id'])->first();
                            $dtPromotion->customer()->delete();
                            $promotionCustomer = new PromotionCustomer();
                            $promotionCustomer->customer_id = $id;
                            $promotionCustomer->promotion_id = $dtPromotion->id;
                            $promotionCustomer->save();
                            //end
                            DB::table('tbl_referral_level')->where('id', $checkRefferal->id)->update(['status' => 1]);
                        }
                    }
                }
            }

            //gui mail
//                $dataMail = [
//                    'fullname' => !empty($data['fullname']) ? $data['fullname'] : null,
//                    'email' => !empty($data['email']) ? $data['email'] : null,
//                    'phone' => !empty($data['phone']) ? $data['phone'] : null,
//                    'date_create' => date('Y-m-d H:i:s'),
//                ];
//                $emailCc = 'thuannguyen.fososoft@gmail.com';
//                $emailCc = null;
//                if (!empty($emailCc)) {
//                    Mail::send('admin.email-template.new_customer_register', $dataMail,
//                        function ($message) use ($emailCc) {
//                            $message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
//                            $message->to($emailCc, 'Đăng ký người dùng');
//                            $message->subject('Đăng ký người dùng');
//                        });
//                }

            if (!empty($dataResult['token'])) {
                return response()->json($dataResult);
            }
//            } catch (\Exception $exception) {
//                $dataResult['result'] = false;
//                $dataResult['message'] = lang('c_sign_up_fail');
//                return response()->json($dataResult);
//            }

        } else {

            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function start_sign_up()
    {
        $_locale = $this->_locale;
        if (empty($_locale)) {
            $_locale = 'vi';
        }

        try {
            $dataResult = [
                'result' => false,
            ];
            if ($this->request->input()) {
                $data = $this->request->input();
                if (!empty($data['phone'])) {
                    $kt_phone = DB::table('tbl_clients')
                        ->where('phone', $data['phone'])
                        ->get()
                        ->first();
                    if (!empty($kt_phone->id)) {
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = lang('c_phone_isset_pls_input_phone_other');
                        return response()->json($dataResult);
                    }
                }
            
                if (empty($data['phone']) && empty($data['email'])) {

                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = lang('c_pls_input_phone_sign_up');
                    return response()->json($dataResult);
                }
                if (empty($data['fullname'])) {
                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = lang('c_pls_input_fullname');
                    return response()->json($dataResult);
                }

                $data['event'] = $data['event'] ?? 'register';
                try {

                    $dataResult = $this->otpDangKyAccount();
                    return $dataResult;
                } catch (\Exception $exception) {
                    $dataResult['result'] = false;
                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = $exception->getMessage();
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_pls_input');
                return response()->json($dataResult);
            }
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyOtp()
    {
        $dataResult = [
            'result' => false,
        ];
        if ($this->request->input()) {
            $data = $this->request->input();
            if (!empty($data['phone'])) {
                $kt_phone = DB::table('tbl_clients')
                    ->where('phone', $data['phone'])
                    ->get()
                    ->first();
                if (!empty($kt_phone)) {
                    $dataResult['message'] = lang('c_phone_isset_pls_input_phone_other');
                    return response()->json($dataResult);
                }
            }
            if (empty($data['phone']) && empty($data['email'])) {
                $dataResult['message'] = lang('c_pls_input_phone_sign_up');
                return response()->json($dataResult);
            }
            if (empty($data['fullname'])) {
                $dataResult['message'] = lang('c_pls_input_fullname');
                return response()->json($dataResult);
            }

            if (empty($data['key_code'])) {
                $dataResult['result'] = false;
                $dataResult['message'] = 'Mã OTP không được bỏ trống';
                return response()->json($dataResult);
            } else {
                if (config('app.debug')) {
                    if ($data['key_code'] != '111111') {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                } else {
                    $ktKeyCode = DB::table('tbl_otp_client')
                        ->where('phone', $data['phone'])
                        ->whereRaw('DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "' . date('Y-m-d H:i:s') . '"')
                        ->first();
                    if (empty($ktKeyCode)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_expired');
                        return response()->json($dataResult);
                    } elseif ($ktKeyCode->key_code != $data['key_code']) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                }
            }
            $dataResult['result'] = true;
            $dataResult['message'] = 'Nhập OTP thành công!';
            return response()->json($dataResult);
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    //đăng ký tài khoản (bước 1 đăng ký otp)
    public function sign_up()
    {
        $_locale = $this->_locale;
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        try {
            $dataResult = [
                'result' => false,
                'title' => lang('notification')
            ];
            if ($this->request->input()) {
                $data = $this->request->input();
                $vsession = $this->request->vsession;
                if (!empty($data['phone'])) {
                    $kt_phone = DB::table('tbl_clients')
                        ->where('phone', $data['phone'])
                        ->get()
                        ->first();
                    if (!empty($kt_phone)) {
                        $dataResult['message'] = lang('c_phone_isset_pls_input_phone_other');
                        return response()->json($dataResult);
                    }
                }
                if (empty($data['phone']) && empty($data['email'])) {
                    $dataResult['message'] = lang('c_pls_input_phone_sign_up');
                    return response()->json($dataResult);
                }
                if (empty($data['fullname'])) {
                    $dataResult['message'] = lang('c_pls_input_fullname');
                    return response()->json($dataResult);
                }
                if (empty($data['password'])) {
                    $dataResult['message'] = lang('c_pls_input_password');
                    return response()->json($dataResult);
                }
                $data['event'] = $data['event'] ?? 'register';
                if (!empty($data['email'])) {
                    $kt_email = DB::table('tbl_clients')
                        ->where('email', trim($data['email']))->get()
                        ->first();
                    if (!empty($kt_email)) {
                        $dataResult['message'] = lang('c_phone_isset_pls_input_email_other');
                        return response()->json($dataResult);
                    }
                }

                if (empty($data['key_code'])) {
                    $dataResult['result'] = false;
                    $dataResult['message'] = lang('c_sign_up_fail');
                    return response()->json($dataResult);
                } else {
                    if (config('app.debug')) {
                        if ($data['key_code'] != '111111') {
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('c_code_otp_fail');
                            return response()->json($dataResult);
                        }
                    } else {
                        $ktKeyCode = DB::table('tbl_otp_client')
                            ->where('phone', $data['phone'])
                            ->where('event', $data['event'])
                            ->whereRaw('DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "' . date('Y-m-d H:i:s') . '"')
                            ->first();
                        if (empty($ktKeyCode)) {
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('c_code_otp_expired');
                            return response()->json($dataResult);
                        } elseif ($ktKeyCode->key_code != $data['key_code']) {
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('c_code_otp_fail');
                            return response()->json($dataResult);
                        }
                    }
                }


                DB::beginTransaction();
                $responseRefCode = $this->svAdmin->getOrderRef('client');
                $code = $responseRefCode['reference_no'] ?? time();
                try {
                    $dataInsert = [
                        'code' => $code,
                        'phone' => !empty($data['phone']) ? $data['phone'] : null,
                        'verify_phone' => 1,
                        'email' => !empty($data['email']) ? $data['email'] : null,
                        'address' => !empty($data['address']) ? $data['address'] : null,
                        'birthday' => !empty($data['birthday']) ? to_sql_date($data['birthday'], true) : null,
                        'gender' => !empty($data['gender']) ? $data['gender'] : null,
                        'fullname' => !empty($data['fullname']) ? $data['fullname'] : null,
                        'lang_default' => !empty($_locale) ? $_locale : 'vi',
                    ];
                    $apple_test = $this->svAdmin->get_option('apple_test') ?? 0;
                    if (!empty($data['password'])) {
                        $dataInsert['password'] = encrypt($data['password']);
                    }
                    $dataInsert['code_introduce'] = createCodeIntroduce();
                    $id = DB::table('tbl_clients')->insertGetId($dataInsert);
                    $this->svAdmin->updateOrderRef('client');

                    if (!empty($data['phone'])) {
                        DB::table('tbl_otp_client')
                            ->where('phone', $data['phone'])
                            ->where('event', $data['event'] ?? 'register')
                            ->delete();
                    }

                
                    if (!empty($data['code_introduce'])) {
                        $clientIntroduce = DB::table('tbl_clients')
                            ->where('code_introduce', $data['code_introduce'])->first();
                        if (!empty($clientIntroduce->id)) {
                            DB::table('tbl_client_introduce')->insert([
                                'id_client_introduce' => $clientIntroduce->id,
                                'id_client' => $id
                            ]);
                        }
                    }

                    $dataResult['result'] = true;
                    $dataResult['id'] = $id;
                    $dataResult['message'] = lang('c_sign_up_success');
                    $dataResult['token'] = $this->Create_Token([
                        'password' => !empty($dataInsert['password']) ? $dataInsert['password'] : null,
                        'id' => $id,
                        'sign_up_with' => !empty($data['sign_up_with']) ? $data['sign_up_with'] : null,
                        'id_sign_up' => !empty($data['id_sign_up']) ? $data['id_sign_up'] : null,
                        'is_app' => !empty($data['is_app']) ? 1 : 0,
                        'vsession' => !empty($vsession) ? $vsession : NULL,
                    ]);
                    $dataResult['lang_default'] = $dataInsert['lang_default'];

                    if (!empty($data['player_id'])) {
                        DB::table('tbl_player_id')->where('player_id', $data['player_id'])->delete();
                        DB::table('tbl_player_id')->insert([
                            'object_id' => $id,
                            'object_type' => $data['object_type'] ?? 'customer',
                            'player_id' => $data['player_id'],
                        ]);
                    }


                    DB::commit();

                    if (!empty($dataResult['token'])) {
                        return response()->json($dataResult);
                    }
                } catch (\Exception $exception) {
                    DB::rollBack();
                    $dataResult['result'] = false;
                    $dataResult['message'] = $exception->getMessage();
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['message'] = lang('c_pls_input');
                return response()->json($dataResult);
            }
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //đăng nhập
    public function login()
    {
        try {
            $dataResult = [
                'result' => false,
                'title' => lang('notification')
            ];
            if ($this->request->input()) {
                $data = $this->request->input();
                $vsession = $this->request->vsession;
                $type_login = $data['type_login'] ?? 'password';
                if (empty($data['phone']) && empty($data['email'])) {
                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = lang('c_pls_input_phone_login');
                    return response()->json($dataResult);
                }
                if ($type_login == 'password') {
                    if (empty($data['password'])) {
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = lang('c_pls_input_password');
                        return response()->json($dataResult);
                    }
                }
                if (!empty($data['phone'])) {
                    $ktLogin = DB::table('tbl_clients')->where('phone', $data['phone'])->first();
                    if (empty($ktLogin)) {
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = lang('c_not_find_phone');
                        return response()->json($dataResult);
                    }
                }
                if (!empty($ktLogin)) {
                    if ($ktLogin->active == 0) {
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = lang('Tài khoản của bạn đang tạm khóa, vui lòng liên hệ hotline để được xử lý!');
                        return response()->json($dataResult);
                    }
                    if ($type_login == 'otp') {

                        if (empty($data['key_code'])) {
                            $dataResult['title'] = lang('notification');
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('Vui lòng nhập mã OTP');
                            return response()->json($dataResult);
                        } else {
                            if (config('app.debug')) {
                                if ($data['key_code'] != '111111') {
                                    $dataResult['result'] = false;
                                    $dataResult['message'] = lang('c_code_otp_fail');
                                    return response()->json($dataResult);
                                }
                            } else {
                                $ktKeyCode = DB::table('tbl_otp_client')->where('phone', $data['phone'])->whereRaw(
                                    'DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "' . date('Y-m-d H:i:s') . '"'
                                )->first();
                                if (empty($ktKeyCode)) {
                                    $dataResult['result'] = false;
                                    $dataResult['message'] = lang('c_code_otp_expired');
                                    return response()->json($dataResult);
                                } elseif ($ktKeyCode->key_code != $data['key_code']) {
                                    $dataResult['result'] = false;
                                    $dataResult['message'] = lang('c_code_otp_fail');
                                    return response()->json($dataResult);
                                }
                            }
                        }
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = lang('c_login_success');
                        $dataResult['id'] = $ktLogin->id;
                        $dataResult['result'] = true;
                        $dataResult['token'] = $this->Create_Token([
                            'password' => $ktLogin->password,
                            'id' => $ktLogin->id,
                            'sign_up_with' => !empty($ktLogin->sign_up_with) ? $ktLogin->sign_up_with : null,
                            'id_sign_up' => !empty($ktLogin->id_sign_up) ? $ktLogin->id_sign_up : null,
                            'vsession' => !empty($vsession) ? $vsession : null,
                        ]);
                        if (!empty($data['player_id'])) {
                            DB::table('tbl_player_id')->where('player_id', $data['player_id'])->delete();
                            DB::table('tbl_player_id')->insert([
                                'object_id' => $ktLogin->id,
                                'object_type' => $data['object_type'] ?? 'customer',
                                'player_id' => $data['player_id'],
                            ]);
                        }
                        if (!empty($dataResult['token'])) {
                            return response()->json($dataResult);
                        }
                    } else if ($type_login == 'password') {
                        if (!empty($ktLogin->password)) {
                            if (decrypt($ktLogin->password) != $data['password']) {
                                $dataResult['title'] = lang('notification');
                                $dataResult['message'] = lang('c_password_incorrect');
                                return response()->json($dataResult);
                            } else {
                                $dataResult['title'] = lang('notification');
                                $dataResult['message'] = lang('c_login_success');
                                $dataResult['id'] = $ktLogin->id;
                                $dataResult['result'] = true;
                                $dataResult['token'] = $this->Create_Token([
                                    'password' => $ktLogin->password,
                                    'id' => $ktLogin->id,
                                    'sign_up_with' => !empty($ktLogin->sign_up_with) ? $ktLogin->sign_up_with : null,
                                    'id_sign_up' => !empty($ktLogin->id_sign_up) ? $ktLogin->id_sign_up : null,
                                    'is_app' => !empty($data['is_app']) ? 1 : 0,
                                    'vsession' => !empty($vsession) ? $vsession : null,
                                ]);
                                $dataResult['lang_default'] = $ktLogin->lang_default;
                                if (!empty($data['player_id'])) {
                                    DB::table('tbl_player_id')->where('player_id', $data['player_id'])->delete();
                                    DB::table('tbl_player_id')->insert([
                                        'object_id' => $ktLogin->id,
                                        'object_type' => $data['object_type'] ?? 'customer',
                                        'player_id' => $data['player_id'],
                                    ]);
                                }
                                if (!empty($dataResult['token'])) {
                                    return response()->json($dataResult);
                                }
                            }
                        } else {
                            $dataResult['title'] = lang('notification');
                            $dataResult['message'] = lang('c_password_incorrect');
                            return response()->json($dataResult);
                        }
                    }
                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = lang('Đăng nhập thất bại!');
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['message'] = lang('c_pls_input');
                return response()->json($dataResult);
            }
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //edit
    public function send_otp_login()
    {
        $this->request->merge(['event' => 'login']);
        $this->request->merge(['phone' => $this->request->phone]);
        $dataResult = $this->otpDangKyAccount();
        return $dataResult;
    }

    //edit
    public function send_otp_update_password()
    {
        $this->request->merge(['event' => 'change_password']);
        $this->request->merge(['phone' => $this->request->client->phone]);
        $dataResult = $this->otpDangKyAccount();
        return $dataResult;
    }

    //edit
    public function update_password()
    {
        try {
            $id_client = $this->request->client->id;
            $dataResult = [
                'result' => false,
                'title' => lang('notification')
            ];

            if ($this->request->input()) {
                $data = $this->request->input();
                $type = $data['type'];
                $kt_account = DB::table('tbl_clients')
                    ->where('id', $id_client)
                    ->first();
                if (empty($kt_account->id)) {
                    $dataResult['message'] = lang('Lỗi đăng nhập vui lòng thử lại');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
                $client = Clients::find($id_client);
                if ($type == 'password') {
//                    if(empty($data['password_old']) && !empty($client->password)) {
//                        $dataResult['message'] = lang('c_pls_input_password_old');
//                        $dataResult['result'] = false;
//                        return response()->json($dataResult);
//                    }

                    if (empty($data['password'])) {
                        $dataResult['message'] = lang('c_pls_input_password');
                        $dataResult['result'] = false;
                        return response()->json($dataResult);
                    }
//                    if(!empty($client->password) && decrypt($client->password) != $data['password_old']) {
//                        $dataResult['message'] = lang('Mật khẩu cũ không đúng!');
//                        $dataResult['result'] = false;
//                        return response()->json($dataResult);
//                    }

                    $client->password = encrypt($data['password']);
                    $successChange = $client->save();
                    if (!empty($successChange)) {
                        $dataResult['result'] = true;
                        $dataResult['message'] = lang('c_update_password_success');
                        $dataResult['token'] = $this->Create_Token([
                            'password' => $client->password,
                            'id' => $id_client,
                            'sign_up_with' => $client->sign_up_with ?? NULL,
                            'id_sign_up' => $client->id_sign_up ?? NULL,
                        ]);
                        return response()->json($dataResult);
                    }
                    $dataResult['result'] = false;
                    $dataResult['message'] = lang('c_update_password_fail');
                    return response()->json($dataResult);
                } elseif ($type == 'otp') {
                    if (empty($data['key_code'])) {
                        $dataResult['message'] = lang('Vui lòng nhập mã OTP');
                        $dataResult['result'] = false;
                        return response()->json($dataResult);
                    }
                    if (empty($data['password'])) {
                        $dataResult['message'] = lang('c_pls_input_password');
                        $dataResult['result'] = false;
                        return response()->json($dataResult);
                    }


                    if (config('app.debug')) {
                        if ($data['key_code'] != '111111') {
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('c_code_otp_fail');
                            return response()->json($dataResult);
                        }
                    } else {
                        $ktKeyCode = DB::table('tbl_otp_client')
                            ->where('phone', $data['phone'])
                            ->where('event', 'change_password')
                            ->whereRaw('DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "' . date('Y-m-d H:i:s') . '"')
                            ->first();
                        if (empty($ktKeyCode)) {
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('c_code_otp_expired');
                            return response()->json($dataResult);
                        } elseif ($ktKeyCode->key_code != $data['key_code']) {
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('c_code_otp_fail');
                            return response()->json($dataResult);
                        }

                        $client->password = encrypt($data['password']);
                        $successChange = $client->save();
                        if (!empty($successChange)) {
                            $dataResult['result'] = true;
                            $dataResult['message'] = lang('c_update_password_success');
                            $dataResult['token'] = $this->Create_Token([
                                'password' => $client->password,
                                'id' => $id_client,
                                'sign_up_with' => $client->sign_up_with ?? NULL,
                                'id_sign_up' => $client->id_sign_up ?? NULL,
                            ]);
                            return response()->json($dataResult);
                        }
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_update_password_fail');
                        return response()->json($dataResult);
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //gửi OTP quên mật khẩu
    public function send_otp_forgot_password()
    {
        if(empty($this->request->phone)){
            $dataResult['message'] = lang('Vui lòng nhập số điện thoại');
            $dataResult['result'] = false;
            return response()->json($dataResult);
        }
        $this->request->merge(['event' => 'forgot_password']);
        $this->request->merge(['phone' => $this->request->phone]);
        $dataResult = $this->otpDangKyAccount();
        return $dataResult;
    }

    //check xem OTP đúng chưa
    public function check_otp_forgot_password()
    {
        if ($this->request->input()) {
            $data = $this->request->input();
            if (!empty($data['phone'])) {
                $kt_phone = DB::table('tbl_clients')
                    ->where('phone', $data['phone'])
                    ->first();
                if (empty($kt_phone)) {
                    $dataResult['message'] = lang('Số điện thoại này chưa đăng ký tài khoản!');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            }

            if (empty($data['key_code'])) {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('Mã OTP không được bỏ trống');
                return response()->json($dataResult);
            } else {
                if (config('app.debug')) {
                    if ($data['key_code'] != '111111') {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                } else {
                    $ktKeyCode = DB::table('tbl_otp_client')
                        ->where('phone', $data['phone'])
                        ->whereRaw('DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "' . date('Y-m-d H:i:s') . '"')
                        ->first();
                    if (empty($ktKeyCode)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_expired');
                        return response()->json($dataResult);
                    } elseif ($ktKeyCode->key_code != $data['key_code']) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                }
            }
            $dataResult['result'] = true;
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('Nhập OTP thành công!');
            return response()->json($dataResult);
        } else {
            $dataResult['message'] = lang('c_pls_input');
            $dataResult['title'] = lang('notification');
            return response()->json($dataResult);
        }
    }

    //đổi mật khẩu khi quên mật khẩu
    public function forgot_password()
    {
        if ($this->request->input()) {
            $data = $this->request->input();

            if (!empty($data['phone'])) {

                $kt_phone = DB::table('tbl_clients')
                    ->where('phone', $data['phone'])
                    ->first();
                if (empty($kt_phone)) {
                    $dataResult['message'] = lang('Số điện thoại này chưa đăng ký tài khoản!');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }

                if(empty($data['key_code'])){
                    $dataResult['result'] = false;
                    $dataResult['message'] = lang('Mã OTP không được bỏ trống');
                    return response()->json($dataResult);
                }
                $ktKeyCode = DB::table('tbl_otp_client')
                    ->where('phone', $data['phone'])
                    ->where('event', 'forgot_password')
                    ->first();

                if (empty($ktKeyCode)) {
                    $dataResult['result'] = false;
                    $dataResult['message'] = lang('c_code_otp_expired');
                    return response()->json($dataResult);
                } elseif ($ktKeyCode->key_code != $data['key_code']) {
                    $dataResult['result'] = false;
                    $dataResult['message'] = lang('c_code_otp_fail');
                    return response()->json($dataResult);
                }
               
            }

            if (empty($data['password']) && !empty($kt_phone->id)) {
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_pls_input_password');
                $dataResult['result'] = false;
                return response()->json($dataResult);
            }

            try {
                $client = Clients::find($kt_phone->id);
                $client->password = encrypt($data['password']);
                $client->save();
                if ($client) {
                    $dataResult['result'] = true;
                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = lang('Cập nhập mật khẩu thành công!');
                    return response()->json($dataResult);
                } else {
                    $dataResult['result'] = false;
                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = lang('Cập nhập mật khẩu thất bại!');

                    $dataResult['token'] = $this->Create_Token([
                        'password' => $client->password,
                        'fullname' => $client->fullname,
                        'id' => $client->id,
                        'player_id' => !empty($data['player_id']) ? $data['player_id'] : null,
                    ]);

                    return response()->json($dataResult);
                }
            } catch (\Exception $exception) {
                $dataResult['result'] = false;
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = $exception->getMessage();
                return response()->json($dataResult);
            }
        } else {

            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    //edit
    public function otpDangKyAccount()
    {
        $optionSetting = $this->svAdmin->GetSetings();
        $limit_otp_change_pass = $optionSetting['limit_otp_change_pass'] ?? 5;
        $time_otp = $optionSetting['time_otp'] ?? 3;
        $content_otp_register = $optionSetting['content_otp_register'] ?? 300;
        $content_otp_change_pass = $optionSetting['content_otp_change_pass'] ?? 300;

        $phone = $this->request->input('phone');
        $event = !empty($this->request->input('event')) ? $this->request->input('event') : 'register';
        $ipClient = $this->request->ip();

        $ktSendSms = DB::table('tbl_send_sms')->where(function ($q) use ($phone, $ipClient) {
            if (!empty($phone)) {
                $q->orWhere('phone', $phone);
            }
            if (!empty($ipClient)) {
                $q->orWhere('ip_user', $ipClient);
            }
        })->whereDate('created_at', Carbon::today())->count();


        if (config('app.debug')) {
            $limit_otp_change_pass = 100000000;
        }
        $limit_otp_change_pass = 100000000;

        if ($ktSendSms > $limit_otp_change_pass) {
            $dataResult['message'] = lang('Bạn đã vượt quá số lần gửi OTP trong 1 ngày!');
            $dataResult['result'] = false;
            return response()->json($dataResult);
        }

        if (!empty($phone)) {
            $date = date('Y-m-d H:i:s');
            $phone_check = substr($phone, 1, 9);
            $phone_check = '84' . $phone_check;

            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $needle = "Windows";
            $ktOTp = DB::table('tbl_otp_client')->select('*', DB::raw('TIMESTAMPDIFF(SECOND,  "' . $date . '", date_end) as time_otp'))
                ->where('event', $event)->where('phone', $phone)->first();
            if (!empty($ktOTp->id) && $ktOTp->time_otp > 0) {
                $dataResult['time'] = $ktOTp->time_otp;
                $dataResult['result'] = true;
                return response()->json($dataResult);
            }
            if ($event == 'login') {
                $ktPhoneClient = DB::table('tbl_clients')
                    ->where('phone', $phone)
                    ->get()->first();
                if (empty($ktPhoneClient)) {
                    $dataResult['message'] = lang('Số điện thoại này chưa đăng ký tài khoản!');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            } else if ($event == 'forgot_password') {
                $ktPhoneClient = DB::table('tbl_clients')
                    ->where('phone', $phone)
                    ->get()->first();
                if (empty($ktPhoneClient)) {
                    $dataResult['message'] = lang('Số điện thoại này chưa đăng ký tài khoản!');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            } elseif ($event == 'change_password') {
                $ktPhoneClient = DB::table('tbl_clients')
                    ->where('phone', $phone)
                    ->get()->first();
                if (empty($ktPhoneClient)) {
                    $dataResult['message'] = lang('Số điện thoại này chưa đăng ký tài khoản!');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
                $countSendSms = DB::table('tbl_send_sms')->where('phone', $phone_check)
                    ->where('event', 'change_password')
                    ->where(DB::raw('DATE_FORMAT(date_send,"%Y-%m-%d")'), '=', date('Y-m-d'))->count();
                if ($countSendSms >= $limit_otp_change_pass) {
                    $dataResult['message'] = lang('Số điện thoại này đã vượt quá số lần gửi OTP trong 1 ngày!');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            } elseif ($event == 'register') {
                $ktPhoneClient = DB::table('tbl_clients')
                    ->where('phone', $phone)
                    ->get()->first();
                $countSendSms = DB::table('tbl_send_sms')->where('phone', $phone_check)
                    ->where('event', 'register')
                    ->where(DB::raw('DATE_FORMAT(date_send,"%Y-%m-%d")'), '=', date('Y-m-d'))->count();

                if ($countSendSms >= $limit_otp_change_pass) {
                    $dataResult['message'] = lang('Số điện thoại này đã vượt quá số lần gửi OTP trong 1 ngày!');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
                $countSendLimitSms = DB::table('tbl_send_sms')
                    ->where('event', 'register')
                    ->where('user_agent', $userAgent)
                    ->where(DB::raw('DATE_FORMAT(date_send,"%Y-%m-%d")'), '=', date('Y-m-d'))->count();
                if (!empty($ktPhoneClient)) {
                    $dataResult['message'] = lang('c_phone_isset_pls_input_phone_other');
                    $dataResult['result'] = false;
                    $dataResult['isset'] = true;
                    return response()->json($dataResult);
                }
            }

            DB::table('tbl_otp_client')->where('event', $event)->where('phone', $phone)->delete();

            $key_code = $this->createKeyCode();
            $dateEnd = strtotime('+' . $time_otp . ' minute', strtotime($date));
            $dateEnd = date('Y-m-d H:i:s', $dateEnd);
            $idOTP = DB::table('tbl_otp_client')->insertGetId([
                'phone' => $phone,
                'event' => $event,
                'key_code' => $key_code,
                'date_end' => $dateEnd
            ]);

            if (!empty($idOTP)) {
                DB::table('tbl_send_sms')->insertGetId([
                    'phone' => $phone,
                    'brand_name' => 'ZALO',
                    'message' => $key_code,
                    'date_send' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'ip_user' => $ipClient,
                ]);
                $dataResult['message'] = lang('c_send_otp_true');
                $dataResult['result'] = true;
                $dataResult['key_code'] = '';
                $dataResult['time'] = $time_otp * 60;
                $content_sms = '';
                if ($event == 'register') {
                    $content_sms = $content_otp_register;
                } elseif ($event == 'change_password') {
                    $content_sms = $content_otp_change_pass;
                }
                $content_sms = str_replace('{code}', $key_code, $content_sms);
//                if (empty(strpos($userAgent, $needle)))
                {
                    if (!config('app.debug')) {
                        send_zalo_admin($phone, 'otp', ['otp' => $key_code]);
                    }
                }
                return response()->json($dataResult);
            } else {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_send_otp_fail');
                return response()->json($dataResult);
            }
        }
        $dataResult['result'] = false;

        $dataResult['title'] = lang('notification');
        $dataResult['message'] = lang('c_send_otp_fail');
        return response()->json($dataResult);
    }

    public function update_account()
    {
        $dataResult = [
            'result' => false,
        ];
        $token = $this->request->bearerToken();
        $data = $this->request->input();
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (!empty($id)) {
            DB::beginTransaction();
            try {
                $dataClient = Clients::find($id);
                if (isset($data['email']) && !empty($data['email']) && empty($dataClient->sign_up_with)) {
                    $ktEmail = Clients::where('email', $data['email'])->where('id', '!=', $id)->first();
                    if (!is_null($ktEmail)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_kt_email_isset_pls_input_email_other');
                        return response()->json($dataResult);
                    }
                }
                if (isset($data['phone']) && !empty($data['phone']) && !empty($dataClient->sign_up_with)) {
                    $ktPhone = Clients::where('phone', $data['phone'])->where('id', '!=', $id)->first();
                    if (!is_null($ktPhone)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_kt_phone_isset_pls_input_phone_other');
                        return response()->json($dataResult);
                    }
                }

                $dataUpdate = [];
                if (!empty($data) || !empty($_FILES['avatar'])) {
                    if (isset($data['phone']) && !empty($dataClient->sign_up_with)) {
                        $dataUpdate['phone'] = $data['phone'];
                    }
                    if (isset($data['birthday'])) {
                        $dataUpdate['birthday'] = to_sql_date($data['birthday']);
                        $now = strtotime(date('Y-m-d') . ' 23:59:59');
                        $birthdayCheck = strtotime(to_sql_date($data['birthday']));
                        if ($now < $birthdayCheck) {
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('khong_nhap_ngay_nho_hon_ngay_hien_tai');
                            return response()->json($dataResult);
                        }
                    }
                    if (isset($data['gender']) && is_numeric($data['gender'])) {
                        $dataUpdate['gender'] = ($data['gender'] ?? 0);
                    }
                    if (isset($data['prefix_phone'])) {
                        $dataUpdate['prefix_phone'] = $data['prefix_phone'];
                    }
                    if (isset($data['email']) && empty($dataClient->sign_up_with)) {
                        $dataUpdate['email'] = $data['email'];
                    }
                    if (isset($data['mst'])) {
                        $dataUpdate['mst'] = $data['mst'];
                    }
                    if (isset($data['fullname'])) {
                        $dataUpdate['fullname'] = $data['fullname'];
                    }
                    if (isset($data['address'])) {
                        $dataUpdate['address'] = $data['address'];
                    }
                    if (isset($data['birthday'])) {
                        $dataUpdate['birthday'] = to_sql_date($data['birthday']);
                    }
                    if (isset($data['number_cccd'])) {
                        $dataUpdate['number_cccd'] = $data['number_cccd'];
                    }
                    if (isset($data['issued_cccd'])) {
                        $dataUpdate['issued_cccd'] = $data['issued_cccd'];
                    }
                    if (isset($data['date_cccd'])) {
                        $dataUpdate['date_cccd'] = to_sql_date($data['date_cccd']);
                    }
                    if (isset($data['number_passport'])) {
                        $dataUpdate['number_passport'] = $data['number_passport'];
                    }
                    if (isset($data['issued_passport'])) {
                        $dataUpdate['issued_passport'] = $data['issued_passport'];
                    }
                    if (isset($data['date_passport'])) {
                        $dataUpdate['date_passport'] = to_sql_date($data['date_passport']);
                    }

                    if (!empty($_FILES['avatar'])) {
                        if (!empty($dataClient->avatar)) {
                            $this->deleteFile($dataClient->avatar);
                        }
                        $image_avatar = $this->UploadFile($this->request->file('avatar'), 'clients/' . $dataClient->id, 70, 70, false);

                        if (!empty($image_avatar)) {
                            $avatar = is_array($image_avatar) ? $image_avatar[0] : $image_avatar;
                            if (!empty($avatar)) {
                                $dataUpdate['avatar'] = $avatar;
                            }
                        }
                    }

                    if (!empty($dataUpdate)) {
                        $affected = DB::table('tbl_clients')->where('id', $id)->update($dataUpdate);
                        if ($affected >= 0) {
                            DB::commit();
                            $dataResult['result'] = true;
                            $dataResult['title'] = lang('notification');
                            $dataResult['message'] = lang('c_update_account_success');
                            if (!empty($dataUpdate['password'])) {
                                $dataResult['token'] = $this->Create_Token([
                                    'password' => $dataUpdate['password'],
                                    'fullname' => $dataUpdate['fullname'],
                                    'id' => $id,
                                    'player_id' => !empty($data['player_id']) ? $data['player_id'] : null,
                                ]);
                                if (!empty($dataResult['token'])) {
                                    DB::table('tbl_session_login')->where('token', $token)->delete();
                                }
                            }
                            return response()->json($dataResult);
                        } else {
                            $dataResult['result'] = false;
                            $dataResult['title'] = lang('notification');
                            $dataResult['message'] = lang('c_update_account_fail');
                            return response()->json($dataResult);
                        }
                    }
                }
                $dataResult['result'] = false;
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_update_account_fail');
                return response()->json($dataResult);
            } catch (\Exception $exception) {
                DB::rollBack();
                $dataResult['title'] = lang('notification');
                $dataResult['result'] = false;
                $dataResult['message'] = $exception->getMessage();
                return response()->json($dataResult);
            }
        } else {
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_account_not_isset');
            return response()->json($dataResult);
        }
    }

    public function update_account_register()
    {
        $dataResult = [
            'result' => false,
        ];
        $token = $this->request->bearerToken();
        $data = $this->request->input();
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (!empty($id)) {
            DB::beginTransaction();
            try {
                $dataClient = Clients::find($id);
                if (isset($data['email']) && !empty($data['email']) && empty($dataClient->sign_up_with)) {
                    $ktEmail = Clients::where('email', $data['email'])->where('id', '!=', $id)->first();
                    if (!is_null($ktEmail)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_kt_email_isset_pls_input_email_other');
                        return response()->json($dataResult);
                    }
                }
                if (isset($data['phone']) && !empty($data['phone']) && !empty($dataClient->sign_up_with)) {
                    $ktPhone = Clients::where('phone', $data['phone'])->where('id', '!=', $id)->first();
                    if (!is_null($ktPhone)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_kt_phone_isset_pls_input_phone_other');
                        return response()->json($dataResult);
                    }
                }

                $dataUpdate = [];
                if (!empty($data) || !empty($_FILES['avatar'])) {
                    if (isset($data['phone']) && !empty($dataClient->sign_up_with)) {
                        $dataUpdate['phone'] = $data['phone'];
                    }
                    if (isset($data['birthday'])) {
                        $dataUpdate['birthday'] = to_sql_date($data['birthday']);
                        $now = strtotime(date('Y-m-d') . ' 23:59:59');
                        $birthdayCheck = strtotime(to_sql_date($data['birthday']));
                        if ($now < $birthdayCheck) {
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('khong_nhap_ngay_nho_hon_ngay_hien_tai');
                            return response()->json($dataResult);
                        }
                    }
                    if (empty($data['type_client'])){
                        $dataResult['result'] = false;
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = lang('Vui lòng chọn loại đối tượng!');
                        return response()->json($dataResult);
                    }
                    if ($data['type_client'] == 2){
//                        if(empty($data['mst'])){
//                            $dataResult['result'] = false;
//                            $dataResult['title'] = lang('notification');
//                            $dataResult['message'] = lang('Vui lòng nhập mã số thuế!');
//                            return response()->json($dataResult);
//                        }
                        if(empty($data['type_partner'])){
                            $dataResult['result'] = false;
                            $dataResult['title'] = lang('notification');
                            $dataResult['message'] = lang('Vui lòng chọn loại đối tác!');
                            return response()->json($dataResult);
                        }

                        if($data['type_partner'] == 2) {
                            if (empty($data['code_introduce'])) {
                                $dataResult['result'] = false;
                                $dataResult['title'] = lang('notification');
                                $dataResult['message'] = lang('Vui lòng nhập mã giới thiệu Leader cấp!');
                                return response()->json($dataResult);
                            }
                            $clientIntroduce = DB::table('tbl_clients')
                                ->where('code_introduce', $data['code_introduce'])->where('is_leader', 1)->first();
                            if (empty($clientIntroduce)) {
                                $dataResult['result'] = false;
                                $dataResult['title'] = lang('notification');
                                $dataResult['message'] = lang('Không tồn tại Leader !');
                                return response()->json($dataResult);
                            }
                            if ($clientIntroduce->id == $id) {
                                $dataResult['result'] = false;
                                $dataResult['title'] = lang('notification');
                                $dataResult['message'] = lang('Leader không thể là chính mình!');
                                return response()->json($dataResult);
                            }

                            $clientIntroduce = DB::table('tbl_clients')
                            ->where('code_introduce', $data['code_introduce'])->where('is_leader', 1)->first();

                            DB::table('tbl_client_introduce')->where('id_client',$id)->delete();
                            DB::table('tbl_client_introduce')->insert([
                                'id_client_introduce' => $clientIntroduce->id,
                                'id_client' => $id,
                                'machines_id' => $data['machines_id'] ?? null,
                            ]);
                        }

                        if($data['type_partner'] == 1){
                            if (empty($data['code_introduce'])) {
                                $dataResult['result'] = false;
                                $dataResult['title'] = lang('notification');
                                $dataResult['message'] = lang('Vui lòng nhập mã Leader CTY cấp !');
                                return response()->json($dataResult);
                            }
                            // hau
                            $CheckLeaderCode = $this->svAdmin->get_code_leader($data['code_introduce']);
                            // $CheckLeaderCode = DB::table('tbl_code_leader')
                            // ->where('code', $data['code_introduce'])->where('status', 0)->first();
                            if (empty($CheckLeaderCode)) {
                                $dataResult['result'] = false;
                                $dataResult['title'] = lang('notification');
                                $dataResult['message'] = lang('Không tồn tại Leader CTY cấp !');
                                return response()->json($dataResult);
                            }
                            $dataUpdate['code_introduce_admin'] = $data['code_introduce'];
                            $dataUpdate['code_introduce'] = createCodeLeader($id);

                            // DB::table('tbl_clients')->where('id', $id)->update([
                            //     'code_introduce' => $data['code_introduce'],
                            // ]);
                            // Cập nhật trạng thái và gán User vào mã Leader
                            // DB::table('tbl_code_leader')->where('id', $CheckLeaderCode->id)->update([
                            //     'status' => 1,
                            //     'customer_id' => $id,
                            //     'used_at' => date('Y-m-d H:i:s')
                            // ]);
                        }
                    }

                    if (empty($dataClient->sign_up_with)) {
                        if (empty($data['email'])) {
                            $dataResult['result'] = false;
                            $dataResult['title'] = lang('notification');
                            $dataResult['message'] = lang('Vui nhâp email!');
                            return response()->json($dataResult);
                        }
                    }

                    if (empty($data['fullname'])){
                        $dataResult['result'] = false;
                        $dataResult['title'] = lang('notification');
                        if ($data['type_client'] == 2) {
                            $dataResult['message'] = lang('Vui lòng nhập tên Đại lý/ Spa/ CTV!');
                        } else {
                            $dataResult['message'] = lang('Vui lòng nhập tên tài khoản');
                        }
                        return response()->json($dataResult);
                    }

                    if (empty($data['address'])){
                        $dataResult['result'] = false;
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = lang('Vui lòng nhập địa chỉ chi tiết!');
                        return response()->json($dataResult);
                    }

                    if (!empty($dataClient->sign_up_with)) {
                        if (empty($data['phone'])) {
                            $dataResult['result'] = false;
                            $dataResult['title'] = lang('notification');
                            $dataResult['message'] = lang('Vui lòng nhập số điện thoại!');
                            return response()->json($dataResult);
                        }
                    }

                    if (empty($_FILES['avatar'])){
                        $dataResult['result'] = false;
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = lang('Vui lòng thêm hình ảnh đại diện!');
                        return response()->json($dataResult);
                    }


                    $dataUpdate['email'] = $data['email'];
                    $dataUpdate['mst'] = $data['mst'] ?? null;
                    $dataUpdate['type_client'] = $data['type_client'];
                    $dataUpdate['type_partner'] = $data['type_partner'] ?? 0;
                    $dataUpdate['fullname'] = $data['fullname'];
                    $dataUpdate['address'] = $data['address'];
                    $dataUpdate['leader_id'] = $clientIntroduce->id ?? 0;
                    $dataUpdate['is_leader'] = $data['type_partner'] == 1 ? 1 : 0;

                    if (!empty($_FILES['avatar'])) {
                        if (!empty($dataClient->avatar)) {
                            $this->deleteFile($dataClient->avatar);
                        }
                        $image_avatar = $this->UploadFile($this->request->file('avatar'), 'clients/' . $dataClient->id, 70, 70, false);

                        if (!empty($image_avatar)) {
                            $avatar = is_array($image_avatar) ? $image_avatar[0] : $image_avatar;
                            if (!empty($avatar)) {
                                $dataUpdate['avatar'] = $avatar;
                            }
                        }
                    }

                    if (!empty($dataUpdate)) {
                        $affected = DB::table('tbl_clients')->where('id', $id)->update($dataUpdate);
                        if ($affected >= 0) {
                            DB::commit();
                            $dataResult['result'] = true;
                            $dataResult['title'] = lang('notification');
                            $dataResult['message'] = lang('c_update_account_success');
                            return response()->json($dataResult);
                        } else {
                            $dataResult['result'] = false;
                            $dataResult['title'] = lang('notification');
                            $dataResult['message'] = lang('c_update_account_fail');
                            return response()->json($dataResult);
                        }
                    }
                }
                $dataResult['result'] = false;
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_update_account_fail');
                return response()->json($dataResult);
            } catch (\Exception $exception) {
                DB::rollBack();
                $dataResult['title'] = lang('notification');
                $dataResult['result'] = false;
                $dataResult['message'] = $exception->getMessage();
                return response()->json($dataResult);
            }
        } else {
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_account_not_isset');
            return response()->json($dataResult);
        }
    }

//    public function create_otp_update_password()
//    {
//        $token = $this->request->bearerToken();
//        $data = $this->request->input();
//        $id = !empty($this->request->client) ? $this->request->client->id : 0;
//        if (!empty($id)) {
//            $dataClient = Clients::find($id);
//            if (!empty($dataClient->referral_code)) {
//                $email = $dataClient->email;
//                $date = date('Y-m-d H:i:s');
//                DB::table('tbl_otp_client')->where('email', $email)->delete();
//                $key_code = $this->createKeyCode();
//                $dateEnd = strtotime('+' . get_option('time_otp') . ' minute', strtotime($date));
//                $dateEnd = date('Y-m-d H:i:s', $dateEnd);
//                $idOTP = DB::table('tbl_otp_client')->insertGetId([
//                    'email' => $email,
//                    'key_code' => $key_code,
//                    'date_end' => $dateEnd
//                ]);
//                if (!empty($idOTP)) {
//                    $data['message'] = lang('c_send_otp_true');
//                    $data['result'] = true;
//                    $data['key_code'] = $key_code;
//                    $data['time'] = get_option('time_otp') * 60;
//                    $dataMail = [
//                        'code' => $key_code,
//                    ];
//                    $emailCc = $email;
//                    Mail::send('admin.email-template.send_otp', $dataMail, function ($message) use ($emailCc) {
//                        $message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
//                        $message->to($emailCc, 'SMB OTP');
//                        $message->subject('SMB OTP');
//                    });
//                    return response()->json($data);
//                } else {
//                    $dataResult['result'] = false;
//                    $dataResult['message'] = lang('c_send_otp_fail');
//                    return response()->json($dataResult);
//                }
//            } else {
//                $dataResult['result'] = false;
//                $dataResult['message'] = lang('c_not_find_username');
//                return response()->json($dataResult);
//            }
//        } else {
//            $dataResult['result'] = false;
//            $dataResult['message'] = lang('not_find_account');
//            return response()->json($dataResult);
//        }
//    }

    public function get_info_account()
    {
        $dataResult = [
            'result' => false,
        ];
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (!empty($id)) {
            $data = Clients::select(
                'id', 'code', 'fullname', 'phone', 'email', 'address', 'birthday',
                'gender','created_at', 'active', 'lang_default','type_client'
            )->selectRaw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar')
                ->where('id', $id)
                ->first();
            if (!empty($data)) {
                if (!empty($data->password)) {
                    $data->password = true;
                } elseif (!empty($data->password)) {
                    $data->password = false;
                }
                $data->is_avatar = true;

                if (empty($data->avatar)) {
                    $data->avatar = $this->baseUrl . '/avatar.jpg';
                    $data->is_avatar = false;
                }

                $homeCounts = [];
                $transactionCounts = [];
                if($data->type_client != 2){
                    $admin = 0;
                } else {
                    $admin = 1;
                }
                if (!empty($id)) {
                    $response = $this->svAdmin->countHomes([$id],$admin);
                    if (isset($response['result']) && $response['result']) {
                        $homeCounts = $response['data'];
                    }

                }
                if($data->type_client != 2){
                    $data['total_home'] = $homeCounts[$id] ?? 0;
                } else {
                    $data['total_home'] = $homeCounts['total_home'] ?? 0;
                }
                $dataResult['result'] = true;
                $dataResult['info'] = $data;
                $dataResult['lang_default'] = $data->lang_default ?? 'vi';
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_get_info_success');
                return response()->json($dataResult);
            } else {
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_get_info_fail');
                return response()->json($dataResult, 403);
            }
        } else {
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_code_token_fail');
            return response()->json($dataResult, 503);
        }
    }

    public function get_info_address_customer()
    {
        $dataResult = [
            'result' => false,
        ];
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (!empty($id)) {
            $data = Clients::select(
                'id', 'code', 'fullname', 'phone', 'email', 'prefix_phone', 'sign_up_with', 'address', 'birthday',
                'gender',
                'created_at', 'point', 'account_balance', 'customer_alepay_id', 'password', 'verify_phone',
                'number_cccd', 'issued_cccd', 'date_cccd', 'number_passport', 'issued_passport', 'date_passport',
                'referral_code', 'active', 'code_introduce'
            )->selectRaw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar')
                ->where('id', $id)->first();

            $clientsAddress = ClientAddress::where('customer_id', $id)->first();
            if (!empty($clientsAddress)) {
                $data->phone = $clientsAddress->phone;
                $data->name = $clientsAddress->name;
                $data->address = $clientsAddress->address;
            }

            if (!empty($data)) {
                if (!empty($data->password)) {
                    $data->password = true;
                } elseif (!empty($data->password)) {
                    $data->password = false;
                }
                if (empty($data->avatar)) {

                    //                    $data->avatar = url('/') . '/admin/assets/images/avatar.jpg';

                    $data->avatar = $this->baseUrl . '/avatar.jpg';

                }
                $link_referral = $this->svAdmin->get_option('short_link_referral');
                $data->link_referral = $link_referral . '?pid=referral_own_media&af_sub1=' . $data->code_introduce . '&campaign=referral';
                $dataResult['result'] = true;
                $dataResult['info'] = $data;
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_get_info_success');

                return response()->json($dataResult);
            } else {
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_get_info_fail');
                return response()->json($dataResult, 403);
            }
        } else {
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_code_token_fail');
            return response()->json($dataResult, 503);
        }
    }

    public function logout()
    {
        $dataResult = [
            'result' => false,
            'message' => lang('c_logout_fail')
        ];
        $player_id = $this->request->input('player_id');
        $token = $this->request->bearerToken();
        if ($token) {
            DB::table('tbl_session_login')->where('token', $token)->delete();

            if (!empty($player_id)) {
                DB::table('tbl_player_id')->where('player_id', $player_id)->delete();
            }

            $dataResult['result'] = true;
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_logout_true');
        }
        return response()->json($dataResult);
    }

    private function Create_Token($data = [])
    {
        $dateNow = date('Y-m-d H:i:s');
        $player_id = !empty($data['player_id']) ? $data['player_id'] : null;
        $fullname = !empty($data['fullname']) ? $data['fullname'] : null;
        $customer_id = !empty($data['id']) ? $data['id'] : null;
        $password = !empty($data['password']) ? $data['password'] : null;
        $phone = !empty($data['phone']) ? $data['phone'] : null;
        $is_app = !empty($data['is_app']) ? $data['is_app'] : 0;
        $vsession = !empty($data['vsession']) ? $data['vsession'] : 0;
        $privateKey = file_get_contents(storage_path('keys/private.pem'));

        $payload = [
            'customer_id' => $customer_id,
            'customer_name' => $fullname, // audience
            'phone' => $phone, // audience
            'password' => $password,
            'date' => $dateNow, // 1 phút
        ];

        $token = JWT::encode($payload, $privateKey, 'RS256');

        $ktToken = DB::table('tbl_session_login')
            ->where('id_client', $customer_id)
            ->where('token', $token)
            ->where(function ($sqlWhere) use ($player_id) {
                $sqlWhere->where('player_id', $player_id);
            })->get()->first();
        if (!empty($vsession) && !empty($token)) {
            //đồng bộ vsession của user
            $this->svAdmin->PostAdmin($this->request, $token, 'api/synchronized_vsession');
        }

        if (!empty($ktToken)) {
            DB::table('tbl_session_login')
                ->where('id', $ktToken->id)
                ->update([
                    'token' => $token,
                    'is_app' => $is_app ?? 0,
                ]);
        } else {
            DB::table('tbl_session_login')
                ->insertGetId([
                    'token' => $token,
                    'id_client' => $data['id'],
                    'is_app' => $is_app,
                ]);
        }
        return $token;
    }

    //edit
    private function createKeyCode()
    {
        $keyCode = rand(100000, 999999);
        if (config('app.debug')) {
            $keyCode = '111111';
        }
        return $keyCode;
    }

    public function lockAccount()
    {
        $token = $this->request->bearerToken();
        $id = $this->Info_To_Token($token);
        if (!empty($id)) {
            $client = Clients::find($id);
            if (!empty($client)) {
                $client->active = 1;
                $client->save();
                if ($client) {
                    $dataResult['result'] = true;
                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = lang('Tài khoản của bạn bị khóa');
                    return response()->json($dataResult);
                } else {
                    $dataResult['result'] = false;
                    $dataResult['title'] = lang('notification');
                    $dataResult['message'] = lang('dt_error');
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['result'] = false;
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_get_info_fail');
                return response()->json($dataResult, 403);
            }
        } else {
            $dataResult['result'] = false;
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_code_token_fail');
            return response()->json($dataResult, 503);
        }
    }

    public function info_introduce()
    {

        $id_client = $this->request->client->id;

        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }


        $dataResult = [];

        $dataResult['result'] = true;

        if ($current_page == 1) {
            $count_guest = DB::table('tbl_client_introduce')->join(
                'tbl_clients',
                'tbl_clients.id',
                '=',
                'tbl_client_introduce.id_client'
            )->where('id_client_introduce', $id_client)->count();
            $dataResult['guest'] = $count_guest;
            $count_review = DB::table('tbl_client_introduce')->join(
                'tbl_clients',
                'tbl_clients.id',
                '=',
                'tbl_client_introduce.id_client'
            )->where('status', 1)->where('id_client_introduce', $id_client)->count();
            $dataResult['review'] = $count_review;
        }

        $data = DB::table('tbl_client_introduce')
            ->select(
                'tbl_clients.id',
                'fullname',
                'email',
                'birthday',
                'type_client',
                DB::raw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar'),
                DB::raw('CONCAT(LEFT(phone, 6), "****") AS phone')
            )
            ->join('tbl_clients', 'tbl_clients.id', '=', 'tbl_client_introduce.id_client')
            ->where('id_client_introduce', $id_client)
            ->paginate($per_page, ['*'], 'page', $current_page);
        $dataResult['data'] = $data;
        $dataResult['title'] = lang('notification');
        return response()->json($dataResult);
    }

    protected function getUserByTokenApple($token)
    {


        $parts = explode('.', $token);

        // Lấy payload (phần giữa)
        $payload = $parts[1];

        // JWT dùng base64url -> convert sang base64 thường
        $payload = strtr($payload, '-_', '+/');

        // Thêm padding nếu thiếu
        $remainder = strlen($payload) % 4;
        if ($remainder) {
            $payload .= str_repeat('=', 4 - $remainder);
        }

        // Decode rồi parse JSON
        $json = base64_decode($payload);
        $data = json_decode($json, true);
        return $data;

//        $claims = explode('.', $token)[1];
//        dd(base64_decode($claims));
//        return json_decode(base64_decode($claims), true);
    }

    //Cập nhật thông tin kết hợp với đăng ký sản phẩm
    public function update_account_signup_review()
    {
        $_locale = $this->_locale;
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $dataResult = [
            'result' => false,
        ];
        $token = $this->request->bearerToken();
        $data = $this->request->input();
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        $id_adress = !empty($this->request->id_adress) ? $this->request->id_adress : 0;
        $test_app = 1;
        if (!empty($id)) {
            if (empty($data['id_product'])) {
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_pls_input_check_product_review');
                return response()->json($dataResult);
            }
            try {
                DB::beginTransaction();
                $dataClient = Clients::find($id);
                if (isset($data['email']) && !empty($data['email']) && empty($dataClient->sign_up_with) && empty($dataClient->email)) {
                    $ktEmail = Clients::where('email', $data['email'])->where('id', '!=', $id)->first();
                    if (!is_null($ktEmail)) {
                        $dataResult['result'] = false;
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = lang('c_kt_email_isset_pls_input_email_other');
                        return response()->json($dataResult);
                    }
                }
                if (isset($data['phone']) && !empty($data['phone']) && (!empty($dataClient->sign_up_with) || empty($dataClient->phone))) {
                    $ktPhone = Clients::where('phone', $data['phone'])->where('id', '!=', $id)->first();
                    if (!is_null($ktPhone)) {
                        $dataResult['result'] = false;
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = lang('c_kt_phone_isset_pls_input_phone_other');
                        return response()->json($dataResult);
                    }
                }

                $dataUpdate = [];
                if (!empty($data) || !empty($_FILES['avatar'])) {
                    if (isset($data['phone']) && (!empty($dataClient->sign_up_with) || empty($dataClient->phone))) {
                        $dataUpdate['phone'] = $data['phone'];
                    }
                    if (isset($data['birthday'])) {
                        $dataUpdate['birthday'] = to_sql_date($data['birthday']);
                        $now = strtotime(date('Y-m-d') . ' 23:59:59');
                        $birthdayCheck = strtotime(to_sql_date($data['birthday']));
                        if ($now < $birthdayCheck) {
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('khong_nhap_ngay_nho_hon_ngay_hien_tai');
                            return response()->json($dataResult);
                        }
                    }
                    if (isset($data['gender']) && empty($dataClient->gender) && is_numeric($data['gender'])) {
                        $dataUpdate['gender'] = $data['gender'] ?? 0;
                    }
                    if (isset($data['email']) && empty($dataClient->sign_up_with) && empty($dataClient->email)) {
                        $dataUpdate['email'] = $data['email'];
                    }
                    if (isset($data['fullname']) && empty($dataClient->fullname)) {
                        $dataUpdate['fullname'] = $data['fullname'];
                    }
                    if (isset($data['address']) && empty($dataClient->address)) {
                        $dataUpdate['address'] = $data['address'];
                    }
                    if (isset($data['birthday']) && empty($dataClient->birthday)) {
                        $dataUpdate['birthday'] = to_sql_date($data['birthday']);
                    }
                    if (!empty($_FILES['avatar'])) {
                        if (!empty($dataClient->avatar)) {
                            $this->deleteFile($dataClient->avatar);
                        }
                        $image_avatar = $this->UploadFile(
                            $this->request->file('avatar'),
                            'clients/' . $dataClient->id,
                            70,
                            70,
                            false
                        );
                        if (!empty($image_avatar)) {
                            $avatar = is_array($image_avatar) ? $image_avatar[0] : $image_avatar;
                            if (!empty($avatar)) {
                                $dataUpdate['avatar'] = $avatar;
                            }
                        }
                    }

                    if (!empty($dataUpdate)) {
                        $affected = Clients::where('id', $id)->update($dataUpdate);
                        if (empty($affected)) {
                            $dataResult['result'] = false;
                            $dataResult['title'] = lang('notification');
                            $dataResult['message'] = lang('c_update_account_fail');
                            return response()->json($dataResult);
                        }
                    }

                    $address = $data['address'] ?? NULL;
                    $phone = $data['phone'] ?? NULL;
                    $name = $data['fullname'] ?? NULL;
                    $email = $data['email'] ?? NULL;

                    $ktClientAddress = ClientAddress::where('customer_id', $id)->first();
                    if (empty($ktClientAddress->id) && !empty($address)) {
                        //thêm địa chỉ mặc định cho khách hàng
                        change_client_address([
                            'customer_id' => $id,
                            'address' => $address ?? NULL,
                            'phone' => $phone ?? NULL,
                            'name' => $name ?? NULL,
                            'email' => $email ?? NULL,
                            'default_address' => 1,
                        ]);
                        $ktClientAddress = ClientAddress::where('customer_id', $id)->where('default_address', 1)->first();
                    } else {
                        if (empty($id_adress)) {
                            $ktClientAddress = ClientAddress::where('customer_id', $id)->where(
                                'address', $address
                            )->where('phone', $phone)
                                ->where('name', $name)
                                ->where('email', $email)->first();
                        } else {
                            $ktClientAddress = ClientAddress::where('customer_id', $id)->where('id', $id_adress)->first();
                            if (!empty($ktClientAddress->id)) {
                                change_client_address([
                                    'customer_id' => $id,
                                    'address' => $address ?? null,
                                    'phone' => $phone ?? null,
                                    'name' => $name ?? null,
                                    'email' => $email ?? null,
                                ], $id_adress);
                                $ktClientAddress = ClientAddress::where('customer_id', $id)->where('id', $id_adress)->first();
                            }
                        }
                        if (empty($ktClientAddress->id) && !empty($address)) {
                            $id_address = change_client_address([
                                'customer_id' => $id,
                                'address' => $address ?? NULL,
                                'phone' => $phone ?? NULL,
                                'name' => $name ?? NULL,
                                'email' => $email ?? NULL,
                            ], $id_adress);
                            $ktClientAddress = ClientAddress::where('customer_id', $id)->where('id', $id_address)->first();
                        }
                    }

                    if (!empty($data['id_product'])) {
                        if (empty($test_app)) {
                            $ktCountProduct = $this->svAdmin->KTReviewProduct(
                                $data['id_product'],
                                $id,
                                $_locale
                            );
                        }
                    }

                    if (!empty($ktCountProduct) && empty($ktCountProduct['result'])) {
                        $dataResult['result'] = false;
                        $dataResult['title'] = lang('notification');
                        $dataResult['message'] = $ktCountProduct['message'];
                        DB::rollBack();
                        return response()->json($dataResult);
                    }
                    $dataResult['result'] = true;
                    $dataResult['message'] = lang('c_sign_up_success');
                    if (!empty($dataUpdate['fullname']) && $dataUpdate['fullname'] != $dataClient->fullname) {
                        $dataResult['token'] = $this->Create_Token([
                            'password' => !empty($dataClient->password) ? decrypt($dataClient->password) : null,
                            'fullname' => !empty($dataUpdate['fullname']) ? $dataUpdate['fullname'] : $dataClient->fullname,
                            'id' => $id,
                            'player_id' => !empty($data['player_id']) ? $data['player_id'] : null,
                            'vsession' => !empty($data['vsession']) ? $data['vsession'] : null,
                        ]);
                    }
                    if (!empty($dataResult['token'])) {
                        DB::table('tbl_session_login')->where('token', $token)->delete();
                    } else if (empty($dataResult['token'])) {
                        $dataResult['token'] = $token;
                    }

                    if (!empty($data['id_product'])) {
                        //đăng ký dùng thử sản phẩm
                        $infoSignUpReview = $this->svAdmin->SignUpReviewProduct(
                            $data['id_product'],
                            $id,
                            $dataResult['token'],
                            $_locale,
                            [
                                'hidden_chat' => 1,
                                'id_address' => !empty($ktClientAddress->id) ? $ktClientAddress->id : null,
                                'name_receiver' => !empty($ktClientAddress->name) ? $ktClientAddress->name : null,
                                'phone_receiver' => !empty($ktClientAddress->phone) ? $ktClientAddress->phone : null,
                                'address_receiver' => !empty($ktClientAddress->address) ? $ktClientAddress->address : null,
                                'email_receiver' => !empty($ktClientAddress->email) ? $ktClientAddress->email : null,
                                'test_app' => $test_app
                            ]
                        );
                        if (!empty($infoSignUpReview['result'])) {
//                            $infoClient = Clients::find($id);
//                            $infoSignUpReview['data']['address'] = $ktClientAddress->address;
//                            $infoSignUpReview['data']['phone'] = $ktClientAddress->phone;
//                            $infoSignUpReview['data']['address'] = $ktClientAddress->address;
                            $infoSignUpReview['data']['expected_delivery'] = lang('expected_delivery_time_demo');
                            $dataResult['infoSignUpReview'] = $infoSignUpReview['data'];
                        } else {
                            DB::rollBack();
                            $data['result'] = false;
                            $data['message'] = $infoSignUpReview['message'] ?? lang('c_sign_up_fail');
                            return response()->json($data);
                        }
                    }

                    $dataResult['title'] = lang('notification');
                    DB::commit();
                    return response()->json($dataResult);
                }
            } catch (\Exception $exception) {
                DB::rollBack();
                $data['result'] = false;
                $data['title'] = lang('notification');
                $data['message'] = $exception->getMessage();
                return response()->json($data);
            }

            $dataResult['result'] = true;
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_sign_up_success');
            $dataResult['lang_default'] = $dataClient->lang_default;
            return response()->json($dataResult);
        } else {

            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_sign_up_fail');
            return response()->json($dataResult);
        }
    }
//
//    private function change_client_address($data = [], $id = 0) {
//        $id_client = $data['customer_id'];
//        if (!empty($id)) {
//            $address = ClientAddress::where('id', $id)
//                ->where('customer_id', $id_client)
//                ->first();
//            if (!is_null($address)) {
//                $dataUpdate = [];
//                if (isset($data['address'])) {
//                    $dataUpdate['address'] = $data['address'];
//                }
//                if (isset($data['province_id'])) {
//                    $dataUpdate['province_id'] = $data['province_id'];
//                }
//                if (isset($data['district_id'])) {
//                    $dataUpdate['district_id'] = $data['district_id'];
//                }
//                if (isset($data['ward_id'])) {
//                    $dataUpdate['ward_id'] = $data['ward_id'];
//                }
//                if (isset($data['name'])) {
//                    $dataUpdate['name'] = $data['name'];
//                }
//                if (isset($data['type'])) {
//                    $dataUpdate['type'] = $data['type'];
//                }
//                if (!empty($dataUpdate)) {
//                    $address->update($dataUpdate);
//                    return true;
//                }
//            }
//            return false;
//        }
//        else {
//            $address = new ClientAddress();
//            $address->customer_id = $id_client;
//            $address->address = $data['address'];
//            if (isset($data['province_id'])) {
//                $address->province_id = $data['province_id'];
//            }
//            if (isset($data['district_id'])) {
//                $address->district_id = $data['district_id'];
//            }
//            if (isset($data['ward_id'])) {
//                $address->ward_id = $data['ward_id'];
//            }
//            if (isset($data['name'])) {
//                $address->name = $data['name'];
//            }
//            if (isset($data['type'])) {
//                $address->type = $data['type'];
//                $dataUpdate['type'] = $data['type'];
//            }
//            $address->save();
//            if (!empty($address->id)) {
//                return true;
//            }
//
//            return false;
//        }
//    }

    public function cong_test()
    {
        changePoint(6, 'contribute');
//        challenge_success(9);die();
//        $listClient = Clients::whereRaw('fullname is not null')
//            ->whereRaw('address is not null')->get();
//        $dataAddress = [];
//        foreach($listClient as $key => $value) {
//            $ktAddress = ClientAddress::where('customer_id', $value->id)->first();
//            if(empty($ktAddress)) {
//                change_client_address([
//                    'customer_id' => $value->id,
//                    'address' => $value->address,
//                    'phone' => $value->phone,
//                    'name' => $value->fullname,
//                    'default_address' => 1,
//                ]);
//                $ktAddress = ClientAddress::where('customer_id', $value->id)->first();
//            }
//            $dataAddress[$value->id] = [
//                'name_receiver' => $ktAddress->name,
//                'phone_receiver' => $ktAddress->phone,
//                'address_receiver' => $ktAddress->address,
//                'id_address' => $ktAddress->id,
//            ];
//        }
//        if(!empty($dataAddress)) {
//            $request = new Request();
//            $request->merge(['address' => $dataAddress]);
//            $this->svAdmin->PostAdmin($request, NULL, 'api/cong_test');
//        }
//
//        $dataResult = [
//            'result' => true,
//        ];
//        return response()->json($dataResult);
    }

    public function language_user_active()
    {
        $_locale = $this->_locale;
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $client_id = $this->request->client->id;
        $client = Clients::find($client_id);
        $client->lang_default = $_locale;
        $client->save();
        $dataResult = [
            'result' => true,
            'message' => lang('c_update_language_success'),
        ];
        return response()->json($dataResult);
    }

    public function changePassword()
    {
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        $password_old = $this->request->input('password_old');
        $password = $this->request->input('password');
        if (!empty($id)) {
            $dtClient = Clients::find($id);
            if (empty($password_old)) {
                $dataResult['result'] = false;
                $dataResult['message'] = 'Vui lòng nhập mật khẩu cũ!';
                return response()->json($dataResult);
            }
            if (empty($password)) {
                $dataResult['result'] = false;
                $dataResult['message'] = 'Vui lòng nhập mật khẩu!';
                return response()->json($dataResult);
            }
            DB::beginTransaction();
            try {
                $password = encrypt($password);
                if (!empty($dtClient) && !empty($dtClient['password'])) {
                    if (decrypt($dtClient['password']) != $password_old) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = 'Mật khẩu cũ không đúng!';
                        return response()->json($dataResult);
                    }
                    $dtClient->password = $password;
                    $dtClient->save();
                    DB::commit();
                    $dataResult['token'] = $this->Create_Token([
                        'password' => $dtClient->password,
                        'id' => $dtClient->id,
                        'sign_up_with' => $dtClient->sign_up_with ?? NULL,
                        'id_sign_up' => $dtClient->id_sign_up ?? NULL,
                    ]);
                    $dataResult['result'] = true;
                    $dataResult['message'] = 'Đổi mật khẩu thành công';
                    return response()->json($dataResult);
                }
            } catch (\Exception $exception) {
                DB::rollBack();
                $dataResult['result'] = false;
                $dataResult['message'] = $exception->getMessage();
                return response()->json($dataResult);
            }
        } else {
            $dataResult['result'] = false;
            $dataResult['message'] = lang('c_code_token_fail');
            return response()->json($dataResult, 503);
        }
    }
    function checkDeleteCodeLeader($code_leader = '') {
        $data = DB::table('tbl_clients')->where('code_introduce_admin', $code_leader)->first();
        return response()->json(['result' => $data]);
    }
}
