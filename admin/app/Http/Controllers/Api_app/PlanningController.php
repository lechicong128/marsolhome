<?php

namespace App\Http\Controllers\Api_app;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanningController extends AuthController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getList()
    {
        $currentPage = $this->request->query('current_page', 1);
        $perPage = $this->request->query('per_page', 10);
        $provinceId = $this->request->query('province_id');
        $search = $this->request->query('search');
        $planningType = $this->request->query('planning_type');
        $status = $this->request->query('status');

        $query = DB::table('tbl_plannings as p')
            ->leftJoin('tbl_provinces as pr', 'pr.id', '=', 'p.province_id')
            ->select(['p.*', 'pr.name as province_name'])
            ->where('p.active', 1);

        if (!empty($provinceId)) {
            $query->where('p.province_id', $provinceId);
        }

        if (!empty($search)) {
            $query->where('p.name', 'like', '%' . $search . '%');
        }

        if (!empty($planningType)) {
            $query->where('p.planning_type', $planningType);
        }

        if (!empty($status)) {
            $query->where('p.status', $status);
        }

        $plannings = $query->orderBy('p.id', 'desc')
            ->paginate($perPage, ['*'], '', $currentPage);

        // Map full image and kml URL paths if applicable
        $plannings->getCollection()->transform(function ($item) {
            if ($item->image) {
                $item->image = asset('storage/' . $item->image);
            }
            if ($item->kml_file) {
                $baseName = pathinfo($item->kml_file, PATHINFO_FILENAME);
                $jsonRelativePath = "kmz-cache/{$baseName}.json";
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($jsonRelativePath)) {
                    $item->cache_json_url = asset('storage/' . $jsonRelativePath);
                } else {
                    $item->cache_json_url = null;
                }
                $item->kml_file = asset('storage/' . $item->kml_file);
            } else {
                $item->cache_json_url = null;
            }
            return $item;
        });

        return response()->json([
            'result' => true,
            'data' => $plannings->items(),
            'current_page' => $plannings->currentPage(),
            'last_page' => $plannings->lastPage(),
            'per_page' => $plannings->perPage(),
            'total' => $plannings->total(),
            'next' => $plannings->hasMorePages() ? 1 : 0
        ]);
    }

    public function getDetail($id = '')
    {
        if (empty($id)) {
            $id = $this->request->input('id');
        }

        if (empty($id)) {
            return response()->json([
                'result' => false,
                'message' => 'Thiếu tham số id.'
            ], 400);
        }

        $planning = DB::table('tbl_plannings as p')
            ->leftJoin('tbl_provinces as pr', 'pr.id', '=', 'p.province_id')
            ->select(['p.*', 'pr.name as province_name'])
            ->where('p.id', $id)
            ->where('p.active', 1)
            ->first();

        if (!$planning) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy quy hoạch hoặc đã bị khóa.'
            ], 404);
        }

        if ($planning->image) {
            $planning->image = asset('storage/' . $planning->image);
        }
        if ($planning->kml_file) {
            $baseName = pathinfo($planning->kml_file, PATHINFO_FILENAME);
            $jsonRelativePath = "kmz-cache/{$baseName}.json";
            
            // Trigger processing on-the-fly if cache not present
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($jsonRelativePath)) {
                \App\Services\KmlProcessorService::process($planning->kml_file);
            }

            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($jsonRelativePath)) {
                $planning->cache_json_url = asset('storage/' . $jsonRelativePath);
            } else {
                $planning->cache_json_url = null;
            }
            $planning->kml_file = asset('storage/' . $planning->kml_file);
        } else {
            $planning->cache_json_url = null;
        }

        return response()->json([
            'result' => true,
            'data' => $planning
        ]);
    }

    public function processKmz($filename = '')
    {
        if (empty($filename)) {
            $filename = $this->request->input('filename');
        }

        if (empty($filename)) {
            return response()->json([
                'result' => false,
                'message' => 'Thiếu tên file.'
            ], 400);
        }

        // Look for the file in plannings/
        $kmlFilePath = 'plannings/' . $filename;
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($kmlFilePath)) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy file trên hệ thống.'
            ], 404);
        }

        $result = \App\Services\KmlProcessorService::process($kmlFilePath);
        if ($result) {
            return response()->json([
                'result' => true,
                'data' => $result
            ]);
        }

        return response()->json([
            'result' => false,
            'message' => 'Không thể phân tích dữ liệu KML/KMZ hoặc file không chứa GroundOverlay.'
        ], 500);
    }
}
