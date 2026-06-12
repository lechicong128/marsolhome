<style>
    :root {
        --home-primary: #005ae0;
        --home-primary-dark: #004ec4;
        --home-border: #e2e8f0;
    }

    #PlanningForm .form-group {
        margin-bottom: 14px;
        position: relative;
    }

    #PlanningForm .form-group label {
        font-weight: 600;
        color: #475569;
        font-size: 13px;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    #PlanningForm .form-group label .label-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        font-size: 12px;
        flex-shrink: 0;
    }

    #PlanningForm .form-group label .label-icon.icon-blue {
        background: linear-gradient(135deg, #e0f0ff 0%, #d0e8ff 100%);
        color: #3a94ef;
    }

    #PlanningForm .form-group label .label-icon.icon-green {
        background: linear-gradient(135deg, #e0f8ee 0%, #d0f0e0 100%);
        color: #27ae60;
    }

    #PlanningForm .form-group label .label-icon.icon-orange {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe8cc 100%);
        color: #f39c12;
    }

    #PlanningForm .form-group label .label-icon.icon-purple {
        background: linear-gradient(135deg, #f0e6ff 0%, #e6d9ff 100%);
        color: #8e44ad;
    }

    #PlanningForm .form-group label .label-icon.icon-red {
        background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
        color: #e74c3c;
    }

    #PlanningForm .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 7px 11px;
        height: 36px;
        font-size: 13px;
        color: #334155;
        outline: none;
        transition: all 0.16s ease;
        background: #ffffff;
        width: 100%;
    }

    #PlanningForm textarea.form-control {
        height: auto;
    }

    #PlanningForm .form-control:focus {
        border-color: #005ae0;
        box-shadow: 0 0 0 3px rgba(0, 90, 224, 0.10);
    }

    #PlanningForm .select2-container--default .select2-selection--single {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        height: 36px;
        background-color: #ffffff;
        transition: all 0.16s ease;
    }

    #PlanningForm .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 34px;
        padding-left: 11px;
        color: #334155;
        font-size: 13px;
    }

    #PlanningForm .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px;
    }

    #PlanningForm .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #005ae0;
        box-shadow: 0 0 0 3px rgba(0, 90, 224, 0.10);
    }

    #PlanningForm .kml-current-file {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 6px 12px;
        background: linear-gradient(135deg, #f0f7ff 0%, #e8f1fb 100%);
        border: 1px solid #d0e2f5;
        border-radius: 8px;
        font-size: 12px;
        color: #2c5282;
    }

    #PlanningForm .kml-current-file .kml-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: linear-gradient(135deg, #3a94ef 0%, #2980d9 100%);
        border-radius: 6px;
        color: white;
        font-size: 11px;
    }

    #PlanningForm .kml-current-file a {
        color: #2c5282;
        font-weight: 600;
        text-decoration: none;
    }

    #PlanningForm input[type="file"] {
        padding: 6px 10px;
        background-color: #ffffff;
        border: 1.5px dashed #d0d5dd;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.25s ease;
        height: auto;
        width: 100%;
        font-size: 12.5px;
    }

    #PlanningForm input[type="file"]:hover {
        border-color: #005ae0;
        background-color: #f7fbff;
    }

    #PlanningForm .show_error {
        background: #fff5f5;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        color: #c53030;
        display: none;
    }

    #PlanningForm .show_error:not(:empty) {
        display: block;
        margin-bottom: 12px;
    }

    #PlanningForm .form-helper {
        font-size: 11.5px;
        color: #64748b;
        margin-top: 4px;
    }

    /* 2-column layout */
    .planning-two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 20px;
    }

    .planning-col {
        min-width: 0;
    }

    .planning-col-title {
        font-size: 12px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .planning-col-title i {
        font-size: 13px;
    }

    .planning-col-divider {
        width: 1px;
        background: #f1f5f9;
        margin: 0;
    }

    /* Buttons */
    .mod-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        height: 40px !important;
        padding: 0 18px !important;
        border-radius: 10px !important;
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
        box-shadow: 0 8px 16px rgba(0, 90, 224, 0.22) !important;
    }

    .mod-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 22px rgba(0, 90, 224, 0.28) !important;
        color: #ffffff !important;
    }

    .mod-btn-secondary {
        border: 1px solid var(--home-border) !important;
        color: #475569 !important;
        background: #ffffff !important;
    }

    .mod-btn-secondary:hover {
        background: #f8fafc !important;
        color: #0f172a !important;
    }

    .modal-overlay {
        top: 0 !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #PlanningForm .modal-content {
        max-width: 860px !important;
        width: 96vw !important;
    }
