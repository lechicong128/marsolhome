<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ChallengeMeResources;
use App\Models\Clients;
use App\Models\Transaction;
use App\Models\TransactionPayment;

// use App\Models\ChallengeMeItem;
use App\Models\RankCommunity;
use App\Services\AdminService;
use App\Services\NotiService;
use App\Services\ServiceService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Helpers\SocketHelpers;
use Carbon\Carbon;

class TransactionPaymentController extends AuthController
{
    use UploadFile;

    protected $AdminService;
    protected $adminNoti;
    protected $_locale;

    public function __construct(Request $request, AdminService $adminService, NotiService $notiService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->AdminService = $adminService;
        $this->adminNoti = $notiService;
        $this->_locale = $request->_locale;
    }

    public function getList()
    {
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');
        $customer_search = $this->request->input('customer_search') ?? 0;
        $date_search = $this->request->input('date_search') ?? null;
        $status_search = $this->request->input('status_search');
        if (!is_numeric($status_search)) {
            $status_search = -1;
        }
        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0], true);
            $end_date = to_sql_date($date_search[1], false) . ' 23:59:59';
        } else {
            $start_date = null;
            $end_date = null;
        }
        $query = TransactionPayment::with([
            'customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            },
            'transaction' => function ($q) {
                $q->select('id', 'reference_no');
            }
        ])->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%$search%");
                $q->orWhere('date', 'like', "%$search%");
                $q->orWhereHas('customer', function ($instance) use ($search) {
                    $instance->where('fullname', 'like', "%$search%");
                    $instance->orWhere('phone', 'like', "%$search%");
                });
            });
        }
        if ($status_search != -1) {
            if ($status_search == 1) {
                $query->whereIn('status', [
                    Config::get('constant')['status_success_payment']
                ]);
            } else {
                if ($status_search == 2) {
                    $query->where('status', $status_search);
                } else {
                    $query->where(function ($q) use ($status_search) {
                        $q->where('status', 0);
                    });
                }
            }
        }
        if (!empty($customer_search)) {
            $query->where('tbl_transaction_payment.customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $query->whereBetween('tbl_transaction_payment.date', [$start_date, $end_date]);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtCustomer = $value->customer ?? null;
                if (!empty($dtCustomer)) {
                    $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL') . '/' . $dtCustomer->avatar : null;
                    $data[$key]['customer']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]->customer = null;
                }
            }
        }
        $total = TransactionPayment::count();
        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function countAll()
    {
        $customer_search = $this->request->input('customer_search');
        $date_search = $this->request->input('date_search');
        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0], true);
            $end_date = to_sql_date($date_search[1], false) . ' 23:59:59';
        }
        $now = Carbon::now();
        /**
         * Base filter (chung cho tất cả)
         */
        $baseQuery = TransactionPayment::where('id', '!=', 0);
        if (!empty($customer_search)) {
            $baseQuery->where('customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $baseQuery->whereBetween('date', [$start_date, $end_date]);
        }
        /**
         * Tổng số (tất cả trạng thái)
         */
        $all = (clone $baseQuery)->count();

        /**
         * Đếm 3 trạng thái theo đúng nghiệp vụ
         */
        $counts = [
            // 0 - Đang diễn ra
            0 => (clone $baseQuery)->where(function ($q){
                $q->where('status', 0);
            })->count(),
            // 1 - Hoàn thành
            1 => (clone $baseQuery)->where(
                'status',
                [Config::get('constant')['status_success_payment']]
            )->count(),
            // 2 - Hết hạn
            2 => (clone $baseQuery)->where(function ($q) {
                $q->where('status', 2);
            })->count(),
        ];
        /**
         * Format trả về cho frontend
         */
        $arr = [
            ['status' => 0, 'count' => $counts[0]],
            ['status' => 1, 'count' => $counts[1]],
            ['status' => 2, 'count' => $counts[2]],
        ];
        return response()->json([
            'all' => $all,
            'arr' => $arr,
            'result' => true,
            'message' => lang('success'),
        ]);
    }

    public function delete()
    {
        $id = $this->request->input('id') ?? 0;
        $is_admin = $this->request->input('is_admin') ?? 0;
        $dtData = TransactionPayment::find($id);
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = lang('404_not_found');
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if($is_admin == 0) {
                if($dtData->status != 0) {
                    $data['result'] = false;
                    $data['message'] = lang('Phiếu thanh toán đã được thanh toán không thể xóa. Chỉ Admin mới được quyền xóa!');
                    return response()->json($data);
                }
            }
            $dtData->delete();

            DB::commit();
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

    public function changeStatus(){
        $id = $this->request->input('payment_id') ?? 0;
        $app = $this->request->input('app') ?? 0;
        $data['title'] = lang('notification');
        if ($app == 1){
            $partner_id = $this->request->client->id ?? 0;
            if (empty($partner_id)){
                $data['result'] = false;
                $data['data'] = [];
                $data['message'] = 'Vui lòng đăng nhập để sử dụng tính năng!.';
                return response()->json($data);
            }
            $type_staff_status = 2;
            $staff_status = $partner_id;
            $type = 'customer';
        } else {
            $partner_id = 0;
            $type_staff_status = 1;
            $staff_status = $this->request->input('staff_status') ?? 0;
            $type = 'staff';
        }
        $payment = TransactionPayment::find($id);
        $transaction = Transaction::find($payment->id_transaction);
        $customer_id = $payment->customer_id;
        if (in_array($transaction->status, [
            Config::get('constant')['status_cancel'],
        ])) {
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = 'Đơn hàng đã hủy không thể thực hiện.';
            return response()->json($data);
        }

        DB::beginTransaction();
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
            ->where('tbl_clients.id', $customer_id)
            ->get()->toArray();
        if (!empty($dtCustomer)) {
            $arr_object_id = array_merge($arr_object_id, $dtCustomer);
        }
        $arr_object_id = array_values($arr_object_id);
        try
        {
            if ($payment->status == 0){
                $payment->status = 1;
                $payment->type_staff_status = $type_staff_status;
                $payment->staff_status = $staff_status;
                $payment->date_status = date('Y-m-d H:i:s');
                $payment->amount_payment = $payment->amount;
                $payment->save();

                //cập nhật trạng thái thanh toán cho đơn hàng
                $transaction->status = Config::get('constant')['status_payment'];
                $transaction->type_staff_status = $type_staff_status;
                $transaction->staff_status = $staff_status;
                $transaction->date_status = date('Y-m-d H:i:s');
                $transaction->warning_payment = 0;
                $transaction->save();

                //gửi thông báo
                $payment->data_customer = [
                    'id' => $payment->customer->id,
                    'fullname' => $payment->customer->fullname,
                    'phone' => $payment->customer->phone,
                    'email' => $payment->customer->email,
                ];
                $payment->makeHidden(['customer']);

                $payment->data_transaction = [
                    'id' => $payment->transaction->id,
                    'reference_no' => $payment->transaction->reference_no,
                ];
                $payment->makeHidden(['transaction']);

                $this->requestNoti = clone $this->request;
                $this->requestNoti->merge(['type_noti' => 'change_status_payment']);
                $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
                $this->requestNoti->merge(['dtData' => $payment]);
                $this->requestNoti->merge(['customer_id' => $partner_id]);
                $this->requestNoti->merge(['type' => $type]);
                $this->requestNoti->merge(['staff_id' => $this->request->input('staff_status')]);
                $this->adminNoti->addNoti($this->requestNoti);

                DB::commit();
                $data['result'] = true;
                $data['data'] = $payment;
                $data['message'] = lang('Thanh toán thành công');
                return response()->json($data);
            } else {
                $data['result'] = false;
                $data['data'] = [];
                $data['message'] = 'Phiếu thu đã được thanh toán.';
                return response()->json($data);
            }
        } catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
