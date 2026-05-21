<?php

namespace App\Http\Controllers;


use App\Models\PaymentMode;
use App\Traits\UploadFile;
use App\Services\BonusPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class BonusPaymentController extends Controller
{
    protected $bonusPaymentService;
    use UploadFile;
    public function __construct(Request $request, BonusPaymentService $bonusPaymentService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->bonusPaymentService = $bonusPaymentService;
    }

    public function get_list()
    {
        if (!has_permission('bonus_payment', 'view') && !has_permission('bonus_payment', 'viewown')) {
            access_denied();
        }
        $title = lang('dt_bonus_payment');
        return view('admin.bonus_payment.list', [
            'title' => $title
        ]);
    }

    public function getList()
    {
        if (!has_permission('bonus_payment', 'view') && !has_permission('bonus_payment', 'viewown')) {
            access_denied();
        }
        $response = $this->bonusPaymentService->getList($this->request);
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
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/bonus_payment/delete/' . $id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_bonus_payment') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
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
            ->editColumn('total', function ($dtData) {
                return '<div class="text-right">' . (!empty($dtData['total']) ? formatMoney($dtData['total']) : 0) . '</div>';
            })
            ->editColumn('payment_mode_id', function ($dtData) use ($dataPayment) {
                return '<div class="text-center">' . (!empty($dtData['payment_mode_id']) ? $dataPayment[$dtData['payment_mode_id']]['name'] : '') . '</div>';
            })
            ->editColumn('status', function ($dtData) {
                $status = $dtData['status'] ?? null;
                $label = '';
                $intStatus = is_numeric($status) ? (int) $status : null;
                if ($intStatus === 0) {
                    $label = '<div class="dt-update label label-warning" data-type="payment" style="cursor: pointer;" href="admin/bonus_payment/changeStatus/'.$dtData['id'].'">'.lang('Chưa chi').'</div>';
                } elseif ($intStatus === 1) {
                    $label = '<span class="label label-success">'.lang('Đã chi').'</span>';

                }
                $dateHtml = !empty($dtData['date_status']) ? '<div>' . _dt($dtData['date_status']) . '</div>' : '';

                return '<div class="text-center">' . $label . $dateHtml.'</div>';
            })
            ->editColumn('note', function ($dtData) {
                return '<div class="text-left">' . $dtData['note'] . '</div>';
            })
            ->rawColumns(['options', 'reference_no', 'date', 'id', 'customer', 'payment_mode_id', 'total','status','note'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->addIndexColumn()
            ->skipPaging()
            ->make(true);
    }

    public function countAll()
    {
        $response = $this->bonusPaymentService->countAll($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function get_detail($id = 0)
    {
        if(!has_permission('bonus_payment', 'add')) {
            access_denied(true, lang('dt_access'));
        }
        $title = lang('dt_add_bonus_payment');
        $dtPaymentMode = PaymentMode::where('active',1)->get();
        $reference_no = getReference('pay_slip');
        return view('admin.bonus_payment.detail', [
            'title' => $title,
            'id' => $id,
            'dtPaymentMode' => $dtPaymentMode,
            'reference_no' => $reference_no
        ]);
    }

    public function submit($id = 0)
    {
        $total = number_unformat($this->request->input('total',0));
        $this->request->merge(['id' => $id]);
        $this->request->merge(['total' => $total]);
        $response = $this->bonusPaymentService->detail($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = '')
    {
        if(!has_permission('bonus_payment', 'delete')) {
            access_denied(true, lang('dt_access'));
        }
        $this->request->merge(['id' => $id]);
        $response = $this->bonusPaymentService->delete($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function changeStatus($id)
    {
        if (!has_permission('bonus_payment','approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $bonus_payment_id = $id;
        $this->request->merge(['staff_status' => Config::get('constant')['user_admin']]);
        $this->request->merge(['bonus_payment_id' => $bonus_payment_id]);
        $responseUpdate =  $this->bonusPaymentService->changeStatus($this->request);
        $dtUpdate = $responseUpdate->getData(true);
        $data = $dtUpdate['data'] ?? [];
        return response()->json($data);
    }
}
