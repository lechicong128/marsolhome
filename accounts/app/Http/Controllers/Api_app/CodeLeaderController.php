<?php

namespace App\Http\Controllers\Api_app;

use App\Models\CodeLeader;
use App\Models\Clients;
use App\Services\AdminService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CodeLeaderController extends AuthController
{
    use UploadFile;

    protected $AdminService;
    protected $_locale;

    public function __construct(Request $request, AdminService $adminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->AdminService = $adminService;
        $this->_locale = $request->_locale;
    }

    /**
     * Lấy danh sách mã leader (DataTable server-side)
     */
    public function getList()
    {
        try {
            $search = $this->request->input('search.value');
            $start = $this->request->input('start', 0);
            $length = $this->request->input('length', 10);
            $orderColumnIndex = $this->request->input('order.0.column');
            $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
            $orderDir = $this->request->input('order.0.dir', 'asc');
            
            // Fix lỗi sort cột ảo 'stt' hoặc 'options'
            $validOrderColumns = ['id', 'code', 'status', 'customer_id', 'created_at', 'used_at'];
            if (!in_array($orderBy, $validOrderColumns)) {
                $orderBy = 'id';
            }

            $customer_search = $this->request->input('customer_search') ?? 0;
            $date_search = $this->request->input('date_search') ?? null;
            $status_search = $this->request->input('status_search');

            if (!is_numeric($status_search)) {
                $status_search = -1;
            }
            $status_search = intval($status_search);

            // Parse date range
            $start_date = null;
            $end_date = null;
            if (!empty($date_search)) {
                $date_search = explode(' - ', $date_search);
                if (count($date_search) == 2) {
                    $start_date = to_sql_date($date_search[0], true);
                    $end_date = to_sql_date($date_search[1], false) . ' 23:59:59';
                }
            }

            $query = CodeLeader::with([
                'customer' => function ($q) {
                    $q->select('id', 'fullname', 'phone', 'email', 'avatar');
                },
            ])->where('id', '!=', 0);

            // Search
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%$search%");
                    $q->orWhereHas('customer', function ($instance) use ($search) {
                        $instance->where('fullname', 'like', "%$search%");
                        $instance->orWhere('phone', 'like', "%$search%");
                    });
                });
            }

            // Filter by status
            if ($status_search != -1) {
                $query->where('status', $status_search);
            }

            // Filter by customer
            if (!empty($customer_search)) {
                $query->where('customer_id', $customer_search);
            }

            // Filter by date
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }

            $total = CodeLeader::count();
            $filtered = (clone $query)->count();
            $data = $query->orderBy($orderBy, $orderDir)->skip($start)->take($length)->get();

            // Process avatar URL
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

            return response()->json([
                'total' => $total,
                'filtered' => $filtered,
                'data' => $data,
                'result' => true,
                'message' => 'Lấy danh sách thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'total' => 0,
                'filtered' => 0,
                'data' => [],
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Đếm tổng mã leader theo trạng thái
     */
    public function countAll()
    {
        $customer_search = $this->request->input('customer_search') ?? null;
        $date_search = $this->request->input('date_search') ?? null;

        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0], true);
            $end_date = to_sql_date($date_search[1], false) . ' 23:59:59';
        }

        $baseQuery = CodeLeader::where('id', '!=', 0);

        if (!empty($customer_search)) {
            $baseQuery->where('customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $baseQuery->whereBetween('created_at', [$start_date, $end_date]);
        }

        $all = (clone $baseQuery)->count();

        $counts = [
            0 => (clone $baseQuery)->where('status', 0)->count(),
            1 => (clone $baseQuery)->where('status', 1)->count(),
        ];

        $arr = [
            ['status' => 0, 'count' => $counts[0]],
            ['status' => 1, 'count' => $counts[1]],
        ];

        return response()->json([
            'total' => $all,
            'filtered' => $all,
            'data' => $arr,
            'result' => true,
            'message' => 'success',
        ]);
    }

    /**
     * Tạo mã leader mới
     */
    public function submit()
    {
        $id = $this->request->input('id') ?? 0;
        $create_mode = $this->request->input('create_mode', 'manual');
        $quantity = $this->request->input('quantity') ?? 1;
        $code_input = $this->request->input('code', '');
        $note = $this->request->input('note') ?? null;

        if ($create_mode == 'manual') {
            $quantity = 1;
            $code_input = trim($code_input);
            if (empty($code_input)) {
                return response()->json([
                    'result' => false,
                    'message' => 'Vui lòng nhập mã Leader',
                ]);
            }
            if (CodeLeader::where('code', $code_input)->exists()) {
                return response()->json([
                    'result' => false,
                    'message' => 'Mã Leader này đã tồn tại, vui lòng nhập mã khác',
                ]);
            }
        } else {
            $validator = Validator::make($this->request->all(), [
                'quantity' => 'required|integer|min:1|max:100',
            ], [
                'quantity.required' => 'Vui lòng nhập số lượng mã',
                'quantity.min' => 'Số lượng tối thiểu là 1',
                'quantity.max' => 'Số lượng tối đa là 100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => $validator->errors()->first(),
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $createdCodes = [];

            for ($i = 0; $i < $quantity; $i++) {
                if ($create_mode == 'manual') {
                    $code = strtoupper($code_input);
                    $dtData = new CodeLeader();
                    $dtData->code = $code;
                    $dtData->status = 0;
                    $dtData->note = $note;
                    $dtData->save();
                } else {
                    $code = createCodeLeader($note);
                }
                $createdCodes[] = $code;
            }

            DB::commit();
            return response()->json([
                'result' => true,
                'message' => "Tạo thành công {$quantity} mã Leader",
                'data' => $createdCodes,
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Xoá mã leader
     */
    public function delete()
    {
        $id = $this->request->input('id') ?? 0;
        $dtData = CodeLeader::find($id);

        if (empty($dtData)) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy mã Leader',
            ]);
        }

        if ($dtData->status == 1) {
            return response()->json([
                'result' => false,
                'message' => 'Không thể xóa mã đã được sử dụng',
            ]);
        }

        DB::beginTransaction();
        try {
            $dtData->delete();
            DB::commit();
            return response()->json([
                'result' => true,
                'message' => 'Xóa mã Leader thành công',
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Đổi trạng thái mã leader
     */
    public function changeStatus()
    {
        $id = $this->request->input('id') ?? 0;
        $dtData = CodeLeader::find($id);

        if (empty($dtData)) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy mã Leader',
            ]);
        }

        DB::beginTransaction();
        try {
            if ($dtData->status == 0) {
                $dtData->status = 1;
                $dtData->used_at = Carbon::now();
            } else {
                $dtData->status = 0;
                $dtData->customer_id = null;
                $dtData->used_at = null;
            }
            $dtData->save();

            DB::commit();
            return response()->json([
                'result' => true,
                'message' => 'Cập nhật trạng thái thành công',
                'data' => $dtData,
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Gán khách hàng vào mã leader
     */
    public function addClient()
    {
        $code_leader_id = $this->request->input('code_leader_id') ?? 0;
        $customer_id = $this->request->input('customer_id') ?? 0;

        $validator = Validator::make($this->request->all(), [
            'code_leader_id' => 'required',
            'customer_id' => 'required',
        ], [
            'code_leader_id.required' => 'Vui lòng chọn mã Leader',
            'customer_id.required' => 'Vui lòng chọn khách hàng',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $dtData = CodeLeader::find($code_leader_id);
        if (empty($dtData)) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy mã Leader',
            ]);
        }

        if ($dtData->status == 1) {
            return response()->json([
                'result' => false,
                'message' => 'Mã Leader này đã được sử dụng',
            ]);
        }

        $customer = Clients::find($customer_id);
        if (empty($customer)) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy khách hàng',
            ]);
        }

        // Kiểm tra khách hàng đã có mã leader chưa
        $existingCode = CodeLeader::where('customer_id', $customer_id)
            ->where('status', 1)
            ->first();
        if (!empty($existingCode)) {
            return response()->json([
                'result' => false,
                'message' => 'Khách hàng này đã được gán mã Leader: ' . $existingCode->code,
            ]);
        }

        DB::beginTransaction();
        try {
            $dtData->customer_id = $customer_id;
            $dtData->status = 1;
            $dtData->used_at = Carbon::now();
            $dtData->save();

            DB::commit();
            return response()->json([
                'result' => true,
                'message' => 'Gán khách hàng thành công',
                'data' => $dtData,
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    // Đã thay thế bằng hàm createCodeLeader trong helpers
    public function updatedataold()
    {
        DB::beginTransaction();
        try {
            $clients = \App\Models\Clients::all();
            $countLeader = 0;
            $countNonLeader = 0;

            foreach ($clients as $client) {
                if ($client->is_leader == 1) {
                    if (!empty($client->code_introduce)) {
                        $checkCode = CodeLeader::where('code', $client->code_introduce)->first();
                        if (!$checkCode) {
                            $checkCode = new CodeLeader();
                            $checkCode->code = $client->code_introduce;
                        }
                        $checkCode->status = 1;
                        $checkCode->customer_id = $client->id;
                        if (empty($checkCode->used_at)) {
                            $checkCode->used_at = date('Y-m-d H:i:s');
                        }
                        $checkCode->save();
                        $countLeader++;
                    }
                } else {
                    if (!empty($client->code_introduce)) {
                        $client->code_introduce = '';
                        $client->save();
                        $countNonLeader++;
                    }
                }
            }

            DB::commit();
            return response()->json([
                'result' => true,
                'message' => "Đồng bộ thành công! Đã tạo/cập nhật $countLeader mã Leader và xóa rỗng $countNonLeader mã của khách hàng thường."
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
