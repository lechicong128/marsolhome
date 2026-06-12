<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class PlanningController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        $title = 'Danh sách quy hoạch';
        if (!has_permission('plannings', 'view')) {
            access_denied();
        }
        return view('admin.planning.list', [
            'title' => $title,
        ]);
    }

    public function getPlannings()
    {
        if (!has_permission('plannings', 'view')) {
            access_denied();
        }

        $plannings = DB::table('tbl_plannings as p')
            ->leftJoin('tbl_provinces as pr', 'pr.id', '=', 'p.province_id')
            ->select(['p.*', 'pr.name as province_name'])
            ->orderBy('p.id', 'desc')
            ->get();

        return DataTables::of($plannings)
            ->addColumn('options', function ($item) {
                $id = $item->id;
                $edit = "<a href='admin/plannings/detail/$id' class='flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors dt-modal'><i class='fa fa-pencil text-slate-400'></i> Sửa quy hoạch</a>";
                
                $viewMap = "";
                if ($item->kml_file) {
                    $viewMap = "<a href='admin/plannings/view-map/$id' class='flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors dt-modal'><i class='fa fa-map-marker text-success'></i> Xem bản đồ</a>";
                }
                
                $delete = '<a type="button" class="po-delete flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50/50 transition-colors" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <div class=\'p-1 text-center\'>
                    <p class=\'text-xs text-slate-600 mb-2 font-medium\'>Xác nhận xóa quy hoạch này?</p>
                    <div class=\'flex gap-2 justify-center\'>
                        <button href=\'admin/plannings/delete/' . $id. '\' class=\'btn btn-danger btn-xs dt-delete\'>' . lang('dt_delete') . '</button>
                        <button class=\'btn btn-default btn-xs po-close\'>' . lang('dt_close') . '</button>
                    </div>
                </div>
            "><i class="fa fa-remove text-red-400"></i> Xóa quy hoạch</a>';
            
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-sm transition-all" type="button" id="dropdownMenu' . $id . '" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <i class="fa fa-chevron-down text-[9px] opacity-60"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right rounded-2xl shadow-xl border border-slate-100 py-1.5 min-w-[150px]" role="menu" aria-labelledby="dropdownMenu' . $id . '">
                                <li style="cursor: pointer">' . $edit . '</li>
                                ' . ($viewMap ? '<li style="cursor: pointer">' . $viewMap . '</li>' : '') . '
                                <li class="divider" style="margin: 4px 0"></li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('name', function ($item) {
                return '<div class="font-semibold text-slate-900">' . e($item->name) . '</div>';
            })
            ->editColumn('province_name', function ($item) {
                $location = e($item->province_name ?? 'Chưa chọn');
                if ($item->location_text) {
                    $location .= '<div class="text-[13px] text-slate-400 mt-0.5 font-normal">' . e($item->location_text) . '</div>';
                }
                return '<div class="text-slate-500 font-medium">' . $location . '</div>';
            })
            ->editColumn('area', function ($item) {
                $html = '<div class="text-right font-bold text-slate-800">' . number_format($item->area, 2, ',', '.') . ' m²</div>';
                if ($item->scale) {
                    $html .= '<div class="text-right text-[13px] text-slate-400 mt-0.5 font-normal">' . number_format($item->scale, 2, ',', '.') . ' ha</div>';
                }
                return $html;
            })
            ->editColumn('kml_file', function ($item) {
                if ($item->kml_file) {
                    return '<div class="text-center"><a href="' . asset('storage/' . $item->kml_file) . '" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-all text-decoration-none"><i class="fa fa-download"></i> Tải file KML/KMZ</a></div>';
                }
                return '<div class="text-center text-muted">Không có file</div>';
            })
            ->editColumn('active', function ($item) {
                $checked = $item->active == 1 ? 'checked' : '';
                return '<div class="flex items-center justify-center">
                    <label class="relative inline-flex items-center cursor-pointer m-0">
                        <input type="checkbox" name="active" value="1" ' . $checked . ' data-href="admin/plannings/changeStatus/' . $item->id . '" data-status="' . $item->active . '" class="sr-only peer dt-active">
                        <div class="relative w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-brand-500"></div>
                    </label>
                </div>';
            })
            ->addColumn('decision_info', function ($item) {
                $html = '<div class="font-semibold text-slate-800 text-sm">' . e($item->decision_no ?? 'Không có số QĐ') . '</div>';
                if ($item->approved_date) {
                    $html .= '<div class="text-xs text-slate-400 mt-0.5"><i class="fa fa-calendar-o"></i> ' . date('d/m/Y', strtotime($item->approved_date)) . '</div>';
                }
                return $html;
            })
            ->addColumn('planning_type_text', function ($item) {
                if ($item->planning_type === 'published') {
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[13px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Đang công bố</span>';
                }
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[13px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">Dự thảo góp ý</span>';
            })
            ->addColumn('status_text', function ($item) {
                switch ($item->status) {
                    case 'approved':
                        return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[13px] font-semibold bg-green-50 text-green-700 border border-green-200">Đã phê duyệt</span>';
                    case 'effective':
                        return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[13px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Hiệu lực</span>';
                    case 'draft':
                        return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[13px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Dự thảo</span>';
                    case 'expired':
                        return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[13px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Hết hiệu lực</span>';
                    default:
                        return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[13px] font-semibold bg-slate-50 text-slate-700 border border-slate-200">' . e($item->status) . '</span>';
                }
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'province_name', 'decision_info', 'planning_type_text', 'status_text', 'area', 'kml_file', 'active'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = 'Thêm quy hoạch mới';
            if (!has_permission('plannings', 'add')) {
                access_denied(true);
            }
        } else {
            if (!has_permission('plannings', 'edit')) {
                access_denied(true);
            }
            $title = 'Sửa quy hoạch';
        }
        
        $planning = null;
        if ($id > 0) {
            $planning = Planning::find($id);
        }
        
        $provinces = DB::table('tbl_provinces')->orderBy('name', 'asc')->get();

        return view('admin.planning.detail', [
            'title' => $title,
            'id' => $id,
            'planning' => $planning,
            'provinces' => $provinces,
        ]);
    }

    public function submit($id = 0)
    {
        if (empty($id)) {
            if (!has_permission('plannings', 'add')) {
                return response()->json(['result' => false, 'message' => 'Bạn không có quyền thêm quy hoạch.']);
            }
        } else {
            if (!has_permission('plannings', 'edit')) {
                return response()->json(['result' => false, 'message' => 'Bạn không có quyền sửa quy hoạch.']);
            }
        }

        $rules = [
            'name' => 'required',
            'province_id' => 'required|integer',
            'area' => 'required|numeric',
            'location_text' => 'nullable|string|max:255',
            'planning_type' => 'required|string|in:published,draft_feedback',
            'status' => 'required|string|in:approved,effective,draft,expired',
            'description' => 'nullable|string',
            'decision_no' => 'required|string|max:255',
            'approved_date' => 'nullable|date',
            'scale' => 'required|numeric',
        ];

        if (empty($id)) {
            $rules['kml_file'] = 'required|file';
        } else {
            $rules['kml_file'] = 'nullable|file';
        }

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => implode('<br>', $validator->errors()->all())
            ]);
        }

        if ($id > 0) {
            $planning = Planning::find($id);
            if (!$planning) {
                return response()->json(['result' => false, 'message' => 'Không tìm thấy quy hoạch.']);
            }
        } else {
            $planning = new Planning();
        }

        if ($this->request->hasFile('kml_file')) {
            $file = $this->request->file('kml_file');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['kml', 'kmz'])) {
                return response()->json([
                    'result' => false,
                    'message' => 'Chỉ chấp nhận file định dạng .kml hoặc .kmz'
                ]);
            }
            if ($file->getSize() > 100 * 1024 * 1024) {
                return response()->json([
                    'result' => false,
                    'message' => 'Kích thước file KML/KMZ tối đa là 100MB'
                ]);
            }

            // Rename and save securely
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('plannings', $fileName, 'public');

            // Delete old file if exists
            if ($planning->kml_file) {
                Storage::disk('public')->delete($planning->kml_file);
                // Clean up old cache
                $oldBaseName = pathinfo($planning->kml_file, PATHINFO_FILENAME);
                Storage::disk('public')->delete("kmz-cache/{$oldBaseName}.json");
                Storage::disk('public')->deleteDirectory("kmz-cache/{$oldBaseName}");
            }
            $planning->kml_file = $path;

            // Generate cache immediately
            \App\Services\KmlProcessorService::process($path);
        }

        if ($this->request->hasFile('image')) {
            $file = $this->request->file('image');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                return response()->json([
                    'result' => false,
                    'message' => 'Chỉ chấp nhận file ảnh định dạng JPG, JPEG, PNG, GIF'
                ]);
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json([
                    'result' => false,
                    'message' => 'Kích thước ảnh tối đa là 5MB'
                ]);
            }

            // Rename and save securely
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('plannings', $fileName, 'public');

            // Delete old file if exists
            if ($planning->image) {
                Storage::disk('public')->delete($planning->image);
            }
            $planning->image = $path;
        }

        DB::beginTransaction();
        try {
            $planning->name = $this->request->input('name');
            $planning->province_id = $this->request->input('province_id');
            $planning->location_text = $this->request->input('location_text');
            $planning->planning_type = $this->request->input('planning_type');
            $planning->status = $this->request->input('status');
            $planning->description = $this->request->input('description');
            $planning->decision_no = $this->request->input('decision_no');
            $planning->approved_date = $this->request->input('approved_date');
            $planning->scale = $this->request->input('scale');
            $planning->area = $this->request->input('area');
            $planning->save();
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Lưu thông tin quy hoạch thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        if (!has_permission('plannings', 'delete')) {
            return response()->json(['result' => false, 'message' => 'Bạn không có quyền xóa quy hoạch.']);
        }

        $planning = Planning::find($id);
        if (!$planning) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy quy hoạch.']);
        }

        DB::beginTransaction();
        try {
            if ($planning->kml_file) {
                Storage::disk('public')->delete($planning->kml_file);
                // Clean up cache
                $baseName = pathinfo($planning->kml_file, PATHINFO_FILENAME);
                Storage::disk('public')->delete("kmz-cache/{$baseName}.json");
                Storage::disk('public')->deleteDirectory("kmz-cache/{$baseName}");
            }
            if ($planning->image) {
                Storage::disk('public')->delete($planning->image);
            }
            $planning->delete();
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Xóa quy hoạch thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra khi xóa: ' . $e->getMessage()
            ]);
        }
    }

    public function changeStatus($id)
    {
        if (!has_permission('plannings', 'edit')) {
            return response()->json(['result' => false, 'message' => 'Bạn không có quyền sửa quy hoạch.']);
        }

        $planning = Planning::find($id);
        if (!$planning) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy quy hoạch.']);
        }

        try {
            $planning->active = $this->request->input('status') == 0 ? 1 : 0;
            $planning->save();

            return response()->json([
                'result' => true,
                'message' => 'Cập nhật trạng thái thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function viewMap($id)
    {
        if (!has_permission('plannings', 'view')) {
            access_denied(true);
        }

        $planning = Planning::find($id);
        if (!$planning) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy quy hoạch.']);
        }

        $cacheJsonUrl = null;
        if ($planning->kml_file) {
            // Process on-the-fly if cache not present
            \App\Services\KmlProcessorService::process($planning->kml_file);

            $baseName = pathinfo($planning->kml_file, PATHINFO_FILENAME);
            $jsonRelativePath = "kmz-cache/{$baseName}.json";
            if (Storage::disk('public')->exists($jsonRelativePath)) {
                $cacheJsonUrl = asset('storage/' . $jsonRelativePath);
            }
        }

        return view('admin.planning.view_map', [
            'planning' => $planning,
            'cacheJsonUrl' => $cacheJsonUrl,
        ]);
    }
}
