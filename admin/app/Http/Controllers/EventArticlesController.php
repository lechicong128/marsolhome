<?php

namespace App\Http\Controllers;

use App\Models\CategoryProducts;
use App\Models\ProductCategory;
use App\Models\GroupPermission;
use App\Models\Language;
use App\Models\Permission;
use App\Models\Products;
use App\Models\ProductTranslations;
use App\Services\ServiceService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\DataTables;

class EventArticlesController extends Controller
{
    protected $dbService;
    use UploadFile;
    public function __construct(Request $request, ServiceService $serviceService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
        $this->baseUrl = config('services.storage.url');
        $this->dbService = $serviceService;
    }

    public function get_list(){
        if (!has_permission('event_articles','view')){
            access_denied();
        }
        return view('admin.event_articles.list',[
            'title' => lang('list_event_articles'),
        ]);
    }

    public function detail($id = ''){
        if (!has_permission('event_articles','view')){
            access_denied();
        }
        $language = Language::get();
        if (empty($id)){
            if (!has_permission('event_articles', 'add')){
                access_denied();
            }
            $title = lang('c_add_event_articles');
        }
        else {
            if (!has_permission('event_articles', 'edit')){
                access_denied();
            }
            $title = lang('c_edit_event_articles');
            $this->request->merge(['id' => $id]);
            $event_articles = $this->dbService->detailEventArticles($this->request);
            $event_articles = $event_articles->getData(true);
            $event_articles = $event_articles['data'] ?? [];

            if(!empty($event_articles['product_id'])) {
                $listProduct = Products::select('id',
                    DB::raw('CONCAT("'.$this->baseUrl.'/", image) as image'),
                    DB::raw('CONCAT(code, " - ", name) as name')
                )->whereIn('id', $event_articles['product_id'])->get();
            }
        }
        return view('admin.event_articles.detail',[
            'id' => $id ?? 0,
            'title' => $title,
            'event_articles' => $event_articles ?? [],
            'language' => $language ?? [],
            'products' => $listProduct ?? [],
        ]);
    }

    public function getTable(){
        $response = $this->dbService->getListEventArticles($this->request);
        $data = $response->getData(true);
        $EventArticles = collect($data['data']);
        if(!empty($this->request['_locale'])) {
            App::setLocale($this->request['_locale']);
        }

        $language = Language::get();
        $keyLanguage = [];
        foreach($language as $key => $value) {
            $keyLanguage[$value['code']] = $value;
        }

        return (new CollectionDataTable($EventArticles))
            ->addColumn('options', function ($EventArticles) {
                $id = $EventArticles['id'];
                $edit = "<a href='admin/event_articles/detail/$id'><i class='fa fa-pencil'></i> " . lang('c_edit_event_articles') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                        <button href=\'admin/event_articles/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                        <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                    "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_event_articles') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             '.lang('dt_actions').'
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('name', function ($EventArticles) use ($keyLanguage) {
                $transalations = $EventArticles['transalations'] ?? [];
                $str = '';
                foreach($transalations as $key => $value) {
                    if(!empty($value['name'])) {
                        $imgLogo = $this->baseUrl  .'/'. $keyLanguage[$value['language']]->image;
                        $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span>'.$value['name'].'</span></div>';
                    }
                }
                return $str;
            })
            ->editColumn('code', function ($EventArticles) {
                $str = '<div>'.$EventArticles['code'].'</div>';
                if($EventArticles['type_event_articles'] == 1) {
                    $str .= '<div><span class="label label-info">'.lang('event').'</span></div>';
                }
                else if($EventArticles['type_event_articles'] == 2) {
                    $str .= '<div><span class="label label-primary">'.lang('challenge').'</span></div>';
                }
                return $str;
            })
            ->editColumn('created_at', function ($EventArticles) {
                $str = _dt($EventArticles['created_at']);
                return $str;
            })
            ->editColumn('active', function ($EventArticles) {
                $id = $EventArticles['id'];
                $classes = $EventArticles['active'] == 1 ? "btn-info" : "btn-danger";
                $content = $EventArticles['active'] == 1 ? lang('c_active') : lang('c_block');
                $str = "<a class='dt-update text-center btn btn-xs $classes' href='admin/event_articles/active/$id'>$content</a>";
                return $str;
            })
            ->editColumn('is_hot', function ($EventArticles) {
                $checked = $EventArticles['is_hot'] == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/event_articles/changeIsHot/'.$EventArticles['id'].'" data-status="'.$EventArticles['is_hot'].'"></div>';
                return $str;
            })
            ->editColumn('count_view', function ($EventArticles) {
                $str = '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-warning">'.$EventArticles['count_view'].'</a></div>';
                return $str;
            })
            ->editColumn('count_join', function ($EventArticles) {
                $str = '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-info">'.$EventArticles['count_join'].'</a></div>';
                return $str;
            })
            ->addColumn('status_now', function ($EventArticles) {
                $str = '';
                $statusNow = eventStatus($EventArticles['date_start_event'], $EventArticles['date_end_event']);
                if($statusNow['id'] == 1) {
                    $str = '<div class="text-center">
                                <a class="text-center btn btn-xs status_now_1">'.$statusNow['name'].'</a>
                            </div>';
                }
                else if($statusNow['id'] == 2) {
                    $str = '<div class="text-center"><a class="text-center btn btn-xs status_now_2">'.$statusNow['name'].'</a></div>';
                }
                else if($statusNow['id'] == 3) {
                    $str = '<div class="text-center"><a class="text-center btn btn-xs status_now_3">'.$statusNow['name'].'</a></div>';
                }
                return $str;
            })
            ->editColumn('image', function ($EventArticles) {
                $dtImage = !empty($EventArticles['image']) ? $EventArticles['image'] : imgDefault();
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px" class="show_image">
                            <img src="' . $dtImage . '" alt="avatar" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                 class="img-responsive img-circle"
                                 style="width: 50px;height: 50px">

                        </div>';

                return $str;
            })
            ->addIndexColumn()
            ->rawColumns(['options','id','code', 'active', 'image', 'created_at', 'name', 'slug', 'is_hot', 'count_view', 'count_join', 'status_now'])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function submit($id = 0) {

        $languageDefault = DB::table('tbl_language')->where('is_default', 1)->value('code');
        $name_main = $this->request->name[$languageDefault];
        if(empty($name_main)) {
            $listName = $this->request->name;
            foreach($listName as $name) {
                if(!empty($name)) {
                    $name_main = $name;
                    break;
                }
            }
        }
        if(empty($name_main)) {
            return response()->json([
                'result' => false,
                'message' => lang('c_ls_input_one_name'),
            ]);
        }
        $this->request->merge(['id' => ($id ?? 0)]);
        $this->request->merge(['prizes' => number_unformat($this->request->prizes)]);
        $this->request->merge(['total_money_prizes' => number_unformat($this->request->total_money_prizes)]);
        $this->request->merge(['total_product' => number_unformat($this->request->total_product)]);
        $this->request->merge(['languageDefault' =>$languageDefault]);
        $this->request->merge(['name_main' => $name_main]);
        $this->request->merge(['_locale' => App::getLocale()]);
        $response = $this->dbService->SubmitEventArticles($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        $this->request->merge(['id' => $id]);
        $response = $this->dbService->deleteEventArticles($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function active($id = 0){
        $this->request->merge(['id' => $id]);
        $response = $this->dbService->activeEventArticles($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function changeIsHot ($id = 0){
        $this->request->merge(['id' => $id]);
        $response = $this->dbService->ChangIsHotEventArticles($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }
}
