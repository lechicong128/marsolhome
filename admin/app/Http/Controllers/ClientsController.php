<?php

namespace App\Http\Controllers;

use App\Models\BusinessPercent;
use App\Models\ClientBusiness;
use App\Models\ClientBusinessImage;
use App\Models\ClientsReview;
use App\Models\CompanyCar;
use App\Models\DiscountApp;
use App\Models\DrivingLiscense;
use App\Models\GroupPermission;
//use App\Models\Permission;
//use App\Models\Department;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\TypeCar;
use App\Models\User;
use App\Models\Clients;
use App\Traits\UploadFile;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Helpers\FilesHelpers;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\AccountService;

class ClientsController extends Controller
{
    protected $dbAccount;
    use UploadFile;
    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->dbAccount = $accountService;
    }

    public function get_list(){
        if (!has_permission('clients','view')) {
            access_denied();
        }
        $title = lang('Quản lý thành viên');
        return view('admin.clients.list',[
            'title' => $title,
        ]);
    }


    public function get_detail($id = 0) {
        if (!has_permission('clients', 'edit')){
            access_denied();
        }
        if (!has_permission('clients','view')) {
            access_denied();
        }
        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->getDetailCustomer($this->request);
        $data = $response->getData(true);
        $client = $data['client'] ?? [];

        $title = lang('c_title_edit_client');
        return view('admin.clients.detail',[
            'id' => $id,
            'title' => $title,
            'client' => $client,
        ]);
    }

    public function view($id = 0){
        if (!has_permission('clients','view') && has_permission('clients','viewown')) {
        }

        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->getDetailCustomer($this->request);
        $data = $response->getData(true);
        $client = $data['client'] ?? [];
        $client_information_vat = $client['client_information_vat'] ?? [];
        $referral = $data['referral'] ?? [];
        if (!has_permission('clients','view') && has_permission('clients','viewown')) {
            if (!empty($id) && empty($client['id'])) {
                access_denied();
            }
        }

        $client['count_product_review'] = ClientsReview::where('id_client', $client['id'] ?? 0)->count();
        $level = $referral['level'] ?? 0;
        $countMember = $referral['count_member'] ?? 0;
        $dataReferralLevel = $referral['data'] ?? [];
        $title = lang('dt_view_client');
        return view('admin.clients.view',[
            'title' => $title,
            'client' => $client,
            'level' => $level,
            'dataReferralLevel' => $dataReferralLevel,
            'countMember' => $countMember,
            'client_information_vat' => $client_information_vat,
        ]);
    }

    public function getListCustomer()
    {
        $this->request->merge(['type_client' => 1]);
        if (!has_permission('clients','view') && has_permission('clients','viewown')) {
            //            $UserAres = UserAres::where('id_user', get_staff_user_id())->get();
            $this->request->merge(['ares_permission' => 1]);
            //            $aresPer = [];
            //            foreach ($UserAres as $key => $item) {
            //                $aresPer[] = $item->id_ares;
            //            }
            //            $this->request->merge(['aresPer' => $aresPer]);
            $this->request->merge(['user_id' => get_staff_user_id()]);
        }

        $response = $this->dbAccount->getListCustomer($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $clients = collect($data['data']);

        return (new CollectionDataTable($clients))
            ->addColumn('options', function ($client) {
                $customer_id = $client['id'];
                $view = "<a href='admin/clients/view/$customer_id'><i class='fa fa-eye'></i> " . lang('dt_view') . "</a>";
                $edit = "<a href='admin/clients/detail/$customer_id'><i class='fa fa-pencil'></i> " . lang('c_edit_client') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/clients/delete/' . $customer_id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_client') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $view . '</li>
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('fullname', function ($client) {
                $str = '<div><a href="admin/clients/view/' . $client['id'] . '">' . $client['fullname'] . '</a></div>';
                return $str;
            })
            ->editColumn('phone', function ($client) {
                $str = $client['phone'];
                return $str;
            })
            ->addColumn('img_membership_level', function ($client) {
                $memberLevel = MemberShipLevel::find($client['membership_level']);
                //                $dtImage = !empty($client['membership_level']) ? url('/upload/membership_level/'.$client['membership_level'].'.png') : null;
                $dtImage = !empty($memberLevel->icon) ? asset('storage/'.$memberLevel->icon) : null;
                if($client['active_limit_private'] == 1) {
                    $radio_discount = $client['radio_discount_private'];
                }
                else {
                    $radio_discount = $memberLevel->radio_discount;
                }

                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                             class="show_image">
                            <img src="' . $dtImage . '" alt="avatar"
                                 class="img-responsive img-circle"
                                 style="width: 30px;height: 30px"><span class="m-t-5" style="color:'.$memberLevel->color.'"><strong>Hạng ' . $memberLevel->name. '</strong> ('.$radio_discount.'%)</span>
                        </div>';
                return $str;
            })
            ->addColumn('invoice_limit', function ($client) {
                if(!empty($client['active_limit_private'])) {
                    return '<div class="text-center">'.(!empty($client['invoice_limit_private']) ? number_format($client['invoice_limit_private']) : 'Chưa đặt hạn mức').'</div>';
                }
                else {
                    $membership_level = MemberShipLevel::find($client['membership_level']);
                    return '<div class="text-center">' . (!empty($membership_level->invoice_limit) ? number_format($membership_level->invoice_limit) : 'Không giới hạn') . '</div>';
                }
            })
            ->editColumn('point_membership', function ($client) {
                $str = '<div class="label label-default">'.number_format($client['point_membership']).'</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('ranking_date', function ($client) {
                $str = _dthuan($client['ranking_date']);
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('code_introduce_parent', function ($client) {
                $str = '<div class="label label-default">1</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('referral_code', function ($client) {
                $str = '<div class="label label-default">'.$client['referral_code'].'</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('created_at', function ($client) {
                $str = _dt($client['created_at']);
                return $str;
            })
            ->editColumn('date_active', function ($client) {
                $customer_package = $client['customer_package'] ?? null;
                $namePackage = '';
                $checkDefault = 0;
                if (!empty($customer_package)){
                    $namePackage = $customer_package['name'];
                    $checkDefault = $customer_package['package']['check_default'] ?? 0;
                }
                $str = !empty($client['date_active']) ? _dthuan($client['date_active']) : null;
                return '<div>'.$str.'</div><div><span class="label '.($checkDefault == 1 ? 'label-default': 'label-info').'">'.$namePackage.'</span></div>';
            })
            ->editColumn('active', function ($client) {
                $customer_id = $client['id'];
                if ($client['active'] == 1) {
                    $str = "<a class='dt-update status-badge status-active' href='admin/clients/active/$customer_id'><i class='fa fa-check-circle'></i> " . lang('Hoạt động') . "</a>";
                } else {
                    $str = "<a class='dt-update status-badge status-locked' href='admin/clients/active/$customer_id'><i class='fa fa-times-circle'></i> " . lang('Khoá') . "</a>";
                }
                return $str;
            })
            ->editColumn('avatar', function ($client) {
                $dtImage = !empty($client['avatar']) ? $client['avatar'] : imgDefault();
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';

                return $str;
            })
            ->editColumn('type_client', function ($client) {
                if ($client['type_client'] == 0) {
                    $str = "<span class='type-badge type-viewer'><i class='fa fa-eye'></i> " . lang('Người xem') . "</span>";
                } elseif ($client['type_client'] == 1) {
                    $str = "<span class='type-badge type-sale'><i class='fa fa-id-badge'></i> " . lang('Nhân viên sale') . "</span>";
                } elseif ($client['type_client'] == 2) {
                    $str = "<span class='type-badge type-admin'><i class='fa fa-shield'></i> " . lang('Admin') . "</span>";
                } else {
                    $str = "<span class='type-badge type-undefined'><i class='fa fa-question-circle'></i> " . lang('Chưa xác định') . "</span>";
                }
                return $str;
            })
            ->rawColumns(['options', 'active', 'avatar', 'type_client', 'img_membership_level', 'phone', 'created_at', 'fullname','referral_code','point_membership','ranking_date','invoice_limit','date_active','code_introduce_parent'])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function countAll(){
        $response = $this->dbAccount->countAll($this->request);
        $data = $response->getData(true);
        $data['all'] = $data['total'] ?? 0;
        $data['arrType'] = $data['arrType'] ?? [];
        return response()->json($data);
    }

    public function detail() {
        if(!empty($this->request->invoice_limit_private)) {
            $this->request->merge(['invoice_limit_private' => number_unformat($this->request->invoice_limit_private)]);
        }
        $response = $this->dbAccount->detailCustomer($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        if (!has_permission('clients', 'delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->deleteCustomer($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function active($id = 0){
        if (!has_permission('clients', 'approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->active($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function changeStatusLeader($id = 0){
        if (!has_permission('clients', 'approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->changeStatusLeader($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function getClientsIntroduceOrder(){
        $response = $this->dbAccount->getListDataOrderReferral($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $clients = collect($data['data']);

        return Datatables::of($clients)
            ->addColumn('avatar', function ($data) {
                $dtImage = !empty($data['avatar']) ? $data['avatar'] : imgDefault();
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                return $str;
            })
            ->addColumn('code', function ($data)  {
                return '<div class="text-center">'.$data['code'].'</div>';
            })
            ->addColumn('fullname', function ($data) {
                return '<div class="text-center">'.$data['fullname'].'</div>';
            })
            ->addColumn('phone', function ($data) {
                return '<div class="text-center">'.$data['phone'].'</div>';
            })
            ->addColumn('type_client', function ($data) {
                if ($data['type_client'] == 2) {
                    $str = "<span class='type-badge type-sale'><i class='fa fa-star'></i> " . lang('client_type_2') . "</span>";
                } else {
                    $str = "<span class='type-badge type-viewer'><i class='fa fa-user'></i> " . lang('client_type_1') . "</span>";
                }
                return $str;
            })
            ->addColumn('created_at', function ($data) {
                return '<div class="text-center">'._dt($data['date']).'</div>';
            })
            ->addColumn('code_review', function ($data) {
                return '<div class="text-center">'.($data['reference_no']).'</div>';
            })
            ->addColumn('active', function ($data){
                $customer_id = $data['id'];
                if ($data['active'] == 1) {
                    $str = "<a class='dt-update status-badge status-active'><i class='fa fa-check-circle'></i> " . lang('Hoạt động') . "</a>";
                } else {
                    $str = "<a class='dt-update status-badge status-locked'><i class='fa fa-times-circle'></i> " . lang('Khoá') . "</a>";
                }
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('updated_at')
            ->rawColumns([
                'created_at','avatar','code','fullname','phone','active','type_client','code_review'
            ])
            ->make(true);
    }

    public function getClientsOrderLeader(){
        $response = $this->dbAccount->getClientsOrderLeader($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $clients = collect($data['data']);

        return Datatables::of($clients)
        ->addColumn('id', function ($row) use (&$start) {
            return '<div>'.(++$start).'</div>';
        })
        ->addColumn('date', function ($data) {
            return '<div class="text-center">'._dt($data['date']).'</div>';
        })
        ->addColumn('code_order', function ($data) {
            return '<div class="text-left"><a class="dt-modal" href="admin/transaction/view/'.$data['id'].'">'.$data['reference_no'].'</a></div>';
        })
        ->addColumn('customer', function ($data) {
            $dtImage = !empty($data['customer']['avatar']) ? $data['customer']['avatar'] : imgDefault();
            $strImage = '<div>
                <img src="' . $dtImage . '" alt="avatar"
                     class="img-responsive img-circle"
                     style="width: 50px;height: 50px">

            </div>';

            $str = $strImage . '<div class="text-left m-l-10"><a href="admin/clients/view/'.$data['customer']['id'].'">'.$data['customer']['fullname'].'</a></div>';
            return '<div style="display: flex;align-items: center;">'.$str.'</div>';
        })
        ->addColumn('total_order', function ($data) {
            return '<div>'.formatMoney($data['grand_total']).'</div>';
        })
        ->addColumn('total_leader', function ($data) {
            return '<div>'.formatMoney($data['total_accumulate']).'</div>';
        })
        ->removeColumn('updated_at')
        ->rawColumns([
            'date','code_order','total_order','total_leader','id','customer'
        ])
        ->make(true);
    }

    public function changeStatusTypeLeader()
    {
        if (!has_permission('clients', 'approve')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $client_id = $this->request->client_id;
        $type_leader = $this->request->status;
        $this->request->merge(['id' => $client_id]);
        $this->request->merge(['type_leader' => $type_leader]);
        $response = $this->dbAccount->changeStatusTypeLeader($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function detailInformationVat($id = 0){
        $this->request->merge(['customer_id' => $id]);
        $response = $this->dbAccount->detailInformationVat($this->request);
        $data = $response->getData(true);
        $data = $data['data'];
        return response()->json($data);
    }

    public function ajaxSearch() {
        if (!has_permission('clients', 'view')) {
            return response()->json(['results' => []]);
        }
        $q = $this->request->input('q');
        $typeClientSearch = $this->request->input('type_client_search');
        $newRequest = new \Illuminate\Http\Request();
        $mergeData = [
            'search' => ['value' => $q],
            'start' => 0,
            'length' => 50
        ];
        if ($typeClientSearch) {
            $mergeData['type_client_search'] = $typeClientSearch;
        }
        $newRequest->merge($mergeData);
        $response = $this->dbAccount->getListCustomer($newRequest);
        $data = $response->getData(true);
        
        $results = [];
        if (!empty($data['data'])) {
            foreach ($data['data'] as $item) {
                $results[] = [
                    'id' => $item['id'],
                    'text' => ($item['fullname'] ?? '') . ' - ' . ($item['phone'] ?? ''),
                    'fullname' => $item['fullname'] ?? '',
                    'phone' => $item['phone'] ?? ''
                ];
            }
        }
        return response()->json([
            'results' => $results
        ]);
    }
}
