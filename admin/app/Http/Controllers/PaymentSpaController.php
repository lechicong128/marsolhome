<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Helpers\SocketHelpersAdmin;
use App\Models\Notification;
use App\Services\AccountService;


class PaymentSpaController extends Controller
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
        if (!has_permission('payment_spa', 'view') && !has_permission('payment_spa', 'viewown')) {
            access_denied();
        }
        return view('admin.payment_spa.list', [
            'title' => 'Thanh toán Spa',
        ]);
    }

    public function getTable()
    {
        if (!has_permission('payment_spa', 'view') && !has_permission('payment_spa', 'viewown')) {
            return response()->json(['result' => false, 'message' => lang('Không có quyền xem!')]);
        }
        $query = DB::table('tbl_spa_payments as p')
            ->select(
                'p.id',
                'p.payment_code',
                'p.amount',
                'p.payment_method',
                'p.note',
                'p.status',
                'p.approved_at',
                'p.created_at',
                'b.booking_code',
                'b.customer_name',
                'b.customer_phone',
                'b.booking_date',
                'b.booking_time',
                'b.total_amount as booking_total',
                'br.name as branch_name',
                'pm.name as payment_method_name'
            )
            ->leftJoin('tbl_spa_bookings as b', 'b.id', '=', 'p.id_booking')
            ->leftJoin('tbl_branches as br', 'br.id', '=', 'b.branch_id')
            ->leftJoin('tbl_payment_mode as pm', 'pm.id', '=', 'p.payment_method')
            ->orderBy('p.id', 'desc');

        // Filter
        if ($this->request->filled('status')) {
            $query->where('p.status', $this->request->input('status'));
        }
        if ($this->request->filled('booking_date')) {
            $query->where('b.booking_date', $this->request->input('booking_date'));
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
                return '<a type="button" class="btn btn-info btn-xs btn-view-payment" data-id="' . $row->id . '">
                            <i class="fa fa-eye"></i> Xem
                        </a>';
            })
            ->editColumn('status', function ($row) {
                $map = [
                    'pending'  => ['label' => 'Chờ duyệt', 'class' => 'warning'],
                    'approved' => ['label' => 'Đã duyệt',  'class' => 'success'],
                    'rejected' => ['label' => 'Từ chối',   'class' => 'danger'],
                ];
                $s = $map[$row->status] ?? ['label' => $row->status, 'class' => 'default'];
                return '<span class="label label-' . $s['class'] . '">' . $s['label'] . '</span>';
            })
            ->editColumn('amount', function ($row) {
                return number_format($row->amount, 0, ',', '.') . ' đ';
            })
            ->editColumn('payment_method_name', function ($row) {
                return $row->payment_method_name
                    ? '<span class="label label-primary">' . $row->payment_method_name . '</span>'
                    : '<span class="text-muted">—</span>';
            })
            ->editColumn('booking_date', function ($row) {
                return $row->booking_date ? date('d/m/Y', strtotime($row->booking_date)) : '—';
            })
            ->editColumn('approved_at', function ($row) {
                return $row->approved_at ? date('d/m/Y H:i', strtotime($row->approved_at)) : '—';
            })
            ->editColumn('created_at', function ($row) {
                return date('d/m/Y H:i', strtotime($row->created_at));
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'status', 'payment_method_name'])
            ->make(true);
    }

    public function view($id)
    {
        if (!has_permission('payment_spa', 'view') && !has_permission('payment_spa', 'viewown')) {
            return '<div class="modal-body"><div class="alert alert-danger">' . lang('Không có quyền xem!') . '</div></div>';
        }
        $payment = DB::table('tbl_spa_payments as p')
            ->select(
                'p.*',
                'b.booking_code',
                'b.customer_name',
                'b.customer_phone',
                'b.booking_date',
                'b.booking_time',
                'b.total_amount as booking_total',
                'b.status as booking_status',
                'br.name as branch_name',
                'pm.name as payment_method_name'
            )
            ->leftJoin('tbl_spa_bookings as b', 'b.id', '=', 'p.id_booking')
            ->leftJoin('tbl_branches as br', 'br.id', '=', 'b.branch_id')
            ->leftJoin('tbl_payment_mode as pm', 'pm.id', '=', 'p.payment_method')
            ->where('p.id', $id)
            ->first();

        if (empty($payment)) {
            return '<div class="modal-body"><div class="alert alert-danger">Không tìm thấy dữ liệu.</div></div>';
        }

        $services = DB::table('tbl_spa_booking_services')
            ->where('id_booking', $payment->id_booking)
            ->get();

        return view('admin.payment_spa.view', [
            'payment'  => $payment,
            'services' => $services,
        ]);
    }

    /**
     * Duyệt thanh toán → status = 'approved'
     * Đồng thời cập nhật payment_status của booking → 'paid'
     */
    public function approve($id)
    {
        if (!has_permission('payment_spa', 'edit')) {
            return response()->json(['result' => false, 'message' => lang('Không có quyền duyệt!')]);
        }
        $payment = DB::table('tbl_spa_payments')->where('id', $id)->first();
        if (empty($payment)) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy phiếu thanh toán']);
        }
        try {
            DB::beginTransaction();

            DB::table('tbl_spa_payments')->where('id', $id)->update([
                'status'      => 'approved',
                'amount_payment'      => $payment->amount,
                'approved_by' => auth()->id() ?? 0,
                'approved_at' => now(),
                'updated_at'  => now(),
            ]);

            // Cập nhật trạng thái thanh toán trên booking
            DB::table('tbl_spa_bookings')->where('id', $payment->id_booking)->update([
                'payment_status' => 'paid',
                'updated_at'     => now(),
            ]);

            DB::commit();
            
            SocketHelpersAdmin::sendSocketToClient($payment->id_client, $payment->id_booking, 'payment_booking');
            $booking = DB::table('tbl_spa_bookings')->where('id', $payment->id_booking)->first();
            $newRequest = clone $this->request;
            $newRequest->merge(['id' => $payment->id_client]);
            $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
            $arr_object_id    = $responseClient->getData(true)['client'] ?? [];
            $dtData = [];
            $dtData['reference_no'] = $booking->booking_code;
            $dtData['reference_no_pay'] = $payment->payment_code;
            Notification::notiApprovePaymentBookingSpa($payment->id_client,$dtData, $arr_object_id,$id);
            return response()->json(['result' => true, 'message' => lang('dt_success')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Từ chối thanh toán → status = 'rejected'
     * Đồng thời cập nhật payment_status của booking → 'pending'
     */
    public function reject($id)
    {
        if (!has_permission('payment_spa', 'edit')) {
            return response()->json(['result' => false, 'message' => lang('Không có quyền từ chối!')]);
        }
        $payment = DB::table('tbl_spa_payments')->where('id', $id)->first();
        if (empty($payment)) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy phiếu thanh toán']);
        }
        try {
            DB::beginTransaction();

            DB::table('tbl_spa_payments')->where('id', $id)->update([
                'status'     => 'rejected',
                'updated_at' => now(),
            ]);

            DB::table('tbl_spa_bookings')->where('id', $payment->id_booking)->update([
                'payment_status' => 'pending',
                'updated_at'     => now(),
            ]);

            DB::commit();
            return response()->json(['result' => true, 'message' => lang('dt_success')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }
}
