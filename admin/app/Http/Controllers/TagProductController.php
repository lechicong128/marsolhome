<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\TagProduct;
use App\Models\TagProductTranslations;
use App\Traits\UploadFile;
use Google\Service\Webfonts\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Matcher\Not;
use Yajra\DataTables\DataTables;

class TagProductController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
    }

    public function get_list(){
        if (!has_permission('tag_product','view')){
            access_denied();
        }
        $type = $this->request->input('type');
        return view('admin.tag_product.list',[
            'title' => lang('dt_tag_product'),
            'type' => $type ?? 'tag_product',
        ]);
    }

    public function getTable()
    {
        $dtData = TagProduct::with(['transalations.language_detail']);
        $dtData->orderBy('id', 'desc');
        return Datatables::of($dtData)
            ->addColumn('options', function ($data) {
                $edit = "<a class='dt-modal' href='admin/tag_product/detail/$data->id'><i class='fa fa-pencil'></i> " . lang('dt_tag_product_edit') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/tag_product/delete/' . $data->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_tag_product_delete') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                                Tác vụ <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('name', function ($data) {
                $str = '';
                foreach($data->transalations as $key => $value) {
                    if(!empty($value['name'])) {
                        $imgLogo = $this->baseUrl  .'/'. $value['language_detail']['image'];
                        $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span class="inline-flex">'.$value['name'].'</span></div>';
                    }
                }
                return $str;
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'active', 'name'])
            ->make(true);
    }

    public function detail($id = 0)
    {
        if (empty($id)) {
            if (!has_permission('tag_product','add')){
                access_denied(true, lang('dt_can_not_add'));
            }
            $title = lang('dt_tag_product_add');
        } else {
            if (!has_permission('tag_product','edit')){
                access_denied(true, lang('dt_can_not_edit'));
            }
            $title = lang('dt_tag_product_edit');
            $dtData = TagProduct::find($id);
            if(!empty($dtData->id)) {
                $translations = TagProductTranslations::where('tag_product_id', $id)->get();
                $data_translations = [];
                foreach ($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'name' => $translation->name,
                    ];
                }
                $dtData->translations = $data_translations;
            }
        }
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();

        return view('admin.tag_product.detail', [
            'title' => $title,
            'id' => $id,
            'dtData' => $dtData ?? NULL,
            'language' => $language ?? NULL,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $rules = [
            'name' => 'required|unique:tbl_tag_product,name,' . $id,
        ];
        $messages = [
            'name.required' => lang('dt_input_name'),
            'name.unique' => lang('dt_name_unique'),
        ];
        if (!empty($id)) {
            $dtData = TagProduct::find($id);
        } else {
            $dtData = new TagProduct();
        }

        $validator = Validator::make($this->request->all(), $rules, $messages);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        $LanguagDefault = Language::where('is_default', 1)->first();
        DB::beginTransaction();
        try {
            $title = $this->request->input('name');
            $dtData->name = $title[$LanguagDefault->code] ?? '';
            $dtData->color = $this->request->input('color');
            $dtData->background = $this->request->input('background');
            $dtData->save();
            if ($dtData) {
                foreach($title as $language => $value) {
                    DB::table('tbl_tag_product_translations')->updateOrInsert(
                        [
                            'tag_product_id' => $dtData->id,
                            'language' => $language
                        ],
                        [
                            'name' => $value,
                        ]
                    );
                }
                DB::commit();
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function delete($id){
        if (!has_permission('tag_product','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $dtData = TagProduct::find($id);

        DB::beginTransaction();
        try {
            if (count($dtData->product) >= 1){
                $data['result'] = false;
                $data['message'] = lang('dt_exist_tag_product');
                return response()->json($data);
            }
            $success = $dtData->delete();
            if ($success) {
                $dtData->transalations()->delete();
                DB::commit();
                $data['result'] = true;
                $data['message'] = lang('dt_success');
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_error');
            }
            return response()->json($data);
        } catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }
}
