<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
class Sendmail extends Model
{
    use HasFactory;
    // protected $table = 'tbl_sendmail';
    static function SendMailCreateOrdersNew($id = '')
    {
        $transactionService = new \App\Services\TransactionService();
        $request = app(\Illuminate\Http\Request::class);
        $request->merge(['id' => $id]);
        $response = $transactionService->getListDetailTransactionSendmail($request);
        $dataRes = $response->getData(true);
        if (empty($dataRes['result'])) {
            return;
        }
        // API có thể trả về data.data hoặc data trực tiếp
        $transaction = $dataRes['data']['data'] ?? ($dataRes['data'] ?? []);
        if (empty($transaction)) {
            \Illuminate\Support\Facades\Log::warning('SendMailCreateOrdersNew: no transaction for id=' . $id);
            return;
        }
        \Illuminate\Support\Facades\Log::info('SendMailCreateOrdersNew data keys', array_keys($transaction));

        $email_admin = get_option('admin_email_orders');
        $email_cc_admin = get_option('cc_admin_email_orders');

        if (!empty($email_admin)) {
            $customer = $transaction['customer'] ?? [];
            $info_delivery = $transaction['info_delivery'] ?? [];

            \Illuminate\Support\Facades\Log::info('SendMailCreateOrdersNew info_delivery keys', array_keys($info_delivery ?: []));

            $name_delivery = !empty($info_delivery['name_delivery']) ? $info_delivery['name_delivery'] : ($customer['fullname'] ?? '');
            $phone_delivery = !empty($info_delivery['phone_delivery']) ? $info_delivery['phone_delivery'] : ($customer['phone'] ?? '');

            // Thử nhiều key cho địa chỉ - key chính là address_delivery theo view.blade.php
            $address_delivery = $info_delivery['address_delivery']
                ?? $info_delivery['full_address']
                ?? $info_delivery['address_full']
                ?? $info_delivery['address']
                ?? '';

            $dataMail = [
                'title' => 'ĐƠN HÀNG MỚI [' . ($transaction['reference_no'] ?? '') . ']',
                'transaction' => $transaction,
                'items' => $transaction['items'] ?? [],
                'name_delivery' => $name_delivery,
                'phone_delivery' => $phone_delivery,
                'address_delivery' => $address_delivery,
                'payment_method' => $transaction['payment_mode']['name'] ?? '',
            ];

            if (!empty($email_admin)) {
                Mail::send('admin.email-template.create_order', ['dataMail' => $dataMail], function ($message) use ($email_admin, $email_cc_admin, $dataMail) {
                    $from_address = config('mail.from.address') ?: config('mail.mailers.smtp.username');
                    $from_name = config('mail.from.name') ?: config('app.name');
                    
                    $message->from($from_address, $from_name);
                    $message->to($email_admin);
                    if (!empty($email_cc_admin)) {
                        $message->cc(explode(',', $email_cc_admin));
                    }
                    $message->subject($dataMail['title']);
                });
            }
        }
    }
    static function SendMailCreateOrders($id = '')
    {
        $transactionService = new \App\Services\TransactionService();
        $request = app(\Illuminate\Http\Request::class);
        $request->merge(['id' => $id]);
        $response = $transactionService->getListDetailTransactionSendmail($request);
        $dataRes = $response->getData(true);
        if (empty($dataRes['result'])) {
            return;
        }
        $transaction = $dataRes['data']['data'] ?? [];
        if (empty($transaction)) {
            return;
        }

        $email_admin = get_option('admin_email_orders');
        $email_cc_admin = get_option('cc_admin_email_orders');

        if (!empty($email_admin)) {
            $template = DB::table('tbl_email_template')->where('id', 2)->first();
           
            $content = '<style>
            #wap_email {
                display: flex;
                margin-bottom: 5px;
                flex-wrap: wrap;
            }
            </style>';
            if (!empty($template->content)) {
                $content .= $template->content;
            }
            $customer = $transaction['customer'] ?? [];
            $info_delivery = $transaction['info_delivery'] ?? [];

            $name_clients = !empty($info_delivery['name_delivery']) ? $info_delivery['name_delivery'] : ($customer['fullname'] ?? '');
            $phone_clients = !empty($info_delivery['phone_delivery']) ? $info_delivery['phone_delivery'] : ($customer['phone'] ?? '');
            $email_clients = !empty($customer['email']) ? $customer['email'] : '';

            $content = str_replace('{name_clients}', $name_clients, $content);
            $content = str_replace('{phone_clients}', $phone_clients, $content);
            $content = str_replace('{email_clients}', $email_clients, $content);
            $content = str_replace('{date_order}', _dt($transaction['date']), $content);
            $content = str_replace('{code_order}', ($transaction['reference_no']), $content);
            $content = str_replace('{name_tournaments}', '', $content);
            $content = str_replace('{date_tournaments}', '', $content);

            $imgFavicon = get_option('favicon');
            $imgFavicon = !empty($imgFavicon) ? $imgFavicon : imgCameraDefault();
            $img = '<img style="width: 100px;" src="' . $imgFavicon . '">';

            $content = str_replace('{logo}', $img, $content);
            $items = $transaction['items'] ?? [];
            $html_items = '<table border="1" width="100%" cellpadding="0" cellspacing="0" style="min-width: 300px; margin: 0 auto; height: 81px; border-radius: 10px; border-color: #e6e6e6; border: 1px solid #e6e6e6; padding: 10px;">
                            <tbody>
                                <tr style="height: 23px;">
                                    <td style="width: 50%; height: 23px; border-bottom: 1px solid #e6e6e6; border-top: none; border-left: none; border-right: none;"><strong><span style="font-size: 12pt;color:#4c6735;">Sản phẩm</span></strong></td>
                                </tr>';
            $dem = 0;
            foreach ($items as $key => $value) {
                $product = $value['product'] ?? [];
                $product_thumb = !empty($product['image']) ? $product['image'] : '';
                $html_items .= '<tr style="height: 19px;"><td style="width: 50%; height: 20px; border-style: none;"><div style="">';
                $dem++;
                $html_items .= '<div class="wrap-box-content" style="font-size: 13px;width: 100%;padding-bottom: 15px;display: flex;">
                            <div class="box-content-text-dashboard box-content-img">
                                <img src="' . $product_thumb . '" style="margin-top: 8px;width: 80px !important;height: 80px;border-radius: 5px;">
                            </div>
                            <div class=" box-content-text-dashboard box-content-value" style="margin-left: 25px;color:black;margin-top: 8px;line-height: 21px;">
                                <div><b>' . ($product['name'] ?? '') . '</b></div>';

                if (!empty($product['variant_option']['name'])) {
                    $html_items .= '<div style="color: #767676;">Phân loại: ' . $product['variant_option']['name'] . '</div>';
                }

                $html_items .= '<div>SL: ' . formatNumber($value['quantity']) . '</div>';
                $html_items .= '<div style="font-weight: 500;color:#6A853E;">' . formatMoney($value['price']) . 'đ</div>';

                $html_items .= '
                        </div></div>';
                $html_items .= '</div></td></tr>';
            }
            $html_items .= '</tbody>
                        </table>';

            $content = str_replace('{items}', $html_items, $content);

            $summary_html = '<div style="margin-top: 15px; border-top: 1px solid #e6e6e6; padding-top: 10px; font-family: sans-serif; color: #1f2937;">';
            $summary_html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span style="color: #6b7280;">Tổng tiền: </span> <b>' . formatMoney($transaction['total']) . 'đ</b></div>';
            if ($transaction['total_promotion'] > 0) {
                $summary_html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span style="color: #6b7280;">Khuyến mãi: </span> <b style="color: #ef4444;">-' . formatMoney($transaction['total_promotion']) . 'đ</b></div>';
            }
            if ($transaction['total_discount'] > 0) {
                $summary_html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span style="color: #6b7280;">Chiết khấu (' . ($transaction['percent_discount'] ?? 0) . '%): </span> <b style="color: #ef4444;">-' . formatMoney($transaction['total_discount']) . 'đ</b></div>';
            }
            if ($transaction['total_vat'] > 0) {
                $summary_html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span style="color: #6b7280;">Thuế VAT (' . ($transaction['vat'] ?? 0) . '%): </span> <b>' . formatMoney($transaction['total_vat']) . 'đ</b></div>';
            }
            if ($transaction['cost_delivery'] > 0) {
                $summary_html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span style="color: #6b7280;">Phí vận chuyển: </span> <b>' . formatMoney($transaction['cost_delivery']) . 'đ</b></div>';
            }
            if ($transaction['discount_cost_delivery'] > 0) {
                $summary_html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span style="color: #6b7280;">Giảm phí vận chuyển: </span> <b style="color: #10b981;">-' . formatMoney($transaction['discount_cost_delivery']) . 'đ</b></div>';
            }
            $summary_html .= '<div style="display: flex; justify-content: space-between; font-size: 16px; color: #6A853E; margin-top: 10px; border-top: 2px solid #e5e7eb; padding-top: 10px;"><span>Thành tiền: </span> <b>' . formatMoney($transaction['grand_total']) . 'đ</b></div>';
            $summary_html .= '</div>';

            $content .= $summary_html;
            $date = date('d/m/Y H:i:s');
            $title_email = 'Đơn hàng mới '.$transaction['reference_no'].' '.$date;

            if (!empty($email_admin)) {
                Mail::html($content, function ($message) use ($email_admin, $email_cc_admin, $title_email) {
                    $from_address = config('mail.from.address') ?: config('mail.mailers.smtp.username');
                    $from_name = config('mail.from.name') ?: config('app.name');
                    
                    $message->from($from_address, $from_name);
                    $message->to($email_admin);
                    if (!empty($email_cc_admin)) {
                        $message->cc($email_cc_admin);
                    }
                    $message->subject($title_email);
                });
            }
        }
    }
    static function SendMailCreateBooking($id = '')
    {
        $booking = DB::table('tbl_spa_bookings as b')
            ->select('b.*', 'br.name as branch_name', 'pm.name as payment_method_name')
            ->leftJoin('tbl_branches as br', 'br.id', '=', 'b.branch_id')
            ->leftJoin('tbl_payment_mode as pm', 'pm.id', '=', 'b.payment_method')
            ->where('b.id', $id)
            ->first();

        if (empty($booking)) {
            return;
        }

        $branch_id = $booking->branch_id;
        $emails = \App\Models\User::where('is_receive_email_spa', 1)
            ->whereHas('branches', function ($q) use ($branch_id) {
                $q->where('tbl_user_branch.branch_id', $branch_id);
            })
            ->pluck('email')
            ->filter()
            ->toArray();

        // Admin defaults if no users are setup for this branch? Or strict rule.
        // Rule: "nếu check mà ko chọn chi nhánh thì ko gửi"
        if (empty($emails)) {
            return;
        }

        $template = DB::table('tbl_email_template')->where('id', 1)->first();
       
        $content = '<style>
        #wap_email {
            display: flex;
            margin-bottom: 5px;
            flex-wrap: wrap;
        }
        </style>';
        
        if (!empty($template->content)) {
            $content .= $template->content;
        }

        $content = str_replace('{name_clients}', $booking->customer_name ?? '', $content);
        $content = str_replace('{phone_clients}', $booking->customer_phone ?? '', $content);
        $content = str_replace('{email_clients}', '', $content);
        $date_start = !empty($booking->booking_date) ? date('d/m/Y', strtotime($booking->booking_date)) . ' ' . ($booking->booking_time ?? '') : '';
        $content = str_replace('{date_order}', $date_start, $content);
        $content = str_replace('{code_order}', ($booking->booking_code ?? ''), $content);
        $content = str_replace('{name_tournaments}', '', $content);
        $content = str_replace('{date_tournaments}', '', $content);

        $imgFavicon = get_option('favicon');
        $imgFavicon = !empty($imgFavicon) ? $imgFavicon : imgCameraDefault();
        $img = '<img style="width: 100px;" src="' . $imgFavicon . '">';

        $content = str_replace('{logo}', $img, $content);
        
        $services = DB::table('tbl_spa_booking_services as bs')
           ->leftJoin('tbl_services as s', 's.id', '=', 'bs.id_service')
           ->select('bs.*', 's.image as service_image')
           ->where('bs.id_booking', $id)
           ->get();

        $html_items = '<table border="1" width="100%" cellpadding="0" cellspacing="0" style="min-width: 300px; margin: 0 auto; border-radius: 10px; border-color: #e6e6e6; border: 1px solid #e6e6e6; padding: 10px;">
                        <tbody>
                            <tr style="height: 23px;">
                                <td style="width: 50%; height: 23px; border-bottom: 1px solid #e6e6e6; border-top: none; border-left: none; border-right: none;"><strong><span style="font-size: 12pt;color:#4c6735;">Dịch vụ Spa</span></strong></td>
                            </tr>';
        
        foreach ($services as $key => $value) {
            $service_name = $value->name ?? '';
            $price = $value->price ?? 0;
            
            $imagePath = $value->service_image ?? '';
            if (!empty($imagePath) && !str_starts_with($imagePath, 'http')) {
                $imagePath = asset('storage/' . $imagePath);
            }
            $product_thumb = !empty($imagePath) ? $imagePath : imgCameraDefault();

            $html_items .= '<tr style="height: 19px;"><td style="width: 50%; height: 20px; border-style: none;"><div style="">';
            $html_items .= '<div class="wrap-box-content" style="font-size: 13px;width: 100%;padding-bottom: 15px;display: flex;">
                        <div class="box-content-text-dashboard box-content-img">
                            <img src="' . $product_thumb . '" style="margin-top: 8px;width: 80px !important;height: 80px;border-radius: 5px;object-fit:cover;">
                        </div>
                        <div class=" box-content-text-dashboard box-content-value" style="margin-left: 25px;color:black;margin-top: 8px;line-height: 21px;">
                            <div><b>' . $service_name . '</b></div>';

            $html_items .= '<div>SL: 1</div>';
            $html_items .= '<div style="font-weight: 500;color:#6A853E;">' . formatMoney($price) . 'đ</div>';

            $html_items .= '</div></div></div></td></tr>';
        }
        $html_items .= '</tbody>
                    </table>';

        $content = str_replace('{items}', $html_items, $content);

        $paymentStatusMap = [
            'pending'   => 'Chờ thanh toán',
            'paid'      => 'Đã thanh toán',
            'pay_later' => 'Thanh toán sau',
            'transfer'  => 'Chuyển khoản',
        ];
        $paymentStatus = $paymentStatusMap[$booking->payment_status] ?? $booking->payment_status;

        $summary_html = '<div style="margin-top: 15px; border-top: 1px solid #e6e6e6; padding-top: 10px; font-family: sans-serif; color: #1f2937;">';
        $summary_html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span style="color: #6b7280;">Chi nhánh: </span> <b>' . ($booking->branch_name ?? '') . '</b></div>';
        if (!empty($booking->payment_method_name)) {
            $summary_html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span style="color: #6b7280;">Thanh toán: </span> <b>' . $booking->payment_method_name . '</b></div>';
        }
        $summary_html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span style="color: #6b7280;">Trạng thái thanh toán: </span> <b>' . $paymentStatus . '</b></div>';
        $summary_html .= '<div style="display: flex; justify-content: space-between; font-size: 16px; color: #6A853E; margin-top: 10px; border-top: 2px solid #e5e7eb; padding-top: 10px;"><span>Thành tiền: </span> <b>' . formatMoney($booking->total_amount ?? 0) . 'đ</b></div>';
        $summary_html .= '</div>';

        $content .= $summary_html;
        $date = date('d/m/Y H:i:s');
        $title_email = 'Lịch hẹn Spa mới '.$booking->booking_code.' '.$date;

        foreach ($emails as $email_to) {
            Mail::html($content, function ($message) use ($email_to, $title_email) {
                $from_address = config('mail.from.address') ?: config('mail.mailers.smtp.username');
                $from_name = config('mail.from.name') ?: config('app.name');
                
                $message->from($from_address, $from_name);
                $message->to($email_to);
                $message->subject($title_email);
            });
        }
    }
    static function SendMailCreateBookingNew($id = '')
    {
        $booking = DB::table('tbl_spa_bookings as b')
            ->select('b.*', 'br.name as branch_name', 'pm.name as payment_method_name')
            ->leftJoin('tbl_branches as br', 'br.id', '=', 'b.branch_id')
            ->leftJoin('tbl_payment_mode as pm', 'pm.id', '=', 'b.payment_method')
            ->where('b.id', $id)
            ->first();

        if (empty($booking)) {
            return;
        }

        $branch_id = $booking->branch_id;
        $emails = \App\Models\User::where('is_receive_email_spa', 1)
            ->whereHas('branches', function ($q) use ($branch_id) {
                $q->where('tbl_user_branch.branch_id', $branch_id);
            })
            ->pluck('email')
            ->filter()
            ->toArray();

        // Rule: "nếu check mà ko chọn chi nhánh thì ko gửi"
        if (empty($emails)) {
            return;
        }

        $services = DB::table('tbl_spa_booking_services as bs')
            ->leftJoin('tbl_services as s', 's.id', '=', 'bs.id_service')
            ->select('bs.*', 's.name as service_name', 's.image as service_image')
            ->where('bs.id_booking', $id)
            ->get()
            ->map(function ($item) {
                $imagePath = $item->service_image ?? '';
                if (!empty($imagePath) && !str_starts_with($imagePath, 'http')) {
                    $imagePath = asset('storage/' . $imagePath);
                }
                $item->service_image_url = $imagePath;
                return $item;
            });

        $paymentStatusMap = [
            'pending'   => 'Chờ thanh toán',
            'paid'      => 'Đã thanh toán',
            'pay_later' => 'Thanh toán sau',
            'transfer'  => 'Chuyển khoản',
        ];
        $paymentStatus = $paymentStatusMap[$booking->payment_status] ?? ($booking->payment_status ?? '');

        $booking_date_fmt = !empty($booking->booking_date)
            ? date('d/m/Y', strtotime($booking->booking_date)) . ' ' . ($booking->booking_time ?? '')
            : '';

        $dataMail = [
            'title'          => 'LỊCH HẸN SPA MỚI [' . ($booking->booking_code ?? '') . ']',
            'booking'        => $booking,
            'services'       => $services,
            'payment_status' => $paymentStatus,
            'booking_date'   => $booking_date_fmt,
        ];

        $title_email = 'Lịch hẹn Spa mới ' . ($booking->booking_code ?? '') . ' ' . date('d/m/Y H:i:s');

        foreach ($emails as $email_to) {
            Mail::send('admin.email-template.create_booking', ['dataMail' => $dataMail], function ($message) use ($email_to, $title_email) {
                $from_address = config('mail.from.address') ?: config('mail.mailers.smtp.username');
                $from_name = config('mail.from.name') ?: config('app.name');

                $message->from($from_address, $from_name);
                $message->to($email_to);
                $message->subject($title_email);
            });
        }
    }
}