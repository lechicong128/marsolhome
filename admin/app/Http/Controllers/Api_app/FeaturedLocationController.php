<?php

namespace App\Http\Controllers\Api_app;

use App\Models\FeaturedLocation;
use App\Models\Home;
use App\Models\TypeProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeaturedLocationController extends AuthController
{
    public function getList()
    {
        $baseUrl = config('services.storage.url');
        
        $propertyTypeId = $this->request->input('property_type_id') ?? $this->request->input('property_type_search') ?? $this->request->input('property_type');
        
        $propertyTypeIds = [];
        if (!empty($propertyTypeId)) {
            if (is_array($propertyTypeId)) {
                $propertyTypeIds = $propertyTypeId;
            } elseif (is_string($propertyTypeId) && strpos($propertyTypeId, ',') !== false) {
                $propertyTypeIds = explode(',', $propertyTypeId);
            } elseif (is_numeric($propertyTypeId)) {
                $propertyTypeIds = [$propertyTypeId];
            } else {
                $matchedType = DB::table('tbl_type_property')
                    ->where('name', 'like', '%' . $propertyTypeId . '%')
                    ->first();
                if ($matchedType) {
                    $propertyTypeIds = [$matchedType->id];
                }
            }
        }

        if (empty($propertyTypeIds)) {
            $firstType = TypeProperty::orderBy('id', 'asc')->first();
            $propertyTypeIds = $firstType ? [$firstType->id] : [];
        }

        $activeLocations = FeaturedLocation::where('is_active', 1)
            ->orderBy('display_order', 'asc')
            ->get();
            
        $provinceIds = $activeLocations->pluck('province_id')->filter()->unique()->toArray();
        
        $counts = [];
        if (!empty($provinceIds)) {
            $today = date('Y-m-d');
            $query = Home::where('status', 2)
                ->whereIn('province_id', $provinceIds);
//                ->where(function ($q) use ($today) {
//                    $q->whereNull('tbl_home.end_date')
//                      ->orWhere('tbl_home.end_date', '>=', $today);
//                });

            if (!empty($propertyTypeIds)) {
                $query->whereIn('property_type', $propertyTypeIds);
            }

            $counts = $query->select('province_id', DB::raw('count(*) as total'))
                ->groupBy('province_id')
                ->pluck('total', 'province_id')
                ->toArray();
        }
        
        $data = $activeLocations->map(function ($item) use ($baseUrl, $counts) {
            $count = isset($counts[$item->province_id]) ? (int)$counts[$item->province_id] : 0;
            
            $imageUrl = '';
            if (!empty($item->image_url)) {
                if (strpos($item->image_url, 'http') === 0) {
                    $imageUrl = $item->image_url;
                } else {
                    $imageUrl = $baseUrl . '/' . $item->image_url;
                }
            }
            
            return [
                'id' => $item->id,
                'province_id' => $item->province_id,
                'custom_name' => $item->custom_name ?? '',
                'image_url' => $imageUrl,
                'display_order' => $item->display_order,
                'active_listings_count' => $count
            ];
        });
        
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách địa điểm nổi bật thành công'
        ]);
    }
}
