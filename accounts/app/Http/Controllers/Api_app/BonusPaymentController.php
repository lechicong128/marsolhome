<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ChallengeMeResources;
use App\Models\BonusPayment;
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

class BonusPaymentController extends AuthController
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
        $query = BonusPayment::with([
            'customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            },
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
                    Config::get('constant')['status_success_bonus_payment']
                ]);
            } else {
                $query->where('status', $status_search);
            }
        }
        if (!empty($customer_search)) {
            $query->where('tbl_pay_slip.customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $query->whereBetween('tbl_pay_slip.date', [$start_date, $end_date]);
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
        $total = BonusPayment::count();
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
        $baseQuery = BonusPayment::where('id', '!=', 0);
        if (!empty($customer_search)) {
            $baseQuery->where('customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $baseQuery->whereBetween('date', [$start_date, $end_date]);
        }
        $all = (clone $baseQuery)->count();

        /**
         * Đếm 3 trạng thái theo đúng nghiệp vụ
         */
        $counts = [
            0 => (clone $baseQuery)->where(function ($q){
                $q->where('status', 0);
            })->count(),
            1 => (clone $baseQuery)->where(
                'status',
                [Config::get('constant')['status_success_bonus_payment']]
            )->count(),
        ];
        $arr = [
            ['status' => 0, 'count' => $counts[0]],
            ['status' => 1, 'count' => $counts[1]],
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
        $dtData = BonusPayment::find($id);
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = lang('404_not_found');
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
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
        $id = $this->request->input('bonus_payment_id') ?? 0;
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
            $staff_status = $partner_id;
        } else {
            $staff_status = $this->request->input('staff_status') ?? 0;
        }
        $bonusPayment = BonusPayment::find($id);
        $customer_id = $bonusPayment->customer_id;

        DB::beginTransaction();
        try
        {
            if ($bonusPayment->status == 0){
                $bonusPayment->status = 1;
                $bonusPayment->staff_status = $staff_status;
                $bonusPayment->date_status = date('Y-m-d H:i:s');
                $bonusPayment->save();

                DB::commit();
                $data['result'] = true;
                $data['data'] = $bonusPayment;
                $data['message'] = lang('Chi thưởng thành công');
                return response()->json($data);
            } else {
                $data['result'] = false;
                $data['data'] = [];
                $data['message'] = 'Phiếu chi thưởng đã được chi!.';
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

    public function detail()
    {
        $id = $this->request->input('id') ?? 0;
        $validator = Validator::make($this->request->all(),
            [
                'customer_id' => 'required',
                'payment_mode_id' => 'required',
                'total' => 'required',
            ]
            , [
                'customer_id.required' => 'Bạn chưa chọn Leader',
                'payment_mode_id.required' => 'Vui lòng chọn phương thức thanh toán',
                'total.required' => 'Vui lòng nhập số tiền',
            ]);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        if (empty($id)) {
            $dtData = new BonusPayment();
        } else {
            $dtData = BonusPayment::find($id);
        }
        $responseRefCode = $this->AdminService->getOrderRef('pay_slip');
        $reference_no = $responseRefCode['reference_no'] ?? time();
        DB::beginTransaction();
        try {
            $dtData->reference_no = $reference_no;
            $dtData->date = to_sql_date($this->request->input('date_new'),true);
            $dtData->total = $this->request->input('total') ?? 0;
            $dtData->customer_id = $this->request->input('customer_id') ?? 0;
            $dtData->payment_mode_id = $this->request->input('payment_mode_id') ?? 0;
            $dtData->note = $this->request->input('note') ?? null;
            $dtData->save();
            if ($dtData) {
                $this->AdminService->updateOrderRef('pay_slip');
                DB::commit();
                $data['result'] = true;
                if (empty($id)) {
                    $data['message'] = 'Thêm mới thành công';
                } else {
                    $data['message'] = 'Cập nhật thành công';
                }
            } else {
                $data['result'] = false;
                if (empty($id)) {
                    $data['message'] = 'Thêm mới thất bại';
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
