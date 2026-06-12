<?php

namespace App\Http\Controllers;

use App\Models\TypeProperty;
use App\Models\Utility;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Mail;

class TypePropertyController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->base_url = config('services.storage.url');
    }

    public function get_list()
    {
        $title = lang('dt_type_property');
        if (!has_permission('type_property','view')){
            access_denied();
        }
        return view('admin.type_property.list', [
            'title' => $title,
        ]);
    }

    public function getTypeProperty()
    {
        $_locale = $this->request->input('_locale','vi');
        $dtTypeProperty = TypeProperty::orderByRaw('id desc')->get();
        return Datatables::of($dtTypeProperty)
            ->addColumn('options', function ($type_property) {
                $edit = "<a class='dt-modal' href='admin/type_property/detail/$type_property->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_type_property') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/type_property/delete/'.$type_property->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_type_property') .'</a>';
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
            ->editColumn('image', function ($type_property) {
                $image = $type_property->image ? $this->base_url . '/' . $type_property->image : null;
                if ($image) {
                    return '<div class="text-center"><img src="'.$image.'" width="55" height="55" style="object-fit: cover; border-radius: 4px;" alt=""></div>';
                }
                return '<div class="text-center">-</div>';
            })
            ->editColumn('name', function ($type_property) {
                $name = $type_property->name ?? '';
                return '<div>'.$name.'</div>';
            })
             ->editColumn('active', function ($type_property) {
                $checked = $type_property->active == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#0050c8" data-href="admin/type_property/changeStatus/'.$type_property->id.'" data-status="'.$type_property->active.'">';
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options','name','active','image'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_type_property');
            if (!has_permission('type_property','add')){
                access_denied(true);
            }
        } else {
            if (!has_permission('type_property','edit')){
                access_denied(true);
            }
            $title = lang('dt_edit_type_property');
        }
        $type_property = TypeProperty::with('utilities')->find($id);
        $utilities = Utility::where('active', 1)->get();
        return view('admin.type_property.detail', [
            'title' => $title,
            'id' => $id,
            'type_property' => $type_property,
            'utilities' => $utilities,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_type_property,name,' . $id,
            ]
            , [
                'name.required' => lang('dt_name_required'),
                'name.unique' => lang('dt_name_unique'),
            ]);
        if (!empty($id)){
            $type_property = TypeProperty::find($id);
        } else {
            $type_property = new TypeProperty();
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
            $type_property->name = $name ?? '';
            $type_property->save();

            // Sync utilities pivot table
            $selectedUtilities = $this->request->input('utilities', []);
            $type_property->utilities()->sync($selectedUtilities);

            // Sync with old columns for backward compatibility
            $floorsUtility = Utility::where('name', 'Số tầng')->first();
            $entranceUtility = Utility::where('name', 'Đường vào (m)')->first();
            $facadeUtility = Utility::where('name', 'Mặt tiền (m)')->first();

            $type_property->has_floors = ($floorsUtility && in_array($floorsUtility->id, $selectedUtilities)) ? 1 : 0;
            $type_property->has_entrance = ($entranceUtility && in_array($entranceUtility->id, $selectedUtilities)) ? 1 : 0;
            $type_property->has_facade = ($facadeUtility && in_array($facadeUtility->id, $selectedUtilities)) ? 1 : 0;
            $type_property->save();

            if ($this->request->hasFile('image')) {
                if (!empty($type_property->image)) {
                    $this->deleteFile($type_property->image);
                }
                $path = $this->UploadFile($this->request->file('image'), 'type_property/' . $type_property->id, 0, 0, false);
                if ($path) {
                    $type_property->image = $path;
                    $type_property->save();
                }
            }

            DB::commit();
            if ($type_property) {
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

    public function delete($id){
        if (!has_permission('type_property','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $type_property = TypeProperty::find($id);
        DB::beginTransaction();
        try {
            // $homeCount = Home::where('type_property_id', $id)->count();
            // if ($homeCount > 0) {
            //     $data['result'] = false;
            //     $data['message'] = 'Loại bất động sản này đã được sử dụng!';
            //     return response()->json($data);
            // }
            $type_property->delete();
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

    public function changeStatus($id)
    {
        if (!has_permission('type_property', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $type_property = TypeProperty::find($id);
        try {
            $type_property->active = $this->request->input('status') == 0 ? 1 : 0;
            $type_property->save();
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
