<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Lịch hẹn Spa</title>
    <style type="text/css">
        img { max-width: 100%; height: auto; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; background-color: #f6f6f6; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        td { vertical-align: top; }
        @media only screen and (max-width: 600px) {
            .wrapper { width: 100% !important; }
            .main-table { width: 100% !important; }
            .col-label { width: 45% !important; }
            .svc-img { display: none !important; }
            .svc-name-cell { padding: 10px 8px !important; }
            .svc-date-cell { padding: 10px 4px !important; font-size: 11px !important; }
            .svc-price-cell { padding: 10px 8px !important; }
            h2 { font-size: 16px !important; }
        }
    </style>
</head>
<body bgcolor="#f6f6f6">

{{-- Wrapper ngoài --}}
<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f6f6f6">
    <tr>
        <td align="center" style="padding: 20px 10px;">

            {{-- Card chính --}}
            <table class="main-table" width="600" cellpadding="0" cellspacing="0"
                   style="background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 6px;">
                <tr>
                    <td style="padding: 24px 24px 0;">

                        {{-- Tiêu đề --}}
                        <h2 style="margin: 0 0 10px; font-size: 20px; font-weight: 700; color: #1a1a1a; font-family: Arial, sans-serif;">
                            {{$dataMail['title'] ?? ''}}
                        </h2>
                        <p style="margin: 0 0 6px; font-size: 14px; color: #555; font-family: Arial, sans-serif;">
                            Mã lịch hẹn: <strong style="color: #1565c0;">{{$dataMail['booking']->booking_code ?? ''}}</strong>
                        </p>
                        @if(!empty($dataMail['booking']->note))
                        <p style="margin: 0 0 6px; font-size: 14px; color: #666; font-family: Arial, sans-serif;">
                            Ghi chú: <em>{{$dataMail['booking']->note}}</em>
                        </p>
                        @endif
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin: 14px 0 0;">
                            <tr><td style="border-top: 1px solid #e0e0e0; height: 1px; font-size: 0;">&nbsp;</td></tr>
                        </table>

                    </td>
                </tr>

                {{-- Thông tin đặt lịch --}}
                <tr>
                    <td style="padding: 16px 24px 0;">
                        <h3 style="margin: 0 0 10px; font-size: 15px; font-weight: 700; color: #285b23; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: .5px;">
                            &#128197; Thông tin đặt lịch
                        </h3>
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="background-color: #f4f8f4; border-radius: 6px;">
                            <tr>
                                <td class="col-label" width="40%" style="padding: 9px 14px; font-size: 13px; color: #666; font-family: Arial, sans-serif;">
                                    &#128100; Khách hàng
                                </td>
                                <td style="padding: 9px 14px; font-size: 13px; font-weight: 700; color: #222; font-family: Arial, sans-serif;">
                                    {{$dataMail['booking']->customer_name ?? ''}}
                                </td>
                            </tr>
                            <tr style="background-color: #edf4ed;">
                                <td style="padding: 9px 14px; font-size: 13px; color: #666; font-family: Arial, sans-serif;">
                                    &#128222; Điện thoại
                                </td>
                                <td style="padding: 9px 14px; font-size: 13px; font-weight: 700; color: #222; font-family: Arial, sans-serif;">
                                    {{$dataMail['booking']->customer_phone ?? ''}}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 9px 14px; font-size: 13px; color: #666; font-family: Arial, sans-serif;">
                                    &#128205; Chi nhánh
                                </td>
                                <td style="padding: 9px 14px; font-size: 13px; font-weight: 700; color: #222; font-family: Arial, sans-serif;">
                                    {{$dataMail['booking']->branch_name ?? ''}}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Danh sách dịch vụ --}}
                <tr>
                    <td style="padding: 20px 24px 0;">
                        <h3 style="margin: 0 0 10px; font-size: 15px; font-weight: 700; color: #285b23; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: .5px;">
                            &#128203; Dịch vụ đặt lịch
                        </h3>

                        {{-- Header bảng --}}
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="border-radius: 6px; overflow: hidden;">
                            <tr style="background-color: #285b23;">
                                <th align="left" style="padding: 10px 12px; font-size: 12px; font-weight: 700; color: #fff; font-family: Arial, sans-serif; width: 38%;">
                                    Dịch vụ
                                </th>
                                <th align="center" style="padding: 10px 8px; font-size: 12px; font-weight: 700; color: #fff; font-family: Arial, sans-serif; width: 22%;">
                                    Ngày hẹn
                                </th>
                                <th align="center" style="padding: 10px 8px; font-size: 12px; font-weight: 700; color: #fff; font-family: Arial, sans-serif; width: 16%;">
                                    Giờ hẹn
                                </th>
                                <th align="right" style="padding: 10px 12px; font-size: 12px; font-weight: 700; color: #fff; font-family: Arial, sans-serif; width: 24%;">
                                    Thành tiền
                                </th>
                            </tr>

                            @php $totalServices = 0; @endphp
                            @forelse($dataMail['services'] as $i => $service)
                            @php
                                $price = $service->price ?? 0;
                                $totalServices += $price;
                                $rowBg = ($i % 2 === 0) ? '#ffffff' : '#f9fdf9';
                            @endphp
                            <tr bgcolor="{{ $rowBg }}" style="border-bottom: 1px solid #e8e8e8;">
                                <td class="svc-name-cell" style="padding: 11px 12px; font-size: 13px; font-weight: 600; color: #222; font-family: Arial, sans-serif; vertical-align: middle;">
                                    {{$service->service_name ?? ($service->name ?? '')}}
                                </td>
                                <td class="svc-date-cell" align="center" style="padding: 11px 8px; font-size: 12px; color: #444; font-family: Arial, sans-serif; vertical-align: middle; white-space: nowrap;">
                                    @if(!empty($service->booking_date))
                                        {{ date('d/m/Y', strtotime($service->booking_date)) }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td align="center" style="padding: 11px 8px; font-size: 12px; color: #444; font-family: Arial, sans-serif; vertical-align: middle; white-space: nowrap;">
                                    @if(!empty($service->booking_time))
                                        {{ \Carbon\Carbon::parse($service->booking_time)->format('H:i') }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="svc-price-cell" align="right" style="padding: 11px 12px; font-size: 13px; font-weight: 700; color: #1565c0; font-family: Arial, sans-serif; vertical-align: middle; white-space: nowrap;">
                                    {{formatMoney($price)}} &#8363;
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" align="center" style="padding: 16px; font-size: 13px; color: #999; font-family: Arial, sans-serif;">
                                    Không có dịch vụ
                                </td>
                            </tr>
                            @endforelse
                        </table>
                    </td>
                </tr>

                {{-- Thanh toán --}}
                <tr>
                    <td style="padding: 20px 24px 0;">
                        <h3 style="margin: 0 0 10px; font-size: 15px; font-weight: 700; color: #285b23; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: .5px;">
                            &#128179; Thanh toán
                        </h3>
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="background-color: #f4f8f4; border-radius: 6px;">
                            <tr>
                                <td style="padding: 9px 14px; font-size: 13px; color: #666; font-family: Arial, sans-serif;">Tạm tính</td>
                                <td align="right" style="padding: 9px 14px; font-size: 13px; font-weight: 600; color: #1565c0; font-family: Arial, sans-serif;">
                                    {{formatMoney($totalServices)}} &#8363;
                                </td>
                            </tr>
                            @if(!empty($dataMail['booking']->payment_method_name))
                            <tr style="background-color: #edf4ed;">
                                <td style="padding: 9px 14px; font-size: 13px; color: #666; font-family: Arial, sans-serif;">Hình thức thanh toán</td>
                                <td align="right" style="padding: 9px 14px; font-size: 13px; font-weight: 600; color: #222; font-family: Arial, sans-serif;">
                                    {{$dataMail['booking']->payment_method_name}}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td style="padding: 9px 14px; font-size: 13px; color: #666; font-family: Arial, sans-serif;">Trạng thái thanh toán</td>
                                <td align="right" style="padding: 9px 14px; font-size: 13px; font-weight: 600; font-family: Arial, sans-serif;">
                                    @php
                                        $payStatus = $dataMail['booking']->payment_status ?? '';
                                        $payColor  = $payStatus === 'paid' ? '#10b981' : ($payStatus === 'pending' ? '#f59e0b' : '#6b7280');
                                    @endphp
                                    <span style="color: {{$payColor}};">{{$dataMail['payment_status'] ?? ''}}</span>
                                </td>
                            </tr>
                            <tr style="background-color: #285b23;">
                                <td style="padding: 12px 14px; font-size: 15px; font-weight: 700; color: #fff; font-family: Arial, sans-serif;">
                                    Tổng thanh toán
                                </td>
                                <td align="right" style="padding: 12px 14px; font-size: 15px; font-weight: 700; color: #fff; font-family: Arial, sans-serif; white-space: nowrap;">
                                    {{formatMoney($dataMail['booking']->total_amount ?? $totalServices)}} &#8363;
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td align="center" style="padding: 24px; font-size: 12px; color: #888; font-family: Arial, sans-serif; line-height: 1.6;">
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="background-color: #f4f8f4; border-radius: 6px;">
                            <tr>
                                <td align="center" style="padding: 16px 20px;">
                                    <p style="margin: 0 0 4px; font-size: 14px; font-weight: 700; color: #285b23; font-family: Arial, sans-serif;">NGLOW SPA</p>
                                    <p style="margin: 0; font-size: 12px; color: #777; font-family: Arial, sans-serif;">Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
            {{-- /Card chính --}}

        </td>
    </tr>
</table>

</body>
</html>
