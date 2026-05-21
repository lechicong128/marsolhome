<?php

namespace App\Http\Controllers;

use App\Models\CategoryService;
use App\Models\Service;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ServiceController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('services', 'view')) {
            access_denied();
        }
        $category_services = CategoryService::where('active', 1)->orderBy('name')->get();
        return view('admin.services.list', [
            'title'             => lang('c_list_services'),
            'category_services' => $category_services,
        ]);
    }

    public function detail($id = '')
    {
        if (!has_permission('services', 'view')) {
            access_denied();
        }

        if (empty($id)) {
            if (!has_permission('services', 'add')) {
                access_denied();
            }
            $title = lang('c_add_services');
        } else {
            if (!has_permission('services', 'edit')) {
                access_denied();
            }
            $title   = lang('c_edit_services');
            $service = Service::find($id);
        }

        $category_services = CategoryService::where('active', 1)->get();

        return view('admin.services.detail', [
            'id'                => $id ?? 0,
            'title'             => $title,
            'service'           => $service ?? [],
            'category_services' => $category_services,
        ]);
    }

    public function getTable()
    {
        $service = Service::query()
            ->leftJoin('tbl_category_services as cs', 'cs.id', '=', 'tbl_services.id_category')
            ->select('tbl_services.*', 'cs.name as category_name');

        if ($this->request->filled('filter_category')) {
            $service->where('tbl_services.id_category', $this->request->input('filter_category'));
        }

        return Datatables::of($service)
            ->addColumn('category_name', function ($service) {
                return '<div class="text-center">' . ($service->category_name ?? '<span class="text-muted">-</span>') . '</div>';
            })
            ->addColumn('options', function ($service) {
                $view   = "<a class='dt-modal' href='admin/services/view/$service->id'><i class='fa fa-eye'></i> " . lang('dt_view') . "</a>";
                $edit   = "<a href='admin/services/detail/$service->id'><i class='fa fa-pencil'></i> " . lang('c_edit_services') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/services/delete/' . $service->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_services') . '</a>';

                return ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $view . '</li>
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';
            })
            ->editColumn('active', function ($service) {
                $checked = $service->active == 1 ? 'checked' : '';
                return '<div><input type="checkbox" ' . $checked . ' name="active" class="active dt-active" data-plugin="switchery" data-color="#285b23" data-href="admin/services/changeStatus/' . $service->id . '" data-status="' . $service->active . '"></div>';
            })
            ->editColumn('image', function ($service) {
                $dtImage = !empty($service->image) ? asset('storage/' . $service->image) : 'admin/assets/images/not_available.jpg';
                return '<div style="display:flex;justify-content:center;margin-top:5px" class="show_image">
                    <img src="' . $dtImage . '" alt="image" class="img-responsive img-circle" style="width:50px;height:50px">
                </div>';
            })
            ->editColumn('price', function ($service) {
                return '<div class="text-right">' . number_format($service->price ?? 0) . '</div>';
            })
            ->editColumn('discount_percent', function ($service) {
                return '<div class="text-center">' . ($service->discount_percent ?? 0) . '%</div>';
            })
            ->editColumn('duration_minutes', function ($service) {
                return '<div class="text-center">' . ($service->duration_minutes ?? 0) . ' phút</div>';
            })
            ->editColumn('is_hot', function ($service) {
                $checked = $service->is_hot == 1 ? 'checked' : '';
                return '<div><input type="checkbox" ' . $checked . ' name="is_hot" class="is_hot dt-active" data-plugin="switchery" data-color="#e08a00" data-href="admin/services/changeIsHot/' . $service->id . '" data-status="' . $service->is_hot . '"></div>';
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'active', 'is_hot', 'image', 'price', 'discount_percent', 'duration_minutes', 'category_name'])
            ->make(true);
    }

    public function submit($id = 0)
    {
        $data = [];

        if (!empty($id)) {
            $service = Service::find($id);
            if (!empty($service)) {
                if ($service->code != $this->request->input('code')) {
                    $validator = Validator::make(
                        $this->request->all(),
                        [
                            'code' => 'unique:tbl_services,code',
                            'name' => 'required',
                        ],
                        [
                            'code.unique'   => lang('code_category_da_duoc_su_dung'),
                            'name.required' => 'Bạn chưa nhập tên',
                        ]
                    );
                } else {
                    $validator = Validator::make(
                        $this->request->all(),
                        ['name' => 'required'],
                        ['name.required' => 'Bạn chưa nhập tên']
                    );
                }
            }
        } else {
            $validator = Validator::make(
                $this->request->all(),
                [
                    'code' => 'unique:tbl_services,code',
                    'name' => 'required',
                ],
                [
                    'code.unique'   => lang('code_category_da_duoc_su_dung'),
                    'name.required' => 'Bạn chưa nhập tên',
                ]
            );
            $service = new Service();
        }

        if ($validator->fails()) {
            $data['result']  = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }

        DB::beginTransaction();
        try {
            $service->code              = $this->request->input('code');
            $service->name              = $this->request->input('name');
            $service->price             = number_unformat($this->request->input('price', 0));
            $service->discount_percent  = $this->request->input('discount_percent', 0);
            $service->duration_minutes  = $this->request->input('duration_minutes', 0);
            $service->id_category       = $this->request->input('id_category');
            $service->content           = $this->request->input('content');
            $service->save();
            DB::commit();

            if ($service) {
                // Auto-generate code
                if (empty($service->code)) {
                    $code   = 'DV-' . str_pad($service->id, 6, '0', STR_PAD_LEFT);
                    $exists = Service::where('code', $code)->first();
                    $service->code = empty($exists->id) ? $code : $code . '-1';
                    $service->save();
                }

                // Upload ảnh đại diện
                if ($this->request->hasFile('image')) {
                    if (!empty($service->image)) {
                        $this->deleteFile($service->image);
                    }
                    $path          = $this->UploadFile($this->request->file('image'), 'services/' . $service->id, 600, 600, false);
                    $service->image = $path;
                    $service->save();
                }

                // Upload ảnh gallery
                if ($this->request->hasFile('images')) {
                    foreach ($this->request->file('images') as $file) {
                        $path = $this->UploadFile($file, 'services/' . $service->id, 800, 800, false);
                        DB::table('tbl_services_images')->insert([
                            'id_service' => $service->id,
                            'image'      => $path,
                        ]);
                    }
                }

                $data['result']  = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result']  = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function view($id)
    {
        if (!has_permission('services', 'view')) {
            access_denied();
        }

        $service = Service::find($id);
        if (empty($service)) {
            abort(404);
        }

        $images = DB::table('tbl_services_images')
            ->where('id_service', $id)
            ->orderBy('id', 'asc')
            ->get();

        $category = \App\Models\CategoryService::find($service->id_category);

        return view('admin.services.view', [
            'service'  => $service,
            'images'   => $images,
            'category' => $category,
        ]);
    }

    public function changeStatus($id)
    {
        $service = Service::find($id);
        try {
            $service->active = $this->request->input('status') == 0 ? 1 : 0;
            $service->save();
            $data['result']  = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result']  = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function changeIsHot($id)
    {
        $service = Service::find($id);
        try {
            $service->is_hot = $this->request->input('status') == 0 ? 1 : 0;
            $service->save();
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
        if (!has_permission('services', 'delete')) {
            $data['result']  = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }

        $service = Service::find($id);
        try {
            $service->delete();
            if (!empty($service->image)) {
                $this->deleteFile($service->image);
            }
            // Xóa ảnh gallery
            $images = DB::table('tbl_services_images')->where('id_service', $id)->get();
            foreach ($images as $img) {
                if (!empty($img->image)) {
                    $this->deleteFile($img->image);
                }
            }
            DB::table('tbl_services_images')->where('id_service', $id)->delete();

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