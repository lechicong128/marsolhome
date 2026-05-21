<div class="title-statistic title_left">
    <span class="mleft5 mright5">
        {{lang('c_setting_driver')}}
    </span>
</div>
<div class="col-md-12">
    <label>Thiết lập thời gian tăng giá</label>
    <table class="table-bordered table" id="table_setup_price">
        <thead>
        <tr>
            <th style="width: 50px;text-align: center">
                <button type="button" class="btn btn-xs btn-info btn-icon" onclick="addRow()"><i class="fa fa-plus" aria-hidden="true"></i></button>
            </th>
            <th class="text-center">Thời gian bắt đầu</th>
            <th class="text-center">Thời gian kết thúc</th>
            <th class="text-center" style="width: 150px">Phần trăm tăng giá</th>
            <th class="text-center" style="width: 150px">Loại</th>
            <th class="text-center" style="width: 120px">Trạng thái</th>
            <th class="text-center" style="width: 100px"></th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    <label class="hide">Chính sách hủy chuyến</label>
    <table class="table-bordered table hide" id="table_category_cancel">
        <thead>
        <tr>
            <th style="width: 50px;text-align: center">STT</th>
            <th class="text-center" style="width: 500px">Tiêu chí</th>
            <th class="text-center">Người đặt xe hủy chuyến</th>
            <th class="text-center">Tài xế hủy chuyến</th>
            <th class="text-center">Phần trăm hoàn tiền người đặt xe</th>
            <th class="text-center">Phần trăm đền tiền tài xế</th>
        </tr>
        </thead>
        <tbody>
        @php
            $counterDriver = 0;
        @endphp
        @forelse($dtCancelTripDriver as $key => $value)
            @php
                $value = (array)$value;
            @endphp
            <tr>
                <td class="text-center">{{(++$key)}}</td>
                <td>
                    <input type="text" name="name_trip_driver[{{$counterDriver}}]" id="name_trip_driver{{$counterDriver}}"
                           value="{{ $value['name']}}"
                           class="name form-control">
                    <input type="hidden" name="counter_driver[]" value="{{$counterDriver}}"
                           class="form-control counter_driver">
                    <input type="hidden" name="id_trip_driver[{{$counterDriver}}]" value="{{$value['id']}}"
                           class="form-control">
                </td>
                <td>
                    <input type="text" name="guest_cancel_driver[{{$counterDriver}}]"
                           value="{{$value['guest_cancel']}}" class="form-control guest_cancel_driver">
                </td>
                <td>
                    <input type="text" name="owen_cancel_driver[{{$counterDriver}}]"
                           value="{{$value['owen_cancel']}}" class="form-control owen_cancel_driver">
                </td>
                <td style="width: 120px">
                    <input type="text" name="percent_guest_cancel_driver[{{$counterDriver}}]"
                           value="{{$value['percent_guest_cancel']}}" class="form-control percent_guest_cancel_driver" onchange="formatNumBerKeyChange(this)">
                </td>
                <td style="width: 120px">
                    <input type="text" name="percent_owen_cancel_driver[{{$counterDriver}}]"
                           value="{{$value['percent_owen_cancel']}}" class="form-control percent_owen_cancel_driver" onchange="formatNumBerKeyChange(this)">
                </td>
            </tr>
            @php
                $counterDriver ++;
            @endphp
        @empty
            <tr></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="col-md-12">
    <div class="form-group">
        <label for="hour_remind_driver_province">Số giờ nhắc chuyến đi tỉnh (tiếng)</label>
        <input type="text" name="hour_remind_driver_province" id="hour_remind_driver_province" value="{{get_option('hour_remind_driver_province')}}" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
