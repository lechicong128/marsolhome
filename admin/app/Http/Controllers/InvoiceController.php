<?php

namespace App\Http\Controllers;


use App\Services\InvoiceService;
use App\Services\AccountService;
use App\Services\TransactionService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class InvoiceController extends Controller
{
    protected $customerService;
    protected $invoiceService;
    protected $accountService;
    protected $transactionService;
    use UploadFile;
    public function __construct(Request $request,InvoiceService $invoiceService, AccountService $accountService, TransactionService $transactionService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->invoiceService = $invoiceService;
        $this->accountService = $accountService;
        $this->transactionService = $transactionService;
    }

    public function waiting_invoice(){
        if (!has_permission('waiting_invoice','view')) {
            access_denied();
        }
        $title = lang('Chờ xuất hóa đơn');
        return view('admin.invoice.waiting_invoice',[
            'title' => $title
        ]);
    }

    public function getListWaitingInvoice()
    {
        if (!has_permission('waiting_invoice', 'view')) {
            $data['result'] = false;
            $data['message'] = lang('Không có quyền xem!');
            $data['data'] = [];
            return response()->json($data);
        }
        $response = $this->invoiceService->getListWaitingInvoice($this->request);
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
            ->addColumn('reference_no', function ($dtData){
                $id = $dtData['id'];
                return "<a class='dt-modal' href='admin/transaction/view/$id'>".$dtData['reference_no']."</a>";
            })
            ->editColumn('date', function ($dtData) {
                return '<div>'.(!empty($dtData['date']) ? _dt($dtData['date']) : '').'</div>';
            })
            ->addColumn('customer', function ($dtData) {
                $customer = $dtData['customer'] ?? [];
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($customer['fullname']) ? $customer['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($customer['phone']) ? $customer['phone'] : 'Chưa có sdt').'</div>';
            })
            ->editColumn('total_net', function ($dtData) {
                return '<div>'.(!empty($dtData['total_amount_vat']) ? formatMoney($dtData['total_amount_vat']) : 0).'</div>';
            })
            ->editColumn('total_vat', function ($dtData) {
                return '<div>'.(!empty($dtData['total_vat']) ? formatMoney($dtData['total_vat']) : 0).'</div>';
            })
            ->editColumn('total', function ($dtData) {
                return '<div>'.(!empty($dtData['grand_total']) ? formatMoney($dtData['grand_total']) : 0).'</div>';
            })
            ->addColumn('status_invoice', function ($dtData) {
                $optionStatus = '<div class="btn-group">
                <button type="button" class="btn btn-white dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false" style="min-width: 150px;border: 1px solid red!important">
                <div class="label" style="color: red">Chờ duyệt</div>
                <span class="caret"></span> </button>
                <ul class="dropdown-menu">';
                $optionStatus .= '<li style="cursor: pointer""><a onclick="changeStatusInvoice('.$dtData['id'].',1)" data-id="1">Duyệt xuất nháp</a></li>';
                $optionStatus .= '</ul></div>';
                return $optionStatus;
            })
            ->rawColumns(['reference_no', 'date', 'customer','total_net','total_vat','total','id','status_invoice'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function detailInvoice()
    {
        if (!has_permission('waiting_invoice', 'add')) {
            access_denied(true);
        }
        $transaction_id = $this->request->input('invoice_id') ?? 0;
        $transaction = [];
        if(!empty($transaction_id)){
            $this->request->merge(['id' => $transaction_id]);
            $responseTransaction = $this->transactionService->getListDetailTransaction($this->request);
            $dataTransaction = $responseTransaction->getData(true);
            $transaction = $dataTransaction['data']['data'] ?? [];
        }
        if (!has_permission('waiting_invoice', 'add')) {
            $data['result'] = false;
            $data['message'] = lang('Không có quyền thêm!');
            return response()->json($data);
        }
        return view('admin.invoice.detail_invoice',[
            'invoice_id' => $transaction_id,
            'title' => lang('Tạo hóa đơn'),
            'transaction' => $transaction ?? [],
        ]);
    }

    public function submitDetailInvoice()
    {
        $response = $this->invoiceService->submitDetailInvoice($this->request);
        $dataRes = $response->getData(true);
        return response()->json($dataRes);
    }

    public function listInvoice()
    {
        if (!has_permission('invoice', 'view')) {
            access_denied();
        }
        $title = lang('Hóa đơn');
        return view('admin.invoice.list',[
            'title' => $title
        ]);
    }

    public function getListInvoice()
    {
        if (!has_permission('invoice', 'view')) {
            $data['result'] = false;
            $data['message'] = lang('Không có quyền xem!');
            $data['data'] = [];
            return response()->json($data);
        }
        $response = $this->invoiceService->getListInvoice($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);

        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
        ->addColumn('options', function ($dtData) {
            $id = $dtData['id'];
            $view = "<a href='admin/invoice/view/$id' class='dt-modal'><i class='fa fa-eye'></i> " . lang('Xem hóa đơn hệ thống') . "</a>";
            $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/invoice/deleteInvoice/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('Xóa hóa đơn') . '</a>';
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
                return "<div><a class='dt-modal' href='admin/invoice/view/$id'>".$dtData['reference_no']."</a></div>";
            })
            ->addColumn('reference_no_bill', function ($dtData){
                return '<div>'.(!empty($dtData['reference_no_bill']) ? $dtData['reference_no_bill'] : '').'</div>';
            })
            ->addColumn('reference_no_transaction', function ($dtData){
                $transaction = $dtData['transaction'] ?? [];
                if(!empty($transaction)){
                    $reference_no_transaction = '';
                    foreach($transaction as $item){
                        $reference_no = $item['reference_no'];
                        $reference_no_transaction .= '<div><a class="dt-modal" href="admin/transaction/view/'.$item['id'].'">'.$reference_no.'</a></div>';
                    }
                    return $reference_no_transaction;
                }
                return '<div></div>';
            })
            ->editColumn('date', function ($dtData) {
                return '<div>'.(!empty($dtData['date']) ? _dthuan($dtData['date']) : '').'</div>';
            })
            ->addColumn('customer', function ($dtData) {
                $customer = $dtData['customer'] ?? [];
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($customer['fullname']) ? $customer['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($customer['phone']) ? $customer['phone'] : 'Chưa có sdt').'</div>';
            })
            ->editColumn('total_net', function ($dtData) {
                return '<div>'.(!empty($dtData['total_net']) ? formatMoney($dtData['total_net']) : 0).'</div>';
            })
            ->editColumn('total_vat', function ($dtData) {
                return '<div>'.(!empty($dtData['total_tax']) ? formatMoney($dtData['total_tax']) : 0).'</div>';
            })
            ->editColumn('total', function ($dtData) {
                return '<div>'.(!empty($dtData['grand_total']) ? formatMoney($dtData['grand_total']) : 0).'</div>';
            })
            ->addColumn('status', function ($dtData) {
                if($dtData['status'] == 1){
                    return '<div class="label label-success">Đã phát hành</div>';
                } else {
                    return '<div class="label label-danger">Chưa phát hành</div>';
                }
            })
            ->addColumn('status_invoice', function ($dtData) {
                if($dtData['status_invoice'] == 1){
                    return '<div class="label label-success"><a style="cursor: pointer;color: white;" onclick="viewInvoice('.$dtData['id'].')">Đã tạo hóa đơn nháp</a></div>';
                } else {
                    return '<div class="label label-danger">Chưa tạo hóa đơn nháp</div>';
                }
            })
            ->rawColumns(['reference_no','reference_no_transaction','reference_no_bill', 'date', 'customer','total_net','total_vat','total','id','status','status_invoice','options'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function viewInvoice()
    {
        $invoice_id = $this->request->input('invoice_id') ?? 0;
        $response = $this->invoiceService->viewInvoice($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        return response()->json($data);
    }

    public function deleteInvoice($id)
    {
        if (!has_permission('invoice', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('Không có quyền xóa!');
            return response()->json($data);
        }   
        $this->request->merge(['id' => $id]);
        $response = $this->invoiceService->deleteInvoice($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function view($id){
        if (!has_permission('invoice', 'view')) {
           access_denied(true);
        }   
        $title = lang('Xem hóa đơn hệ thống');
        $this->request->merge(['id' => $id]);
        $response = $this->invoiceService->getDetailInvoice($this->request);
        $data = $response->getData(true);
        $dtData = $data['data'] ?? [];
        return view('admin.invoice.view',[
            'title' => $title,
            'id' => $id,
            'dtData' => $dtData,
        ]);
    }
}