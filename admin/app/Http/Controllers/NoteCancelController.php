<?php

namespace App\Http\Controllers;

use app\Models\Language;
use App\Models\NoteCancel;
use App\Models\NoteCancelTranslations;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Matcher\Not;
use Yajra\DataTables\DataTables;

class NoteCancelController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getNoteCancel()
    {
        $dtNoteCancel = NoteCancel::orderByRaw('id desc')->get();
        return Datatables::of($dtNoteCancel)
            ->addColumn('options', function ($note_cancel) {
                $edit = "<a class='dt-modal' href='admin/note_cancel/detail/$note_cancel->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_note_cancel') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/note_cancel/delete/' . $note_cancel->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_note_cancel') . '</a>';
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
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_note_cancel');
        } else {
            $title = lang('dt_edit_note_cancel');
        }
        $noteCancel = NoteCancel::find($id);
        if (!empty($noteCancel)){
            $translations = NoteCancelTranslations::where('note_cancel_id', $id)->get();
            $data_translations = [];
            foreach ($translations as $translation) {
                $data_translations[$translation->language] = [
                    'note' => $translation->note,
                ];
            }
            $noteCancel->translations = $data_translations;
        }
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();
        return view('admin.note_cancel.detail', [
            'title' => $title,
            'id' => $id,
            'noteCancel' => $noteCancel,
            'language' => $language ?? null,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'note' => 'required',
            ]
            , [
                'note.required' => lang('dt_note_cancel_require'),
            ]);

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        if (!empty($id)) {
            $noteCancel = NoteCancel::find($id);
        } else {
            $noteCancel = new NoteCancel();
        }
        DB::beginTransaction();
        $LanguagDefault = Language::where('is_default', 1)->first();
        try {
            $note = $this->request->note;
            $noteCancel->note = $note[$LanguagDefault->code] ?? '';
            $noteCancel->save();
            DB::commit();
            if ($noteCancel) {
                foreach($note as $language => $value) {
                    DB::table('tbl_note_cancel_translations')->updateOrInsert(
                        [
                            'note_cancel_id' => $noteCancel->id,
                            'language' => $language
                        ],
                        [
                            'note' => $value,
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
        $noteCancel = NoteCancel::find($id);
        try {
            $success = $noteCancel->delete();
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
