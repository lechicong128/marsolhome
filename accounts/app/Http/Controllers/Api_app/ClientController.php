<?php

namespace App\Http\Controllers\Api_app;

use App\Models\BonusPayment;
use App\Models\ClientAddress;
use App\Models\ClientIntroduce;
use App\Models\Clients;
use App\Helpers\FilesHelpers;
use App\Models\Promotion;
use App\Models\ReferralLevel;
use App\Models\SyntheticAffiliate;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ClientInformationVat;
use App\Services\AdminService;
use App\Traits\UploadFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

//use App\Services\AccountService;
//use App\Services\AresService;
use Yajra\DataTables\CollectionDataTable;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomerResources;

class ClientController extends AuthController
{
    use UploadFile;

    protected $AdminService;
    protected $_locale;

    public function __construct(Request $request, AdminService $adminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
        $this->AdminService = $adminService;
        $this->_locale = $request->_locale;
    }

    public function getListCustomer()
    {
        $filter = $this->request->input('filter');
        $type_client_search = $filter['type_client_search'] ?? -1;
        $active_search = $filter['active_search'] ?? -1;
        $search = $this->request->input('search');
        $orderBy = $this->request->input('order_by', 'id');
        $orderDir = $this->request->input('order_dir', 'asc');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $query = Clients::select('*', DB::raw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar'))
            ->where('id', '!=', 0);
        $query->with([
            'client_intro_level' => function ($q) {
                $q->select('id', 'id_client_introduce', 'id_client');
                $q->with([
                    'parent' => function ($qr) {
                        $qr->select('id', 'fullname', 'email', 'phone', 'avatar', 'type_client', 'code_introduce');
                    }
                ]);
            }
        ]);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }
        if ($type_client_search != -1) {
            $query->where('type_client', $type_client_search);
        }
        if ($active_search != -1 && $active_search != '') {
            $query->where('active', $active_search);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        
        $total = $query->count();
        return response()->json([
            'total' => $total,
            '_locale' => $this->request->_locale,
            'filtered' => $filtered,
            'data' => $data
        ]);
    }

    public function countAll()
    {
        $filter = $this->request->input('filter') ?? $this->request->all();
        $type_client_search = $filter['type_client_search'] ?? -1;
        $active_search = $filter['active_search'] ?? -1;

        $arrType = [
            [
                'id' => 0,
            ],
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ];

        $query = Clients::where('id', '!=', 0);
        if ($active_search != -1 && $active_search != '') {
            $query->where('active', $active_search);
        }
        $totalAll = $query->count();

        foreach ($arrType as $key => $value) {
            $type_client = $value['id'];
            $query = Clients::where('id', '!=', 0);
            $query->where('type_client', $type_client);
            if ($active_search != -1 && $active_search != '') {
                $query->where('active', $active_search);
            }
            $total = $query->count();
            $arrType[$key]['total'] = $total;
        }

        return response()->json([
            'total' => $totalAll,
            'arrType' => $arrType,
        ]);
    }

    public function getDetailCustomer()
    {
        $id = $this->request->input('id') ?? 0;
        $query = Clients::with([
            'referral_lel' => function ($q) {
                $q->select('id', 'id_client_introduce', 'id_client');
                $q->with([
                    'parent' => function ($qr) {
                        $qr->select('id', 'fullname', 'email','phone','avatar','type_client');
                    }
                ]);
            },
            'client_information_vat' => function ($q) {
                $q->select('id', 'customer_id', 'type', 'name', 'company', 'vat', 'address', 'payment_mode','account_name', 'account_no', 'email');
            }
        ]);
        $client = $query->find($id);
        if (!empty($client)) {
            $dtImage = !empty($client->avatar) ? $this->baseUrl . '/' . $client->avatar : null;
            $client->avatar = $dtImage;
            $client->count_install_app = $client->referral->count();
        }
        $storageUrl = $this->baseUrl;
        $arrId = getDataTreeReferralLevel($id);
        $dataReferralLevel = ClientIntroduce::select('id','id_client','id_client_introduce')
            ->with(['customer' => function ($query) use($storageUrl) {
                $query->select('id', 'fullname', 'code','phone','email','type_client', DB::raw("IF(avatar IS NOT NULL, CONCAT('$storageUrl/', avatar), NULL) as avatar"));
            }])->whereIn('id_client',$arrId)->get();
        $newElement = new ClientIntroduce();
        $newElement->id_client = $id;
        $newElement->id_client_introduce = 0;
        $dataReferralLevel->prepend($newElement);
        $newElement->load(['customer' => function ($query) use($storageUrl) {
            $query->select('id', 'fullname', 'code','phone','email','type_client', DB::raw("IF(avatar IS NOT NULL, CONCAT('$storageUrl/', avatar), NULL) as avatar"));
        }]);
        $dtReferralLevelValue = get_parent_id_referral_level_new($dataReferralLevel);
        $dtReferralLevel = collect(Arr::pluck($dtReferralLevelValue,'level'));
        $level = $dtReferralLevel->max();
        $countMember = ($dtReferralLevel->count()) - 1;
        $data['referral'] = [
            'level' => $level,
            'count_member' => $countMember,
            'data' => $dtReferralLevelValue
        ];
        $data['result'] = true;
        $data['client'] = $client;


        $data['message'] = 'Lấy thông tin khách hàng thành công';
        return response()->json($data);
    }

    public function getListDetailCustomer()
    {
        $list_id = $this->request->input('list_id') ?? [];
        $search = $this->request->input('search') ?? NULL;
        if (!empty($list_id) || !empty($search)) {
            $clients = Clients::whereRaw('phone is not null');
            if (!empty($list_id)) {
                $clients->whereIn('id', $list_id);
            }
            if (!empty($search)) {
                if (is_array($search)) {
                    $search = $search['value'] ?? null;
                }
                $clients->where(function ($q) use ($search) {
                    $q->where('fullname', 'like', "%$search%");
                    $q->orWhere('phone', 'like', "%$search%");
                    $q->orWhere('address', 'like', "%$search%");
                });
            }
            $clients = $clients->get();
            $dataClients = [];
            if (!empty($clients)) {
                foreach ($clients as $key => $client) {
                    $dataClients[$client->id] = [
                        'id' => $client->id,
                        'code' => $client->code,
                        'fullname' => $client->fullname,
                        'phone' => $client->phone,
                        'address' => $client->address,
                        'type_client' => $client->type_client,
                        'active' => $client->active,
                        'avatar' => !empty($client->avatar) ? ($this->baseUrl . '/' . $client->avatar) : null,
                    ];
                }
            }
            $data['result'] = true;
            $data['clients'] = $dataClients;
        } else {
            $data['result'] = false;
            $data['clients'] = [];
        }


        $data['message'] = 'Lấy thông tin khách hàng thành công';
        return response()->json($data);
    }
    public function getDetailCustomerPlayerid(){
        $id = $this->request->input('id') ?? 0;
        // $client = Clients::find($id);
        // if (!empty($client)){
        //     $dtImage = !empty($client->avatar) ? $this->baseUrl.'/'.$client->avatar : null;
        //     $client->avatar = $dtImage;
        //     $client->count_install_app = $client->referral->count();
        // }
        // $data['result'] = true;
        // $data['client'] = $client;
        $arr_object_id = [];
        $dtCustomer = Clients::select(
            'tbl_clients.fullname as name',
            'tbl_clients.id as object_id',
            'tbl_player_id.player_id as player_id',
            DB::raw("'customer' as 'object_type'")
        )
            ->leftJoin('tbl_player_id', function ($join) {
                $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
            })
            ->where('tbl_clients.id', $id)
            ->get()->toArray();
        if (!empty($dtCustomer)) {
            $arr_object_id = array_merge($arr_object_id, $dtCustomer);
        }
        $data['result'] = true;
        $data['client'] = $arr_object_id;
        $data['message'] = 'Lấy thông tin khách hàng thành công';
        return response()->json($data);
    }
    public function detail()
    {
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        if (empty($client)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn tại khách hàng';
            return response()->json($data);
        }
        $ClientRules = [];
        if (filled($this->request->email)) {
            $ClientRules['email'] = 'unique:tbl_clients,email,' . $id;
        }
        if (filled($this->request->phone)) {
            $ClientRules['phone'] = 'unique:tbl_clients,phone,' . $id;
        }
        $clientMessages = [
            'email.unique' => 'Email người đã tồn tại',
            'phone.unique' => 'Số điện thoại đã tồn tại',
        ];
        $validatorClient = Validator::make($this->request->all(), $ClientRules, $clientMessages);
        if ($validatorClient->fails()) {
            $data['result'] = false;
            $data['message'] = $validatorClient->errors()->all()[0];
            echo json_encode($data);
            die();
        }


        DB::beginTransaction();
        try {
            $client->fullname = $this->request->fullname;
            $client->phone = $this->request->phone ?? NULL;
            $client->email = $this->request->email ?? NULL;
            $client->active = $this->request->active;
            $client->type_client = $this->request->type_client;
            $client->number_cccd = $this->request->number_cccd;
            $client->issued_cccd = $this->request->issued_cccd;
            // if ($this->request->type_client == 1) {
            //     $client->type_partner = 0;
            //     $client->mst = null;
            // } else {
            //     $client->type_partner = $this->request->type_partner;
            //     $client->mst = $this->request->mst;
            // }
            if (!empty($this->request->date_cccd)) {
                $client->date_cccd = to_sql_date($this->request->date_cccd);
            }
            if (!empty($this->request->date_passport)) {
                $client->date_passport = to_sql_date($this->request->date_passport);
            }
            $client->number_passport = $this->request->number_passport;
            $client->issued_passport = $this->request->issued_passport;
            if (!empty($this->request->password)) {
                $client->password = encrypt($this->request->password);
            }

            $client->province_id = $this->request->province_id ?? 0;
            $client->wards_id = $this->request->wards_id ?? 0;
            $client->address = $this->request->address ?? null;

            $client->active_limit_private = $this->request->active_limit_private ?? 0;
            if ($client->active_limit_private == 1) {
                $client->invoice_limit_private = $this->request->invoice_limit_private;
                $client->radio_discount_private = $this->request->radio_discount_private;
            } else {
                $client->radio_discount_private = NULL;
                $client->invoice_limit_private = NULL;
            }

            $client->save();
            if ($client) {
                if ($this->request->hasFile('avatar')) {
                    if (!empty($client->avatar)) {
                        $this->deleteFile($client->avatar);
                    }
                    $path = $this->UploadFile($this->request->file('avatar'), 'clients/' . $client->id, 70, 70, false);
                    $client->avatar = $path;
                    $client->save();
                }
                DB::commit();
                $data['result'] = true;
                $data['message'] = 'Cập nhật thành công';
            } else {
                $data['result'] = false;
                $data['message'] = 'Cập nhật thất bại';
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function deleteCustomer()
    {
        $token = $this->request->bearerToken();
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        if (empty($client)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn tại khách hàng';
            return response()->json($data);
        }
        if($client->id == 2){
            $data['result'] = false;
            $data['message'] = 'Tài khoản apple test không thể xóa';
            return response()->json($data);
        }
        
        DB::beginTransaction();
        $clientIntroduce = DB::table('tbl_client_introduce')->where('id_client_introduce', $id)->first();
        if (!empty($clientIntroduce)) {
            $data['result'] = false;
            $data['message'] = 'Tài khoản đã được giới thiệu cho người khác nên không thể xóa';
            return response()->json($data);
        }
        try {
            $client->delete();
            DB::table('tbl_session_login')->where('id_client', $id)->delete();

            DB::table('tbl_client_introduce')->where('id_client', $id)->delete();
            DB::table('tbl_client_introduce')->where('id_client_introduce', $id)->delete();

            if (!empty($token)) {
                //đồng bộ vsession của user
                $this->AdminService->PostAdmin($this->request, $token, 'api/reset_vsession');
            }
            ClientAddress::where('customer_id', $id)->delete();
            DB::commit();

            DB::table('tbl_code_leader')->where('customer_id', $id)->update([
                'status' => 0,
                'customer_id' => null,
                'used_at' => null
            ]);
            $data['result'] = true;
            $data['message'] = lang('c_delete_true');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function active()
    {
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        DB::beginTransaction();
        try {
            $client->active = $client->active == 0 ? 1 : 0;
            $client->save();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeStatusLeader()
    {
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        DB::beginTransaction();
        try {
            if($client->is_leader == 1) {
                $check_leader = get_parent_customer_leader($id);
                if(empty($check_leader)) {
                    $data['result'] = false;
                    $data['message'] = 'Không thể tắt trạng thái Leader vì phía trên không có Leader nào quản lý bạn sẽ dẫn đến lỗi khi phát sinh đơn hàng!';
                    return response()->json($data);
                }
            }
            $client->is_leader = $client->is_leader == 0 ? 1 : 0;
            $client->save();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeStatusTypeLeader()
    {
        $id = $this->request->input('id') ?? 0;
        $type_leader = $this->request->input('type_leader') ?? 0;
        $client = Clients::find($id);
        DB::beginTransaction();
        try {
            $client->type_leader = $type_leader;
            $client->save();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListData()
    {
        $search = $this->request->input('search') ?? null;
        $limit = $this->request->input('limit') ?? 50;
        $id = $this->request->input('customer_id') ?? [];
        $filter_home = $this->request->input('filter_home') ?? 0;
        $query = Clients::select('id', 'fullname', 'phone', 'avatar', 'email', 'type_client', 'created_at')
            ->where('active', 1)
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%");
                $q->orWhere('phone', 'like', "%$search%");
            });
        }
        if (!empty($id)) {
            $query->whereIn('id', $id);
        }
        if(!empty($filter_home)){
            $query->whereIn('type_client', [1,2]);
        }
        $data = $query->limit($limit)->get();

        // Batch fetch total homes from admin service to avoid N+1 queries
        $customerIds = $data->pluck('id')->toArray();
        $homeCounts = [];
        $transactionCounts = [];
        if (!empty($customerIds)) {
            $response = $this->AdminService->countHomes($customerIds);
            if (isset($response['result']) && $response['result']) {
                $homeCounts = $response['data'];
            }

            $transactionCounts = DB::table('tbl_transaction')
                ->select('customer_id', DB::raw('count(*) as total'))
                ->whereIn('customer_id', $customerIds)
                ->groupBy('customer_id')
                ->pluck('total', 'customer_id')
                ->toArray();
        }

        foreach ($data as $item) {
            $item->total_homes = $homeCounts[$item->id] ?? 0;
            $item->total_transactions = $transactionCounts[$item->id] ?? 0;
        }

        return response()->json([
            'data' => CustomerResources::collection($data),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function updateTypeClient()
    {
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        DB::beginTransaction();
        try {
            $client->type_client = 2;
            $client->count_review = ($client->count_review + 1);
            $client->save();

            $clientIntroduce = DB::table('tbl_client_introduce')
                ->where('id_client', $id)->first();

            DB::table('tbl_client_introduce')
                ->where('id_client', $id)
                ->update(['status' => 1]);

            if (!empty($clientIntroduce->id_client_introduce)) {
                reviewClassClientApi($clientIntroduce->id_client_introduce);
            }

            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListInfoShortClient()
    {
        $list_id_client = $this->request->input('list_id_client') ?? [];
        if (!empty($list_id_client)) {
            $client = Clients::whereIn('id', $list_id_client)->select(
                'id',
                'fullname',
                'phone',
                'birthday',
                'email',
                'type_client',
                'address',
                DB::raw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar')
            )->get();
            return response()->json([
                'result' => true,
                'message' => lang('dt_success'),
                'data' => $client ?? [],
            ]);
        } else {
            return response()->json([
                'result' => false,
                'message' => lang('dt_fail'),
                'data' => [],
            ]);
        }
    }

    public function getClientsIntroduce()
    {
        $filter = $this->request->input('filter');
        $type_client_search = $filter['type_client_search'] ?? 0;
        $active_search = $filter['active_search'] ?? -1;
        $search = $this->request->input('search');
        $orderBy = $this->request->input('order_by', 'id');
        $orderDir = $this->request->input('order_dir', 'asc');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $customer_id = $filter['customer_id'] ?? 0;
        $query = DB::table('tbl_client_introduce')
            ->leftJoin('tbl_clients', 'tbl_client_introduce.id_client', '=', 'tbl_clients.id')
            ->select('*', DB::raw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar'), DB::raw('CONCAT(LEFT(phone, 6), "****") AS phone'))
            ->where('tbl_client_introduce.id_client_introduce', '=', $customer_id);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }
        if (($type_client_search)) {
            $query->where('type_client', $type_client_search);
        }
        if ($active_search != -1 && $active_search != '') {
            $query->where('active', $active_search);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        $total = DB::table('tbl_client_introduce')
            ->where('tbl_client_introduce.id_client_introduce', '=', $customer_id)
            ->count();
        return response()->json([
            'total' => $total,
            '_locale' => $this->request->_locale,
            'filtered' => $filtered,
            'data' => $data
        ]);
    }

    public function getListCustonerIdReferral()
    {
        $customer_id = $this->request->input('customer_id') ?? 0;
        $arrCustomerId = DB::table('tbl_client_introduce')->where('tbl_client_introduce.id_client_introduce', '=', $customer_id)->pluck('id_client')->toArray();
        return response()->json([
            'data' => $arrCustomerId,
            'result' => true,
        ]);
    }

    public function addReferral()
    {
        $_locale = $this->_locale;
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $referral_code = $this->request->input('referral_code') ?? null;
        $machines_id = $this->request->input('machines_id') ?? null;
        $type = $this->request->input('type') ?? null;
        $data['title_referral'] = null;
        $data['content_referral_new'] = null;
        $data['show_modal'] = false;
        $data['title'] = lang('notification');
        if (!empty($referral_code) && !empty($machines_id)) {
            $dtClient = Clients::where('code_introduce', $referral_code)->first();
            if (!empty($dtClient)) {
                $check = ReferralLevel::where('machines_id', $machines_id)->where('referral_code', $referral_code)->first();
                if (empty($check)) {
                    ReferralLevel::where('machines_id', $machines_id)->update([
                        'status_cancel' => 1
                    ]);
                    DB::beginTransaction();
                    try {
                        $referral = new ReferralLevel();
                        $referral->parent_id = $dtClient->id;
                        $referral->referral_code = $referral_code;
                        $referral->type = $type;
                        $referral->machines_id = $machines_id;
                        $referral->save();

                        //lưu khuyến mãi
                        $checkPromotion = Promotion::where('machines_id', $machines_id)->where('require_first_order', 1)->first();
                        $percent_referral = $this->AdminService->get_option('percent_referral');
                        $money_max_referral = $this->AdminService->get_option('money_max_referral');
                        $content_referral = $this->AdminService->get_option('content_referral_' . $_locale . '');
                        $content_referral = json_decode($content_referral, true);
                        $title_referral = $content_referral['title'];
                        $content_referral_new = $content_referral['content'];
                        $content_referral_new = str_replace('{percent}', $percent_referral . ' %', $content_referral_new);

                        //kt xem có đơn hàng sử dụng km này chưa
                        $checkTransaction = Transaction::where(function ($query) use ($machines_id, $checkPromotion) {
                            $query->where('machines_id', $machines_id);
                            $query->where('promotion_id', $checkPromotion->id ?? 0);
                            $query->whereIn('status',
                                [
                                    Config::get('constant')['status_approve'],
                                    Config::get('constant')['status_finish'],
                                    Config::get('constant')['status_request'],
                                ]);
                        })->first();
                        if (empty($checkTransaction)) {
                            if (empty($checkPromotion)) {
                                $promotion = new Promotion();
                                $promotion->code = $machines_id;
                                $promotion->name = '' . $percent_referral . '% off';
                                $promotion->type = 0;
                                $promotion->percent = $percent_referral;
                                $promotion->money_max = $money_max_referral;
                                $promotion->cash = 0;
                                $promotion->number_day = 1;
                                $promotion->type_use_one = 1;
                                $promotion->indefinite = 1;
                                $promotion->quantity = 0;
                                $promotion->date_start = null;
                                $promotion->date_end = null;
                                $promotion->type_customer = 1;
                                $promotion->detail = '' . $percent_referral . '% off, up to ' . $money_max_referral . 'K';
                                $promotion->note = 'Promo voucher for ' . $percent_referral . '% off on your first order';
                                $promotion->created_by = 0;
                                $promotion->active = 1;
                                $promotion->machines_id = $machines_id;
                                $promotion->require_first_order = 1;
                                $promotion->save();
                                $checkInsert = true;
                            } else {
                                $checkInsert = true;
                            }
                            if ($checkInsert) {
                                $data['title_referral'] = $title_referral;
                                $data['content_referral_new'] = $content_referral_new;
                                $data['show_modal'] = true;
                            }
                        }
                        DB::commit();
                        $data['result'] = true;
                        $data['message'] = lang('dt_success');
                        return response()->json($data);
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        $data['result'] = false;
                        $data['message'] = $exception->getMessage();
                        return response()->json($data);
                    }
                } else {
                    $data['result'] = false;
                    $data['message'] = lang('dt_error');
                    return response()->json($data);
                }
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_not_client');
                return response()->json($data);
            }
        } else {
            $data['result'] = false;
            $data['message'] = lang('dt_not_exists_referral');
            return response()->json($data);
        }
    }

    public function getDataReferral()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $query = Clients::select('id', 'fullname');
        $query->addSelect(DB::raw('(SELECT COUNT(tbl_client_introduce.id)
                                            FROM tbl_client_introduce
                                            WHERE tbl_client_introduce.id_client_introduce = tbl_clients.id) AS client_introduce'));
        $query->addSelect(DB::raw('(SELECT COUNT(tbl_referral_level.id)
                                            FROM tbl_referral_level
                                            WHERE tbl_referral_level.parent_id = tbl_clients.id) AS count_install_app'));
        $query->addSelect(DB::raw('(SELECT GROUP_CONCAT(tbl_client_introduce.id_client)
                                            FROM tbl_client_introduce
                                            WHERE tbl_client_introduce.id_client_introduce = tbl_clients.id) AS id_client'));
        $query->where('id', $customer_id);
        $dataCustomer = $query->first();
        $id_client = $dataCustomer['id_client'] ?? 0;
        $id_client = explode(',', $id_client);
        $requestReview = clone $this->request;
        $requestReview->merge(['customer_id' => $id_client]);

        $responseReview = $this->AdminService->getDataClientReview($requestReview);
        $dataReview = $responseReview->getData(true);
        $install_app_review = 0;
        if ($dataReview['result']) {
            if ($dataReview['type'] == 'count') {
                $install_app_review = $dataReview['data'];
            }
        }

        $listIds = "(" . implode(",", $id_client) . ")";
        $baseQuery = DB::table('tbl_transaction')
            ->select('tbl_transaction.*')
            ->join(DB::raw("
                (SELECT customer_id, MIN(id) AS first_date
                 FROM tbl_transaction
                 WHERE tbl_transaction.customer_id IN $listIds
                 AND tbl_transaction.status = " . Config::get('constant')['status_finish'] . "
                 GROUP BY tbl_transaction.customer_id) t
            "), function ($join) {
                $join->on('tbl_transaction.customer_id', '=', 't.customer_id');
                $join->on('tbl_transaction.id', '=', 't.first_date');
            })->get();

        $total_point = $baseQuery->sum('total_customer');
        $install_app_order = $baseQuery->count();

        $data = [
            'total_point' => $total_point,
            'install_app' => $dataCustomer['count_install_app'] ?? 0,
            'referall' => $dataCustomer['client_introduce'] ?? 0,
            'install_app_review' => $install_app_review,
            'install_app_order' => $install_app_order,
            'result' => true
        ];
        return response()->json($data);
    }

    public function getDetailDataReferral()
    {
        $type_search = $this->request->input('type_search');
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $query = Clients::select('id', 'fullname');
        $query->addSelect(DB::raw('(SELECT COUNT(tbl_client_introduce.id)
                                            FROM tbl_client_introduce
                                            WHERE tbl_client_introduce.id_client_introduce = tbl_clients.id) AS client_introduce'));
        $query->addSelect(DB::raw('(SELECT COUNT(tbl_referral_level.id)
                                            FROM tbl_referral_level
                                            WHERE tbl_referral_level.parent_id = tbl_clients.id) AS count_install_app'));
        $query->addSelect(DB::raw('(SELECT GROUP_CONCAT(tbl_client_introduce.id_client)
                                            FROM tbl_client_introduce
                                            WHERE tbl_client_introduce.id_client_introduce = tbl_clients.id) AS id_client'));
        $query->where('id', $customer_id);
        $dataCustomer = $query->first();
        $id_client = $dataCustomer['id_client'] ?? 0;
        $id_client = explode(',', $id_client);
        $requestReview = clone $this->request;
        $requestReview->merge(['customer_id' => $id_client]);
        $requestReview->merge(['type' => 'list']);

        $responseReview = $this->AdminService->getDataClientReview($requestReview);
        $dataReview = $responseReview->getData(true);
        $dataReviewList = $dataReview['data'] ?? [];
        if ($dataReview['result']) {
            if ($dataReview['type'] == 'list') {
                if (empty($dataReviewList)) {
                    $dataReviewList[] = [
                        'id_client' => 0,
                        'created_at' => null,
                    ];
                }
            }
        }

        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $listIds = "(" . implode(",", $id_client) . ")";

        $referralInstall = DB::table('tbl_client_introduce')
            ->leftJoin('tbl_clients', 'tbl_clients.id', '=', 'tbl_client_introduce.id_client')
            ->select(
                'tbl_clients.id as id',
                DB::raw('COALESCE(tbl_clients.fullname, tbl_clients.phone) as fullname'),
                DB::raw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar'),
                'tbl_client_introduce.created_at as created_at',
                DB::raw('1 as type'),
                DB::raw('0 as point')
            )
            ->where('tbl_client_introduce.id_client_introduce', '=', $customer_id);

        $reviewQuery = DB::table(DB::raw('(' .
            collect($dataReviewList)->map(function ($value) {
                return "(SELECT '{$value['id_client']}' as id_client, '{$value['created_at']}' as created_at)";
            })->implode(' UNION ALL ') . ') AS tb_review'));

        $referralReview = DB::query()
            ->fromSub($reviewQuery, 'tb_review')
            ->join('tbl_clients', 'tbl_clients.id', '=', 'tb_review.id_client')
            ->select(
                'tbl_clients.id as id',
                DB::raw('COALESCE(tbl_clients.fullname, tbl_clients.phone) as fullname'),
                DB::raw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar'),
                'tb_review.created_at as created_at',
                DB::raw('2 as type'),
                DB::raw('0 as point')
            );

        $referralOrder = DB::table('tbl_transaction')
            ->select(
                'tbl_clients.id as id',
                DB::raw('COALESCE(tbl_clients.fullname, tbl_clients.phone) as fullname'),
                DB::raw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar'),
                'tbl_transaction.created_at as created_at',
                DB::raw('3 as type'),
                'tbl_transaction.total_customer as point'
            )
            ->join('tbl_clients', 'tbl_clients.id', '=', 'tbl_transaction.customer_id')
            ->join(DB::raw("
                (SELECT customer_id, MIN(id) AS first_date
                 FROM tbl_transaction
                 WHERE tbl_transaction.customer_id IN $listIds
                 AND tbl_transaction.status = " . Config::get('constant')['status_finish'] . "
                 GROUP BY tbl_transaction.customer_id) t
            "), function ($join) {
                $join->on('tbl_transaction.customer_id', '=', 't.customer_id');
                $join->on('tbl_transaction.id', '=', 't.first_date');
            });

        $tb_referral = $referralInstall->unionAll($referralReview)->unionAll($referralOrder);
        $queryReferral = DB::query()
            ->fromSub($tb_referral, 'tb_referral')
            ->select(
                'tb_referral.id',
                'tb_referral.avatar',
                'tb_referral.fullname',
                'tb_referral.created_at',
                'tb_referral.type',
                'tb_referral.point',
            );
        if (!empty($type_search)) {
            $queryReferral->where('tb_referral.type', '=', $type_search);
        }
        $paginate = $queryReferral->paginate($per_page, ['*'], '', $current_page);

        $paginate->getCollection()->transform(function ($item) {
            $arrType = getListTypeReferral($item->type);
            return [
                'id' => $item->id,
                'avatar' => $item->avatar,
                'fullname' => $item->fullname,
                'created_at' => $item->created_at,
                'point' => $item->point,
                'type' => $arrType,
            ];
        });


        return response()->json([
            'data' => [
                'data' => $paginate->items(),
                'links' => [
                    'first' => $paginate->url(1),
                    'last' => $paginate->url($paginate->lastPage()),
                    'prev' => $paginate->previousPageUrl(),
                    'next' => $paginate->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $paginate->currentPage(),
                    'from' => $paginate->firstItem(),
                    'last_page' => $paginate->lastPage(),
                    'links' => $paginate->linkCollection(),
                    'path' => $paginate->path(),
                    'per_page' => $paginate->perPage(),
                    'to' => $paginate->lastItem(),
                    'total' => $paginate->total(),
                ]
            ],
            'result' => true,
        ]);

    }

    public function getDetailDataOrderReferral()
    {
        $type_search = $this->request->input('type_search');
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $query = Clients::select('id', 'fullname');
        $query->addSelect(DB::raw('(SELECT GROUP_CONCAT(tbl_client_introduce.id_client)
                                            FROM tbl_client_introduce
                                            WHERE tbl_client_introduce.id_client_introduce = tbl_clients.id) AS id_client'));
        $query->where('id', $customer_id);
        $dataCustomer = $query->first();
        $id_client = $dataCustomer['id_client'] ?? 0;
        $id_client = explode(',', $id_client);

        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $listIds = "(" . implode(",", $id_client) . ")";

        $referralOrder = Transaction::with('transaction_item')
            ->select(
                'tbl_clients.id as customer_id',
                DB::raw('COALESCE(tbl_clients.fullname, tbl_clients.phone) as fullname'),
                DB::raw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar'),
                'tbl_transaction.created_at as created_at',
                DB::raw('3 as type'),
                'tbl_transaction.total_customer as point',
                'tbl_transaction.id',
            )
            ->join('tbl_clients', 'tbl_clients.id', '=', 'tbl_transaction.customer_id')
            ->join(DB::raw("
                (SELECT customer_id, MIN(id) AS first_date
                 FROM tbl_transaction
                 WHERE tbl_transaction.customer_id IN $listIds
                 AND tbl_transaction.status = " . Config::get('constant')['status_finish'] . "
                 GROUP BY tbl_transaction.customer_id) t
            "), function ($join) {
                $join->on('tbl_transaction.customer_id', '=', 't.customer_id');
                $join->on('tbl_transaction.id', '=', 't.first_date');
            });
        $paginate = $referralOrder->paginate($per_page, ['*'], '', $current_page);
        $allProductIds = $paginate->getCollection()
            ->flatMap(function ($item) {
                return $item->transaction_item->pluck('product_id');
            })
            ->unique()
            ->values()
            ->toArray();
        $arrProductId = $allProductIds;
        $arrProductId = !empty($arrProductId) ? $arrProductId : [0];
        $this->requestProduct = clone $this->request;
        $this->requestProduct->merge(['arrProduct' => $arrProductId]);
        $responseProduct = $this->AdminService->getListDataProduct($this->requestProduct);
        $dataProduct = $responseProduct->getData(true);
        $dtProduct = collect($dataProduct['data']['data'] ?? []);

        $paginate->getCollection()->transform(function ($item) use ($dtProduct) {
            $arrType = getListTypeReferral($item->type);
            $transactionItem = $item->transaction_item->map(function ($it) use ($dtProduct) {
                $product = $dtProduct->where('id', $it->product_id)->first();
                return [
                    'id' => $it->id,
                    'price' => $it->price,
                    'quantity' => $it->quantity,
                    'amount' => $it->total,
                    'product' => $product,
                ];
            });
            return [
                'id' => $item->id,
                'avatar' => $item->avatar,
                'fullname' => $item->fullname,
                'created_at' => $item->created_at,
                'point' => $item->point,
                'type' => $arrType,
                'items' => $transactionItem
            ];
        });


        return response()->json([
            'data' => [
                'data' => $paginate->items(),
                'links' => [
                    'first' => $paginate->url(1),
                    'last' => $paginate->url($paginate->lastPage()),
                    'prev' => $paginate->previousPageUrl(),
                    'next' => $paginate->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $paginate->currentPage(),
                    'from' => $paginate->firstItem(),
                    'last_page' => $paginate->lastPage(),
                    'links' => $paginate->linkCollection(),
                    'path' => $paginate->path(),
                    'per_page' => $paginate->perPage(),
                    'to' => $paginate->lastItem(),
                    'total' => $paginate->total(),
                ]
            ],
            'result' => true,
        ]);

    }

    // danh sách địa chỉ khách hàng
    public function get_list_address()
    {
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $id_client = !empty($this->request->client->id) ? $this->request->client->id : 0;
        $list_address = ClientAddress::where('customer_id', $id_client);
        $list_address = $list_address->paginate($per_page, ['*'], 'page', $current_page);
        $dataResult['result'] = true;
        $dataResult['title'] = lang('notification');
        $dataResult['message'] = lang('dt_list_success');
        $dataResult['data'] = $list_address;
        return response()->json($dataResult);
    }

    public function get_address_default()
    {
        $id_client = !empty($this->request->client->id) ? $this->request->client->id : 0;
        $list_address = ClientAddress::where('customer_id', $id_client)
            ->orderBy('default_address', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        return response()->json($list_address);
    }

    // thêm hoặc cập nhật địa chỉ khách hàng
    public function update_client_address()
    {
        $dataResult = [
            'result' => false,
        ];
        $id_client = $this->request->client->id;
        $id = $this->request->id ?? null;
        $data = $this->request->input();
        if (!empty($id)) {
            $data['customer_id'] = $id_client;
            $success = change_client_address($data, $id);
            if (!empty($success)) {
                $dataResult['result'] = true;
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_update_address_success');
                return response()->json($dataResult);
            }
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_update_address_fail');
            return response()->json($dataResult);
        } else {
            $data['customer_id'] = $id_client;
            $success = change_client_address($data);
            if (!empty($success)) {
                $dataResult['result'] = true;
                $dataResult['title'] = lang('notification');
                $dataResult['message'] = lang('c_add_address_success');
                return response()->json($dataResult);
            }

            $dataResult['result'] = false;
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_add_address_fail');
            return response()->json($dataResult);

        }
    }

    public function delete_address()
    {
        $id_client = !empty($this->request->client->id) ? $this->request->client->id : 0;
        $id = $this->request->input('id');
        $delete_address = ClientAddress::where('customer_id', $id_client)->where('id', $id)->delete();
        if (!empty($delete_address)) {
            $dataResult['result'] = true;
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_delete_true');
        } else {
            $dataResult['result'] = false;
            $dataResult['title'] = lang('notification');
            $dataResult['message'] = lang('c_delete_fail');
        }
        return response()->json($dataResult);
    }

    public function getListDataOrderReferral()
    {
        $filter = $this->request->input('filter');
        $type_client_search = $filter['type_client_search'] ?? 0;
        $active_search = $filter['active_search'] ?? -1;
        $search = $this->request->input('search');
        $orderBy = $this->request->input('order_by', 'id');
        $orderDir = $this->request->input('order_dir', 'asc');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $customer_id = $this->request->input('customer_id') ?? 0;
        $query = Clients::select(
            'tbl_clients.*',
            DB::raw('CONCAT("' . $this->baseUrl . '/", avatar) as avatar'),
            'tbl_transaction.date as date',
            'tbl_transaction.reference_no'
        )
            ->join('tbl_transaction', 'tbl_transaction.customer_id', '=', 'tbl_clients.id')
            ->join(DB::raw("
                (
                    SELECT
                        customer_id,
                        MIN(id) AS first_date
                    FROM tbl_transaction
                    WHERE customer_id IN (
                        SELECT id_client
                        FROM tbl_client_introduce
                        WHERE id_client_introduce = $customer_id
                    )
                    AND status = " . Config::get('constant')['status_finish'] . "
                    GROUP BY customer_id
                ) AS t
            "), function ($join) {
                $join->on('tbl_transaction.customer_id', '=', 't.customer_id');
                $join->on('tbl_transaction.id', '=', 't.first_date');
            });
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('tbl_transaction.reference_no', 'like', "%$search%");
            });
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        $total = Clients::count();
        return response()->json([
            'total' => $total,
            '_locale' => $this->request->_locale,
            'filtered' => $filtered,
            'data' => $data
        ]);
    }

    public function addAffiliate()
    {
        $_locale = $this->_locale;
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $referral_code = $this->request->input('referral_code') ?? null;
        $machines_id = $this->request->input('machines_id') ?? null;
        $code_product = $this->request->input('code_product') ?? null;
        $data['title'] = lang('notification');
        if (!empty($referral_code) && !empty($code_product)) {
            $dtClient = Clients::where('code_introduce', $referral_code)->first();
            if (!empty($dtClient)) {
                $this->requestProduct = clone $this->request;
                $this->requestProduct->merge(['arrIdProduct' => is_array($code_product) ? $code_product : [$code_product]]);
                $responseProduct = $this->AdminService->getListDataProduct($this->requestProduct);
                $dataProduct = $responseProduct->getData(true);
                $dtProduct = collect($dataProduct['data']['data'][0] ?? []);
                if (!empty($dtProduct)) {
                    DB::beginTransaction();
                    try {
                        $affiliate = new SyntheticAffiliate();
                        $affiliate->customer_id = $dtClient->id;
                        $affiliate->product_id = $dtProduct['id'];
                        $affiliate->review_id = 0;
                        $affiliate->variant_id = 0;
                        $affiliate->machines_id = $machines_id;
                        $affiliate->save();
                        DB::commit();
                        $data['result'] = true;
                        $data['message'] = lang('dt_success');
                        return response()->json($data);
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        $data['result'] = false;
                        $data['message'] = $exception->getMessage();
                        return response()->json($data);
                    }
                } else {
                    $data['result'] = false;
                    $data['message'] = lang('dt_error');
                    return response()->json($data);
                }
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_not_client');
                return response()->json($data);
            }
        } else {
            $data['result'] = false;
            $data['message'] = lang('dt_not_exists_referral');
            return response()->json($data);
        }
    }

    public function getDataAffiliate()
    {
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $date_start = $this->request->input('date_start') ?? date('d/m/Y');
        $date_end = $this->request->input('date_end') ?? date('d/m/Y');

        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $clickSub = DB::table('tbl_synthetic_affiliate')
            ->select('customer_id', DB::raw('COUNT(*) AS count_click'))
            ->when($date_start && $date_end, function ($q) use ($date_start, $date_end) {
                $q->whereBetween(DB::raw('DATE(created_at)'), [to_sql_date($date_start), to_sql_date($date_end)]);
            })
            ->groupBy('customer_id');

        $transactionSub = DB::table('tbl_transaction_item')
            ->select(
                'customer_id_affiliate',
                DB::raw('COUNT(DISTINCT transaction_id) AS count_order'),
                DB::raw('COUNT(customer_id_affiliate) AS count_customer_affiliate'),
                DB::raw('SUM(tbl_transaction_item.quantity) AS sum_quantity'),
                DB::raw('SUM(tbl_transaction_item.total) AS sum_total'),
                DB::raw('SUM(tbl_transaction_item.total_affiliate) AS sum_total_affiliate')
            )
            ->join('tbl_transaction', 'tbl_transaction.id', '=', 'tbl_transaction_item.transaction_id')
            ->when($date_start && $date_end, function ($q) use ($date_start, $date_end) {
                $q->whereBetween(DB::raw('DATE(date)'), [to_sql_date($date_start), to_sql_date($date_end)]);
            })
            ->groupBy('customer_id_affiliate');

        $dataCustomer = Clients::query()
            ->leftJoinSub($clickSub, 'sa', 'sa.customer_id', '=', 'tbl_clients.id')
            ->leftJoinSub($transactionSub, 'ti', 'ti.customer_id_affiliate', '=', 'tbl_clients.id')
            ->select(
                'tbl_clients.id',
                'fullname',
                DB::raw('COALESCE(sa.count_click,0) AS count_click'),
                DB::raw('COALESCE(ti.count_order,0) AS count_order'),
                DB::raw('COALESCE(ti.sum_quantity,0) AS sum_quantity'),
                DB::raw('COALESCE(ti.sum_total,0) AS sum_total'),
                DB::raw('COALESCE(ti.sum_total_affiliate,0) AS sum_total_affiliate'),
                DB::raw('COALESCE(ti.count_customer_affiliate,0) AS count_customer_affiliate')
            )
            ->where('tbl_clients.id', $customer_id)
            ->first();

        $query = TransactionItem::where('customer_id_affiliate', '=', $customer_id);
        $query->join('tbl_transaction', 'tbl_transaction.id', '=', 'tbl_transaction_item.transaction_id');
        $query->when($date_start && $date_end, function ($q) use ($date_start, $date_end) {
            $q->whereBetween(DB::raw('DATE(date)'), [to_sql_date($date_start), to_sql_date($date_end)]);
        });
        $query->select('product_id');
        $query->selectRaw('SUM(tbl_transaction_item.quantity) as total_quantity');
        $query->selectRaw('SUM(tbl_transaction_item.total) as total_amount');
        $query->selectRaw('SUM(tbl_transaction_item.total_affiliate) as total_affiliate');
        $query->groupBy('product_id');
        $paginate = $query->paginate($per_page, ['*'], '', $current_page);

        $allProductIds = $paginate->pluck('product_id')->flatten()->unique()->toArray();
        $arrProductId = !empty($allProductIds) ? $allProductIds : [0];
        $this->requestProduct = clone $this->request;
        $this->requestProduct->merge(['arrProduct' => $arrProductId]);
        $responseProduct = $this->AdminService->getListDataProduct($this->requestProduct);
        $dataProduct = $responseProduct->getData(true);
        $dtProduct = collect($dataProduct['data']['data'] ?? []);

        $paginate->getCollection()->transform(function ($item) use ($dtProduct) {
            $product = $dtProduct->where('id', '=', $item->product_id)->first();
            $item->id = $item->product_id;
            $item->product = $product;
            return $item;
        });

        $click = DB::table('tbl_synthetic_affiliate')
            ->selectRaw('DATE(created_at) as date, COUNT(*) AS count_click')
            ->when($date_start && $date_end, function ($q) use ($date_start, $date_end) {
                $q->where('created_at', '>=', to_sql_date($date_start) . ' 00:00:00')
                    ->where('created_at', '<=', to_sql_date($date_end) . ' 23:59:59');
            })
            ->where('customer_id', $customer_id)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->pluck('count_click', 'date');
        $arrDate = date_range($date_start, $date_end);
        $result = [];
        foreach ($arrDate as $date) {
            $d = to_sql_date($date);

            $result[] = [
                'date' => $d,
                'count_click' => $click[$d] ?? 0
            ];
        }

        $data = [
            'count_click' => $dataCustomer['count_click'],
            'count_order' => $dataCustomer['count_order'] ?? 0,
            'sum_quantity' => $dataCustomer['sum_quantity'] ?? 0,
            'sum_total' => $dataCustomer['sum_total'] ?? 0,
            'sum_total_affiliate' => $dataCustomer['sum_total_affiliate'] ?? 0,
            'count_customer_affiliate' => $dataCustomer['count_customer_affiliate'] ?? 0,
            'data_click' => $result,
            'data' => [
                'data' => $paginate->items(),
                'links' => [
                    'first' => $paginate->url(1),
                    'last' => $paginate->url($paginate->lastPage()),
                    'prev' => $paginate->previousPageUrl(),
                    'next' => $paginate->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $paginate->currentPage(),
                    'from' => $paginate->firstItem(),
                    'last_page' => $paginate->lastPage(),
                    'links' => $paginate->linkCollection(),
                    'path' => $paginate->path(),
                    'per_page' => $paginate->perPage(),
                    'to' => $paginate->lastItem(),
                    'total' => $paginate->total(),
                ]
            ],
            'result' => true
        ];
        return response()->json($data);
    }

    public function getDataProductAffiliateNext()
    {
        $current_page = 2;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $date_start = $this->request->input('date_start') ?? date('d/m/Y');
        $date_end = $this->request->input('date_end') ?? date('d/m/Y');
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $query = TransactionItem::where('customer_id_affiliate', '=', $customer_id);
        $query->join('tbl_transaction', 'tbl_transaction.id', '=', 'tbl_transaction_item.transaction_id');
        $query->when($date_start && $date_end, function ($q) use ($date_start, $date_end) {
            $q->whereBetween(DB::raw('DATE(date)'), [to_sql_date($date_start), to_sql_date($date_end)]);
        });
        $query->select('product_id');
        $query->selectRaw('SUM(tbl_transaction_item.quantity) as total_quantity');
        $query->selectRaw('SUM(tbl_transaction_item.total) as total_amount');
        $query->selectRaw('SUM(tbl_transaction_item.total_affiliate) as total_affiliate');
        $query->groupBy('product_id');
        $paginate = $query->paginate($per_page, ['*'], '', $current_page);

        $allProductIds = $paginate->pluck('product_id')->flatten()->unique()->toArray();
        $arrProductId = !empty($allProductIds) ? $allProductIds : [0];
        $this->requestProduct = clone $this->request;
        $this->requestProduct->merge(['arrProduct' => $arrProductId]);
        $responseProduct = $this->AdminService->getListDataProduct($this->requestProduct);
        $dataProduct = $responseProduct->getData(true);
        $dtProduct = collect($dataProduct['data']['data'] ?? []);

        $paginate->getCollection()->transform(function ($item) use ($dtProduct) {
            $product = $dtProduct->where('id', '=', $item->product_id)->first();
            $item->id = $item->product_id;
            $item->product = $product;
            return $item;
        });
        $data = [
            'data' => [
                'data' => $paginate->items(),
                'links' => [
                    'first' => $paginate->url(1),
                    'last' => $paginate->url($paginate->lastPage()),
                    'prev' => $paginate->previousPageUrl(),
                    'next' => $paginate->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $paginate->currentPage(),
                    'from' => $paginate->firstItem(),
                    'last_page' => $paginate->lastPage(),
                    'links' => $paginate->linkCollection(),
                    'path' => $paginate->path(),
                    'per_page' => $paginate->perPage(),
                    'to' => $paginate->lastItem(),
                    'total' => $paginate->total(),
                ]
            ],
            'result' => true
        ];
        return response()->json($data);
    }

    public function getDataAffiliateChart()
    {
        $date_start = $this->request->input('date_start') ?? date('d/m/Y');
        $date_end = $this->request->input('date_end') ?? date('d/m/Y');
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $type = $this->request->input('type') ?? 'click';
        if ($type == 'click') {
            $click = DB::table('tbl_synthetic_affiliate')
                ->selectRaw('DATE(created_at) as date, COUNT(*) AS count_click')
                ->when($date_start && $date_end, function ($q) use ($date_start, $date_end) {
                    $q->where('created_at', '>=', to_sql_date($date_start) . ' 00:00:00')
                        ->where('created_at', '<=', to_sql_date($date_end) . ' 23:59:59');
                })
                ->where('customer_id', $customer_id)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->pluck('count_click', 'date');
        } else {
            $query = DB::table('tbl_transaction_item')
                ->select(
                    DB::raw('DATE(date) as date'),
                    DB::raw('COUNT(DISTINCT transaction_id) AS count_order'),
                    DB::raw('COUNT(customer_id_affiliate) AS count_customer_affiliate'),
                    DB::raw('SUM(tbl_transaction_item.quantity) AS sum_quantity'),
                    DB::raw('SUM(tbl_transaction_item.total) AS sum_total'),
                    DB::raw('SUM(tbl_transaction_item.total_affiliate) AS sum_total_affiliate')
                )
                ->join('tbl_transaction', 'tbl_transaction.id', '=', 'tbl_transaction_item.transaction_id')
                ->when($date_start && $date_end, function ($q) use ($date_start, $date_end) {
                    $q->whereBetween(DB::raw('DATE(date)'), [to_sql_date($date_start), to_sql_date($date_end)]);
                })
                ->where('tbl_transaction_item.customer_id_affiliate', $customer_id)
                ->groupBy(DB::raw('DATE(date)'));
            if ($type == 'order') {
                $click = $query->pluck('count_order', 'date');
            } elseif ($type == 'quantity') {
                $click = $query->pluck('sum_quantity', 'date');
            } elseif ($type == 'sale') {
                $click = $query->pluck('sum_total', 'date');
            } elseif ($type == 'rose') {
                $click = $query->pluck('sum_total_affiliate', 'date');
            } elseif ($type == 'customer') {
                $click = $query->pluck('count_customer_affiliate', 'date');
            }
        }
        $arrDate = date_range($date_start, $date_end);
        $result = [];
        foreach ($arrDate as $date) {
            $d = to_sql_date($date);

            $result[] = [
                'date' => $d,
                'total' => $click[$d] ?? 0
            ];
        }
        $data['data'] = $result;
        $data['result'] = true;
        return response()->json($data);
    }

    public function getListFillterDay()
    {
        $today = date('Y-m-d');

        $dateRanges = [
//            [
//                'start_date' => date('Y-m-d', strtotime('-1 day')),
//                'end_date'   => date('Y-m-d', strtotime('-1 day')),
//                'title' => lang('dt_hom_qua')
//            ],
            [
                'start_date' => date('Y-m-d', strtotime('-7 days')),
                'end_date' => $today,
                'title' => lang('dt_7_ngay_truoc')
            ],
            [
                'start_date' => date('Y-m-d', strtotime('-30 days')),
                'end_date' => $today,
                'title' => lang('dt_30_ngay_truoc')
            ],
            [
                'start_date' => date('Y-m-d', strtotime('-2 months')),
                'end_date' => $today,
                'title' => lang('dt_2_thang_truoc')
            ],
        ];
        $data['data'] = $dateRanges;
        $data['result'] = true;
        return response()->json($data);
    }

    public function getDataLeader()
    {
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $year = $this->request->input('year') ?? date('Y');

        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;

        $total_revenue = Transaction::where('leader_id', $customer_id)
            ->whereYear('date', $year)
            ->where('status', Config::get('constant')['status_finish'])
            ->sum('grand_total');

        $query = Transaction::where('leader_id', '=', $customer_id);
        $query->when($year, function ($q) use ($year) {
            $q->whereYear('date', $year);
        });
        $query->where('status', Config::get('constant')['status_finish']);
        $query->select('id','date','reference_no','grand_total','total_accumulate');
        $paginate = $query->paginate($per_page, ['*'], '', $current_page);

        $data = [
            'revenue' => $total_revenue,
            'data' => [
                'data' => $paginate->items(),
                'links' => [
                    'first' => $paginate->url(1),
                    'last' => $paginate->url($paginate->lastPage()),
                    'prev' => $paginate->previousPageUrl(),
                    'next' => $paginate->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $paginate->currentPage(),
                    'from' => $paginate->firstItem(),
                    'last_page' => $paginate->lastPage(),
                    'links' => $paginate->linkCollection(),
                    'path' => $paginate->path(),
                    'per_page' => $paginate->perPage(),
                    'to' => $paginate->lastItem(),
                    'total' => $paginate->total(),
                ]
            ],
            'result' => true
        ];
        return response()->json($data);
    }

    public function getDataTransactionLeaderNext()
    {
        $current_page = 2;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $year = $this->request->input('year') ?? date('Y');

        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $query = Transaction::where('leader_id', '=', $customer_id);
        $query->when($year, function ($q) use ($year) {
            $q->whereYear('date', $year);
        });
        $query->where('status', Config::get('constant')['status_finish']);
        $query->select('id','date','reference_no','grand_total','total_accumulate');
        $paginate = $query->paginate($per_page, ['*'], '', $current_page);
        $data = [
            'data' => [
                'data' => $paginate->items(),
                'links' => [
                    'first' => $paginate->url(1),
                    'last' => $paginate->url($paginate->lastPage()),
                    'prev' => $paginate->previousPageUrl(),
                    'next' => $paginate->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $paginate->currentPage(),
                    'from' => $paginate->firstItem(),
                    'last_page' => $paginate->lastPage(),
                    'links' => $paginate->linkCollection(),
                    'path' => $paginate->path(),
                    'per_page' => $paginate->perPage(),
                    'to' => $paginate->lastItem(),
                    'total' => $paginate->total(),
                ]
            ],
            'result' => true
        ];
        return response()->json($data);
    }

    public function getListBonusPayment()
    {
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $year = $this->request->input('year') ?? date('Y');
        $month = $this->request->input('month') ?? date('m');

        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $storageUrl = $this->baseUrl;
        $query = BonusPayment::with(['customer' => function ($query) use($storageUrl) {
            $query->select('id', 'fullname', 'code','phone','email','type_client', DB::raw("IF(avatar IS NOT NULL, CONCAT('$storageUrl/', avatar), NULL) as avatar"));
        }])->where('customer_id', '=', $customer_id);
        $query->when($year, function ($q) use ($year) {
            $q->whereYear('date', $year);
        });
        $query->when($month, function ($q) use ($month) {
            $q->whereMonth('date', $month);
        });
        $query->select('id','date','reference_no','total','note','customer_id','status','date_status');
        $paginate = $query->paginate($per_page, ['*'], '', $current_page);

        $paginate->getCollection()->transform(function ($item) {
            $item->status = [
                'id' => $item->status,
                'status' => $item->status == 1 ? 'Đã chi' : 'Chưa chi',
                'date_status' => $item->date_status,
            ];
            unset($item->date_status);
            return $item;
        });
        $data = [
            'data' => [
                'data' => $paginate->items(),
                'links' => [
                    'first' => $paginate->url(1),
                    'last' => $paginate->url($paginate->lastPage()),
                    'prev' => $paginate->previousPageUrl(),
                    'next' => $paginate->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $paginate->currentPage(),
                    'from' => $paginate->firstItem(),
                    'last_page' => $paginate->lastPage(),
                    'links' => $paginate->linkCollection(),
                    'path' => $paginate->path(),
                    'per_page' => $paginate->perPage(),
                    'to' => $paginate->lastItem(),
                    'total' => $paginate->total(),
                ]
            ],
            'result' => true
        ];
        return response()->json($data);
    }

    public function getListTopLeader()
    {
        $year = date('Y');
        $monthInput = $this->request->input('month');
        $month = is_numeric($monthInput) ? (int)$monthInput : (int)date('m');
        if ($month < 1 || $month > 12) {
            $month = (int)date('m');
        }
        $start = Carbon::create((int)$year, (int)$month, 1)->startOfMonth();
        $end = Carbon::create((int)$year, (int)$month, 1)->endOfMonth();
        $storageUrl = $this->baseUrl;
        
        $statusFinish = Config::get('constant')['status_finish'];
        $sub = DB::table('tbl_transaction as t')
            ->selectRaw('t.leader_id, SUM(t.grand_total) as sum_total')
            ->whereBetween('t.date', [$start, $end])
            ->where('t.status', $statusFinish)
            ->where('t.leader_id', '>', 0)
            ->groupBy('t.leader_id');

        $rows = DB::query()
            ->fromSub($sub, 'x')
            ->join('tbl_clients as c', 'c.id', '=', 'x.leader_id')
            ->orderByDesc('x.sum_total')
            ->limit(10)
            ->get([
                'x.leader_id',
                'x.sum_total',
                'c.id as leader__id',
                'c.fullname as leader__fullname',
                'c.code as leader__code',
                'c.phone as leader__phone',
                'c.email as leader__email',
                'c.type_client as leader__type_client',
                DB::raw("IF(c.avatar IS NOT NULL, CONCAT('$storageUrl/', c.avatar), NULL) as leader__avatar"),
            ]);

        $result = $rows->map(function ($r) {
            return (object)[
                'leader_id' => $r->leader_id,
                'sum_total' => $r->sum_total,
                'leader' => (object)[
                    'id' => $r->leader__id,
                    'fullname' => $r->leader__fullname,
                    'code' => $r->leader__code,
                    'phone' => $r->leader__phone,
                    'email' => $r->leader__email,
                    'type_client' => $r->leader__type_client,
                    'avatar' => $r->leader__avatar,
                ],
            ];
        });
        return response()->json([
            'result' => true,
            'data' => $result
        ]);
    }

    public function getClientsOrderLeader()
    {
        $filter = $this->request->input('filter', []);
        $type_client_search = $filter['type_client_search'] ?? 0;
        $active_search = $filter['active_search'] ?? -1;
        
        $search = $this->request->input('search');
        $orderBy = $this->request->input('order_by', 'id');
        $orderDir = $this->request->input('order_dir', 'asc');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $customer_id = $this->request->input('customer_id') ?? 0;
        $year_search = $this->request->input('year_search') ?? date('Y');
     
        $allowedSort = ['id', 'date', 'reference_no', 'grand_total'];
        if (!in_array($orderBy, $allowedSort)) {
            $orderBy = 'id';
        }
        $orderDir = strtolower($orderDir) === 'desc' ? 'desc' : 'asc';
        
        $query = Transaction::with(['customer' => function ($q) {
            $q->select(
                'id',
                'fullname',
                'phone',
                'email',
                DB::raw("CONCAT('" . rtrim($this->baseUrl, '/') . "/', avatar) as avatar")
            );
        }])->select(
            'tbl_transaction.id as id',
            'tbl_transaction.customer_id',
            'tbl_transaction.date as date',
            'tbl_transaction.reference_no',
            'tbl_transaction.grand_total',
            'tbl_transaction.total_accumulate'
        );
        
        $query->where('tbl_transaction.leader_id', $customer_id);
        $query->where('tbl_transaction.status', Config::get('constant')['status_finish']);
        
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('tbl_transaction.reference_no', 'like', "%$search%");
            });
        }
        $query->whereYear('tbl_transaction.date', $year_search);
        
        // count đúng
        $filtered = (clone $query)->count();
        
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        
        
        // total đúng
        $total = Transaction::where('tbl_transaction.leader_id', $customer_id)
            ->where('tbl_transaction.status', Config::get('constant')['status_finish'])
            ->whereYear('tbl_transaction.date', $year_search)
            ->count();
        return response()->json([
            'total' => $total,
            '_locale' => $this->request->_locale,
            'filtered' => $filtered,
            'data' => $data
        ]);
    }

    public function detailInformationVat()
    {
        $id = $this->request->input('id_client_information_vat') ?? 0;

        $vatRules = [
            'type_vat' => 'required',
            'address' => 'required',
            'payment_method' => 'required',
            'email' => 'required',
        ];
        $vatMessages = [
            'type_vat.required' => 'Vui lòng chọn loại',
            'address.required' => 'Vui lòng nhập địa chỉ',
            'payment_method.required' => 'Vui lòng nhập hình thức thanh toán',
            'email.required' => 'Vui lòng nhập email',
        ];
        $validatorVat = Validator::make($this->request->all(), $vatRules, $vatMessages);
        if ($validatorVat->fails()) {
            $data['result'] = false;
            $data['message'] = $validatorVat->errors()->all()[0];
            return response()->json($data);
        }
        if (empty($id)) {
            $dtData = new ClientInformationVat();
        } else {
            $dtData = ClientInformationVat::find($id);
        }
        DB::beginTransaction();
        $type_vat = $this->request->type_vat;
        $name = $this->request->name;
        $company = $this->request->company;
        $vat = $this->request->vat;
        $email = $this->request->email;
        if ($type_vat == 1) {
            if(empty($company) || empty($vat)) {
                $data['result'] = false;
                $data['message'] = 'Vui lòng nhập tên đơn vị và mã số thuế';
                return response()->json($data);
            }
        } else {
            if(empty($name)) {
                $data['result'] = false;
                $data['message'] = 'Vui lòng nhập tên người mua';
                return response()->json($data);
            }
        }
        try {
            $dtData->type = $this->request->type_vat;
            $dtData->address = $this->request->address;
            $dtData->payment_mode = $this->request->payment_method;
            $dtData->customer_id = $this->request->customer_id;
            $dtData->name = $name ?? null;
            $dtData->company = $company ?? null;
            $dtData->vat = $vat ?? null;
            $dtData->email = $email ?? null;
            $dtData->account_name = $this->request->account_name ?? null;
            $dtData->account_no = $this->request->account_no ?? null;
            $dtData->save();
            if ($dtData) {

                DB::commit();
                $data['result'] = true;
                if (empty($id)) {
                    $data['message'] = 'Thêm thành công';
                } else {
                    $data['message'] = 'Cập nhật thành công';
                }
            } else {
                $data['result'] = false;
                if (empty($id)) {
                    $data['message'] = 'Thêm thất bại';
                } else {
                    $data['message'] = 'Cập nhật thất bại';
                }
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

}