<div class="col-md-12">
    <div class="form-group">
        <label for="point_driver">Số điểm khi hoàn thành 1 chuyến</label>
        <input type="text" name="point_driver" id="point_driver" value="{{get_option('point_driver')}}"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-12">
    <div class="form-group">
        <label for="time_cancel_trip">Thời gian tự động hủy chuyến (giây)</label>
        <input type="text" name="time_cancel_trip" id="time_cancel_trip" value="{{get_option('time_cancel_trip')}}" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
<div class="col-md-12">
    <div class="form-group">
        <label for="percent_business">Phần trăm chiết khấu doanh nghiệp</label>
        <input type="text" name="percent_business" id="percent_business" value="{{get_option('percent_business')}}" min="0" max="100" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
<div class="col-md-12 hide">
    <div class="form-group">
        <label for="number_auto_accpet">Số lần bật nhận chuyến tự động trong 1 ngày</label>
        <input type="text" name="number_auto_accpet" id="number_auto_accpet" value="{{get_option('number_auto_accpet')}}" onchange="formatNumBerKeyChange(this)"
               class="form-control">
    </div>
</div>
@section('script')
<script>
    var counter = 0;
    var dtCategoryCar = <?= !empty($dtCategoryCar) ? json_encode($dtCategoryCar) : [] ?>;

    function getOptionCategoryCar(){
        var option = `<option></option>`;
        if(dtCategoryCar.length > 0){
            $.each(dtCategoryCar,function (k,v){
                option += `<option value="${v.id}">${v.name}</option>`;
            });
        }
        return option;
    }
    function addRow() {
        var tr = $('<tr></tr>');
        var td_delete = $('<td class="text-center"></td>');
        var td_stt = $('<td></td>');
        var td_hour_start = $('<td></td>');
        var td_hour_end = $('<td></td>');
        var td_coefficient = $('<td></td>');
        var td_type = $('<td></td>');
        var td_status = $('<td></td>');
        td_delete.append('<div class="text-center"><i class="fa fa-remove btn btn-danger remove-row"></i></div>');
        td_stt.append('<div class="text-center stt"></div>');
        td_hour_start.append('<input type="time" name="hour_start[' + counter + ']" id="hour_start' + counter + '" value="" style="width: 100%;" class="hour_start form-control"><input type="hidden" name="counter_setup[]" value="' + counter + '">');
        td_hour_end.append('<input type="time" name="hour_end[' + counter + ']" id="hour_end' + counter + '" value="" style="width: 100%;" class="hour_end form-control">');
        td_coefficient.append('<input type="text" name="coefficient[' + counter + ']" id="coefficient' + counter + '" onchange="formatNumBerKeyChange(this)" value="1" style="width: 100%;" class="coefficient form-control">');
        td_type.append(`<select class="category_car_id select2 form-control" required name="category_car_id[${counter}]">
            ${getOptionCategoryCar()}
        </select>`);


        tr.append(td_stt);
        tr.append(td_hour_start);
        tr.append(td_hour_end);
        tr.append(td_coefficient);
        tr.append(td_type);
        tr.append(td_status);
        tr.append(td_delete);
        $('#table_setup_price tbody').append(tr);
        counter++;
        $(".category_car_id").select2();
        totalSetup();
    }
    $(document).on('click', '.remove-row', function(event) {
        event.preventDefault();
        tr = $(this).closest('tr');
        tr.remove();
        totalSetup();
    })
    function totalSetup(){
        tb = '#table_setup_price tbody tr';
        var n = $(tb).length;
        var stt = 0;
        for (ii = 0; ii < n; ii++) {
            stt++;
            element = $(tb)[ii];
            $(element).find('.stt').html(stt);
        }
    }
    let loadDataSetup =  function(){
        $.ajax({
            url: 'admin/settings/loadDataSetup',
            type: 'POST',
            dataType: 'json',
            cache: false,
        })
            .done(function (data) {
              $("#table_setup_price").find('tbody').html(data.html);
                $(".category_car_id").select2();
                counter = data.counter;
            })
            .fail(function () {

            });
        return false;
    }
    loadDataSetup();
</script>
@endsection
