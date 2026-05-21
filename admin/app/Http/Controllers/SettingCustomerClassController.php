<?php

namespace App\Http\Controllers;


use app\Models\Language;
use App\Services\AccountService;
use App\Services\PromotionService;
use App\Services\SettingCustomerClassService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class SettingCustomerClassController extends Controller
{
    protected $customerClassService;
    use UploadFile;
    public function __construct(Request $request,SettingCustomerClassService $customerClassService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->customerClassService = $customerClassService;
    }

    public function get_list(){
        if (!has_permission('setting_customer_class','view')) {
            access_denied();
        }
        $title = lang('dt_setting_customer_class');
        return view('admin.setting_customer_class.list',[
            'title' => $title
        ]);
    }

    public function getList()
    {
        $_locale = $this->request->input('_locale','vi');
        $response = $this->customerClassService->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($data) {
                $id = $data['id'];
                $edit = "<a class='dt-modal' href='admin/setting_customer_class/detail/$id'><i class='fa fa-pencil'></i> " . lang('dt_setting_customer_class_edit') . "</a>";
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                        </div>';

                return $options;
            })
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('icon', function ($data) {
                $dtIcon = $data['icon'];
                return loadImage($dtIcon, '100px', 'img-rounded', '', '', '50px');
            })
            ->editColumn('image', function ($data) {
                $dtImage = $data['image'];
                return loadImage($dtImage, '110px', 'img-rounded');
            })
            ->editColumn('image_background', function ($data) {
                $dtImageBackground = $data['image_background'];
                return loadImage($dtImageBackground, '250px', 'img-rounded', '', '', '150px');
            })
            ->editColumn('rule', function ($data) use ($_locale) {
                $rule = $data['rule'] ?? [];
                $htmlRule = '';
                if (!empty($rule)) {
                    foreach($data['transalations'] as $key => $value) {
                        if ($value['language'] == $_locale) {
                            $content_conditions = $value['content_conditions'];
                            foreach ($rule as $k => $v) {
                                if ($v['type'] == 'review') {
                                    $content_conditions = str_replace('{review}', $v['rule'], $content_conditions);
                                } else {
                                    if ($v['type'] == 'affiliate') {
                                        $content_conditions = str_replace('{affiliate}', $v['rule'], $content_conditions);
                                    }
                                }
                                $htmlRule = $content_conditions;
                            }
                        }
                    }
                }
                return '<div style="white-space: break-spaces;">' . $htmlRule . '</div>';
            })
            ->editColumn('benefits', function ($data) use ($_locale) {
                $transalations = collect($data['transalations']) ?? [];
                $benefits = $transalations->where('language',$_locale)->first();
                $data['benefits'] = str_replace('{percent}',$data['percent'].' %',$benefits['benefits']);
                return '<div style="white-space: break-spaces;">'.$data['benefits'].'</div>';
            })
            ->editColumn('name', function ($data) use ($_locale) {
                $transalations = collect($data['transalations']) ?? [];
                $name = $transalations->where('language',$_locale)->first();
                return '<div>'.$name['name'].'</div>';
            })
            ->rawColumns(['options','image','icon','image_background','rule','id','benefits','name'])
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
        if (!has_permission('setting_customer_class', 'edit')) {
            access_denied(true, lang('dt_access'));
        }
        $title = lang('dt_setting_customer_class_edit');
        $response = $this->customerClassService->getDetail($this->request);
        $data = $response->getData(true);
        $dtData = $data['dtData'] ?? [];
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();
        return view('admin.setting_customer_class.detail',[
            'id' => $id,
            'title' => $title,
            'language' => $language,
            'dtData' => $dtData ?? [],
        ]);
    }

    public function detail($id = 0) {
        $LanguagDefault = Language::where('is_default', 1)->first();
        $this->request->merge(['id' => $id]);
        $this->request->merge(['created_by' => get_staff_user_id()]);
        $this->request->merge(['LanguagDefault' => json_encode($LanguagDefault)]);
        $response = $this->customerClassService->detail($this->request);
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
