<?php

namespace App\Http\Controllers\Api_app;

use App\Models\CategoryService;
use App\Models\Service;
use App\Models\WorkShift;
use App\Services\AccountService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\NotificationTrait;
use App\Models\Notification;
use App\Http\Controllers\Api_app\Pay2sController;
use App\Helpers\SocketHelpersAdmin;

class SpaController extends AuthController
{
    use UploadFile, NotificationTrait;
    protected $dbAccount;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
        app(\App\Http\Middleware\CheckLoginApi::class)->getDataToken($this->request);
        $this->dbAccount = $accountService;
        $this->baseUrl = config('services.storage.url');
    }
    public function getSession()
    {
        $data = [
            [
                'id' => 1,
                'name' => 'Sáng'
            ],
            [
                'id' => 2,
                'name' => 'Chiều'
            ],
            [
                'id' => 3,
                'name' => 'Tối'
            ],
        ];
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
    public function updateStatusSpa()  {
        // try {
        //     DB::beginTransaction();

        //     $today = now()->format('Y-m-d');

        //     // Lấy các lịch hẹn của những ngày trước đó, đang không ở trạng thái completed hoặc cancelled
        //     $bookings = DB::table('tbl_spa_bookings')
        //         ->whereNotIn('status', ['completed', 'cancelled'])
        //         ->whereDate('booking_date', '<', $today)
        //         ->get();

        //     foreach ($bookings as $booking) {
        //         // Kiểm tra đã thanh toán hay chưa (payment_status = 'paid' hoặc có phiếu thu approved)
        //         $isPaid = false;
        //         if (isset($booking->payment_status) && $booking->payment_status === 'paid') {
        //             $isPaid = true;
        //         } else {
        //             $payment = DB::table('tbl_spa_payments')
        //                          ->where('id_booking', $booking->id)
        //                          ->where('status', 'approved')
        //                          ->first();
        //             if ($payment) {
        //                 $isPaid = true;
        //             }
        //         }

        //         if ($isPaid) {
        //             DB::table('tbl_spa_bookings')->where('id', $booking->id)->update([
        //                 'status'     => 'completed',
        //                 'updated_at' => now(),
        //             ]);
        //         } else {
        //             $note = empty($booking->note_cancel) 
        //                     ? "Hệ thống hủy" 
        //                     : $booking->note_cancel . "\nLý do hủy: Hệ thống hủy";
                            
        //             DB::table('tbl_spa_bookings')->where('id', $booking->id)->update([
        //                 'status'      => 'cancelled',
        //                 'note_cancel' => $note,
        //                 'updated_at'  => now(),
        //             ]);
        //         }
        //     }

        //     DB::commit();

        //     return response()->json([
        //         'result'  => true,
        //         'message' => 'Cập nhật trạng thái thành công',
        //         'data'    => [],
        //     ]);

        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return response()->json([
        //         'result'  => false,
        //         'message' => $e->getMessage(),
        //         'data'    => [],
        //     ], 500);
        // }
    }
    public function getWorkShift(Request $request)
    {
        $shifts = WorkShift::where('active', 1)->get();
        return response()->json([
            'status' => 'success',
            'data' => $shifts
        ]);
    }
    /**
     * Lấy danh sách khung giờ theo ngày & buổi
     *
     * POST params:
     *   - date    : ngày cần lấy (YYYY-MM-DD), mặc định = hôm nay
     *   - session : 1 = Sáng (đến 11:30), 2 = Chiều (12:00-16:30), 3 = Tối (17:00-hết ca)
     *               nếu không truyền → trả toàn bộ ca
     *
     * Response mỗi slot:
     *   { time: "10:30", label: "10:30 SA", disabled: false }
     */
    
    public function getTime(Request $request)
    {
        // 1. Xác định ngày và day_of_week (0=Sun … 6=Sat)
        $dateStr    = $request->input('date', now()->format('Y-m-d'));
        $session    = $request->input('session'); // 1|2|3|null

        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', $dateStr)->startOfDay();
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => 'Ngày không hợp lệ', 'data' => []]);
        }

        $dayOfWeek = (int) $date->dayOfWeek; // 0=Sun, 6=Sat

        // 2. Lấy ca làm việc của ngày đó
        $shift = WorkShift::where('day_of_week', $dayOfWeek)
                          ->where('active', 1)
                          ->first();

        if (empty($shift)) {
            return response()->json([
                'result'  => false,
                'message' => 'Ngày này không có ca làm việc',
                'data'    => [],
            ]);
        }

        // 3. Parse start/end time
        [$startH, $startM] = array_map('intval', explode(':', $shift->start_time));
        [$endH,   $endM  ] = array_map('intval', explode(':', $shift->end_time));

        $startMinutes = $startH * 60 + $startM;
        $endMinutes   = $endH   * 60 + $endM;

        // 4. Giờ hiện tại (chỉ dùng khi date = hôm nay)
        $isToday       = $date->isSameDay(now());
        $nowMinutes    = $isToday ? (now()->hour * 60 + now()->minute) : -1;

        // 5. Khoảng giờ theo buổi
        $sessionRanges = [
            1 => [0,    719],   // Sáng : 00:00 – 11:59
            2 => [720,  1019],  // Chiều : 12:00 – 16:59
            3 => [1020, 1439],  // Tối   : 17:00 – 23:59
        ];

        // 6. Tạo từng slot 30 phút
        $slots = [];
        for ($m = $startMinutes; $m <= $endMinutes; $m += 30) {
            $h    = intdiv($m, 60);
            $min  = $m % 60;
            $time = sprintf('%02d:%02d', $h, $min);

            // Lọc theo buổi nếu có truyền session
            if (!empty($session) && isset($sessionRanges[(int)$session])) {
                [$rangeStart, $rangeEnd] = $sessionRanges[(int)$session];
                if ($m < $rangeStart || $m > $rangeEnd) {
                    continue;
                }
            }

            // Label hiển thị: SA / CH / PM
            if ($h < 12) {
                $label = $time . ' SA';
            } elseif ($h < 17) {
                $label = $time . ' CH';
            } else {
                $label = $time . ' TT';
            }

            $slots[] = [
                'time'     => $time,
                'label'    => $label,
                'disabled' => $isToday && $m <= $nowMinutes,
            ];
        }

        return response()->json([
            'result'  => true,
            'message' => lang('get_data_success'),
            'data'    => $slots,
        ]);
    }
    /**
     * API tạo lịch hẹn spa
     *
     * POST body (JSON hoặc form-data):
     *   - customer_name   : string  (bắt buộc) – Tên khách hàng
     *   - customer_phone  : string  (bắt buộc) – Số điện thoại
     *   - booking_date    : string  (bắt buộc) – Ngày hẹn (YYYY-MM-DD)
     *   - booking_time    : string  (bắt buộc) – Giờ hẹn (HH:MM)
     *   - total_amount    : numeric (bắt buộc) – Tổng tiền
     *   - payment_method  : string  (bắt buộc) – "transfer" | "pay_later"
     *   - services        : array   (bắt buộc) – Danh sách dịch vụ
     *       [{ id_service, name, price, quantity }]
     *   - note            : string  (tuỳ chọn) – Ghi chú
     *
     * Response:
     *   { result, message, data: { booking_code, id } }
     */
    public function AddBookingDateServices(Request $request)
    {
        // 1. Validate
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'total_amount'   => 'required|numeric|min:0',
            'payment_method' => 'required|integer',
            'branch_id'      => 'required|integer',
            'services'                      => 'required|array|min:1',
            'services.*.id_service'         => 'required|integer',
            'services.*.name'               => 'required|string',
            'services.*.price'              => 'required|numeric|min:0',
            'services.*.quantity'           => 'required|integer|min:1',
            'services.*.booking_date'       => 'required|date_format:Y-m-d',
            'services.*.booking_time'       => 'required|date_format:H:i',
        ], [
            'customer_name.required'                  => 'Vui lòng nhập tên khách hàng',
            'customer_phone.required'                 => 'Vui lòng nhập số điện thoại',
            'branch_id.required'                      => 'Vui lòng chọn chi nhánh',
            'total_amount.required'                   => 'Vui lòng nhập tổng tiền',
            'payment_method.required'                 => 'Vui lòng chọn hình thức thanh toán',
            'payment_method.in'                       => 'Hình thức thanh toán không hợp lệ (transfer | pay_later)',
            'services.required'                       => 'Vui lòng chọn ít nhất 1 dịch vụ',
            'services.min'                            => 'Vui lòng chọn ít nhất 1 dịch vụ',
            'services.*.id_service.required'          => 'Thiếu ID dịch vụ',
            'services.*.name.required'                => 'Thiếu tên dịch vụ',
            'services.*.price.required'               => 'Thiếu giá dịch vụ',
            'services.*.quantity.required'            => 'Thiếu số lượng dịch vụ',
            'services.*.booking_date.required'        => 'Thiếu ngày hẹn cho dịch vụ',
            'services.*.booking_date.date_format'     => 'Ngày hẹn không đúng định dạng (YYYY-MM-DD)',
            'services.*.booking_time.required'        => 'Thiếu giờ hẹn cho dịch vụ',
            'services.*.booking_time.date_format'     => 'Giờ hẹn không đúng định dạng (HH:MM)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result'  => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ], 422);
        }
        $id_client = $this->request->client->id ?? 0;
        if(empty($id_client)) {
            return response()->json([
                'result'  => false,
                'message' => 'Vui lòng đăng nhập',
                'data'    => [],
            ], 401);
        }
        DB::beginTransaction();
        try {
            // 2. Tạo mã lịch hẹn tạm thời (sẽ cập nhật sau khi có id)
            $bookingId = DB::table('tbl_spa_bookings')->insertGetId([
                'booking_code'   => 'BK-TMP',
                'customer_name'  => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'total_amount'   => $request->input('total_amount'),
                'payment_method' => $request->input('payment_method'),
                'branch_id'      => $request->input('branch_id'),
                'status'         => 'pending',
                'note'           => $request->input('note', ''),
                'id_client'      => $id_client,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // 3. Sinh mã lịch hẹn: BK-YYYYMMDD-000001
            $dateStr     = now()->format('Ymd');
            $bookingCode = 'BK-' . $dateStr . '-' . str_pad($bookingId, 6, '0', STR_PAD_LEFT);

            DB::table('tbl_spa_bookings')
                ->where('id', $bookingId)
                ->update(['booking_code' => $bookingCode]);

            // 4. Lưu danh sách dịch vụ (mỗi service có booking_date và booking_time riêng)
            $services    = $request->input('services');
            $serviceRows = [];
            foreach ($services as $svc) {
                $serviceRows[] = [
                    'id_booking'       => $bookingId,
                    'id_service'       => $svc['id_service'],
                    'name'             => $svc['name'],
                    'price'            => $svc['price'],
                    'discount_percent' => $svc['discount_percent'] ?? 0,
                    'duration_minutes' => $svc['duration_minutes'] ?? 0,
                    'quantity'         => $svc['quantity'],
                    'amount'           => $svc['price'] * $svc['quantity'],
                    'booking_date'     => $svc['booking_date'],
                    'booking_time'     => $svc['booking_time'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            DB::table('tbl_spa_booking_services')->insert($serviceRows);

            // 5. Tạo phiếu thanh toán
            $paymentId = DB::table('tbl_spa_payments')->insertGetId([
                'id_booking'     => $bookingId,
                'id_client'     => $id_client,
                'payment_code'   => 'PAY-TMP',
                'amount'         => $request->input('total_amount'),
                'payment_method' => $request->input('payment_method'),
                'note'           => $request->input('note', ''),
                'status'         => 'pending',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $paymentCode = 'PAY-' . $dateStr . '-' . str_pad($paymentId, 6, '0', STR_PAD_LEFT);

            DB::table('tbl_spa_payments')
                ->where('id', $paymentId)
                ->update(['payment_code' => $paymentCode]);

            DB::commit();

            // Tạo mã QR chuyển khoản Pay2s
            $qrData = Pay2sController::generateQR(
                $paymentCode,
                (float) $request->input('total_amount'),
                config('services.url'),
                config('services.storage.url')
            );

            if (empty($qrData['qr'])) {
                return response()->json([
                    'result'  => false,
                    'message' => $qrData['message'] ?? 'Tạo mã QR thất bại',
                    'data'    => [],
                ], 500);
            }
            $newRequest = clone $this->request;
            $newRequest->merge(['id' => $id_client]);
            $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
            $arr_object_id    = $responseClient->getData(true)['client'] ?? [];
            $id = $bookingId;
            $dtData = [];
            $dtData['reference_no'] = $bookingCode;
            Notification::notiAddBookingSpa($id_client,$dtData, $arr_object_id,$id);
            DB::table('tbl_cron_email')->insert([
                'id_ref' => $bookingId,
                'type' => 2,
                'status' => 0,
            ]);
            return response()->json([
                'result'  => true,
                'message' => 'Đặt lịch thành công',
                'data'    => [
                    'id'           => $bookingId,
                    'booking_code' => $bookingCode,
                    'payment_code' => $paymentCode,
                    'info_payment' => $qrData ?? []
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result'  => false,
                'message' => $e->getMessage(),
                'data'    => [],
            ], 500);
        }
    }
    public function AddBooking(Request $request)
    {
        // 1. Validate
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'booking_date'   => 'required|date_format:Y-m-d',
            'booking_time'   => 'required|date_format:H:i',
            'total_amount'   => 'required|numeric|min:0',
            'payment_method' => 'required|integer',
            'branch_id'      => 'required|integer',
            'services'       => 'required|array|min:1',
            'services.*.id_service' => 'required|integer',
            'services.*.name'       => 'required|string',
            'services.*.price'      => 'required|numeric|min:0',
            'services.*.quantity'   => 'required|integer|min:1',
        ], [
            'customer_name.required'         => 'Vui lòng nhập tên khách hàng',
            'customer_phone.required'        => 'Vui lòng nhập số điện thoại',
            'booking_date.required'          => 'Vui lòng chọn ngày hẹn',
            'branch_id.required'             => 'Vui lòng chọn chi nhánh',
            'booking_date.date_format'       => 'Ngày hẹn không đúng định dạng (YYYY-MM-DD)',
            'booking_time.required'          => 'Vui lòng chọn giờ hẹn',
            'booking_time.date_format'       => 'Giờ hẹn không đúng định dạng (HH:MM)',
            'total_amount.required'          => 'Vui lòng nhập tổng tiền',
            'payment_method.required'        => 'Vui lòng chọn hình thức thanh toán',
            'payment_method.in'              => 'Hình thức thanh toán không hợp lệ (transfer | pay_later)',
            'services.required'              => 'Vui lòng chọn ít nhất 1 dịch vụ',
            'services.min'                   => 'Vui lòng chọn ít nhất 1 dịch vụ',
            'services.*.id_service.required' => 'Thiếu ID dịch vụ',
            'services.*.name.required'       => 'Thiếu tên dịch vụ',
            'services.*.price.required'      => 'Thiếu giá dịch vụ',
            'services.*.quantity.required'   => 'Thiếu số lượng dịch vụ',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result'  => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ], 422);
        }
        $id_client = $this->request->client->id ?? 0;
        if(empty($id_client)) {
            return response()->json([
                'result'  => false,
                'message' => 'Vui lòng đăng nhập',
                'data'    => [],
            ], 401);
        }
        DB::beginTransaction();
        try {
            // 2. Tạo mã lịch hẹn tạm thời (sẽ cập nhật sau khi có id)
            $booking = new \stdClass();

            $bookingId = DB::table('tbl_spa_bookings')->insertGetId([
                'booking_code'   => 'BK-TMP',
                'customer_name'  => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'booking_date'   => $request->input('booking_date'),
                'booking_time'   => $request->input('booking_time'),
                'total_amount'   => $request->input('total_amount'),
                'payment_method' => $request->input('payment_method'),
                'branch_id' => $request->input('branch_id'),
                // 'payment_status' => $request->input('payment_method'),
                'status'         => 'pending',
                'note'           => $request->input('note', ''),
                'id_client'           => $id_client,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // 3. Sinh mã lịch hẹn: BK-YYYYMMDD-000001
            $dateStr     = \Carbon\Carbon::parse($request->input('booking_date'))->format('Ymd');
            $bookingCode = 'BK-' . $dateStr . '-' . str_pad($bookingId, 6, '0', STR_PAD_LEFT);

            DB::table('tbl_spa_bookings')
                ->where('id', $bookingId)
                ->update(['booking_code' => $bookingCode]);

            // 4. Lưu danh sách dịch vụ
            $services    = $request->input('services');
            $serviceRows = [];
            foreach ($services as $svc) {
                $serviceRows[] = [
                    'id_booking' => $bookingId,
                    'id_service' => $svc['id_service'],
                    'name'       => $svc['name'],
                    'price'      => $svc['price'],
                    'discount_percent'      => $svc['discount_percent'],
                    'duration_minutes'      => $svc['duration_minutes'],
                    'quantity'   => $svc['quantity'],
                    'amount'     => $svc['price'] * $svc['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('tbl_spa_booking_services')->insert($serviceRows);

            // 5. Tạo phiếu thanh toán
            $paymentId = DB::table('tbl_spa_payments')->insertGetId([
                'id_booking'     => $bookingId,
                'id_client'     => $id_client,
                'payment_code'   => 'PAY-TMP',
                'amount'         => $request->input('total_amount'),
                'payment_method' => $request->input('payment_method'),
                'note'           => $request->input('note', ''),
                'status'         => 'pending',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $paymentCode = 'PAY-' . $dateStr . '-' . str_pad($paymentId, 6, '0', STR_PAD_LEFT);

            DB::table('tbl_spa_payments')
                ->where('id', $paymentId)
                ->update(['payment_code' => $paymentCode]);

            DB::commit();

            // Tạo mã QR chuyển khoản Pay2s
            $qrData = Pay2sController::generateQR(
                $paymentCode,
                (float) $request->input('total_amount'),
                config('services.url'),
                config('services.storage.url')
            );

            if (empty($qrData['qr'])) {
                return response()->json([
                    'result'  => false,
                    'message' => $qrData['message'] ?? 'Tạo mã QR thất bại',
                    'data'    => [],
                ], 500);
            }
            $newRequest = clone $this->request;
            $newRequest->merge(['id' => $id_client]);
            $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
            $arr_object_id    = $responseClient->getData(true)['client'] ?? [];
            $id = $bookingId;
            $dtData = [];
            $dtData['reference_no'] = $bookingCode;
            Notification::notiAddBookingSpa($id_client,$dtData, $arr_object_id,$id);
            DB::table('tbl_cron_email')->insert([
                'id_ref' => $bookingId,
                'type' => 2,
                'status' => 0,
            ]);
            return response()->json([
                'result'  => true,
                'message' => 'Đặt lịch thành công',
                'data'    => [
                    'id'           => $bookingId,
                    'booking_code' => $bookingCode,
                    'payment_code' => $paymentCode,
                    'info_payment' => $qrData ?? []
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result'  => false,
                'message' => $e->getMessage(),
                'data'    => [],
            ], 500);
        }
    }
    function testnoti(Request $request) {
        $id_client = $this->request->client->id ?? 0;
        $newRequest = clone $this->request;
        $newRequest->merge(['id' => $id_client]);
        $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
        $arr_object_id    = $responseClient->getData(true)['client'] ?? [];
        $id = '';
        $dtData = [];
        $dtData['reference_no'] = 'BK-20250318-000001';
        Notification::notiAddBookingSpa($id_client,$dtData, $arr_object_id,$id);
    }
    /**
     * Lấy danh sách lịch hẹn spa của client đang đăng nhập
     *
     * GET/POST params:
     *   - from_date : string (YYYY-MM-DD) – Từ ngày (tuỳ chọn)
     *   - to_date   : string (YYYY-MM-DD) – Đến ngày (tuỳ chọn)
     *
     * Response:
     *   {
     *     result: true,
     *     message: "...",
     *     data: {
     *       "2025-03-01": [ { booking }, ... ],
     *       "2025-03-02": [ { booking }, ... ],
     *       ...
     *     }
     *   }
     */
    function GetListStatusFilter(Request $request)  {
        $id_client = $this->request->client->id ?? 0;

        $base = DB::table('tbl_spa_bookings')->where('id_client', $id_client);

        // Lọc từ ngày
        if ($request->filled('from_date')) {
            $base->whereDate('created_at', '>=', $request->input('from_date'));
        }

        // Lọc đến ngày
        if ($request->filled('to_date')) {
            $base->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $counts = [
            'all' => (clone $base)->count(),
            'upcoming'  => (clone $base)
                ->whereDate('created_at', '>=', now()->format('Y-m-d'))
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
        ];

        $data = [
            [
                'id'    => '',
                'name'  => 'Tất cả',
                'count' => $counts['all'],
            ],
            [
                'id'    => 'upcoming',
                'name'  => 'Sắp tới',
                'count' => $counts['upcoming'],
            ],
            [
                'id'    => 'completed',
                'name'  => 'Hoàn thành',
                'count' => $counts['completed'],
            ],
            [
                'id'    => 'cancelled',
                'name'  => 'Đã hủy',
                'count' => $counts['cancelled'],
            ],
        ];
        return response()->json([
            'result'  => true,
            'message' => lang('get_data_success'),
            'data'    => $data,
        ]);
    }
    public function CancelSpaBooking(Request $request)
    {
        // 1. Kiểm tra đăng nhập
        $id_client = $this->request->client->id ?? 0;
        if (empty($id_client)) {
            return response()->json([
                'result'  => false,
                'message' => 'Vui lòng đăng nhập',
                'data'    => [],
            ], 401);
        }

        // 2. Kiểm tra id_booking
        $id_booking = $request->input('id_booking');
        if (empty($id_booking)) {
            return response()->json([
                'result'  => false,
                'message' => 'Vui lòng nhập id_booking',
                'data'    => [],
            ], 422);
        }

        // 3. Tìm lịch hẹn
        $booking = DB::table('tbl_spa_bookings')->where('id', $id_booking)->first();
        if (empty($booking)) {
            return response()->json([
                'result'  => false,
                'message' => 'Không tìm thấy lịch hẹn',
                'data'    => [],
            ], 404);
        }

        // 4. Kiểm tra lịch hẹn có thuộc về client đang đăng nhập không
        if ((int) $booking->id_client !== (int) $id_client) {
            return response()->json([
                'result'  => false,
                'message' => 'Bạn không có quyền hủy lịch hẹn này',
                'data'    => [],
            ], 403);
        }

        // 5. Kiểm tra trạng thái – không thể hủy nếu đã hủy hoặc đã hoàn thành
        if (in_array($booking->status, ['cancelled', 'completed'])) {
            $msg = $booking->status === 'cancelled'
                ? 'Lịch hẹn này đã được hủy trước đó'
                : 'Lịch hẹn này đã hoàn thành, không thể hủy';
            return response()->json([
                'result'  => false,
                'message' => $msg,
                'data'    => [],
            ], 422);
        }

        // 6. Lấy ghi chú / lý do hủy
        $note = $request->input('note_cancel');
        
        $updateData = [
            'status'     => 'cancelled',
            'updated_at' => now(),
        ];

        if (!empty($note)) {
            $updateData['note_cancel'] = $note;
        }

        // 7. Hủy lịch hẹn
        DB::table('tbl_spa_bookings')->where('id', $id_booking)->update($updateData);
        $newRequest = clone $this->request;
        $newRequest->merge(['id' => $id_client]);
        $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
        $arr_object_id    = $responseClient->getData(true)['client'] ?? [];
        $id = $id_booking;
        $this->_refundTreatmentForBooking($id, 'cancel');
        $dtData = [];
        $dtData['reference_no'] = $booking->booking_code;
        $dtData['note_cancel'] = $note;
        Notification::notiCancelBookingSpa($id_client,$dtData, $arr_object_id,$id);
        return response()->json([
            'result'  => true,
            'message' => 'Hủy lịch hẹn thành công',
            'data'    => [],
        ]);
    }
    private function _refundTreatmentForBooking($bookingId, $action = 'delete')
    {
        $sessions = DB::table('tbl_treatment_sessions')->where('id_booking', $bookingId)->get();
        if ($sessions->isEmpty()) return;

        foreach ($sessions as $session) {
            $alreadyRefunded = strpos($session->note, '(Đã hoàn lại') !== false;

            if (!$alreadyRefunded && in_array($action, ['cancel', 'delete'])) {
                $purchase = DB::table('tbl_treatment_purchases')->where('id', $session->id_purchase)->first();
                if ($purchase) {
                    // Hoàn buổi (giảm số đã dùng)
                    $newUsed = max(0, $purchase->used_sessions - 1);
                    // Nếu hoàn buổi khiến chưa hết số lưu-> đổi state về active
                    $newStatus = ($newUsed < $purchase->total_sessions && $purchase->status == 'completed') 
                                 ? 'active' : $purchase->status;
                    
                    DB::table('tbl_treatment_purchases')->where('id', $purchase->id)->update([
                        'used_sessions' => $newUsed,
                        'status'        => $newStatus
                    ]);
                }
            }

            // Nếu chỉ hủy đơn, chỉ append ghi chú, không xóa data session log lẫn ID liệu trình đã áp dụng ở service
            if ($action == 'cancel' && !$alreadyRefunded) {
                DB::table('tbl_treatment_sessions')->where('id', $session->id)->update([
                    'note' => $session->note . ' (Đã hoàn lại do huỷ lịch hẹn)'
                ]);
            }
        }
        
        // Nếu là xóa đơn, thì xóa sạch log trong DB và tháo link
        if ($action == 'delete') {
            // Xóa lịch sử sử dụng buổi
            DB::table('tbl_treatment_sessions')->where('id_booking', $bookingId)->delete();
            
            // Reset liên kết ở mặt hàng
            DB::table('tbl_spa_booking_services')->where('id_booking', $bookingId)->update([
                'id_treatment_purchase' => null
            ]);
        }
    }
    public function GetListSpaBooking(Request $request)
    {
        $id_client = $this->request->client->id ?? 0;

        if (empty($id_client)) {
            return response()->json([
                'result'  => false,
                'message' => 'Vui lòng đăng nhập',
                'data'    => [],
            ], 401);
        }

        try {
            $current_page = (int) $request->input('current_page', 1);
            $per_page     = (int) $request->input('per_page', 10);

            $query = DB::table('tbl_spa_bookings')
                ->where('id_client', $id_client)
                ->orderBy('created_at', 'desc');

            // Lọc trạng thái
            $status = $request->input('status');
            if (!empty($status)) {
                if ($status === 'upcoming') {
                    // Sắp tới: booking_date từ hôm nay trở đi
                    $query->whereDate('created_at', '>=', now()->format('Y-m-d'));
                    $query->whereNotIn('status', ['completed', 'cancelled']);
                } else {
                    $query->where('status', $status);
                }
            }

            // Lọc từ ngày
            if ($request->filled('from_date')) {
                try {
                    $fromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->input('from_date'))->startOfDay();
                    $query->whereDate('created_at', '>=', $fromDate->format('Y-m-d'));
                } catch (\Exception $e) {
                    return response()->json([
                        'result'  => false,
                        'message' => 'from_date không đúng định dạng (YYYY-MM-DD)',
                        'data'    => [],
                    ], 422);
                }
            }

            // Lọc đến ngày
            if ($request->filled('to_date')) {
                try {
                    $toDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->input('to_date'))->endOfDay();
                    $query->whereDate('created_at', '<=', $toDate->format('Y-m-d'));
                } catch (\Exception $e) {
                    return response()->json([
                        'result'  => false,
                        'message' => 'to_date không đúng định dạng (YYYY-MM-DD)',
                        'data'    => [],
                    ], 422);
                }
            }
            $paginated = $query->paginate($per_page, ['*'], 'page', $current_page);
            $bookings  = collect($paginated->items());
            if ($bookings->isEmpty()) {
                $response         = $paginated->toArray();
                $response['data'] = (object)[];
                return response()->json($response);
            }
            // Lấy danh sách dịch vụ của tất cả booking
            $bookingIds = $bookings->pluck('id')->toArray();
            $services = DB::table('tbl_spa_booking_services as bs')
                ->leftJoin('tbl_services as s', 's.id', '=', 'bs.id_service')
                ->select(
                    'bs.*',
                    DB::raw('IF(s.image IS NOT NULL AND s.image != "", CONCAT("' . $this->baseUrl . '/", s.image), NULL) as image')
                )
                ->whereIn('bs.id_booking', $bookingIds)
                ->get()
                ->groupBy('id_booking');
            // Gắn services vào từng booking và group theo ngày
            $grouped = [];
            foreach ($bookings as $booking) {
                $booking->services = $services[$booking->id] ?? collect([]);
                $dateKey = \Carbon\Carbon::parse($booking->created_at)->format('Y-m-d');
                $grouped[$dateKey][] = $booking;
            }
            $response         = $paginated->toArray();
            $response['data'] = $grouped;
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => $e->getMessage(),
                'data'    => [],
            ], 500);
        }
    }
    public function GetDetailSpaBooking(Request $request)
    {
        $id_client = $this->request->client->id ?? 0;

        if (empty($id_client)) {
            return response()->json([
                'result'  => false,
                'message' => 'Vui lòng đăng nhập',
                'data'    => (object)[],
            ], 401);
        }

        $id_booking = $request->input('id_booking');
        if (empty($id_booking)) {
            return response()->json([
                'result'  => false,
                'message' => 'Vui lòng truyền id_booking',
                'data'    => (object)[],
            ], 422);
        }

        try {
            // 1. Lấy thông tin booking cùng tên chi nhánh
            $booking = DB::table('tbl_spa_bookings as b')
                ->leftJoin('tbl_branches as br', 'br.id', '=', 'b.branch_id')
                ->select(
                    'b.*',
                    'br.name as branch_name'
                )
                ->where('b.id', $id_booking)
                ->where('b.id_client', $id_client)
                ->first();

            if (empty($booking)) {
                return response()->json([
                    'result'  => false,
                    'message' => 'Không tìm thấy lịch hẹn',
                    'data'    => (object)[],
                ], 404);
            }

            // 2. Lấy danh sách dịch vụ của booking
            $services = DB::table('tbl_spa_booking_services as bs')
                ->leftJoin('tbl_services as s', 's.id', '=', 'bs.id_service')
                ->leftJoin('tbl_treatment_purchases as tp', 'bs.id_treatment_purchase', '=', 'tp.id')
                ->select(
                    'bs.*',
                    'tp.purchase_code as applied_treatment_code',
                    'tp.treatment_name as applied_treatment_name',
                    DB::raw('IF(s.image IS NOT NULL AND s.image != "", CONCAT("' . $this->baseUrl . '/", s.image), NULL) as image')
                )
                ->where('bs.id_booking', $booking->id)
                ->orderBy('bs.booking_date', 'asc')
                ->orderBy('bs.booking_time', 'asc')
                ->get();

            $booking->services = $services;

            // 3. Lấy thông tin thanh toán kèm tên phương thức
            $payment = DB::table('tbl_spa_payments as p')
                ->leftJoin('tbl_payment_mode as pm', 'pm.id', '=', 'p.payment_method')
                ->select(
                    'p.*',
                    'pm.name as payment_method_name'
                )
                ->where('p.id_booking', $booking->id)
                ->first();
                
            $booking->payment = $payment;

            // 4. Nếu chưa thanh toán thì trả về thêm thông tin mã QR chuyển khoản (nếu có)
            if ($payment && $payment->status === 'pending' && !empty($payment->payment_code)) {
                $qrData = \App\Http\Controllers\Api_app\Pay2sController::generateQR(
                    $payment->payment_code,
                    (float) $payment->amount,
                    config('services.url'),
                    config('services.storage.url')
                );
                
                $booking->info_payment = !empty($qrData['qr']) ? $qrData : null;
            } else {
                $booking->info_payment = null;
            }

            return response()->json([
                'result'  => true,
                'message' => lang('get_data_success'),
                'data'    => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => $e->getMessage(),
                'data'    => (object)[],
            ], 500);
        }
    }
    public function WebhookPay2sSpa(Request $request)  {
        $local = $request->local;
        // if(empty($local)) {
        //     $expectedToken = '123';
        //     $authHeader = $request->header('Authorization');

        //     if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        //         return response()->json(['success' => false], 401);
        //     }

        //     $receivedToken = $matches[1];
        //     if ($receivedToken !== $expectedToken) {
        //         return response()->json(['success' => false], 403);
        //     }
        //     $data = $request->json()->all();
        // }
        // else {
        //     $data = $request->data;
        //      $data = json_decode($data, true);;
        // }
      $data = $request->input();
    //    $data = [
    //        "transactions" => [
    //            [
    //                "id" => 12341,
    //                "gateway" => "MBB",
    //                "transactionDate" => "2026-01-08 12:34:37",
    //                "transactionNumber" => "FT26008000642065",
    //                "vaNumber" => "963869",
    //                "accountNumber" => "95652868",
    //                "content" => "PAY-20260318-000026",
    //                "transferType" => "IN",
    //                "transferAmount" => 660000,
    //                "checksum" => "b9cc91651582ddbf1568e376ec0dd555"
    //            ]
    //        ]
    //    ];   
        if (!isset($data['transactions']) || !is_array($data['transactions'])) {
            //            Log::error('pay2s', [
            //                'success' => false,
            //                'message' => 'Invalid payload'
            //            ]);
            return response()->json(['success' => false], 400);
        }
        /** =========================
         *  PROCESS TRANSACTIONS
         *  ========================= */
        foreach ($data['transactions'] as $transaction) {
            if (($transaction['transferType'] ?? '') !== 'IN') {
                continue;
            }
            // $reference_no = extractAndNormalizeVideo($transaction['content']);
            $reference_no = $transaction['content'];
            $money = $transaction['transferAmount'];

            DB::table('tbl_log_webhook_payment')->insert([
                'code_payment' => $transaction['transactionNumber'] ?? NULL,
                'code' => $reference_no ?? NULL,
                'money' => $money,
                'type' => 'pay2s',
                'data_json' => json_encode($transaction),
            ]);

            if (!$reference_no) {
                continue;
            }


            $payment = DB::table('tbl_spa_payments')->where('payment_code', $reference_no)->first();
            if (!$payment || !in_array($payment->status, ['pending'])) {
                continue;
            }
            $amountPaid = $payment->amount_payment + $money;
            $statusPayment = $amountPaid >= $payment->amount ? 'approved' : 'pending';
            $payment->status = $statusPayment;
            $payment->amount_payment = $amountPaid;
            
            DB::table('tbl_spa_payments')->where('id', $payment->id)->update([
                'status' => $payment->status,
                'amount_payment' => $payment->amount_payment,
                'updated_at' => now(),
            ]);
            if($statusPayment == 'approved'){
                DB::table('tbl_spa_bookings')->where('id', $payment->id_booking)->update([
                'payment_status' => 'paid',
                'updated_at'     => now(),
                ]);
            }
            if($statusPayment == 'approved'){
                SocketHelpersAdmin::sendSocketToClient($payment->id_client, $payment->id_booking, 'payment_booking');
                $booking = DB::table('tbl_spa_bookings')->where('id', $payment->id_booking)->first();
                $newRequest = clone $this->request;
                $newRequest->merge(['id' => $payment->id_client]);
                $responseClient = $this->dbAccount->getDetailCustomerPlayerid($newRequest);
                $arr_object_id    = $responseClient->getData(true)['client'] ?? [];
                $dtData = [];
                $dtData['reference_no'] = $booking->booking_code;
                $dtData['reference_no_pay'] = $payment->payment_code;
                Notification::notiApprovePaymentBookingSpa($payment->id_client,$dtData, $arr_object_id,$payment->id);
            }
            DB::commit();  
        }

        //        Log::info('pay2s', [
        //            'success' => true,
        //            'message' => 'Transactions processed successfully'
        //        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transactions processed successfully'
        ], 200);
    }
}