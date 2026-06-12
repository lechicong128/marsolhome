<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\NotificationCollection;
use App\Http\Resources\Notification as NotificationResource;
use App\Models\Notification;
use App\Models\User;
use App\Traits\SocketTrait;
use App\Services\AccountService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationController extends AuthController
{

    use SocketTrait;

    public function __construct(Request $request,AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->dbAccount = $accountService;
    }

    public function getListNotification()
    {
        $current_page = 1;
        $per_page = 10;
        $status = $this->request->input('status');
        if (!is_numeric($status)) {
            $status = -1;
        }
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $dateStart = $this->request->input('date_start');
        if (!empty($dateStart)) {
            $dateStart = to_sql_date($dateStart);
        }

        $dateEnd = $this->request->input('date_end');
        if (!empty($dateEnd)) {
            $dateEnd = to_sql_date($dateEnd);
        }


        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $dtNotification = Notification::select(
            'tbl_notification.*',
            'tbl_notification_staff.is_read',
            'tbl_notification_staff.object_id as customer_id',
            'tbl_notification_staff.object_type as type_customer',
        )
            ->join('tbl_notification_staff', 'tbl_notification_staff.notification_id', '=', 'tbl_notification.id')
            ->where(function ($query) use ($customer_id, $status) {
                $query->where('tbl_notification_staff.object_id', $customer_id);
                $query->where(function ($q) {
                    $q->where('tbl_notification_staff.object_type', 'customer');
                });
                if ($status >= 0) {
                    $query->where('tbl_notification_staff.is_read', $status);
                }
            })
            ->when(
                !empty($dateStart),
                fn($q) =>
                $q->whereDate('tbl_notification.created_at', '>=', $dateStart)
            )
            ->when(!empty($dateEnd), function ($q) use ($dateEnd) {
                $q->whereRaw('DATE_FORMAT(tbl_notification.created_at, "%Y-%m-%d") <= ?', [$dateEnd]);
            })
            ->orderByRaw('tbl_notification.created_at desc,tbl_notification.id desc')
            ->paginate($per_page, ['*'], '', $current_page);
        
        $customerIds = $dtNotification
            ->map(function ($item) {
                $data = json_decode($item->json_data, true);
                return $data['customer']['id'] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        $dataCustomer = [];
        if(!empty($customerIds)){
            $requestCustomer = new Request();
            $requestCustomer->merge(['list_id' => $customerIds]);
            $responseCustomer = $this->dbAccount->getListDetailCustomer($requestCustomer);
            $listDataCustomer = $responseCustomer->getData(true);
            if ($listDataCustomer['result']) {
                $dataCustomer = $listDataCustomer['clients'];
            }
        }
        $dtNotification->getCollection()->transform(function ($item) use ($dataCustomer) {
            $data = json_decode($item->json_data, true);
            $customer = collect($dataCustomer)->where('id', ($data['customer']['id'] ?? 0))->first();
            $item->customer = $customer ?? null;
            return $item;
        });
        return new NotificationCollection($dtNotification);
    }

    public function CountNotification()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $dtNotificationIsNotRead = Notification::join('tbl_notification_staff', 'tbl_notification_staff.notification_id', '=', 'tbl_notification.id')
            ->where(function ($query) use ($customer_id) {
                $query->where('tbl_notification_staff.object_id', $customer_id);
                $query->where(function ($q) {
                    $q->where('tbl_notification_staff.object_type', 'customer');
                });
            })->where('is_read', 0)
            ->count();

        $dtNotificationIsRead = Notification::join('tbl_notification_staff', 'tbl_notification_staff.notification_id', '=', 'tbl_notification.id')
            ->where(function ($query) use ($customer_id) {
                $query->where('tbl_notification_staff.object_id', $customer_id);
                $query->where(function ($q) {
                    $q->where('tbl_notification_staff.object_type', 'customer');
                });
            })->where('is_read', 1)
            ->count();


        $data['result'] = true;
        $data['data'] = [
            [
                'count' => ($dtNotificationIsRead + $dtNotificationIsNotRead),
                'id' => '-1',
                'color' => '#989898',
                'name' => lang('all')
            ],
            [
                'count' => $dtNotificationIsNotRead,
                'id' => '0',
                'color' => '#f05050',
                'name' => lang('is_not_read')
            ],
            [
                'count' => $dtNotificationIsRead,
                'id' => '1',
                'color' => '#81c868',
                'name' => lang('is_read')
            ],
        ];
        return response()->json($data);
    }

    public function getDetail($id = 0)
    {
        $dtNotification = Notification::select(
            'tbl_notification.*',
            'tbl_notification_staff.is_read',
            'tbl_notification_staff.object_id as customer_id',
            'tbl_notification_staff.object_type as type_customer',
        )
            ->join('tbl_notification_staff', 'tbl_notification_staff.notification_id', '=', 'tbl_notification.id')
            ->where(function ($query) use ($id) {
                $query->where('tbl_notification.id', $id);
            })->first();
        $LangContent = LangNoti($dtNotification->content);
        $dtNotification->content = $LangContent;
        return NotificationResource::make($dtNotification);
    }

    public function readAllNotification()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $type = !empty($this->request->input('type')) ? $this->request->input('type') : null;
        if (empty($customer_id)) {
            $data['result'] = false;
            $data['message'] = lang('khong_ton_tai_nguoi_dung');
            return response()->json($data);
        }
        $success = DB::table('tbl_notification_staff')
            ->where(function ($query) use ($customer_id, $type) {
                $query->where('is_read', 0);
                $query->where('object_id', $customer_id);
                $query->where(function ($q) {
                    $q->where('tbl_notification_staff.object_type', 'customer');
                    $q->orWhere('tbl_notification_staff.object_type', 'owen');
                });
            })
            ->update(['is_read' => 1]);

        $data['result'] = 1;
        $data['message'] = lang('dt_success');
        return response()->json($data);

    }

    public function readSingleNotification()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $notification_id = $this->request->input('notification_id') ? $this->request->input('notification_id') : 0;
        $notification = Notification::find($notification_id);
        if (empty($notification)) {
            $data['result'] = false;
            $data['message'] = lang('khong_ton_tai_thong_bao');
            return response()->json($data);
        }
        if (empty($customer_id)) {
            $data['result'] = false;
            $data['message'] = lang('khong_ton_tai_nguoi_dung');
            return response()->json($data);
        }
        $success = DB::table('tbl_notification_staff')
            ->where(function ($query) use ($customer_id, $notification_id) {
                $query->where('is_read', 0);
                $query->where('object_id', $customer_id);
                $query->where('notification_id', $notification_id);
            })
            ->update(['is_read' => 1]);

        $data['result'] = 1;
        $data['message'] = lang('dt_success');
        return response()->json($data);
    }

    public function checkReadNoti()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $type = !empty($this->request->input('type')) ? $this->request->input('type') : null;
        $dtNoti = Notification::whereHas('notification_staff', function ($query) use ($customer_id, $type) {
            $query->where('is_read', 0);
            $query->where('object_id', $customer_id);
            $query->where(function ($q) {
                $q->where('tbl_notification_staff.object_type', 'customer');
                $q->orWhere('tbl_notification_staff.object_type', 'owen');
            });
        })->count();
        $data['check'] = $dtNoti;
        $data['result'] = true;
        return response()->json($data);
    }
    public function addNotiPost()
    {
        $type_noti = $this->request->input('type_noti') ?? null;
        $arr_object_id = $this->request->input('arr_object_id');
        $customer_id = $this->request->input('customer_id');
        $type = $this->request->input('type');
        $staff_id = $this->request->input('staff_id');
        $point = $this->request->input('point') ?? 0;
        $locale = $this->request->input('locale') ?? 'vi';

        $actorName = $this->request->input('actorName') ?? null;
        $likerId = $this->request->input('likerId') ?? null;
        $commenterId = $this->request->input('commenterId') ?? null;
        $others = $this->request->input('others') ?? null;
        $dtData = $this->request->input('dtData');
        $taggedUsers = $this->request->input('tagged_users');
        $arr_object_id_tag = $this->request->input('arr_object_id_tag');
        $arr_object_id_comment = $this->request->input('arr_object_id_comment');

        if ($type_noti == 'notifyLikePost') {
            $check = Notification::notifyLikePost($customer_id, $arr_object_id, $actorName, $likerId, $others, $dtData, $locale);
        }
        if ($type_noti == 'notifyComment') {
            $check = Notification::notifyComment($customer_id, $taggedUsers, $actorName, $commenterId, $arr_object_id, $dtData, $locale, $arr_object_id_tag, $arr_object_id_comment);
            // $data['result'] = true;
            // $data['message'] = $check;
            // return response()->json($data);
        }
        if ($type_noti == 'notifyLikeComment') {
            $check = Notification::notifyLikeComment($customer_id, $taggedUsers, $actorName, $commenterId, $arr_object_id, $dtData, $locale);
            // $data['result'] = true;
            // $data['message'] = $check;
            // return response()->json($data);
        }
        $data['result'] = true;
        $data['message'] = lang('dt_success');
        return response()->json($data);
    }

    public function addNoti()
    {
        $type_noti = $this->request->input('type_noti') ?? null;
        $arr_object_id = $this->request->input('arr_object_id');
        $dtData = $this->request->input('dtData');
        $customer_id = $this->request->input('customer_id');
        $type = $this->request->input('type');
        $staff_id = $this->request->input('staff_id');
        $point = $this->request->input('point') ?? 0;
        $point_client = $this->request->input('point_client') ?? 0;
        $title_point = $this->request->input('title_point') ?? null;
        $type_check = $this->request->input('type_check') ?? 1;
        $locale = $this->request->input('locale') ?? 'vi';
        $customer_id_new = $customer_id;
        if ($type == 'staff') {
            $check = 1;
            $customer_id = get_staff_user_id() ?? $staff_id;
        } else {
            $check = 2;
            $customer_id = $customer_id;
        }
        $arrType = ['change_point', 'noti_referral_parent_customer', 'noti_add_point_parent_customer_referral', 'noti_affiliate_customer_finish', 'noti_affiliate_customer'];
        $dtStaffAdmin = User::select(
            'tbl_users.name',
            'tbl_users.id as object_id',
            'tbl_player_id.player_id as player_id',
            DB::raw("'staff' as 'object_type'")
        )
            ->leftJoin('tbl_player_id', function ($join) {
                $join->on('tbl_player_id.object_id', '=', 'tbl_users.id');
                $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
            })
            ->where('admin', 1)
            ->where('active', 1)
            ->get()->toArray();
        if (!in_array($type_noti, $arrType)) {
            if (!empty($dtStaffAdmin)) {
                $arr_object_id = array_merge($arr_object_id, $dtStaffAdmin);
            }
        }
        if ($type_noti == 'noti_referral_parent_customer') {
            Notification::notiReferralParentCustomer(
                $dtData,
                Config::get('constant')['noti_referral_parent_customer'],
                $customer_id,
                $check,
                $arr_object_id
            );
        }
        elseif ($type_noti == 'noti_affiliate_customer') {
            Notification::notiAffiliateCustomer($dtData);
        }
        elseif ($type_noti == 'noti_affiliate_customer_finish') {
            Notification::notiAffiliateCustomerFinish($dtData);
        }
        elseif ($type_noti == 'noti_add_point_parent_customer_referral') {
            Notification::notiAddPointParentCustomer(
                $dtData,
                Config::get('constant')['noti_add_point_parent_customer_referral'],
                $customer_id,
                $check,
                $arr_object_id
            );
        }
        elseif ($type_noti == 'noti_approve_transaction') {
            Notification::notiChangeStatusTransaction(
                $dtData,
                Config::get('constant')['noti_approve_transaction'],
                $customer_id,
                $check,
                $arr_object_id
            );
        }
        elseif ($type_noti == 'noti_finish_transaction') {
            Notification::notiChangeStatusTransaction(
                $dtData,
                Config::get('constant')['noti_finish_transaction'],
                $customer_id,
                $check,
                $arr_object_id
            );
        }
        elseif ($type_noti == 'noti_cancel_transaction') {
            Notification::notiChangeStatusTransaction(
                $dtData,
                Config::get('constant')['noti_cancel_transaction'],
                $customer_id,
                $check,
                $arr_object_id
            );
        }
        elseif ($type_noti == 'remind_payment') {
            Notification::notiRemindPaymentTransaction(
                $dtData,
                Config::get('constant')['noti_remind_payment'],
                $customer_id,
                $check,
                $arr_object_id
            );
        }
        elseif ($type_noti == 'change_point') {
            Notification::notiChangePointClient($customer_id_new, $arr_object_id, $point, $point_client, $title_point, $locale);
        }
        elseif ($type_noti == 'change_balance') {
            Notification::notiChangeBalance($customer_id_new, $arr_object_id, $point, $point_client, $title_point);
        }
        elseif ($type_noti == 'noti_upgrade_membership') {
            Notification::notiUpgradeMembership($customer_id_new, $dtData, $type_check, $arr_object_id);
        } elseif ($type_noti == 'change_status_payment') {
            $this->sendNotificationSocket([
                'channels' => $arr_object_id,
                'event' => 'payment_transaction',
                'data' => $dtData,
                'db_name' => config('database.connections.mysql.database')
            ], 'change-status');
            Notification::notiPaymentTransaction(
                $dtData,
                Config::get('constant')['noti_transaction_payment'],
                $customer_id,
                $check,
                $arr_object_id
            );
        }
        elseif ($type_noti == 'review_items') {
            Notification::notiActiveReivewClient($customer_id_new, $arr_object_id, $point, $locale);

        }
        elseif ($type_noti == 'authenticated_challengeMe') {
            Notification::notiAuthenticatedChallengeMe($customer_id_new, $arr_object_id, $point, $locale);

        }
        elseif ($type_noti == 'plus_refund') {
            Notification::notiPlusRefundChallengeMe($customer_id_new, $arr_object_id, $point, $locale);
        }
        elseif ($type_noti == 'contribute') {
            Notification::notiContributeClient($customer_id_new, $dtData, $arr_object_id, $locale);
        }
        elseif ($type_noti == 'update_rank_challenge') {
            Notification::notiUpdateRankChallengeClient($customer_id_new, $dtData, $arr_object_id, $locale);
        }
        elseif ($type_noti == 'challenge_me_fail') {
            //thông báo rằng thử thách đã thất bại
            Notification::notiChallengeMeFail($customer_id_new, $dtData, $arr_object_id, $locale);
        }
        elseif ($type_noti == 'challenge_me_success') {
            //thông báo rằng thử thách đã thất bại
            Notification::notiChallengeMeSuccess($customer_id_new, $dtData, $arr_object_id, $locale);
        }
        elseif ($type_noti == 'general') {
            Notification::notiGeneralClient($customer_id_new, $dtData, $arr_object_id, $locale);
        }
        elseif ($type_noti == 'message') {
            Notification::notiMessage($customer_id_new, $dtData, $arr_object_id, $locale);
        }
        else {
            $this->sendNotificationSocket([
                'channels' => $arr_object_id,
                'event' => 'check-payment',
                'data' => $dtData,
                'db_name' => config('database.connections.mysql.database')
            ], 'change-status');
            Notification::notiPaymentTransactionPackage(
                $dtData,
                Config::get('constant')['noti_transaction_package_payment'],
                $customer_id,
                $check,
                $arr_object_id
            );
        }
        $data['result'] = true;
        $data['message'] = lang('dt_success');
        return response()->json($data);
    }


    public function addNotiMutil()
    {
        $type_noti = $this->request->input('type_noti') ?? null;
        $arr_object_id = $this->request->input('arr_object_id');
        $dtData = $this->request->input('dtData');
        $info_client = $this->request->input('info_client');
        if(!empty($info_client) && !empty($dtData) && !empty($arr_object_id) && !empty($type_noti)) {
            $sendNoti = Notification::notiClientMutil($info_client, $dtData, $arr_object_id, $type_noti);
        }
        $data['data'] = $sendNoti ?? [];
        $data['result'] = true;
        $data['message'] = lang('dt_success');
        return response()->json($data);
    }
}
