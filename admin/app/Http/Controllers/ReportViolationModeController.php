<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\Notification;
use App\Models\ReportViolationMode;
use App\Models\ReportViolationTranslations;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Language;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReportViolationModeController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('report_violation', 'view')) {
            access_denied();
        }
        return view('admin.report_violation.list');
    }

    public function getReportViolation()
    {
        $datareport_violation = ReportViolationMode::orderByRaw('id desc')->get();
        return Datatables::of($datareport_violation)
            ->addColumn('options', function ($report_violation) {
                $edit = "<a class='dt-modal' href='admin/report_violation/detail/$report_violation->id'><i class='fa fa-pencil'></i> " . lang('ch_edit_report_violation') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/report_violation/delete/' . $report_violation->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('ch_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('ch_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_report_violation_delete') . '</a>';
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
            ->editColumn('type', function ($transaction) {
                $type = $transaction['type'] ?? null;
                $label = '';
                if ($type === 'post') {
                    $label = '<span class="label label-warning">' . lang('ch_post') . '</span>';
                } elseif ($type === 'comment') {
                    $label = '<span class="label label-success">' . lang('ch_comment') . '</span>';
                }

                return $label;
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'type'])
            ->make(true);
    }
    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('ch_add_report_violation');
            if (!has_permission('report_violation', 'add')) {
                access_denied(true);
            }
        } else {
            if (!has_permission('report_violation', 'edit')) {
                access_denied(true);
            }
            $title = lang('ch_edit_report_violation');
            $ReportViolationMode = ReportViolationMode::find($id);

            if(!empty($ReportViolationMode->id)) {
                $translations = ReportViolationTranslations::where('id_reportviolation', $id)->get();
                $data_translations = [];
                foreach ($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'name' => $translation->name,
                    ];
                }
                $ReportViolationMode->translations = $data_translations;
            }
        }
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();

        return view('admin.report_violation.detail', [
            'title' => $title,
            'id' => $id,
            'report_violation' => $ReportViolationMode ?? NULL,
            'language' => $language,
        ]);
    }
    public function submit($id = 0)
    {
        $name = $this->request->input('name');

        $data = [];
        // $validator = Validator::make(
        //     $this->request->all(),
        //     [
        //         'name' => 'required'
        //     ],
        //     [
        //         'name.required' => 'Bạn chưa nhập tên'
        //     ]
        // );
        $isName = false;
        foreach($name as $language => $value) {
            if(!empty($value)) {
                $isName = true;
                break;
            }
        }
        if(empty($isName)) {
            $data['result'] = false;
            $data['message'] = lang('pls_input_one_name_reportViolation');
            echo json_encode($data);
            die();
        }
        if (!empty($id)) {
            $ReportViolationMode = ReportViolationMode::find($id);
        } else {
            $ReportViolationMode = new ReportViolationMode();
        }
        // if ($validator->fails()) {
        //     $data['result'] = 0;
        //     $data['message'] = $validator->errors()->all();
        //     echo json_encode($data);
        //     die();
        // }
        $LanguagDefault = Language::where('is_default', 1)->first();

        try {
            DB::transaction(function () use ($ReportViolationMode, $LanguagDefault) {
                $ReportViolationMode->name = $this->request->input('name')[$LanguagDefault->code] ?? '';
                $ReportViolationMode->type = $this->request->type;
                $ReportViolationMode->save();
            });
            if (!empty($name)) {
                foreach ($name as $language => $value) {
                    DB::table('tbl_reportviolation_translations')->updateOrInsert(
                        [
                            'id_reportviolation' => $ReportViolationMode->id,
                            'language' => $language
                        ],
                        [
                            'name' => $value
                        ]
                    );
                }
            }
            return response()->json([
                'result' => true,
                'message' => lang('dt_success')
            ]);
        } catch (\Exception $exception) {
            Log::error('Error saving ReportViolationMode: ' . $exception->getMessage());
            return response()->json([
                'result' => false,
                'message' => lang('dt_error')
            ]);
        }
    }
    public function delete($id)
    {
        if (!has_permission('report_violation', 'delete')) {
            return response()->json([
                'result' => false,
                'message' => lang('dt_access')
            ]);
        }

        $ReportViolationMode = ReportViolationMode::find($id);

        if (!$ReportViolationMode) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy dữ liệu'
            ]);
        }

        try {
            // $isUsed = DB::table('tbl_post_reports')
            //     ->where('violation_id', $id)
            //     ->exists();

            // if ($isUsed) {
            //     return response()->json([
            //         'result' => false,
            //         'message' => 'Không thể xoá: Loại vi phạm đang được sử dụng.'
            //     ]);
            // }

            $ReportViolationMode->delete();

            DB::table('tbl_reportviolation_translations')->where('id_reportviolation', $id)->delete();

            return response()->json([
                'result' => true,
                'message' => lang('dt_success')
            ]);
        } catch (\Exception $exception) {
            Log::error('Error deleting ReportViolationMode: ' . $exception->getMessage());
            return response()->json([
                'result' => false,
                'message' => lang('dt_error')
            ]);
        }
    }
}
