<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Models\Notification;
use App\Models\Products;
use App\Models\ScriptChat;
use App\Models\SignUpReview;
use App\Models\ClientsReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\Services\AccountService;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\CollectionDataTable;

class ClientController extends AuthController
{
    protected $serviceAccount;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->serviceAccount = $accountService;
        $this->baseUrlAdmin = config('services.storage.url');
    }

    public function getListCustomer()
    {
        $response = $this->serviceAccount->getListCustomer($this->request);
        $data = $response->getData(true);
        $clients = collect($data['data']);
        if(!empty($this->request['_locale'])) {
            App::setLocale($this->request['_locale']);
        }
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
                             '.lang('dt_actions').'
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
            ->editColumn('created_at', function ($client) {
                $str = _dt($client['created_at']);
                // $str = !empty($client['created_at']) ? date('d/m/Y', strtotime($client['created_at'])) : '';
                return $str;
            })
            ->editColumn('active', function ($client) {
                $customer_id = $client['id'];
                $classes = $client['active'] == 1 ? "btn-info" : "btn-danger";
                $content = $client['active'] == 1 ? lang('c_active') : lang('c_block');
                $str = "<a class='dt-update text-center btn btn-xs $classes' href='admin/clients/active/$customer_id'>$content</a>";
                return $str;
            })
            ->editColumn('code_introduce', function ($client) {
                return '';
            })
            ->editColumn('type_client', function ($client) {
                $classesT = 'btn-default';
                $contentT = lang('Chưa xác định');
                if($client['type_client'] == 0){
                    $classesT = 'btn-default';
                    $contentT = lang('Người xem');
                }elseif ($client['type_client'] == 1) {
                    $classesT = 'btn-info';
                    $contentT = lang('Môi giới');
                } elseif($client['type_client'] == 2){
                    $classesT = 'btn-danger';
                    $contentT = lang('Chính chủ');
                }
                $str = "<a class='text-center btn btn-xs $classesT'>$contentT</a>";
                return $str;
            })
            ->editColumn('avatar', function ($client) {
                $dtImage = !empty($client['avatar']) ? $client['avatar'] : imgDefault();
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar" onerror="this.onerror=null; this.src=\'admin/assets/images/users/avatar-1.jpg\';"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';

                return $str;
            })
            ->rawColumns(['options', 'active', 'avatar', 'type_client', 'phone', 'created_at', 'fullname'])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }


    public function getClientsIntroduce()
    {
        $response = $this->serviceAccount->getClientsIntroduce($this->request);
        $data = $response->getData(true);
        $clients = collect($data['data']);
        if(!empty($this->request['_locale'])) {
            App::setLocale($this->request['_locale']);
        }

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
                             '.lang('dt_actions').'
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
            ->editColumn('created_at', function ($client) {
                $str = _dt($client['created_at']);
                return $str;
            })
            ->editColumn('active', function ($client) {
                $customer_id = $client['id'];
                $classes = $client['active'] == 1 ? "btn-info" : "btn-danger";
                $content = $client['active'] == 1 ? lang('c_active') : lang('c_block');
                $str = "<a class='dt-update text-center btn btn-xs $classes'>$content</a>";
                return $str;
            })
            ->editColumn('code_introduce_parent', function ($client) {
                $str = '<div class="label label-default">1</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('code_introduce', function ($client) {
                $str = "<div class='text-center'><a class='text-center btn btn-xs btn-warning'>".$client['code_introduce']."</a></div>";
                return $str;
            })
            ->editColumn('type_client', function ($client) {
                $classesT = 'btn-danger';
                $contentT = lang('client_type_1');
                if ($client['type_client'] == 2) {
                    $classesT = 'btn-info';
                    $contentT = lang('client_type_2');
                }
                $str = "<a class='text-center btn btn-xs $classesT'>$contentT</a>";
                return $str;
            })
            ->editColumn('avatar', function ($client) {
                $dtImage = !empty($client['avatar']) ? $client['avatar'] : imgDefault();
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar" onerror="this.onerror=null; this.src=\'admin/assets/images/users/avatar-1.jpg\';"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';

                return $str;
            })
            ->rawColumns(['options', 'active', 'avatar', 'type_client', 'phone', 'created_at', 'fullname', 'code_introduce','code_introduce_parent'])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function countAll(){
        $response = $this->serviceAccount->countAll($this->request);
        $data = $response->getData(true);
        $data['result'] = true;
        $data['message'] = lang('dt_success');
        return response()->json($data);
    }

    public function SignUpReviewProduct() {
        app(\App\Http\Middleware\CheckLoginApi::class)
            ->getDataToken($this->request);
        $id_client = !empty($this->request->client) ? $this->request->client->id : $this->request->input('id_client', 0);
        $hidden_chat = $this->request->input('hidden_chat', 0);
        $test_app = $this->request->input('test_app', 0);
        $id_address = $this->request->input('id_address', 0);

        $name_receiver = $this->request->input('name_receiver', NULL);
        $phone_receiver = $this->request->input('phone_receiver', NULL);
        $address_receiver = $this->request->input('address_receiver', NULL);
        $email_receiver = $this->request->input('email_receiver', NULL);
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        try {
            DB::beginTransaction();
//            $id_client = $this->request->input('id_client', 0);
            $list_review = $this->request->input('list_review', []);
            if(!empty($list_review) && !is_array($list_review) && is_numeric($list_review)) {
                $list_review = [$list_review];
            }
            if (!empty($id_client) && !empty($list_review)) {
                $code_review = getReference('review');
                $signUpReview = new SignUpReview();
                $signUpReview->code_review = $code_review;
                $signUpReview->id_client = $id_client;
                $signUpReview->id_address = $id_address ?? NULL;
                $signUpReview->name_receiver = $name_receiver ?? NULL;
                $signUpReview->phone_receiver = $phone_receiver ?? NULL;
                $signUpReview->address_receiver = $address_receiver ?? NULL;
                $signUpReview->email_receiver = $email_receiver ?? NULL;
                $signUpReview->save();
                if(!empty($signUpReview->id)) {
                    updateReference('review');

                    foreach ($list_review as $key => $value) {
                        $ktSignUpReview = DB::table('tbl_clients_sign_up_review')->where(
                            'id_client', $id_client
                        )->where('id_product', $value)->first();
                        if (!empty($ktSignUpReview->id)) {
                            DB::rollBack();
                            $data['result'] = false;
                            $data['message'] = lang('you_sign_up_review_not');
                            return response()->json($data);
                        }
                        else {
                            $products = Products::find($value);
                            if(empty($test_app)) {
                                if (!empty($products->limit_people)) {
                                    if ($products->count_join >= $products->limit_people) {
                                        DB::rollBack();
                                        $data['result'] = false;
                                        $data['message'] = lang('Hết lượt đăng ký review sản phẩm');
                                        return response()->json($data);
                                    }
                                }
                            }
                            DB::table('tbl_clients_sign_up_review')->insert([
                                'id_review' => $signUpReview->id,
                                'code_review' => $code_review,
                                'id_client' => $id_client,
                                'id_product' => $value,
                            ]);
                            $products->count_join = ($products->count_join + 1);
                            $products->save();
                        }
                    }

                    $data['data']['code'] = $code_review;
                    $data['data']['info_product'] = Products::from('tbl_products as p')
                            ->select('p.id', 'p.code', 'p.is_use', 'p.color_header', 'p.background_color', 'p.limit_people', 'p.count_join', 'p.average_star', 'p.quantity_reviews', 'p.contribute',
                            'pt.name', 'pt.content', 'pt.language', DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image'), 'p.slug', 'p.date_end_promotion')
                            ->join('tbl_clients_sign_up_review', 'tbl_clients_sign_up_review.id_product', '=', 'p.id')
                        ->where('tbl_clients_sign_up_review.id_review', $signUpReview->id)
                        ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                            $join->on('pt.id_product', '=', 'p.id');
                            $join->where('pt.language', '=', $_locale);
                        })->get();
                }

                if(!empty($hidden_chat)) {
                    ScriptChat::where('id_client', $id_client)
                        ->update(['hidden' => 1]);
                }
                DB::commit();
                Notification::notiSignUpReview($signUpReview->id, 'sign_up_review');


                $data['data']['address'] = $signUpReview->address_receiver ?? '';
                $data['data']['phone'] = $signUpReview->phone_receiver ?? '';
                $data['data']['name'] = $signUpReview->name_receiver ?? '';

                $data['result'] = true;
                $data['message'] = lang('register_review_success');
                return response()->json($data);
            }

            DB::rollBack();
            $data['result'] = false;
            $data['message'] = lang('register_review_failed');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function ktReviewProduct() {
        try {
            $id_client = $this->request->input('id_client', 0);
            $list_review = $this->request->input('list_review', []);
            if (!empty($list_review)) {
                if (is_numeric($list_review)) {
                    if(!empty($id_client)) {
                        $ktSignUpReview = DB::table('tbl_clients_sign_up_review')
                            ->where(
                            'id_client',
                            $id_client
                        )->where(
                            'id_product',
                            $list_review
                        )->first();
                    }
                    if (!empty($ktSignUpReview->id)) {
                        $data['result'] = false;
                        $data['message'] = lang('Sản phẩm đã được đăng ký trước đó');
                        return response()->json($data);
                    }

                    $products = Products::find($list_review);
                    if (!empty($products->limit_people)) {
                        if ($products->count_join >= $products->limit_people) {
                            $data['result'] = false;
                            $data['message'] = lang('Đã Hết lượt đăng ký review sản phẩm');
                            return response()->json($data);
                        }
                    }
                }
                else {
                    foreach ($list_review as $key => $value) {
                        if(!empty($id_client)) {
                            $ktSignUpReview = DB::table('tbl_clients_sign_up_review')->where(
                                'id_client',
                                $id_client
                            )->where('id_product', $value)->first();
                            if (!empty($ktSignUpReview->id)) {
                                $data['result'] = false;
                                $data['message'] = lang('Sản phẩm đã được đăng ký trước đó');
                                return response()->json($data);
                            }
                        }

                        $products = Products::find($value);
                        if (!empty($products->limit_people)) {
                            if ($products->count_join >= $products->limit_people) {
                                $data['result'] = false;
                                $data['message'] = lang('Hết lượt đăng ký review sản phẩm thành công');
                                return response()->json($data);
                            }
                        }
                    }
                }
                $data['result'] = true;
                $data['message'] = lang('Kiểm tra thành công');
                return response()->json($data);
            }
            $data['result'] = false;
            $data['message'] = lang('Kiểm tra không thành công');
            return response()->json($data);
        }
        catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function appendReviewProduct() {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $id_client = $this->request->client->id;
        $id_products = $this->request->input('id_product');


        if(!empty($id_client) && !empty($id_products)) {
            if(!is_array($id_products)) {
                $id_products = [$id_products];
            }
            $ktSignUpReview = DB::table('tbl_clients_sign_up_review')->where(
                'id_client', $id_client)
                ->whereIn('id_product', $id_products
            )->first();
            if (!empty($ktSignUpReview->id)) {
                $data['result'] = false;
                $data['title'] = lang('notification');
                $data['message'] = lang('Sản phẩm đã được đăng ký trước đó');
                return response()->json($data);
            }
            foreach($id_products as $key => $id_product) {
                $products = Products::find($id_product);
                if (!empty($products->limit_people)) {
                    if ($products->count_join >= $products->limit_people) {
                        $data['result'] = false;
                        $data['title'] = lang('notification');
                        $data['message'] = $products->code . lang(' Đã Hết lượt đăng ký review sản phẩm');
                        return response()->json($data);
                    }
                }
            }

            $code_review = getReference('review');
            $signUpReview = new SignUpReview();
            $signUpReview->code_review = $code_review;
            $signUpReview->id_client = $id_client;
            $signUpReview->save();
            updateReference('review');
            foreach($id_products as $key => $id_product) {
                DB::table('tbl_clients_sign_up_review')->insert([
                    'id_review' => $signUpReview->id,
                    'code_review' => $code_review,
                    'id_client' => $id_client,
                    'id_product' => $id_product
                ]);
                $products = Products::find($id_product);
                $products->count_join = ($products->count_join + 1);
                $products->save();
            }

            Notification::notiSignUpReview($signUpReview->id, 'sign_up_review');
            $data['result'] = true;
            $data['title'] = lang('notification');
            $data['message'] = lang('Đăng ký dùng thử thành công');
            return response()->json($data);
        }

        $data['result'] = false;
        $data['title'] = lang('notification');
        $data['message'] = lang('Đăng ký không thành công');
        return response()->json($data);
    }


    //đồng bộ phiên chat cho khách hàng
    public function synchronized_vsession(){
        $id_client = $this->request->client->id;
        $vsession = $this->request->input('vsession');

        $ktScriptChat = ScriptChat::where('vsession', '!=', $vsession)
            ->where('id_client', $id_client)
            ->where('hidden', 0)
            ->first();
        if(!empty($ktScriptChat->id)) {
            ScriptChat::where('vsession', '!=', $vsession)
                ->where('id_client', $id_client)
                ->update(['hidden' => 1]);
        }

        ScriptChat::where('vsession', $vsession)
            ->where('id_client', 0)
            ->update(['id_client' => $id_client]);

        DB::table('tblsession_client')
            ->where('vsession', $vsession)
            ->where('id_client', 0)
            ->update(['id_client' => $id_client]);

        $data['result'] = true;
        $data['title'] = lang('notification');
        $data['message'] = lang('dt_access');
        return response()->json($data);

    }

    public function reset_vsession(){
        $id_client = $this->request->client->id;
        ScriptChat::where('id_client', $id_client)
            ->update(['id_client' => 0]);

        DB::table('tblsession_client')
            ->where('id_client', $id_client)
            ->update(['id_client' => 0]);

        $data['result'] = true;
        $data['title'] = lang('notification');
        $data['message'] = lang('dt_access');
        return response()->json($data);

    }


    public function cong_test() {
        dd(createPay2SPayment([
                'code' => 'DH123456',
                'amount' => '1000',
        ]));
    }
}
