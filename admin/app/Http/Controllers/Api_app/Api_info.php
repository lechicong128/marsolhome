<?php

namespace App\Http\Controllers\Api_app;

use app\Services\ServiceService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\FilesHelpers;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Terms;
use App\Models\TermsTranslations;
use App\Models\IconApp;
use App\Models\TransferAddress;
use App\Models\TransferAddressRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Google_Client;
use function Laravel\Prompts\table;
use DateTime;
use App\Libraries\App;
class Api_info extends AuthController
{
    protected $dbService;
    public function __construct(Request $request, ServiceService $serviceService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->SaveSession = true;
        app(\App\Http\Middleware\CheckLoginApi::class)->getDataToken($this->request);
        $this->baseUrlAdmin = config('services.storage.url');
        $this->baseUrl = config('services.storage.url');
        $this->dbService = $serviceService;

    }

    //(1)
    public function get_info_settings()
    {
        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');

        $dataField = DB::table('tbl_options')->where(function ($field) {
            $field->whereIn('name', [
                'onesignal_id',
                'onesignal_key',
                'google_api_key',
                'pusher',
                'cluster',
                'rule_delete_account',
                'hour_start_car',
                'hour_end_car',
                'hour_min_car',
                'hour_wait_status',
                'email_recruitment',
                'check_option',
                'token_key',
                'version_app',
                'note_version_app',
                'version_app_android',
                'time_cancel_trip',
                'onesignal_key_driver',
                'onesignal_id_driver',
                'display_talented',
                'display_driver',
                'time_cancel_driver',
                'time_cancel_driver_province',
                'contact_phone',
                'contact_email',
                'contact_address_head_office',
                'contact_phone_head_office',
                'contact_address_branch_office',
                'contact_phone_branch_office',
                'contact_data_place_google_map',
                'address_our_location',
                'link_contact_facebook',
                'link_contact_telegram',
                'link_contact_zalo',
                'link_contact_shoppe',
                'link_contact_tiktok',
                'content_short_footer',
                'copyright_footer',
                'money_unit',
                'link_messenger',
                'link_telegram',
                'link_facebook',
                'link_facebook',
                'address_our_location_en',
                'address_our_location_zh',
                'content_short_footer_en',
                'content_short_footer_zh',
                'content_short_footer_ko',
                'content_short_footer_ja',
                'address_our_location_ko',
                'address_our_location_ja',
                'otp_default',
                'check_otp',
                'min_request_withdraw_money',
                'min_transfer_package',
                'fee_withdraw',
                'fee_transfer',
                'link_url_index',
                'button_text_index',
                'referral_program_vi',
                'referral_program_en',
                'referral_program_kr',
                'exchange_rate_haru_wallet',
                'withdrawal_limit',
            ]); //ID của onesinal và key của onesinal
        })->get();
        $data = [];
        foreach ($dataField as $key => $value) {
            if ($value->name == 'rule_delete_account') {
                $value->value = str_replace('src="/storage', 'src="' . asset('/storage') . '', $value->value);
            }
            $data[$value->name] = $value->value;
        }
        $banner = Banner::where('active', 1)->first();
        if (!empty($banner)) {
            $data['banner'] = !empty($banner->image) ? asset('storage/' . $banner->image) : null;
        } else {
            $data['banner'] = null;
        }
        $iconAppTL = IconApp::where('active', 1)->where('type', 1)->first();
        if (!empty($iconAppTL)) {
            $data['icon_app_tl'] = !empty($iconAppTL->image) ? asset('storage/' . $iconAppTL->image) : null;
        } else {
            $data['icon_app_tl'] = null;
        }
        $iconAppTL = IconApp::where('active', 1)->where('type', 2)->first();
        if (!empty($banner)) {
            $data['icon_app_ct'] = !empty($iconAppTL->image) ? asset('storage/' . $iconAppTL->image) : null;
        } else {
            $data['icon_app_ct'] = null;
        }
        $iconAppTL = IconApp::where('active', 1)->where('type', 3)->first();
        if (!empty($banner)) {
            $data['icon_app_tx'] = !empty($iconAppTL->image) ? asset('storage/' . $iconAppTL->image) : null;
        } else {
            $data['icon_app_tx'] = null;
        }
        $dtTransferAddress = TransferAddress::where('active', 1)->get();
        if (!empty($dtTransferAddress)) {
            foreach ($dtTransferAddress as $key => $value) {
                if (!empty($value->image)) {
                    $value->image = asset('storage/' . $value->image);
                }
            }
        }
        $data['dtTransferAddress'] = $dtTransferAddress;

        $type_transfer_address = !empty(get_option('type_transfer_address')) ? explode(',', get_option('type_transfer_address')) : [];
        $data['dtTransferAddressRequest'] = TransferAddress::whereIn('Network', $type_transfer_address)->get();

        if ($_locale != $locale_default_vn) {
            $data['address_our_location'] = $data['address_our_location_' . $_locale];
            $data['content_short_footer'] = $data['content_short_footer_' . $_locale];
        }

        return response()->json($data, 200);
    }

