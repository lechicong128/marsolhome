<?php

namespace App\Http\Controllers;

use App\Models\CategoryProducts;
use App\Models\Notification;
use App\Models\ProductCategory;
use App\Models\GroupPermission;
use App\Models\Language;
use App\Models\Permission;
use App\Models\Products;
use App\Models\ProductTranslations;
use App\Models\SignUpReview;
use App\Models\ClientsReview;
use App\Models\ReviewFile;
use App\Models\VideoFile;
use App\Services\AccountService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ClientsReviewController extends Controller
{
    protected $dbAccount;
    use UploadFile;
    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
        $this->dbAccount = $accountService;
    }

    public function get_list(){
        if (!has_permission('clients_review','view')){
            access_denied();
        }
        return view('admin.clients_review.list',[
            'title' => lang('list_client_review'),
        ]);
    }


    public function getTable() {
        $_locale = 'vi';
        $id_customer = $this->request->input('id_customer');
        $filter_status_search = $this->request->input('status_search');
        $filter_date_search = $this->request->input('date_search');
        $filter_product_search = $this->request->input('product_search');
        $date_start = null;
        $date_end = null;
        if(!empty($filter_date_search)) {
            $filter_date_search = explode(' - ', $filter_date_search);
            $date_start = to_sql_date($filter_date_search[0]);
            $date_end = to_sql_date($filter_date_search[1]) .' 23:59:59';
        }

        $searchValue = $this->request->input('search.value');
        $listIDsearch = [];
        if(!empty($searchValue)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['search' => $searchValue]);
            $responseClientSearch = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClientSearch = $responseClientSearch->getData(true);
            if(!empty($dataClientSearch) && !empty($dataClientSearch['clients'])) {
                $dataClientSearch = $dataClientSearch['clients'];
                foreach($dataClientSearch as $value) {
                    $listIDsearch[] = $value['id'];
                }
            }
        }

        // DataTables paging
        $start  = (int) $this->request->input('start', 0);
        $length = (int) $this->request->input('length', 10);

        // DataTables ordering (an toàn với fallback)
        $orderReq = $this->request->input('order', []);
        $columns  = $this->request->input('columns', []);
        $columnIndex = isset($orderReq[0]['column']) ? (int)$orderReq[0]['column'] : 0;
        $orderBy = $columns[$columnIndex]['name'] ?? 'tbl_sign_up_review.id';
        $orderDir = isset($orderReq[0]['dir']) && in_array(strtolower($orderReq[0]['dir']), ['asc','desc'])
            ? $orderReq[0]['dir'] : 'desc';
        $baseQuery = SignUpReview::query()
            ->select('tbl_sign_up_review.*')
            ->with('clients_review')
            ->with('clients_review.products')
            ->where(function($q) use ($id_customer, $filter_status_search) {
                if (!empty($id_customer)) {
                    $q->where('tbl_sign_up_review.id_client', '=', $id_customer);
                }
                if (is_numeric($filter_status_search) && $filter_status_search >= 0) {
                    $q->where('tbl_sign_up_review.status', '=', $filter_status_search);
                }
            })
            ->when(!empty($searchValue), function ($q) use ($searchValue, $_locale, $listIDsearch) {
                $q->where(function ($w) use ($searchValue, $_locale, $listIDsearch) {
                    $w->where('tbl_sign_up_review.code_review', 'like', "%{$searchValue}%")
                        ->orWhere('tbl_sign_up_review.id', '=', $searchValue)
                        ->orWhereHas(
                            'clients_review.products',
                            function ($p) use ($searchValue, $_locale) {
                                // ràng buộc locale cho chắc
                                $p->whereExists(function ($sub) use ($_locale, $searchValue) {
                                    $sub->from('tbl_product_translations as pt')->whereColumn(
                                            'pt.id_product',
                                            'tbl_products.id'
                                        )->where('pt.language', $_locale)->where(
                                            function ($qPdocut) use ($searchValue) {
                                                $qPdocut->where('pt.name', 'like', "%{$searchValue}%");
                                                $qPdocut->orWhere('tbl_products.code', '=', $searchValue);
                                            }
                                        );
                                });
                            }
                        )
                        ->orWhereIn('tbl_sign_up_review.id_client', $listIDsearch);
                });
            })
            ->when(!empty($date_start), function ($q) use ($date_start, $_locale) {
                $q->where('tbl_sign_up_review.created_at', '>=', $date_start);
            })
            ->when(!empty($date_end), function ($q) use ($date_end, $_locale) {
                $q->where('tbl_sign_up_review.created_at', '<=', $date_end);
            })
            ->when(!empty($filter_product_search), function ($q) use ($filter_product_search, $_locale) {
                $q->WhereHas('clients_review.products',
                    function ($p) use ($filter_product_search, $_locale) {
                        $p->whereExists(function ($sub) use ($_locale, $filter_product_search) {
                            $sub->where('tbl_products.id', '=', $filter_product_search);
                        });
                    }
                );
            });

        $idsOnPage = (clone $baseQuery)
            ->orderBy($orderBy, $orderDir)
            ->when($length > -1, function($q) use ($start, $length) {
                // DataTables dùng start/length; length = -1 nghĩa là hiển thị tất cả
                $q->skip($start)->take($length);
            })
            ->pluck('tbl_sign_up_review.id_client')
            ->unique()
            ->filter() // loại null/empty
            ->values()
            ->all();

        // Lấy thông tin khách chỉ cho các id trên TRANG HIỆN TẠI
        $dataClient = [];
        if (!empty($idsOnPage)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['list_id' => $idsOnPage]);
            unset($newRequest['search']); // tránh ảnh hưởng bởi search chung
            $responseClient = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClient = $responseClient->getData(true);
        }

        // Trả DataTables: áp dụng cùng order để đồng bộ với idsOnPage
        return Datatables::of($baseQuery->orderBy($orderBy, $orderDir))
            ->filter(function($query) use ($listIDsearch, $searchValue) {
                if (!empty($listIDsearch)) {
                    $query->whereIn('tbl_sign_up_review.id_client', $listIDsearch);
                }
            })
            ->addColumn('options', function ($SignReview) {
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/clients_review/delete/'.$SignReview->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_review_product') .'</a>';
                return '<div class="dropdown text-center">
                        <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                            Tác vụ <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenu1">
                            <li style="cursor: pointer">'.$delete.'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('clients', function ($SignReview) use ($dataClient) {
                $client = $dataClient['clients'][$SignReview->id_client] ?? [];
                if (empty($client)) return '';
                $imgAvatar = $client['avatar'] ?? '';
                return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                 style="width:35px;height:35px;" src="' . $imgAvatar . '"/>
                        </div>
                        <div>
                            <strong>' . ($client['fullname'] ?? '') . '</strong>
                            <br><small>' . ($client['phone'] ?? '') . '</small>
                        </div>
                    </div>';
            })
            ->addColumn('address', function ($SignReview) use ($dataClient) {
//                $client = $dataClient['clients'][$SignReview->id_client] ?? [];
//                if (empty($client)) return '';
//                return '<div><strong>' . ($client['address'] ?? '') . '</strong></div>';
                return '<div><strong>' . ($SignReview->address_receiver ?? '') . '</strong></div>';
            })
            ->editColumn('products', function ($SignReview) {
                // Gom tất cả sản phẩm của đơn đăng ký
                $items = [];
                foreach ($SignReview->clients_review as $vPRo) {

                    foreach ($vPRo->products as $p) {
                        $items[] = [
                            'id_review' =>  $vPRo->id,
                            'img'       => $this->baseUrlAdmin . '/' . $p->image,
                            'name'      => $p->name,
                            'code'      => $p->code,
                            'is_review' => $vPRo->is_review ? true : false,
                            'status' => $SignReview->status,
                        ];
                    }
                }

                $total = count($items);
                if ($total === 0) return '';

                $visible = array_slice($items, 0, 3);
                $hidden  = array_slice($items, 3);

                // Render sản phẩm (tối đa 3 cái đầu)
                $htmlItem = function($it) {
                    $badge = $it['is_review'] ? '<i class="fa fa-check-square-o text-success" title="Đã Review" aria-hidden="true"></i>' : '';
                    if(empty($badge)) {
                        if($it['status'] < 3 || $it['status'] == Config::get('constant')['cancel_review_guest']) {
                            $badge = '<i data-status="'.$it['status'].'" class="fa fa-remove text-danger pointer" onclick="removeItems(' . $it['id_review'] . ')" title="' . lang(
                                    'remove'
                                ) . '" aria-hidden="true"></i>';
                        }
                    }

                    return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle"
                                 onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                 style="width:35px;height:35px;" src="'.e($it['img']).'"/>
                        </div>
                        <div>
                            <strong>'.e($it['name']).' '.$badge.'</strong>
                            <br><small>Mã: '.e($it['code']).'</small>
                        </div>
                    </div>';
                };

                $str = '';
                foreach ($visible as $it) $str .= $htmlItem($it);

                if ($total > 3) {
                    $idBox  = 'more-pro-'.$SignReview->id;
                    $remain = $total - 3;

                    // Khối ẩn có hiệu ứng collapse
                    $str .= '<div id="'.$idBox.'" class="collapse mt-2">';
                    foreach ($hidden as $it) $str .= $htmlItem($it);
                    $str .= '</div>';

                    // Nút đẹp: pill + outline + icon + badge đếm
                    $str .= '<button type="button"
                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 see-more-toggle mt-1"
                                    data-toggle="collapse"
                                    data-target="#'.$idBox.'"
                                    aria-expanded="false"
                                    aria-controls="'.$idBox.'"
                                    data-show-text="'.lang('ct_short').'"
                                    data-hide-text="'.lang('products').'">
                                <i class="fa fa-plus-circle mr-1"></i>
                                (<span class=" ml-2">'.$remain.' </span>
                                <span class="toggle-text">'.lang('products').'</span>)
                             </button>';
                }

                return $str;
            })
            ->editColumn('code_review', function ($SignReview) {
                return '<div class="text-center"><a class="dt-modal" href="admin/clients_review/view/'.$SignReview->id.'">'.e($SignReview->code_review).'</a></div>';
            })
            ->editColumn('status', function ($SignReview) {
                $statusReview = status_product_review($SignReview->status);
                $optionStatus = '<div class="btn-group">
                    <button type="button" class="btn btn-white dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false"
                            style="min-width:150px;border:1px solid '.$statusReview['color'].' !important">
                        <div class="label" style="color:'.$statusReview['color'].'">'.$statusReview['name'].'</div>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">';
                foreach (status_product_review() as $key => $value) {
                    $index = $SignReview->status;
                    $check = ($value['id'] == Config::get('constant')['cancel_owen']) ? 1 : 0;
                    $classes = ($key < $index) ? 'pointer-events' : '';
                    if ($SignReview->status == Config::get('constant')['success_review_guest']
                        && $value['id'] != Config::get('constant')['success_review_guest']) {
                        $classes = 'pointer-events';
                    }
                    $optionStatus .= '<li style="cursor:pointer" class="'.$classes.'">
                        <a onclick="changeStatus('.$SignReview->id.','.$value['id'].','.$check.')" data-id="'.$value['id'].'">'.$value['name'].'</a>
                    </li>';
                }
                $optionStatus .= '</ul></div>';
                return $optionStatus;
            })
            ->editColumn('created_at', function ($SignReview) {
                return '<div class="text-center">'._dt($SignReview->created_at).'</div>';
            })
            ->addIndexColumn()
            ->removeColumn('updated_at')
            ->rawColumns([
                'options',
                'code_review',
                'products',
                'clients',
                'address',
                'status',
                'created_at',
            ])
            ->make(true);
    }

    public function changeStatus()
    {
        if (!has_permission('clients_review', 'approve')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $id = $this->request->id;
        $status = $this->request->status;
        $noteStatus = $this->request->note;

        $SignUpReview = SignUpReview::find($id);


//        $CodeProductReview = ClientsReview::where('id_review', $id)->select(DB::raw('GROUP_CONCAT(code) as code_product'))
//            ->join('tbl_products', 'tbl_products.id', '=', 'tbl_clients_sign_up_review.id_product', 'left')
//            ->first();
//
//        $this->request->merge(['id' => $SignUpReview->id_client]);
//        $responseClient = $this->dbAccount->getDetailCustomer($this->request);
//        $dataClient = $responseClient->getData(true)['client'];
//
//        $dataSendZalo = [
//            'date' => date("H:i d/m/Y", strtotime($SignUpReview->created_at)),
//            'order_code' => $SignUpReview->code_review,
//            'code_product' => $CodeProductReview->code_product ?? '',
//            'name' => $dataClient['fullname'] ?? '',
//            'status' => status_product_review($SignUpReview->status)['name'] ?? '',
//        ];
//        $success = send_zalo($dataClient['phone'], 'changeStatus', 492827, $dataSendZalo);die();



        if(!empty($SignUpReview->id)) {
            $arr = [Config::get('constant')['cancel_review_guest'], Config::get('constant')['success_review_guest']];
            $index = $SignUpReview->status;
            if ($index == Config::get('constant')['cancel_review_guest']) {
                $data['result'] = false;
                $data['message'] = lang('sign_up_review_cancel_not_edit');
                return response()->json($data);
            }
            if ($index == Config::get('constant')['success_review_guest']) {
                $data['result'] = false;
                $data['message'] = lang('sign_up_review_change_not_edit');
                return response()->json($data);
            }
            if ($status < $index) {
                $data['result'] = false;
                $data['message'] = lang('status_loss_status_now');
                return response()->json($data);
            }
            if ($SignUpReview->status == $this->request->status) {
                $data['result'] = false;
                $data['message'] = lang('status_isset_status_now');
                return response()->json($data);
            }
            DB::beginTransaction();
            try {
                $SignUpReview->status = $status;
                $SignUpReview->date_status = date('Y-m-d H:i:s');
                $SignUpReview->staff_status = get_staff_user_id();
                if ($status == 4 || $status == 5) {
                    if (empty($signUpReview->date_success)) {
                        $SignUpReview->date_success = date('Y-m-d H:i:s');
                    }
                }
                $SignUpReview->save();
                if ($SignUpReview->status == Config::get('constant')['cancel_review_guest']) {
                    //hủy đăng ký nên hoàn lại 1 đăng ký
                    $clientReview = ClientsReview::where('id_review')->get();
                    foreach ($clientReview as $key => $value) {
                        $products = Products::find($value->id_product);
                        $products->count_join = ($products->count_join + 1);
                        $products->save();
                    }
                }


                $CodeProductReview = ClientsReview::where('id_review', $id)->select(DB::raw('GROUP_CONCAT(code) as code_product'))
                    ->join('tbl_products', 'tbl_products.id', '=', 'tbl_clients_sign_up_review.id_product', 'left')
                    ->first();

                $this->request->merge(['id' => $SignUpReview->id_client]);
                $responseClient = $this->dbAccount->getDetailCustomer($this->request);
                $dataClient = $responseClient->getData(true)['client'];
                $dataSendZalo = [
                    'date' => date("H:i d/m/Y", strtotime($SignUpReview->created_at)),
                    'order_code' => $SignUpReview->code_review,
                    'code_product' => $CodeProductReview->code_product ?? '',
                    'name' => $dataClient['fullname'] ?? '',
                    'status' => status_product_review($SignUpReview->status)['name'] ?? '',
                ];
                send_zalo($dataClient['phone'], 'changeStatus', 492827, $dataSendZalo);


                activity()->causedBy(get_staff_user_id())->performedOn($SignUpReview)->useLog(
                        'SignUpReview'
                    )->withProperties(['SignUpReview' => 'change_status'])->log(
                        'Thay đổi trạng thái phiếu đăng ký thành công - [' . $status . '] [' . $SignUpReview->code . ']'
                    );
                $data['result'] = true;
                $data['message'] = lang('c_update_status_success');
                DB::commit();
                return response()->json($data);
            } catch (\Exception $exception) {
                DB::rollBack();
                $data['result'] = false;
                $data['message'] = $exception;
                return response()->json($data);
            }
        }
        else {
            $data['result'] = false;
            $data['message'] = lang('c_not_found_data');
        }
    }

    public function delete($id){
        if (!has_permission('clients_review', 'delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $SignUpReview = SignUpReview::find($id);
        try {
            $clientIsReview = ClientsReview::where('id_review', $id)->where('is_review', 1)->first();
            if(!empty($clientIsReview->id)) {
                $data['result'] = false;
                $data['message'] = lang('Phiếu có sản phẩm đã review không thể xóa');
                return response()->json($data);
            }
            if($SignUpReview->status != Config::get('constant')['cancel_review_guest']) {
                //hủy đăng ký nên hoàn lại 1 đăng ký
                $clientReview = ClientsReview::where('id_review', $id)->get();
                foreach($clientReview as $key => $value) {
                    $products = Products::find($value->id_product);
                    $products->count_join = ($products->count_join + 1);
                    $products->save();
                }
            }
            $SignUpReview->delete();

            ClientsReview::where('id_review', $id)->delete();

            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function deleteReview($id){
        if (!has_permission('clients_review', 'delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $clientReview = ClientsReview::find($id);
        try {
            $newRequest = clone $this->request;
            $newRequest->merge(['id_review' => $id]);
            $responseDetailPointReivew = $this->dbAccount->getDetailPointReivew($newRequest);
            $dataClient = $responseDetailPointReivew->getData(true);
            $newRequest->merge(['id_history' => $dataClient['HistoryPoint']['id'] ?? 0]);
            $isDeletePoint = false;
            if(!empty($dataClient['HistoryPoint']) && !empty($dataClient['dataClients'])) {
                if($dataClient['HistoryPoint']['point'] > $dataClient['dataClients']['point']){
                    $point_client = $dataClient['dataClients']['point'];
                    $point = $dataClient['HistoryPoint']['point'];
                    // 'Tổng điểm {point_client} của khách hàng này không đủ để xóa review có điểm {point} này',
                    $dt_not_delete_review_point = lang('dt_not_delete_review_point');
                    $dt_not_delete_review_point = str_replace('{point_client}', formatMoney($point_client), $dt_not_delete_review_point);
                    $dt_not_delete_review_point = str_replace('{point}', formatMoney($point), $dt_not_delete_review_point);
                    $data['result'] = false;
                    $data['message'] = $dt_not_delete_review_point;
                    return response()->json($data);
                }
                $isDeletePoint = true;
            }
            if(!empty($clientReview->video_review)) {
                $this->deleteFile($clientReview->video_review);
                $clientReview->video_review = NULL;
            }

            if(!empty($clientReview->video_review_render)) {
                $this->deleteFile($clientReview->video_review_render);
                $clientReview->video_review_render = NULL;
            }

            if(!empty($clientReview->small_image_video_review)) {
                $this->deleteFile($clientReview->small_image_video_review);
                $clientReview->small_image_video_review = NULL;
            }

            if(!empty($clientReview->active)) {
                $products = Products::find($clientReview->id_product);
                if (!empty($products->id)) {
                    $products->quantity_reviews = ($products->quantity_reviews - 1);
                    $products->save();
                }
            }

            $clientReview->date_active_review = NULL;
            $clientReview->is_review = 0;
            $clientReview->evaluate = 0;
            $clientReview->content_evaluate = NULL;
            $clientReview->date_review = NULL;
            $clientReview->active = 0;
            $clientReview->save();

            $listReview = ReviewFile::where('id_review', $id)->get();
            foreach ($listReview as $key => $value) {
                $this->deleteFile($value->media);
            }
            ReviewFile::where('id_review', $id)->delete();
            if($isDeletePoint == true) {
                $responseDeletePoint = $this->dbAccount->getDeleteDetailPointReivew($newRequest);
            }

            if($clientReview->type_object == 'transaction') {
                $id_transaction = $clientReview->id_transaction;
                $clientReview->delete();
                $KTReviewTransaction = ClientsReview::where('id_transaction', $id_transaction)->get();
                if($KTReviewTransaction->isEmpty()) {
                    $this->dbAccount->UnCheckTransactionReview($this->request, $id_transaction);
                    DB::table('tbl_transaction_review')->where('id_transaction', $id_transaction)->delete();
                }
            }

            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function countAll(){
        $id_customer = $this->request->input('id_customer');
        $filter_is_review = $this->request->input('filter_is_review');
        $filter_status_search = $this->request->input('status_search');
        $listStatus = status_product_review();
        $countData = [];
        foreach($listStatus as $key => $value) {
            $countData[$value['id']] = SignUpReview::where(function($q) use ($id_customer, $filter_is_review, $value) {
                if(!empty($id_customer)) {
                    $q->where('tbl_sign_up_review.id_client', '=', $id_customer);
                }
                if(is_numeric($value['id']) && $value['id'] >= 0) {
                    $q->where('tbl_sign_up_review.status', '=', $value['id']);
                }
            })->count();
        }
        return response()->json([
            'data' => $countData
        ]);
    }

    public function library_review() {
        if (!has_permission('clients_review','view')){
            access_denied();
        }
        return view('admin.clients_review.library_review',[
            'title' => lang('c_library_review'),
        ]);
    }

    public function getTableReview(){
        $_locale = 'vi';
        $id_customer          = $this->request->input('id_customer');
        $filter_is_review     = $this->request->input('filter_is_review');
        $filter_status_search = $this->request->input('status_search');


        $type_object_search = $this->request->input('type_object_search');
        $filter_date_search = $this->request->input('date_search');
        $filter_product_search = $this->request->input('product_search');
        $date_start = null;
        $date_end = null;
        if(!empty($filter_date_search)) {
            $filter_date_search = explode(' - ', $filter_date_search);
            $date_start = to_sql_date($filter_date_search[0]);
            $date_end = to_sql_date($filter_date_search[1]) .' 23:59:59';
        }

        $searchValue = $this->request->input('search.value');
        $listIDsearch = [];
        if(!empty($searchValue)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['search' => $searchValue]);
            $responseClientSearch = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClientSearch = $responseClientSearch->getData(true);
            if(!empty($dataClientSearch) && !empty($dataClientSearch['clients'])) {
                $dataClientSearch = $dataClientSearch['clients'];
                foreach($dataClientSearch as $value) {
                    $listIDsearch[] = $value['id'];
                }
            }
        }

        // --- DataTables paging & ordering ---
        $start   = (int) $this->request->input('start', 0);
        $length  = (int) $this->request->input('length', 10);
        $orders  = $this->request->input('order', []);
        $columns = $this->request->input('columns', []);

        $colIndex = isset($orders[0]['column']) ? (int)$orders[0]['column'] : 0;
        $orderBy  = $columns[$colIndex]['name'] ?? 'tbl_clients_sign_up_review.id';
        $orderDir = isset($orders[0]['dir']) && in_array(strtolower($orders[0]['dir']), ['asc','desc'])
            ? $orders[0]['dir'] : 'desc';

        // --- Base query (KHÔNG gọi get) ---
        $baseQuery = ClientsReview::select(
            'tbl_clients_sign_up_review.*',
            'tbl_products.name as name_product',
            'tbl_products.code as code_product',
            'tbl_products.image as image_product'
        )
            ->leftJoin('tbl_products', 'tbl_products.id', '=', 'tbl_clients_sign_up_review.id_product')
            ->with(['transalations.language_detail'])
            ->where(function($q) use ($id_customer, $filter_is_review, $filter_status_search) {
                if (!empty($id_customer)) {
                    $q->where('tbl_clients_sign_up_review.id_client', '=', $id_customer);
                }
                if ($filter_is_review !== null && $filter_is_review !== '') {
                    $q->where('tbl_clients_sign_up_review.is_review', '=', $filter_is_review);
                }
                if (is_numeric($filter_status_search) && $filter_status_search >= 0) {
                    $q->where('tbl_clients_sign_up_review.status', '=', $filter_status_search);
                }
            })
            ->when(!empty($searchValue), function ($q) use ($searchValue, $listIDsearch) {
                $q->where(function ($w) use ($searchValue, $listIDsearch) {
                    $w->where('tbl_clients_sign_up_review.code_review', 'like', "%{$searchValue}%")
                        ->orWhere('tbl_products.name', 'like', "%{$searchValue}%")
                        ->orWhere('tbl_products.code', 'like', "%{$searchValue}%")
                        ->orWhereIn('tbl_clients_sign_up_review.id_client', $listIDsearch);
                });
            })
            ->when(!empty($date_start), function ($q) use ($date_start, $_locale) {
                $q->where('tbl_clients_sign_up_review.date_review', '>=', $date_start);
            })
            ->when(!empty($date_end), function ($q) use ($date_end, $_locale) {
                $q->where('tbl_clients_sign_up_review.date_review', '<=', $date_end);
            })
            ->when(!empty($filter_product_search), function ($q) use ($filter_product_search, $_locale) {
                $q->where('tbl_clients_sign_up_review.id_product', '=', $filter_product_search);
            })
            ->when(!empty($type_object_search), function ($q) use ($type_object_search, $_locale) {
                $q->where('tbl_clients_sign_up_review.type_object', '=', $type_object_search);
            });

        // --- CHỈ lấy id_client trên TRANG HIỆN TẠI ---
        $idsOnPage = (clone $baseQuery)
            ->orderBy($orderBy, $orderDir)
            ->when($length > -1, function($q) use ($start, $length) {
                $q->skip($start)->take($length);
            })
            ->pluck('tbl_clients_sign_up_review.id_client')
            ->unique()
            ->filter()
            ->values()
            ->all();

        // --- Lấy thông tin khách cho đúng các id ở trang hiện tại ---
        $dataClient = [];
        if (!empty($idsOnPage)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['list_id' => $idsOnPage]);
            unset($newRequest['search']); // tránh ảnh hưởng bởi search chung
            $responseClient = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClient = $responseClient->getData(true);
        }

        // --- Trả DataTables (áp dụng cùng order để đồng bộ) ---
        return Datatables::of($baseQuery->orderBy($orderBy, $orderDir))
            ->filter(function($query) use ($listIDsearch, $searchValue) {
                if (!empty($listIDsearch)) {
                    $query->whereIn('tbl_clients_sign_up_review.id_client', $listIDsearch);
                }
            })
            ->addColumn('options', function ($clientReview) {
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/clients_review/deleteReview/'.$clientReview->id.'\' class=\'btn btn-danger dt-delete\'>'.lang('dt_delete').'</button>
                <button class=\'btn btn-default po-close\'>'.lang('dt_close').'</button>
            "><i class="fa fa-remove width-icon-actions"></i> '.lang('c_delete_review').'</a>';

                return '<div class="dropdown text-center">
                        <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                            Tác vụ <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenu1">
                            <li style="cursor: pointer">'.$delete.'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('clients', function ($clientReview) use ($dataClient) {
                $client = $dataClient['clients'][$clientReview->id_client] ?? [];
                if (empty($client)) {
                    // fallback tối giản khi chưa có cache client
                    return '<div class="text-muted">'.$clientReview->id_client.'</div>';
                }
                $imgAvatar = $client['avatar'] ?? '';
                return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                 style="width:35px;height:35px;" src="'.e($imgAvatar).'"/>
                        </div>
                        <div>
                            <strong>'.e($client['fullname'] ?? '').'</strong>
                            <br><small>'.e($client['phone'] ?? '').'</small>
                        </div>
                    </div>';
            })
            ->addColumn('product', function ($clientReview) {
                $imgProduct = $this->baseUrlAdmin.'/'.$clientReview->image_product;
                return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                 style="width:35px;height:35px;" src="'.e($imgProduct).'"/>
                        </div>
                        <div>
                            <strong>'.e($clientReview->name_product).'</strong>
                            <br><small>Mã: '.e($clientReview->code_product).'</small>
                        </div>
                    </div>';
            })
            ->editColumn('active', function ($clientReview) {
                $statusReview = status_product_review_active($clientReview->active, 'all');
                if ($clientReview->active == 1) {
                    $optionStatus = '<a class="text-center btn btn-xs btn-info">'.lang('Approved').'</a>';
                } else {
                    $optionStatus = '<div class="btn-group">
                    <button type="button" class="btn btn-white dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false"
                            style="border:1px solid '.$statusReview['color'].' !important;padding:1px 5px;">
                        <div class="label" style="color: '.$statusReview['color'].'">'.$statusReview['name'].'</div>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">';
                    foreach (status_product_review_active() as $key => $value) {
                        if ($value['id'] == $clientReview->active) continue;
                        $optionStatus .= '<li style="cursor:pointer">
                        <a style="color: '.$value['color'].'" onclick="changeActive('.$clientReview->id.','.$value['id'].')" data-id="'.$value['id'].'">'.$value['name'].'</a>
                    </li>';
                    }
                    $optionStatus .= '</ul></div>';
                }
                return '<div class="text-center">'.$optionStatus.'</div>';
            })
            ->editColumn('evaluate', function ($clientReview) {
                $str = 'Chưa đánh giá';
                if (!empty($clientReview->evaluate)) {
                    $str = '<div class="rating">';
                    for ($i = 0; $i < floor($clientReview->evaluate); $i++) {
                        $str .= '<span class="star"><i class="fa fa-star" style="font-size:12px"></i></span>';
                    }
                    if ($clientReview->evaluate < 5 && (ceil($clientReview->evaluate) / $clientReview->evaluate) != 1) {
                        $str .= '<span class="star"><i class="fa fa-star-half-o" style="font-size:12px"></i></span>';
                    }
                    $str .= '</div><div>('.$clientReview->evaluate.' sao)</div>';
                }
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('video_review', function ($clientReview) {
                $str = 'Chưa có';
                if (!empty($clientReview->video_review)) {
                    $countVideo = ReviewFile::where('id_review', $clientReview->id)->where('type', 'video')->count() + 1;
                    $countImg   = ReviewFile::where('id_review', $clientReview->id)->where('type', 'image')->count();
                    $str = '<span class="media-badge media-video">
                            <i class="fa fa fa-file-video-o"></i> '.$countVideo.' '.lang('video').'
                        </span>';
                    if ($countImg > 0) {
                        $str .= '<span class="media-badge media-photos">
                                <i class="fa fa-image"></i> '.$countImg.' '.lang('photos').'
                            </span>';
                    }
                }
                return $str;
            })
            ->editColumn('code_review', function ($clientReview) {
                return '<div class="text-center"><a class="dt-modal" href="admin/clients_review/viewReview/'.$clientReview->id.'">'.e($clientReview->code_review).'</a></div>';
            })
            ->editColumn('type_object', function ($clientReview) {
                $strTypeObject = '<a class="dt-update text-center btn btn-xs btn-info">'.lang('type_object_sign_up').'</a>';
                if($clientReview->type_object == 'transaction') {
                    $strTypeObject = '<a class="dt-update text-center btn btn-xs btn-warning">'.lang('type_object_transaction').'</a>';
                }
                return '<div class="text-center">' . $strTypeObject . '</div>';
            })
            ->editColumn('date_review', function ($clientReview) {
                return '<div class="text-center">'._dt($clientReview->date_review).'</div>';
            })
            ->editColumn('is_review', function ($clientReview) {
                return !empty($clientReview->is_review)
                    ? '<div class="text-center"><a class="text-center btn btn-xs btn-info">Đã review</a></div>'
                    : '<div class="text-center"><a class="text-center btn btn-xs btn-danger">Chưa review</a></div>';
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns([
                'options','code_review','code_product',
                'name_product','active','image_product',
                'transalations','clients','product',
                'evaluate','video_review','date_review','is_review','type_object',
            ])
            ->make(true);
    }

    public function active_review() {
        if (has_permission('clients_review','approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $id = $this->request->input('id');
        $active = $this->request->input('active') ?? 0;
        $note_rejected = $this->request->input('note_rejected');
        DB::beginTransaction();
        try {
            $clientReview = ClientsReview::find($id);

            if ($clientReview->active == 1) {
                $data['result'] = false;
                $data['message'] = lang('c_review_that_active');
                return response()->json($data);
            }

            if($active == 1) {
                if (empty($clientReview->is_review)) {
                    $data['result'] = false;
                    $data['message'] = lang('c_review_is_not_has_review');
                    return response()->json($data);
                }
                if ($clientReview->active == 1) {
                    $data['result'] = false;
                    $data['message'] = lang('c_review_that_active');
                    return response()->json($data);
                }
                $clientReview->active = 1;
                $clientReview->date_active_review = date('Y-m-d H:i:s');
                $clientReview->note_rejected = NULL;
                $clientReview->save();
                $products = Products::find($clientReview->id_product);
                if (!empty($products->id)) {
                    $products->quantity_reviews = $products->quantity_reviews + 1;
                    $products->save();
                }
                $this->request->merge([
                    'id' => $clientReview->id_client
                ]);
                $updateClient = $this->dbAccount->updateTypeClient($this->request);
                $updateClient = $updateClient->getData(true);
                $this->request->merge([
                    'id_review' => $id
                ]);
                $this->request->merge([
                    'id_product' => $clientReview->id_product
                ]);
                $updatePointReview = $this->dbAccount->updatePointReview($this->request);
                if (empty($updateClient['result'])) {
                    DB::rollBack();
                    $data['result'] = false;
                    $data['message'] = lang('c_error_is_active');
                    return response()->json($data);
                }
                $request = new Request();
                $request->merge(['id_client' => $clientReview->id_client]);
                $this->dbAccount->detail('api/reviewClassClientApi', $request); // đánh giá lại hạng user


                if(!empty($clientReview->video_review)) {
                    $videoFile = new VideoFile();
                    $videoFile->original_video = $clientReview->video_review;
                    $videoFile->rel_type = 'review';
                    $videoFile->rel_id = $clientReview->id;
                    $videoFile->id_product = $clientReview->id_product;
                    $videoFile->id_client = $clientReview->id_client;
                    $videoFile->description = $clientReview->content_evaluate;
                    $videoFile->duration = $this->GetDurationVideo(
                        storage_path('app/public/' . $clientReview->video_review)
                    );
                    $videoFile->save();
                }

            }
            else if($active == 2) {
                $clientReview->active = 2;
                $clientReview->date_active_review = date('Y-m-d H:i:s');
                $clientReview->note_rejected = $note_rejected ?? NULL;
                $clientReview->save();

                DB::table('tbl_history_cancel_review')->insert([
                    'id_sign_up_review' => $clientReview->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => get_staff_user_id(),
                    'note_rejected' => $note_rejected ?? NULL,
                    'active' => $clientReview->active,
                ]);

                $products = Products::find($clientReview->id_product);
                if (!empty($products->id)) {
                    $products->quantity_reviews = $products->quantity_reviews - 1;
                    $products->save();
                }
            }
            else if($active == 0) {
                $clientReview->active = 0;
                $clientReview->date_active_review = NULL;
                $clientReview->note_rejected = $note_rejected ?? NULL;
                $clientReview->save();
            }
            DB::commit();
            Notification::notiChangeActiveReview($clientReview->id, 'change_active_review', $clientReview->active);
            $data['result'] = true;
            $data['message'] = lang('active_review_success_' .$clientReview->active);
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }

    }

    public function modal_cancel_review($id = '') {
        $clientReview = ClientsReview::find($id);
        if(!empty($clientReview)) {
            if($clientReview->active == 1) {
                $data['result'] = false;
                $data['data'] = "<script>alert_float('error','".lang("review_active_not_active")."');</script>";
                return response()->json($data);
            }
        }
        $data['result'] = true;
        $data['data'] = view('admin.clients_review.modal_cancel',[
            'title' => lang('reason_refuse_review'),
            'id' => $clientReview->id
        ])->render();
        return response()->json($data);
    }

    public function viewReview($id = '') {
        $_locale = 'vi';
        $productReviews = ClientsReview::query()
            ->select('tbl_clients_sign_up_review.*','pt.name',
                'tbl_products.code as code_product',
                'tbl_products.image as image_product',
                'tbl_products.color_header as color_header',
                'tbl_products.background_color as background_color',
                'pt.content')
            ->where('tbl_clients_sign_up_review.id', $id)
            ->join('tbl_products', 'tbl_products.id', '=', 'tbl_clients_sign_up_review.id_product')
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'tbl_products.id')
                    ->where('pt.language', '=', $_locale);
            })->get();
        if(!empty($productReviews)) {
            foreach($productReviews as $keyProduct => $productReview) {
                $productReview->image_product = $this->baseUrlAdmin . '/' . $productReview->image_product;
                $ReviewFile = ReviewFile::where('id_review', $productReview->id)->get();
                $dataReviewFile = [];
                foreach ($ReviewFile as $key => $value) {
                    $dataReviewFile[] = [
                        'id' => $value->id,
                        'type' => $value->type,
                        'filetype' => $value->filetype,
                        'media' => $this->baseUrlAdmin . '/' . $value->media,
                        'mime_type' => $value->mime_type,
                        'name_file' => $value->name_file,
                    ];
                }
                $productReview->media_other = $dataReviewFile;
                if (!empty($productReview->video_review)) {
                    $productReview->video_review = $this->baseUrlAdmin . '/' . $productReview->video_review;
                }
                $id_product_review  = $productReview->id;
                $productReview->list_evaluate = DB::table('tbl_type_evaluate')
                    ->leftJoin('tbl_client_evaluate', function ($join) use ($id_product_review) {
                        $join->on('tbl_client_evaluate.id_evaluate', '=', 'tbl_type_evaluate.id')
                            ->where('tbl_client_evaluate.id_sign_review', '=', $id_product_review);
                    })->get();
            }
        }
        return view('admin.clients_review.view',[
            'title' => lang('c_view_detail_review_client'),
            'product_review' => $productReviews
        ]);
    }

    public function view($id = '') {
        $_locale = 'vi';
        $productReviews = ClientsReview::query()
            ->select('tbl_clients_sign_up_review.*','pt.name',
                'tbl_products.code as code_product',
                'tbl_products.image as image_product',
                'tbl_products.color_header as color_header',
                'tbl_products.background_color as background_color',
                'pt.content')
            ->where('tbl_sign_up_review.id', $id)
            ->join('tbl_products', 'tbl_products.id', '=', 'tbl_clients_sign_up_review.id_product')
            ->leftJoin('tbl_sign_up_review', function($join) {
                $join->on('tbl_sign_up_review.id', '=', 'tbl_clients_sign_up_review.id_review')
                    ->where('tbl_clients_sign_up_review.type_object', '=', 'sign_up');
            })
//            ->leftJoin('tbl_sign_up_review', 'tbl_sign_up_review.id', '=', 'tbl_clients_sign_up_review.id_review')
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'tbl_products.id')
                    ->where('pt.language', '=', $_locale);
            })->get();

        if(!empty($productReviews)) {
            foreach($productReviews as $keyProduct => $productReview) {
                $productReview->image_product = $this->baseUrlAdmin . '/' . $productReview->image_product;
                $ReviewFile = ReviewFile::where('id_review', $productReview->id)->get();
                $dataReviewFile = [];
                foreach ($ReviewFile as $key => $value) {
                    $dataReviewFile[] = [
                        'id' => $value->id,
                        'type' => $value->type,
                        'filetype' => $value->filetype,
                        'media' => $this->baseUrlAdmin . '/' . $value->media,
                        'mime_type' => $value->mime_type,
                        'name_file' => $value->name_file,
                    ];
                }
                $productReview->media_other = $dataReviewFile;
                if (!empty($productReview->video_review)) {
                    $productReview->video_review = $this->baseUrlAdmin . '/' . $productReview->video_review;
                }
            }
            $id_product_review  = $productReview->id;
            $productReview->list_evaluate = DB::table('tbl_type_evaluate')
                ->leftJoin('tbl_client_evaluate', function ($join) use ($id_product_review) {
                    $join->on('tbl_client_evaluate.id_evaluate', '=', 'tbl_type_evaluate.id')
                        ->where('tbl_client_evaluate.id_sign_review', '=', $id_product_review);
                })->get();

        }

        return view('admin.clients_review.view',[
            'title' => lang('c_view_detail_review_client'),
            'product_review' => $productReviews
        ]);
    }

    public function removeItems() {
        if (!has_permission('clients_review', 'delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $id = $this->request->input('id');
        if(!empty($id)) {
            $clientReview = ClientsReview::find($id);
            if(!empty($clientReview->is_review)) {
                $data['result'] = false;
                $data['message'] = lang('c_review_is_being_used');
                return response()->json($data);
            }


            try {
                $SignUpReview = SignUpReview::find($clientReview->id_review);
                if($SignUpReview->status > 3 && $SignUpReview->status != Config::get('constant')['cancel_review_guest']) {
                    $data['result'] = false;
                    $data['message'] = lang('c_laster_sh_not_delete');
                    return response()->json($data);
                }

                $countReview = ClientsReview::where('id_review', $SignUpReview->id)->count();
                if($countReview == 1) {
                    $this->delete($SignUpReview->id);
                }

                if(!empty($clientReview->active)) {
                    $products = Products::find($clientReview->id_product);
                    if (!empty($products->id)) {
                        $products->quantity_reviews = ($products->quantity_reviews - 1);
                        $products->save();
                    }
                }

                if($SignUpReview->status != Config::get('constant')['cancel_review_guest']) {
                    //hủy đăng ký nên hoàn lại 1 đăng ký
                    $products = Products::find($clientReview->id_product);
                    $products->count_join = ($products->count_join + 1);
                    $products->save();
                }

                $clientReview->delete();

                $listReview = ReviewFile::where('id_review', $id)->get();
                foreach ($listReview as $key => $value) {
                    $this->deleteFile($value->media);
                }
                ReviewFile::where('id_review', $id)->delete();

                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            } catch (\Exception $exception){
                $data['result'] = false;
                $data['message'] = $exception;
                return response()->json($data);
            }
        }
    }

    public function exportReviewExcel()
    {
        $_locale = 'vi';
        $id_customer = $this->request->input('id_customer');
        $filter_status_search = $this->request->input('status_search');
        $searchValue = $this->request->input('search.value');
        $listIDsearch = [];
        if(!empty($searchValue)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['search' => $searchValue]);
            $responseClientSearch = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClientSearch = $responseClientSearch->getData(true);
            if(!empty($dataClientSearch) && !empty($dataClientSearch['clients'])) {
                $dataClientSearch = $dataClientSearch['clients'];
                foreach($dataClientSearch as $value) {
                    $listIDsearch[] = $value['id'];
                }
            }
        }

        // DataTables paging
        $start  = (int) $this->request->input('start', 0);
        $length = (int) $this->request->input('length', -1);

        // DataTables ordering (an toàn với fallback)
        $orderReq = $this->request->input('order', []);
        $columns  = $this->request->input('columns', []);
        $columnIndex = isset($orderReq[0]['column']) ? (int)$orderReq[0]['column'] : 0;
        $orderBy = $columns[$columnIndex]['name'] ?? 'tbl_sign_up_review.id';
        $orderDir = isset($orderReq[0]['dir']) && in_array(strtolower($orderReq[0]['dir']), ['asc','desc'])
            ? $orderReq[0]['dir'] : 'desc';
        $baseQuery = SignUpReview::query()
            ->select('tbl_sign_up_review.*')
            ->with('clients_review')
            ->with('clients_review.products')
            ->where(function($q) use ($id_customer, $filter_status_search) {
                if (!empty($id_customer)) {
                    $q->where('tbl_sign_up_review.id_client', '=', $id_customer);
                }
                if (is_numeric($filter_status_search) && $filter_status_search >= 0) {
                    $q->where('tbl_sign_up_review.status', '=', $filter_status_search);
                }
            })
            ->when(!empty($searchValue), function ($q) use ($searchValue, $_locale, $listIDsearch) {
                $q->where(function ($w) use ($searchValue, $_locale, $listIDsearch) {
                    $w->where('tbl_sign_up_review.code_review', 'like', "%{$searchValue}%")
                        ->orWhere('tbl_sign_up_review.id', '=', $searchValue)
                        ->orWhereHas(
                            'clients_review.products',
                            function ($p) use ($searchValue, $_locale) {
                                // ràng buộc locale cho chắc
                                $p->whereExists(function ($sub) use ($_locale, $searchValue) {
                                    $sub->from('tbl_product_translations as pt')->whereColumn(
                                        'pt.id_product',
                                        'tbl_products.id'
                                    )->where('pt.language', $_locale)->where(
                                        function ($qPdocut) use ($searchValue) {
                                            $qPdocut->where('pt.name', 'like', "%{$searchValue}%");
                                            $qPdocut->orWhere('tbl_products.code', '=', $searchValue);
                                        }
                                    );
                                });
                            }
                        )
                        ->orWhereIn('tbl_sign_up_review.id_client', $listIDsearch);
                });
            });

        $idsOnPage = (clone $baseQuery)
            ->orderBy($orderBy, $orderDir)
            ->when($length > -1, function($q) use ($start, $length) {
                // DataTables dùng start/length; length = -1 nghĩa là hiển thị tất cả
                $q->skip($start)->take($length);
            })
            ->pluck('tbl_sign_up_review.id_client')
            ->unique()
            ->filter() // loại null/empty
            ->values()
            ->all();

        // Lấy thông tin khách chỉ cho các id trên TRANG HIỆN TẠI
        $dataClient = [];
        if (!empty($idsOnPage)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['list_id' => $idsOnPage]);
            unset($newRequest['search']); // tránh ảnh hưởng bởi search chung
            $responseClient = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClient = $responseClient->getData(true);
        }
        $baseQuery->orderBy($orderBy, $orderDir);
        $rows = $baseQuery->get()->toArray();
        $titles = [
            lang('dt_stt'),
            lang('c_customer_review'),
            lang('dt_phone_user'),
            lang('address'),
            lang('c_code_review'),
            lang('c_code_products'),
            lang('c_name_products'),
            lang('c_status_review'),
        ];
        $dataRows = [];
        foreach($rows as $key => $row) {
            foreach($row['clients_review'] as $k => $v) {
                $client = $dataClient['clients'][$row['id_client']] ?? [];
                $statusReview = status_product_review($row['status']);
                $dataRows[] = [
                    ($key + 1),
                    $client['fullname'] ?? '',
                    $client['phone'] ?? '',
//                    $client['address'] ?? '',
                    $row['address_receiver'] ?? '',
                    $row['code_review'],
                    $v['products'][0]['code'] ?? '',
                    $v['products'][0]['name'] ?? '',
                    $statusReview['name'] ?? '',
                ];
            }
        }
        return export_excel_table($titles, $dataRows, 'review_list.xlsx',[
                'group_by' => 4, // cột index để group: 1 = "Thành viên đăng ký"
                'group_columns' => ['A','B','C','D','E','H'] // cột được merge
            ]);
    }

    public function getClientsIntroduceReview(){

        $customer_id = $this->request->input('customer_id') ?? 0;
        $requestCustomer = new Request();
        $requestCustomer->merge(['customer_id' => $customer_id]);
        $responseCustomer = $this->dbAccount->getListCustonerIdReferral($requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);

        $arrCustomerId = $dataCustomer['data'] ?? [0];
        if(empty($arrCustomerId)){
            $arrCustomerId =[0];
        }
        $arrCustomerId = is_array($arrCustomerId) ? $arrCustomerId : [$arrCustomerId];
        $listIds = "(" . implode(",", $arrCustomerId) . ")";

        $_locale = 'vi';


        $filter_date_search = $this->request->input('date_search');
        $date_start = null;
        $date_end = null;
        if(!empty($filter_date_search)) {
            $filter_date_search = explode(' - ', $filter_date_search);
            $date_start = to_sql_date($filter_date_search[0]);
            $date_end = to_sql_date($filter_date_search[1]) .' 23:59:59';
        }

        $searchValue = $this->request->input('search.value');
        $listIDsearch = [];
        if(!empty($searchValue)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['search' => $searchValue]);
            $responseClientSearch = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClientSearch = $responseClientSearch->getData(true);
            if(!empty($dataClientSearch) && !empty($dataClientSearch['clients'])) {
                $dataClientSearch = $dataClientSearch['clients'];
                foreach($dataClientSearch as $value) {
                    $listIDsearch[] = $value['id'];
                }
            }
        }

        $start   = (int) $this->request->input('start', 0);
        $length  = (int) $this->request->input('length', 10);

        $baseQuery = DB::table('tbl_sign_up_review')
            ->select('tbl_sign_up_review.*')
            ->when(!empty($searchValue), function ($q) use ($searchValue, $listIDsearch) {
                $q->where(function ($w) use ($searchValue, $listIDsearch) {
                    $w->where('tbl_sign_up_review.code_review', 'like', "%{$searchValue}%")
                        ->orWhereIn('tbl_sign_up_review.id_client', $listIDsearch);
                });
            })
            ->join(DB::raw("
                (SELECT id_client, MIN(id) AS first_date
                 FROM tbl_sign_up_review
                 WHERE tbl_sign_up_review.id_client IN $listIds
                 GROUP BY tbl_sign_up_review.id_client) t
            "), function($join) {
                $join->on('tbl_sign_up_review.id_client', '=', 't.id_client');
                $join->on('tbl_sign_up_review.id', '=', 't.first_date');
            });
        // --- CHỈ lấy id_client trên TRANG HIỆN TẠI ---
        $idsOnPage = (clone $baseQuery)
            ->when($length > -1, function($q) use ($start, $length) {
                $q->skip($start)->take($length);
            })
            ->pluck('tbl_sign_up_review.id_client')
            ->unique()
            ->filter()
            ->values()
            ->all();

        // --- Lấy thông tin khách cho đúng các id ở trang hiện tại ---
        $dataClient = [];
        if (!empty($idsOnPage)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['list_id' => $idsOnPage]);
            unset($newRequest['search']); // tránh ảnh hưởng bởi search chung
            $responseClient = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClient = $responseClient->getData(true);
        }

        // --- Trả DataTables (áp dụng cùng order để đồng bộ) ---
        return Datatables::of($baseQuery)
            ->addColumn('avatar', function ($data) use ($dataClient) {
                $client = $dataClient['clients'][$data->id_client] ?? [];
                $dtImage = !empty($client['avatar']) ? $client['avatar'] : imgDefault();
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                return $str;
            })
            ->addColumn('code', function ($data)  use ($dataClient) {
                $client = $dataClient['clients'][$data->id_client] ?? [];
                return '<div class="text-center">'.$client['code'].'</div>';
            })
            ->addColumn('fullname', function ($data)  use ($dataClient) {
                $client = $dataClient['clients'][$data->id_client] ?? [];
                return '<div class="text-center">'.$client['fullname'].'</div>';
            })
            ->addColumn('phone', function ($data) use ($dataClient) {
                $client = $dataClient['clients'][$data->id_client] ?? [];
                return '<div class="text-center">'.$client['phone'].'</div>';
            })
            ->addColumn('type_client', function ($data)  use ($dataClient){
                $client = $dataClient['clients'][$data->id_client] ?? [];
                $classesT = 'btn-danger';
                $contentT = lang('client_type_1');
                if ($client['type_client'] == 2) {
                    $classesT = 'btn-info';
                    $contentT = lang('client_type_2');
                }
                $str = "<a class='text-center btn btn-xs $classesT'>$contentT</a>";
                return $str;
            })
            ->addColumn('created_at', function ($data) {
                return '<div class="text-center">'._dt($data->created_at).'</div>';
            })
            ->addColumn('code_review', function ($data) {
                return '<div class="text-center">'.($data->code_review).'</div>';
            })
            ->addColumn('active', function ($data) use ($dataClient){
                $client = $dataClient['clients'][$data->id_client] ?? [];
                $customer_id = $client['id'];
                $classes = $client['active'] == 1 ? "btn-info" : "btn-danger";
                $content = $client['active'] == 1 ? "Hoạt động" : "Khoá";
                $str = "<a class='dt-update text-center btn btn-xs $classes'>$content</a>";
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('updated_at')
            ->rawColumns([
                'created_at','avatar','code','fullname','phone','active','type_client','code_review'
            ])
            ->make(true);
    }
}
