<div class="title-statistic title_left">
    <span class="mleft5 mright5">
        {{lang('dt_info_car')}}
    </span>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="km_delivery_car">Số km hỗ trợ giao xe</label>
        <input type="text" name="km_delivery_car" id="km_delivery_car" value="{{get_option('km_delivery_car')}}" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="fee_km_delivery_car">Phí trên 1 km</label>
        <input type="text" name="fee_km_delivery_car" id="fee_km_delivery_car" value="{{formatMoney(get_option('fee_km_delivery_car'))}}" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="free_km_delivery_car">Số km miễn phí</label>
        <input type="text" name="free_km_delivery_car" id="free_km_delivery_car" value="{{get_option('free_km_delivery_car')}}" onchange="formatNumBerKeyChange(this)"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="km_delivery_car">Số km hỗ trợ giao xe tối đa</label>
        <input type="text" name="km_delivery_car_limit" id="km_delivery_car_limit" value="{{get_option('km_delivery_car_limit')}}" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="fee_km_delivery_car">Phí trên 1 km tối đa</label>
        <input type="text" name="fee_km_delivery_car_limit" id="fee_km_delivery_car_limit" value="{{formatMoney(get_option('fee_km_delivery_car_limit'))}}" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="free_km_delivery_car">Số km miễn phí tối đa</label>
        <input type="text" name="free_km_delivery_car_limit" id="free_km_delivery_car_limit" value="{{get_option('free_km_delivery_car_limit')}}" onchange="formatNumBerKeyChange(this)"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="range_km_delivery_car">Đơn vị tăng 1 lần (km hỗ trợ giao xe)</label>
        <input type="text" name="range_km_delivery_car" id="range_km_delivery_car" value="{{get_option('range_km_delivery_car')}}" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="range_fee_km_delivery_car">Đơn vị tăng 1 lần (phí trên 1 km)</label>
        <input type="text" name="range_fee_km_delivery_car" id="range_fee_km_delivery_car" value="{{formatMoney(get_option('range_fee_km_delivery_car'))}}" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="range_free_km_delivery_car">Đơn vị tăng 1 lần (số km miễn phí)</label>
        <input type="text" name="range_free_km_delivery_car" id="range_free_km_delivery_car" value="{{get_option('range_free_km_delivery_car')}}" onchange="formatNumBerKeyChange(this)"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="percent_discount">Giảm giá thuê xe tuần (%)</label>
        <input type="text" name="percent_discount" id="percent_discount" value="{{(get_option('percent_discount'))}}" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="limit_km_day">Số km tối đa trong 1 ngày</label>
        <input type="text" name="limit_km_day" id="limit_km_day" value="{{get_option('limit_km_day')}}" onchange="formatNumBerKeyChange(this)"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="clearfix"></div>
<div class="col-md-4">
    <div class="form-group">
        <label for="hour_start_car">Giờ nhận xe</label>
        <input type="text" name="hour_start_car" id="hour_start_car" value="{{get_option('hour_start_car')}}"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="hour_end_car">Giờ trả xe</label>
        <input type="text" name="hour_end_car" id="hour_end_car" value="{{get_option('hour_end_car')}}"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="hour_min_car">Thời gian tối thiểu để nhận xe khi thuê chung 1 ngày</label>
        <input type="text" name="hour_min_car" id="hour_min_car" value="{{get_option('hour_min_car')}}"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="point_car">Số điểm khi hoàn thành 1 chuyến</label>
        <input type="text" name="point_car" id="point_car" value="{{get_option('point_car')}}"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="percent_deposit">{{lang('dt_setting_car_deposit')}}</label>
        <input type="text" name="percent_deposit" id="percent_deposit" value="{{get_option('percent_deposit')}}"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="number_book_car">{{lang('dt_number_book_car')}}</label>
        <input type="text" name="number_book_car" id="number_book_car" value="{{get_option('number_book_car')}}"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="number_deposit_car">{{lang('dt_number_deposit_car')}}</label>
        <input type="text" name="number_deposit_car" id="number_deposit_car"
               value="{{get_option('number_deposit_car')}}" class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="note_mortgage">Ghi chú thế chấp</label>
        <textarea class="form-control note_mortgage"
                  name="note_mortgage">{{get_option('note_mortgage')}}</textarea></div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="noti_mortgage">Cảnh báo thế chấp</label>
        <textarea class="form-control noti_mortgage"
                  name="noti_mortgage">{{get_option('noti_mortgage')}}</textarea></div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="setting_price_car">{{lang('dt_setting_price_car')}}</label>
        <textarea class="editor setting_price_car"
                  name="setting_price_car">{{get_option('setting_price_car')}}</textarea></div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="setting_insurance_car">{{lang('dt_setting_insurance_car')}}</label>
        <textarea class="editor setting_insurance_car"
                  name="setting_insurance_car">{{get_option('setting_insurance_car')}}</textarea></div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="documentation_policy_car">{{lang('dt_documentation_policy_car')}}</label>
        <textarea class="editor documentation_policy_car"
                  name="documentation_policy_car">{{get_option('documentation_policy_car')}}</textarea>
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="mortgage_policy_car">{{lang('dt_mortgage_policy_car')}}</label>
        <textarea class="editor mortgage_policy_car"
                  name="mortgage_policy_car">{{get_option('mortgage_policy_car')}}</textarea>
    </div>
</div>
<div class="title-statistic title_left">
    <span class="mleft5 mright5">
        Chính sách hủy chuyến
    </span>