    public function get_info()
    {
        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');

        $dataField = DB::table('tbl_options')->where(function ($field) {
            $field->whereIn('name', [
                'contact_phone',
                'contact_email',
                'contact_address_head_office',
                'contact_phone_head_office',
                'contact_address_branch_office',
                'contact_phone_branch_office',
                'contact_data_place_google_map',
                'address_our_location',
                'link_contact_facebook',
                'link_contact_telegram',
                'link_contact_instagram',
                'link_contact_zalo',
                'link_contact_shoppe',
                'link_contact_tiktok',
                'show_button_next_login',
                'vat',
                'link_oa',
                'link_agency',
                'version_app',
                'version_app_android',
                'link_apple',
                'link_android',
                'vi_note_version_app',
                'apple_test',
                'title_introduce_1',
                'sub_title_introduce_1',
                'title_introduce_2',
                'sub_title_introduce_2',
            ]); //ID của onesinal và key của onesinal
        })->get();
        $data = [];
        foreach ($dataField as $key => $value) {
            $data[$value->name] = $value->value;
        }
        return response()->json($data, 200);
    }
    public function get_version_app()
    {
        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');

        $dataField = DB::table('tbl_options')->where(function ($field) {
            $field->whereIn('name', [
                'version_app',
                'cn_note_version_app',
                'en_note_version_app',
                'kr_note_version_app',
                'th_note_version_app',
                'vi_note_version_app',
                'version_app_android',
            ]); //ID của onesinal và key của onesinal
        })->get();
        $data = [];
        foreach ($dataField as $key => $value) {
            $data[$value->name] = $value->value;
        }
        $_locale = $this->request->input('_locale');
        if (empty($_locale)) {
            $_locale = 'vi';
        }

        $data['note_version_app'] = $data[$_locale . '_note_version_app'];

        return response()->json($data, 200);
    }

    public function get_setings_account()
    {
        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');
        $dataField = DB::table('tbl_options')->where(function ($field) {
            $field->whereIn('name', [
                'limit_otp_change_pass',
                'time_otp',
                'content_otp_register',
                'content_otp_change_pass',
            ]); //ID của onesinal và key của onesinal
        })->get();
        $data = [];
        foreach ($dataField as $key => $value) {
            $data[$value->name] = $value->value;
        }
        return response()->json($data, 200);
    }

    public function type_evaluate()
    {
        $_locale = $this->request->input('_locale');
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $data = DB::table('tbl_type_evaluate')
            ->select('tbl_type_evaluate.id', 'tbl_type_evaluate.type', 'tt.name', 'tt.language')
            ->leftJoin('tbl_type_evaluate_translations as tt', function ($join) use ($_locale) {
                $join->on('tt.id_evaluate', '=', 'tbl_type_evaluate.id')
                    ->where('tt.language', '=', $_locale);
            })->get();
        return response()->json($data, 200);

    }

