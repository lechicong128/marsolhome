<form id="CodeLeaderForm" action="admin/code_leader/submit/{{$id}}" method="post" data-parsley-validate novalidate>
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
                            <label>Phương thức tạo <span class="text-danger">*</span></label>
                            <div>
                                 <label class="radio-inline">
                                    <input type="radio" name="create_mode" value="random" checked> Sinh ngẫu nhiên hàng loạt
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="create_mode" value="manual"> Tự nhập mã (1 mã)
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="wrap-code-input" style="display: none;">
                            <label for="code">Mã Leader <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="Nhập mã (VD: RRV7SM)">
                        </div>

                        <div class="form-group" id="wrap-quantity-input">
                            <label for="quantity">Số lượng mã cần tạo <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="100" placeholder="Nhập số lượng mã cần tạo">
                            <small class="text-muted">Mã sinh ngẫu nhiên dạng 6 ký tự (VD: RRV7SM).</small>
                        </div>

                        <div class="form-group">
                            <label for="note">Ghi chú</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú..."></textarea>
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
    $('input[name="create_mode"]').change(function() {
        if ($(this).val() === 'manual') {
            $('#wrap-code-input').show();
            $('#wrap-quantity-input').hide();
        } else {
            $('#wrap-code-input').hide();
            $('#wrap-quantity-input').show();
        }
    });

    $("#CodeLeaderForm").validate({
        rules: {
            quantity: {
                required: function() { return $('input[name="create_mode"]:checked').val() === 'random'; },
                min: 1,
                max: 100,
            },
            code: {
                required: function() { return $('input[name="create_mode"]:checked').val() === 'manual'; },
            }
        },
        messages: {
            quantity: {
                required: "{{lang('dt_required')}}",
                min: "Số lượng tối thiểu là 1",
                max: "Số lượng tối đa là 100",
            },
            code: {
                required: "{{lang('dt_required')}}"
            }
        },
        submitHandler: function (form) {
            var url = form.action;
            var form = $(form),
                formData = new FormData(),
                formParams = form.serializeArray();

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
                    } else {
                        htmlError = 'Có lỗi xảy ra';
                    }
                    $(".show_error").html(htmlError);
                    alert_float('error', htmlError);
                });
            return false;
        }
    });
</script>
