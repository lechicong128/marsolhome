<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Script;
use App\Models\ScriptTranslations;
use App\Models\ScriptDetail;
use App\Models\ScriptDetailTranslations;
use App\Traits\NotificationTrait;
use App\Traits\UploadFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ScriptController extends Controller
{
    use UploadFile, NotificationTrait;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('script', 'view')) {
            access_denied();
        }
        $title = lang('c_script');
        return view('admin.script.list', [
            'title' => $title,
        ]);
    }

    public function getTable(DataTables $dataTables)
    {
        $status_search = $this->request->input('status_search');
        $dtScript = Script::select('id', 'name', 'icon', 'active', 'chat_default', 'active_append_product')
            ->where(function ($query) use ($status_search) {
                if ($status_search != -1) {
                    $query->where('active', $status_search);
                }
            }
        );
        return $dataTables::of($dtScript)
            ->addColumn('options', function ($dtScript) {
            $detail = "<a class='dt-modal' href='admin/script/detail/$dtScript->id'><i class='fa fa-eye'></i> " . lang(
                    'dt_view'
                ) . "</a>";
            $settings = "<a target='_blank' href='admin/script/setting/$dtScript->id'><i class='fa fa-cog'></i> ".lang('c_title_settings')."</a>";
            $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/script/delete/' . $dtScript->id . '\' class=\'btn btn-danger dt-delete\'>' . lang(
                    'dt_delete'
                ) . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete') . '</a>';
            $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $detail . '</li>
                                <li style="cursor: pointer">' . $settings . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';
            return $options;
        })->editColumn('name', function ($dtScript) {
            return "<a class='dt-modal' href='admin/script/view/$dtScript->id'>" . $dtScript->name . "</a>";
        })->editColumn('active', function ($data) {
            $checked = $data->active == 1 ? 'checked' : '';
            $event_active = $data->active == 1 ? '0' : '1';
            $str = '<input type="checkbox" ' . $checked . ' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/script/changeStatus/' . $data->id . '" data-status="' . $event_active . '">';
            return '<div class="text-center">' . $str . '</div>';
        })->editColumn('chat_default', function ($data) {
            $checked = $data->chat_default == 1 ? 'checked' : '';
            $event_active = $data->chat_default == 1 ? '0' : '1';
            $str = '<input type="checkbox" ' . $checked . ' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/script/changeStatusDefault/' . $data->id . '" data-status="' . $event_active . '">';
            return '<div class="text-center">' . $str . '</div>';
        })->editColumn('icon', function ($dtScript) {
            $icon = $dtScript->icon;
            $htmlImage = '';
            if (!empty($icon)) {
                $dtImage = !empty($dtScript->icon) ? asset('storage/' . $dtScript->icon) : null;
                $htmlImage = loadImage($dtImage, '110px', 'img-rounded');
            }
            return $htmlImage;
        })->addIndexColumn()
            ->removeColumn('created_at')
            ->rawColumns(['options', 'name', 'icon', 'active', 'chat_default'])
            ->make(true);
    }

    public function changeStatus()
    {
        if (!has_permission('script', 'approve')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $id = $this->request->id;
        $active = $this->request->status;
        $script = Script::find($id);
        DB::beginTransaction();
        try {
            $script->active = $active ?? 0;
            $script->save();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeStatusDefault()
    {
        if (!has_permission('script', 'approve')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $id = $this->request->id;
        $chat_default = $this->request->status;
        $script = Script::find($id);
        DB::beginTransaction();
        try {
            $script->chat_default = $chat_default ?? 0;
            $script->save();
            DB::commit();

            Script::where('id', '!=', $id)->update(['chat_default' => 0]);

            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function detail($id = '') {
        if (!empty($id)) {
            $title = lang('dt_detail_script');
            $dtScript = Script::find($id);
            if (empty($dtScript->id)) {
                $data['result'] = false;
                $data['message'] = lang('c_not_find_script');
                return response()->json($data);
            }
        }
        else {
            $title = lang('c_new_script');
        }
        return view('admin.script.detail', [
            'title' => $title,
            'id' => $id ?? '',
            'script' => $dtScript ?? [],
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        if (!empty($id)) {
            $script = Script::find($id);
        } else {
            $script = new Script();
        }
        if (empty($this->request->input('name'))) {
            $data['result'] = false;
            $data['message'] = lang('pls_input_name');
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $script->name = $this->request->input('name');
            $script->save();
            DB::commit();
            if ($script) {
                if ($this->request->hasFile('icon')) {
                    if (!empty($script->icon)) {
                        $this->deleteFile($script->icon);
                    }
                    $path = $this->UploadFile(
                        $this->request->file('icon'),
                        'script/' . $script->id,
                        260,
                        390
                    );
                    $script->icon = $path;
                    $script->save();
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


    public function detail_content($id = '') {
        if (!empty($id)) {
            $title = lang('c_script_content');
            $script = Script::find($id);
            if(!empty($script)) {
                $translations = ScriptTranslations::where('id_script', $id)->get();
                $data_translations = [];
                foreach ($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'content' => $translation->content,
                    ];
                }
                $script->translations = $data_translations;
            }
            $language = Language::get();
        }
        return view('admin.script.detail_content', [
            'title' => $title,
            'id' => $id ?? '',
            'script' => $script ?? '',
            'language' => $language ?? '',
        ]);
    }

    public function submit_content($id = 0)
    {
        $data = [];
        if (!empty($id)) {
            $script = Script::find($id);
        }
        else {
            $data['result'] = false;
            $data['message'] = lang('da_co_loi_xay_ra_vui_long_thu_lai');
            return response()->json($data);
        }
        if (empty($this->request->input('content'))) {
            $data['result'] = false;
            $data['message'] = lang('pls_input_content');
            return response()->json($data);
        }
        $dataContent = $this->request->input('content');
        DB::beginTransaction();
        try {
            $script->name = $this->request->input('name');
            $script->save();
            DB::commit();
            if ($script) {
                foreach ($dataContent as $language => $value) {
                    DB::table('tbl_script_translations')->updateOrInsert(
                        [
                            'id_script' => $script->id,
                            'language' => $language
                        ],
                        [
                            'content' => $dataContent[$language] ?? '',
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

    public function setting($id = 0)
    {
        $_locale = getLangSystemToClient();
        $dtScript = Script::find($id);
        if (!empty($dtScript->id)) {
            $dtScriptDetail = ScriptDetail::where('id_script', '=', $id)
                ->select('tbl_script_detail.*', 'st.content as content', 'st.language')
                ->LeftJoin('tbl_script_detail_translations as st', function ($join) use ($_locale) {
                    $join->on('tbl_script_detail.id', '=', 'st.id_script_detail')
                        ->where('st.language', '=', $_locale);
                })
                ->where('id_parent', '=', 0)->first();
            if (empty($dtScriptDetail->id)) {
                $dtScriptDetail = new ScriptDetail();
                $dtScriptDetail->id_script = $id;
                $dtScriptDetail->event_show = 'start';
                $dtScriptDetail->id_parent = 0;
                $dtScriptDetail->level = 0;
                $dtScriptDetail->created_by = get_staff_user_id();
                $dtScriptDetail->type_send = 0;
                $dtScriptDetail->save();
            }
        }
        else {
            return redirect('admin/script/list')
                ->with('error', 'not_found_script');
        }
        $title = lang('detail_script') . ': ' . $dtScriptDetail->name;
        return view('admin.script.setting', [
            'title' => $title,
            'id' => $id,
            'script' => $dtScriptDetail,
            'dtScriptDetail' => $dtScriptDetail,
        ]);
    }

    public function add_detail_parent()
    {
        $id_parent = $this->request->input('id_parent');
        if (!empty($id_parent)) {
            $script_detail_parent = ScriptDetail::find($id_parent);
            if (!empty($script_detail_parent->id)) {
                $kt_text_script_detail_child = ScriptDetail::where('id_parent', $id_parent)->where(
                    'event_show',
                    'text'
                )->first();
                if (!empty($kt_text_script_detail_child->id)) {
                    $data['result'] = false;
                    $data['alert_type'] = 'fail';
                    $data['message'] = lang('step_next_isset_text');
                    return response()->json($data);
                }
                if ($script_detail_parent->event_show != 'select') {
                    $kt_script_detail_child = ScriptDetail::where('id_parent', $id_parent);
                    if (!empty($kt_script_detail_child->id)) {
                        $data['result'] = false;
                        $data['alert_type'] = 'fail';
                        $data['message'] = lang('step_now_not_type_select_not_create');
                        return response()->json($data);
                    }
                }
                $event_show = 'select';
                if ($script_detail_parent->event_show == 'select') {
                    $event_show = 'options';
                }
                $insertDetail = new ScriptDetail();
                $insertDetail->id_script = $script_detail_parent->id_script;
                $insertDetail->event_show = $event_show;
                $insertDetail->id_parent = $id_parent;
                $insertDetail->level = ($script_detail_parent->level + 1);
                $insertDetail->created_by = get_staff_user_id();
                $insertDetail->type_send = 0;
                $success = $insertDetail->save();
                if (!empty($success)) {
                    $id = $insertDetail->id;
                    $data['result'] = true;
                    $data['alert_type'] = 'success';
                    $data['active_view_app'] = 1;
                    $data['id'] = $id;
                    $data['html'] = ViewHtmlScript($id);
                    $data['message'] = lang('create_script_succes');
                    return response()->json($data);
                }
            }
        }
        $data['success'] = false;
        $data['alert_type'] = 'fail';
        $data['message'] = lang('create_script_fail');
        return response()->json($data);
    }

    public function submit_edit_detail_child($id = '')
    {

        $LanguagDefault = Language::where('is_default', 1)->first();
        $data = $this->request->input();
        $script_detail = ScriptDetail::where('id', $id)->first();
        if (!empty($data['active_view_app'])) {
            $data['active_view_app'] = 1;
        } else {
            $data['active_view_app'] = 0;
        }
        $event_show_before = $script_detail->event_show ?? '';
        if (!empty($script_detail->id) && !empty($script_detail->id_parent)) {
            $script_detail_parent = ScriptDetail::find($script_detail->id_parent);
            if (empty($script_detail_parent->active_view_app) && $script_detail_parent->active_view_app != $data['active_view_app']) {
                $data['active_view_app'] = 0;
            }
        }

        if ($data['event_show'] != 'link' && $data['event_show'] != 'youtube' && $data['event_show'] != 'video_youtube') {
            $data['link'] = null;
        }
        if ($data['event_show'] != 'button' && $data['event_show'] != 'event_app' && $data['event_show'] != 'options') {
            $data['event_app'] = null;
        }
        if ($data['event_show'] == 'event_app') {
            $data['show_move_event'] = null;
        }
        if ($data['event_show'] != 'settimeout') {
            $data['minute_time_out'] = null;
        }
        if ($data['event_show'] == 'content_center') {
            $data['type_send'] = 2;
        }
        if (empty($data['prioritize'])) {
            $data['prioritize'] = 0;
        }
        $dataContent = !empty($data['content']) ? $data['content'] : null;
        $script_detail->content = $dataContent[$LanguagDefault->code] ?? '';

        $script_detail->name = !empty($data['name']) ? $data['name'] : null;
        $script_detail->link = !empty($data['link']) ? $data['link'] : null;
        $script_detail->event_show = !empty($data['event_show']) ? $data['event_show'] : null;
        $script_detail->event_app = !empty($data['event_app']) ? $data['event_app'] : null;
        $script_detail->minute_time_out = !empty($data['minute_time_out']) ? $data['minute_time_out'] : null;
        $script_detail->type_send = !empty($data['type_send']) ? $data['type_send'] : null;
        $script_detail->use_limit = !empty($data['use_limit']) ? $data['use_limit'] : null;
        $script_detail->prioritize = !empty($data['prioritize']) ? $data['prioritize'] : null;
        $script_detail->active_view_app = !empty($data['active_view_app']) ? $data['active_view_app'] : 0;
        $script_detail->show_move_event = !empty($data['show_move_event']) ? $data['show_move_event'] : NULL;
        $script_detail->id_event_app = !empty($data['id_event_app']) ? $data['id_event_app'] : NULL;
        $script_detail->active_start = !empty($data['active_start']) ? $data['active_start'] : 0;
        $script_detail->is_multiple = !empty($data['is_multiple']) ? $data['is_multiple'] : 0;
        $script_detail->seconds_to_wait	 = !empty($data['seconds_to_wait']) ? $data['seconds_to_wait'] : 0; // thời gian chờ
        $script_detail->end_to_reset	 = !empty($data['end_to_reset']) ? $data['end_to_reset'] : 0;// bước reset lại từ đầu
        $script_detail->end_to_web	 = !empty($data['end_to_web']) ? $data['end_to_web'] : 0;
        $success = $script_detail->save();
        if (!empty($success)) {
            if(!empty($dataContent)) {
                foreach ($dataContent as $language => $value) {
                    DB::table('tbl_script_detail_translations')->updateOrInsert(
                        [
                            'id_script_detail' => $script_detail->id,
                            'language' => $language
                        ],
                        [
                            'content' => $dataContent[$language] ?? '',
                        ]
                    );
                }
            }

            $this->get_list_id_child($id, $list_id);
            ScriptDetail::whereIn('id', $list_id)->update(
                ['active_view_app' => $data['active_view_app'] ?? 0]
            );
            if (!empty($data['use_limit'])) {
                ScriptDetail::where('id', '!=', $id)->where(
                    'id_script',
                    $script_detail->id_script
                )->update(['use_limit' => 0]);
            }
            if ($this->request->hasFile('file')) {
                if (!empty($script_detail->file)) {
                    $this->deleteFile($script_detail->file);
                }
                $path = $this->UploadFile($this->request->file('file'), 'script_detail/' . $id, 0, 0, false);
                $script_detail->file = $path;
                $script_detail->save();
            }
            $id_use_limit = ScriptDetail::where('use_limit', 1)->where(
                'id_script',
                $script_detail->id_script
            )->first();
            $data = [
                'success' => true,
                'div_limit' => (!empty($id_use_limit) ? $id_use_limit->use_limit : null),
                'alert_type' => 'success',
                'message' => lang('update_success'),
                'html' => ViewHtmlScript($id),
                'active_view_app' => !empty($data['active_view_app']) ? $data['active_view_app'] : 0,
                'id' => $id,
                'list_id' => $list_id
            ];
            return response()->json($data);
        }
        return response()->json(['success' => false, 'alert_type' => 'danger', 'message' => lang('c_update_fail')]);
    }

    public function edit_detail_child($id = '')
    {
        $data['script_detail'] = ScriptDetail::find($id);
        if(!empty($data['script_detail']->id)) {
            $translations = ScriptDetailTranslations::where('id_script_detail', $id)->get();
            $data_translations = [];
            foreach ($translations as $translation) {
                $data_translations[$translation->language] = [
                    'content' => $translation->content,
                ];
            }
            $data['script_detail']->translations = $data_translations;
        }
        if (!empty($data['script_detail']->id_parent)) {
            $script_detail_parent = ScriptDetail::find($data['script_detail']->id_parent);
        }
        if (!empty($script_detail_parent->id) && $script_detail_parent->event_show == 'select') {
            $data['event_show'] = [
                ['id' => 'options', 'name' => lang('options')]
            ];
        } else {
            if ($data['script_detail']->event_show == 'select') {
                $script_detail_child = ScriptDetail::where(
                    'id_parent',
                    $data['script_detail']->id
                )->where('id_script', $data['script_detail']->id_script)->count();
                if (!empty($script_detail_child)) {
                    $data['event_show'] = [
                        [
                            'id' => 'select',
                            'name' => lang('select')
                        ]
                    ];
                }
            } else {
                $script_detail_child = ScriptDetail::where(
                    'id_parent',
                    $data['script_detail']->id_parent
                )->where('id_script', $data['script_detail']->id_script)->count();
                if (!empty($script_detail_child) && $script_detail_child > 1) {
                    $data['event_show'] = list_event_show();
                    unset($data['event_show'][0]);
                }
            }
        }
        if (empty($data['script_detail']->id_parent)) {
            $data['event_show'] = list_event_show();
            $data['event_show'][] = ['id' => 'start', 'name' => lang('start')];
        }
        $data['type_send'] = [
            ['id' => 0, 'name' => lang('staff')],
            ['id' => 1, 'name' => lang('client')],
        ];
        $data['title'] = lang('edit');
        $data['script'] = Script::find($data['script_detail']->id_script);
        $script_detail_is_child = ScriptDetail::where('id_parent', $id)->first();
        if (!empty($script_detail_is_child->id)) {
            if (!empty($data['event_show'])) {
                foreach ($data['event_show'] as $key => $value) {
                    if ($value['id'] == 'active_helpdesk') {
                        unset($data['event_show'][$key]);
                        break;
                    }
                }
            }
        }
        if (empty($data['event_show'])) {
            $data['event_show'] = list_event_show();
        }
        $data['event_app'] = list_event_app();
        if(!empty($data['script_detail']->event_app)) {
            $data['id_event_app'] = list_view_append($data['script_detail']->event_app, 0);
        }

        $data['language'] = Language::get();
        return view('admin.script.edit_detail_child', $data);

    }

    public function delete_script()
    {
        $id = $this->request->input('id');
        if (!empty($id)) {
            $script_detail_child = ScriptDetail::where('id_parent', $id)->first();
            if (!empty($script_detail_child->id)) {
                $data['success'] = false;
                $data['alert_type'] = 'fail';
                $data['message'] = lang('step_laster_not_delete');
                return response()->json($data);
            }
            $script_detail = ScriptDetail::find($id);
            if (!empty($script_detail)) {
                $success = $script_detail->delete();
                if (!empty($success)) {
                    $data['success'] = true;
                    $data['alert_type'] = 'success';
                    $data['id_parent'] = $script_detail->id_parent;
                    $data['message'] = lang('c_delete_true');
                    return response()->json($data);
                }
            }
        }
        $data['success'] = false;
        $data['alert_type'] = 'fail';
        $data['message'] = lang('c_delete_fail');
        return response()->json($data);
    }

    public function change_status_detail()
    {
        $id = $this->request->input('id');
        $status = $this->request->input('active_view_app');
        $status = $status ?? 0;
        $script_detail = ScriptDetail::find($id);
        if (!empty($script_detail->id) && !empty($script_detail->id_parent) && !empty($status)) {
            $script_detail_parent = ScriptDetail::find($script_detail->id_parent);
            if (empty($script_detail_parent->id)) {
                $data['success'] = false;
                return response()->json($data);
            }
        }
        $script_detail->active_view_app = !empty($status) ? 1 : 0;
        $success = $script_detail->save();
        if (!empty($success)) {
            $this->get_list_id_child($id, $list_id);
            ScriptDetail::whereIn('id', $list_id)->update(['active_view_app' => !empty($status) ? 1 : 0]);

            $data['success'] = true;
            $data['list_id'] = $list_id;
            return response()->json($data);
        }
        $data['success'] = false;
        return response()->json($data);
    }

    public function delete($id = '')
    {
        if (!empty($id)) {
            $script = Script::find($id);
            if(!empty($script->icon)) {
                $this->deleteFile($script->icon);
            }
            $success = $script->delete();
            if(!empty($success)) {
                $script_detail_child = ScriptDetail::where('id_script', $id)->get();
                foreach ($script_detail_child as $key => $value) {
                    if (!empty($value->file)) {
                        $this->deleteFile($value->file);
                    }
                }
                ScriptDetail::where('id_script', $id)->delete();
                $data['result'] = true;
                $data['alert_type'] = 'success';
                $data['message'] = lang('c_delete_true');
                return response()->json($data);
            }
        }
        $data['result'] = false;
        $data['alert_type'] = 'fail';
        $data['message'] = lang('c_delete_fail');
        return response()->json($data);
    }
    private function get_list_id_child($id = '', &$array = [])
    {
        $array[] = $id;
        $script_detail = ScriptDetail::where('id_parent', $id)->get()->toArray();
        if (!empty($script_detail)) {
            foreach ($script_detail as $key => $value) {
                $this->get_list_id_child($value['id'], $array);
            }
            return $array;
        }
    }

    public function get_id_event_app() {
        $type = $this->request->input('type');
        $data = list_view_append($type);
        return response()->json($data);
    }

}
