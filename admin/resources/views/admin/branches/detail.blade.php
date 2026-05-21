<form id="BranchForm" action="admin/branch/submit/{{!empty($id) ? $id : '0'}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="show_error" style="color: red"></div>

                        <div class="form-group">
                            <label for="icon">Hình QR Fanpage</label>
                            <input type="file" name="icon" id="icon" class="form-control" accept="image/*">
                            @if(!empty($branch) && $branch->icon)
                                <div class="mt-2" style="margin-top: 10px;">
                                    <img src="{{ asset('storage/' . $branch->icon) }}" style="max-height: 50px;" alt="icon">
                                </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="name">Tên chi nhánh <i class="text-danger required">*</i></label>
                            <input type="text" name="name" id="name" class="form-control"
                                   placeholder="Nhập tên chi nhánh"
                                   value="{{!empty($branch) ? $branch->name : ''}}">
                        </div>

                        <div class="form-group">
                            <label for="phone">Số điện thoại <i class="text-danger required">*</i></label>
                            <input type="text" name="phone" id="phone" class="form-control"
                                   placeholder="Nhập số điện thoại"
                                   value="{{!empty($branch) ? $branch->phone : ''}}">
                        </div>

                        <div class="form-group">
                            <label for="address">Địa chỉ <i class="text-danger required">*</i></label>
                            <input type="text" name="address" id="address" class="form-control"
                                   placeholder="Nhập địa chỉ chi nhánh"
                                   value="{{!empty($branch) ? $branch->address : ''}}">
                        </div>

                        <div class="form-group">
                            <label for="map_link">Link Google Map</label>
                            <input type="text" name="map_link" id="map_link" class="form-control"
                                   placeholder="https://maps.google.com/..."
                                   value="{{!empty($branch) ? $branch->map_link : ''}}">
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary waves-effect waves-light"
                        type="submit">{{lang('dt_save')}}</button>
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">{{lang('dt_close')}}</button>
            </div>
        </div>
    </div>
</form>
<script>
    $("#BranchForm").validate({
        rules: {
            'name'    : "required",
            'phone'   : "required",
            'address' : "required",
        },
        messages: {
            name    : "Vui lòng nhập tên chi nhánh",
            phone   : "Vui lòng nhập số điện thoại",
            address : "Vui lòng nhập địa chỉ",
        },
        submitHandler: function (form) {
            var url = form.action;
            var formJQ = $(form),
                formData = new FormData(formJQ[0]);

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
                        oTable.draw();
                        $('.modal-dialog .close').trigger('click');
                        alert_float('success', data.message);
                    } else {
                        $(".show_error").html(data.message);
                        alert_float('error', data.message);
                    }
                })
                .fail(function (err) {
                    htmlError = '';
                    if (err.responseJSON && err.responseJSON.errors) {
                        for (var [el, message] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                    }
                    $(".show_error").html(htmlError);
                    alert_float('error', htmlError);
                });
            return false;
        }
    });
</script>
