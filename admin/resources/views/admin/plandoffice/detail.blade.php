<style>
    :root {
        --home-primary: #005ae0;
        --home-primary-dark: #004ec4;
        --home-border: #e2e8f0;
    }

    #PlandofficeForm .form-group {
        margin-bottom: 16px;
    }

    #PlandofficeForm .form-group label {
        font-weight: 600;
        color: #475569;
        font-size: 13.5px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    #PlandofficeForm .form-group label .label-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        font-size: 13px;
    }

    #PlandofficeForm .form-group label .label-icon.icon-blue {
        background: linear-gradient(135deg, #e0f0ff 0%, #d0e8ff 100%);
        color: #3a94ef;
    }
    
    #PlandofficeForm .form-group label .label-icon.icon-purple {
        background: linear-gradient(135deg, #f0e6ff 0%, #e6d9ff 100%);
        color: #8e44ad;
    }

    #PlandofficeForm .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 12px;
        height: 38px;
        font-size: 13.5px;
        color: #334155;
        outline: none;
        transition: all 0.16s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        background: #ffffff;
    }

    #PlandofficeForm .form-control:focus {
        border-color: #005ae0;
        box-shadow: 0 0 0 3px rgba(0, 90, 224, 0.10);
    }

    #PlandofficeForm .select2-container--default .select2-selection--single {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        height: 38px;
        background-color: #ffffff;
        transition: all 0.16s ease;
    }

    #PlandofficeForm .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
        color: #334155;
        font-size: 13.5px;
    }

    #PlandofficeForm .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    #PlandofficeForm .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #005ae0;
        box-shadow: 0 0 0 3px rgba(0, 90, 224, 0.10);
    }

    #PlandofficeForm .kml-current-file {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        padding: 8px 14px;
        background: linear-gradient(135deg, #f0f7ff 0%, #e8f1fb 100%);
        border: 1px solid #d0e2f5;
        border-radius: 10px;
        font-size: 13px;
        color: #2c5282;
        transition: all 0.2s ease;
    }

    #PlandofficeForm .kml-current-file:hover {
        background: linear-gradient(135deg, #e0efff 0%, #d8eafb 100%);
        border-color: #b0cfe8;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(58, 148, 239, 0.1);
    }

    #PlandofficeForm .kml-current-file .kml-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, #3a94ef 0%, #2980d9 100%);
        border-radius: 7px;
        color: white;
        font-size: 12px;
    }

    #PlandofficeForm .kml-current-file a {
        color: #2c5282;
        font-weight: 600;
        text-decoration: none;
    }

    #PlandofficeForm input[type="file"] {
        padding: 8px 12px;
        background-color: #ffffff;
        border: 1.5px dashed #d0d5dd;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.25s ease;
        height: auto;
        width: 100%;
    }

    #PlandofficeForm input[type="file"]:hover {
        border-color: #005ae0;
        background-color: #f7fbff;
    }

    #PlandofficeForm .show_error {
        background: #fff5f5;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
        color: #c53030;
        display: none;
    }

    #PlandofficeForm .show_error:not(:empty) {
        display: block;
        margin-bottom: 15px;
    }

    #PlandofficeForm .form-helper {
        font-size: 12px;
        color: #64748b;
        margin-top: 5px;
    }

    /* Buttons systems */
    .mod-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        height: 42px !important;
        padding: 0 18px !important;
        border-radius: 12px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        transition: all .18s ease;
        cursor: pointer;
    }

    .mod-btn-primary {
        border: 0 !important;
        color: #ffffff !important;
        background: linear-gradient(135deg, var(--home-primary), #2563eb) !important;
        box-shadow: 0 10px 18px rgba(0, 90, 224, 0.22) !important;
    }

    .mod-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(0, 90, 224, 0.26) !important;
        color: #ffffff !important;
    }

    .mod-btn-secondary {
        border: 1px solid var(--home-border) !important;
        color: #475569 !important;
        background: #ffffff !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
    }

    .mod-btn-secondary:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    .modal-overlay {
        top: 0 !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #PlandofficeForm .modal-content {
        max-width: 480px !important;
    }
