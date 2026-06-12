<?php

namespace App\Http\Controllers;

use App\Models\Plandoffice;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class PlandofficeController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        $title = 'Danh sách địa chính';
        if (!has_permission('plandoffices', 'view')) {
            access_denied();
        }

        return view('admin.plandoffice.list', [
            'title' => $title,
        ]);
    }

    public function getPlannings()
    {
        if (!has_permission('plandoffices', 'view')) {
            access_denied();
        }

        $plannings = DB::table('tbl_plandoffices as po')
            ->leftJoin('tbl_provinces as pr', 'pr.id', '=', 'po.province_id')
            ->leftJoin(DB::raw('(SELECT plandoffice_id, COUNT(*) as parcels_count FROM tbl_plandoffice_parcels GROUP BY plandoffice_id) as p_count'), 'p_count.plandoffice_id', '=', 'po.id')
            ->select(['po.*', 'pr.name as province_name', 'p_count.parcels_count'])
            ->orderBy('po.id', 'desc')
            ->get();

        return DataTables::of($plannings)
            ->addColumn('options', function ($item) {
                $edit = "<a class='dt-modal' href='admin/plandoffices/detail/$item->id'><i class='fa fa-pencil'></i> Sửa cấu hình</a>";
                
                $viewMap = "";
                if ($item->kml_file) {
                    $viewMap = "<a class='dt-modal' href='admin/plandoffices/view-map/$item->id'><i class='fa fa-map-marker text-success'></i> Xem bản đồ</a>";
                }
                
                $parcels = "<a href='admin/plandoffices/parcels/$item->id'><i class='fa fa-list text-info'></i> Danh sách tờ thửa</a>";
                
                $extract = '<a type="button" class="btn-extract-kml" data-id="'.$item->id.'"><i class="fa fa-database text-warning"></i> Trích xuất tờ thửa</a>';

                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/plandoffices/delete/'.$item->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> Xóa quy hoạch</a>';
                
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu' . $item->id . '" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu' . $item->id . '">
                                <li style="cursor: pointer">' . $edit . '</li>
                                ' . ($viewMap ? '<li style="cursor: pointer">' . $viewMap . '</li>' : '') . '
                                <li style="cursor: pointer">' . $parcels . '</li>
                                <li style="cursor: pointer">' . $extract . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('name', function ($item) {
                return '<div>' . e($item->name) . '</div>';
            })
            ->editColumn('province_name', function ($item) {
                return '<div>' . e($item->province_name ?? 'Chưa chọn') . '</div>';
            })
            ->editColumn('kml_file', function ($item) {
                if ($item->kml_file) {
                    $kmls = explode('||', $item->kml_file);
                    $count = count(array_filter(array_map('trim', $kmls)));
                    return '<div class="text-center font-bold">' . $count . ' file KML</div>';
                }
                return '<div class="text-center text-muted">Không có file</div>';
            })
            ->editColumn('parcels_count', function ($item) {
                return '<div class="text-center font-bold text-info">' . number_format((int)($item->parcels_count ?? 0)) . ' thửa</div>';
            })
            ->editColumn('active', function ($item) {
                $checked = $item->active == 1 ? 'checked' : '';
                return '<input type="checkbox" ' . $checked . ' name="active" class="active dt-active" data-plugin="switchery" data-color="#0050c8" data-href="admin/plandoffices/changeStatus/' . $item->id . '" data-status="' . $item->active . '">';
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'province_name', 'kml_file', 'parcels_count', 'active'])
            ->make(true);
    }

    public function parcels($plandoffice_id)
    {
        if (!has_permission('plandoffices', 'view')) {
            access_denied();
        }

        $plandoffice = Plandoffice::find($plandoffice_id);
        if (!$plandoffice) {
            abort(404, 'Không tìm thấy địa chính.');
        }

        $title = 'Danh sách tờ thửa - ' . $plandoffice->name;

        return view('admin.plandoffice.parcels', [
            'title' => $title,
            'plandoffice' => $plandoffice,
        ]);
    }

    public function getParcels($plandoffice_id)
    {
        if (!has_permission('plandoffices', 'view')) {
            access_denied();
        }

        $query = DB::table('tbl_plandoffice_parcels')->where('plandoffice_id', $plandoffice_id);

        return DataTables::of($query)
            ->filter(function ($q) {
                $search = $this->request->input('search.value');
                if (!empty($search)) {
                    $q->where(function ($subQ) use ($search) {
                        $subQ->where('so_to', 'like', "%{$search}%")
                             ->orWhere('so_thua', 'like', "%{$search}%")
                             ->orWhere('ten_chu', 'like', "%{$search}%")
                             ->orWhere('loai_dat', 'like', "%{$search}%");
                    });
                }
            })
            ->editColumn('dien_tich', function ($item) {
                if ($item->dien_tich !== null) {
                    return '<div class="text-right font-bold">' . number_format($item->dien_tich, 2, ',', '.') . ' m²</div>';
                }
                return '<div class="text-center text-muted">-</div>';
            })
            ->editColumn('loai_dat_quy_hoach', function ($item) {
                if (empty($item->loai_dat_quy_hoach)) {
                    return '<div class="text-center text-muted">-</div>';
                }
                $data = json_decode($item->loai_dat_quy_hoach, true);
                if (empty($data) || !is_array($data)) {
                    return '<div class="text-center text-muted">-</div>';
                }
                $html = '<div style="font-size: 12px; line-height: 1.4; max-width: 280px; word-wrap: break-word; white-space: normal;">';
                foreach ($data as $row) {
                    $type = e($row['type'] ?? '');
                    $area = isset($row['area']) ? number_format($row['area'], 2, ',', '.') . ' m²' : '-';
                    $percentage = isset($row['percentage']) ? number_format($row['percentage'], 2, ',', '.') . '%' : '-';
                    $html .= "<div style='margin-bottom: 4px;'><span class='label label-info' style='display: inline-block; margin-right: 4px; padding: 2px 5px;'>{$type}</span>: <strong>{$area}</strong> ({$percentage})</div>";
                }
                $html .= '</div>';
                return $html;
            })
            ->editColumn('mo_ta_thua', function ($item) {
                if (empty($item->mo_ta_thua)) {
                    return '<div class="text-center text-muted">-</div>';
                }
                $text = e($item->mo_ta_thua);
                if (mb_strlen($text) > 80) {
                    $short = mb_substr($text, 0, 77) . '...';
                    return "<div title='{$text}' style='font-size: 12px; max-width: 220px; word-wrap: break-word; white-space: normal; line-height: 1.4;'>{$short}</div>";
                }
                return "<div style='font-size: 12px; max-width: 220px; word-wrap: break-word; white-space: normal; line-height: 1.4;'>{$text}</div>";
            })
            ->addColumn('options', function ($item) {
                $edit = "<a class='btn btn-icon btn-default dt-modal' href='admin/plandoffices/edit-parcel/{$item->id}' title='Sửa'><i class='fa fa-edit'></i></a>";
                return '<div class="text-center">' . $edit . '</div>';
            })
            ->addIndexColumn()
            ->rawColumns(['dien_tich', 'options', 'loai_dat_quy_hoach', 'mo_ta_thua'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = 'Thêm địa chính mới';
            if (!has_permission('plandoffices', 'add')) {
                access_denied(true);
            }
            $plandoffice = null;
        } else {
            if (!has_permission('plandoffices', 'edit')) {
                access_denied(true);
            }
            $title = 'Cấu hình quy hoạch địa chính';
            $plandoffice = Plandoffice::find($id);
        }
        
        $provinces = DB::table('tbl_provinces')->orderBy('name', 'asc')->get();

        return view('admin.plandoffice.detail', [
            'title' => $title,
            'id' => $id,
            'plandoffice' => $plandoffice,
            'provinces' => $provinces,
        ]);
    }

    public function submit($id = 0)
    {
        if (empty($id)) {
            if (!has_permission('plandoffices', 'add')) {
                return response()->json(['result' => false, 'message' => 'Bạn không có quyền thêm địa chính.']);
            }
        } else {
            if (!has_permission('plandoffices', 'edit')) {
                return response()->json(['result' => false, 'message' => 'Bạn không có quyền sửa địa chính.']);
            }
        }

        if ($id > 0) {
            $plandoffice = Plandoffice::find($id);
            if (!$plandoffice) {
                return response()->json(['result' => false, 'message' => 'Không tìm thấy cấu hình quy hoạch.']);
            }
        } else {
            $plandoffice = new Plandoffice();
        }

        $kml_files = [];
        if (!empty($plandoffice->kml_file)) {
            $decoded = json_decode($plandoffice->kml_file, true);
            if (is_array($decoded)) {
                $kml_files = $decoded;
            } else {
                $kml_files = explode('||', $plandoffice->kml_file);
            }
        }
        $kml_files = array_filter(array_map('trim', $kml_files));

        $rules = [
            'name' => 'required',
        ];

        if (empty($kml_files)) {
            $rules['kml_files'] = 'required|array|min:1';
            $rules['kml_files.*'] = 'file';
        } else {
            $rules['kml_files'] = 'nullable|array';
            $rules['kml_files.*'] = 'file';
        }

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => implode('<br>', $validator->errors()->all())
            ]);
        }

        if ($this->request->hasFile('kml_files')) {
            $uploadedFiles = $this->request->file('kml_files');
            foreach ($uploadedFiles as $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension !== 'kml') {
                    return response()->json([
                        'result' => false,
                        'message' => 'Chỉ chấp nhận file định dạng .kml'
                    ]);
                }
                if ($file->getSize() > 10 * 1024 * 1024) {
                    return response()->json([
                        'result' => false,
                        'message' => 'Kích thước file KML tối đa là 10MB'
                    ]);
                }

                // Rename and save securely
                $fileName = time() . '_' . uniqid() . '.' . $extension;
                $path = $file->storeAs('plandoffices', $fileName, 'public');
                $kml_files[] = $path;
            }
        }

        $plandoffice->kml_file = empty($kml_files) ? null : implode('||', $kml_files);

        DB::beginTransaction();
        try {
            $plandoffice->name = $this->request->input('name');
            $plandoffice->province_id = $this->request->input('province_id') ?: 0;
            $plandoffice->area = 0;
            $plandoffice->save();
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Lưu thông tin cấu hình quy hoạch thành công'
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
        if (!has_permission('plandoffices', 'delete')) {
            return response()->json(['result' => false, 'message' => 'Bạn không có quyền xóa cấu hình quy hoạch.']);
        }

        $plandoffice = Plandoffice::find($id);
        if (!$plandoffice) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy quy hoạch.']);
        }

        $kml_files = [];
        if (!empty($plandoffice->kml_file)) {
            $decoded = json_decode($plandoffice->kml_file, true);
            if (is_array($decoded)) {
                $kml_files = $decoded;
            } else {
                $kml_files = explode('||', $plandoffice->kml_file);
            }
        }
        $kml_files = array_filter(array_map('trim', $kml_files));

        DB::beginTransaction();
        try {
            // Delete KML files from storage
            foreach ($kml_files as $file) {
                Storage::disk('public')->delete($file);
            }

            // Delete associated parcels
            DB::table('tbl_plandoffice_parcels')->where('plandoffice_id', $id)->delete();

            // Delete office
            $plandoffice->delete();

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Xóa cấu hình quy hoạch thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra khi xóa: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteKmlFile()
    {
        if (!has_permission('plandoffices', 'edit')) {
            return response()->json(['result' => false, 'message' => 'Bạn không có quyền sửa cấu hình quy hoạch.']);
        }

        $fileName = $this->request->input('file_name');
        $id = $this->request->input('id');
        $plandoffice = Plandoffice::find($id);
        if (!$plandoffice || empty($fileName)) {
            return response()->json(['result' => false, 'message' => 'Tham số không hợp lệ.']);
        }

        $kml_files = [];
        if (!empty($plandoffice->kml_file)) {
            $decoded = json_decode($plandoffice->kml_file, true);
            if (is_array($decoded)) {
                $kml_files = $decoded;
            } else {
                $kml_files = explode('||', $plandoffice->kml_file);
            }
        }
        $kml_files = array_filter(array_map('trim', $kml_files));

        if (($key = array_search($fileName, $kml_files)) !== false) {
            unset($kml_files[$key]);
            Storage::disk('public')->delete($fileName);
        }

        $plandoffice->kml_file = empty($kml_files) ? null : implode('||', $kml_files);
        $plandoffice->save();

        return response()->json([
            'result' => true,
            'message' => 'Xóa file KML thành công.'
        ]);
    }

    public function changeStatus($id)
    {
        if (!has_permission('plandoffices', 'edit')) {
            return response()->json(['result' => false, 'message' => 'Bạn không có quyền sửa cấu hình quy hoạch.']);
        }

        $plandoffice = Plandoffice::find($id);
        if (!$plandoffice) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy cấu hình quy hoạch.']);
        }

        try {
            $plandoffice->active = $this->request->input('status') == 0 ? 1 : 0;
            $plandoffice->save();

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
        if (!has_permission('plandoffices', 'view')) {
            access_denied(true);
        }

        $plandoffice = Plandoffice::find($id);
        if (!$plandoffice) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy cấu hình quy hoạch.']);
        }

        $kml_files = [];
        if (!empty($plandoffice->kml_file)) {
            $decoded = json_decode($plandoffice->kml_file, true);
            if (is_array($decoded)) {
                $kml_files = $decoded;
            } else {
                $kml_files = explode('||', $plandoffice->kml_file);
            }
        }
        $kml_files = array_filter(array_map('trim', $kml_files));

        $kml_urls = [];
        foreach ($kml_files as $file) {
            $kml_urls[] = asset('storage/' . $file);
        }

        $default_so_to = DB::table('tbl_plandoffice_parcels')
            ->where('plandoffice_id', $id)
            ->whereNotNull('so_to')
            ->where('so_to', '<>', '')
            ->groupBy('so_to')
            ->orderByRaw('COUNT(*) DESC')
            ->value('so_to') ?? '';

        return view('admin.plandoffice.view_map', [
            'plandoffice' => $plandoffice,
            'kml_urls' => $kml_urls,
            'default_so_to' => $default_so_to,
        ]);
    }

    public function extractParcels()
{
    if (!has_permission('plandoffices', 'edit')) {
        return response()->json([
            'result' => false,
            'message' => 'Bạn không có quyền cập nhật quy hoạch.'
        ]);
    }

    $id = $this->request->input('id');

    /**
     * Nếu muốn nhập số tờ mặc định từ form thì truyền thêm so_to_default.
     * Ví dụ: request có so_to_default = 12
     */
    $so_to_default = trim((string) $this->request->input('so_to_default', ''));

    $plandoffice = Plandoffice::find($id);

    if (!$plandoffice || empty($plandoffice->kml_file)) {
        return response()->json([
            'result' => false,
            'message' => 'Vui lòng upload file KML trước khi lấy dữ liệu.'
        ]);
    }

    $kml_files = [];

    if (!empty($plandoffice->kml_file)) {
        $decoded = json_decode($plandoffice->kml_file, true);

        if (is_array($decoded)) {
            $kml_files = $decoded;
        } else {
            $kml_files = explode('||', $plandoffice->kml_file);
        }
    }

    $kml_files = array_filter(array_map('trim', $kml_files));

    if (empty($kml_files)) {
        return response()->json([
            'result' => false,
            'message' => 'Vui lòng upload file KML trước khi lấy dữ liệu.'
        ]);
    }

    try {
        set_time_limit(300);

        $parcels = [];
        $debugFiles = [];

        foreach ($kml_files as $kml_file) {
            $filePath = storage_path('app/public/' . $kml_file);

            if (!file_exists($filePath)) {
                continue;
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_file($filePath);

            if ($xml === false) {
                continue;
            }

            $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
            $placemarks = $xml->xpath('//kml:Placemark');

            $geometries = [];
            foreach ($placemarks as $placemark) {
                $hasPoly = !empty($placemark->xpath('.//kml:Polygon'));
                $hasLine = !empty($placemark->xpath('.//kml:LineString'));
                $hasMulti = !empty($placemark->xpath('.//kml:MultiGeometry'));

                if ($hasPoly || $hasLine || $hasMulti) {
                    $coord_els = $placemark->xpath('.//kml:coordinates');
                    if (!empty($coord_els)) {
                        $coords = $this->parseCoordinates((string)$coord_els[0]);
                        if (count($coords) >= 3) {
                            $centroid = $this->getPolygonCentroid($coords);
                            if ($centroid) {
                                $geometries[] = [
                                    'coords' => $coords,
                                    'centroid' => $centroid
                                ];
                            }
                        }
                    }
                }
            }

            $points_by_type = [
                'số thửa' => [],
                'diện tích' => [],
                'công trình nhà' => [],
                'số tờ' => [],
                'đất ở đô thị' => [],
                'tên chủ' => []
            ];

            foreach ($placemarks as $placemark) {
                $name = isset($placemark->name)
                    ? $this->cleanUtf8String((string) $placemark->name)
                    : '';

                $description = isset($placemark->description)
                    ? $this->cleanUtf8String((string) $placemark->description)
                    : '';

                $styleUrl = isset($placemark->styleUrl)
                    ? trim((string) $placemark->styleUrl)
                    : '';

                // Chỉ lấy Point label
                $point = $placemark->xpath('.//kml:Point');

                if (empty($point)) {
                    continue;
                }

                $coord_el = $point[0]->xpath('.//kml:coordinates');

                if (empty($coord_el)) {
                    continue;
                }

                $coord_text = trim((string) $coord_el[0]);
                $parts = explode(',', $coord_text);

                if (count($parts) < 2) {
                    continue;
                }

                $lng = (float) $parts[0];
                $lat = (float) $parts[1];

                $pt_type = $this->classifyPointByContent($name, $styleUrl, $description);

                if ($pt_type && isset($points_by_type[$pt_type])) {
                    $points_by_type[$pt_type][] = [
                        'name' => trim($name),
                        'style' => $styleUrl,
                        'desc' => $description,
                        'coord' => [
                            'lng' => $lng,
                            'lat' => $lat
                        ]
                    ];
                }
            }

            $so_thua_list = $points_by_type['số thửa'];
            $dien_tich_list = $points_by_type['diện tích'];
            $cong_trinh_list = $points_by_type['công trình nhà'];
            $so_to_list = $points_by_type['số tờ'];
            $loai_dat_list = $points_by_type['đất ở đô thị'];
            $ten_chu_list = $points_by_type['tên chủ'];

            /**
             * Debug để bạn xem file lấy ra được bao nhiêu điểm.
             * Khi chạy ổn rồi thì có thể bỏ phần $debugFiles này.
             */
            $debugFiles[] = [
                'file' => $kml_file,
                'so_thua' => count($so_thua_list),
                'dien_tich' => count($dien_tich_list),
                'loai_dat' => count($loai_dat_list),
                'so_to' => count($so_to_list),
                'cong_trinh' => count($cong_trinh_list),
                'ten_chu' => count($ten_chu_list),
                'sample_so_thua' => array_slice($so_thua_list, 0, 5),
                'sample_dien_tich' => array_slice($dien_tich_list, 0, 5),
                'sample_loai_dat' => array_slice($loai_dat_list, 0, 5),
            ];

            /**
             * Khoảng cách ghép Point.
             * 0.00015 tương đương khoảng 15-20m tùy khu vực.
             * Nếu thấy không ghép được diện tích/loại đất thì tăng lên 0.0002.
             */
            $THRESHOLD = 0.00015;

            foreach ($so_thua_list as $st) {
                $st_coord = $st['coord'];
                $so_thua_val = trim($st['name']);

                if ($so_thua_val === '') {
                    continue;
                }

                // Tìm diện tích gần nhất
                $best_dt = null;
                $min_dt_dist = INF;

                foreach ($dien_tich_list as $dt) {
                    $d = $this->calcDistance($st_coord, $dt['coord']);

                    if ($d < $min_dt_dist) {
                        $min_dt_dist = $d;
                        $best_dt = $dt;
                    }
                }

                // Tìm công trình gần nhất
                $best_ct = null;
                $min_ct_dist = INF;

                foreach ($cong_trinh_list as $ct) {
                    $d = $this->calcDistance($st_coord, $ct['coord']);

                    if ($d < $min_ct_dist) {
                        $min_ct_dist = $d;
                        $best_ct = $ct;
                    }
                }

                // Tìm số tờ gần nhất
                $best_to = null;
                $min_to_dist = INF;

                foreach ($so_to_list as $to) {
                    $d = $this->calcDistance($st_coord, $to['coord']);

                    if ($d < $min_to_dist) {
                        $min_to_dist = $d;
                        $best_to = $to;
                    }
                }

                // Tìm loại đất gần nhất
                $best_ld = null;
                $min_ld_dist = INF;

                foreach ($loai_dat_list as $ld) {
                    $d = $this->calcDistance($st_coord, $ld['coord']);

                    if ($d < $min_ld_dist) {
                        $min_ld_dist = $d;
                        $best_ld = $ld;
                    }
                }

                // Tìm tên chủ gần nhất
                $best_tc = null;
                $min_tc_dist = INF;

                foreach ($ten_chu_list as $tc) {
                    $d = $this->calcDistance($st_coord, $tc['coord']);

                    if ($d < $min_tc_dist) {
                        $min_tc_dist = $d;
                        $best_tc = $tc;
                    }
                }

                $dien_tich_val = ($best_dt && $min_dt_dist < $THRESHOLD)
                    ? trim($best_dt['name'])
                    : '';

                $cong_trinh_val = ($best_ct && $min_ct_dist < $THRESHOLD)
                    ? trim($best_ct['name'])
                    : '';

                $loai_dat_val = ($best_ld && $min_ld_dist < $THRESHOLD)
                    ? trim($best_ld['name'])
                    : '';

                /**
                 * Quan trọng:
                 * Không lấy số tờ vô điều kiện.
                 * Vì TEST.kml không có số tờ nên phải check khoảng cách.
                 */
                $so_to_val = ($best_to && $min_to_dist < $THRESHOLD)
                    ? trim($best_to['name'])
                    : '';

                /**
                 * Nếu file không có số tờ thì dùng số tờ nhập ngoài form.
                 * Nếu không nhập thì để null.
                 */
                if ($so_to_val === '' && $so_to_default !== '') {
                    $so_to_val = $so_to_default;
                }

                $ten_chu_val = ($best_tc && $min_tc_dist < $THRESHOLD)
                    ? trim($best_tc['name'])
                    : '';

                if ($dien_tich_val !== '') {
                    $dien_tich_val = str_replace(',', '.', $dien_tich_val);
                    $dien_tich_val = preg_replace('/[^0-9.]/', '', $dien_tich_val);
                    $dien_tich_val = $dien_tich_val !== '' ? (float) $dien_tich_val : null;
                } else {
                    $dien_tich_val = null;
                }

                // Tìm geometry (đa giác) gần nhất dựa trên tâm
                $best_geo = null;
                $min_geo_dist = INF;
                foreach ($geometries as $geo) {
                    $d = $this->calcDistance($st_coord, $geo['centroid']);
                    if ($d < $min_geo_dist) {
                        $min_geo_dist = $d;
                        $best_geo = $geo;
                    }
                }

                $geo_coords = null;
                // Threshold 35m (0.00035 độ)
                if ($best_geo && $min_geo_dist < 0.00035) {
                    $geo_coords = json_encode($best_geo['coords']);
                }

                $parcels[] = [
                    'plandoffice_id' => $plandoffice->id,
                    'so_to' => $so_to_val !== '' ? $so_to_val : null,
                    'so_thua' => $so_thua_val !== '' ? $so_thua_val : null,
                    'dien_tich' => $dien_tich_val,
                    'cong_trinh' => $cong_trinh_val !== '' ? $cong_trinh_val : null,
                    'loai_dat' => $loai_dat_val !== '' ? $loai_dat_val : null,
                    'ten_chu' => $ten_chu_val !== '' ? $ten_chu_val : null,
                    'lat' => $st_coord['lat'],
                    'lng' => $st_coord['lng'],
                    'coords' => $geo_coords,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        /**
         * Nếu muốn test trước chưa lưu DB thì mở đoạn này.
         */
        // return response()->json([
        //     'result' => true,
        //     'debug' => $debugFiles,
        //     'total_parcels' => count($parcels),
        //     'sample_parcels' => array_slice($parcels, 0, 20)
        // ]);

        /**
         * Lấy danh sách đã có để tránh trùng.
         * Nếu so_to null thì key sẽ là "NO_TO-số_thửa".
         */
        $existingParcels = DB::table('tbl_plandoffice_parcels')
            ->where('plandoffice_id', $plandoffice->id)
            ->get()
            ->groupBy(function ($item) {
                $soTo = trim((string) $item->so_to);
                $soThua = trim((string) $item->so_thua);

                if ($soTo === '') {
                    $soTo = 'NO_TO';
                }

                return $soTo . '-' . $soThua;
            })
            ->toArray();

        $newParcels = [];
        $totalUpdated = 0;

        foreach ($parcels as $p) {
            if (empty($p['so_thua'])) {
                continue;
            }

            $soToKey = trim((string) $p['so_to']);

            if ($soToKey === '') {
                $soToKey = 'NO_TO';
            }

            $key = $soToKey . '-' . trim((string) $p['so_thua']);

            if (isset($existingParcels[$key])) {
                $existing = $existingParcels[$key][0];
                $updateData = [
                    'lat' => $p['lat'],
                    'lng' => $p['lng'],
                    'coords' => $p['coords'],
                    'updated_at' => now()
                ];

                if (empty($existing->dien_tich) && $p['dien_tich'] !== null) {
                    $updateData['dien_tich'] = $p['dien_tich'];
                }
                if (empty($existing->loai_dat) && $p['loai_dat'] !== null) {
                    $updateData['loai_dat'] = $p['loai_dat'];
                }
                if (empty($existing->cong_trinh) && $p['cong_trinh'] !== null) {
                    $updateData['cong_trinh'] = $p['cong_trinh'];
                }
                if (empty($existing->ten_chu) && $p['ten_chu'] !== null) {
                    $updateData['ten_chu'] = $p['ten_chu'];
                }

                DB::table('tbl_plandoffice_parcels')
                    ->where('id', $existing->id)
                    ->update($updateData);
                $totalUpdated++;
            } else {
                $newParcels[] = $p;
            }
        }

        DB::beginTransaction();

        try {
            if (!empty($newParcels)) {
                foreach (array_chunk($newParcels, 500) as $chunk) {
                    DB::table('tbl_plandoffice_parcels')->insert($chunk);
                }
            }

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Trích xuất dữ liệu thành công! Đã thêm ' . count($newParcels) . ' số thửa mới, cập nhật tọa độ ' . $totalUpdated . ' thửa cũ.',
                'debug' => $debugFiles,
                'total_found' => count($parcels),
                'total_inserted' => count($newParcels),
                'total_updated' => $totalUpdated
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();

            return response()->json([
                'result' => false,
                'message' => 'Lỗi lưu database: ' . $ex->getMessage()
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'result' => false,
            'message' => 'Lỗi xử lý file KML: ' . $e->getMessage()
        ]);
    }
}

    private function classifyPointByContent($name, $style, $desc)
{
    if (empty($name)) {
        return null;
    }

    $clean_name = trim(str_replace("\xEF\xBB\xBF", '', $name));
    $style = trim($style);
    $desc = trim($desc);

    if ($clean_name === '') {
        return null;
    }

    /**
     * =========================================================
     * CASE 1: TEST.kml
     *
     * description = Nhan Thua
     *
     * #InPointMap_Point_005 => số thửa
     * #InPointMap_Point_004 => diện tích
     * #InPointMap_Point_000 => loại đất
     * =========================================================
     */
    if ($desc === 'Nhan Thua') {
        if ($style === '#InPointMap_Point_005' && preg_match('/^\d+$/', $clean_name)) {
            return 'số thửa';
        }

        if ($style === '#InPointMap_Point_004' && preg_match('/^\d+([,.]\d+)?$/', $clean_name)) {
            return 'diện tích';
        }

        if ($style === '#InPointMap_Point_000') {
            return 'đất ở đô thị';
        }

        return null;
    }

    /**
     * =========================================================
     * CASE 2: dctest.kml / file cũ
     * =========================================================
     */

    // Diện tích file cũ
    if ($style === '#InPointMap_Point_003' && $desc === '13' && preg_match('/^\d+([,.]\d+)?$/', $clean_name)) {
        return 'diện tích';
    }

    // Số tờ file cũ nếu có
    if ($style === '#InPointMap_Point_007' && $desc === '1') {
        return 'số tờ';
    }

    // Tên chủ file cũ
    if ($style === '#InPointMap_Point_004' && $desc === '4') {
        return 'tên chủ';
    }

    // Số thửa file cũ
    if ($style === '#InPointMap_Point_002' && $desc === '13' && preg_match('/^\d+$/', $clean_name)) {
        return 'số thửa';
    }

    // Loại đất file cũ
    if ($style === '#InPointMap_Point_001' && in_array($desc, ['13', '4', '3', '53'])) {
        return 'đất ở đô thị';
    }

    /**
     * =========================================================
     * Mã loại đất fallback
     * =========================================================
     */
    $land_codes = [
        'ODT', 'CLN', 'BCS', 'CQP', 'DCK', 'DGT', 'DNL', 'DTL', 'TIN', 'ONT',
        'HNK', 'LNK', 'BHK', 'TSC', 'DVH', 'DKN', 'DHT', 'DXH', 'DGD', 'DKT',
        'DDT', 'DSH', 'DKV', 'CANH', 'RPT', 'RSN', 'MNC', 'TONG', 'LUC', 'SKC',
        'TMD', 'NTS', 'SON', 'PNK', 'DRA', 'NTD', 'DTS', 'DTT', 'DCH', 'DNG',
        'NKH', 'LMU', 'NCS', 'RDD', 'RSX', 'RPH'
    ];

    $upper_name = strtoupper($clean_name);

    // Loại đất đơn: ODT, CLN, DTL...
    if (in_array($upper_name, $land_codes)) {
        return 'đất ở đô thị';
    }

    // Loại đất ghép: ODT+CLN, ODT+BHK...
    if (strpos($clean_name, '+') !== false) {
        $parts = explode('+', $clean_name);
        $all_in = true;

        foreach ($parts as $part) {
            if (!in_array(strtoupper(trim($part)), $land_codes)) {
                $all_in = false;
                break;
            }
        }

        if ($all_in) {
            return 'đất ở đô thị';
        }
    }

    /**
     * =========================================================
     * Công trình nhà
     * =========================================================
     */
    $is_construction_style =
        ($style === '#InPointMap_Point_000' && $desc === '15') ||
        ($style === '#InPointMap_Point_006' && $desc === 'TEXT') ||
        ($style === '#InPointMap_Point_003' && $desc === 'Level 24');

    if (
        $is_construction_style ||
        in_array($clean_name, ['b', 'g', 't', 'B', 'G', 'T', 'b2', 'b3', 'b4', 'b5', 'g2', 'B2', 'B3', 'B4', 'B5', 'G2']) ||
        (
            strtoupper(mb_substr($clean_name, 0, 1, 'UTF-8')) === 'B' &&
            mb_strlen($clean_name, 'UTF-8') <= 3 &&
            preg_match('/\d/', $clean_name)
        )
    ) {
        return 'công trình nhà';
    }

    /**
     * =========================================================
     * Bỏ qua nhãn đường/khu/vị trí
     * =========================================================
     */
    $ignore_keywords = [
        'Khu', 'Tổ', 'Đường', 'Phường', 'Ngõ', 'UBND', 'Trường', 'Sông',
        'PHỐ', 'VỊNH', 'THÔN', 'XÓM', 'TỈNH', 'HUYỆN', 'XÃ'
    ];

    foreach ($ignore_keywords as $keyword) {
        if (mb_stripos($clean_name, $keyword, 0, 'UTF-8') !== false) {
            return null;
        }
    }

    /**
     * =========================================================
     * Tên chủ fallback
     * Có khoảng trắng và bắt đầu bằng chữ.
     * Ví dụ: Nguyễn Văn A
     * =========================================================
     */
    if (strpos($clean_name, ' ') !== false) {
        $first_char = mb_substr($clean_name, 0, 1, 'UTF-8');

        if (preg_match('/^\p{L}$/u', $first_char)) {
            return 'tên chủ';
        }
    }

    /**
     * =========================================================
     * Số thửa fallback
     * Chỉ nhận số nguyên.
     * Không nhận 68,4 hoặc 300,0 vì đó thường là diện tích.
     * =========================================================
     */
    if (preg_match('/^\d+$/', $clean_name)) {
        return 'số thửa';
    }

    return null;
}

    private function calcDistance($coord1, $coord2)
{
    $lng1 = isset($coord1['lng']) ? (float) $coord1['lng'] : 0;
    $lat1 = isset($coord1['lat']) ? (float) $coord1['lat'] : 0;

    $lng2 = isset($coord2['lng']) ? (float) $coord2['lng'] : 0;
    $lat2 = isset($coord2['lat']) ? (float) $coord2['lat'] : 0;

    $dx = $lng1 - $lng2;
    $dy = $lat1 - $lat2;

    return sqrt(($dx * $dx) + ($dy * $dy));
}

    private function parseCoordinates($coordText)
{
    $coords = [];
    $coordText = trim((string)$coordText);
    if (empty($coordText)) {
        return $coords;
    }

    $points = preg_split('/\s+/', $coordText);
    foreach ($points as $p) {
        $parts = explode(',', trim($p));
        if (count($parts) >= 2) {
            $lng = (float)$parts[0];
            $lat = (float)$parts[1];
            $coords[] = [$lat, $lng];
        }
    }
    return $coords;
}

    private function getPolygonCentroid($polygon)
{
    $latSum = 0;
    $lngSum = 0;
    $count = count($polygon);
    if ($count === 0) return null;

    foreach ($polygon as $pt) {
        $latSum += $pt[0];
        $lngSum += $pt[1];
    }
    return [
        'lat' => $latSum / $count,
        'lng' => $lngSum / $count
    ];
}

    private function cleanUtf8String($string)
{
    if ($string === null) {
        return '';
    }

    $string = (string) $string;

    // Xóa BOM
    $string = str_replace("\xEF\xBB\xBF", '', $string);

    // Decode HTML entity nếu KML có mã hóa
    $string = html_entity_decode($string, ENT_QUOTES | ENT_XML1, 'UTF-8');

    // Xóa ký tự control
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string);

    return trim($string);
}

    public function getParcelInfo()
    {
        if (!has_permission('plandoffices', 'view')) {
            return response()->json(['result' => false, 'message' => 'Access denied']);
        }

        $so_thua = $this->request->input('so_thua');
        if (empty($so_thua)) {
            return response()->json(['result' => false, 'message' => 'Missing parameter so_thua']);
        }

        $plandoffice_id = $this->request->input('plandoffice_id');
        $so_to = $this->request->input('so_to');

        $query = DB::table('tbl_plandoffice_parcels')
            ->where('so_thua', $so_thua);

        if (!empty($plandoffice_id)) {
            $query->where('plandoffice_id', $plandoffice_id);
        }
        if (!empty($so_to)) {
            $query->where('so_to', $so_to);
        }

        $parcel = $query->first();

        if ($parcel) {
            return response()->json([
                'result' => true,
                'data' => $parcel
            ]);
        }

        return response()->json([
            'result' => false,
            'message' => 'Không tìm thấy thông tin thửa đất trong CSDL.'
        ]);
    }

    public function getMapData($id)
    {
        if (!has_permission('plandoffices', 'view')) {
            return response()->json(['result' => false, 'message' => 'Access denied']);
        }

        $parcels = DB::table('tbl_plandoffice_parcels')
            ->where('plandoffice_id', $id)
            ->whereNotNull('coords')
            ->select(['id', 'so_to', 'so_thua', 'dien_tich', 'loai_dat', 'cong_trinh', 'ten_chu', 'lat', 'lng', 'coords'])
            ->get();

        return response()->json([
            'result' => true,
            'data' => $parcels
        ]);
    }

    public function edit_parcel($id)
    {
        if (!has_permission('plandoffices', 'edit')) {
            access_denied(true);
        }

        $parcel = DB::table('tbl_plandoffice_parcels')->where('id', $id)->first();
        if (!$parcel) {
            abort(404, 'Không tìm thấy thửa đất.');
        }

        // Decode loai_dat_quy_hoach JSON string
        $loai_dat_quy_hoach = [];
        if (!empty($parcel->loai_dat_quy_hoach)) {
            $decoded = json_decode($parcel->loai_dat_quy_hoach, true);
            if (is_array($decoded)) {
                $loai_dat_quy_hoach = $decoded;
            }
        }

        return view('admin.plandoffice.edit_parcel', [
            'title' => 'Sửa thông tin thửa đất',
            'parcel' => $parcel,
            'loai_dat_quy_hoach' => $loai_dat_quy_hoach,
        ]);
    }

    public function update_parcel(Request $request, $id)
    {
        if (!has_permission('plandoffices', 'edit')) {
            return response()->json(['result' => false, 'message' => 'Bạn không có quyền sửa thửa đất.']);
        }

        $parcel = DB::table('tbl_plandoffice_parcels')->where('id', $id)->first();
        if (!$parcel) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy thửa đất.']);
        }

        $validator = Validator::make($request->all(), [
            'so_to' => 'nullable|string|max:50',
            'so_thua' => 'nullable|string|max:50',
            'dien_tich' => 'nullable|numeric|min:0',
            'loai_dat' => 'nullable|string|max:100',
            'cong_trinh' => 'nullable|string|max:100',
            'ten_chu' => 'nullable|string|max:255',
            'mo_ta_thua' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => implode('<br>', $validator->errors()->all())
            ]);
        }

        // Process loai_dat_quy_hoach dynamic array
        $types = $request->input('loai_dat_quy_hoach_type', []);
        $areas = $request->input('loai_dat_quy_hoach_area', []);
        $percentages = $request->input('loai_dat_quy_hoach_percentage', []);

        $planning_list = [];
        if (is_array($types)) {
            for ($i = 0; $i < count($types); $i++) {
                if (isset($types[$i]) && trim($types[$i]) !== '') {
                    $planning_list[] = [
                        'type' => trim($types[$i]),
                        'area' => isset($areas[$i]) ? (float)$areas[$i] : null,
                        'percentage' => isset($percentages[$i]) ? (float)$percentages[$i] : null,
                    ];
                }
            }
        }

        $loai_dat_quy_hoach_json = !empty($planning_list) ? json_encode($planning_list, JSON_UNESCAPED_UNICODE) : null;

        try {
            DB::table('tbl_plandoffice_parcels')->where('id', $id)->update([
                'so_to' => $request->input('so_to'),
                'so_thua' => $request->input('so_thua'),
                'dien_tich' => $request->input('dien_tich') !== null ? (float)$request->input('dien_tich') : null,
                'loai_dat' => $request->input('loai_dat'),
                'cong_trinh' => $request->input('cong_trinh'),
                'ten_chu' => $request->input('ten_chu'),
                'loai_dat_quy_hoach' => $loai_dat_quy_hoach_json,
                'mo_ta_thua' => $request->input('mo_ta_thua'),
                'updated_at' => now(),
            ]);

            return response()->json([
                'result' => true,
                'message' => 'Cập nhật thửa đất thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
}
