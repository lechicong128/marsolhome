<style>
    .tab-btn {
        flex: 1;
        padding: 10px 10px;
        border: none;
        background: transparent;
        cursor: pointer;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        color: #6c757d;
    }

    .tab-btn.active {
        /*background: #3a94ef;*/
        color: white;
        /*box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);*/
        margin-right: 5px;
        border: 1;
    }
    .nav.nav-tabs > li.tab-btn.active > a {
        background-color: #3a94ef;
        color: white !important;
        border: 0;
        border-radius: 10px;
    }

    .tab-btn:hover:not(.active) {
        background: rgba(79, 172, 254, 0.1);
        color: #4facfe;
    }
    .nav.nav-tabs > li > a, .nav.tabs-vertical > li > a{
        line-height: 25px;
    }
</style>
<form id="PaymentModeForm" action="admin/payment_mode/submit/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
               <h2 class="modal-title">{{ $title }}</h2>
                <button type="button" class="close" data-dismiss="modal" title="Đóng">
                    <!-- Sử dụng SVG cho nút Close sắc nét -->
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 1L1 13M1 1L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="id" value="{{!empty($paymentMode) ? $paymentMode['id'] : 0}}" >
                        <div class="show_error" style="color: red"></div>
                        <div class="form-group">
                            <label for="image">{{lang('dt_image')}}</label>
                            <input type="file" name="image[]" multiple id="image" class="filestyle image"
                                   data-buttonbefore="true">
                            @if(!empty($paymentMode) && $paymentMode->image != null)
                                <input type="hidden" name="image_old" id="image_old"
                                       class="image_old"
                                       data-buttonbefore="true" value="{{!empty($paymentMode) ? $paymentMode->image : ''}}">
                                {!! loadImage(asset('storage/'.$paymentMode->image)) !!}
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('dt_name_payment_mode')}}</label>
                            <input type="text" name="name[<?= $language_default->code ?>]" class="form-control name" value="{{$paymentMode->name ?? ''}}">
                        </div>
                        <div class="form-group">
                            <label for="note">{{lang('dt_note')}}</label>
                            <textarea name="note[<?= $language_default->code ?>]" class="form-control note">{{$paymentMode['note'] ?? ''}}</textarea>
                        </div>
                        <div class="form-group hide">
                            <label for="balance">Số dư đầu kỳ</label>
                            <input type="text" name="balance" class="form-control balance" onchange="formatNumBerKeyChange(this)" value="{{!empty($paymentMode) ? formatMoney($paymentMode['balance']) : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('dt_type')}}</label>
                            <select class="type select2 form-control" name="type" id="type">
                                <option value="1" {{!empty($paymentMode) && $paymentMode['type'] == 1 ? 'selected' : ''}}>{{lang('dt_cash')}}</option>
                                <option value="2" {{!empty($paymentMode) && $paymentMode['type'] == 2 ? 'selected' : ''}}>{{lang('dt_bank_name')}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            <div class="modal-footer">
                <button class="btn btn-default" id="saveBtn">Lưu Lại</button>
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy bỏ</button>
            </div>
        </div>
    </div>
</form>
<script>
    $("#type").select2();
    $("#PaymentModeForm").validate({
        rules: {
        },
        messages: {
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
                        $('.modal-overlay .close').trigger('click');
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
