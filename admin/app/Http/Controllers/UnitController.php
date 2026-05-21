<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use app\Models\Language;
use App\Models\Notification;
use App\Models\PaymentMode;
use App\Models\PaymentModeTranslations;
use App\Models\ReferralLevel;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Mail;
use App\Models\Unit;

class UnitController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('unit','view')){
            access_denied();
        }
        return view('admin.unit.list');
    }

    public function getUnit()
    {
        $_locale = $this->request->input('_locale','vi');
        $dtUnit = Unit::orderByRaw('id desc')->get();
        return Datatables::of($dtUnit)
            ->addColumn('options', function ($unit) {
                $edit = "<a class='dt-modal' href='admin/unit/detail/$unit->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_unit') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/unit/delete/'.$unit->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_unit') .'</a>';
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
            ->editColumn('name', function ($unit) use ($_locale) {
                $name = $unit->name ?? '';
                return '<div>'.$name.'</div>';
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options','name'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_unit');
            if (!has_permission('unit','add')){
                access_denied(true);
            }
        } else {
            if (!has_permission('unit','edit')){
                access_denied(true);
            }
            $title = lang('dt_edit_unit');
        }
        $unit = Unit::find($id);
        return view('admin.unit.detail', [
            'title' => $title,
            'id' => $id,
            'unit' => $unit
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_unit,name,' . $id,
            ]
            , [
                'name.required' => lang('dt_name_required'),
                'name.unique' => lang('dt_name_unique'),
            ]);
        if (!empty($id)){
            $unit = Unit::find($id);
        } else {
            $unit = new Unit();
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
            $unit->name = $name;
            $unit->save();
            DB::commit();
            if ($unit) {
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
        if (!has_permission('unit','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $unit = Unit::find($id);
        DB::beginTransaction();
        try {
            if($unit->product->count() > 0){
                $data['result'] = false;
                $data['message'] = lang('Đơn vị tính đang có sản phẩm không thể xóa');
                return response()->json($data);
            }
            $unit->delete();
            DB::Commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
