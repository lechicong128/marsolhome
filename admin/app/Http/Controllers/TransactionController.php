<?php

namespace App\Http\Controllers;


use App\Services\TransactionService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class TransactionController extends Controller
{
    protected $customerService;
    protected $transactionService;
    use UploadFile;
    public function __construct(Request $request,TransactionService $transactionService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->transactionService = $transactionService;
    }

    public function get_list(){
        if (!has_permission('transaction','view') && !has_permission('transaction','viewown')) {
            access_denied();
        }
        $title = lang('dt_transaction');
        return view('admin.transaction.list',[
            'title' => $title
        ]);
    }

    public function getList()
    {
        if (!has_permission('transaction', 'view') && !has_permission('transaction','viewown')) {
            $data['result'] = false;
            $data['message'] = lang('Không có quyền xem!');
            $data['data'] = [];
            return response()->json($data);
        }
        $response = $this->transactionService->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $view = "<a href='admin/transaction/view/$id' class='dt-modal'><i class='fa fa-eye'></i> " . lang('dt_view_transaction') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/transaction/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_transaction') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $view . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->addColumn('reference_no', function ($dtData){
                $id = $dtData['id'];
                return "<a class='dt-modal' href='admin/transaction/view/$id'>".$dtData['reference_no']."</a>";
            })
            ->editColumn('date', function ($dtData) {
                return '<div>'.(!empty($dtData['date']) ? _dt($dtData['date']) : '').'</div>';
            })
            ->editColumn('status', function ($transaction) {
                $optionStatus = '<div class="btn-group">
                                 <button type="button" class="btn btn-white dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false" style="min-width: 150px;border: 1px solid '.getValueStatusTransaction($transaction['status'],'background').' !important">
                                 <div class="label" style="color: '.getValueStatusTransaction($transaction['status'],'background').'">'.getValueStatusTransaction($transaction['status']).'</div>
                                 <span class="caret"></span> </button>
                                 <ul class="dropdown-menu">';
                foreach (getListStatusTransaction() as $key => $value){
                    $index = getValueStatusTransaction($transaction['status'],'index');
                    $arr = [Config::get('constant')['status_cancel']];
                    $check = 0;
                    $classes = '';
                    if ($value['index'] < $index){
                        if (!in_array($value['id'],$arr)) {
                            $classes = 'pointer-events';
                        }
                    }
                    if ($transaction['status'] == Config::get('constant')['status_cancel']){
                        if ($value['id'] != Config::get('constant')['status_cancel']) {
                            $classes = 'pointer-events';
                        }
                    }
                    if ($transaction['status'] == Config::get('constant')['status_finish']){
                        if ($value['id'] != Config::get('constant')['status_finish']) {
                            $classes = 'pointer-events';
                        }
                    }
                    $optionStatus .= '<li style="cursor: pointer" class="'.$classes.'"><a onclick="changeStatus('.$transaction['id'].','.$value['id'].','.$check.')" data-id="'.$value['id'].'">'.$value['name'].'</a></li>';
                }
                $optionStatus .= '</ul></div>';

                $optionStatus .= '<div>'.(!empty($transaction['date_status']) ? _dt($transaction['date_status']) : '').'</div>';
                if($transaction['status'] == Config::get('constant')['status_cancel']){
                    $optionStatus .= '<div>'.(!empty($transaction['note_status']) ? ($transaction['note_status']) : '').'</div>';
                }
                return $optionStatus;
            })
            ->addColumn('customer', function ($dtData) {
                $customer = $dtData['customer'] ?? [];
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($customer['fullname']) ? $customer['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($customer['phone']) ? $customer['phone'] : 'Chưa có sdt').'</div>';
            })
            ->editColumn('customer_level', function ($dtData) {
                if($dtData['check_leader'] == 1){
                    return '<div class="label label-success">Leader</div>';
                } else {
                    return '<div class="label label-primary">'.(!empty($dtData['level']) ? 'F'.$dtData['level'] : '').'</div>';
                }
            })
            ->editColumn('information_delivery', function ($dtData) {
                $str = '<div>
                    <div>Tên : '.($dtData['name_delivery'] ?? '').'</div>
                    <div>SĐT : '.($dtData['phone_delivery'] ?? '').'</div>
                    <div>Địa chỉ : '.($dtData['address_delivery'] ?? '').'</div>
                </div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('total', function ($dtData) {
                return '<div>'.(!empty($dtData['total']) ? formatMoney($dtData['total']) : 0).'</div>';
            })
            ->addColumn('reduction', function ($dtData) {
                $htmlReduction = '<table class="stats-table">
                        <tr>
                            <td class="label-cell">Giảm giá:</td>
                            <td class="value-cell">'.(!empty($dtData['total_promotion']) ? formatMoney($dtData['total_promotion']) : 0).'</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Chiết khấu:</td>
                            <td class="value-cell">'.(!empty($dtData['total_discount']) ? formatMoney($dtData['total_discount']) : 0).'</td>
                        </tr>
                    </table>';
                return $htmlReduction;
            })
            ->editColumn('total_vat', function ($dtData) {
                return '<div>'.(!empty($dtData['total_vat']) ? formatMoney($dtData['total_vat']) : 0).'</div>';
            })
            ->editColumn('grand_total', function ($dtData) {
                return '<div>'.(!empty($dtData['grand_total']) ? formatMoney($dtData['grand_total']) : 0).'</div>';
            })
            ->editColumn('total_discount_leader', function ($dtData) {
                return '<div>'.(!empty($dtData['total_discount_leader']) ? formatMoney($dtData['total_discount_leader']) : 0).'</div>';
            })
            ->editColumn('total_discount_customer_f1', function ($dtData) {
                return '<div>'.(!empty($dtData['total_discount_customer_f1']) ? formatMoney($dtData['total_discount_customer_f1']) : 0).'</div>';
            })
            ->addColumn('status_invoice', function ($dtData) {
                $invoice = $dtData['invoice'][0] ?? [];
                if (empty($invoice)) {
                    return '<div class="label label-danger">Chưa tạo hóa đơn</div>';
                } else {
                    if(($invoice['status'] == 1)) {
                        return '<div class="label label-success">Đã phát hành</div>';
                    } elseif (!empty($invoice['status_invoice'])) {
                        return '<div class="label label-primary">Đã xuất nháp</div>';
                    } else {
                        return '<div></div>';
                    }
                }
            })
            ->addColumn('warehouse_status', function ($dtData) {
                $transactionId = $dtData['id'] ?? null;
                if (!$transactionId) return '';
                $wInfo = \Illuminate\Support\Facades\DB::table('tbl_transaction_warehouse as tw')
                    ->leftJoin('tbl_users as u', 'tw.warehouse_approved_by', '=', 'u.id')
                    ->where('tw.transaction_id', $transactionId)
                    ->select('tw.warehouse_status', 'u.name as approver_name')
                    ->first();
                $wStatus = $wInfo->warehouse_status ?? 0;
                if ($wStatus == 1) {
                    $name = $wInfo->approver_name ?? 'N/A';
                    return '<div class="text-center"><span class="label label-success" style="cursor:pointer;" onclick="cancelWarehouseApprove('.$transactionId.')" title="Nhấn để bỏ duyệt kho"><i class="fa fa-check"></i> Đã duyệt</span><div style="font-size: 12px; margin-top: 3px; color: #2d812d; font-weight: bold;">'.$name.'</div></div>';
                }
                return '<div class="text-center"><button class="btn btn-xs btn-warning" onclick="openWarehouseModal('.$transactionId.')"><i class="fa fa-cube"></i> Duyệt kho</button></div>';
            })
            ->rawColumns(['options', 'reference_no', 'date', 'date_start','id','status','customer','grand_total','reduction','total','information_delivery','total_vat','customer_level','status_invoice','total_discount_leader','total_discount_customer_f1','warehouse_status'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function get_detail($id = 0) {
        $this->request->merge(['id' => $id]);
        $arrIdOtherAmenitiesCar = [];
        $arrDate = [];
        if (empty($id)){
            if (!has_permission('service', 'add')) {
                access_denied(true, lang('Không có quyền thêm'));
            }
            $title = lang('dt_add_service');
        } else {
            if (!has_permission('service', 'edit')) {
                access_denied(true, lang('Không có quyền sửa'));
            }
            $title = lang('dt_edit_service');
            $response = $this->fnbService->getDetail($this->request);
            $data = $response->getData(true);
            $dtData = $data['dtData'] ?? [];
            $this->request->merge(['customer_id' => [$dtData['customer_id'] ?? [0]]]);
            $responseCustomer = $this->fnbCustomerService->getListData($this->request);
            $dataCustomer = $responseCustomer->getData(true);
            $customers = collect($dataCustomer['data']);
            $dtData['customer'] = $customers->where('id','=',$dtData['customer_id'])->first();
            if (!empty($dtData['other_amenities'])) {
                foreach ($dtData['other_amenities'] as $key => $value) {
                    $arrIdOtherAmenitiesCar[] = $value['id'];
                }
            }

            if (!empty($dtData['day'])) {
                foreach ($dtData['day'] as $key => $value) {
                    $arrDate[] = $value['day'];
                }
            }
        }
        $titleService = lang('dt_service_list');
        $dtCategoryService = [];
        $dtProvince = [];
        $dtWard = [];
        $otherAmenities = $this->fnbOtherAmenitisService->getListData($this->request)->getData(true)['data'] ?? [];

        // Lấy thông tin xuất kho
        $warehouseInfo = \Illuminate\Support\Facades\DB::table('tbl_transaction_warehouse as tw')
            ->leftJoin('tbl_users as u', 'tw.warehouse_approved_by', '=', 'u.id')
            ->where('tw.transaction_id', $id)
            ->select('tw.warehouse_status', 'u.name as approver_name', 'tw.warehouse_approved_at')
            ->first();
       
        return view('admin.service.detail',[
            'id' => $id,
            'title' => $title,
            'titleService' => $titleService,
            'dtData' => $dtData ?? [],
            'dtCategoryService' => $dtCategoryService,
            'dtProvince' => $dtProvince,
            'dtWard'=> $dtWard,
            'otherAmenities' => $otherAmenities,
            'arrIdOtherAmenitiesCar' => $arrIdOtherAmenitiesCar,
            'arrDate' => $arrDate,
            'warehouseStatus' => $warehouseInfo->warehouse_status ?? 0,
            'approverName' => $warehouseInfo->approver_name ?? null,
            'approvedAt' => $warehouseInfo->warehouse_approved_at ?? null,
        ]);
    }

    public function detail($id = 0) {
        $this->request->merge(['id' => $id]);
        $price = !empty($this->request->input('price')) ? number_unformat($this->request->input('price')) : 0;
        $this->request->merge(['price' => $price]);
        $response = $this->fnbService->detail($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        if (!has_permission('transaction', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $is_admin = is_admin();
        $this->request->merge(['is_admin' => $is_admin]);
        $this->request->merge(['id' => $id]);
        // Bỏ duyệt kho nếu có
        if($is_admin == 0) {
            $this->request->merge(['check_detail_transaction' => 1]);
            $response = $this->transactionService->getListDetailTransaction($this->request);
            $dataTransaction = $response->getData(true);
            $dataTransaction = $dataTransaction['data']['data'] ?? [];
            if($dataTransaction['warehouse_status'] > 0) {
                $data['result'] = false;
                $data['message'] = 'Đơn hàng đã có xuất kho không thể xóa! Vui lòng bỏ duyệt kho trước';
                return response()->json($data);
            }
        } else {
            $this->_revertWarehouseStock($id);
        }

        $response = $this->transactionService->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function countAll(){
        $response = $this->transactionService->countAll($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function view($id = 0){
        if (!has_permission('transaction','view') && !has_permission('transaction', 'viewown')) {
            access_denied(true, lang('dt_access'));
        }
        $title = lang('dt_view_transaction');
        $this->request->merge(['id' => $id]);
        $response = $this->transactionService->getListDetailTransaction($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtDataArray = $data['data']['data'] ?? [];

        // Lấy thông tin xuất kho
        $warehouseStatus = \Illuminate\Support\Facades\DB::table('tbl_transaction_warehouse')
            ->where('transaction_id', $id)
            ->value('warehouse_status');

        $warehouseLogs = [];
        if ($warehouseStatus == 1) {
            $logs = \Illuminate\Support\Facades\DB::table('tbl_transaction_warehouse_details as twd')
                ->leftJoin('tbl_warehouse_import_details as wid', 'twd.detail_id', '=', 'wid.id')
                ->leftJoin('tbl_warehouse_imports as wi', 'wid.id_import', '=', 'wi.id')
                ->where('twd.transaction_id', $id)
                ->select(
                    'twd.id_product',
                    'twd.qty_take',
                    'wi.import_code',
                    'wi.import_date'
                )
                ->get();

            foreach ($logs as $log) {
                if (!isset($warehouseLogs[$log->id_product])) {
                    $warehouseLogs[$log->id_product] = [];
                }
                $warehouseLogs[$log->id_product][] = $log;
            }
        }
        $warehouseInfo = \Illuminate\Support\Facades\DB::table('tbl_transaction_warehouse as tw')
            ->leftJoin('tbl_users as u', 'tw.warehouse_approved_by', '=', 'u.id')
            ->where('tw.transaction_id', $id)
            ->select('tw.warehouse_status', 'u.name as approver_name', 'tw.warehouse_approved_at')
            ->first();
        return view('admin.transaction.view',[
            'title' => $title,
            'dtData' => $dtDataArray,
            'warehouseStatus' => $warehouseStatus,
            'warehouseLogs' => $warehouseLogs,
            'approverName' => $warehouseInfo->approver_name ?? null,
            'approvedAt' => $warehouseInfo->warehouse_approved_at ?? null,
        ]);
    }

    public function changeStatus(){
        if (!has_permission('transaction','approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $transaction_id = $this->request->input('transaction_id') ?? 0;
        $status = $this->request->input('status') ?? 0;
        $this->request->merge(['status' => $status]);
        $this->request->merge(['staff_status' => Config::get('constant')['user_admin']]);
        $this->request->merge(['transaction_id' => $transaction_id]);
        $responseUpdate =  $this->transactionService->changeStatus($this->request);
        $dtUpdate = $responseUpdate->getData(true);
        $data = $dtUpdate['data'] ?? [];

        // Nếu chuyển sang trạng thái Hủy thì tự động bỏ duyệt kho
        if ($data['result'] == true && $status == \Config::get('constant')['status_cancel']) {
            $this->_revertWarehouseStock($transaction_id);
        }

        return response()->json($data);
    }

    /**
     * Lấy danh sách sản phẩm của đơn hàng + trạng thái kho để hiển thị modal duyệt kho
     */
    public function getOrderItemsForWarehouse($id)
    {
        // Lấy thông tin transaction từ service (external API)
        $this->request->merge(['id' => $id]);
        $response = $this->transactionService->getListDetailTransaction($this->request);
        $data = $response->getData(true);
        
        if (empty($data['result'])) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy đơn hàng']);
        }

        // Parse đúng structure: data.data.items[]
        $orderData = $data['data']['data'] ?? [];
        $rawItems  = $orderData['items'] ?? [];

        // Nếu API không trả về items thì báo lỗi
        if (empty($rawItems)) {
            return response()->json(['result' => false, 'message' => 'Đơn hàng không có sản phẩm hoặc không đọc được chi tiết.']);
        }

        // Chuẩn hoá thành flat array có đủ id_product, product_name, product_code, quantity
        $details = [];
        foreach ($rawItems as $raw) {
            $product = $raw['product'] ?? [];
            if (empty($product['id'])) continue;
            $details[] = [
                'id_product'   => $product['id'],
                'product_name' => $product['name'] ?? '',
                'product_code' => $product['code'] ?? '',
                'quantity'     => $raw['quantity'] ?? 1,
                'price'        => $raw['price'] ?? 0,
            ];
        }

        // Với mỗi sản phẩm, lấy danh sách lô kho còn tồn (FIFO: cũ trước)
        $result = [];
        foreach ($details as $item) {
            $item = (array) $item;
            $idProduct = $item['id_product'] ?? null;
            if (!$idProduct) continue;

            // Tổng tồn kho
            $stock = DB::table('tbl_warehouse_stock')->where('id_product', $idProduct)->first();
            $totalStock = $stock ? $stock->quantity : 0;

            // Lấy các lô còn hàng, sắp xếp FIFO (import_date cũ nhất trước)
            $lots = DB::table('tbl_warehouse_import_details as d')
                ->join('tbl_warehouse_imports as i', 'd.id_import', '=', 'i.id')
                ->where('d.id_product', $idProduct)
                ->where('i.status', 1)
                ->where('d.remaining_qty', '>', 0)
                ->select(
                    'd.id as detail_id',
                    'i.id as import_id',
                    'i.import_code',
                    'i.import_date',
                    'i.supplier_name',
                    'd.quantity as import_qty',
                    DB::raw('COALESCE(d.remaining_qty, 0) as remaining_qty')
                )
                ->orderBy('i.import_date', 'asc') // FIFO
                ->get();

            // Tự động phân bổ số lượng cần lấy theo FIFO
            $qtyNeeded  = intval($item['quantity'] ?? 1);
            $allocation = [];
            $remaining  = $qtyNeeded;
            foreach ($lots as $lot) {
                if ($remaining <= 0) break;
                $take = min($remaining, $lot->remaining_qty);
                $allocation[] = [
                    'detail_id'    => $lot->detail_id,
                    'import_id'    => $lot->import_id,
                    'import_code'  => $lot->import_code,
                    'import_date'  => $lot->import_date,
                    'supplier_name'=> $lot->supplier_name,
                    'remaining_qty'=> $lot->remaining_qty,
                    'suggested_qty'=> $take,
                ];
                $remaining -= $take;
            }

            $result[] = [
                'id_product'   => $idProduct,
                'product_name' => $item['product_name'] ?? ($item['name'] ?? ''),
                'product_code' => $item['product_code'] ?? ($item['code'] ?? ''),
                'qty_ordered'  => $qtyNeeded,
                'total_stock'  => $totalStock,
                'enough_stock' => ($totalStock >= $qtyNeeded),
                'lots'         => $lots,
                'allocation'   => $allocation,
                'unallocated'  => $remaining, // số lượng chưa đủ kho
            ];
        }

        $warehouseInfo = \Illuminate\Support\Facades\DB::table('tbl_transaction_warehouse as tw')
            ->leftJoin('tbl_users as u', 'tw.warehouse_approved_by', '=', 'u.id')
            ->where('tw.transaction_id', $id)
            ->select('tw.warehouse_status', 'u.name as approver_name', 'tw.warehouse_approved_at')
            ->first();

        $cancelStatus = \Config::get('constant')['status_cancel'];
        $orderStatus = $orderData['status'] ?? null;
        $isStatusId = is_array($orderStatus) ? ($orderStatus['id'] ?? null) : $orderStatus;
        $isCancelled = ($isStatusId == $cancelStatus);

        return response()->json([
            'result'           => true,
            'items'            => $result,
            'warehouse_status' => $warehouseInfo->warehouse_status ?? 0,
            'approver_name'    => $warehouseInfo->approver_name ?? null,
            'approved_at'      => $warehouseInfo->warehouse_approved_at ?? null,
            'is_cancelled'     => $isCancelled,
        ]);
    }

    /**
     * Duyệt kho cho 1 đơn hàng: trừ remaining_qty từng lô theo selection của user
     */
    public function approveWarehouse($id)
    {
        if (!has_permission('transaction', 'approve')) {
            return response()->json(['result' => false, 'message' => lang('dt_access')]);
        }

        // allocation: array [ { detail_id, qty_take } ]
        $allocation = $this->request->input('allocation', []);

        if (empty($allocation)) {
            return response()->json(['result' => false, 'message' => 'Chưa có thông tin phân bổ kho']);
        }

        // --- VALIDATION SỐ LƯỢNG ---
        // Lấy lại chi tiết đơn hàng
        $this->request->merge(['id' => $id]);
        $response = $this->transactionService->getListDetailTransaction($this->request);
        $data = $response->getData(true);
        $orderData = $data['data']['data'] ?? [];
        $rawItems  = $orderData['items'] ?? [];

        // Không cho duyệt kho đơn đã hủy
        $orderStatus = $orderData['status'] ?? null;
        $cancelStatus = \Config::get('constant')['status_cancel'];
        if (!empty($orderStatus) && ($orderStatus == $cancelStatus || (is_array($orderStatus) && ($orderStatus['id'] ?? null) == $cancelStatus))) {
            return response()->json(['result' => false, 'message' => 'Đơn hàng đã bị hủy, không thể duyệt kho!']);
        }

        // Tính tổng lượng đã nhập theo từng id_product
        $allocatedByProduct = [];
        // Mảng tạm chứa details để tí nữa update đỡ phải query lại (tuỳ chọn, nhưng hiện tại query lại ở dưới cũng không sao)
        
        foreach ($allocation as $item) {
            $detailId = intval($item['detail_id'] ?? 0);
            $qtyTake  = intval($item['qty_take'] ?? 0);
            if ($detailId <= 0 || $qtyTake <= 0) continue;
            
            $detail = DB::table('tbl_warehouse_import_details')->where('id', $detailId)->first();
            if ($detail) {
                if (!isset($allocatedByProduct[$detail->id_product])) {
                    $allocatedByProduct[$detail->id_product] = 0;
                }
                $allocatedByProduct[$detail->id_product] += $qtyTake;
            }
        }

        // Kiểm tra xem phân bổ có đủ so với yêu cầu đơn hàng không
        foreach ($rawItems as $raw) {
            $product = $raw['product'] ?? [];
            if (empty($product['id'])) continue;
            
            $productId = $product['id'];
            $qtyNeeded = intval($raw['quantity'] ?? 1);
            $qtyAllocated = $allocatedByProduct[$productId] ?? 0;
            
            if ($qtyAllocated != $qtyNeeded) {
                return response()->json([
                    'result' => false, 
                    'message' => 'Sản phẩm [' . ($product['name'] ?? $productId) . '] cần xuất ' . $qtyNeeded . ' nhưng bạn đang phân bổ ' . $qtyAllocated . '. Vui lòng kiểm tra lại.'
                ]);
            }
        }
        // --- END VALIDATION ---

        DB::beginTransaction();
        try {
            foreach ($allocation as $item) {
                $detailId = intval($item['detail_id'] ?? 0);
                $qtyTake  = intval($item['qty_take'] ?? 0);
                if ($detailId <= 0 || $qtyTake <= 0) continue;

                $detail = DB::table('tbl_warehouse_import_details')->where('id', $detailId)->first();
                if (!$detail) continue;

                $newRemaining = max(0, $detail->remaining_qty - $qtyTake);
                DB::table('tbl_warehouse_import_details')->where('id', $detailId)->update([
                    'remaining_qty' => $newRemaining,
                    'updated_at'    => now(),
                ]);

                // Trừ tổng tồn kho
                DB::table('tbl_warehouse_stock')
                    ->where('id_product', $detail->id_product)
                    ->decrement('quantity', $qtyTake);
                    
                // Ghi log chi tiết để sau này có thể huỷ
                DB::table('tbl_transaction_warehouse_details')->insert([
                    'transaction_id' => $id,
                    'id_product'     => $detail->id_product,
                    'detail_id'      => $detailId,
                    'qty_take'       => $qtyTake,
                    'created_at'     => now(),
                ]);
            }

            // Đánh dấu đơn hàng đã duyệt kho (upsert vào bảng local)
            DB::table('tbl_transaction_warehouse')->updateOrInsert(
                ['transaction_id' => $id],
                [
                    'warehouse_status'      => 1,
                    'warehouse_approved_at' => now(),
                    'warehouse_approved_by' => auth()->guard('admin')->id(),
                    'updated_at'            => now(),
                ]
            );

            DB::commit();

            // Sync warehouse_status = 1 lên tbl_transaction (accounts service)
            $this->request->merge(['transaction_id' => $id, 'warehouse_status' => 1,'warehouse_approved_at' => now(),'warehouse_approved_by' => auth()->guard('admin')->id()]);
            $this->transactionService->changeWarehouseStatus($this->request);

            return response()->json(['result' => true, 'message' => 'Duyệt kho thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Bỏ duyệt kho đơn hàng: Trả lại tồn kho lô và tổng tồn kho
     */
    public function cancelWarehouseApprove($id)
    {
        if (!has_permission('transaction', 'approve')) {
            return response()->json(['result' => false, 'message' => lang('dt_access')]);
        }

        DB::beginTransaction();
        try {
            $reverted = $this->_revertWarehouseStock($id);
            if ($reverted !== true) {
                 return response()->json(['result' => false, 'message' => $reverted]);
            }

            DB::commit();
            return response()->json(['result' => true, 'message' => 'Bỏ duyệt kho thành công! Đã hoàn trả tồn kho.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Helper revert kho
     */
    private function _revertWarehouseStock($id)
    {
        $warehouseRecord = DB::table('tbl_transaction_warehouse')->where('transaction_id', $id)->first();
        if (!$warehouseRecord || $warehouseRecord->warehouse_status != 1) {
            return true; // Không cần revert
        }

        // Không dùng transaction ở đây vì có thể đang nằm trong transaction cha (delete/cancel)
        // Lấy danh sách các lô đã bị trừ
        $deductedLots = DB::table('tbl_transaction_warehouse_details')->where('transaction_id', $id)->get();
        
        foreach ($deductedLots as $log) {
            // Trả lại 'remaining_qty' cho lô
            DB::table('tbl_warehouse_import_details')
                ->where('id', $log->detail_id)
                ->increment('remaining_qty', $log->qty_take);

            // Trả lại tồn chung
            DB::table('tbl_warehouse_stock')
                ->where('id_product', $log->id_product)
                ->increment('quantity', $log->qty_take);
        }

        // Xóa log
        DB::table('tbl_transaction_warehouse_details')->where('transaction_id', $id)->delete();

        // Cập nhật trạng thái
        DB::table('tbl_transaction_warehouse')->where('transaction_id', $id)->update([
            'warehouse_status' => 0,
            'warehouse_approved_at' => null,
            'warehouse_approved_by' => null,
            'updated_at' => now(),
        ]);

        // Sync warehouse_status = 0 lên tbl_transaction (accounts service)
        $this->request->merge(['transaction_id' => $id, 'warehouse_status' => 0,'warehouse_approved_at' => null,'warehouse_approved_by' => null]);
        $this->transactionService->changeWarehouseStatus($this->request);

        return true;
    }

    public function detailTransaction($id){
        if (!has_permission('transaction', 'add')) {
            access_denied(true);
        }
        return view('admin.transaction.detail',[
            'id' => $id,
            'title' => lang('Thêm mới đơn hàng'),
            'vat' => get_option('vat') ?? 0,
        ]);
    }

    public function submitTransaction($id = 0){
        $this->request->merge(['admin' => 1]);
        $this->request->merge(['id' => $id]);
        $this->request->merge(['name_delivery' => $this->request->input('name_delivery') ?? 'Đức Thuận']);
        $this->request->merge(['phone_delivery' => $this->request->input('phone_delivery') ?? '0772818495']);
        $this->request->merge(['email_delivery' => $this->request->input('email_delivery') ?? 'thuan@gmail.com']);
        $this->request->merge(['address_delivery' => $this->request->input('address_delivery') ?? '69/1/3 Nguyễn Gia Trí. Bình Thạnh']);
       
        $product_id = $this->request->input('product_id') ?? [];
        $items = [];
        if(!empty($product_id)){
            foreach($product_id as $key => $item){
                $variant_id = $this->request->input('variant_id')[$key] ?? 0;
                $product_id_variant = $this->request->input('product_id_variant')[$key] ?? 0;
                $quantity = number_unformat($this->request->input('quantity')[$key]) ?? 0;
                $price = number_unformat($this->request->input('price')[$key]) ?? 0;
                if($product_id_variant > 0){
                    if(empty($variant_id)){
                        return response()->json(['result' => false, 'message' => 'Vui lòng chọn biến thể sản phẩm']);
                    }
                }
                $items[$key] = [
                    'product_id' => $item,
                    'variant_id' => $variant_id,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }
        }
        $this->request->merge(['items' => $items]);
        unset($this->request['product_id']);
        unset($this->request['variant_id']);
        unset($this->request['quantity']);
        unset($this->request['price']);
        $response = $this->transactionService->addTransaction($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function getDiscountCustomer()
    {
        $this->request->merge(['customer_id' => $this->request->input('customer_id')]);
        $response = $this->transactionService->getDiscountCustomer($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }
}
