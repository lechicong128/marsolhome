<div class="title-statistic title_left">
    <span class="mleft5 mright5">
        {{lang('c_setting_car_talent')}}
    </span>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="km_delivery_car_talent">Đưa đón tận nơi trong vòng</label>
        <input type="text" name="km_delivery_car_talent" id="km_delivery_car_talent" value="{{get_option('km_delivery_car_talent')}}"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="fee_km_delivery_car_talent">Phí đưa đón 1 km</label>
        <input type="text" name="fee_km_delivery_car_talent" id="fee_km_delivery_car_talent" value="{{formatMoney(get_option('fee_km_delivery_car_talent'))}}"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="free_km_delivery_car_talent">Miễn phí km đưa đón</label>
        <input type="text" name="free_km_delivery_car_talent" id="free_km_delivery_car_talent" value="{{get_option('free_km_delivery_car_talent')}}"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="range_km_delivery_car_talent">Đơn vị tăng 1 lần (Đưa đón tận nơi trong vòng)</label>
        <input type="text" name="range_km_delivery_car_talent" id="range_km_delivery_car_talent" value="{{get_option('range_km_delivery_car_talent')}}"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="range_fee_km_delivery_car_talent">Đơn vị tăng 1 lần (Phí đưa đón 1 km)</label>
        <input type="text" name="range_fee_km_delivery_car_talent" id="range_fee_km_delivery_car_talent" value="{{formatMoney(get_option('range_fee_km_delivery_car_talent'))}}"
               class="form-control">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="range_free_km_delivery_car_talent">Đơn vị tăng 1 lần (Miễn phí km đưa đón)</label>
        <input type="text" name="range_free_km_delivery_car_talent" id="range_free_km_delivery_car_talent" value="{{get_option('range_free_km_delivery_car_talent')}}"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="limit_km_day_talent">Số km tối đa trong 1 ngày</label>
        <input type="text" name="limit_km_day_talent" id="limit_km_day_talent" value="{{get_option('limit_km_day_talent')}}"
               class="form-control" onchange="formatNumBerKeyChange(this)">
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="setting_number_hour_day_car_talent">Số giờ tối thiểu thuê 1 ngày</label>
        <input class="setting_number_hour_day_car_talent form-control"
                  name="setting_number_hour_day_car_talent" value="{{get_option('setting_number_hour_day_car_talent')}}"></div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="setting_hour_night_car_talent">Số giờ tính đêm</label>
        <input class="setting_hour_night_car_talent form-control"
               name="setting_hour_night_car_talent" value="{{get_option('setting_hour_night_car_talent')}}"></div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="setting_price_car_talent">{{lang('dt_setting_price_car')}}</label>
        <textarea class="editor setting_price_car_talent"
                  name="setting_price_car_talent">{{get_option('setting_price_car_talent')}}</textarea></div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="setting_insurance_car_talent">{{lang('dt_setting_insurance_car')}}</label>
        <textarea class="editor setting_insurance_car_talent"
                  name="setting_insurance_car_talent">{{get_option('setting_insurance_car_talent')}}</textarea></div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="total_km_car_talent">Số km được đi</label>
        <textarea class="editor total_km_car_talent"
                  name="total_km_car_talent">{{get_option('total_km_car_talent')}}</textarea></div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label for="setting_service_car_talent">Phí dịch vụ</label>
        <textarea class="editor setting_service_car_talent"
                  name="setting_service_car_talent">{{get_option('setting_service_car_talent')}}</textarea></div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="setting_shuttle_car_talent">Phí đưa đón</label>
        <textarea class="editor setting_shuttle_car_talent"
                  name="setting_shuttle_car_talent">{{get_option('setting_shuttle_car_talent')}}</textarea></div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="setting_interprovincial_travel">Di chuyển liên tỉnh, trả khách về lại điểm đón</label>
        <textarea class="editor setting_interprovincial_travel"
                  name="setting_interprovincial_travel">{{get_option('setting_interprovincial_travel')}}</textarea></div>
</div>