    public function type_status_review()
    {
        $_locale = $this->request->input('_locale');
        $id_client = $this->request->client->id ?? 0;
        $nameAll = 'Tất cả';
        if (empty($_locale)) {
            $_locale = 'vi';
            $nameAll = 'Tất cả';
        } else if ($_locale == 'en') {
            $nameAll = 'All';
        } else if ($_locale == 'kr') {
            $nameAll = '모두';
        }

        $data = DB::table('tbl_status_review')
            ->select(
                'tbl_status_review.id',
                'tbl_status_review.type',
                'tt.name',
                'tbl_status_review.color',
                'tt.language',
                DB::raw('(
                    SELECT COUNT(*)
                    FROM tbl_sign_up_review
                    WHERE tbl_sign_up_review.status = tbl_status_review.id
                    AND id_client = ' . $id_client . '
                ) as countReview')
            )
            ->leftJoin('tbl_status_review_translations as tt', function ($join) use ($_locale) {
                $join->on('tt.id_status', '=', 'tbl_status_review.id')
                    ->where('tt.language', '=', $_locale);
            })
            ->orderBy('tbl_status_review.id', 'asc')
            ->get()->toArray();
        $countAll = 0;
        if (!empty($id_client)) {
            foreach ($data as $key => $value) {
                $countAll += $value->countReview;
            }
        }
        $data = array_merge([
            [
                'id' => 0,
                'type' => 1,
                'name' => $nameAll,
                'color' => '#fe6bba',
                'language' => $_locale,
                'countReview' => $countAll,
            ]
        ], $data);
        return response()->json($data, 200);
    }

    public function send_zalo()
    {
        $phone = $this->request->input('phone');
        $event = $this->request->input('event');
        if ($event == 'otp') {
            $template_id = Config::get('constant')['template_id_otp'];
        }
        $data = $this->request->input('data');
        $keyPass = $this->request->input('key');
        if ($keyPass != md5(md5(json_encode($data)))) {
            $data['result'] = false;
            $data['message'] = 'Gửi không thành công';
            return response()->json($data, 200);
        }
        if (!empty($phone) && !empty($template_id)) {
            $success = send_zalo($phone, $event, $template_id, $data);
            if (!empty($success)) {
                $data['result'] = true;
                $data['message'] = 'Gửi thành công';
                return response()->json($data, 200);
            }
        }
        $data['result'] = false;
        $data['message'] = 'Gửi không thành công';
        return response()->json($data, 200);
    }
    //lấy thông tin thiết lập ví
    public function get_haru_wallet()
    {
        $dataField = DB::table('tbl_options')->where(function ($field) {
            $field->whereIn('name', [
                'exchange_rate_haru_wallet',
                'withdrawal_limit',
            ]); //ID của onesinal và key của onesinal
        })->get();
        $data = [];
        foreach ($dataField as $key => $value) {
            if ($value->name == 'rule_delete_account') {
                $value->value = str_replace('src="/storage', 'src="' . asset('/storage') . '', $value->value);
            }
            $data[$value->name] = $value->value;
        }
        return response()->json([
            'result' => true,
            'data' => $data
        ], 200);
    }
    //lấy thông tin chương trình referral
    public function get_referral_program()
    {
        $_locale = $this->request->input('_locale');
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $keyLang = 'referral_program_' . $_locale;
        $dataField = DB::table('tbl_options')->where(function ($field) use ($keyLang) {
            $field->where('name', $keyLang); //ID của onesinal và key của onesinal
        })->first();
        $referral_program = json_decode($dataField->value, true);
        $data = $referral_program;
        return response()->json([
            'result' => true,
            'data' => $data
        ], 200);
    }

