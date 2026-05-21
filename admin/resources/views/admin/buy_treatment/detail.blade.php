@extends('admin.layouts.index')
@section('content')
<style>
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #495057;
        font-size: 14px;
    }
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #3a94ef;
        outline: none;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">Thẻ liệu trình spa</h4>
        <ol class="breadcrumb">
            <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
            <li><a href="admin/buy_treatment/list">Danh sách thẻ liệu trình</a></li>
            <li class="active">{{$title ?? ''}}</li>
        </ol>
    </div>
</div>

<div class="row">
    <form action="admin/buy_treatment/submit/{{!empty($id) ? $id : 0}}" method="post"
          id="BuyTreatmentForm" data-parsley-validate novalidate>
        {{csrf_field()}}
        <div class="col-lg-12">
            <div class="card-box">
                <div class="row">

                    <!-- Cột trái -->
                    <div class="col-md-4">

                        <div class="form-group">
                            <label for="id_client">Khách hàng <i class="text-danger">*</i></label>
                            <select id="id_client" name="id_client" class="form-control select2" style="width:100%" data-placeholder="-- Tìm kiếm tên / SĐT --" required>
                                @if(!empty($purchase) && !empty($purchase->customer_name))
                                    <option value="{{$purchase->id_client ?? ''}}" selected>
                                        {{$purchase->customer_name}} ({{$purchase->customer_phone}})
                                    </option>
                                @else
                                    <option></option>
                                @endif
                            </select>
                            {{-- Hidden fields lưu thông tin thực sự (được fill qua select2:select) --}}
                            <input type="hidden" id="customer_name" name="customer_name"
                                   value="{{!empty($purchase) ? $purchase->customer_name : ''}}">
                            <input type="hidden" id="customer_phone" name="customer_phone"
                                   value="{{!empty($purchase) ? $purchase->customer_phone : ''}}">
                        </div>

                        {{-- Chi nhánh: ẩn, mặc định 0 = áp dụng tất cả --}}
                        <input type="hidden" name="id_branch" value="{{!empty($purchase) ? $purchase->id_branch : 0}}">

                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select name="status" id="status" class="form-control">
                                <option value="active"    {{(!empty($purchase) && $purchase->status == 'active')    ? 'selected' : ''}}>Đang dùng</option>
                                <option value="completed" {{(!empty($purchase) && $purchase->status == 'completed') ? 'selected' : ''}}>Đã hoàn thành</option>
                                <option value="cancelled" {{(!empty($purchase) && $purchase->status == 'cancelled') ? 'selected' : ''}}>Đã huỷ</option>
                            </select>
                        </div>

                    </div>

                    <!-- Cột phải -->
                    <div class="col-md-8">

                        <div class="form-group">
                            <label for="treatment_name">Tên thẻ liệu trình gói mua <i class="text-danger">*</i></label>
                            <input type="text" id="treatment_name" name="treatment_name" class="form-control" required autocomplete="off"
                                   placeholder="Vd: Gói 10 buổi trị mụn chuyên sâu"
                                   value="{{!empty($purchase) ? $purchase->treatment_name : ''}}">
                        </div>

                        <div class="form-group">
                            <label for="id_category">Gắn với Danh mục Dịch vụ</label>
                            <select name="id_category" id="id_category" class="form-control select2" style="width:100%">
                                <option value="0" {{(empty($purchase) || $purchase->id_category == 0) ? 'selected' : ''}}>-- Khác / Áp dụng tất cả danh mục --</option>
                                @foreach($categories as $cat)
                                    <option value="{{$cat->id}}"
                                        {{(!empty($purchase) && $purchase->id_category == $cat->id) ? 'selected' : ''}}>
                                        {{$cat->name}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_sessions">Số buổi <i class="text-danger">*</i></label>
                                    <input type="number" id="total_sessions" name="total_sessions"
                                           min="1" step="1" required
                                           value="{{!empty($purchase) ? $purchase->total_sessions : ''}}"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="price">Giá trị liệu trình (VNĐ) <i class="text-danger">*</i></label>
                                    <input type="text" id="price" name="price"
                                           value="{{!empty($purchase) ? number_format($purchase->price ?? 0) : '0'}}"
                                           class="form-control text-right" required
                                           onkeyup="formatNumBerKeyChange(this)">
                                </div>
                            </div>
                            @if(!empty($purchase))
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Số buổi đã dùng</label>
                                    <input type="text" class="form-control" readonly
                                           value="{{$purchase->used_sessions ?? 0}} / {{$purchase->total_sessions ?? 0}} buổi">
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="note">Ghi chú</label>
                            <textarea name="note" id="note" rows="3" class="form-control">{{!empty($purchase) ? $purchase->note : ''}}</textarea>
                        </div>

                    </div>

                </div><!-- /row -->
            </div><!-- /card-box -->

            <div class="form-group text-right m-b-0 m-t-10" style="padding: 0 15px;">
                <button class="btn btn-primary waves-effect waves-light" type="submit">
                    {{lang('dt_save')}}
                </button>
                <a href="admin/buy_treatment/list" class="btn btn-default waves-effect waves-light m-l-5">
                    {{lang('dt_cancel')}}
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        // Khởi tạo Select2 cho khách hàng (dùng helper chuẩn của project)
        searchAjaxSelect2('#id_client', 'admin/category/searchCustomer');

        // Sau khi khởi tạo xong, gán luôn event listener để lấy tên và số điện thoại đưa vào hidden layer
        $('#id_client').on('select2:select', function (e) {
            var d = e.params.data;
            // Text trả về có dạng "Nguyễn Văn A (0987654321)"
            var parts = d.text.split('(');
            var name  = parts[0].trim();
            var phone = parts[1] ? parts[1].replace(')', '').trim() : (d.phone || '');
            
            $('#customer_name').val(name);
            $('#customer_phone').val(phone);
        });

        $('#id_client').on('select2:clear', function () {
            $('#customer_name').val('');
            $('#customer_phone').val('');
        });
    });



    // Validate & Submit
    $("#BuyTreatmentForm").validate({
        ignore: [], // Bắt lỗi cả hidden và select2
        rules: {
            id_client:      { required: true },
            treatment_name: { required: true },
            total_sessions: { required: true, min: 1 },
            price:          { required: true },
        },
        messages: {
            id_client:      { required: 'Bạn chưa chọn khách hàng' },
            treatment_name: { required: 'Bạn chưa nhập tên liệu trình' },
            total_sessions: { required: 'Bạn chưa nhập số buổi', min: 'Số buổi phải ≥ 1' },
            price:          { required: 'Bạn chưa nhập giá trị' },
        },
        submitHandler: function (form) {
            var url    = form.action;
            var params = $(form).serializeArray();
            $.post(url, params, function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                    setTimeout(function () {
                        window.location.href = 'admin/buy_treatment/list';
                    }, 800);
                } else {
                    var msg = Array.isArray(data.message) ? data.message.join('<br>') : data.message;
                    alert_float('error', msg);
                }
            }, 'json');
            return false;
        }
    });
</script>
@endsection
