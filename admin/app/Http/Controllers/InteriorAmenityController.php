<?php

namespace App\Http\Controllers;

use App\Models\InteriorAmenity;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class InteriorAmenityController extends Controller
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
        $title = lang('dt_interior_amenities');
        if (!has_permission('interior_amenities', 'view')) {
            access_denied();
        }
        return view('admin.interior_amenity.list', [
            'title' => $title,
        ]);
    }

    public function getInteriorAmenities()
    {
        $_locale = $this->request->input('_locale', 'vi');
        $dtInteriorAmenity = InteriorAmenity::orderByRaw('id desc')->get();
        return Datatables::of($dtInteriorAmenity)
            ->addColumn('options', function ($interior_amenity) {
                $edit = "<a class='dt-modal' href='admin/interior_amenities/detail/$interior_amenity->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_interior_amenity') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/interior_amenities/delete/'.$interior_amenity->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_interior_amenity') .'</a>';
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
            ->editColumn('name', function ($interior_amenity) {
                $name = $interior_amenity->name ?? '';
                return '<div>'.$name.'</div>';
            })
             ->editColumn('icon', function ($interior_amenity) {
                $icon = $interior_amenity->icon ? $this->base_url.'/'.$interior_amenity->icon : null;
                if ($icon) {
                    return '<div class="text-center"><img src="'.$icon.'" width="30" alt=""></div>';
                } else {
                    return '<div class="text-center"></div>';
                }
            })
             ->editColumn('active', function ($interior_amenity) {
                $checked = $interior_amenity->active == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#0050c8" data-href="admin/interior_amenities/changeStatus/'.$interior_amenity->id.'" data-status="'.$interior_amenity->active.'">';
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'active','icon'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_interior_amenity');
            if (!has_permission('interior_amenities', 'add')) {
                access_denied(true);
            }
        } else {
            if (!has_permission('interior_amenities', 'edit')) {
                access_denied(true);
            }
            $title = lang('dt_edit_interior_amenity');
        }
        $interior_amenity = InteriorAmenity::find($id);
        return view('admin.interior_amenity.detail', [
            'title' => $title,
            'id' => $id,
            'interior_amenity' => $interior_amenity,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_interior_amenities,name,' . $id,
            ],
            [
                'name.required' => lang('dt_name_required'),
                'name.unique' => lang('dt_name_unique'),
            ]);
        if (!empty($id)) {
            $interior_amenity = InteriorAmenity::find($id);
        } else {
            $interior_amenity = new InteriorAmenity();
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
            $interior_amenity->name = $name ?? '';
            $interior_amenity->save();

            if ($this->request->hasFile('icon')) {
                $path = $this->UploadFile($this->request->file('icon'), 'interior_amenity/' . $interior_amenity->id, 0, 0, false);
                if ($path) {
                    $interior_amenity->icon = $path;
                    $interior_amenity->save();
                }
            }
            DB::commit();
            if ($interior_amenity) {
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
        if (!has_permission('interior_amenities', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $interior_amenity = InteriorAmenity::find($id);
        DB::beginTransaction();
        try {
            $interior_amenity->delete();
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
        if (!has_permission('interior_amenities', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $interior_amenity = InteriorAmenity::find($id);
        try {
            $interior_amenity->active = $this->request->input('status') == 0 ? 1 : 0;
            $interior_amenity->save();
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