    public function getInfoContact()
    {
        $_locale = $this->request->input('_locale');
        if (empty($_locale)) {
            $_locale = 'vi';
        }

        $array = [
            $_locale . '_address',
            $_locale . '_phone',
            $_locale . '_email',
            $_locale . '_title_thumbnal',
            $_locale . '_content_thumbnal',
            $_locale . '_image_thumbnal',
        ];

        $data = DB::table('tbl_options')->select('name', 'value')->whereIn('name', $array)->get();
        $dataResult = [];
        foreach ($data as $key => $value) {
            if ($value->name == $_locale . '_address') {
                $dataResult['address'] = $value->value;
            } else {
                if ($value->name == $_locale . '_phone') {
                    $dataResult['phone'] = $value->value;
                } else {
                    if ($value->name == $_locale . '_email') {
                        $dataResult['email'] = $value->value;
                    } else {
                        if ($value->name == $_locale . '_title_thumbnal') {
                            $dataResult['title_thumbnal'] = $value->value;
                        } else {
                            if ($value->name == $_locale . '_content_thumbnal') {
                                $dataResult['content_thumbnal'] = $value->value;
                            } else {
                                if ($value->name == $_locale . '_image_thumbnal') {
                                    $imgthumbnal = $value->value;
                                    $imgthumbnal = !empty($imgthumbnal) ? $imgthumbnal : imgCameraDefault();
                                    $dataResult['image_thumbnal'] = asset($imgthumbnal);
                                }
                            }
                        }
                    }
                }
            }
        }
        return response()->json([
            'result' => true,
            'data' => $dataResult
        ], 200);
    }

    public function getInfoBannerEvent()
    {
        $_locale = $this->request->input('_locale');
        if (empty($_locale)) {
            $_locale = 'vi';
        }

        $array = [
            //            'banner_event_' . $_locale,
//            'banner_event_' . $_locale,
            'image_banner_event_' . $_locale,
            'image_footer_banner_event',
            'banner_event_' . $_locale,
        ];

        $data = DB::table('tbl_options')->select('name', 'value')->whereIn('name', $array)->get();
        $dataResult = [];
        $info_event = $this->dbService->info_data_articles_is_hot($this->request);
        $dataInfoEvent = $info_event->getData(true);
        if ($dataInfoEvent['result']) {
            $dataResult['info_event'] = $dataInfoEvent['data'];
        } else {
            $dataResult['info_event'] = [];
        }
        foreach ($data as $key => $value) {
            if ($value->name == 'banner_event_' . $_locale) {
                $dataResult['banner_event'] = json_decode($value->value);
            } else {
                if ($value->name == 'image_banner_event_' . $_locale) {
                    $imgthumbnal = $value->value;
                    $imgthumbnal = !empty($imgthumbnal) ? $imgthumbnal : imgCameraDefault();
                    $dataResult['image_banner_event'] = asset($imgthumbnal);
                } else if ($value->name == 'image_footer_banner_event') {
                    $imgthumbnal = $value->value;
                    $imgthumbnal = !empty($imgthumbnal) ? $imgthumbnal : imgCameraDefault();
                    $dataResult['image_footer_banner_event'] = asset($imgthumbnal);
                }
            }

        }
        return response()->json([
            'result' => true,
            'data' => $dataResult
        ], 200);
    }

    public function terms()
    {
        $_locale = $this->request->input('_locale');
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $data = Terms::select('tbl_terms.id', 'tt.title', 'tt.content')
            ->where('type', 1)
            ->where('active', 1)
            ->join('tbl_terms_translations as tt', function ($join) use ($_locale) {
                $join->on('tt.id_terms', '=', 'tbl_terms.id')
                    ->where('tt.language', '=', $_locale);
            })->orderBy('order_by', 'desc')->get();

        return response()->json([
            'result' => true,
            'data' => $data
        ], 200);
    }

