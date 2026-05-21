<form id="BonusPaymentForm" action="admin/bonus_payment/submit/{{$id}}" method="post" data-parsley-validate novalidate>
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
                            <label for="name">{{lang('dt_date_new')}}</label>
                            <input type="text" name="date_new" class="form-control date_new datetimepicker" value="{{date('d/m/Y H:i')}}">
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('Mã phiếu')}}</label>
                            <input type="text" name="reference_no" class="form-control reference_no" readonly value="{{$reference_no}}">
                        </div>
                        <div class="form-group">
                            <label for="customer_id">{{lang('Leader')}}</label>
                            <select class="customer_id select2" id="customer_id"
                                    data-placeholder="Chọn ..." name="customer_id">
                                <option></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="payment_mode_id">{{lang('dt_payment_mode')}}</label>
                            <select class="payment_mode_id select2" id="payment_mode_id"
                                    data-placeholder="Chọn ..." name="payment_mode_id">
                                <option></option>
                                @foreach($dtPaymentMode as $paymentMode)
                                    <option
                                        value="{{$paymentMode->id}}">{{$paymentMode->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('dt_total')}}</label>
                            <input type="text" name="total" class="form-control total" onkeyup="formatNumBerKeyChange(this)" value="0">
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('dt_note')}}</label>
                            <textarea name="note" class="form-control note"></textarea>
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
    searchAjaxSelect2('#customer_id','admin/category/searchCustomer')
    $("#payment_mode_id").select2();
    $("#BonusPaymentForm").validate({
        rules: {
            date_new: {
                required: true,
            },
            payment_mode_id: {
                required: true,
            },
            customer_id: {
                required: true,
            },
            total: {
                required: true,
            },
        },
        messages: {
            date_new: {
                required: "{{lang('dt_required')}}",
            },
            payment_mode_id: {
                required: "{{lang('dt_required')}}",
            },
            customer_id: {
                required: "{{lang('dt_required')}}",
            },
            total: {
                required: "{{lang('dt_required')}}",
            },
        },
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
                        oTable.draw();
                        $('.modal-dialog .close').trigger('click');
                        alert_float('success',data.message);
                    } else {
                        $(".show_error").html(data.message);
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
</script>
