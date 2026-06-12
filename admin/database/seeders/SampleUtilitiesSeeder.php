<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Utility;
use App\Models\UtilityOption;
use App\Models\TypeProperty;

class SampleUtilitiesSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            [
                'name' => 'Phù hợp gia đình',
                'input_type' => 'select',
                'options' => ['4 - 6 người', '1 - 2 người', '2 - 4 người', 'Trên 6 người'],
            ],
            [
                'name' => 'Loại hình',
                'input_type' => 'select',
                'options' => ['Nhà riêng', 'Căn hộ/Chung cư', 'Biệt thự', 'Nhà phố', 'Đất nền'],
            ],
            [
                'name' => 'Diện tích đất',
                'input_type' => 'number',
            ],
            [
                'name' => 'Diện tích xây dựng',
                'input_type' => 'number',
            ],
            [
                'name' => 'Diện tích sử dụng',
                'input_type' => 'number',
            ],
            [
                'name' => 'Giá/m2',
                'input_type' => 'number',
            ],
            [
                'name' => 'Giấy tờ pháp lý',
                'input_type' => 'select',
                'options' => ['Sổ hồng riêng', 'Sổ hồng chung', 'Sổ đỏ', 'Hợp đồng mua bán', 'Giấy tờ khác'],
            ],
            [
                'name' => 'Loại đất',
                'input_type' => 'select',
                'options' => ['Đất đô thị', 'Đất trồng cây lâu năm', 'Đất nông nghiệp', 'Đất thương mại dịch vụ'],
            ],
            [
                'name' => 'Tình trạng nội thất',
                'input_type' => 'select',
                'options' => ['Full nội thất', 'Nội thất cơ bản', 'Nhà trống', 'Thô'],
            ],
            [
                'name' => 'Hướng cửa chính',
                'input_type' => 'select',
                'options' => ['Hướng đông nam', 'Hướng Đông', 'Hướng Tây', 'Hướng Nam', 'Hướng Bắc', 'Hướng Đông Bắc', 'Hướng Tây Nam', 'Hướng Tây Bắc'],
            ],
            [
                'name' => 'Chiều ngang',
                'input_type' => 'number',
            ],
            [
                'name' => 'Chiều dài',
                'input_type' => 'number',
            ],
            [
                'name' => 'Hẻm/ mặt tiền',
                'input_type' => 'number',
            ],
            [
                'name' => 'Phòng ngủ',
                'input_type' => 'number',
            ],
            [
                'name' => 'Phòng vệ sinh',
                'input_type' => 'number',
            ],
            [
                'name' => 'Chỗ đậu xe',
                'input_type' => 'select',
                'options' => ['1 ô tô trong nhà', 'Nhiều ô tô', 'Chỉ đậu xe máy', 'Không có chỗ đậu xe'],
            ],
        ];

        $propertyTypes = TypeProperty::all();

        foreach ($samples as $sample) {
            // Check if utility already exists
            $utility = Utility::where('name', $sample['name'])->first();
            if (!$utility) {
                $utility = new Utility();
                $utility->name = $sample['name'];
                $utility->input_type = $sample['input_type'];
                $utility->active = 1;
                $utility->transaction_type = 3; // Both
                $utility->save();
            }

            // Seed options if type is select
            if ($sample['input_type'] === 'select' && isset($sample['options'])) {
                foreach ($sample['options'] as $optName) {
                    $opt = UtilityOption::where('utility_id', $utility->id)
                        ->where('name', $optName)
                        ->first();
                    if (!$opt) {
                        UtilityOption::create([
                            'utility_id' => $utility->id,
                            'name' => $optName,
                        ]);
                    }
                }
            }

            // Sync with all property types
            foreach ($propertyTypes as $pt) {
                $exists = DB::table('tbl_type_property_utilities')
                    ->where('type_property_id', $pt->id)
                    ->where('utility_id', $utility->id)
                    ->exists();
                if (!$exists) {
                    DB::table('tbl_type_property_utilities')->insert([
                        'type_property_id' => $pt->id,
                        'utility_id' => $utility->id,
                    ]);
                }
            }
        }
    }
}