</style>
<form id="PlanningForm" action="admin/plannings/submit/{{$id}}" method="post" enctype="multipart/form-data" data-parsley-validate novalidate>
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
                <div class="show_error"></div>
                <input type="hidden" name="id" value="{{!empty($planning) ? $planning['id'] : 0}}">

                <div class="planning-two-col">

                    {{-- CỘT TRÁI: Thông tin chung --}}
                    <div class="planning-col">
                        <div class="planning-col-title">
                            <i class="fa fa-info-circle text-primary"></i> Thông tin chung
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon icon-blue"><i class="fa fa-map"></i></span>
                                Tên quy hoạch <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" placeholder="Nhập tên quy hoạch..." value="{{$planning->name ?? ''}}" required>
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon icon-green"><i class="fa fa-map-marker"></i></span>
                                Tỉnh/Thành phố <span class="text-danger">*</span>
                            </label>
                            <select name="province_id" class="form-control select2" required style="width: 100%;">
                                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}" {{ (isset($planning) && $planning->province_id == $province->id) ? 'selected' : '' }}>
                                        {{ $province->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon icon-green"><i class="fa fa-map-signs"></i></span>
                                Khu vực chi tiết
                            </label>
                            <input type="text" name="location_text" class="form-control" placeholder="VD: Bình Thạnh, TP. HCM..." value="{{$planning->location_text ?? ''}}">
                            <div class="form-helper">Hiển thị phụ trên app (quận/huyện, TP)</div>
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon icon-blue"><i class="fa fa-tag"></i></span>
                                Loại quy hoạch <span class="text-danger">*</span>
                            </label>
                            <select name="planning_type" class="form-control" required>
                                <option value="">-- Chọn loại --</option>
                                <option value="published" {{ (isset($planning) && $planning->planning_type == 'published') ? 'selected' : '' }}>Đang công bố</option>
                                <option value="draft_feedback" {{ (isset($planning) && $planning->planning_type == 'draft_feedback') ? 'selected' : '' }}>Dự thảo góp ý</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon icon-green"><i class="fa fa-check-circle"></i></span>
                                Trạng thái <span class="text-danger">*</span>
                            </label>
                            <select name="status" class="form-control" required>
                                <option value="">-- Chọn trạng thái --</option>
                                <option value="approved" {{ (isset($planning) && $planning->status == 'approved') ? 'selected' : '' }}>Đã phê duyệt</option>
                                <option value="effective" {{ (isset($planning) && $planning->status == 'effective') ? 'selected' : '' }}>Hiệu lực</option>
                                <option value="draft" {{ (isset($planning) && $planning->status == 'draft') ? 'selected' : '' }}>Dự thảo</option>
                                <option value="expired" {{ (isset($planning) && $planning->status == 'expired') ? 'selected' : '' }}>Hết hiệu lực</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon icon-orange"><i class="fa fa-align-left"></i></span>
                                Mô tả
                            </label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả quy hoạch...">{{$planning->description ?? ''}}</textarea>
                        </div>
                    </div>

                    {{-- CỘT PHẢI: Thông tin pháp lý & File --}}
                    <div class="planning-col" style="border-left: 1px solid #f1f5f9; padding-left: 20px;">
                        <div class="planning-col-title">
                            <i class="fa fa-file-text-o" style="color:#8e44ad;"></i> Thông tin pháp lý & File
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon icon-blue"><i class="fa fa-file-text"></i></span>
                                Số quyết định <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="decision_no" class="form-control" placeholder="VD: QĐ số 4295/QĐ-UBND..." value="{{$planning->decision_no ?? ''}}" required>
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon icon-purple"><i class="fa fa-calendar"></i></span>
                                Ngày phê duyệt
                            </label>
                            <input type="date" name="approved_date" class="form-control" value="{{$planning->approved_date ?? ''}}">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0 12px;">
                            <div class="form-group">
                                <label>
                                    <span class="label-icon icon-orange"><i class="fa fa-expand"></i></span>
                                    Quy mô (ha) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="any" name="scale" class="form-control" placeholder="VD: 426.8..." value="{{$planning->scale ?? ''}}" required>
                            </div>

                            <div class="form-group">
                                <label>
                                    <span class="label-icon icon-orange"><i class="fa fa-arrows-alt"></i></span>
                                    Diện tích (m²) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="any" name="area" class="form-control" placeholder="Nhập m²..." value="{{$planning->area ?? ''}}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon icon-purple"><i class="fa fa-image"></i></span>
                                Ảnh đại diện
                            </label>
                            <input type="file" name="image" accept="image/*">
                            <div class="form-helper">JPG, PNG, GIF — tối đa 5MB</div>
                            @if(!empty($planning) && $planning->image)
                                <div class="kml-current-file">
                                    <span class="kml-icon"><i class="fa fa-image"></i></span>
                                    <a href="{{ asset('storage/' . $planning->image) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $planning->image) }}" style="height:36px;border-radius:5px;margin-left:4px;" alt="Ảnh quy hoạch">
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>
                                <span class="label-icon icon-purple"><i class="fa fa-file-code-o"></i></span>
                                File KML/KMZ <span class="text-danger">{{ empty($planning) ? '*' : '' }}</span>
                            </label>
                            <input type="file" name="kml_file" accept=".kml,.kmz" {{ empty($planning) ? 'required' : '' }}>
                            <div class="form-helper">Chỉ chấp nhận .kml hoặc .kmz — tối đa 100MB</div>
                            @if(!empty($planning) && $planning->kml_file)
                                <div class="kml-current-file">
                                    <span class="kml-icon"><i class="fa fa-file-text-o"></i></span>
                                    <span>File hiện tại:</span>
                                    <a href="{{ asset('storage/' . $planning->kml_file) }}" target="_blank">{{ basename($planning->kml_file) }}</a>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>{{-- end planning-two-col --}}
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; padding: 14px 20px; border-top: 1px solid #eef2f7; background: #ffffff;">
                <button type="button" class="mod-btn mod-btn-secondary" data-dismiss="modal">Hủy bỏ</button>
                <button class="mod-btn mod-btn-primary" id="saveBtn">Lưu Lại</button>
            </div>
        </div>
    </div>
</form>

<script>
    if ($.fn.select2) {
        $('.select2').select2({
            dropdownParent: $('.modal-overlay')
        });
    }

    $("#PlanningForm").validate({
        rules: {
            name: { required: true },
            province_id: { required: true },
            area: { required: true, number: true },
            decision_no: { required: true },
            scale: { required: true, number: true },
            status: { required: true },
            planning_type: { required: true }
        },
        messages: {
            name: { required: "Vui lòng nhập tên quy hoạch" },
            province_id: { required: "Vui lòng chọn tỉnh/thành phố" },
            area: { required: "Vui lòng nhập diện tích", number: "Diện tích phải là số hợp lệ" },
            decision_no: { required: "Vui lòng nhập số quyết định" },
            scale: { required: "Vui lòng nhập quy mô (ha)", number: "Quy mô phải là số hợp lệ" },
            status: { required: "Vui lòng chọn trạng thái" },
            planning_type: { required: "Vui lòng chọn loại quy hoạch" }
        },
        submitHandler: function (form) {
            var saveBtn = $('#saveBtn');
            var cancelBtn = $('.mod-btn-secondary');
            var originalBtnHtml = saveBtn.html();

            // Disable buttons and show loading state
            saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang lưu...');
            cancelBtn.prop('disabled', true);

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
                        oTable.draw();
                        $('.modal-overlay .close').trigger('click');
                        alert_float('success', data.message);
                    } else {
                        $(".show_error").html(data.message);
                        alert_float('error', data.message);
                        // Restore buttons
                        saveBtn.prop('disabled', false).html(originalBtnHtml);
                        cancelBtn.prop('disabled', false);
                    }
                })
                .fail(function (err) {
                    var htmlError = '';
                    if (err.responseJSON && err.responseJSON.errors) {
                        for (var [el, message] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                    } else {
                        htmlError = 'Có lỗi xảy ra, vui lòng thử lại.';
                    }
                    $(".show_error").html(htmlError);
                    alert_float('error', htmlError);
                    // Restore buttons
                    saveBtn.prop('disabled', false).html(originalBtnHtml);
                    cancelBtn.prop('disabled', false);
                });
            return false;
        }
    });
</script>
