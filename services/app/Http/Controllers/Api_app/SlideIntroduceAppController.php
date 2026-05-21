<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Models\SlideIntroduceApp;
use App\Models\SlideIntroduceAppTranslations;
use App\Helpers\FilesHelpers;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;
use Illuminate\Support\Facades\Validator;
use App\Services\AdminService;

class SlideIntroduceAppController extends AuthController
{
    use UploadFile;
    protected $svAdmin;
    public function __construct(Request $request, AdminService $adminService)
    {
        parent::__construct($request);
        $this->svAdmin = $adminService;
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
    }

    public function get_list(){
        $filter = $this->request->input('filter');
        $active_search = $filter['active_search'] ?? -1;
        $search = $this->request->input('search');
        $orderBy = $this->request->input('order_by', 'order_by');
        if($orderBy == 'DT_RowIndex') {
            $orderBy = 'order_by';
        }
        $orderDir = $this->request->input('order_dir', 'asc');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $query = SlideIntroduceApp::select('*', DB::raw('CONCAT("'.$this->baseUrl.'/", image) as image'))
            ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%");
            });
        }
        if ($active_search != -1 && $active_search != ''){
//            $query->where('active', $active_search);
        }
        $query->with(['transalations']);
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        $total = SlideIntroduceApp::count();
        return response()->json([
            'total' => $total,
            '_locale' => $this->request->_locale,
            'filtered' => $filtered,
            'data' => $data
        ]);
    }

    public function countAll(){
        $filter = $this->request->input('filter');
        $type_client_search = $filter['type_client_search'] ?? 0;
        $active_search = $filter['active_search'] ?? -1;

        $arrType = [
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ];

        $query = SlideIntroduceApp::where('id','!=',0);
        if ($active_search != -1 && $active_search != ''){
            $query->where('active', $active_search);
        }
        $totalAll = $query->count();

        foreach ($arrType as $key => $value){
            $type_client = $value['id'];
            $query = SlideIntroduceApp::where('id','!=',0);
//            $query->where('type_client', $type_client);
            if (($type_client_search)){
//                $query->where('type_client', $type_client_search);
            }
            if ($active_search != -1 && $active_search != ''){
                $query->where('active', $active_search);
            }
            $total = $query->count();
            $arrType[$key]['total'] = $total;
        }

        return response()->json([
            'total' => $totalAll,
            'arrType' => $arrType,
        ]);
    }

    public function detail(){
        $id = $this->request->input('id') ?? 0;
        if(!empty($id)) {
            $slide_introduce = SlideIntroduceApp::find($id);
            if (!empty($slide_introduce)) {
                $dtImage = !empty($slide_introduce->image) ? $this->baseUrl . '/' . $slide_introduce->image : null;
                $slide_introduce->image = $dtImage;

                $DataTranslations = SlideIntroduceAppTranslations::where('id_slide_introduce_app', $slide_introduce->id)->get();
                $eventStran = [];
                foreach($DataTranslations as $translation) {
                    $eventStran[$translation->language] = $translation;
                }
                $slide_introduce->translations = $eventStran;
            }
            $data['result'] = true;
            $data['data'] = $slide_introduce;
            $data['message'] = lang('get_data_success');
        }
        else {
            $data['result'] = false;
            $data['message'] = lang('get_data_fail');
        }
        return response()->json($data);
    }

    public function submit(){
        $id = $this->request->input('id') ?? 0;
        $slide_introduce = SlideIntroduceApp::find($id);
        if (empty($slide_introduce->id) && $id != 0){
            $data['result'] = false;
            $data['message'] = lang('data_not_found');
            return response()->json($data);
        }

        $EventRules = [];
        if(filled($this->request->code)) {
            $EventRules['code'] = 'unique:tbl_slide_introduce_app,code,' . $id;
        }
        $EventMessages = [
            'code.unique' => lang('code_unique'),
        ];

        $validator = Validator::make($this->request->all(), $EventRules, $EventMessages);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all()[0];
            return response()->json($data);
        }


        DB::beginTransaction();
        try {
            if(empty($slide_introduce->id)){
                $slide_introduce = new SlideIntroduceApp();
            }
            $title = $this->request->title ?? [];

            if(is_string($title)) {
                $title = json_decode($title, true);
                $ktName = false;
                foreach($title as $language => $value) {
                    if(!empty($value)) {
                        $ktName = true;

                    }
                }
                if(empty($ktName)) {
                    $data['result'] = false;
                    $data['message'] = lang('c_ls_input_one_title');
                    return response()->json($data);
                }
            }

            $content = $this->request->content ?? [];
            if(is_string($content)) {
                $content = json_decode($content, true);
            }

            $slide_introduce->title = $this->request->title_main ?? '';
            $slide_introduce->save();
            if ($slide_introduce) {
                if(!empty($title)) {
                    foreach ($title as $language => $value) {
                        DB::table('tbl_slide_introduce_app_translations')->updateOrInsert(
                            [
                                'id_slide_introduce_app' => $slide_introduce->id,
                                'language' => $language
                            ], [
                                'title' => $value,
                                'content' => $content[$language] ?? '',
                            ]
                        );
                    }
                }

                if ($this->request->hasFile('image')) {
                    if (!empty($slide_introduce->image)) {
                        $this->deleteFile($slide_introduce->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'slide_introduce/' . $slide_introduce->id, 70, 70, false);
                    $slide_introduce->image = $path;
                    $slide_introduce->save();
                }

                DB::commit();
                $data['result'] = true;
                if(!empty($id)) {
                    $data['message'] = lang('update_success');
                }
                else {
                    $data['message'] = lang('add_success');
                }
            }
            else {
                $data['result'] = false;
                if(!empty($id)) {
                    $data['message'] = lang('update_fail');
                }
                else {
                    $data['message'] = lang('add_fail');
                }
            }
            return response()->json($data);
        }
        catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function delete(){
        $id = $this->request->input('id') ?? 0;
        $slide_introduce = SlideIntroduceApp::find($id);
        if (empty($slide_introduce->id)){
            $data['result'] = false;
            $data['message'] = lang('data_not_found');
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if (!empty($slide_introduce->image)){
                $this->deleteFile($slide_introduce->image);
            }

            $slide_introduce->delete();
            DB::table('tbl_slide_introduce_app_translations')
                ->where('id_slide_introduce_app', $slide_introduce->id)
                ->delete();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('c_delete_true');
            return response()->json($data);
        }
        catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function active(){
        $id = $this->request->input('id') ?? 0;
        $slide_introduce_app = SlideIntroduceApp::find($id);
        DB::beginTransaction();
        try {
            $slide_introduce_app->active = $slide_introduce_app->active == 0 ? 1 : 0;
            $slide_introduce_app->save();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        }
        catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function order_by()
    {
        $list_order_by = $this->request->input('list_order_by');
        if (!empty($list_order_by)) {
            $list_array = [];
            foreach ($list_order_by as $id => $order_by) {
                $job_category = SlideIntroduceApp::find($id);
                $job_category->order_by = $order_by;
                $job_category->save();
            }
            $data['result'] = 1;
            $data['message'] = lang('c_order_by_true');
            return response()->json($data);
        }
        $data['result'] = 0;
        $data['message'] = lang('c_order_by_false');
        return response()->json($data);
    }

    public function get_data_slide() {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $query = SlideIntroduceApp::select('tbl_slide_introduce_app.*',
            'st.title',
            'st.content',
            'st.language',
            DB::raw('CONCAT("'.$this->baseUrl.'/", tbl_slide_introduce_app.image) as image')
        )->join('tbl_slide_introduce_app_translations as st', function($join) use ($_locale) {
            $join->on('tbl_slide_introduce_app.id', '=', 'st.id_slide_introduce_app')
                 ->where('st.language', $_locale);
        })
        ->where('tbl_slide_introduce_app.id','!=',0)
        ->where('tbl_slide_introduce_app.active', 1);
        $query->orderBy('order_by', 'asc');
        $data = $query->get();
        return response()->json([
            'data' => $data
        ]);
    }
}
