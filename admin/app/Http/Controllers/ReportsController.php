<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class ReportsController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function realEstateData()
    {
        $title = 'Báo cáo nguồn data bất động sản';
        
        // Departments mock
        $departments = [
            ['id' => 'kd1', 'name' => 'KD Dự án 1'],
            ['id' => 'kd2', 'name' => 'KD Dự án 2'],
            ['id' => 'kd3', 'name' => 'KD Dự án 3'],
        ];

        // Sales groups mock
        $salesGroups = [
            ['id' => 'g1', 'name' => 'Nhóm sales A'],
            ['id' => 'g2', 'name' => 'Nhóm sales B'],
            ['id' => 'g3', 'name' => 'Nhóm sales C'],
        ];

        // Staff mock for filter
        $staffList = [
            ['id' => '1', 'name' => 'Nguyễn Văn An'],
            ['id' => '2', 'name' => 'Trần Minh Tú'],
            ['id' => '3', 'name' => 'Lê Thị Mai'],
            ['id' => '4', 'name' => 'Phạm Quốc Huy'],
            ['id' => '5', 'name' => 'Đỗ Thu Hằng'],
            ['id' => '6', 'name' => 'Hoàng Văn Nam'],
            ['id' => '7', 'name' => 'Nguyễn Thu Trang'],
            ['id' => '8', 'name' => 'Trần Quốc Bảo'],
            ['id' => '9', 'name' => 'Bùi Thị Lan'],
            ['id' => '10', 'name' => 'Phan Thanh Tùng'],
            ['id' => '11', 'name' => 'Vũ Văn Kiên'],
            ['id' => '12', 'name' => 'Đặng Thị Hương'],
            ['id' => '13', 'name' => 'Lưu Văn Đức'],
            ['id' => '14', 'name' => 'Trịnh Minh Châu'],
        ];

        return view('admin.report.real_estate_data', [
            'title' => $title,
            'departments' => $departments,
            'salesGroups' => $salesGroups,
            'staffList' => $staffList
        ]);
    }

    public function getListRealEstateData(Request $request)
    {
        // Define full dataset matching the image exactly
        $dataset = [
            [
                'id' => 1,
                'staff_name' => 'Nguyễn Văn An',
                'department' => 'KD Dự án 1',
                'department_id' => 'kd1',
                'group_id' => 'g1',
                'staff_id' => '1',
                'quota' => 80,
                'entered' => 72,
                'source1' => 26,
                'source2' => 20,
                'source3' => 26,
                'valid' => 62,
                'duplicate' => 5,
                'missing' => 5,
            ],
            [
                'id' => 2,
                'staff_name' => 'Trần Minh Tú',
                'department' => 'KD Dự án 1',
                'department_id' => 'kd1',
                'group_id' => 'g1',
                'staff_id' => '2',
                'quota' => 70,
                'entered' => 60,
                'source1' => 22,
                'source2' => 18,
                'source3' => 20,
                'valid' => 51,
                'duplicate' => 4,
                'missing' => 5,
            ],
            [
                'id' => 3,
                'staff_name' => 'Lê Thị Mai',
                'department' => 'KD Dự án 1',
                'department_id' => 'kd1',
                'group_id' => 'g2',
                'staff_id' => '3',
                'quota' => 60,
                'entered' => 54,
                'source1' => 18,
                'source2' => 16,
                'source3' => 20,
                'valid' => 45,
                'duplicate' => 3,
                'missing' => 6,
            ],
            [
                'id' => 4,
                'staff_name' => 'Phạm Quốc Huy',
                'department' => 'KD Dự án 2',
                'department_id' => 'kd2',
                'group_id' => 'g2',
                'staff_id' => '4',
                'quota' => 60,
                'entered' => 52,
                'source1' => 18,
                'source2' => 16,
                'source3' => 18,
                'valid' => 43,
                'duplicate' => 4,
                'missing' => 5,
            ],
            [
                'id' => 5,
                'staff_name' => 'Đỗ Thu Hằng',
                'department' => 'KD Dự án 2',
                'department_id' => 'kd2',
                'group_id' => 'g2',
                'staff_id' => '5',
                'quota' => 50,
                'entered' => 45,
                'source1' => 15,
                'source2' => 15,
                'source3' => 15,
                'valid' => 38,
                'duplicate' => 3,
                'missing' => 4,
            ],
            [
                'id' => 6,
                'staff_name' => 'Hoàng Văn Nam',
                'department' => 'KD Dự án 2',
                'department_id' => 'kd2',
                'group_id' => 'g3',
                'staff_id' => '6',
                'quota' => 50,
                'entered' => 38,
                'source1' => 12,
                'source2' => 12,
                'source3' => 14,
                'valid' => 31,
                'duplicate' => 3,
                'missing' => 4,
            ],
            [
                'id' => 7,
                'staff_name' => 'Nguyễn Thu Trang',
                'department' => 'KD Dự án 3',
                'department_id' => 'kd3',
                'group_id' => 'g3',
                'staff_id' => '7',
                'quota' => 45,
                'entered' => 36,
                'source1' => 12,
                'source2' => 10,
                'source3' => 14,
                'valid' => 28,
                'duplicate' => 4,
                'missing' => 4,
            ],
            [
                'id' => 8,
                'staff_name' => 'Trần Quốc Bảo',
                'department' => 'KD Dự án 3',
                'department_id' => 'kd3',
                'group_id' => 'g3',
                'staff_id' => '8',
                'quota' => 45,
                'entered' => 32,
                'source1' => 10,
                'source2' => 10,
                'source3' => 12,
                'valid' => 25,
                'duplicate' => 3,
                'missing' => 4,
            ],
            [
                'id' => 9,
                'staff_name' => 'Bùi Thị Lan',
                'department' => 'KD Dự án 3',
                'department_id' => 'kd3',
                'group_id' => 'g1',
                'staff_id' => '9',
                'quota' => 40,
                'entered' => 28,
                'source1' => 8,
                'source2' => 8,
                'source3' => 12,
                'valid' => 21,
                'duplicate' => 3,
                'missing' => 4,
            ],
            [
                'id' => 10,
                'staff_name' => 'Phan Thanh Tùng',
                'department' => 'KD Dự án 3',
                'department_id' => 'kd3',
                'group_id' => 'g2',
                'staff_id' => '10',
                'quota' => 40,
                'entered' => 21,
                'source1' => 7,
                'source2' => 5,
                'source3' => 9,
                'valid' => 17,
                'duplicate' => 2,
                'missing' => 2,
            ],
            [
                'id' => 11,
                'staff_name' => 'Vũ Văn Kiên',
                'department' => 'KD Dự án 1',
                'department_id' => 'kd1',
                'group_id' => 'g1',
                'staff_id' => '11',
                'quota' => 10,
                'entered' => 0,
                'source1' => 0,
                'source2' => 0,
                'source3' => 0,
                'valid' => 0,
                'duplicate' => 0,
                'missing' => 0,
            ],
            [
                'id' => 12,
                'staff_name' => 'Đặng Thị Hương',
                'department' => 'KD Dự án 2',
                'department_id' => 'kd2',
                'group_id' => 'g2',
                'staff_id' => '12',
                'quota' => 10,
                'entered' => 0,
                'source1' => 0,
                'source2' => 0,
                'source3' => 0,
                'valid' => 0,
                'duplicate' => 0,
                'missing' => 0,
            ],
            [
                'id' => 13,
                'staff_name' => 'Lưu Văn Đức',
                'department' => 'KD Dự án 3',
                'department_id' => 'kd3',
                'group_id' => 'g3',
                'staff_id' => '13',
                'quota' => 10,
                'entered' => 0,
                'source1' => 0,
                'source2' => 0,
                'source3' => 0,
                'valid' => 0,
                'duplicate' => 0,
                'missing' => 0,
            ],
            [
                'id' => 14,
                'staff_name' => 'Trịnh Minh Châu',
                'department' => 'KD Dự án 3',
                'department_id' => 'kd3',
                'group_id' => 'g3',
                'staff_id' => '14',
                'quota' => 10,
                'entered' => 0,
                'source1' => 0,
                'source2' => 0,
                'source3' => 0,
                'valid' => 0,
                'duplicate' => 0,
                'missing' => 0,
            ],
        ];

        // Apply filters
        $filteredData = $dataset;

        if ($request->filled('department_id')) {
            $filteredData = array_filter($filteredData, function($item) use ($request) {
                return $item['department_id'] == $request->input('department_id');
            });
        }

        if ($request->filled('group_id')) {
            $filteredData = array_filter($filteredData, function($item) use ($request) {
                return $item['group_id'] == $request->input('group_id');
            });
        }

        if ($request->filled('staff_id')) {
            $filteredData = array_filter($filteredData, function($item) use ($request) {
                return $item['staff_id'] == $request->input('staff_id');
            });
        }

        // Re-key array after filtering
        $filteredData = array_values($filteredData);

        // Compute metrics for response
        $totalQuota = array_sum(array_column($filteredData, 'quota'));
        $totalEntered = array_sum(array_column($filteredData, 'entered'));
        $totalSource1 = array_sum(array_column($filteredData, 'source1'));
        $totalSource2 = array_sum(array_column($filteredData, 'source2'));
        $totalSource3 = array_sum(array_column($filteredData, 'source3'));
        $totalValid = array_sum(array_column($filteredData, 'valid'));
        $totalDuplicate = array_sum(array_column($filteredData, 'duplicate'));
        $totalMissing = array_sum(array_column($filteredData, 'missing'));
        
        $totalProgress = $totalQuota > 0 ? round(($totalEntered / $totalQuota) * 100, 2) : 0;
        $totalValidRate = $totalEntered > 0 ? round(($totalValid / $totalEntered) * 100, 2) : 0;

        $dtData = collect($filteredData);
        $start = intval($request->input('start', 0));

        // Format data to Datatables response
        return (new CollectionDataTable($dtData))
            ->addColumn('stt', function ($row) use (&$start) {
                return '<div style="text-align:center;font-weight:700;color:#334155">' . (++$start) . '</div>';
            })
            ->editColumn('staff_name', function ($row) {
                return '<div style="font-weight:600;color:#1e293b;text-align:left">' . e($row['staff_name']) . '</div>';
            })
            ->editColumn('department', function ($row) {
                return '<div style="color:#64748b;font-weight:500;text-align:left">' . e($row['department']) . '</div>';
            })
            ->editColumn('quota', function ($row) {
                return '<div style="text-align:center;font-weight:600;color:#374151">' . $row['quota'] . '</div>';
            })
            ->editColumn('entered', function ($row) {
                return '<div style="text-align:center;font-weight:700;color:#4338ca">' . $row['entered'] . '</div>';
            })
            ->addColumn('progress', function ($row) {
                $pct = $row['quota'] > 0 ? round(($row['entered'] / $row['quota']) * 100) : 0;
                
                // Determine progress color
                $barBg = 'linear-gradient(90deg, #10b981, #34d399)';
                $textColor = '#059669';
                $cssClass = 'high';
                if ($pct < 60) {
                    $barBg = 'linear-gradient(90deg, #ef4444, #f87171)';
                    $textColor = '#dc2626';
                    $cssClass = 'low';
                } elseif ($pct < 85) {
                    $barBg = 'linear-gradient(90deg, #f59e0b, #fbbf24)';
                    $textColor = '#d97706';
                    $cssClass = 'mid';
                }

                return '<div class="progress-cell">
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill ' . $cssClass . '" style="width:' . $pct . '%"></div>
                            </div>
                            <span class="progress-pct ' . $cssClass . '">' . $pct . '%</span>
                        </div>';
            })
            ->editColumn('source1', function ($row) {
                return '<div style="text-align:center;font-weight:500;color:#374151">' . $row['source1'] . '</div>';
            })
            ->editColumn('source2', function ($row) {
                return '<div style="text-align:center;font-weight:500;color:#374151">' . $row['source2'] . '</div>';
            })
            ->editColumn('source3', function ($row) {
                return '<div style="text-align:center;font-weight:500;color:#374151">' . $row['source3'] . '</div>';
            })
            ->editColumn('valid', function ($row) {
                return '<div style="text-align:center;font-weight:700;color:#059669">' . $row['valid'] . '</div>';
            })
            ->editColumn('duplicate', function ($row) {
                return '<div style="text-align:center;font-weight:700;color:#e11d48">' . $row['duplicate'] . '</div>';
            })
            ->editColumn('missing', function ($row) {
                return '<div style="text-align:center;font-weight:700;color:#d97706">' . $row['missing'] . '</div>';
            })
            ->addColumn('valid_rate', function ($row) {
                $pct = $row['entered'] > 0 ? round(($row['valid'] / $row['entered']) * 100, 2) : 0;
                $color = $pct >= 80 ? '#059669' : ($pct >= 60 ? '#d97706' : '#dc2626');
                return '<div style="text-align:center;font-weight:700;color:' . $color . '">' . number_format($pct, 2, ',', '.') . '%</div>';
            })
            ->addColumn('action', function ($row) {
                return '<div style="text-align:center">
                            <button class="rpt-btn-eye btn-view-detail" data-id="' . $row['id'] . '" data-name="' . e($row['staff_name']) . '">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>';
            })
            ->rawColumns(['stt', 'staff_name', 'department', 'quota', 'entered', 'progress', 'source1', 'source2', 'source3', 'valid', 'duplicate', 'missing', 'valid_rate', 'action'])
            ->with([
                'draw' => intval($request->input('draw')),
                'summary' => [
                    'quota' => $totalQuota,
                    'entered' => $totalEntered,
                    'source1' => $totalSource1,
                    'source2' => $totalSource2,
                    'source3' => $totalSource3,
                    'valid' => $totalValid,
                    'duplicate' => $totalDuplicate,
                    'missing' => $totalMissing,
                    'progress' => number_format($totalProgress, 2, ',', '.') . '%',
                    'valid_rate' => number_format($totalValidRate, 2, ',', '.') . '%',
                ]
            ])
            ->skipPaging()
            ->make(true);
    }
}
