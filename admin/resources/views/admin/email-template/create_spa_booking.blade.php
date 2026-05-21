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
                                        Mã giao dịch: <a style="color: #1565c0;">{{$dataMail['transaction']->reference_no ?? ''}}</a>
                                        <br>
                                        Dịch vụ đăng ký: {{$dataMail['transaction']->name_job_category ?? ''}}
                                        <br>
                                        Thời gian đăng ký: 20/07/2025
                                        <hr/>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="max-width: 600px; margin: 0 auto; background-color: white;  border-radius: 8px; overflow: hidden;">
                                            <!-- Content -->
                                            <div>

                                                <!-- Vị trí làm việc -->
                                                <div style="margin-bottom: 25px;">
                                                    <h2 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">Vị trí làm việc</h2>

                                                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                                                        <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white; font-weight: bold;">📍</div>
                                                        <div>
                                                            <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 5px 0;">{{$dataMail['transaction']->name_type_address }}</h3>
                                                            <p style="color: #666; font-size: 14px; margin: 0;">{{$dataMail['transaction']->work_address ?? ''}}</p>
                                                        </div>
                                                    </div>

                                                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                                                        <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                                                            {!! !empty($dataMail['customer']->avatar) ? '<img style="width: 40px;height: 40px;border-radius: 50%;" src="' . url($dataMail['customer']->avatar).'">' : '👤' !!}
                                                        </div>
                                                        <div>
                                                            <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 5px 0;">{{$dataMail['customer']->fullname ?? '' }}</h3>
                                                            <p style="color: #666; font-size: 14px; margin: 0;">{{$dataMail['customer']->phone ?? '' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Thông tin công việc -->
                                                <div style="margin-bottom: 25px;">
                                                    <h2 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">Thông tin công việc</h2>

                                                    <div style="background-color: #f8f9fa; border-radius: 8px; padding: 0px; margin-bottom: 20px;">

                                                        <!-- Thời gian làm việc -->
                                                        <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                                                            <table style="width: 100%; border-collapse: collapse;">
                                                                <tr>
                                                                    <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                                                                        <h4 style="font-size: 14px; font-weight: 600; margin: 0 0 5px 0; color: #333;">Ngày làm việc</h4>
                                                                        <p style="font-size: 14px; color: #666; margin: 0;">{{ _dayOfWeek($dataMail['transaction']->date_start) }}</p>
                                                                    </td>
                                                                    <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                                                                        <h4 style="font-size: 14px; font-weight: 600; margin: 0 0 5px 0; color: #333;">Kết thúc</h4>
                                                                        <p style="font-size: 14px; color: #666; margin: 0;">{{ _dayOfWeek($dataMail['transaction']->date_end) }}</p>
                                                                    </td>

                                                                </tr>
                                                                <tr>
                                                                    <td style="width: 50%; vertical-align: top; ">
                                                                        <h4 style="font-size: 14px; font-weight: 600; margin: 0 0 5px 0; color: #333;">Bắt đầu</h4>
                                                                        <p style="font-size: 14px; color: #666; margin: 0;">{{$dataMail['transaction']->time_transaction }}</p>
                                                                    </td>
                                                                    <td style="width: 50%; vertical-align: top; ">
                                                                        <h4 style="font-size: 14px; font-weight: 600; margin: 0 0 5px 0; color: #333;">Loại nhà</h4>
                                                                        <p style="font-size: 14px; color: #666; margin: 0;">{{$dataMail['transaction']->name_type_address }}</p>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>

                                                        <!-- Danh sách dịch vụ -->
                                                        <div>
                                                            <h4 style="font-size: 16px; font-weight: 600; margin: 0 0 0px 0; color: #333;">Danh sách dịch vụ</h4>
                                                            <table style="width: 100%; border-collapse: collapse; margin-bottom: 0px;">
                                                                <thead>
                                                                <tr style="background-color: #f8f9fa;">
                                                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; font-weight: 600; font-size: 14px;">Đơn giá</th>
                                                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; font-weight: 600; font-size: 14px;">SL</th>
                                                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; font-weight: 600; font-size: 14px;text-align: right;">Thành tiền</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                @php
                                                                    $totalService = 0;
                                                                @endphp
                                                                @foreach($dataMail['transaction']->service as $key => $valService)
                                                                    <tr>
                                                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-size: 14px;">
                                                                            <strong>{{$valService->name_child}}</strong><br>
                                                                            <span style="font-weight: 600; color: #1565c0;">{{formatNumber($valService->price)}} ₫</span>
                                                                        </td>
                                                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-size: 14px;">x{{$valService->quantity}} {{$valService->name_unit}}</td>
                                                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-size: 14px; font-weight: 600; color: #1565c0;text-align: right;">{{formatNumber($valService->grand_total)}} ₫</td>
                                                                    </tr>
                                                                    @php
                                                                        $totalService += $valService->grand_total;
                                                                    @endphp
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
                                                                <td style="padding: 5px 0; font-size: 14px;">Tổng dịch vụ tạm tính</td>
                                                                <td style="padding: 5px 0; font-size: 14px; text-align: right; font-weight: 600; color: #1565c0;">{{formatNumber($totalService)}} ₫</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding: 5px 0; font-size: 14px;">Phí khảo sát</td>
                                                                <td style="padding: 5px 0; font-size: 14px; text-align: right; font-weight: 600; color: #1565c0;">{{formatNumber($dataMail['transaction']->price_service_fee)}} ₫</td>
                                                            </tr>
                                                            <tr style="border-top: 1px solid #e0e0e0;">
                                                                <td style="padding: 10px 0 0 0; font-size: 16px; font-weight: 600;">Tổng cộng</td>
                                                                <td style="padding: 10px 0 0 0; font-size: 16px; text-align: right; font-weight: 600; color: #1565c0;">{{formatNumber($dataMail['transaction']->grand_total)}} ₫</td>
                                                            </tr>
                                                        </table>
                                                    </div>

                                                    <div style="background-color: #e3f2fd; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                                                        <h4 style="font-size: 14px; font-weight: 600; margin: 0 0 5px 0; color: #1565c0;">Tiền tạm ứng 💡</h4>
                                                        <p style="font-size: 14px; color: #666; margin: 0 0 10px 0;">(Tiền sẽ được hoàn trả khi nghiệm thu.)</p>
                                                        <div style="font-size: 18px; font-weight: 600; color: #1565c0;">{{formatNumber($dataMail['transaction']->price_service_fee)}} ₫</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Footer -->
                                            <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666;">
                                                <p style="margin: 0 0 5px 0;">HOMECARE</p>
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