    //lấy thông tin chương trình referral
    public function policy()
    {
        $_locale = $this->request->input('_locale');
        if (empty($_locale)) {
            $_locale = 'vi';
        }

        $id_event_articles = $this->request->input('id_event_articles');
        if (!empty($id_event_articles)) {
            $this->dbService->get_list_by_ids($id_event_articles);
            $response = $this->dbService->get_list_by_ids(
                'api/event_articles/get_list_by_ids',
                array_unique([$id_event_articles]),
                $_locale
            );
            $dataResponse = $response->getData(true);
            $list_event_articles = $dataResponse['data'] ?? [];
            $event_articles = $list_event_articles[$id_event_articles];
        }

        $listKey = [
            'policy_challenge_' . $_locale,
            'title_when_success_challenge_' . $_locale,
            'title_when_fail_challenge_' . $_locale,
            'when_success_challenge_' . $_locale,
            'when_fail_challenge_' . $_locale,
            'radio_challenge_success',
        ];
        $listKeyShow = [
            ('policy_challenge_' . $_locale) => 'policy_challenge',
            ('title_when_success_challenge_' . $_locale) => 'title_when_success_challenge',
            ('title_when_fail_challenge_' . $_locale) => 'title_when_fail_challenge',
            ('when_success_challenge_' . $_locale) => 'when_success_challenge',
            ('when_fail_challenge_' . $_locale) => 'when_fail_challenge',
            ('radio_challenge_success') => 'radio_challenge_success',
        ];
        $radio_challenge_success = get_option('radio_challenge_success');
        $dataField = DB::table('tbl_options')->where(function ($field) use ($listKey) {
            $field->whereIn('name', $listKey); //ID của onesinal và key của onesinal
        })->get();
        $data = [];
        $href_articles = 'https://maskforyou.vn/vi/event/' . ($event_articles['slug'] ?? '');
        foreach ($dataField as $key => $value) {
            $dataVal = $value->value;
            $dataVal = str_replace('{radio_challenge_success}', $radio_challenge_success, $dataVal);
            $dataVal = str_replace('{event_articles}', ($event_articles['name'] ?? ''), $dataVal);
            $dataVal = str_replace('/admin/{href_articles}', $href_articles, $dataVal);
            $dataVal = str_replace('{href_articles}', $href_articles, $dataVal);
            $data[$listKeyShow[$value->name]] = $dataVal;
        }
        return response()->json([
            'result' => true,
            'data' => $data
        ], 200);
    }

    public function how_to_join()
    {
        $_locale = $this->request->input('_locale');
        $type = $this->request->input('type') ?? 'daily';
        $haru_xu = $this->request->input('haru_xu') ?? 0;
        if (empty($_locale)) {
            $_locale = 'vi';
        }

        $listKey = [
            'how_to_join_' . $type . '_' . $_locale,
        ];

        $listKeyShow = [
            ('how_to_join_' . $type . '_' . $_locale) => 'how_to_join',
        ];
        $dataField = DB::table('tbl_options')->where(function ($field) use ($listKey) {
            $field->whereIn('name', $listKey); //ID của onesinal và key của onesinal
        })->get();
        $data = [];
        foreach ($dataField as $key => $value) {
            $ContentDataJoin = json_decode($value->value, true);
            foreach ($ContentDataJoin as $k => $v) {
                $v['content'] = str_replace('{haru_xu}', $haru_xu, $v['content']);
                $v['title'] = str_replace('{haru_xu}', $haru_xu, $v['title']);
                $ContentDataJoin[$k] = $v;
            }
            $data[$listKeyShow[$value->name]] = $ContentDataJoin;
        }
        return response()->json([
            'result' => true,
            'data' => $data
        ], 200);
    }

    public function termsQuestion()
    {
        $_locale = $this->request->input('_locale');
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $data = Terms::select('tbl_terms.id', 'tt.title', 'tt.content')
            ->where('type', 2)
            ->where('active', 1)
            ->join('tbl_terms_translations as tt', function ($join) use ($_locale) {
                $join->on('tt.id_terms', '=', 'tbl_terms.id')
                    ->where('tt.language', '=', $_locale);
            })->orderBy('order_by', 'desc')->get();

        return response()->json([
            'result' => true,
            'data' => $data
        ], 200);
    }

    public function getListDiscountOrder()
    {
        $_locale = $this->request->input('_locale');
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $data = DB::table('tbl_discount_total_orders')->get();

        return response()->json([
            'result' => true,
            'data' => $data
        ], 200);
    }
    public function get_code_leader($field = '')
    {
        $data = DB::table('tbl_users')->where('code_introduce', $field)->where('active', 1)->first();
        return response()->json(['result' => $data]);
    }
    public function getOption($field = '')
    {
        $data = get_option($field);
        return response()->json(['result' => $data]);
    }


    public function updateOption($field = '', $value = ''){
        DB::table('tbl_options')->where('name', $field)->update([
            'value' => $value
        ]);
        $app = new App();
        $app->flushCache();
        return response()->json(['result' => true, 'message' => 'Cập nhật thành công']);
    }

}
