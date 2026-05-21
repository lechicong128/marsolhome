<?php

namespace App\Http\Controllers;


use app\Models\Language;
use App\Services\AccountService;
use App\Services\ServiceService;
use App\Services\PromotionService;
use App\Services\SettingCustomerClassService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class ChallengeController extends Controller
{
    protected $dbAccount;
    protected $dbService;
    use UploadFile;
    public function __construct(Request $request,AccountService $dbAccount,ServiceService $dbService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->dbAccount = $dbAccount;
        $this->dbService = $dbService;
        $this->baseUrlAdmin = config('services.storage.url');
    }

    public function get_list(){
        if (!has_permission('challenge','view')) {
            access_denied();
        }
        $title = lang('c_challenge');
        return view('admin.challenge.list',[
            'title' => $title
        ]);
    }

    public function getList()
    {
        $_locale = $this->request->input('_locale','vi');
        $response = $this->dbAccount->getList('api/challenge/getList',$this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($data) {
                $id = $data['id'];
                $edit = "<a class='dt-modal' href='admin/challenge/detail/$id'><i class='fa fa-pencil'></i> " . lang('c_challenge_edit') . "</a>";
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
            ->addColumn('product', function ($data) use ($_locale) {
                if(!empty($data['id_product'])) {
                    $product = DB::table('tbl_products as p')->select(
                        'p.code',
                        'pt.name',
                        DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", p.image) as image')
                    )->where('p.id', $data['id_product'])->leftJoin(
                            'tbl_product_translations as pt',
                            function ($join) use ($_locale) {
                                $join->on('pt.id_product', '=', 'p.id')->where('pt.language', '=', $_locale);
                            }
                        )->first();
                    return '<div class="product-info">
                            <div class="product-img">
                                <img class="img-circle"
                                     onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                     style="width:35px;height:35px;" src="' . e($product->image) . '"/>
                            </div>
                            <div>
                                <strong>' . e($product->name) . '</strong>
                                <br><small>' . lang('c_code') . ': ' . e($product->code) . '</small>
                            </div>
                        </div>';
                }
                else {
                    return '';
                }
            })
            ->addColumn('event_articles', function ($data) use ($_locale) {
                if(!empty($data['event_articles'])) {
                    return '<div class="product-info">
                            <div class="product-img">
                                <img class="img-circle"
                                     onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                     style="width:35px;height:35px;" src="' . e($data['event_articles']['image']) . '"/>
                            </div>
                            <div>
                                <strong>' . e($data['event_articles']['name']) . '</strong>
                            </div>
                        </div>';
                }
                else {
                    return '';
                }
            })
            ->editColumn('icon', function ($data) {
                $dtIcon = $data['icon'];
                return loadImage($dtIcon, '50px', 'img-rounded', '', '', '50px');
            })
            ->editColumn('name', function ($data) use ($_locale) {
                $transalations = collect($data['transalations']) ?? [];
                if($transalations->isNotEmpty()) {
                    $name = $transalations->where('language', $_locale)->first();
                }
                return '<div>'.($name['name'] ?? '').'</div>';
            })
            ->editColumn('limit_join', function ($data) use ($_locale) {
                return '<div class="text-right">'.(!empty($data['limit_join']) ? number_format($data['limit_join']) : lang('c_not_limit_join')).'</div>';
            })
            ->editColumn('type', function ($data) use ($_locale) {
                $nameType = $data['type'] == 2 ? ('<span class="label label-info">'.lang('daily').'</span>') : ('<span class="label label-success">'.lang('trademark').'</span>');
                return '<div class="text-center">'.$nameType.'</div>';
            })
            ->editColumn('days', function ($data) use ($_locale) {
                return '<div class="text-right">'.(!empty($data['days']) ? number_format($data['days']) : lang('c_not_days')).'</div>';
            })
            ->editColumn('quantity_verification', function ($data) use ($_locale) {
                return '<div class="text-right">'.number_format($data['quantity_verification']).'</div>';
            })
            ->editColumn('min_rank_join', function ($data) use ($_locale) {
                $dtIcon_rank = $data['icon_rank'];
                return '<div class="text-center m-b-5"><img style="width:20px;height:20px;" src="'.$dtIcon_rank.'"> <span>'.$data['name_rank_community'].'</span></div>';
            })
            ->editColumn('status', function ($data) {
                $statusData = status_challenge($data['status'], 'all');
                $optionStatus = '<div class="btn-group">
                    <button type="button" class="btn btn-white dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false"
                            style="min-width:150px;border:1px solid '.$statusData['color'].' !important">
                        <div class="label" style="color:'.$statusData['color'].'">'.$statusData['name'].'</div>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">';
                foreach (status_challenge() as $key => $value) {
                    $index = $data['status'];
                    $check = ($value['id'] == 2) ? 1 : 0;
                    $classes = ($key <= $index) ? 'pointer-events' : '';
//                    if ($SignReview->status == Config::get('constant')['success_review_guest']
//                        && $value['id'] != Config::get('constant')['success_review_guest']) {
//                        $classes = 'pointer-events';
//                    }
                    $optionStatus .= '<li style="cursor:pointer" class="'.$classes.'">
                        <a onclick="changeStatus('.$data['id'].','.$value['id'].','.$check.')" data-id="'.$value['id'].'">'.$value['name'].'</a>
                    </li>';
                }
                $optionStatus .= '</ul></div>';
                return $optionStatus;
            })
            ->rawColumns(['options', 'icon', 'id', 'type', 'limit_join', 'name', 'days', 'min_rank_join', 'quantity_verification', 'product', 'status', 'event_articles'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function get_detail($id = 0) {
        if(!empty($id)) {
            $_locale = App::getLocale();
            $this->request->merge(['id' => $id]);
            if (!has_permission('challenge', 'edit')) {
                access_denied(true, lang('dt_access'));
            }
            $title = lang('c_challenge_edit');
            $response = $this->dbAccount->detail('api/challenge/getDetail', $this->request);
            $data = $response->getData(true);
            $dtData = $data['data'] ?? [];
            if($dtData['type'] == 1 && $dtData['id_product'] > 0) {
                $product = DB::table('tbl_products as p')->select(
                    'p.id',
                    'pt.name',
                    'pt.content',
                    DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image')
                )->where('p.id', $dtData['id_product'])
                    ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                    $join->on('pt.id_product', '=', 'p.id')
                        ->where('pt.language', '=', $_locale);
                })->first();
            }
        } else {
            if (!has_permission('challenge', 'create')) {
                access_denied(true, lang('dt_access'));
            }
            $title = lang('c_challenge_create');
        }
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();


        $responseRank = $this->dbAccount->detail('api/get_list_rank_community', $this->request);
        $dataRank = $responseRank->getData(true);
        $rank_community = $dataRank['data'] ?? [];


        $response_event_articles = $this->dbService->detail('api/event_articles/get_list_data', $this->request);
        $data_event_articles = $response_event_articles->getData(true);
        $event_articles = $data_event_articles['data'] ?? [];

        return view('admin.challenge.detail',[
            'id' => $id,
            'title' => $title,
            'product' => $product ?? [],
            'rank_community' => $rank_community ?? [],
            'language' => $language,
            'dtData' => $dtData ?? [],
            'event_articles' => $event_articles ?? [],
        ]);
    }

    public function detail($id = 0) {
        $LanguagDefault = Language::where('is_default', 1)->first();
        $this->request->merge(['id' => $id]);
        $this->request->merge(['created_by' => get_staff_user_id()]);
        $this->request->merge(['LanguagDefault' => json_encode($LanguagDefault)]);

        $limit_join = number_unformat($this->request->input('limit_join'));
        $this->request->merge(['limit_join' => $limit_join]);

        $days = number_unformat($this->request->input('days'));
        $this->request->merge(['days' => $days]);

        $min_rank_join = number_unformat($this->request->input('min_rank_join'));
        $this->request->merge(['min_rank_join' => $min_rank_join]);

        $coin_success = number_unformat($this->request->input('coin_success'));
        $this->request->merge(['coin_success' => $coin_success]);

        $quantity_verification = number_unformat($this->request->input('quantity_verification'));
        $this->request->merge(['quantity_verification' => $quantity_verification]);

        $response = $this->dbAccount->submit('api/challenge/detail', $this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        if (!has_permission('challenge', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->delete('api/challenge/delete', $this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function countAll(){
        $response = $this->dbAccount->countAll('api/challenge/countAll', $this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function changeStatus(){
        if (!has_permission('challenge','approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $status = $this->request->input('status') ?? 0;
        $id = $this->request->input('id') ?? 0;
        $this->request->merge(['status' => $status]);
        $this->request->merge(['id' => $id]);
        $responseUpdate =  $this->dbAccount->changeStatus('api/challenge/changeStatus', $this->request);
        $dtUpdate = $responseUpdate->getData(true);
        $data = $dtUpdate['data'] ?? [];
        return response()->json($data);
    }
}
