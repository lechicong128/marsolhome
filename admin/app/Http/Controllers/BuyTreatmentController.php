<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\TreatmentPurchase;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use App\Services\AccountService;

class BuyTreatmentController extends Controller
{
    protected $dbAccount;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        $this->dbAccount = $accountService;
        DB::enableQueryLog();
    }

    /**
     * Danh sách liệu trình đã mua
     */
    public function get_list()
    {
        if (!has_permission('buy_treatment', 'view')) {
            access_denied();
        }
        $category_services = \App\Models\CategoryService::where('active', 1)->orderBy('name')->get();

        $branches = DB::table('tbl_branches')
            ->where('active', 1)
            ->get();

        return view('admin.buy_treatment.list', [
            'title'             => 'Thẻ liệu trình',
            'category_services' => $category_services,
            'branches'          => $branches,
        ]);
    }

    /**
     * Form tạo / chỉnh sửa
     */
    public function detail($id = '')
    {
        if (!has_permission('buy_treatment', 'view')) {
            access_denied();
        }

        if (empty($id)) {
            if (!has_permission('buy_treatment', 'add')) {
                access_denied();
            }
            $title    = 'Thêm thẻ liệu trình';
            $purchase = null;
        } else {
            if (!has_permission('buy_treatment', 'edit')) {
                access_denied();
            }
            $title    = 'Chỉnh sửa thẻ liệu trình';
            $purchase = TreatmentPurchase::find($id);
            if (empty($purchase)) {
                abort(404);
            }
            if ($purchase->used_sessions > 0) {
                abort(403, 'Không thể chỉnh sửa liệu trình đã có buổi sử dụng.');
            }
        }

        // Danh sách chuyên mục dịch vụ đang active
        $categories = \App\Models\CategoryService::where('active', 1)->orderBy('name')->get();

        return view('admin.buy_treatment.detail', compact('id', 'title', 'purchase', 'categories'));
    }

    /**
     * DataTable
     */
    public function getTable()
    {
        $query = DB::table('tbl_treatment_purchases as tp')
            ->leftJoin('tbl_category_services as cs', 'cs.id', '=', 'tp.id_category')
            ->leftJoin('tbl_branches as br', 'br.id', '=', 'tp.id_branch')
            ->select(
                'tp.id',
                'tp.purchase_code',
                'tp.customer_name',
                'tp.customer_phone',
                'tp.treatment_name',
                'tp.total_sessions',
                'tp.used_sessions',
                'tp.price',
                'tp.status',
                'tp.created_at',
                'cs.name as category_name',
                'br.name as branch_name'
            );

        if ($this->request->filled('id_category')) {
            $query->where('tp.id_category', $this->request->input('id_category'));
        }
        if ($this->request->filled('id_branch')) {
            $query->where('tp.id_branch', $this->request->input('id_branch'));
        }
        if ($this->request->filled('status')) {
            $query->where('tp.status', $this->request->input('status'));
        }
        if ($this->request->filled('search_customer')) {
            $query->where('tp.id_client', $this->request->input('search_customer'));
        }

        return DataTables::of($query)
            ->editColumn('customer_name', function ($row) {
                return $row->customer_name . '<br><small class="text-muted">' . $row->customer_phone . '</small>';
            })
            ->addColumn('options', function ($row) {
                $view   = "<a class='dt-modal' href='admin/buy_treatment/view/$row->id'><i class='fa fa-eye'></i> Xem</a>";

                $editLi = '';
                $deleteLi = '';
                if (($row->used_sessions ?? 0) == 0) {
                    $edit   = "<a href='admin/buy_treatment/detail/$row->id'><i class='fa fa-pencil'></i> Sửa</a>";
                    $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/buy_treatment/delete/' . $row->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> Xoá</a>';
                    $editLi = '<li style="cursor: pointer">' . $edit . '</li>';
                    $deleteLi = '<li style="cursor: pointer">' . $delete . '</li>';
                }

                return '<div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $view . '</li>
                                ' . $editLi . '
                                ' . $deleteLi . '
                            </ul>
                        </div>';
            })
            ->editColumn('price', function ($row) {
                return '<div class="text-right">' . number_format($row->price ?? 0) . '</div>';
            })
            ->editColumn('total_sessions', function ($row) {
                return '<div class="text-center">' . ($row->total_sessions ?? 0) . ' buổi</div>';
            })
            ->editColumn('used_sessions', function ($row) {
                $remaining = ($row->total_sessions ?? 0) - ($row->used_sessions ?? 0);
                return '<div class="text-center">' . ($row->used_sessions ?? 0) . ' / ' . ($row->total_sessions ?? 0) . '<br><small class="text-muted">Còn: ' . $remaining . '</small></div>';
            })
            ->editColumn('status', function ($row) {
                $map = [
                    'active'    => ['label' => 'Đang dùng',   'class' => 'success'],
                    'completed' => ['label' => 'Đã hoàn thành', 'class' => 'primary'],
                    'cancelled' => ['label' => 'Đã huỷ',     'class' => 'danger'],
                ];
                $s = $map[$row->status] ?? ['label' => $row->status, 'class' => 'default'];
                return '<span class="label label-' . $s['class'] . '">' . $s['label'] . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return '<div class="text-center">' . date('d/m/Y H:i', strtotime($row->created_at)) . '</div>';
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'customer_name', 'price', 'total_sessions', 'used_sessions', 'status', 'created_at'])
            ->make(true);
    }

    /**
     * Lưu (thêm / cập nhật)
     */
    public function submit($id = 0)
    {
        $data = [];

        $validator = Validator::make(
            $this->request->all(),
            [
                'treatment_name'=> 'required',
                'customer_name' => 'required',
                'total_sessions'=> 'required|integer|min:1',
                'price'         => 'required',
            ],
            [
                'treatment_name.required'  => 'Bạn chưa nhập tên liệu trình',
                'customer_name.required'   => 'Bạn chưa nhập tên thành viên',
                'total_sessions.required'  => 'Bạn chưa nhập số buổi',
                'total_sessions.min'       => 'Số buổi phải ≥ 1',
                'price.required'           => 'Bạn chưa nhập giá trị liệu trình',
            ]
        );

        if ($validator->fails()) {
            $data['result']  = false;
            $data['message'] = $validator->errors()->all();
            return response()->json($data);
        }

        if (!empty($id)) {
            $purchase = TreatmentPurchase::find($id);
            if (empty($purchase)) {
                $data['result']  = false;
                $data['message'] = 'Không tìm thấy liệu trình';
                return response()->json($data);
            }
        } else {
            $purchase = new TreatmentPurchase();
        }

        DB::beginTransaction();
        try {
            $purchase->id_category     = $this->request->input('id_category');
            $purchase->treatment_name  = $this->request->input('treatment_name');
            $purchase->id_client       = $this->request->input('id_client') ?: null;
            $purchase->customer_name   = $this->request->input('customer_name');
            $purchase->customer_phone  = $this->request->input('customer_phone');
            $purchase->id_branch       = $this->request->input('id_branch', 0); // 0 = áp dụng tất cả
            $purchase->total_sessions  = (int) $this->request->input('total_sessions');
            $purchase->price           = number_unformat($this->request->input('price', 0));
            $purchase->note            = $this->request->input('note');
            $purchase->status          = $this->request->input('status', 'active');

            // Khi tạo mới: used_sessions = 0
            if (empty($id)) {
                $purchase->used_sessions = 0;
            }

            $purchase->save();

            // Auto tạo mã
            if (empty($purchase->purchase_code)) {
                $purchase->purchase_code = 'LT-' . date('Ymd') . '-' . str_pad($purchase->id, 5, '0', STR_PAD_LEFT);
                $purchase->save();
            }

            // Gửi thông báo khi tạo mới thẻ (dành cho client có tài khoản app)
            if (empty($id) && !empty($purchase->id_client)) {
                $newRequest = clone $this->request;
                $newRequest->merge(['id' => $purchase->id_client]);
                $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
                $arr_object_id  = $responseClient->getData(true)['client'] ?? [];
                
                $dtData = [
                    'treatment_name'     => $purchase->treatment_name,
                    'price'              => $purchase->price,
                    'purchase_code'      => $purchase->purchase_code,
                    'total_sessions'     => $purchase->total_sessions,
                    'remaining_sessions' => $purchase->total_sessions - $purchase->used_sessions,
                ];
                Notification::notiTreatment('create', $purchase->id_client, $dtData, $arr_object_id, $purchase->id);
            }

            DB::commit();
            $data['result']  = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $e) {
            DB::rollBack();
            $data['result']  = false;
            $data['message'] = $e->getMessage();
            return response()->json($data);
        }
    }

    /**
     * Xem chi tiết (load trong modal)
     */
    public function view($id)
    {
        if (!has_permission('buy_treatment', 'view')) {
            access_denied();
        }

        $purchase = DB::table('tbl_treatment_purchases as tp')
            ->leftJoin('tbl_category_services as cs', 'cs.id', '=', 'tp.id_category')
            ->leftJoin('tbl_branches as br', 'br.id', '=', 'tp.id_branch')
            ->select(
                'tp.*',
                'cs.name as category_name',
                'br.name as branch_name'
            )
            ->where('tp.id', $id)
            ->first();

        if (empty($purchase)) {
            abort(404);
        }

        // Lịch sử sử dụng buổi
        $sessions = DB::table('tbl_treatment_sessions')
            ->where('id_purchase', $id)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.buy_treatment.view', compact('purchase', 'sessions'));
    }

    /**
     * Ghi nhận sử dụng 1 buổi
     */
    public function useSession($id)
    {
        $purchase = TreatmentPurchase::find($id);
        if (empty($purchase)) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy liệu trình']);
        }

        $remaining = $purchase->total_sessions - $purchase->used_sessions;
        if ($remaining <= 0) {
            return response()->json(['result' => false, 'message' => 'Liệu trình đã hết buổi']);
        }

        DB::beginTransaction();
        try {
            $purchase->used_sessions += 1;
            // Tự động đổi trạng thái khi hết buổi
            if ($purchase->used_sessions >= $purchase->total_sessions) {
                $purchase->status = 'completed';
            }
            $purchase->save();

            // Ghi log buổi
            DB::table('tbl_treatment_sessions')->insert([
                'id_purchase' => $id,
                'note'        => $this->request->input('note', ''),
                'created_by'  => auth()->id(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Gửi thông báo sử dụng liệu trình thủ công
            if (!empty($purchase->id_client)) {
                $newRequest = clone $this->request;
                $newRequest->merge(['id' => $purchase->id_client]);
                $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
                $arr_object_id  = $responseClient->getData(true)['client'] ?? [];
                
                $dtData = [
                    'treatment_name'     => $purchase->treatment_name,
                    'price'              => $purchase->price,
                    'purchase_code'      => $purchase->purchase_code,
                    'booking_code'       => '',
                    'total_sessions'     => $purchase->total_sessions,
                    'remaining_sessions' => $purchase->total_sessions - $purchase->used_sessions,
                ];
                Notification::notiTreatment('use', $purchase->id_client, $dtData, $arr_object_id, $purchase->id);
                
                if ($purchase->status == 'completed') {
                    Notification::notiTreatment('completed', $purchase->id_client, $dtData, $arr_object_id, $purchase->id);
                }
            }

            DB::commit();
            return response()->json(['result' => true, 'message' => 'Đã ghi nhận 1 buổi sử dụng']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Đổi trạng thái (active / completed / cancelled)
     */
    public function changeStatus($id)
    {
        $purchase = TreatmentPurchase::find($id);
        if (empty($purchase)) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy']);
        }
        try {
            $status = $this->request->input('status', 'active');
            $purchase->status = $status;
            $purchase->save();

            // Nếu hủy / hoàn thành thẻ thì thông báo
            if (in_array($status, ['cancelled', 'completed']) && !empty($purchase->id_client)) {
                $newRequest = clone $this->request;
                $newRequest->merge(['id' => $purchase->id_client]);
                $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
                $arr_object_id  = $responseClient->getData(true)['client'] ?? [];
                
                $dtData = [
                    'treatment_name'     => $purchase->treatment_name,
                    'price'              => $purchase->price,
                    'purchase_code'      => $purchase->purchase_code,
                    'total_sessions'     => $purchase->total_sessions,
                    'remaining_sessions' => $purchase->total_sessions - $purchase->used_sessions,
                ];
                Notification::notiTreatment($status == 'completed' ? 'completed' : 'cancel', $purchase->id_client, $dtData, $arr_object_id, $purchase->id);
            }

            return response()->json(['result' => true, 'message' => lang('dt_success')]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Xoá
     */
    public function delete($id)
    {
        if (!has_permission('buy_treatment', 'delete')) {
            return response()->json(['result' => false, 'message' => lang('dt_access')]);
        }

        $purchase = TreatmentPurchase::find($id);
        if (empty($purchase)) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy']);
        }

        try {
            // Xoá log buổi trước
            DB::table('tbl_treatment_sessions')->where('id_purchase', $id)->delete();
            $purchase->delete();
            return response()->json(['result' => true, 'message' => lang('dt_success')]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }
}
