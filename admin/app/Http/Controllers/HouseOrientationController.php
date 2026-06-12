<?php

namespace App\Http\Controllers;

use App\Models\HouseOrientation;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class HouseOrientationController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        $title = lang('dt_house_orientations');
        if (!has_permission('house_orientations', 'view')) {
            access_denied();
        }
        return view('admin.house_orientation.list', [
            'title' => $title,
        ]);
    }

    public function getHouseOrientations()
    {
        $_locale = $this->request->input('_locale', 'vi');
        $dtHouseOrientation = HouseOrientation::orderByRaw('id desc')->get();
        return Datatables::of($dtHouseOrientation)
            ->addColumn('options', function ($house_orientation) {
                $edit = "<a class='dt-modal' href='admin/house_orientations/detail/$house_orientation->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_house_orientation') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/house_orientations/delete/'.$house_orientation->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_house_orientation') .'</a>';
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
            ->editColumn('name', function ($house_orientation) {
                $name = $house_orientation->name ?? '';
                return '<div>'.$name.'</div>';
            })
             ->editColumn('active', function ($house_orientation) {
                $checked = $house_orientation->active == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#0050c8" data-href="admin/house_orientations/changeStatus/'.$house_orientation->id.'" data-status="'.$house_orientation->active.'">';
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
            $title = lang('dt_add_house_orientation');
            if (!has_permission('house_orientations', 'add')) {
                access_denied(true);
            }
        } else {
            if (!has_permission('house_orientations', 'edit')) {
                access_denied(true);
            }
            $title = lang('dt_edit_house_orientation');
        }
        $house_orientation = HouseOrientation::find($id);
        return view('admin.house_orientation.detail', [
            'title' => $title,
            'id' => $id,
            'house_orientation' => $house_orientation,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_house_orientations,name,' . $id,
            ],
            [
                'name.required' => lang('dt_name_required'),
                'name.unique' => lang('dt_name_unique'),
            ]);
        if (!empty($id)) {
            $house_orientation = HouseOrientation::find($id);
        } else {
            $house_orientation = new HouseOrientation();
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
            $house_orientation->name = $name ?? '';
            $house_orientation->save();
            DB::commit();
            if ($house_orientation) {
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
        if (!has_permission('house_orientations', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $house_orientation = HouseOrientation::find($id);
        DB::beginTransaction();
        try {
            $house_orientation->delete();
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
        if (!has_permission('house_orientations', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $house_orientation = HouseOrientation::find($id);
        try {
            $house_orientation->active = $this->request->input('status') == 0 ? 1 : 0;
            $house_orientation->save();
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
