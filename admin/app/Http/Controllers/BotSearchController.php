<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BotSearchController extends Controller
{
    /**
     * Scan database listings and generate search suggestions.
     * Simulates background worker caching data for search autocomplete.
     */
    public function scanSuggestions()
    {
        // Fetch all active/approved listings (status = 2)
        $listings = DB::table('tbl_home')
            ->select('ward_id', 'is_new_address', 'type', 'property_type', 'price', 'address')
            ->where('status', 2)
            ->whereNotNull('ward_id')
            ->where('ward_id', '>', 0)
            ->get();

        // 1. Grouping arrays
        $wardGroups = [];
        $wardTypeGroups = [];
        $streetGroups = [];
        $streetTypeGroups = [];

        foreach ($listings as $listing) {
            $pType = $listing->property_type;
            $streetName = $this->extractStreetName($listing->address);

            // Ward general key
            $wKey = $listing->ward_id . '_' . $listing->is_new_address . '_' . $listing->type;
            if (!isset($wardGroups[$wKey])) {
                $wardGroups[$wKey] = $this->initGroupFields($listing->ward_id, $listing->is_new_address, $listing->type);
            }
            $this->incrementGroupFields($wardGroups[$wKey], $listing);

            // Ward + Property Type key
            if ($pType) {
                $wtKey = $wKey . '_' . $pType;
                if (!isset($wardTypeGroups[$wtKey])) {
                    $wardTypeGroups[$wtKey] = $this->initGroupFields($listing->ward_id, $listing->is_new_address, $listing->type);
                    $wardTypeGroups[$wtKey]['property_type'] = $pType;
                }
                $this->incrementGroupFields($wardTypeGroups[$wtKey], $listing);
            }

            // Street general key
            if (!empty($streetName)) {
                $sKey = $streetName . '|' . $listing->ward_id . '|' . $listing->is_new_address . '|' . $listing->type;
                if (!isset($streetGroups[$sKey])) {
                    $streetGroups[$sKey] = $this->initGroupFields($listing->ward_id, $listing->is_new_address, $listing->type);
                    $streetGroups[$sKey]['street'] = $streetName;
                }
                $this->incrementGroupFields($streetGroups[$sKey], $listing);

                // Street + Property Type key
                if ($pType) {
                    $stKey = $sKey . '|' . $pType;
                    if (!isset($streetTypeGroups[$stKey])) {
                        $streetTypeGroups[$stKey] = $this->initGroupFields($listing->ward_id, $listing->is_new_address, $listing->type);
                        $streetTypeGroups[$stKey]['street'] = $streetName;
                        $streetTypeGroups[$stKey]['property_type'] = $pType;
                    }
                    $this->incrementGroupFields($streetTypeGroups[$stKey], $listing);
                }
            }
        }

        // Collect unique ward IDs to batch-fetch names
        $newWardIds = [];
        $oldWardIds = [];
        foreach ($listings as $listing) {
            if ($listing->is_new_address) {
                $newWardIds[] = $listing->ward_id;
            } else {
                $oldWardIds[] = $listing->ward_id;
            }
        }
        $newWardIds = array_unique($newWardIds);
        $oldWardIds = array_unique($oldWardIds);

        // Fetch Ward details
        $newWards = [];
        if (!empty($newWardIds)) {
            $newWards = DB::table('tbl_wards_new')
                ->whereIn('id', $newWardIds)
                ->get()
                ->keyBy('id')
                ->toArray();
        }

        $oldWards = [];
        if (!empty($oldWardIds)) {
            $oldWards = DB::table('tblward')
                ->whereIn('wardid', $oldWardIds)
                ->get()
                ->keyBy('wardid')
                ->toArray();
        }

        // Fetch Property Type names
        $propertyTypes = DB::table('tbl_type_property')->get()->keyBy('id')->toArray();

        $suggestions = [];
        $now = Carbon::now();

        // 3. Generate Ward Suggestions (General)
        foreach ($wardGroups as $item) {
            $wardData = $this->resolveWard($item['ward_id'], $item['is_new_address'], $newWards, $oldWards);
            if (!$wardData) continue;
            
            $suggestions = array_merge(
                $suggestions, 
                $this->buildSuggestionsList($item, $wardData['name'], $wardData['type'], 'BĐS', null, $now)
            );
        }

        // 4. Generate Ward Suggestions (Property Type Specific)
        foreach ($wardTypeGroups as $item) {
            $wardData = $this->resolveWard($item['ward_id'], $item['is_new_address'], $newWards, $oldWards);
            if (!$wardData) continue;

            $pTypeName = $this->resolvePropertyTypeName($item['property_type'], $propertyTypes);
            
            $suggestions = array_merge(
                $suggestions, 
                $this->buildSuggestionsList($item, $wardData['name'], $wardData['type'], $pTypeName, null, $now)
            );
        }

        // 5. Generate Street Suggestions (General)
        foreach ($streetGroups as $item) {
            $wardData = $this->resolveWard($item['ward_id'], $item['is_new_address'], $newWards, $oldWards);
            if (!$wardData) continue;

            $suggestions = array_merge(
                $suggestions, 
                $this->buildSuggestionsList($item, $wardData['name'], $wardData['type'], 'BĐS', $item['street'], $now)
            );
        }

        // 6. Generate Street Suggestions (Property Type Specific)
        foreach ($streetTypeGroups as $item) {
            $wardData = $this->resolveWard($item['ward_id'], $item['is_new_address'], $newWards, $oldWards);
            if (!$wardData) continue;

            $pTypeName = $this->resolvePropertyTypeName($item['property_type'], $propertyTypes);

            $suggestions = array_merge(
                $suggestions, 
                $this->buildSuggestionsList($item, $wardData['name'], $wardData['type'], $pTypeName, $item['street'], $now)
            );
        }

        // Save suggestions to table
        DB::beginTransaction();
        try {
            DB::table('tbl_search_suggestions')->delete();
            if (!empty($suggestions)) {
                // Remove potential duplicate suggestion texts
                $uniqueSuggestions = [];
                foreach ($suggestions as $s) {
                    $key = $s['suggestion'] . '|' . $s['type'];
                    if (!isset($uniqueSuggestions[$key]) || $uniqueSuggestions[$key]['score'] < $s['score']) {
                        $uniqueSuggestions[$key] = $s;
                    }
                }

                foreach (array_chunk(array_values($uniqueSuggestions), 500) as $chunk) {
                    DB::table('tbl_search_suggestions')->insert($chunk);
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã quét và lưu ' . count($suggestions) . ' gợi ý tìm kiếm.',
                'count' => count($suggestions),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu gợi ý: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initialize fields for a grouping record.
     */
    private function initGroupFields($ward_id, $is_new_address, $type)
    {
        return [
            'ward_id' => $ward_id,
            'is_new_address' => $is_new_address,
            'type' => $type,
            'total' => 0,
            'under_1b' => 0,
            'under_2b' => 0,
            'under_3b' => 0,
            'under_5b' => 0,
            'under_5m' => 0,
            'under_10m' => 0,
            'under_15m' => 0,
        ];
    }

    /**
     * Increment grouping counts based on listing price.
     */
    private function incrementGroupFields(&$group, $listing)
    {
        $group['total']++;
        if ($listing->type == 1) { // Mua bán
            if ($listing->price < 1000000000) $group['under_1b']++;
            if ($listing->price < 2000000000) $group['under_2b']++;
            if ($listing->price < 3000000000) $group['under_3b']++;
            if ($listing->price < 5000000000) $group['under_5b']++;
        } elseif ($listing->type == 2) { // Cho thuê
            if ($listing->price < 5000000) $group['under_5m']++;
            if ($listing->price < 10000000) $group['under_10m']++;
            if ($listing->price < 15000000) $group['under_15m']++;
        }
    }

    /**
     * Clean and extract street name from address field.
     */
    private function extractStreetName($address)
    {
        if (empty($address)) {
            return '';
        }
        $parts = explode(',', $address);
        $streetPart = trim($parts[0]);
        
        // Remove prefixes like "Số", "Hẻm", "Ngõ", "Kiệt" followed by number/slashes
        $streetPart = preg_replace('/^(số|hẻm|ngõ|kiệt)\s+[0-9]+[a-zA-Z]?(?:\/[0-9]+[a-zA-Z]?)*(?:\-[0-9]+)?\s*/i', '', $streetPart);
        
        // Remove leading numbers, slashes, dashes, e.g. "69/1/3 ", "42-44 "
        $streetPart = preg_replace('/^[0-9]+[a-zA-Z]?(?:\/[0-9]+[a-zA-Z]?)*(?:\-[0-9]+)?\s*/', '', $streetPart);
        
        // Remove prefixes like "Đường", "Đ." (case insensitive)
        $streetPart = preg_replace('/^(đường|đ\.)\s+/i', '', $streetPart);
        
        $streetPart = trim($streetPart);
        
        // If the extracted street name is too short or is just numeric, ignore it
        if (strlen($streetPart) < 3 || is_numeric($streetPart)) {
            return '';
        }
        
        return $streetPart;
    }

    /**
     * Resolve ward name and type.
     */
    private function resolveWard($ward_id, $is_new_address, $newWards, $oldWards)
    {
        $wardName = '';
        $wardType = '';
        if ($is_new_address) {
            if (isset($newWards[$ward_id])) {
                $wardName = $newWards[$ward_id]->name;
                $wardType = $newWards[$ward_id]->type ?? 'Xã/Phường';
            }
        } else {
            if (isset($oldWards[$ward_id])) {
                $wardName = $oldWards[$ward_id]->name;
                $wardType = $oldWards[$ward_id]->type ?? 'Xã/Phường';
            }
        }

        if (empty($wardName)) {
            return null;
        }

        return [
            'name' => $wardName,
            'type' => trim($wardType)
        ];
    }

    /**
     * Resolve property type display name.
     */
    private function resolvePropertyTypeName($propertyTypeId, $propertyTypes)
    {
        if (isset($propertyTypes[$propertyTypeId])) {
            $name = $propertyTypes[$propertyTypeId]->name;
            if ($name === 'Đất bán') {
                return 'Đất';
            }
            return $name;
        }
        return 'BĐS';
    }

    /**
     * Build suggestions list with scores based on grouping records.
     */
    private function buildSuggestionsList($item, $wardName, $wardType, $pTypeName, $street, $now)
    {
        $list = [];
        $typeStr = ($item['type'] == 1) ? 'Mua bán' : 'Cho thuê';
        
        // Build location display part
        $locationPart = empty($street) 
            ? "{$wardType} {$wardName}" 
            : "Đường {$street}, {$wardType} {$wardName}";

        // Multiplier for street suggestions (slightly higher)
        $scoreMultiplier = empty($street) ? 1.0 : 1.2;

        // General suggestion
        $list[] = [
            'suggestion' => "{$typeStr} {$pTypeName} tại {$locationPart}",
            'type' => $typeStr,
            'to_price' => null,
            'ward_id' => $item['ward_id'],
            'is_new_address' => $item['is_new_address'],
            'listing_count' => $item['total'],
            'score' => (int)($item['total'] * 10 * $scoreMultiplier),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // Price brackets
        if ($item['type'] == 1) { // Mua bán
            $brackets = [
                ['key' => 'under_1b', 'to_price' => 1000, 'label' => 'dưới 1 tỷ'],
                ['key' => 'under_2b', 'to_price' => 2000, 'label' => 'dưới 2 tỷ'],
                ['key' => 'under_3b', 'to_price' => 3000, 'label' => 'dưới 3 tỷ'],
                ['key' => 'under_5b', 'to_price' => 5000, 'label' => 'dưới 5 tỷ'],
            ];

            foreach ($brackets as $b) {
                if ($item[$b['key']] > 0) {
                    $list[] = [
                        'suggestion' => "{$typeStr} {$pTypeName} {$b['label']} tại {$locationPart}",
                        'type' => $typeStr,
                        'to_price' => $b['to_price'],
                        'ward_id' => $item['ward_id'],
                        'is_new_address' => $item['is_new_address'],
                        'listing_count' => $item[$b['key']],
                        'score' => (int)($item[$b['key']] * 15 * $scoreMultiplier),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        } elseif ($item['type'] == 2) { // Cho thuê
            $brackets = [
                ['key' => 'under_5m', 'to_price' => 5, 'label' => 'dưới 5 triệu'],
                ['key' => 'under_10m', 'to_price' => 10, 'label' => 'dưới 10 triệu'],
                ['key' => 'under_15m', 'to_price' => 15, 'label' => 'dưới 15 triệu'],
            ];

            foreach ($brackets as $b) {
                if ($item[$b['key']] > 0) {
                    $list[] = [
                        'suggestion' => "{$typeStr} {$pTypeName} {$b['label']} tại {$locationPart}",
                        'type' => $typeStr,
                        'to_price' => $b['to_price'],
                        'ward_id' => $item['ward_id'],
                        'is_new_address' => $item['is_new_address'],
                        'listing_count' => $item[$b['key']],
                        'score' => (int)($item[$b['key']] * 15 * $scoreMultiplier),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        return $list;
    }
}
