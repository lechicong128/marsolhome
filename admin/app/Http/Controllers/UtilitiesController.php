<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\UtilityOption;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class UtilitiesController extends Controller
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
        $title = lang('dt_utilities');
        if (!has_permission('utilities', 'view')) {
            access_denied();
        }
        return view('admin.utility.list', [
            'title' => $title,
        ]);
    }

    public function getUtilities()
    {
        $_locale = $this->request->input('_locale', 'vi');
        $dtUtilities = Utility::orderByRaw('id desc')->get();
        return Datatables::of($dtUtilities)
            ->addColumn('options', function ($utility) {
                $edit = "<a class='dt-modal' href='admin/utilities/detail/$utility->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_utility') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/utilities/delete/'.$utility->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_utility') .'</a>';
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
            ->editColumn('icon', function ($utility) {
                $icon = $utility->icon ? $this->base_url.'/'.$utility->icon : null;
                if ($icon) {
                    return '<div class="text-center"><img src="'.$icon.'" width="30" alt=""></div>';
                } else {
                    return '<div class="text-center"></div>';
                }
            })
            ->editColumn('name', function ($utility) {
                $name = $utility->name ?? '';
                return '<div>'.e($name).'</div>';
            })
            ->editColumn('input_type', function ($utility) {
                $type = $utility->input_type ?? 'number';
                if ($type == 'number') {
                    return 'Nhập số';
                } elseif ($type == 'text') {
                    return 'Nhập chữ';
                } else {
                    return 'Hộp chọn (Select)';
                }
            })
            ->addColumn('transaction_type_text', function ($utility) {
                $type = $utility->transaction_type ?? 3;
                if ($type == 1) {
                    return '<span class="label label-success">Chỉ Bán</span>';
                } elseif ($type == 2) {
                    return '<span class="label label-info">Chỉ Cho Thuê</span>';
                } else {
                    return '<span class="label label-default">Cả Hai</span>';
                }
            })
            ->editColumn('active', function ($utility) {
                $checked = $utility->active == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#0050c8" data-href="admin/utilities/changeStatus/'.$utility->id.'" data-status="'.$utility->active.'">';
                return $str;
            })
            ->editColumn('show_list', function ($utility) {
                $checked = $utility->show_list == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="show_list" class="show_list dt-active"  data-plugin="switchery" data-color="#0050c8" data-href="admin/utilities/changeShowList/'.$utility->id.'" data-status="'.$utility->show_list.'">';
                return $str;
            })
            ->editColumn('show_filter', function ($utility) {
                $checked = $utility->show_filter == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="show_filter" class="show_filter dt-active"  data-plugin="switchery" data-color="#0050c8" data-href="admin/utilities/changeShowFilter/'.$utility->id.'" data-status="'.$utility->show_filter.'">';
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'active', 'transaction_type_text','icon', 'show_list', 'show_filter'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_utility');
            if (!has_permission('utilities', 'add')) {
                access_denied(true);
            }
            $utility = null;
        } else {
            if (!has_permission('utilities', 'edit')) {
                access_denied(true);
            }
            $title = lang('dt_edit_utility');
            $utility = Utility::with('options')->find($id);
        }
        return view('admin.utility.detail', [
            'title' => $title,
            'id' => $id,
            'utility' => $utility,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_utilities,name,' . $id,
                'input_type' => 'required|in:number,text,select',
                'transaction_type' => 'required|in:1,2,3',
            ],
            [
                'name.required' => lang('dt_name_required'),
                'name.unique' => lang('dt_name_unique'),
                'input_type.required' => 'Vui lòng chọn loại nhập liệu',
                'input_type.in' => 'Loại nhập liệu không hợp lệ',
                'transaction_type.required' => 'Vui lòng chọn hình thức áp dụng',
            ]);
        
        $input_type = $this->request->input('input_type');
        $option_names = $this->request->input('option_names', []);
        $option_ids = $this->request->input('option_ids', []);

        if ($input_type === 'select') {
            $nonEmptyOptions = array_filter($option_names, function($val) {
                return trim($val) !== '';
            });
            if (empty($nonEmptyOptions)) {
                $data['result'] = 0;
                $data['message'] = ['Vui lòng thêm ít nhất một tùy chọn cho loại Select'];
                echo json_encode($data);
                die();
            }
        }

        if (!empty($id)) {
            $utility = Utility::find($id);
        } else {
            $utility = new Utility();
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
            $transaction_type = $this->request->input('transaction_type', 3);

            $utility->name = $name ?? '';
            $utility->input_type = $input_type ?? 'number';
            $utility->transaction_type = $transaction_type;
            $utility->unit = $this->request->input('unit');
            $utility->save();

            if ($input_type === 'select') {
                $submittedIds = [];
                foreach ($option_names as $index => $optName) {
                    $optName = trim($optName);
                    if ($optName === '') {
                        continue;
                    }
                    $optId = $option_ids[$index] ?? 0;
                    if ($optId > 0) {
                        $option = UtilityOption::where('utility_id', $utility->id)->find($optId);
                        if ($option) {
                            $option->name = $optName;
                            $option->save();
                            $submittedIds[] = $option->id;
                        }
                    } else {
                        $option = new UtilityOption();
                        $option->utility_id = $utility->id;
                        $option->name = $optName;
                        $option->save();
                        $submittedIds[] = $option->id;
                    }
                }
                // Delete options that were not submitted
                UtilityOption::where('utility_id', $utility->id)
                    ->whereNotIn('id', $submittedIds)
                    ->delete();
            } else {
                // Delete all options if input_type changed to non-select
                UtilityOption::where('utility_id', $utility->id)->delete();
            }

            if ($this->request->hasFile('icon')) {
                $path = $this->UploadFile($this->request->file('icon'), 'utilities/' . $utility->id, 0, 0, false);
                if ($path) {
                    $utility->icon = $path;
                    $utility->save();
                }
            }
            DB::commit();
            if ($utility) {
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
        if (!has_permission('utilities', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $utility = Utility::find($id);
        DB::beginTransaction();
        try {
            // Detach relation with TypeProperty and Home first
            $utility->type_properties()->detach();
            $utility->homes()->detach();
            $utility->delete();
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
        if (!has_permission('utilities', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $utility = Utility::find($id);
        try {
            $utility->active = $this->request->input('status') == 0 ? 1 : 0;
            $utility->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
    
    public function changeShowList($id)
    {
        if (!has_permission('utilities', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $utility = Utility::find($id);
        try {
            $utility->show_list = $this->request->input('status') == 0 ? 1 : 0;
            $utility->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeShowFilter($id)
    {
        if (!has_permission('utilities', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $utility = Utility::find($id);
        if($this->request->input('status') == 0){
            if($utility->input_type != 'select'){
                $data['result'] = false;
                $data['message'] = 'Không thể bật bộ lọc với tiện ích không phải dạng selectbox';
                return response()->json($data);
            }
        }
        try {
            $utility->show_filter = $this->request->input('status') == 0 ? 1 : 0;
            $utility->save();
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
