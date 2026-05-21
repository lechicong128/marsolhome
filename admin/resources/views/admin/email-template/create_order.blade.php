<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
      style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Ubold - Responsive Admin Dashboard Template</title>
    <style type="text/css">
        img {
            max-width: 100%;
        }

        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
            line-height: 1.6em;
        }

        body {
            background-color: #f6f6f6;
        }

        @media only screen and (max-width: 640px) {
            body {
                padding: 0 !important;
            }

            h1 {
                font-weight: 800 !important;
                margin: 20px 0 5px !important;
            }

            h2 {
                font-weight: 800 !important;
                margin: 20px 0 5px !important;
            }

            h3 {
                font-weight: 800 !important;
                margin: 20px 0 5px !important;
            }

            h4 {
                font-weight: 800 !important;
                margin: 20px 0 5px !important;
            }

            h1 {
                font-size: 22px !important;
            }

            h2 {
                font-size: 18px !important;
            }

            h3 {
                font-size: 16px !important;
            }

            .container {
                padding: 0 !important;
                width: 100% !important;
            }

            .content {
                padding: 0 !important;
            }

            .content-wrap {
                padding: 10px !important;
            }

            .invoice {
                width: 100% !important;
            }
        }
    </style>
</head>
<body style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6em; background-color: #f6f6f6; margin: 0;"
      bgcolor="#f6f6f6">
