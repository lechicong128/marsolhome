<?php

namespace App\Http\Controllers;

use App\Services\ServiceService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\DataTables;
use App\Models\Language;

class SlideIntroduceAppController extends Controller
{
    protected $dbService;
    use UploadFile;
    public function __construct(Request $request, ServiceService $serviceService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
        $this->dbService = $serviceService;
    }

    public function get_list(){
        if (!has_permission('slide_introduce_app','view')){
            access_denied();
        }
        return view('admin.slide_introduce_app.list',[
            'title' => lang('slide_introduce_app'),
        ]);
    }

    public function getTable(){
        $response = $this->dbService->getList('api/slide_introduce_app/get_list', $this->request);
        $data = $response->getData(true);
        $slide_introduce_app = collect($data['data']);
        if(!empty($this->request['_locale'])) {
            App::setLocale($this->request['_locale']);
        }

        $language = Language::get();
        $keyLanguage = [];
        foreach($language as $key => $value) {
            $keyLanguage[$value['code']] = $value;
        }

        return (new CollectionDataTable($slide_introduce_app))
            ->addColumn('options', function ($slide_introduce_app) {
                $id = $slide_introduce_app['id'];
                $edit = "<a class='dt-modal' href='admin/slide_introduce_app/detail/$id'><i class='fa fa-pencil'></i> " . lang('c_edit_slide_introduce_app') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                        <button href=\'admin/slide_introduce_app/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                        <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                    "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_slide_introduce_app') . '</a>';
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
            ->editColumn('title', function ($slide_introduce_app) use ($keyLanguage) {
                $transalations = $slide_introduce_app['transalations'] ?? [];
                $str = '';
                foreach($transalations as $key => $value) {
                    if(!empty($value['title'])) {
                        $imgLogo = $this->baseUrl  .'/'. $keyLanguage[$value['language']]->image;
                        $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span>'.$value['title'].'</span></div>';
                    }
                }
                return $str;
            })
            ->editColumn('created_at', function ($slide_introduce_app) {
                $str = _dt($slide_introduce_app['created_at']);
                return $str;
            })
            ->editColumn('active', function ($slide_introduce_app) {
                $id = $slide_introduce_app['id'];
                $checked = $slide_introduce_app['active'] == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/slide_introduce_app/active/'.$id.'" data-status="'.$slide_introduce_app['active'].'"></div>';
                return $str;
            })
            ->editColumn('image', function ($slide_introduce_app) {
                $dtImage = !empty($slide_introduce_app['image']) ? $slide_introduce_app['image'] : imgDefault();
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px" class="show_image">
                            <img src="' . $dtImage . '" alt="avatar" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                 class="img-responsive img-circle"
                                 style="width: 50px;height: 50px">
                        </div>';

                return $str;
            })
            ->addIndexColumn()
            ->rawColumns(['options','id','code', 'active', 'image', 'created_at', 'title', 'status'])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function detail($id = '0'){
        if (!has_permission('slide_introduce_app','view')){
            access_denied();
        }
        $language = Language::get();
        if (empty($id)){
            if (!has_permission('slide_introduce_app', 'add')){
                access_denied();
            }
            $title = lang('c_add_slide_introduce_app');
        }
        else {
            if (!has_permission('slide_introduce_app', 'edit')){
                access_denied();
            }
            $title = lang('c_edit_slide_introduce_app');
            $this->request->merge(['id' => $id]);
            $slide_introduce_app = $this->dbService->detail('api/slide_introduce_app/detail', $this->request);
            $slide_introduce_app = $slide_introduce_app->getData(true);
            $slide_introduce_app = $slide_introduce_app['data'] ?? [];
        }
        return view('admin.slide_introduce_app.detail',[
            'id' => $id ?? 0,
            'title' => $title,
            'slide_introduce_app' => $slide_introduce_app ?? [],
            'language' => $language ?? [],
        ]);
    }

    public function submit($id = 0) {
        $languageDefault = DB::table('tbl_language')->where('is_default', 1)->value('code');
        $title_main = $this->request->title[$languageDefault];
        if(empty($title_main)) {
            $listTitle = $this->request->title;
            foreach($listTitle as $title) {
                if(!empty($title)) {
                    $title_main = $title;
                    break;
                }
            }
        }
        if(empty($title_main)) {
            return response()->json([
                'result' => false,
                'message' => lang('c_ls_input_one_title'),
            ]);
        }
        $this->request->merge(['id' => ($id ?? 0)]);
        $this->request->merge(['languageDefault' =>$languageDefault]);
        $this->request->merge(['title_main' => $title_main]);
        $this->request->merge(['_locale' => App::getLocale()]);
        $this->request->merge(['id' => $id]);
        $response = $this->dbService->submit('api/slide_introduce_app/submit', $this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        $this->request->merge(['id' => $id]);
        $response = $this->dbService->delete('api/slide_introduce_app/delete', $this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function active($id = 0){
        $this->request->merge(['id' => $id]);
        $response = $this->dbService->active('api/slide_introduce_app/active', $this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function order_by()
    {
        $response = $this->dbService->submit('api/slide_introduce_app/order_by', $this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }
}