</div>
<div class="col-md-12">
    <div class="form-group">
        <label for="title_cancel_trip">Chính sách</label>
        <input type="text" name="title_cancel_trip" id="title_cancel_trip" value="{{get_option('title_cancel_trip')}}"
               class="form-control">
    </div>
    <table class="table-bordered table" id="table_category_cancel">
        <thead>
        <tr>
            <th style="width: 50px;text-align: center">STT</th>
            <th class="text-center">Tiêu chí</th>
            <th class="text-center">Khách thuê hủy chuyến</th>
            <th class="text-center">Chủ xe hủy chuyến</th>
            <th class="text-center">Phần trăm hoàn tiền khách thuê</th>
            <th class="text-center">Phần trăm đền tiền chủ xe</th>
            <th class="text-center">Đánh giá hệ thống</th>
        </tr>
        </thead>
        <tbody>
        @php
         $counter = 0;
        @endphp
        @forelse($dtCancelTrip as $key => $value)
            @php
                $value = (array)$value;
            @endphp
        <tr>
            <td class="text-center">{{(++$key)}}</td>
            <td>
                <input type="text" name="name[{{$counter}}]" id="name{{$counter}}"
                       value="{{ $value['name']}}"
                       class="name form-control">
                <input type="hidden" name="counter[]" value="{{$counter}}"
                       class="form-control counter">
                <input type="hidden" name="id[{{$counter}}]" value="{{$value['id']}}"
                       class="form-control">
            </td>
            <td>
                <input type="text" name="guest_cancel[{{$counter}}]"
                       value="{{$value['guest_cancel']}}" class="form-control guest_cancel">
            </td>
            <td>
                <input type="text" name="owen_cancel[{{$counter}}]"
                       value="{{$value['owen_cancel']}}" class="form-control owen_cancel">
            </td>
            <td style="width: 120px">
                <input type="text" name="percent_guest_cancel[{{$counter}}]"
                       value="{{$value['percent_guest_cancel']}}" class="form-control percent_guest_cancel" onchange="formatNumBerKeyChange(this)">
            </td>
            <td style="width: 120px">
                <input type="text" name="percent_owen_cancel[{{$counter}}]"
                       value="{{$value['percent_owen_cancel']}}" class="form-control percent_owen_cancel" onchange="formatNumBerKeyChange(this)">
            </td>
            <td style="width: 120px">
                <input type="text" name="number_star[{{$counter}}]"
                       value="{{$value['number_star']}}" class="form-control number_star" onchange="formatNumBerKeyChange(this)">
            </td>
        </tr>
            @php
                $counter ++;
            @endphp
        @empty
            <tr></tr>
        @endforelse
        </tbody>
    </table>
    <div class="form-group">
        <label for="note_cancel_trip">Ghi chú hủy chuyến</label>
        <textarea class="editor note_cancel_trip"
                  name="note_cancel_trip">{{get_option('note_cancel_trip')}}</textarea>
    </div>
    <div class="form-group">
        <label for="compensation_refund">Thủ tục hoàn tiền và đền cọc</label>
        <textarea class="editor compensation_refund"
                  name="compensation_refund">{{get_option('compensation_refund')}}</textarea>
    </div>
    <div class="form-group">
        <label for="document_deposit">Đặt cọc qua ứng dụng</label>
        <textarea class="editor document_deposit"
                  name="document_deposit">{{get_option('document_deposit')}}</textarea>
    </div>
    <div class="form-group">
        <label for="document_payment">Thanh toán khi nhân xe</label>
        <textarea class="editor document_payment"
                  name="document_payment">{{get_option('document_payment')}}</textarea>
    </div>
</div>
<script>
    var counter = 0;
    function addRow() {
        var tr = $('<tr></tr>');
        var td_delete = $('<td class="text-center"></td>');
        var td_title = $('<td></td>');
        var td_guest_cancel = $('<td></td>');
        var td_owen_cancel = $('<td></td>');
        var td_percent_guest_cancel = $('<td></td>');
        var td_percent_owen_cancel = $('<td></td>');
        var td_number_star = $('<td></td>');
        td_delete.append('');
        td_title.append('<input type="text" name="title[' + counter + ']" id="title_' + counter + '" value="" style="width: 100%;" class="title form-control"><input type="hidden" name="counter[]" value="' + counter + '">');
        td_guest_cancel.append('<input type="text" name="guest_cancel[' + counter + ']" id="guest_cancel' + counter + '" value="" style="width: 100%;" class="guest_cancel form-control">');
        td_owen_cancel.append('<input type="text" name="owen_cancel[' + counter + ']" id="owen_cancel' + counter + '" value="" style="width: 100%;" class="owen_cancel form-control">');
        td_percent_guest_cancel.append('<input type="text" onchange="formatNumBerKeyChange(this)" name="percent_guest_cancel[' + counter + ']" id="percent_guest_cancel' + counter + '" value="" style="width: 100%;" class="percent_guest_cancel form-control">');
        td_percent_owen_cancel.append('<input type="text" onchange="formatNumBerKeyChange(this)" name="percent_owen_cancel[' + counter + ']" id="percent_owen_cancel' + counter + '" value="" style="width: 100%;" class="percent_owen_cancel form-control">');
        td_number_star.append('<input type="text" onchange="formatNumBerKeyChange(this)" name="number_star[' + counter + ']" id="number_star' + counter + '" value="" style="width: 100%;" class="number_star form-control">');

        tr.append(td_delete);
        tr.append(td_title);
        tr.append(td_guest_cancel);
        tr.append(td_owen_cancel);
        tr.append(td_percent_guest_cancel);
        tr.append(td_percent_owen_cancel);
        tr.append(td_number_star);
        $('#table_category_cancel tbody').append(tr);
        counter++;
    }
</script>
