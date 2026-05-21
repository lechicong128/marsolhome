@extends('admin.layouts.index')
@section('content')
    <style>
        /* Toàn bộ CSS được bao bọc trong ID #nglow-policy-app để tránh xung đột */
        #nglow-policy-app {
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f5;
            padding: 20px 0;
            color: #333;
        }
        #nglow-policy-app .ng-serif-font { font-family: 'Playfair Display', serif; }

        /* Brand Colors */
        #nglow-policy-app .ng-brand-bg { background-color: #8b0000 !important; color: white; }
        #nglow-policy-app .ng-brand-text { color: #8b0000; font-weight: bold; }

        /* Header & Logo */
        #nglow-policy-app .ng-logo-container {
            background: #8b0000;
            padding: 5px 15px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }

        /* Panel Custom */
        #nglow-policy-app .ng-panel-luxury {
            border-radius: 4px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            background: #fff;
        }
        #nglow-policy-app .ng-panel-luxury > .ng-panel-heading {
            background-color: #8b0000;
            color: white;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 12px 20px;
            border-bottom: 1px solid #8b0000;
        }
        #nglow-policy-app .ng-panel-body {
            padding: 20px;
        }

        /* Input Styling */
        #nglow-policy-app .ng-input-calc {
            border: 2px solid #eee;
            font-weight: bold;
            color: #8b0000;
            text-align: right;
            border-radius: 4px;
        }
        #nglow-policy-app .ng-input-calc:focus {
            border-color: #8b0000;
            outline: none;
            box-shadow: none;
        }
        #nglow-policy-app .ng-input-res {
            background-color: #f9f9f9 !important;
            border: 1px solid #ccc;
            font-weight: bold;
            color: #333;
            text-align: right;
        }

        /* Inline Inputs */
        #nglow-policy-app .ng-input-inline {
            display: inline-block;
            width: 120px;
            height: 30px;
            padding: 2px 8px;
            font-size: 12px;
        }

        /* Textarea */
        #nglow-policy-app .ng-textarea-inline {
            width: 100%;
            height: 60px;
            padding: 8px;
            font-size: 12px;
            color: #444;
            border: 1px solid #eee;
            resize: vertical;
            border-radius: 4px;
        }
        #nglow-policy-app .ng-textarea-inline:focus {
            border-color: #8b0000;
            outline: none;
        }

        /* Tables */
        #nglow-policy-app .ng-table-custom th {
            background: #fcfcfc;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 2px solid #8b0000 !important;
            color: #666;
            text-align: center;
        }
        th.to {
            width: 200px!important;
        }
        #nglow-policy-app .ng-table-custom td {
            vertical-align: middle !important;
            border-top: 1px solid #f0f0f0;
        }

        #nglow-policy-app .ng-reward-highlight {
            background-color: #fffafa;
            font-weight: bold;
        }

        #nglow-policy-app .ng-footer {
            background: #333;
            color: #ccc;
            padding: 30px 0;
            margin-top: 50px;
            border-radius: 4px;
        }
        .tdMoney {
            max-width: 250px;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title ?? ''}}</h4>
        </div>
    </div>
    <div class="row" id="nglow-policy-app">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <form id="accumulationForm" action="admin/accumulation/submit" method="post" data-parsley-validate novalidate>
                    <div class="row">
                        <!-- PHẦN 1: CHIẾT KHẤU ĐƠN HÀNG -->
                        <div class="col-md-12">
                            <div class="ng-panel-luxury">
                                <div class="ng-panel-heading"><i class="fa fa-tags"></i> 1. Chiết khấu đơn hàng & Đào tạo</div>
                                <div class="ng-panel-body">
                                    <table class="table ng-table-custom">
                                        <thead>
                                        <tr>
                                            <th colspan="2">Giá trị đơn hàng</th>
                                            <th rowspan="2">Mức CK (%)</th>
                                            <th rowspan="2">Nội dung Hỗ trợ Training</th>
                                        </tr>
                                        <tr>
                                            <th class="to">Từ (Lớn hơn)</th>
                                            <th class="to">Đến (Nhỏ Hơn hoặc bằng)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($discount_total_orders as $key => $value)
                                            <tr>
                                                <td><input type="text" name="discount_total_orders[{{$value->id}}][total_order_start]" class="{{empty($key) ? 'hide' : ''}} form-control ng-input-calc number-format" value="{{!empty($value->total_order_start) ? number_format($value->total_order_start) : 0}}"></td>
                                                <td><input type="text" name="discount_total_orders[{{$value->id}}][total_order_end]" class="{{($key + 1) == count($discount_total_orders) ? 'hide' : ''}} form-control ng-input-calc number-format" value="{{!empty($value->total_order_end) ? number_format($value->total_order_end) : ''}}"></td>
                                                <td class="text-center">
                                                    <input type="text" name="discount_total_orders[{{$value->id}}][discount]" class="form-control ng-input-inline ng-input-calc number-format" value="{{$value->discount}}">
                                                </td>
                                                <td>
                                                    <textarea class="ng-textarea-inline" name="discount_total_orders[{{$value->id}}][content]">{{$value->content ?? ''}}</textarea>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="ng-panel-luxury">
                                <div class="ng-panel-heading"><i class="fa fa-percent"></i> 2. Tích lũy Leaders F1</div>
                                <div class="ng-panel-body">
                                    {{--                                    Tính theo doanh số sau chiết khấu--}}
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>Cấp</th>
                                            <th>Mức chiết khấu(%)</th>
                                            @foreach($reached as $valReached)
                                                <th>Đạt Mốc {{$valReached->total_start / 1000000000}} Tỷ (Thưởng)</th>
                                            @endforeach
                                            <th>Ghi Chú</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($leaders as $key => $value)
                                            <tr>
                                                <td>{{$value->level}}</td>
                                                <td><input type="text" name="leaders[{{$value->id}}][level_discount]"  class="form-control ng-input-inline ng-input-calc number-format" value="{{number_format($value->level_discount)}}"></td>
                                                @foreach($reached as $valReached)
                                                    <td><input type="text" name="leaders[{{$value->id}}][reached][{{$valReached->id}}]" class="form-control ng-input-calc number-format" value="{{!empty($reward[$value->id][$valReached->id]) ? number_format($reward[$value->id][$valReached->id]) : ''}}"></td>
                                                @endforeach
                                                <td><textarea class="ng-textarea-inline" name="leaders[{{$value->id}}][note]">{{$value->note ?? ''}}</textarea></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <div class="alert alert-danger" style="font-size: 11px; border-radius: 0;">
                                        <i class="fa fa-bolt"></i> * Càng lên cấp cao – % CK cao – mốc thưởng càng lớn.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PHẦN 2: CHÍNH SÁCH CHUNG -->
                        <div class="col-md-5">
                            <div class="ng-panel-luxury">
                                <div class="ng-panel-heading"><i class="fa fa-percent"></i> 3. Quyền lợi & chính sách công ty</div>
                                <div class="ng-panel-body">
                                    <table class="table table-bordered ng-table-custom">
                                        <thead>
                                        <tr>
                                            <th>% NPP Nhập</th>
                                            <th>% Hoa hồng chênh lệch</th>
                                            <th>Tích Lũy/Năm</th>
                                            <th>Đặc Biệt Thưởng Nóng (%)<br/> (Khi khách hàng thanh toán 100% đơn trong lần đầu tiên)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($interest as $key => $value)
                                            <tr>
                                                <td><input type="text" name="interest[{{$value->id}}][discount_npp]" class="form-control ng-input-inline ng-input-calc number-format" value="{{number_format($value->discount_npp)}}"></td>
                                                <td><input type="text" name="interest[{{$value->id}}][difference]" class="form-control ng-input-inline ng-input-calc number-format" value="{{number_format($value->difference)}}"></td>
                                                @if($key == 0)
                                                    <td rowspan="{{count($interest)}}">Giá trị đơn hàng *
                                                        (578.000.000 / 4.800.000.000)
                                                    </td>
                                                @endif
                                                <td class="text-center">
                                                    <input type="text" name="interest[{{$value->id}}][radio_bonus]" class="form-control ng-input-inline ng-input-calc number-format" value="{{number_format($value->radio_bonus)}}">
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <div class="alert alert-danger" style="font-size: 11px; border-radius: 0;">
                                        <i class="fa fa-bolt"></i> <b>THƯỞNG NÓNG:</b> * % thưởng nóng khi khách hàng thanh toán 100% đơn trong lần đầu tiên
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="ng-panel-luxury">
                                <div class="ng-panel-heading"><i class="fa fa-calculator"></i> 4. Thu nhập thụ động (NPP tái nhập hàng)</div>
                                <div class="ng-panel-body">
                                    <table class="table table-bordered ng-table-custom" id="ng-table-passive">
                                        <thead>
                                        <tr class="active">
                                            <th colspan="2">Giá trị đơn hàng (VNĐ)</th>
                                            <th rowspan="2">% Hoa hồng</th>
                                            <th rowspan="2">Số tiền Hoa hồng</th>
                                            <th rowspan="2">Tích lũy/năm</th>
                                            <th rowspan="2">TỔNG % Hoa Hồng Nhận</th>
                                            <th rowspan="2">Tổng Thực Nhận</th>
                                        </tr>
                                        <tr class="active">
                                            <th class="to">Từ (Lớn hơn hoặc bằng)</th>
                                            <th class="to">Đến (nhỏ hơn)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $MoneyPass = (578000000 / 4800000000);
                                        @endphp
                                        @foreach($passive as $key => $value)
                                            <tr class="ng-row-passive">
                                                <td>
                                                    <input type="text" name="passive[{{$value->id}}][total_order_start]" class="{{empty($key) ? 'hide' : ''}} form-control ng-input-calc ng-val-don number-format" value="{{number_format($value->total_order_start)}}">
                                                </td>
                                                <td>
                                                    <input type="text" name="passive[{{$value->id}}][total_order_end]" class="{{($key + 1 == count($passive)) ? 'hide' : ''}} form-control ng-input-calc ng-val-don number-format" value="{{number_format($value->total_order_end)}}">
                                                </td>
                                                <td>
                                                    <input type="text" name="passive[{{$value->id}}][radio_bonus]" class="form-control ng-input-calc ng-val-percent number-format" value="{{number_format($value->radio_bonus)}}">
                                                </td>
                                                <td class="text-right">{{number_format(($value->radio_bonus * $value->total_order_start) / 100)}}</td>
                                                <td>
                                                    <input type="text" class="form-control ng-input-res ng-val-tichluy text-right" readonly value="{{number_format($MoneyPass * $value->total_order_start) }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="passive[{{$value->id}}][total_radio_bonus]" class="form-control ng-input-res ng-val-tong text-right number-format" value="{{number_format($value->total_radio_bonus)}}" style="color:#8b0000; font-size:16px">
                                                </td>
                                                <td class="text-right">{{number_format((($value->radio_bonus * $value->total_order_start) / 100) + $MoneyPass * $value->total_order_start)}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <div class="alert alert-danger" style="font-size: 11px; border-radius: 0;">
                                        <i class="fa fa-bolt"></i> * Giá trị đơn càng lớn → thu nhập tuyệt đối càng cao, dù % giảm.
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- PHẦN 4: CHÊNH LỆCH LEADERS TẦNG SÂU -->
                        <div class="col-md-12">
                            <div class="ng-panel-luxury">
                                <div class="ng-panel-heading"><i class="fa fa-sitemap"></i> 5. Chênh lệch Leaders</div>
                                <div class="ng-panel-body">
                                    <table class="table table-bordered ng-table-custom" id="ng-table-leaders">
                                        <thead class="ng-brand-bg">
                                        <tr>
                                            <th class="text-center">Leaders Tuyến dưới</th>
                                            <th class="text-center">Mốc giá trị đơn Hàng</th>
                                            <th class="text-center">F Thụ hưởng</th>
                                            <th class="text-center">F1+ Thụ hưởng</th>
                                            <th class="text-center">F2+ Thụ hưởng</th>
                                            <th class="text-center">F3+ Thụ hưởng</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(!empty($accumulation_difference))
                                            @foreach($accumulation_difference as $keyF => $valueF)
                                                @foreach($valueF as $key => $value)
                                                    <tr class="ng-row-leader" data-level="F2">
                                                        @if(empty($key))
                                                            <td class="font-bold" rowspan="4">{{$keyF}}+</td>
                                                        @endif
                                                        <td><input type="text" name="difference[{{$keyF}}][{{$key}}][total_orders]" class="form-control ng-input-calc ng-ld-don number-format" value="{{number_format($value['total_orders'])}}"></td>
                                                        <td><input type="text" name="difference[{{$keyF}}][{{$key}}][F]" class="form-control ng-input-res ng-ld-f2 number-format" value="{{number_format($value['F'])}}"></td>
                                                        <td><input type="text" name="difference[{{$keyF}}][{{$key}}][F1]" class="form-control ng-input-res ng-ld-f2 number-format" value="{{number_format($value['F1'])}}"></td>
                                                        <td><input type="text" name="difference[{{$keyF}}][{{$key}}][F2]" class="form-control ng-input-res ng-ld-f1 number-format" value="{{number_format($value['F2'])}}"></td>
                                                        <td><input type="text" name="difference[{{$keyF}}][{{$key}}][F3]" class="form-control ng-input-res ng-ld-f2 number-format" value="{{number_format($value['F3'])}}"></td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            @php
                                                $listMoc = [
                                                    100000000,
                                                    150000000,
                                                    200000000,
                                                    300000000,
                                                ];

                                                $listF = [
    //                                                'F1',
                                                    'F2',
                                                    'F3',
                                                    'F4',
                                                ];
                                            @endphp
                                            @foreach($listF as $keyF => $valueF)
                                                @foreach($listMoc as $key => $value)
                                                    <tr class="ng-row-leader" data-level="F2">
                                                        @if(empty($key))
                                                            <td class="font-bold" rowspan="4">{{$valueF}}+</td>
                                                        @endif
                                                        <td><input type="text" name="difference[{{$valueF}}][{{$key}}][total_orders]" class="form-control ng-input-calc ng-ld-don" value="{{number_format($value)}}"></td>
                                                        <td><input type="text" name="difference[{{$valueF}}][{{$key}}][F]" class="form-control ng-input-res ng-ld-f2 number-format"></td>
                                                        <td><input type="text" name="difference[{{$valueF}}][{{$key}}][F1]" class="form-control ng-input-res ng-ld-f2 number-format"></td>
                                                        <td><input type="text" name="difference[{{$valueF}}][{{$key}}][F2]" class="form-control ng-input-res ng-ld-f1 number-format"></td>
                                                        <td><input type="text" name="difference[{{$valueF}}][{{$key}}][F3]" class="form-control ng-input-res ng-ld-f2 number-format"></td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                    <div class="alert alert-danger" style="font-size: 11px; border-radius: 0;">
                                        <i class="fa fa-bolt"></i>* Tuyến càng sâu (F3+, F4+) → vẫn có thu nhập nhưng giảm dần
                                    </div>

                                </div>
                            </div>
                        </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary waves-effect waves-light"
                                type="submit">{{lang('dt_save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    {{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">--}}
    {{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/theme/dracula.min.css">--}}
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>--}}
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/sql/sql.min.js"></script>--}}
    <script>
        var editors = {};

        $("#accumulationForm").validate({
            rules: {},
            messages: {},
            submitHandler: function (form) {
                var url = form.action;
                var form = $(form),
                    formData = new FormData(),
                    formParams = form.serializeArray();

                $.each(form.find('input[type="file"]'), function (i, tag) {
                    $.each($(tag)[0].files, function (i, file) {
                        formData.append(tag.name, file);
                    });
                });
                $.each(formParams, function (i, val) {
                    formData.append(val.name, val.value);
                });

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'JSON',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                })
                    .done(function (data) {
                        if (data.result) {
                            alert_float('success',data.message);
                            window.location.reload();
                        } else {
                            alert_float('error',data.message);
                        }
                    })
                    .fail(function (err) {
                        htmlError = '';
                        for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                        $(".show_error").html(htmlError);
                        alert_float('error',htmlError);
                    });
                return false;
            }
        });

        $("body").on('change', '.number-format', function () {
            formatNumBerKeyChange(this)
        });
    </script>
@endsection