</style>
<form id="PlandofficeForm" action="admin/plandoffices/submit/{{$id}}" method="post" enctype="multipart/form-data" data-parsley-validate novalidate>
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
                <div class="planning-form">
                    <div class="show_error"></div>
                    <input type="hidden" name="id" value="{{!empty($plandoffice) ? $plandoffice['id'] : 0}}">

                    <div class="form-group">
                        <label for="name">
                            <span class="label-icon icon-blue"><i class="fa fa-map"></i></span>
                            Tên quy hoạch văn phòng <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" placeholder="Nhập tên quy hoạch..." value="{{$plandoffice->name ?? ''}}" required>
                    </div>

                    <div class="form-group">
                        <label for="province_id">
                            <span class="label-icon icon-blue"><i class="fa fa-globe"></i></span>
                            Tỉnh/Thành <span class="text-danger">*</span>
                        </label>
                        <select name="province_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Chọn Tỉnh/Thành --</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ (isset($plandoffice) && $plandoffice->province_id == $province->id) ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="kml_files">
                            <span class="label-icon icon-purple"><i class="fa fa-file-code-o"></i></span>
                            Tải lên các file KML <span class="text-danger" id="kml-required-star">{{ (empty($plandoffice) || empty($plandoffice->kml_file)) ? '*' : '' }}</span>
                        </label>
                        <input type="file" name="kml_files[]" accept=".kml" multiple {{ (empty($plandoffice) || empty($plandoffice->kml_file)) ? 'required' : '' }}>
                        <div class="form-helper">Chọn một hoặc nhiều file định dạng .kml (tối đa 10MB/file)</div>
                        @if(!empty($plandoffice) && !empty($plandoffice->kml_file))
                            @php
                                $kml_files = explode('||', $plandoffice->kml_file);
                            @endphp
                            <div class="kml-files-list" style="margin-top: 12px; display: flex; flex-direction: column; gap: 8px;">
                                @foreach($kml_files as $file)
                                    @php $file = trim($file); @endphp
                                    @if(!empty($file))
                                        <div class="kml-file-item" data-file="{{ $file }}" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                                                <i class="fa fa-file-text-o text-primary"></i>
                                                <a href="{{ asset('storage/' . $file) }}" target="_blank" style="font-weight: 600; color: #334155;">{{ basename($file) }}</a>
                                            </div>
                                            <button type="button" class="btn btn-xs btn-danger delete-kml-btn" data-file="{{ $file }}" style="border-radius: 4px; padding: 2px 6px;">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; padding: 16px 20px; border-top: 1px solid #eef2f7; background: #ffffff;">
                <button type="button" class="mod-btn mod-btn-secondary" data-dismiss="modal">Hủy bỏ</button>
                <button class="mod-btn mod-btn-primary" id="saveBtn">Lưu Lại</button>
            </div>
        </div>
    </div>
</form>

<script>
    // Initialize select2 if available
    if ($.fn.select2) {
        $('.select2').select2({
            dropdownParent: $('.modal-overlay')
        });
    }

    $("#PlandofficeForm").validate({
        rules: {
            name: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Vui lòng nhập tên quy hoạch văn phòng"
            }
        },
        submitHandler: function (form) {
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

            var $btn = $('#saveBtn');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang tải file...');

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
                        alert_float('success', data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        $btn.prop('disabled', false).html(originalText);
                        $(".show_error").html(data.message);
                        alert_float('error', data.message);
                    }
                })
                .fail(function (err) {
                    $btn.prop('disabled', false).html(originalText);
                    var htmlError = '';
                    if (err.responseJSON && err.responseJSON.errors) {
                        for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                    } else {
                        htmlError = 'Có lỗi xảy ra, vui lòng thử lại.';
                    }
                    $(".show_error").html(htmlError);
                    alert_float('error', htmlError);
                });
            return false;
        }
    });

    $(document).on('click', '.delete-kml-btn', function() {
        var $btn = $(this);
        var fileName = $btn.data('file');
        if (confirm('Bạn có chắc chắn muốn xóa file KML này không?')) {
            $btn.prop('disabled', true);
            $.ajax({
                url: 'admin/plandoffices/delete-kml-file',
                type: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                    file_name: fileName,
                    id: $('input[name="id"]').val()
                },
                dataType: 'json',
                success: function(res) {
                    if (res.result) {
                        alert_float('success', res.message);
                        $btn.closest('.kml-file-item').remove();
                        if ($('.kml-file-item').length === 0) {
                            $('input[name="kml_files[]"]').prop('required', true);
                            $('#kml-required-star').text('*');
                        }
                    } else {
                        alert_float('error', res.message);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alert_float('error', 'Có lỗi xảy ra khi xóa file.');
                    $btn.prop('disabled', false);
                }
            });
        }
    });
</script>
