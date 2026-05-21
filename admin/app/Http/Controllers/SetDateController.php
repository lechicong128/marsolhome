<?php

namespace App\Http\Controllers;

use App\Models\WorkShift;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class SetDateController extends Controller
{
    use UploadFile;

    // Danh sách ngày trong tuần
    const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    /**
     * Trang danh sách ca làm việc
     */
    public function get_list()
    {
        if (!has_permission('set_date', 'view')) {
            access_denied();
        }
        return view('admin.set_date.list', [
            'title' => lang('c_list_work_shifts'),
            'days'  => self::DAYS,
        ]);
    }

    /**
     * DataTable: lấy danh sách ca làm việc (nhóm theo name / ngày)
     */
    public function getTable()
    {
        $shifts = WorkShift::query()->orderBy('day_of_week', 'asc');

        return Datatables::of($shifts)
            ->addColumn('day_name', function ($shift) {
                return self::DAYS[$shift->day_of_week] ?? $shift->day_of_week;
            })
            ->addColumn('options', function ($shift) {
                $delete = '<a type="button" class="po-delete"
                    data-container="body" data-html="true"
                    data-toggle="popover" data-placement="left"
                    data-content="
                    <button href=\'admin/set_date/delete/' . $shift->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_work_shifts') . '</a>';

                return '<div class="dropdown text-center">
                    <button class="btn btn-default dropdown-toggle nav-link" type="button" data-toggle="dropdown">
                        Tác vụ <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-left" role="menu">
                        <li style="cursor:pointer">' . $delete . '</li>
                    </ul>
                </div>';
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'day_name'])
            ->make(true);
    }

    /**
     * Lưu ca làm việc (bulk: toàn bộ 7 ngày trong 1 lần submit)
     * POST data:
     *   shifts[day_of_week] = { start_time, end_time, active }
     */
    public function submit()
    {
        $data = [];

        $validator = Validator::make($this->request->all(), [
            'shifts'             => 'required|array',
            'shifts.*.start_time' => 'required',
            'shifts.*.end_time'   => 'required',
        ], [
            'shifts.required'              => 'Dữ liệu ca làm việc không hợp lệ',
            'shifts.*.start_time.required' => 'Bạn chưa nhập thời gian bắt đầu',
            'shifts.*.end_time.required'   => 'Bạn chưa nhập thời gian kết thúc',
        ]);

        if ($validator->fails()) {
            $data['result']  = false;
            $data['message'] = $validator->errors()->all();
            return response()->json($data);
        }

        DB::beginTransaction();
        try {
            $shiftsInput = $this->request->input('shifts', []);

            foreach ($shiftsInput as $dayOfWeek => $shiftData) {
                if (!isset(self::DAYS[(int)$dayOfWeek])) {
                    continue;
                }

                $active = isset($shiftData['active']) ? 1 : 0;

                // Upsert: nếu đã có thì update, chưa có thì insert
                WorkShift::updateOrCreate(
                    ['day_of_week' => (int)$dayOfWeek],
                    [
                        'start_time' => $shiftData['start_time'],
                        'end_time'   => $shiftData['end_time'],
                        'active'     => $active,
                    ]
                );
            }

            DB::commit();
            $data['result']  = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result']  = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    /**
     * Xoá 1 ca làm việc
     */
    public function delete($id)
    {
        if (!has_permission('set_date', 'delete')) {
            $data['result']  = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }

        $shift = WorkShift::find($id);
        if (empty($shift)) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy dữ liệu']);
        }

        try {
            $shift->delete();
            $data['result']  = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result']  = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    /**
     * Thay đổi trạng thái active
     */
    public function changeStatus($id)
    {
        $shift = WorkShift::find($id);
        try {
            $shift->active = $this->request->input('status') == 0 ? 1 : 0;
            $shift->save();
            $data['result']  = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result']  = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    /**
     * Load data các ca hiện tại (dùng cho modal)
     */
    public function getCurrentShifts()
    {
        $existing = WorkShift::orderBy('day_of_week', 'asc')->get()->keyBy('day_of_week');

        $result = [];
        foreach (self::DAYS as $dayIndex => $dayName) {
            $shift          = $existing->get($dayIndex);
            $result[]       = [
                'day_of_week' => $dayIndex,
                'day_name'    => $dayName,
                'start_time'  => $shift ? $shift->start_time : '10:30',
                'end_time'    => $shift ? $shift->end_time   : '20:30',
                'active'      => $shift ? $shift->active     : 1,
            ];
        }

        return response()->json(['result' => true, 'data' => $result]);
    }
}