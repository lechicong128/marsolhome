<?php

namespace App\Http\Controllers;

use app\Models\Language;
use App\Models\NoteAffiliate;
use App\Models\NoteCancel;
use App\Models\NoteCancelTranslations;
use App\Traits\UploadFile;
use Google\Service\Keep\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Matcher\Not;
use Yajra\DataTables\DataTables;

class NoteAffiliateController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }
    public function get_list(){
        if (!has_permission('note_affiliate','view')){
            access_denied();
        }
        return view('admin.note_affiliate.list',[
            'title' => lang('dt_note_affilate'),
        ]);
    }

    public function getList()
    {
        $_locale = $this->request->input('_locale','vi');
        $dtData = NoteAffiliate::orderByRaw('id desc')->get();
        return Datatables::of($dtData)
            ->addColumn('options', function ($data) {
                $edit = "<a class='dt-modal' href='admin/note_affiliate/detail/$data->id'><i class='fa fa-pencil'></i> " . lang('dt_note_affilate_edit') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/note_affiliate/delete/' . $data->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_note_affilate_delete') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('image', function ($data) {
                $dtImage = !empty($data->image) ? asset('storage/'.$data->image) : null;
                return loadImage($dtImage);
            })
            ->editColumn('title', function ($data) use ($_locale) {
                $title = $data->transalations->where('language',$_locale)->first();
                return '<div>'.($title->title ?? '').'</div>';
            })
            ->editColumn('content', function ($data) use ($_locale) {
                $content = $data->transalations->where('language',$_locale)->first();
                return '<div>'.($content->content ?? '').'</div>';
            })
            ->editColumn('type', function ($data) {
                $str = $data->type == 1 ? '<div class="label label-primary">'.lang('dt_note_review').'</div>' : '<div class="label label-default">'.lang('dt_review').'</div>';
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options','image','title','content','type'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_note_affilate_add');
            if (!has_permission('note_affiliate','add')){
                access_denied(true);
            }
        } else {
            $title = lang('dt_note_affilate_edit');
            if (!has_permission('note_affiliate','add')){
                access_denied(true);
            }
        }
        $dtData = NoteAffiliate::find($id);
        if (!empty($dtData)){
            $translations = $dtData->transalations;
            $data_translations = [];
            foreach ($translations as $translation) {
                $data_translations[$translation->language] = [
                    'title' => $translation->title,
                    'content' => $translation->content,
                ];
            }
            $dtData->translations = $data_translations;
        }
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();
        return view('admin.note_affiliate.detail', [
            'title' => $title,
            'id' => $id,
            'dtData' => $dtData,
            'language' => $language ?? null,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'title' => 'required',
                'content' => 'required',
            ]
            , [
                'title.required' => lang('dt_required_title'),
                'content.required' => lang('dt_required_content'),
            ]);

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        if (!empty($id)) {
            $dtData = NoteAffiliate::find($id);
        } else {
            $dtData = new NoteAffiliate();
        }
        DB::beginTransaction();
        $LanguagDefault = Language::where('is_default', 1)->first();
        try {
            $title = $this->request->title;
            $content = $this->request->input('content');
            $dtData->title = $title[$LanguagDefault->code] ?? '';
            $dtData->content = $content[$LanguagDefault->code] ?? '';
            $dtData->type = $this->request->input('type') ?? 1;
            $dtData->save();
            DB::commit();
            if ($dtData) {
                if ($this->request->hasFile('image')) {
                    if (!empty($dtData->image)){
                        $this->deleteFile($dtData->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'),'note_affiliate/'.$dtData->id,70,70,false);
                    $dtData->image = $path;
                    $dtData->save();
                }
                foreach($title as $language => $value) {
                    $valueContent = $content[$language] ?? '';
                    DB::table('tbl_note_affiliate_translations')->updateOrInsert(
                        [
                            'note_affiliate_id' => $dtData->id,
                            'language' => $language
                        ],
                        [
                            'title' => $value,
                            'content' => $valueContent,
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
        if (!has_permission('note_cancel','edit')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $dtData = NoteAffiliate::find($id);
        try {
            $success = $dtData->delete();
            if ($success) {
                $data['result'] = true;
                $data['message'] = lang('dt_success');
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_error');
            }
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
