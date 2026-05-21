<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In Biên Lai - {{ $booking->booking_code }}</title>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            width: 58mm;
        }

        @page {
            size: 58mm auto;
            margin: 0;
        }

        body {
            overflow: visible !important;
        }

        .receipt-container {
            width: 58mm;
            min-width: 58mm;
            max-width: 58mm;
            padding: 2mm 2.5mm 3mm 2.5mm;
            margin: 0 auto;
            background: #fff;
            page-break-inside: auto !important;
            break-inside: auto !important;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: 700; }
        .uppercase { text-transform: uppercase; }

        .company-name {
            font-size: 13px;
            font-weight: 700;
            margin: 4px 0 6px 0;
            text-transform: uppercase;
        }

        .receipt-title {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 10px 0 3px 0;
        }

        .receipt-code {
            font-size: 11px;
            font-weight: 700;
            margin: 0 0 6px 0;
        }

        .small {
            font-size: 9px;
        }

        .italic {
            font-style: italic;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
            height: 0;
            width: 100%;
        }

        .logo {
            display: block;
            margin: 0 auto 4px auto;
            max-width: 42px;
            max-height: 42px;
            object-fit: contain;
        }

        .branch-info,
        .company-info {
            font-size: 9px;
            line-height: 1.35;
            margin-top: 2px;
            word-break: break-word;
        }

        .branch-info div,
        .company-info div,
        .company-info p {
            margin: 0 0 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td, th {
            padding: 2px 0;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .info-table td:first-child {
            width: 36%;
        }

        .info-table td:last-child {
            width: 64%;
        }

        .items-table thead td {
            font-weight: 700;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }

        .col-name {
            width: 50%;
            text-align: left;
        }

        .col-qty {
            width: 12%;
            text-align: center;
        }

        .col-price {
            width: 38%;
            text-align: right;
        }

        .item-name {
            font-weight: 700;
            line-height: 1.3;
        }

        .item-sub {
            font-size: 9px;
            font-style: italic;
            line-height: 1.25;
            margin-top: 1px;
        }

        .total-row td {
            padding-top: 4px;
            font-weight: 700;
        }

        .pay-row td {
            font-size: 13px;
            font-weight: 700;
            padding-top: 4px;
        }

        .note-wrap {
            margin-top: 10px;
            text-align: center;
        }

        .note-wrap p {
            margin: 0 0 8px 0;
            font-size: 9px;
            font-style: italic;
            line-height: 1.35;
        }

        .qr-image {
            display: block;
            margin: 0 auto;
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }

        .thank-you {
            text-align: center;
            font-weight: 700;
            margin: 10px 0 0 0;
            font-size: 10px;
        }

        .mt-4 { margin-top: 4px; }
        .mt-6 { margin-top: 6px; }
        .mb-4 { margin-bottom: 4px; }

        .no-break,
        table,
        tr,
        td,
        .divider,
        .note-wrap,
        .thank-you {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        @media print {
            html, body {
                width: 58mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .receipt-container {
                width: 58mm !important;
                min-width: 58mm !important;
                max-width: 58mm !important;
                margin: 0 auto !important;
                padding: 2mm 2.5mm 3mm 2.5mm !important;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>
    <?php
        $imgLogo = get_option('logo');
        $img = !empty($imgLogo) ? asset($imgLogo) : '';
        $company_name = get_option('name_company');
        $company_address = get_option('address_company');
        $company_phone = get_option('phone_company');

        // Nên truyền sẵn từ controller nếu có thể
        $payment_method_name = null;
        if (!empty($booking->payment_method)) {
            $payment_method_name = DB::table('tbl_payment_mode')
                ->where('id', $booking->payment_method)
                ->value('name');
        }

        $grand_total = 0;
        $quantity_total = 0;
    ?>

    <div class="receipt-container">
        <div class="text-center no-break">
            @if($img)
                <img src="{{ $img }}" alt="Logo" class="logo">
            @endif

            <div class="company-name">
                {{ !empty($company_name) ? $company_name : 'NGLOW SPA' }}
            </div>

            @if(!empty($branch))
                <div class="branch-info text-left">
                    <div><strong>{{ $branch->name }}</strong></div>
                    @if(!empty($branch->address))
                        <div>{{ $branch->address }}</div>
                    @endif
                    @if(!empty($branch->phone))
                        <div>SĐT: {{ $branch->phone }}</div>
                    @endif
                </div>
            @else
                <div class="company-info text-left">
                    @if(!empty($company_address))
                        <div>{{ $company_address }}</div>
                    @endif
                    @if(!empty($company_phone))
                        <div>SĐT: {{ $company_phone }}</div>
                    @endif
                </div>
            @endif
        </div>

        <div class="receipt-title text-center">Biên Lai</div>
        <div class="receipt-code text-center">{{ $booking->booking_code }}</div>

        <div class="divider"></div>

        <table class="info-table">
            <tr>
                <td>Ngày:</td>
                <td class="text-right">{{ date('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <td>Khách hàng:</td>
                <td class="text-right">{{ $booking->customer_name }}</td>
            </tr>
            <tr>
                <td>Số điện thoại:</td>
                <td class="text-right">{{ $booking->customer_phone }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <table class="items-table">
            <thead>
                <tr>
                    <td class="col-name">Dịch vụ</td>
                    <td class="col-qty">SL</td>
                    <td class="col-price">Số tiền</td>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <?php
                        $qty = 1;
                        $itemTotal = (float)$item->amount * $qty;
                        $grand_total += $itemTotal;
                        $quantity_total += $qty;
                    ?>
                    <tr>
                        <td class="col-name">
                            <div class="item-name">
                                {{ $item->service_name ?? 'Dịch vụ' }}
                            </div>
                            <div class="item-sub">
                                @if(!empty($item->booking_date))
                                    {{ date('d/m/Y', strtotime($item->booking_date)) }}
                                    @if(!empty($item->booking_time))
                                        {{ \Carbon\Carbon::parse($item->booking_time)->format('H:i') }}
                                    @endif
                                    &nbsp;
                                @endif
                                {{ number_format($item->amount, 0, ',', '.') }}đ
                                @if(!empty($item->id_treatment_purchase))
                                    (Liệu trình)
                                @endif
                            </div>
                        </td>
                        <td class="col-qty">{{ $qty }}</td>
                        <td class="col-price">{{ number_format($itemTotal, 0, ',', '.') }}đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table>
            <tr class="total-row">
                <td style="width:50%;">Tổng</td>
                <td style="width:12%;" class="text-center">{{ $quantity_total }}</td>
                <td style="width:38%;" class="text-right">{{ number_format($grand_total, 0, ',', '.') }}đ</td>
            </tr>

            @if(!empty($booking->discount_amount) && $booking->discount_amount > 0)
                <tr>
                    <td colspan="2">Tổng cộng</td>
                    <td class="text-right">{{ number_format($grand_total, 0, ',', '.') }}đ</td>
                </tr>
                <tr>
                    <td colspan="2">
                        Khuyến mãi
                        @if(!empty($booking->coupon_code))
                            <span class="small">({{ $booking->coupon_code }})</span>
                        @endif
                    </td>
                    <td class="text-right">- {{ number_format($booking->discount_amount, 0, ',', '.') }}đ</td>
                </tr>
            @endif

            <tr class="pay-row">
                <td colspan="2">Thanh toán</td>
                <td class="text-right">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</td>
            </tr>

            <tr>
                <td colspan="3"><div class="divider"></div></td>
            </tr>

            <tr>
                <td colspan="2">Trạng thái thanh toán</td>
                <td class="text-right">
                    @if($booking->payment_status == 'paid')
                        Đã thanh toán
                    @elseif($booking->payment_status == 'pending')
                        Chưa thanh toán
                    @else
                        {{ $booking->payment_status }}
                    @endif
                </td>
            </tr>

            <tr>
                <td colspan="2">Phương thức TT</td>
                <td class="text-right">{{ $payment_method_name ?? 'Tiền mặt' }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="note-wrap no-break">
            <p>
                Nếu quý khách có trải nghiệm tốt, xin dành chút thời gian để lại đánh giá giúp chúng tôi cải thiện dịch vụ.
            </p>

            @if(!empty($branch) && !empty($branch->icon))
                <img
                    src="{{ asset('storage/' . $branch->icon) }}"
                    alt="QR Fanpage {{ $branch->name }}"
                    class="qr-image"
                >
                <div class="bold mt-4">{{ $branch->name }}</div>
            @endif
        </div>

        <p class="thank-you">
            Cảm ơn &amp; chúc bạn một ngày tuyệt vời!
        </p>
    </div>

    <script>
        window.onload = function () {
            setTimeout(function () {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>