<?php

namespace App\Http\Controllers;

use App\Traits\UploadFile;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Language;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\TransactionService;
use Yajra\DataTables\CollectionDataTable;

class ReportController extends Controller
{
    use UploadFile;
    protected $transactionService;
    public function __construct(Request $request,TransactionService $transactionService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->transactionService = $transactionService;
    }

    public function report_transaction()
    {
        if (!has_permission('report_transaction','view')){
            access_denied();
        }
        $title = lang('Báo cáo doanh thu tổng hợp');
        $dtStatus = getListStatusTransaction();
        return view('admin.report.report_transaction',[
            'title' => $title,
            'dtStatus' => $dtStatus
        ]);
    }

    public function getListReportTransaction()
    {
        $response = $this->transactionService->getListReportTransaction($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
        ->addColumn('id', function ($row) use (&$start) {
            return '<div>'.(++$start).'</div>';
        })
        ->addColumn('customer_leader', function ($dtData) {
            $url = !empty($dtData['avatar_new']) ? $dtData['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
            return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                    '40px') . '<div>'.(!empty($dtData['fullname']) ? $dtData['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($dtData['phone']) ? $dtData['phone'] : 'Chưa có sdt').'</div>';
        })
        ->addColumn('total_transaction', function ($dtData) {
            return '<div>'.(!empty($dtData['total_transaction']) ? number_format($dtData['total_transaction']) : 0).'</div>';
        })
        ->addColumn('total', function ($dtData) {
            return '<div>'.(!empty($dtData['total']) ? formatMoney($dtData['total']) : 0).'</div>';
        })
        ->addColumn('total_promotion', function ($dtData) {
            return '<div>'.(!empty($dtData['total_promotion']) ? formatMoney($dtData['total_promotion']) : 0).'</div>';
        })
        ->addColumn('total_vat', function ($dtData) {
            return '<div>'.(!empty($dtData['total_vat']) ? formatMoney($dtData['total_vat']) : 0).'</div>';
        })
        ->addColumn('grand_total', function ($dtData) {
            return '<div>'.(!empty($dtData['grand_total']) ? formatMoney($dtData['grand_total']) : 0).'</div>';
        })
        ->addColumn('total_discount', function ($dtData) {
            return '<div>'.(!empty($dtData['total_discount']) ? formatMoney($dtData['total_discount']) : 0).'</div>';
        })
        ->addColumn('total_leader', function ($dtData) {
            return '<div>'.(!empty($dtData['total_leader']) ? formatMoney($dtData['total_leader']) : 0).'</div>';
        })
        ->addColumn('total_accumulate', function ($dtData) {
            return '<div>'.(!empty($dtData['total_accumulate']) ? formatMoney($dtData['total_accumulate']) : 0).'</div>';
        })
        ->rawColumns(['id','customer_leader','total_transaction','total','total_promotion','total_vat','grand_total','total_discount','total_leader','total_accumulate'])
        ->setTotalRecords($data['recordsTotal'])
        ->setFilteredRecords($data['recordsFiltered'])
        ->with([
            'draw' => intval($this->request->input('draw')),
        ])
        ->skipPaging()
        ->make(true);
    }
    
    public function report_transaction_detail()
    {
        if (!has_permission('report_transaction_detail','view')){
            access_denied();
        }
        $title = lang('Báo cáo doanh thu chi tiết');
        $dtStatus = getListStatusTransaction();
        return view('admin.report.report_transaction_detail',[
            'title' => $title,
            'dtStatus' => $dtStatus
        ]);
    }

    public function getListReportTransactionDetail()
    {
        $response = $this->transactionService->getListReportTransactionDetail($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
        ->addColumn('id', function ($dtData) use (&$start) {
            if($dtData['type'] == 'parent'){
                return '<div>'.(++$start).'</div>';
            } else {
                return '';
            }
        })
        ->addColumn('reference_no', function ($dtData){
            if($dtData['type'] == 'parent'){
                $id = $dtData['id'];
                return "<a class='dt-modal' href='admin/transaction/view/$id'>".$dtData['reference_no']."</a>";
            } else {
                return '';
            }
        })
        ->editColumn('date', function ($dtData) {
            return '<div>'.(!empty($dtData['date']) ? _dt($dtData['date']) : '').'</div>';
        })
        ->addColumn('customer', function ($dtData) {
            if($dtData['type'] == 'parent'){
                if($dtData['check_leader'] == 1){
                    $level = '<div class="label label-success">Leader</div>';
                } else {
                    $level = '<div class="label label-primary">'.(!empty($dtData['level']) ? 'F'.$dtData['level'] : '').'</div>';
                }
                $customer = $dtData['customer'] ?? [];
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($customer['fullname']) ? $customer['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($customer['phone']) ? $customer['phone'] : 'Chưa có sdt').'</div>'.$level;
            } else {
                return '';
            }
        })
        ->addColumn('customer_leader', function ($dtData) {
            if($dtData['type'] == 'parent'){
                $customer = $dtData['customer_leader'] ?? [];
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($customer['fullname']) ? $customer['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($customer['phone']) ? $customer['phone'] : 'Chưa có sdt').'</div>';
            } else {
                return '';
            }
        })
        ->editColumn('status', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '<div class="btn" data-toggle="dropdown" aria-expanded="false" style="min-width: 150px;border: 1px solid '.getValueStatusTransaction($dtData['status'],'background').' !important">
                <div class="label" style="color: '.getValueStatusTransaction($dtData['status'],'background').'">'.getValueStatusTransaction($dtData['status']).'</div>
            </div>';
            } else {
                return '';
            }
        })
        ->editColumn('product_name', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '';
            } else {
                $image = '<a href="'.$dtData['product_image'].'" data-lightbox="customer-profile" class="display-block mbot5">
                        <img src="'.$dtData['product_image'].'" alt="Product" class="product-image" style="width: 40px;height: 40px;border-radius: 8px;object-fit: cover;">
                    </a>';
                return '<div style="display: flex;align-items: center;flex-wrap: wrap;">'.$image.'<div>'.(!empty($dtData['product_name']) ? $dtData['product_name'] : '').'</div><div style="color:#337ab7">'.(!empty($dtData['variant_option']) ? $dtData['variant_option'] : '').'</div></div>';
            }
        })
        ->addColumn('quantity', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '';
            } else {
                return '<div>'.(!empty($dtData['quantity']) ? number_format($dtData['quantity']) : 0).'</div>';
            }
        })
        ->addColumn('price', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '';
            } else {
                return '<div>'.(!empty($dtData['price']) ? formatMoney($dtData['price']) : 0).'</div>';
            }
        })
        ->addColumn('total_item', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '';
            } else {
                return '<div>'.(!empty($dtData['total_item']) ? formatMoney($dtData['total_item']) : 0).'</div>';
            }
        })
        ->addColumn('total', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '<div>'.(!empty($dtData['total']) ? formatMoney($dtData['total']) : 0).'</div>';
            } else {
                return '';
            }
        })
        ->addColumn('total_promotion', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '<div>'.(!empty($dtData['total_promotion']) ? formatMoney($dtData['total_promotion']) : 0).'</div>';
            } else {
                return '';
            }
        })
        ->addColumn('total_vat', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '<div>'.(!empty($dtData['total_vat']) ? formatMoney($dtData['total_vat']) : 0).'</div>';
            } else {
                return '';
            }
        })
        ->addColumn('grand_total', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '<div>'.(!empty($dtData['grand_total']) ? formatMoney($dtData['grand_total']) : 0).'</div>';
            } else {
                return '';
            }
        })
        ->addColumn('total_discount', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '<div>'.(!empty($dtData['total_discount']) ? formatMoney($dtData['total_discount']) : 0).'</div>';
            } else {
                return '';
            }
        })
        ->addColumn('total_discount_leader', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '<div>'.(!empty($dtData['total_discount_leader']) ? formatMoney($dtData['total_discount_leader']) : 0).'</div>';
            } else {
                return '';
            }
        })
        ->addColumn('total_accumulate', function ($dtData) {
            if($dtData['type'] == 'parent'){
                return '<div>'.(!empty($dtData['total_accumulate']) ? formatMoney($dtData['total_accumulate']) : 0).'</div>';
            } else {
                return '';
            }
        })
        ->rawColumns(['id','customer_leader','quantity','total','total_promotion','total_vat','grand_total',
        'total_discount','reference_no','date','status','product_name','customer','price','total_item',
        'total_discount_leader','total_accumulate'])
        ->setTotalRecords($data['recordsTotal'])
        ->setFilteredRecords($data['recordsFiltered'])
        ->with([
            'draw' => intval($this->request->input('draw')),
        ])
        ->skipPaging()
        ->make(true);
    }

    public function report_booking()
    {
        if (!has_permission('report_booking','view')){
            access_denied();
        }
        $title = lang('Báo cáo lịch hẹn SPA');
        $dtStatus = [
            ['id' => 'pending', 'name' => 'Chờ xác nhận'],
            ['id' => 'confirmed', 'name' => 'Đã xác nhận'],
            ['id' => 'completed', 'name' => 'Hoàn thành'],
            ['id' => 'cancelled', 'name' => 'Đã huỷ']
        ];
        $branches = DB::table('tbl_branches')->get();
        return view('admin.report.report_booking',[
            'title' => $title,
            'dtStatus' => $dtStatus,
            'branches' => $branches
        ]);
    }

    public function getListReportBooking()
    {
        if (!has_permission('report_booking', 'view')) {
            return response()->json(['result' => false, 'message' => lang('Không có quyền xem!')]);
        }

        $query = DB::table('tbl_spa_bookings as b')
            ->select(
                'b.id_client',
                'b.customer_phone',
                'b.customer_name',
                DB::raw('COUNT(b.id) as total_bookings'),
                DB::raw('SUM(b.total_amount) as total_amount'),
                DB::raw('SUM((SELECT COUNT(*) FROM tbl_spa_booking_services bs WHERE bs.id_booking = b.id)) as total_services')
            )
            ->groupBy('b.id_client', 'b.customer_phone', 'b.customer_name');

        // Lọc theo chi nhánh
        if ($this->request->filled('branch_search') && $this->request->input('branch_search') != '-1') {
            $query->where('b.branch_id', $this->request->input('branch_search'));
        } else {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if ($user) {
                $userBranchIds = $user->branches()->pluck('tbl_branches.id')->toArray();
                if (!empty($userBranchIds)) {
                    $query->whereIn('b.branch_id', $userBranchIds);
                }
            }
        }

        // Lọc theo trạng thái
        if ($this->request->filled('status_search') && $this->request->input('status_search') != '-1') {
            $query->where('b.status', $this->request->input('status_search'));
        }

        // Lọc theo khách hàng
        if ($this->request->filled('customer_search')) {
            $query->where('b.id_client', $this->request->input('customer_search'));
        }

        // Lọc theo ngày
        if ($this->request->filled('date_search')) {
            $date = $this->request->input('date_search');
            $date_arr = explode(' - ', $date);
            if (count($date_arr) == 2) {
                try {
                    // Try parsing with H:i d/m/Y or d/m/Y H:i
                    $format = (strpos($date_arr[0], ':') !== false) ? 'H:i d/m/Y' : 'd/m/Y';
                    $start = \Carbon\Carbon::createFromFormat($format, $date_arr[0])->startOfDay()->format('Y-m-d H:i:s');
                    $end   = \Carbon\Carbon::createFromFormat($format, $date_arr[1])->endOfDay()->format('Y-m-d H:i:s');
                    $query->whereBetween('b.created_at', [$start, $end]);
                } catch (\Exception $e) {
                    // fall back
                }
            }
        }

        return Datatables::of($query)
            ->addColumn('customer', function ($row) {
                $phone = $row->customer_phone ? $row->customer_phone : 'Chưa có sdt';
                $name = $row->customer_name ? $row->customer_name : 'Khách vãng lai';
                return '<div><b>' . $name . '</b></div><div style="color:#337ab7">' . $phone . '</div>';
            })
            ->editColumn('total_amount', function ($row) {
                return '<div class="text-right">'.number_format((float)$row->total_amount, 0, ',', '.').'</div>';
            })
            ->editColumn('total_bookings', function ($row) {
                return '<div class="text-center">'.number_format((int)$row->total_bookings, 0, ',', '.').'</div>';
            })
            ->editColumn('total_services', function ($row) {
                return '<div class="text-center">'.number_format((int)$row->total_services, 0, ',', '.').'</div>';
            })
            ->addColumn('options', function ($row) {
                $dataAttr = '';
                if ($row->id_client) {
                    $dataAttr .= ' data-client-id="'.$row->id_client.'"';
                }
                if ($row->customer_phone) {
                    $dataAttr .= ' data-customer-phone="'.$row->customer_phone.'"';
                }
                return '<a href="javascript:void(0)" style="font-size: 15px;" class="btn btn-info btn-xs btn-view-detail" '.$dataAttr.'>
                            <i class="fa fa-eye"></i> Xem
                        </a>';
            })
            ->addIndexColumn()
            ->rawColumns(['customer', 'total_amount', 'total_bookings', 'total_services', 'options'])
            ->make(true);
    }

    public function getModalBookingDetail()
    {
        $clientId = $this->request->input('client_id');
        $customerPhone = $this->request->input('customer_phone');
        
        if (empty($clientId) && empty($customerPhone)) {
            return response()->json(['result' => false, 'message' => 'Không đủ dữ liệu truy vấn']);
        }

        $query = DB::table('tbl_spa_bookings as b')
            ->select(
                'b.id', 'b.booking_code', 'b.booking_date', 'b.booking_time', 'b.total_amount', 'b.status', 'br.name as branch_name',
                DB::raw('(SELECT COUNT(*) FROM tbl_spa_booking_services bs WHERE bs.id_booking = b.id) as total_services')
            )
            ->leftJoin('tbl_branches as br', 'br.id', '=', 'b.branch_id')
            ->orderBy('b.created_at', 'desc');

        if (!empty($clientId)) {
            $query->where('b.id_client', $clientId);
        } else {
            $query->where('b.customer_phone', $customerPhone);
        }

        // Apply filters
        if ($this->request->filled('branch_search') && $this->request->input('branch_search') != '-1') {
            $query->where('b.branch_id', $this->request->input('branch_search'));
        } else {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if ($user) {
                $userBranchIds = $user->branches()->pluck('tbl_branches.id')->toArray();
                if (!empty($userBranchIds)) {
                    $query->whereIn('b.branch_id', $userBranchIds);
                }
            }
        }

        if ($this->request->filled('status_search') && $this->request->input('status_search') != '-1') {
            $query->where('b.status', $this->request->input('status_search'));
        }

        if ($this->request->filled('date_search')) {
            $date = $this->request->input('date_search');
            $date_arr = explode(' - ', $date);
            if (count($date_arr) == 2) {
                try {
                    $format = (strpos($date_arr[0], ':') !== false) ? 'H:i d/m/Y' : 'd/m/Y';
                    $start = \Carbon\Carbon::createFromFormat($format, $date_arr[0])->startOfDay()->format('Y-m-d H:i:s');
                    $end   = \Carbon\Carbon::createFromFormat($format, $date_arr[1])->endOfDay()->format('Y-m-d H:i:s');
                    $query->whereBetween('b.created_at', [$start, $end]);
                } catch (\Exception $e) {}
            }
        }

        $bookings = $query->get();
        if ($bookings->isNotEmpty()) {
            $bookingIds = $bookings->pluck('id')->toArray();
            $services = DB::table('tbl_spa_booking_services as bs')
                ->leftJoin('tbl_services as s', 's.id', '=', 'bs.id_service')
                ->leftJoin('tbl_treatment_purchases as tp', 'tp.id', '=', 'bs.id_treatment_purchase')
                ->select(
                    'bs.id_booking', 
                    'bs.amount', 
                    's.name as service_name',
                    'tp.purchase_code',
                    'tp.treatment_name'
                )
                ->whereIn('bs.id_booking', $bookingIds)
                ->get()
                ->groupBy('id_booking');
                
            foreach ($bookings as $b) {
                $b->services = $services->get($b->id, collect());
            }
        }

        return view('admin.report.modal_booking_detail', compact('bookings'));
    }
    public function report_booking_detail()
    {
        if (!has_permission('report_booking_detail','view')){
            access_denied();
        }
        $title = lang('Báo cáo chi tiết lịch hẹn SPA');
        $branches = DB::table('tbl_branches')->get();
        return view('admin.report.report_booking_detail',[
            'title' => $title,
            'branches' => $branches
        ]);
    }

    public function getListReportBookingDetail()
    {
        $query = DB::table('tbl_spa_booking_services as bs')
            ->select(
                's.name as service_name',
                's.image',
                DB::raw('COUNT(DISTINCT bs.id_booking) as total_bookings'),
                DB::raw('SUM(CASE WHEN bs.id_treatment_purchase IS NOT NULL THEN 1 ELSE 0 END) as total_treatment_used'),
                DB::raw('SUM(bs.amount) as total_amount')
            )
            ->join('tbl_spa_bookings as b', 'b.id', '=', 'bs.id_booking')
            ->join('tbl_services as s', 's.id', '=', 'bs.id_service')
            ->where('b.status', '!=', 'cancelled')
            ->groupBy('s.id', 's.name', 's.image');

        if ($this->request->filled('branch_search') && $this->request->input('branch_search') != '-1') {
            $query->where('b.branch_id', $this->request->input('branch_search'));
        } else {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if ($user && empty($this->request->input('branch_search'))) {
                $userBranchIds = $user->branches()->pluck('tbl_branches.id')->toArray();
                if (!empty($userBranchIds)) {
                    $query->whereIn('b.branch_id', $userBranchIds);
                }
            }
        }

        if ($this->request->filled('date_search')) {
            $date = $this->request->input('date_search');
            $date_arr = explode(' - ', $date);
            if (count($date_arr) == 2) {
                try {
                    $format = (strpos($date_arr[0], ':') !== false) ? 'H:i d/m/Y' : 'd/m/Y';
                    $start = \Carbon\Carbon::createFromFormat($format, $date_arr[0])->startOfDay()->format('Y-m-d H:i:s');
                    $end   = \Carbon\Carbon::createFromFormat($format, $date_arr[1])->endOfDay()->format('Y-m-d H:i:s');
                    $query->whereBetween('b.created_at', [$start, $end]);
                } catch (\Exception $e) {}
            }
        }

        return Datatables::of($query)
            ->editColumn('service_name', function ($row) {
                $image = $row->image ? asset('storage/'.$row->image) : ('admin/assets/images/not_available.jpg');
                return '<div class="d-flex align-items-center" style="display: flex; align-items: center;">
                            <img src="'.$image.'" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px;">
                            <span>'.$row->service_name.'</span>
                        </div>';
            })
            ->editColumn('total_amount', function ($row) {
                return '<div class="text-right">'.number_format((float)$row->total_amount, 0, ',', '.').'</div>';
            })
            ->editColumn('total_bookings', function ($row) {
                return '<div class="text-center">'.number_format((int)$row->total_bookings, 0, ',', '.').'</div>';
            })
            ->editColumn('total_treatment_used', function ($row) {
                return '<div class="text-center">'.number_format((int)$row->total_treatment_used, 0, ',', '.').'</div>';
            })
            ->addIndexColumn()
            ->rawColumns(['service_name', 'total_amount', 'total_bookings', 'total_treatment_used'])
            ->make(true);
    }
}