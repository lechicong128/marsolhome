<?php

namespace App\Http\Controllers;


use App\Models\PaymentMode;
use App\Traits\UploadFile;
use App\Services\TransactionPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class TransactionPaymentController extends Controller
{
    protected $customerService;
    protected $TransactionPaymentService;
    use UploadFile;
    public function __construct(Request $request, TransactionPaymentService $TransactionPaymentService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->TransactionPaymentService = $TransactionPaymentService;
    }

    public function get_list()
    {
        if (!has_permission('transaction_payment', 'view') && !has_permission('transaction_payment', 'viewown')) {
            access_denied();
        }
        $title = lang('c_transaction_payment');
        return view('admin.transaction_payment.list', [
            'title' => $title
        ]);
    }

    public function getList()
    {
        if (!has_permission('transaction_payment', 'view') && !has_permission('transaction_payment', 'viewown')) {
            access_denied();
        }
        $response = $this->TransactionPaymentService->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));

        $listPaymentModes = PaymentMode::get();
        $dataPayment = [];
        foreach($listPaymentModes as $key => $value){
            $dataPayment[$value['id']] = $value;
        }
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $view = "<a href='admin/transaction_payment/view/$id' class='dt-modal'><i class='fa fa-eye'></i> " . lang('dt_view_transaction_payment') . "</a>";
                $view = '';
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/transaction_payment/delete/' . $id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_transaction_payment') . '</a>';
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
                return '<div>' . (++$start) . '</div>';
            })
            ->editColumn('reference_no', function ($dtData) {
                $id = $dtData['id'];
                return $dtData['reference_no'];
            })
            ->editColumn('date', function ($dtData) {
                return '<div class="text-center">' . (!empty($dtData['date']) ? _dt($dtData['date']) : '') . '</div>';
            })
            ->editColumn('status', function ($dtData) {
                $status = $dtData['status'] ?? null;
                $label = '';
                $intStatus = is_numeric($status) ? (int) $status : null;
                if ($intStatus === 0) {
                    $label = '<div class="dt-update label label-warning" data-type="payment" style="cursor: pointer;" href="admin/transaction_payment/changeStatus/'.$dtData['id'].'">'.lang('status_not_payment').'</div>';
                } elseif ($intStatus === 1) {
                    $label = '<span class="label label-success">'.lang('payment_success').'</span>';

                } elseif ($intStatus === 2) {
                    $label = '<span class="label label-danger">'.lang('payment_not_enough').'</span>';

                }
                $dateHtml = !empty($dtData['date_status']) ? '<div>' . _dt($dtData['date_status']) . '</div>' : '';

                return '<div class="text-center">' . $label . $dateHtml.'</div>';
            })
            ->addColumn('customer', function ($dtData) {
                $customer = $dtData['customer'] ?? [];
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';" style="width:35px;height:35px;" src="' . $url . '" alt="product-img" />
                        </div>
                        <div>
                            <strong>' . (!empty($customer['fullname']) ? $customer['fullname'] : ''). '</strong>
                            <br><small>'. (!empty($customer['phone']) ? $customer['phone'] : '') .'</small>
                        </div>
                    </div>';
            })
            ->addColumn('transaction', function ($dtData) {
                $transaction = $dtData['transaction'] ?? [];
                if(!empty($transaction)) {
                    $TransactionView = '<a class="dt-modal" href="admin/transaction/view/' . $transaction['id'] . '">' . $transaction['reference_no'] . '</a>';
                    return '<div class="text-center">' . $TransactionView . '</div>';
                }
                return '';
            })
            ->editColumn('amount', function ($dtData) {
                return '<div class="text-right">' . (!empty($dtData['amount']) ? formatMoney($dtData['amount']) : 0) . '</div>';
            })
            ->editColumn('amount_payment', function ($dtData) {
                return '<div class="text-right">' . (!empty($dtData['amount_payment']) ? formatMoney($dtData['amount_payment']) : 0) . '</div>';
            })
            ->editColumn('payment_mode_id', function ($dtData) use ($dataPayment) {
                return '<div class="text-center">' . (!empty($dtData['payment_mode_id']) ? $dataPayment[$dtData['payment_mode_id']]['name'] : '') . '</div>';
            })
            ->rawColumns(['options', 'reference_no', 'date', 'id', 'status', 'customer', 'amount', 'amount_payment', 'payment_mode_id', 'transaction'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->addIndexColumn()
            ->skipPaging()
            ->make(true);
    }

    public function view($id = 0)
    {
        if (!has_permission('transaction_payment', 'view') && !has_permission('transaction_payment', 'viewown')) {
            access_denied(true, lang('dt_access'));
        }
        $title = lang('dt_view_transaction_payment');
        $this->request->merge(['id' => $id]);
        $response = $this->challengeMeService->getListDetailChallengeMe($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        return view('admin.transaction_payment.view', [
            'title' => $title,
            'dtData' => $dtData,
        ]);
    }

    public function countAll()
    {
        $response = $this->TransactionPaymentService->countAll($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function delete($id = '')
    {
        if(!has_permission('transaction_payment', 'delete')) {
            access_denied(true, lang('dt_access'));
        }
        $is_admin = is_admin();
        $this->request->merge(['id' => $id]);
        $this->request->merge(['is_admin' => $is_admin]);
        $response = $this->TransactionPaymentService->delete($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function changeStatus($id)
    {
        if (!has_permission('transaction_payment','approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $payment_id = $id;
        $this->request->merge(['staff_status' => Config::get('constant')['user_admin']]);
        $this->request->merge(['payment_id' => $payment_id]);
        $responseUpdate =  $this->TransactionPaymentService->changeStatus($this->request);
        $dtUpdate = $responseUpdate->getData(true);
        $data = $dtUpdate['data'] ?? [];
        return response()->json($data);
    }
}
