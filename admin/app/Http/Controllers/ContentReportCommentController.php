<?php

namespace App\Http\Controllers;

use App\Models\ContentReportComment;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ContentReportCommentController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getContentReportComment()
    {

        $dtContentReportComment = ContentReportComment::orderByRaw('id desc')->get();
        return Datatables::of($dtContentReportComment)
            ->addColumn('options', function ($content_report_comment) {
                $edit = "<a class='dt-modal' href='admin/content_report_comment/detail/$content_report_comment->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_content_report_comment') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/content_report_comment/delete/'.$content_report_comment->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_content_report_comment') .'</a>';
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
            ->editColumn('content', function ($content_report_comment) {
                return '<div>' . e($content_report_comment->content) . '</div>';
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'content'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_content_report_comment');
        } else {
            $title = lang('dt_edit_content_report_comment');
        }
        $content_report_comment = ContentReportComment::find($id);
        return view('admin.content_report_comment.detail', [
            'title' => $title,
            'id' => $id,
            'content_report_comment' => $content_report_comment,
        ]);
    }

    public function submit($id = 0)
    {

        $validator = Validator::make($this->request->all(),
            [
                'content' => 'required',
            ],
            [
                'content.required' => lang('dt_required'),
            ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->all()
            ]);
        }

        if (!empty($id)) {
            $content_report_comment = ContentReportComment::find($id);
            if (empty($content_report_comment)) {
                return response()->json([
                    'result' => false,
                    'message' => lang('dt_error')
                ]);
            }
        } else {
            $content_report_comment = new ContentReportComment();
        }

        DB::beginTransaction();
        try {
            $content_report_comment->content = $this->request->input('content');
            $content_report_comment->save();
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => lang('dt_success')
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        $content_report_comment = ContentReportComment::find($id);
        if (empty($content_report_comment)) {
            return response()->json([
                'result' => false,
                'message' => lang('dt_error')
            ]);
        }

        DB::beginTransaction();
        try {
            $content_report_comment->delete();
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => lang('dt_success')
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
