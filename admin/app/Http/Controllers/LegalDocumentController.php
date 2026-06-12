<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class LegalDocumentController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        $title = lang('dt_legal_documents');
        if (!has_permission('legal_documents', 'view')) {
            access_denied();
        }
        return view('admin.legal_document.list', [
            'title' => $title,
        ]);
    }

    public function getLegalDocuments()
    {
        $_locale = $this->request->input('_locale', 'vi');
        $dtLegalDocument = LegalDocument::orderByRaw('id desc')->get();
        return Datatables::of($dtLegalDocument)
            ->addColumn('options', function ($legal_document) {
                $edit = "<a class='dt-modal' href='admin/legal_documents/detail/$legal_document->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_legal_document') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/legal_documents/delete/'.$legal_document->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_legal_document') .'</a>';
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
            ->editColumn('name', function ($legal_document) {
                $name = $legal_document->name ?? '';
                return '<div>'.$name.'</div>';
            })
             ->editColumn('active', function ($legal_document) {
                $checked = $legal_document->active == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#0050c8" data-href="admin/legal_documents/changeStatus/'.$legal_document->id.'" data-status="'.$legal_document->active.'">';
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'active'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_legal_document');
            if (!has_permission('legal_documents', 'add')) {
                access_denied(true);
            }
        } else {
            if (!has_permission('legal_documents', 'edit')) {
                access_denied(true);
            }
            $title = lang('dt_edit_legal_document');
        }
        $legal_document = LegalDocument::find($id);
        return view('admin.legal_document.detail', [
            'title' => $title,
            'id' => $id,
            'legal_document' => $legal_document,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_legal_documents,name,' . $id,
            ],
            [
                'name.required' => lang('dt_name_required'),
                'name.unique' => lang('dt_name_unique'),
            ]);
        if (!empty($id)) {
            $legal_document = LegalDocument::find($id);
        } else {
            $legal_document = new LegalDocument();
        }

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        DB::beginTransaction();
        try {
            $name = $this->request->input('name');
            $legal_document->name = $name ?? '';
            $legal_document->save();
            DB::commit();
            if ($legal_document) {
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            } else {
                DB::rollBack();
                $data['result'] = false;
                $data['message'] = lang('dt_error');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function delete($id)
    {
        if (!has_permission('legal_documents', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $legal_document = LegalDocument::find($id);
        DB::beginTransaction();
        try {
            $legal_document->delete();
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

    public function changeStatus($id)
    {
        if (!has_permission('legal_documents', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $legal_document = LegalDocument::find($id);
        try {
            $legal_document->active = $this->request->input('status') == 0 ? 1 : 0;
            $legal_document->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
