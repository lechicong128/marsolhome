<?php

namespace App\Http\Controllers;


use app\Models\Language;
use App\Services\AccountService;
use App\Services\PromotionService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class PromotionController extends Controller
{
    protected $promotionService;
    use UploadFile;
    public function __construct(Request $request,PromotionService $promotionService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->promotionService = $promotionService;
    }

    public function get_list(){
        if (!has_permission('promotion','view') && !has_permission('promotion','viewown')) {
            access_denied();
        }
        $title = lang('dt_promotion');
        return view('admin.promotion.list',[
            'title' => $title
        ]);
    }

    public function getList()
    {
        $_locale = $this->request->input('_locale','vi');
        $response = $this->promotionService->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($promotion) {
                $promotion_id = $promotion['id'];
                $edit = "<a href='admin/promotion/detail/$promotion_id'><i class='fa fa-pencil'></i> " . lang('dt_edit_promotion') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/promotion/delete/'.$promotion_id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_promotion') .'</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('image', function ($promotion) {
                $dtImage = $promotion['image'];
                return loadImage($dtImage, '110px', 'img-rounded');
            })
            ->editColumn('type', function ($promotion) {
                $str = '';
                if ($promotion['type'] == 0){
                    $str = '<div class="label label-default">'.lang('dt_percent').'</div>';
                } else {
                    $str = '<div class="label label-primary">'.lang('dt_cash').'</div>';
                }
                return $str;
            })
            ->editColumn('indefinite', function ($promotion) {
                $str = $promotion['indefinite'] == 1 ? '<div class="label label-default">'.lang('dt_indefinite').'</div>' : '';
                return $str;
            })
            ->editColumn('code', function ($promotion) use ($_locale) {
                $transalations = collect($promotion['transalations']) ?? [];
                $code = $transalations->where('language',$_locale)->first();
                $str = '<div>'.(!empty($code['code']) ? $code['code'] : $promotion['code']).'</div>';
                if ($promotion['require_first_order'] == 1){
                    $str .= '<div class="label label-danger">Khuyến mãi Referral</div>';
                }
                return '<div>'.$str.'</div>';
            })
            ->editColumn('name', function ($promotion) use ($_locale) {
                $transalations = collect($promotion['transalations']) ?? [];
                $code = $transalations->where('language',$_locale)->first();
                $str = '<div>'.(!empty($code['name']) ? $code['name'] : $promotion['name']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('detail', function ($promotion) use ($_locale) {
                $transalations = collect($promotion['transalations']) ?? [];
                $code = $transalations->where('language',$_locale)->first();
                $str = '<div>'.(!empty($code['detail']) ? $code['detail'] : $promotion['detail']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('date_start', function ($promotion) {
                $str = !empty($promotion['date_start']) ? '<div>'._dthuan($promotion['date_start']).'</div>' : '';
                return $str;
            })
            ->editColumn('date_end', function ($promotion) {
                $str = !empty($promotion['date_end']) ? '<div>'._dthuan($promotion['date_end']).'</div>' : '';
                return $str;
            })
            ->editColumn('active', function ($promotion) {
                $checked = $promotion['active'] == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/promotion/changeStatus/'.$promotion['id'].'" data-status="'.$promotion['active'].'">';
                return $str;
            })
            ->rawColumns(['options','indefinite','date_start','date_end','active','type','image','code','name','detail','id'])
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
        if (empty($id)){
            if (!has_permission('promotion', 'add')) {
                access_denied(true, lang('dt_can_not_add'));
            }
            $title = lang('dt_add_promotion');
        } else {
            if (!has_permission('promotion', 'edit')) {
                access_denied(true, lang('dt_can_not_edit'));
            }
            $title = lang('dt_edit_promotion');
            $response = $this->promotionService->getDetail($this->request);
            $data = $response->getData(true);
            $dtData = $data['dtData'] ?? [];
        }
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();
        return view('admin.promotion.detail',[
            'id' => $id,
            'title' => $title,
            'language' => $language,
            'promotion' => $dtData ?? [],
        ]);
    }

    public function detail($id = 0) {
        $LanguagDefault = Language::where('is_default', 1)->first();
        $this->request->merge(['id' => $id]);
        $cash = !empty($this->request->input('cash')) ? number_unformat($this->request->input('cash')) : 0;
        $money_max = !empty($this->request->input('money_max')) ? number_unformat($this->request->input('money_max')) : 0;
        $this->request->merge(['cash' => $cash]);
        $this->request->merge(['money_max' => $money_max]);
        $this->request->merge(['created_by' => get_staff_user_id()]);
        $this->request->merge(['LanguagDefault' => json_encode($LanguagDefault)]);
        $response = $this->promotionService->detail($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        if (!has_permission('promotion', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->promotionService->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function countAll(){
        $response = $this->promotionService->countAll($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function changeStatus($id = 0){
        if (!has_permission('promotion','approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $status = $this->request->input('status') ?? 0;
        $this->request->merge(['status' => $status]);
        $this->request->merge(['id' => $id]);
        $responseUpdate =  $this->promotionService->active($this->request);
        $dtUpdate = $responseUpdate->getData(true);
        $data = $dtUpdate['data'] ?? [];
        return response()->json($data);
    }
}
