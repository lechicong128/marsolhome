<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Models\Notification;
use App\Services\AccountService;

class BookingController extends Controller
{
    protected $dbAccount;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        $this->dbAccount = $accountService;
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('booking', 'view') && !has_permission('booking', 'viewown')) {
            access_denied();
        }
        return view('admin.bookings.list', [
            'title' => 'Danh sách lịch hẹn',
        ]);
    }

    public function getTable()
    {
        if (!has_permission('booking', 'view') && !has_permission('booking', 'viewown')) {
            return response()->json(['result' => false, 'message' => lang('Không có quyền xem!')]);
        }
        $query = DB::table('tbl_spa_bookings as b')
            ->select(
                'b.id',
                'b.booking_code',
                'b.customer_name',
                'b.customer_phone',
                'b.total_amount',
                'b.payment_method',
                'b.payment_status',
                'b.status',
                'b.note',
                'b.created_at',
                'br.name as branch_name',
                'pm.name as payment_method_name'
            )
            ->leftJoin('tbl_branches as br', 'br.id', '=', 'b.branch_id')
            ->leftJoin('tbl_payment_mode as pm', 'pm.id', '=', 'b.payment_method')
            ->orderBy('b.id', 'desc');

        // Lọc theo filter (nếu có)
        if ($this->request->filled('status')) {
            $query->where('b.status', $this->request->input('status'));
        }
        if ($this->request->filled('payment_method')) {
            $query->where('b.payment_method', $this->request->input('payment_method'));
        }
        if ($this->request->filled('booking_date')) {
            $query->whereDate('b.created_at', $this->request->input('booking_date'));
        }

        // Lọc theo chi nhánh của user: nếu user được gán các chi nhánh cụ thể → chỉ thấy các chi nhánh đó, còn không (không gán chi nhánh nào) → thấy hết
        $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if ($user) {
            $userBranchIds = $user->branches()->pluck('tbl_branches.id')->toArray();
            if (!empty($userBranchIds)) {
                $query->whereIn('b.branch_id', $userBranchIds);
            }
        }

        return Datatables::of($query)
            ->addColumn('options', function ($row) {
                return '<a style="font-size: 15px;" type="button" class="btn btn-info btn-xs btn-view-booking" data-id="' . $row->id . '">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a style="font-size: 15px;" href="javascript:void(0)" onclick="printBill(' . $row->id . ')" class="btn btn-primary btn-xs">
                            <i class="fa fa-print"></i>
                        </a>
                        <a style="font-size: 15px;" type="button" class="btn btn-danger btn-xs btn-delete-booking" data-id="' . $row->id . '">
                            <i class="fa fa-trash"></i>
                        </a>';
            })
            ->editColumn('status', function ($row) {
                $map = [
                    'pending'   => ['label' => 'Chờ xác nhận', 'class' => 'warning'],
                    'confirmed' => ['label' => 'Đã xác nhận',  'class' => 'info'],
                    'completed' => ['label' => 'Hoàn thành',   'class' => 'success'],
                    'cancelled' => ['label' => 'Đã huỷ',       'class' => 'danger'],
                ];
                $s = $map[$row->status] ?? ['label' => $row->status, 'class' => 'default'];
                return '<span class="label label-' . $s['class'] . '">' . $s['label'] . '</span>';
            })
            ->editColumn('payment_method_name', function ($row) {
                return $row->payment_method_name
                    ? '<span class="label label-primary">' . $row->payment_method_name . '</span>'
                    : '<span class="text-muted">—</span>';
            })
            ->editColumn('payment_status', function ($row) {
                $map = [
                    'pending'   => ['label' => 'Chờ thanh toán', 'class' => 'warning'],
                    'paid'      => ['label' => 'Đã thanh toán',  'class' => 'success'],
                    'pay_later' => ['label' => 'Thanh toán sau', 'class' => 'default'],
                    'transfer'  => ['label' => 'Chuyển khoản',   'class' => 'primary'],
                ];
                $s = $map[$row->payment_status] ?? ['label' => $row->payment_status, 'class' => 'default'];
                return '<span class="label label-' . $s['class'] . '">' . $s['label'] . '</span>';
            })
            ->editColumn('total_amount', function ($row) {
                return number_format($row->total_amount, 0, ',', '.') . ' đ';
            })
            ->editColumn('created_at', function ($row) {
                return date('d/m/Y', strtotime($row->created_at));
            })
            ->editColumn('branch_name', function ($row) {
                return $row->branch_name ?? '<span class="text-muted">—</span>';
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'status', 'payment_method_name', 'payment_status', 'branch_name', 'total_amount'])
            ->make(true);
    }

    public function view($id)
    {
        if (!has_permission('booking', 'view') && !has_permission('booking', 'viewown')) {
            access_denied();
        }
        $booking = DB::table('tbl_spa_bookings as b')
            ->select('b.*', 'br.name as branch_name', 'pm.name as payment_method_name')
            ->leftJoin('tbl_branches as br', 'br.id', '=', 'b.branch_id')
            ->leftJoin('tbl_payment_mode as pm', 'pm.id', '=', 'b.payment_method')
            ->where('b.id', $id)
            ->first();

        if (empty($booking)) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy lịch hẹn']);
        }

        // Lấy dịch vụ kèm id_category từ tbl_services để lọc liệu trình đúng loại
        // Không JOIN trực tiếp vào tbl_treatment_purchases qua id_treatment_purchase
        // vì cột này có thể chưa tồn tại trong DB — sẽ fetch riêng bên dưới
        $services = DB::table('tbl_spa_booking_services as bs')
            ->leftJoin('tbl_services as sv', 'sv.id', '=', 'bs.id_service')
            ->select('bs.*', 'sv.id_category')
            ->where('bs.id_booking', $id)
            ->get();

        // Lấy thông tin liệu trình đã áp dụng cho từng dịch vụ (nếu cột tồn tại)
        $appliedPurchases = [];
        try {
            $appliedIds = $services
                ->filter(fn($s) => !empty($s->id_treatment_purchase))
                ->pluck('id_treatment_purchase')
                ->unique()->values();
            if ($appliedIds->isNotEmpty()) {
                $appliedPurchases = DB::table('tbl_treatment_purchases')
                    ->whereIn('id', $appliedIds)
                    ->get()
                    ->keyBy('id');
            }
        } catch (\Exception $e) {
            // Cột id_treatment_purchase chưa tồn tại trong DB → bỏ qua
        }

        // Gắn thông tin liệu trình đã áp dụng vào từng service
        $services = $services->map(function ($svc) use ($appliedPurchases) {
            $tp = !empty($svc->id_treatment_purchase)
                ? ($appliedPurchases[$svc->id_treatment_purchase] ?? null)
                : null;
            $svc->applied_purchase_code   = $tp->purchase_code   ?? null;
            $svc->applied_treatment_name  = $tp->treatment_name  ?? null;
            $svc->applied_used            = $tp->used_sessions   ?? null;
            $svc->applied_total           = $tp->total_sessions  ?? null;
            return $svc;
        });

        // Liệu trình của khách (nếu booking có id_client), còn active
        $treatmentPurchases = collect();
        if (!empty($booking->id_client)) {
            $treatmentPurchases = DB::table('tbl_treatment_purchases as tp')
                ->leftJoin('tbl_category_services as cs', 'cs.id', '=', 'tp.id_category')
                ->select(
                    'tp.id',
                    'tp.purchase_code',
                    'tp.treatment_name',
                    'tp.id_category',
                    'tp.total_sessions',
                    'tp.used_sessions',
                    'tp.status',
                    'cs.name as category_name'
                )
                ->where('tp.id_client', $booking->id_client)
                ->where('tp.status', 'active')
                ->whereRaw('tp.used_sessions < tp.total_sessions')
                ->orderBy('tp.id', 'desc')
                ->get();
        }

        $statusMap = [
            'pending'   => ['label' => 'Chờ xác nhận', 'class' => 'warning'],
            'confirmed' => ['label' => 'Đã xác nhận',  'class' => 'info'],
            'completed' => ['label' => 'Hoàn thành',   'class' => 'success'],
            'cancelled' => ['label' => 'Đã huỷ',       'class' => 'danger'],
        ];

        return view('admin.bookings.view', [
            'booking'            => $booking,
            'services'           => $services,
            'statusMap'          => $statusMap,
            'treatmentPurchases' => $treatmentPurchases,
        ]);
    }

    /**
     * Áp dụng liệu trình vào một mặt hàng của booking
     */
    public function applyTreatment()
    {
        $bookingServiceId    = $this->request->input('id_booking_service');
        $treatmentPurchaseId = $this->request->input('id_treatment_purchase');

        if (empty($bookingServiceId) || empty($treatmentPurchaseId)) {
            return response()->json(['result' => false, 'message' => 'Thiếu dữ liệu']);
        }

        $bookingSvc = DB::table('tbl_spa_booking_services')->where('id', $bookingServiceId)->first();
        $purchase   = DB::table('tbl_treatment_purchases')->where('id', $treatmentPurchaseId)->first();

        if (empty($bookingSvc) || empty($purchase)) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy dữ liệu']);
        }
        if ($purchase->used_sessions >= $purchase->total_sessions) {
            return response()->json(['result' => false, 'message' => 'Liệu trình đã hết buổi sử dụng']);
        }
        if (!empty($bookingSvc->id_treatment_purchase)) {
            return response()->json(['result' => false, 'message' => 'Mặt hàng này đã được áp dụng liệu trình rồi']);
        }

        DB::beginTransaction();
        try {
            DB::table('tbl_treatment_sessions')->insert([
                'id_purchase'        => $purchase->id,
                'id_booking'         => $bookingSvc->id_booking,
                'id_booking_service' => $bookingSvc->id,
                'note'               => 'Sử dụng 1 buổi từ liệu trình ' . $purchase->purchase_code,
                'created_by'         => \Illuminate\Support\Facades\Auth::guard('admin')->id(),
                'created_at'         => now(),
            ]);

            $newUsed = $purchase->used_sessions + 1;
            DB::table('tbl_treatment_purchases')
                ->where('id', $purchase->id)
                ->update([
                    'used_sessions' => $newUsed,
                    'status'        => $newUsed >= $purchase->total_sessions ? 'completed' : 'active',
                ]);

            // Cập nhật mặt hàng thành 0 đồng vì dùng liệu trình
            DB::table('tbl_spa_booking_services')
                ->where('id', $bookingServiceId)
                ->update([
                    'id_treatment_purchase' => $purchase->id,
                    'amount' => 0
                ]);

            // Tính lại tổng tiền của booking
            $newTotalAmount = DB::table('tbl_spa_booking_services')
                ->where('id_booking', $bookingSvc->id_booking)
                ->sum('amount');

            // Cập nhật tổng tiền booking, nếu = 0 thì chuuyển trạng thái thanh toán thành paid 
            $updateBookingData = ['total_amount' => $newTotalAmount];
            if ($newTotalAmount == 0) {
                $updateBookingData['payment_status'] = 'paid';
            }

            DB::table('tbl_spa_bookings')
                ->where('id', $bookingSvc->id_booking)
                ->update($updateBookingData);

            $booking = DB::table('tbl_spa_bookings')->where('id', $bookingSvc->id_booking)->first();
            
            // Gửi thông báo sử dụng liệu trình
            if (!empty($purchase->id_client)) {
                $newRequest = clone $this->request;
                $newRequest->merge(['id' => $purchase->id_client]);
                $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
                $arr_object_id  = $responseClient->getData(true)['client'] ?? [];
                
                $dtData = [
                    'treatment_name'     => $purchase->treatment_name,
                    'price'              => $purchase->price,
                    'purchase_code'      => $purchase->purchase_code,
                    'booking_code'       => $booking ? $booking->booking_code : '',
                    'total_sessions'     => $purchase->total_sessions,
                    'remaining_sessions' => $purchase->total_sessions - $newUsed,
                ];
                Notification::notiTreatment('use', $purchase->id_client, $dtData, $arr_object_id, $purchase->id);
                
                // Nếu vừa dùng xong buổi cuối cùng thì thông báo completed nữa
                if ($newUsed >= $purchase->total_sessions) {
                    Notification::notiTreatment('completed', $purchase->id_client, $dtData, $arr_object_id, $purchase->id);
                }
            }

            DB::commit();
            return response()->json([
                'result'  => true,
                'message' => 'Xác nhận thành công!',
                'used'    => $newUsed,
                'total'   => $purchase->total_sessions,
                'new_booking_total' => $newTotalAmount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    public function changeStatus($id)
    {
        if (!has_permission('booking', 'edit')) {
            return response()->json(['result' => false, 'message' => lang('Không có quyền sửa!')]);
        }
        $status = $this->request->input('status');
        try {
            $booking = DB::table('tbl_spa_bookings')->where('id', $id)->first();
            
            // Nếu chuyển trạng thái thành cancelled (và trước đó chưa phải cancelled) thì hoàn lại số buổi liệu trình
            if ($status == 'cancelled' && $booking->status != 'cancelled') {
                $this->_refundTreatmentForBooking($id, 'cancel');
            }

            DB::table('tbl_spa_bookings')->where('id', $id)->update([
                'status'     => $status,
                'updated_at' => now(),
            ]);
            $booking = DB::table('tbl_spa_bookings')->where('id', $id)->first(); // Refresh
            $newRequest = clone $this->request;
            $newRequest->merge(['id' => $booking->id_client]);
            $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
            $arr_object_id    = $responseClient->getData(true)['client'] ?? [];
            $dtData = [];
            $dtData['reference_no'] = $booking->booking_code;
            $dtData['status'] = $status;
            Notification::notiApproveBookingSpa($booking->id_client,$dtData, $arr_object_id,$id);
                
            return response()->json(['result' => true, 'message' => lang('dt_success')]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        if (!has_permission('booking', 'delete')) {
            return response()->json(['result' => false, 'message' => lang('Không có quyền xóa!')]);
        }
        try {
            // Kiểm tra có phiếu thanh toán đã duyệt không
            // $approvedPayment = DB::table('tbl_spa_payments')
            //     ->where('id_booking', $id)
            //     ->where('status', 'approved')
            //     ->first();

            // if (!empty($approvedPayment)) {
            //     return response()->json([
            //         'result'  => false,
            //         'message' => 'Lịch hẹn này có phiếu thanh toán đã được duyệt (mã: ' . $approvedPayment->payment_code . '). Không thể xóa!',
            //     ]);
            // }

            // Hoàn lại liệu trình trước khi xóa (nếu chưa hoàn)
            $this->_refundTreatmentForBooking($id, 'delete');

            // Xóa phiếu thanh toán chưa duyệt (nếu có)
            DB::table('tbl_spa_payments')->where('id_booking', $id)->delete();
            DB::table('tbl_spa_booking_services')->where('id_booking', $id)->delete();
            DB::table('tbl_spa_bookings')->where('id', $id)->delete();

            return response()->json(['result' => true, 'message' => lang('dt_success')]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Helper hoàn buổi liệu trình khi hủy/xóa lịch hẹn
     */
    private function _refundTreatmentForBooking($bookingId, $action = 'delete')
    {
        $sessions = DB::table('tbl_treatment_sessions')->where('id_booking', $bookingId)->get();
        if ($sessions->isEmpty()) return;

        foreach ($sessions as $session) {
            $alreadyRefunded = strpos($session->note, '(Đã hoàn lại') !== false;

            if (!$alreadyRefunded && in_array($action, ['cancel', 'delete'])) {
                $purchase = DB::table('tbl_treatment_purchases')->where('id', $session->id_purchase)->first();
                if ($purchase) {
                    // Hoàn buổi (giảm số đã dùng)
                    $newUsed = max(0, $purchase->used_sessions - 1);
                    // Nếu hoàn buổi khiến chưa hết số lưu-> đổi state về active
                    $newStatus = ($newUsed < $purchase->total_sessions && $purchase->status == 'completed') 
                                 ? 'active' : $purchase->status;
                    
                    DB::table('tbl_treatment_purchases')->where('id', $purchase->id)->update([
                        'used_sessions' => $newUsed,
                        'status'        => $newStatus
                    ]);
                }
            }

            // Nếu chỉ hủy đơn, chỉ append ghi chú, không xóa data session log lẫn ID liệu trình đã áp dụng ở service
            if ($action == 'cancel' && !$alreadyRefunded) {
                DB::table('tbl_treatment_sessions')->where('id', $session->id)->update([
                    'note' => $session->note . ' (Đã hoàn lại do huỷ lịch hẹn)'
                ]);
            }
        }
        
        // Nếu là xóa đơn, thì xóa sạch log trong DB và tháo link
        if ($action == 'delete') {
            // Xóa lịch sử sử dụng buổi
            DB::table('tbl_treatment_sessions')->where('id_booking', $bookingId)->delete();
            
            // Reset liên kết ở mặt hàng
            DB::table('tbl_spa_booking_services')->where('id_booking', $bookingId)->update([
                'id_treatment_purchase' => null
            ]);
        }
    }
    public function print_bill($id)
    {
        $booking = DB::table('tbl_spa_bookings')->where('id', $id)->first();
        if (!$booking) {
            abort(404, 'Không tìm thấy lịch hẹn');
        }

        $items = DB::table('tbl_spa_booking_services as bs')
                     ->leftJoin('tbl_services as s', 'bs.id_service', '=', 's.id')
                     ->select('bs.*', 's.name as service_name')
                     ->where('bs.id_booking', $id)
                     ->get();

        $client = null;
        $branch = \App\Models\Branch::find($booking->branch_id);

        return view('admin.bookings.print_bill', compact('booking', 'items', 'client', 'branch'));
    }
}
