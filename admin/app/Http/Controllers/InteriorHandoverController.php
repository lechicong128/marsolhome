<?php

namespace App\Http\Controllers;

use App\Models\InteriorHandover;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class InteriorHandoverController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        $title = lang('dt_interior_handovers');
        if (!has_permission('interior_handovers', 'view')) {
            access_denied();
        }
        return view('admin.interior_handover.list', [
            'title' => $title,
        ]);
    }

    public function getInteriorHandovers()
    {
        $_locale = $this->request->input('_locale', 'vi');
        $dtInteriorHandover = InteriorHandover::orderByRaw('id desc')->get();
        return Datatables::of($dtInteriorHandover)
            ->addColumn('options', function ($interior_handover) {
                $edit = "<a class='dt-modal' href='admin/interior_handovers/detail/$interior_handover->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_interior_handover') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/interior_handovers/delete/'.$interior_handover->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_interior_handover') .'</a>';
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
            ->editColumn('name', function ($interior_handover) {
                $name = $interior_handover->name ?? '';
                return '<div>'.$name.'</div>';
            })
             ->editColumn('active', function ($interior_handover) {
                $checked = $interior_handover->active == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#0050c8" data-href="admin/interior_handovers/changeStatus/'.$interior_handover->id.'" data-status="'.$interior_handover->active.'">';
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
            $title = lang('dt_add_interior_handover');
            if (!has_permission('interior_handovers', 'add')) {
                access_denied(true);
            }
        } else {
            if (!has_permission('interior_handovers', 'edit')) {
                access_denied(true);
            }
            $title = lang('dt_edit_interior_handover');
        }
        $interior_handover = InteriorHandover::find($id);
        return view('admin.interior_handover.detail', [
            'title' => $title,
            'id' => $id,
            'interior_handover' => $interior_handover,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_interior_handovers,name,' . $id,
            ],
            [
                'name.required' => lang('dt_name_required'),
                'name.unique' => lang('dt_name_unique'),
            ]);
        if (!empty($id)) {
            $interior_handover = InteriorHandover::find($id);
        } else {
            $interior_handover = new InteriorHandover();
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
            $interior_handover->name = $name ?? '';
            $interior_handover->save();
            DB::commit();
            if ($interior_handover) {
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
        if (!has_permission('interior_handovers', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $interior_handover = InteriorHandover::find($id);
        DB::beginTransaction();
        try {
            $interior_handover->delete();
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
        if (!has_permission('interior_handovers', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $interior_handover = InteriorHandover::find($id);
        try {
            $interior_handover->active = $this->request->input('status') == 0 ? 1 : 0;
            $interior_handover->save();
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
