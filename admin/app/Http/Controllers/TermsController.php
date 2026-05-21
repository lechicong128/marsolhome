<?php

namespace App\Http\Controllers;

use App\Models\Terms;
use App\Models\TermsTranslations;
use App\Models\Language;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Matcher\Not;
use Yajra\DataTables\DataTables;

class TermsController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
    }

    public function get_list(){
        if (!has_permission('terms','view')){
            access_denied();
        }
        $type = $this->request->input('type');
        return view('admin.terms.list',[
            'title' => lang('terms'),
            'type' => $type ?? 'terms',
        ]);
    }

    public function getTable()
    {
        $types_terms = $this->request->input('types_terms') ?? 'terms';
        $dtTerms = Terms::with(['transalations.language_detail']);
        if (!empty($types_terms)) {
            if($types_terms == 'question') {
                $type = 2;
            }
            else {
                $type = 1;
            }
            $dtTerms = $dtTerms->where('type', $type);
        }
        $dtTerms->orderBy('order_by', 'asc')->orderBy('id', 'desc');
        return Datatables::of($dtTerms)
            ->addColumn('options', function ($terms) use ($types_terms, $type) {
                $edit = "<a class='dt-modal' href='admin/terms/detail/$terms->id?types_terms=$types_terms'><i class='fa fa-pencil'></i> " . lang('edit_terms' . $types_terms) . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/terms/delete/' . $terms->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('delete_terms' . $types_terms) . '</a>';
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
            ->editColumn('active', function ($data) {
                $checked = $data->active == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/terms/changeStatus/'.$data->id.'" data-status="'.$data->active.'"></div>';
                return $str;
            })

            ->addColumn('title', function ($data) {
                $str = '';
                foreach($data->transalations as $key => $value) {
                    if(!empty($value['title'])) {
                        $imgLogo = $this->baseUrl  .'/'. $value['language_detail']['image'];
                        $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span class="inline-flex">'.$value['title'].'</span></div>';
                    }
                }
                return $str;
            })
            ->addColumn('content', function ($data) {
                $str = '';
                foreach($data->transalations as $key => $value) {
                    if(!empty($value['content'])) {
                        $imgLogo = $this->baseUrl  .'/'. $value['language_detail']['image'];
                        $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span class="inline-flex">'.$value['content'].'</span></div>';
                    }
                }
                return $str;
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'active', 'title','content', 'order_by'])
            ->make(true);
    }

    public function changeStatus($id) {
        $terms = Terms::find($id);
        try {
            $terms->active = $this->request->input('status') == 0 ? 1 : 0;
            $terms->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function detail($id = 0)
    {
        $types_terms = $this->request->input('types_terms') ?? 1;
        if (!empty($types_terms)) {
            if($types_terms == 'question') {
                $type = 2;
            }
            else {
                $type = 1;
            }
        }
        if (empty($id)) {
            $title = lang('add_terms'.$types_terms);
        } else {
            $title = lang('edit_terms'.$types_terms);
            $terms = Terms::find($id);
            if(!empty($terms->id)) {
                $translations = TermsTranslations::where('id_terms', $id)->get();
                $data_translations = [];
                foreach ($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'title' => $translation->title,
                        'content' => $translation->content,
                    ];
                }
                $terms->translations = $data_translations;
            }
        }
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();


        return view('admin.terms.detail', [
            'title' => $title,
            'type' => $type,
            'types_terms' => $types_terms,
            'id' => $id,
            'terms' => $terms ?? NULL,
            'language' => $language ?? NULL,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        if (!empty($id)) {
            $terms = Terms::find($id);
            $validator = Validator::make($this->request->all(),
                [
                    'title' => 'required',
                ],
                [
                    'content.required' => 'Bạn chưa nhập tên',
                ]
            );
        } else {
            $validator = Validator::make($this->request->all(),
                [
                    'title' => 'required',
                ],
                [
                    'content.required' => 'Bạn chưa nhập tên',
                ]
            );

            $terms = new Terms();
        }

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        $LanguagDefault = Language::where('is_default', 1)->first();
        DB::beginTransaction();
        try {
            $title = $this->request->input('title');
            $terms->title = $title[$LanguagDefault->code] ?? '';
            $terms->type = $this->request->input('type') ?? '1';
            $terms->save();
            DB::commit();
            if ($terms) {
                foreach($title as $language => $value) {
                    DB::table('tbl_terms_translations')->updateOrInsert(
                        [
                            'id_terms' => $terms->id,
                            'language' => $language
                        ],
                        [
                            'title' => $value,
                            'content' => $this->request->input('content')[$language] ?? '',
                        ]
                    );
                }
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
        $Terms = Terms::find($id);

        try {
            $success = $Terms->delete();
            if ($success) {
                DB::table('tbl_terms_translations')->where('id_terms', $id)->delete();
                $data['result'] = true;
                $data['message'] = lang('dt_success');
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_error');
            }
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function order_by()
    {
        $list_order_by = $this->request->input('list_order_by');
        if (!empty($list_order_by)) {
            $list_array = [];
            foreach ($list_order_by as $id => $order_by) {
                $terms = Terms::find($id);
                $terms->order_by = $order_by;
                $terms->save();
            }
            $data['result'] = 1;
            $data['message'] = lang('c_order_by_true');
            return response()->json($data);
        }
        $data['result'] = 0;
        $data['message'] = lang('c_order_by_false');
        return response()->json($data);
    }
}
