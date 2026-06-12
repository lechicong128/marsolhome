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
        color: white;
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
<form id="UtilityForm" action="admin/utilities/submit/{{$id}}" method="post" enctype="multipart/form-data" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{{ $title }}</h2>
                <button type="button" class="close" data-dismiss="modal" title="Đóng">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 1L1 13M1 1L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="id" value="{{!empty($utility) ? $utility['id'] : 0}}" >
                        <div class="form-group">
                            <label for="name">{{lang('dt_name_utility')}}</label>
                            <input type="text" name="name" class="form-control name" value="{{$utility->name ?? ''}}" required>
                        </div>
                        <div class="form-group">
                            <label for="input_type">{{lang('dt_input_type')}}</label>
                            <select name="input_type" id="input_type" class="form-control" required>
                                <option value="number" {{ (!empty($utility) && $utility->input_type == 'number') ? 'selected' : '' }}>Nhập số</option>
                                <option value="text" {{ (!empty($utility) && $utility->input_type == 'text') ? 'selected' : '' }}>Nhập chữ</option>
                                <option value="select" {{ (!empty($utility) && $utility->input_type == 'select') ? 'selected' : '' }}>Hộp chọn (Select)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="unit">Đơn vị đo (Ví dụ: m, m², phòng...)</label>
                            <input type="text" name="unit" class="form-control" placeholder="Nhập đơn vị nếu có (ví dụ: m, m2...)" value="{{$utility->unit ?? ''}}">
                        </div>
                        <div class="form-group" id="choices_group" style="display: {{ (!empty($utility) && $utility->input_type == 'select') ? 'block' : 'none' }};">
                            <label class="control-label">Các tùy chọn</label>
                            <div id="utility-options-list" style="margin-bottom: 10px;">
                                @if(!empty($utility) && $utility->input_type == 'select' && count($utility->options) > 0)
                                    @foreach($utility->options as $option)
                                        <div class="option-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                                            <input type="hidden" name="option_ids[]" value="{{ $option->id }}">
                                            <input type="text" name="option_names[]" class="form-control option-name-input" value="{{ $option->name }}" placeholder="Nhập tên tùy chọn" required>
                                            <button type="button" class="btn btn-danger btn-remove-option" style="padding: 6px 12px;"><i class="fa fa-trash"></i></button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="option-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                                        <input type="hidden" name="option_ids[]" value="0">
                                        <input type="text" name="option_names[]" class="form-control option-name-input" placeholder="Nhập tên tùy chọn" required>
                                        <button type="button" class="btn btn-danger btn-remove-option" style="padding: 6px 12px;"><i class="fa fa-trash"></i></button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-info" id="btn-add-option">
                                <i class="fa fa-plus"></i> Thêm tùy chọn mới
                            </button>
                        </div>
                        <div class="form-group">
                            <label for="transaction_type">Hình thức áp dụng</label>
                            <select name="transaction_type" class="form-control" required>
                                <option value="3" {{ (isset($utility) && $utility->transaction_type == 3) ? 'selected' : '' }}>Cả Bán và Cho thuê</option>
                                <option value="1" {{ (isset($utility) && $utility->transaction_type == 1) ? 'selected' : '' }}>Chỉ dành cho Bán</option>
                                <option value="2" {{ (isset($utility) && $utility->transaction_type == 2) ? 'selected' : '' }}>Chỉ dành cho Cho thuê</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="icon">Icon tiện ích</label>
                            <input type="file" name="icon" id="icon" class="filestyle image" data-buttonbefore="true">
                            @if(!empty($utility) && $utility->icon != null)
                                <div class="m-t-10">
                                    {!! loadImage(asset('storage/' . $utility->icon), '100px', 'img-rounded', $utility->icon, false, '50px') !!}
                                </div>
                            @endif
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
    $('#input_type').on('change', function() {
        if ($(this).val() === 'select') {
            $('#choices_group').show();
        } else {
            $('#choices_group').hide();
        }
    });

    $('#btn-add-option').on('click', function() {
        var row = $('<div class="option-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">' +
            '<input type="hidden" name="option_ids[]" value="0">' +
            '<input type="text" name="option_names[]" class="form-control option-name-input" placeholder="Nhập tên tùy chọn" required>' +
            '<button type="button" class="btn btn-danger btn-remove-option" style="padding: 6px 12px;"><i class="fa fa-trash"></i></button>' +
            '</div>');
        $('#utility-options-list').append(row);
    });

    $(document).on('click', '.btn-remove-option', function() {
        if ($('#utility-options-list .option-row').length > 1) {
            $(this).closest('.option-row').remove();
        } else {
            alert('Phải có ít nhất một tùy chọn');
        }
    });

    $("#UtilityForm").validate({
        rules: {
            name: {
                required: true
            },
            input_type: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Vui lòng nhập tên tiện ích"
            },
            input_type: {
                required: "Vui lòng chọn loại nhập liệu"
            }
        },
        submitHandler: function (form) {
            if ($('#input_type').val() === 'select') {
                var hasEmpty = false;
                $('.option-name-input').each(function() {
                    if ($(this).val().trim() === '') {
                        hasEmpty = true;
                        $(this).css('border-color', 'red');
                    } else {
                        $(this).css('border-color', '');
                    }
                });
                if (hasEmpty) {
                    alert_float('error', 'Vui lòng nhập đầy đủ tên cho các tùy chọn');
                    return false;
                }
            }

            var url = form.action;
            var formObj = $(form),
                formData = new FormData(),
                formParams = formObj.serializeArray();

            $.each(formObj.find('input[type="file"]'), function (i, tag) {
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
                        oTable.draw('page');
                        $('.modal-overlay .close').trigger('click');
                        alert_float('success', data.message);
                    } else {
                        $(".show_error").html(data.message);
                        alert_float('error', data.message);
                    }
                })
                .fail(function (err) {
                    var htmlError = '';
                    if (err.responseJSON && err.responseJSON.errors) {
                        for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                    } else {
                        htmlError = 'Đã xảy ra lỗi hệ thống';
                    }
                    $(".show_error").html(htmlError);
                    alert_float('error', htmlError);
                });
            return false;
        }
    });
</script>
