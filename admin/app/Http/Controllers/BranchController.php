<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class BranchController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('branch', 'view')) {
            access_denied();
        }
        return view('admin.branches.list', [
            'title' => lang('c_list_branches'),
        ]);
    }

    public function detail($id = '')
    {
        if (!has_permission('branch', 'view')) {
            access_denied();
        }

        if (empty($id)) {
            if (!has_permission('branch', 'add')) {
                access_denied();
            }
            $title = lang('c_add_branch');
        } else {
            if (!has_permission('branch', 'edit')) {
                access_denied();
            }
            $title  = lang('c_edit_branch');
            $branch = Branch::find($id);
        }

        return view('admin.branches.detail', [
            'id'     => $id ?? 0,
            'title'  => $title,
            'branch' => $branch ?? [],
        ]);
    }

    public function getTable()
    {
        $branch = Branch::query();

        return Datatables::of($branch)
            ->addColumn('options', function ($branch) {
                $edit   = "<a class='dt-modal' href='admin/branch/detail/$branch->id'><i class='fa fa-pencil'></i> " . lang('c_edit_branch') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/branch/delete/' . $branch->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_branch') . '</a>';

                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenuBranch" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenuBranch">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('active', function ($branch) {
                $checked = $branch->active == 1 ? 'checked' : '';
                $str     = '<div><input type="checkbox" ' . $checked . ' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/branch/changeStatus/' . $branch->id . '" data-status="' . $branch->active . '"></div>';
                return $str;
            })
            ->editColumn('icon', function ($branch) {
                $dtImage = !empty($branch->icon) ? asset('storage/' . $branch->icon) : null;
                if ($dtImage) {
                    return '<img src="' . $dtImage . '" alt="icon" style="height: 50px; max-width: 50px; object-fit: contain;">';
                }
                return '';
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'active', 'icon'])
            ->make(true);
    }

    public function submit($id = 0)
    {
        $data = [];

        if (!empty($id)) {
            $branch = Branch::find($id);
            if (!empty($branch)) {
                $validator = Validator::make(
                    $this->request->all(),
                    [
                        'name' => 'required',
                        'phone' => 'required',
                        'address' => 'required',
                    ],
                    [
                        'name.required'    => 'Bạn chưa nhập tên chi nhánh',
                        'phone.required'   => 'Bạn chưa nhập số điện thoại',
                        'address.required' => 'Bạn chưa nhập địa chỉ',
                    ]
                );
            }
        } else {
            $validator = Validator::make(
                $this->request->all(),
                [
                    'name'    => 'required',
                    'phone'   => 'required',
                    'address' => 'required',
                ],
                [
                    'name.required'    => 'Bạn chưa nhập tên chi nhánh',
                    'phone.required'   => 'Bạn chưa nhập số điện thoại',
                    'address.required' => 'Bạn chưa nhập địa chỉ',
                ]
            );
            $branch = new Branch();
        }

        if ($validator->fails()) {
            $data['result']  = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }

        DB::beginTransaction();
        try {
            $branch->name       = $this->request->input('name');
            $branch->phone      = $this->request->input('phone');
            $branch->address    = $this->request->input('address');
            $branch->map_link   = $this->request->input('map_link');
            $branch->save();

            if ($this->request->hasFile('icon')) {
                if (!empty($branch->icon)) {
                    $this->deleteFile($branch->icon);
                }
                $path = $this->UploadFile($this->request->file('icon'), 'branches/' . $branch->id, 600, 600, false);
                $branch->icon = $path;
                $branch->save();
            }

            DB::commit();

            $data['result']  = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result']  = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeStatus($id)
    {
        $branch = Branch::find($id);
        try {
            $branch->active = $this->request->input('status') == 0 ? 1 : 0;
            $branch->save();
            $data['result']  = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result']  = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function delete($id)
    {
        if (!has_permission('branch', 'delete')) {
            $data['result']  = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }

        $branch = Branch::find($id);
        try {
            $success = $branch->delete();
            if ($success && !empty($branch->icon)) {
                $this->deleteFile($branch->icon);
            }
            $data['result']  = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result']  = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }
}
