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
use App\Services\AccountService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\DataTables;

class FeedbackController extends Controller
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
        if (!has_permission('feedback','view')){
            access_denied();
        }
        return view('admin.feedback.list',[
            'title' => lang('c_feedback_client'),
        ]);
    }

    public function getTable(){
        $response = $this->dbAccount->getListFeedback($this->request);
        $data = $response->getData(true);
        $feedbacks = collect($data['data']);
        if(!empty($this->request['_locale'])) {
            App::setLocale($this->request['_locale']);
        }
        return (new CollectionDataTable($feedbacks))
            ->addColumn('options', function ($feedback) {
//                $customer_id = $feedback['id'];
//                $view = "<a href='admin/feedback/view/$customer_id'><i class='fa fa-eye'></i> " . lang('dt_view') . "</a>";
////                $edit = "<a href='admin/clients/detail/$customer_id'><i class='fa fa-pencil'></i> " . lang('c_edit_client') . "</a>";
//                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
//                <button href=\'admin/feedback/delete/' . $customer_id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
//                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
//            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_client') . '</a>';
//                $options = ' <div class="dropdown text-center">
//                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
//                             '.lang('dt_actions').'
//                            <span class="caret"></span>
//                            </button>
//                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
//                                <li style="cursor: pointer">' . $view . '</li>
//                                <li style="cursor: pointer">' . $delete . '</li>
//                            </ul>
//                        </div>';

                $delete = '<a type="button" class="po-delete btn btn-icon btn-danger" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/feedback/delete/' . $feedback['id']. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> </a>';
                return '<div class="text-center">' . $delete . '</div>';
            })
            ->addColumn('clients', function ($feedback){
                $str = '';
                if(!empty($feedback['fullname'])) {
                    $imgAvatar = $feedback['avatar'];
                    $str = '<div class="product-info">
                                <div class="product-img">
                                    <a href="'.$imgAvatar.'" data-lightbox="customer-profile-client-'.$feedback['id'].'" class="display-block mbot5">
                                        <img onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';" src="'.$imgAvatar.'" alt="image" class="img-circle b-r-1 brs-20" style="width: 35px;height: 35px">
                                    </a>
                                </div>
                                <div>
                                    <strong>' . ($feedback['fullname'] ?? '') . '</strong>
                                    <br><small>' . ($feedback['phone'] ?? '') . '</small>
                                </div>
                            </div>';
                }
                return $str;
            })
            ->editColumn('improve', function ($feedback){
                $str = '';
                if(!empty($feedback['improve'])) {
                    $improve = explode("||", $feedback['improve']);
                    foreach ($improve as $key => $value) {
                        $str .= '<a class="label label-default m-l-5">' . $value . '</a>';
                    }
                }
                return '<div class="text-center">' . $str . '</div>';
            })
            ->editColumn('file_feedback', function ($feedback){
                $str = '';
                if(!empty($feedback['file_feedback'])) {
                    $file_feedback = explode("||", $feedback['file_feedback']);
                    foreach ($file_feedback as $key => $value) {
                        $str .= '<div class="product-img inline-flex">
                                    <a href="'.$value.'" data-lightbox="customer-profile-'.$feedback['id'].'" class="display-block mbot5">
                                        <img onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';" src="'.$value.'" alt="image" class="img-circle b-r-1 brs-20" style="width: 35px;height: 35px">
                                    </a>
                                </div>';
                    }
                }
                return '<div class="text-center">' . $str . '</div>';
            })
            ->editColumn('created_at', function ($feedback) {
                $str = _dt($feedback['created_at']);
                return $str;
            })
            ->editColumn('star_like', function ($feedback) {
                $str = '<div class="product-info">
                            <div class="product-img">
                                <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';" style="width:35px;height:35px;" src="' . $feedback['img_star_like'] . '"/>
                            </div>
                            <div>
                                <small>' . (lang('lang_star_like_' . $feedback['star_like'])) . '</small>
                            </div>
                        </div>';
                return $str;
            })
            ->rawColumns(['options', 'created_at', 'clients', 'improve', 'star_like', 'file_feedback'])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function countAll(){
        $response = $this->dbAccount->countAllFeedback($this->request);
        $data = $response->getData(true);
        $data['all'] = $data['total'] ?? 0;
        $data['arrType'] = $data['arrType'] ?? [];
        return response()->json($data);
    }

    public function delete($id = 0){
        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->deleteFeedback($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

}