<table class="body-wrap"
       style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: #f6f6f6; margin: 0;"
       bgcolor="#f6f6f6">
    <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <td style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;"
            valign="top"></td>
        <td class="container" width="600"
            style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;"
            valign="top">
            <div class="content"
                 style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                <table class="main" width="100%" cellpadding="0" cellspacing="0" itemprop="action" itemscope
                       itemtype="http://schema.org/ConfirmAction"
                       style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; background-color: #fff; margin: 0; border: 1px solid #e9e9e9;"
                       bgcolor="#fff">
                    <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                        <td class="content-wrap"
                            style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 20px;"
                            valign="top">
                            <meta itemprop="name" content="Confirm Email"
                                  style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"/>
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                    <td class="content-block"
                                        style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 0px;"
                                        valign="top">
                                        <h2 style="font-weight: bold">{{$dataMail['title'] ?? ''}}</h2>
                                    </td>
                                </tr>
                                <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                    <td class="content-block"
                                        style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 0px;"
                                        valign="top">
                                        Mã đơn hàng: <a style="color: #1565c0; font-weight: bold;">{{$dataMail['transaction']['reference_no'] ?? ''}}</a>
                                        <br>
                                        Ngày đặt hàng: {{ !empty($dataMail['transaction']['date']) ? (function_exists('_dt') ? _dt($dataMail['transaction']['date']) : date('d/m/Y H:i', strtotime($dataMail['transaction']['date']))) : '' }}
                                        @if(!empty($dataMail['transaction']['note']) || !empty($dataMail['transaction']['customer_note']))
                                        <br>
                                        Ghi chú: <span style="color: #666; font-style: italic;">{{ $dataMail['transaction']['note'] ?? ($dataMail['transaction']['customer_note'] ?? '') }}</span>
                                        @endif
                                        <hr/>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="max-width: 600px; margin: 0 auto; background-color: white;  border-radius: 8px; overflow: hidden;">
                                            <!-- Content -->
                                            <div>

                                                <!-- Thông tin giao hàng -->
                                                <div style="margin-bottom: 25px;">
                                                    <h2 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">Thông tin giao hàng</h2>

                                                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                                                        <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white; font-weight: bold; font-size: 20px;">📍</div>
                                                        <div>
                                                            <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 5px 0;">Địa chỉ nhận hàng</h3>
                                                            <p style="color: #666; font-size: 14px; margin: 0;">{{$dataMail['address_delivery'] ?? ''}}</p>
                                                        </div>
                                                    </div>

                                                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                                                        <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white; font-size: 20px;">👤</div>
                                                        <div>
                                                            <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 5px 0;">{{$dataMail['name_delivery'] ?? '' }}</h3>
                                                            <p style="color: #666; font-size: 14px; margin: 0;">{{$dataMail['phone_delivery'] ?? '' }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Thông tin Đơn hàng -->
                                                <div style="margin-bottom: 25px;">
                                                    <h2 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">Sản phẩm</h2>

                                                    <div style="background-color: #f8f9fa; border-radius: 8px; padding: 0px; margin-bottom: 20px;">

                                                        <!-- Danh sách Sản phẩm -->
                                                        <div>
                                                            <table style="width: 100%; border-collapse: collapse; margin-bottom: 0px;">
                                                                <thead>
                                                                <tr style="background-color: #f8f9fa;">
                                                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; font-weight: 600; font-size: 14px;">Sản phẩm</th>
                                                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; font-weight: 600; font-size: 14px;">SL</th>
                                                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; font-weight: 600; font-size: 14px;text-align: right;">Đơn giá</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                @foreach($dataMail['items'] as $key => $valItem)
                                                                    @php
                                                                        $product = $valItem['product'] ?? [];
                                                                        $price = $valItem['price'] ?? 0;
                                                                        $product_thumb = !empty($product['image']) ? $product['image'] : 'https://placehold.co/80x80';
                                                                    @endphp
                                                                    <tr>
                                                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-size: 14px; display: flex; align-items: center;">
                                                                            <img src="{{$product_thumb}}" style="width: 50px; height: 50px; border-radius: 4px; object-fit: cover; margin-right: 15px;">
                                                                            <div>
                                                                                <strong>{{$product['name'] ?? ''}}</strong><br>
                                                                                @if(!empty($product['variant_option']['name']))
                                                                                <span style="font-size: 13px; color: #767676;">Phân loại: {{$product['variant_option']['name']}}</span>
                                                                                @endif
                                                                            </div>
                                                                        </td>
                                                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-size: 14px; vertical-align: top;">x{{ formatNumber($valItem['quantity'] ?? 0) }}</td>
                                                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-size: 14px; font-weight: 600; color: #1565c0;text-align: right; vertical-align: top;">{{formatMoney($price)}} ₫</td>
                                                                    </tr>
                                                                @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Thông tin thanh toán -->
                                                <div style="margin-bottom: 25px;">
                                                    <h2 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">Thông tin thanh toán</h2>

                                                    <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px;padding-top:0px;padding-left:12px; margin-bottom: 20px;">
                                                        <table style="width: 100%; border-collapse: collapse;">
                                                            <tr>
                                                                <td style="padding: 15px 0 5px 0; font-size: 14px;">Tổng tiền dự tính</td>
                                                                <td style="padding: 15px 0 5px 0; font-size: 14px; text-align: right; font-weight: 600; color: #1565c0;">{{formatMoney($dataMail['transaction']['total'] ?? 0)}} ₫</td>
                                                            </tr>
                                                            @if(!empty($dataMail['payment_method']))
                                                            <tr>
                                                                <td style="padding: 5px 0; font-size: 14px;">Hình thức thanh toán</td>
                                                                <td style="padding: 5px 0; font-size: 14px; text-align: right; font-weight: 600; color: #333;">{{$dataMail['payment_method']}}</td>
                                                            </tr>
                                                            @endif
                                                            @if(!empty($dataMail['transaction']['total_promotion']))
                                                            <tr>
                                                                <td style="padding: 5px 0; font-size: 14px;">Khuyến mãi</td>
                                                                <td style="padding: 5px 0; font-size: 14px; text-align: right; font-weight: 600; color: #ef4444;">-{{formatMoney($dataMail['transaction']['total_promotion'])}} ₫</td>
                                                            </tr>
                                                            @endif
                                                            @if(!empty($dataMail['transaction']['total_discount']))
                                                            <tr>
                                                                <td style="padding: 5px 0; font-size: 14px;">Chiết khấu</td>
                                                                <td style="padding: 5px 0; font-size: 14px; text-align: right; font-weight: 600; color: #ef4444;">-{{formatMoney($dataMail['transaction']['total_discount'])}} ₫</td>
                                                            </tr>
                                                            @endif
                                                            @if(!empty($dataMail['transaction']['total_vat']))
                                                            <tr>
                                                                <td style="padding: 5px 0; font-size: 14px;">Thuế VAT</td>
                                                                <td style="padding: 5px 0; font-size: 14px; text-align: right; font-weight: 600; color: #1565c0;">{{formatMoney($dataMail['transaction']['total_vat'])}} ₫</td>
                                                            </tr>
                                                            @endif
                                                            @if(!empty($dataMail['transaction']['cost_delivery']))
                                                            <tr>
                                                                <td style="padding: 5px 0; font-size: 14px;">Phí vận chuyển</td>
                                                                <td style="padding: 5px 0; font-size: 14px; text-align: right; font-weight: 600; color: #1565c0;">{{formatMoney($dataMail['transaction']['cost_delivery'])}} ₫</td>
                                                            </tr>
                                                            @endif
                                                            @if(!empty($dataMail['transaction']['discount_cost_delivery']))
                                                            <tr>
                                                                <td style="padding: 5px 0; font-size: 14px;">Giảm phí vận chuyển</td>
                                                                <td style="padding: 5px 0; font-size: 14px; text-align: right; font-weight: 600; color: #10b981;">-{{formatMoney($dataMail['transaction']['discount_cost_delivery'])}} ₫</td>
                                                            </tr>
                                                            @endif
                                                            <tr style="border-top: 1px solid #e0e0e0;">
                                                                <td style="padding: 10px 0 0 0; font-size: 16px; font-weight: 600;">Tổng thanh toán</td>
                                                                <td style="padding: 10px 0 0 0; font-size: 16px; text-align: right; font-weight: 600; color: #1565c0;">{{formatMoney($dataMail['transaction']['grand_total'] ?? 0)}} ₫</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Footer -->
                                            <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666;">
                                                <p style="margin: 0 0 5px 0;">CỬA HÀNG NGLOW</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="footer"
                     style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                </div>
            </div>
        </td>
        <td style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;"
            valign="top"></td>
    </tr>
</table>
</body>
</html>
