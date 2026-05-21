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

class RankCommunityController extends Controller
{
    protected $dbAccount;
    use UploadFile;
    public function __construct(Request $request,AccountService $dbAccount)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->dbAccount = $dbAccount;
    }

    public function get_list(){
        if (!has_permission('rank_community','view')) {
            access_denied();
        }
        $title = lang('dt_rank_community');
        return view('admin.rank_community.list',[
            'title' => $title
        ]);
    }

    public function getList()
    {
        $_locale = $this->request->input('_locale','vi');
        $response = $this->dbAccount->getList('api/rank_community/getList',$this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($data) {
                $id = $data['id'];
                $edit = "<a class='dt-modal' href='admin/rank_community/detail/$id'><i class='fa fa-pencil'></i> " . lang('dt_rank_community_edit') . "</a>";
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
                return loadImage($dtIcon, '50px', 'img-rounded', '', '', '50px');
            })
            ->editColumn('image', function ($data) {
                $dtImage = $data['image'] ?? '';
                return loadImage($dtImage, '110px', 'img-rounded');
            })
            ->editColumn('challenges_next_rank', function ($data) use ($_locale) {
                $transalations = collect($data['transalations']) ?? [];
                if($transalations->isNotEmpty()) {
                    $content_conditions = $transalations->where('language', $_locale)->first();
                    $data['challenges_next_rank'] = str_replace(
                        '{challenges_next_rank}',
                        $data['challenges_next_rank'],
                        $content_conditions['content_conditions']
                    );
                }
                return '<div style="white-space: break-spaces;">'.$data['challenges_next_rank'].'</div>';
            })
            ->editColumn('name', function ($data) use ($_locale) {
                $transalations = collect($data['transalations']) ?? [];
                if($transalations->isNotEmpty()) {
                    $name = $transalations->where('language', $_locale)->first();
                }
                return '<div>'.($name['name'] ?? '').'</div>';
            })
            ->editColumn('betting_limit', function ($data) use ($_locale) {
                return '<div class="text-right">'.number_format($data['betting_limit']).'</div>';
            })
            ->rawColumns(['options','image','icon','id','challenges_next_rank','name','betting_limit'])
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
        if (!has_permission('rank_community', 'edit')) {
            access_denied(true, lang('dt_access'));
        }
        $title = lang('dt_rank_community_edit');
        $response = $this->dbAccount->detail('api/rank_community/getDetail', $this->request);
        $data = $response->getData(true);
        $dtData = $data['data'] ?? [];
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();
        return view('admin.rank_community.detail',[
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
        $betting_limit = number_unformat($this->request->input('betting_limit'));
        $this->request->merge(['betting_limit' => $betting_limit]);
        $response = $this->dbAccount->submit('api/rank_community/detail', $this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        if (!has_permission('rank_community', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->delete('api/rank_community/delete', $this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function countAll(){
        $response = $this->dbAccount->countAll('api/rank_community/countAll', $this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

//    public function changeStatus($id = 0){
//        if (!has_permission('promotion','approve')){
//            $data['result'] = false;
//            $data['message'] = lang('dt_access');
//            return response()->json($data);
//        }
//        $status = $this->request->input('status') ?? 0;
//        $this->request->merge(['status' => $status]);
//        $this->request->merge(['id' => $id]);
//        $responseUpdate =  $this->dbAccount->active($this->request);
//        $dtUpdate = $responseUpdate->getData(true);
//        $data = $dtUpdate['data'] ?? [];
//        return response()->json($data);
//    }
}
