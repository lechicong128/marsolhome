<?php

namespace App\Http\Controllers;

use App\Models\FeaturedLocation;
use App\Models\Province;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class FeaturedLocationAdminController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
    }

    public function get_list()
    {
        return view('admin.featured_locations.list', [
            'title' => 'Quản lý Địa điểm nổi bật'
        ]);
    }

    public function getFeaturedLocations()
    {
        $dtLocations = FeaturedLocation::with(['province'])->orderBy('display_order', 'ASC');

        return Datatables::of($dtLocations)
            ->addColumn('options', function ($data) {
                $edit = "<a class='btn btn-xs btn-outline-primary btn-icon dt-modal' href='admin/featured_locations/detail/$data->id' title='Sửa địa điểm' style='display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; border: 1px solid #3a94ef; color: #3a94ef; background: transparent; transition: all 0.2s;' onmouseover=\"this.style.background='#3a94ef'; this.style.color='white'\" onmouseout=\"this.style.background='transparent'; this.style.color='#3a94ef'\"><i class='fa fa-pencil'></i></a>";
                return '<div style="display: flex; justify-content: center; align-items: center;">' . $edit . '</div>';
            })
            ->editColumn('is_active', function ($data) {
                $checked = $data->is_active == 1 ? 'checked' : '';
                return '<div class="text-center" style="display: flex; justify-content: center; align-items: center;"><input type="checkbox" '.$checked.' name="is_active" class="active dt-active" data-plugin="switchery" data-color="#285b23" data-href="admin/featured_locations/changeStatus/'.$data->id.'" data-status="'.$data->is_active.'"></div>';
            })
            ->editColumn('image_url', function ($data) {
                if (!empty($data->image_url)) {
                    $img = $this->baseUrl . '/' . $data->image_url;
                    return '<div class="text-center">
                                <a href="'.$img.'" data-lightbox="location_'.$data->id.'" class="display-block" style="display: inline-block;">
                                    <img src="'.$img.'" alt="image" class="img-responsive img-thumbnail" style="width: 80px; height: 50px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                                </a>
                            </div>';
                }
                return '<div class="text-center text-muted">-</div>';
            })
            ->editColumn('province_id', function ($data) {
                return $data->province ? $data->province->name : '-';
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'is_active', 'image_url'])
            ->make(true);
    }

    public function changeStatus($id)
    {
        $location = FeaturedLocation::find($id);
        if (empty($location)) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy địa điểm'
            ]);
        }
        
        try {
            $location->is_active = $this->request->input('status') == 0 ? 1 : 0;
            $location->save();
            return response()->json([
                'result' => true,
                'message' => lang('dt_success')
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function get_detail($id = 0)
    {
        $location = FeaturedLocation::find($id);
        if (empty($location)) {
            abort(404, 'Không tìm thấy địa điểm');
        }

        $provinces = Province::orderBy('name', 'asc')->get();
        $title = 'Sửa Địa điểm nổi bật';

        return view('admin.featured_locations.detail', [
            'title' => $title,
            'id' => $id,
            'location' => $location,
            'provinces' => $provinces
        ]);
    }

    public function submit($id = 0)
    {
        $location = FeaturedLocation::find($id);
        if (empty($location)) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy địa điểm'
            ]);
        }

        $validator = Validator::make($this->request->all(), [
            'province_id' => 'required|integer'
        ], [
            'province_id.required' => 'Bạn chưa chọn Tỉnh/Thành phố'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => implode('<br>', $validator->errors()->all())
            ]);
        }

        DB::beginTransaction();
        try {
            $location->province_id = $this->request->input('province_id');
            $location->custom_name = $this->request->input('custom_name');
            $location->save();

            if ($this->request->hasFile('image')) {
                if (!empty($location->image_url)) {
                    $this->deleteFile($location->image_url);
                }
                $path = $this->UploadFile($this->request->file('image'), 'featured_locations/' . $location->id, 600, 600, false);
                $location->image_url = $path;
                $location->save();
            }

            DB::commit();
            return response()->json([
                'result' => true,
                'message' => lang('dt_success')
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function order_by()
    {
        $list_order_by = $this->request->input('list_order_by') ?? [];
        foreach ($list_order_by as $id => $val) {
            FeaturedLocation::where('id', $id)->update(['display_order' => $val]);
        }
        return response()->json([
            'result' => true,
            'message' => 'Cập nhật thứ tự thành công'
        ]);
    }
}
