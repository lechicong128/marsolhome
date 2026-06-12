<?php

namespace App\Http\Controllers\Api_app;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlandofficeController extends AuthController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }
    function GetInfoParcels($id = '') {
        if (empty($id)) {
            $id = $this->request->input('id');
        }

        if (empty($id)) {
            return response()->json([
                'result' => false,
                'message' => 'Thiếu tham số id.'
            ], 400);
        }

        try {
            $parcel = DB::table('tbl_plandoffice_parcels')
                ->where('id', $id)
                ->first();

            if (!$parcel) {
                return response()->json([
                    'result' => false,
                    'message' => 'Không tìm thấy thông tin thửa đất.'
                ], 404);
            }

            // Format coordinates and other json fields
            if ($parcel->coords) {
                $decodedCoords = json_decode($parcel->coords, true);
                $parcel->coords = is_array($decodedCoords) ? $decodedCoords : [];
            } else {
                $parcel->coords = [];
            }

            if ($parcel->loai_dat_quy_hoach) {
                $decodedPlanning = json_decode($parcel->loai_dat_quy_hoach, true);
                $parcel->loai_dat_quy_hoach = is_array($decodedPlanning) ? $decodedPlanning : [];
            } else {
                $parcel->loai_dat_quy_hoach = [];
            }

            return response()->json([
                'result' => true,
                'data' => $parcel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Lỗi truy vấn dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getNearbyParcels()
    {
        $latitude = $this->request->input('lat');
        $longitude = $this->request->input('lng');
        $radius = $this->request->input('bankinh', 5); // Default to 5 km

        if (empty($latitude) || empty($longitude)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng cung cấp tọa độ lat và lng.'
            ], 400);
        }

        $latitude = (float)$latitude;
        $longitude = (float)$longitude;
        $radius = (float)$radius;

        // Bounding Box check first for optimal index usage:
        // 1 degree of latitude is roughly 111.04 km
        $dLat = $radius / 111.04;
        // 1 degree of longitude at a given latitude is roughly 111.04 * cos(lat)
        $dLng = $radius / (111.04 * abs(cos(deg2rad($latitude))));

        $minLat = $latitude - $dLat;
        $maxLat = $latitude + $dLat;
        $minLng = $longitude - $dLng;
        $maxLng = $longitude + $dLng;

        // Calculate distance using spherical law of cosines in raw SQL
        // Radius of the earth is 6371 km
        $distanceSql = "(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat))))";

        try {
            $parcels = DB::table('tbl_plandoffice_parcels')
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->whereBetween('lat', [$minLat, $maxLat])
                ->whereBetween('lng', [$minLng, $maxLng])
                ->select([
                    'id',
                    'plandoffice_id',
                    'so_to',
                    'so_thua',
                    'dien_tich',
                    'cong_trinh',
                    'loai_dat',
                    'ten_chu',
                    'loai_dat_quy_hoach',
                    'mo_ta_thua',
                    'lat',
                    'lng',
                    'coords',
                    'created_at',
                    'updated_at'
                ])
                ->selectRaw("{$distanceSql} AS distance", [$latitude, $longitude, $latitude])
                ->having('distance', '<=', $radius)
                ->orderBy('distance', 'asc')
                ->get();

            // Format coordinates and other json fields
            $formattedParcels = $parcels->map(function ($item) {
                if ($item->coords) {
                    $decodedCoords = json_decode($item->coords, true);
                    $item->coords = is_array($decodedCoords) ? $decodedCoords : [];
                } else {
                    $item->coords = [];
                }

                if ($item->loai_dat_quy_hoach) {
                    $decodedPlanning = json_decode($item->loai_dat_quy_hoach, true);
                    $item->loai_dat_quy_hoach = is_array($decodedPlanning) ? $decodedPlanning : [];
                } else {
                    $item->loai_dat_quy_hoach = [];
                }

                $item->distance = round((float)$item->distance, 3); // Round to 3 decimals (meters precision)
                return $item;
            });

            return response()->json([
                'result' => true,
                'data' => $formattedParcels
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Lỗi truy vấn dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }
}
