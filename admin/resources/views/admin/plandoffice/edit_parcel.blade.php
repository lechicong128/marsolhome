<style>
    :root {
        --home-primary: #005ae0;
        --home-primary-dark: #004ec4;
        --home-border: #e2e8f0;
    }

    #EditParcelForm .form-group {
        margin-bottom: 16px;
    }

    #EditParcelForm .form-group label {
        font-weight: 600;
        color: #475569;
        font-size: 13px;
        margin-bottom: 6px;
        display: block;
    }

    #EditParcelForm .form-control {
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

    #EditParcelForm .form-control:focus {
        border-color: #005ae0;
        box-shadow: 0 0 0 3px rgba(0, 90, 224, 0.10);
    }

    #EditParcelForm textarea.form-control {
        height: auto !important;
        min-height: 80px;
    }

    /* Planning land types table */
    #EditParcelForm .planning-table-container {
        border: 1px solid #e4e7ec;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 8px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    #EditParcelForm .planning-table-container table {
        margin-bottom: 0;
        width: 100%;
    }
    #EditParcelForm .planning-table-container th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 12px;
        padding: 11px 12px;
        border-bottom: 1px solid #e4e7ec;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    #EditParcelForm .planning-table-container td {
        padding: 8px 12px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    #EditParcelForm .planning-table-container tr:last-child td {
        border-bottom: none;
    }
    #EditParcelForm .section-divider {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin-top: 20px;
        margin-bottom: 12px;
        padding-bottom: 6px;
        border-bottom: 2px solid #eaecf0;
    }
    #EditParcelForm .show_error {
        background: #fff5f5;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
        color: #c53030;
        display: none;
    }
    #EditParcelForm .show_error:not(:empty) {
        display: block;
        margin-bottom: 15px;
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

    

    #EditParcelForm .modal-content {
        max-width: 800px !important;
    }
</style>

<form id="EditParcelForm" action="admin/plandoffices/update-parcel/{{$parcel->id}}" method="post" data-parsley-validate novalidate>
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

                <!-- Row 1 -->
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="so_to">Số tờ</label>
                            <input type="text" name="so_to" class="form-control" placeholder="Nhập số tờ..." value="{{$parcel->so_to ?? ''}}">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="so_thua">Số thửa</label>
                            <input type="text" name="so_thua" class="form-control" placeholder="Nhập số thửa..." value="{{$parcel->so_thua ?? ''}}">
                        </div>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="dien_tich">Diện tích (m²)</label>
                            <input type="number" step="0.01" id="dien_tich" name="dien_tich" class="form-control" placeholder="Nhập diện tích..." value="{{$parcel->dien_tich ?? ''}}">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="loai_dat">Loại đất</label>
                            <input type="text" name="loai_dat" class="form-control" placeholder="Nhập loại đất (VD: ODT, CLN...)" value="{{$parcel->loai_dat ?? ''}}">
                        </div>
                    </div>
                </div>

                <!-- Row 3 -->
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="cong_trinh">Công trình</label>
                            <input type="text" name="cong_trinh" class="form-control" placeholder="Nhập công trình nhà..." value="{{$parcel->cong_trinh ?? ''}}">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="ten_chu">Tên chủ</label>
                            <input type="text" name="ten_chu" class="form-control" placeholder="Nhập tên chủ đất..." value="{{$parcel->ten_chu ?? ''}}">
                        </div>
                    </div>
                </div>

                <!-- Loại đất quy hoạch Dynamic Table -->
                <div class="section-divider">Loại đất quy hoạch</div>

                <div class="text-right m-b-10">
                    <button type="button" class="mod-btn mod-btn-primary btn-add-row" style="height: 36px !important; padding: 0 14px !important; border-radius: 10px !important;">
                        <i class="fa fa-plus"></i> Thêm loại đất quy hoạch
                    </button>
                </div>

                <div class="planning-table-container table-responsive">
                    <table class="table" id="planning-land-table">
                        <thead>
                            <tr>
                                <th>Loại đất quy hoạch</th>
                                <th style="width: 200px;">Diện tích (m²)</th>
                                <th style="width: 150px;">% tổng</th>
                                <th style="width: 80px;" class="text-center">Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($loai_dat_quy_hoach) && count($loai_dat_quy_hoach) > 0)
                                @foreach($loai_dat_quy_hoach as $row)
                                    <tr class="planning-land-row">
                                        <td>
                                            <input type="text" name="loai_dat_quy_hoach_type[]" class="form-control planning-type" value="{{ $row['type'] ?? '' }}" placeholder="Nhập loại đất quy hoạch..." required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="loai_dat_quy_hoach_area[]" class="form-control planning-area" value="{{ $row['area'] ?? '' }}" placeholder="Diện tích..." required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="loai_dat_quy_hoach_percentage[]" class="form-control planning-percentage" value="{{ $row['percentage'] ?? '' }}" placeholder="% tổng...">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger btn-remove-row" title="Xóa dòng"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="planning-land-row">
                                    <td>
                                        <input type="text" name="loai_dat_quy_hoach_type[]" class="form-control planning-type" placeholder="Nhập loại đất quy hoạch..." required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="loai_dat_quy_hoach_area[]" class="form-control planning-area" placeholder="Diện tích..." required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="loai_dat_quy_hoach_percentage[]" class="form-control planning-percentage" placeholder="% tổng...">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger btn-remove-row" title="Xóa dòng"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Thông tin mô tả thửa Textarea -->
                <div class="section-divider">Thông tin mô tả thửa</div>
                <div class="form-group">
                    <textarea name="mo_ta_thua" class="form-control" rows="4" placeholder="Nhập thông tin mô tả thửa đất (độ rộng đường, khoảng cách tới đường chính, hình dáng, hướng mặt tiền, v.v.)...">{{$parcel->mo_ta_thua ?? ''}}</textarea>
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
    $(document).ready(function() {
        // Auto calculate percentage helper function
        function calculatePercentages() {
            var totalArea = parseFloat($('#dien_tich').val()) || 0;
            $('#planning-land-table tbody tr').each(function() {
                var $row = $(this);
                var area = parseFloat($row.find('.planning-area').val()) || 0;
                var percentageInput = $row.find('.planning-percentage');
                if (totalArea > 0) {
                    var pct = (area / totalArea) * 100;
                    percentageInput.val(pct.toFixed(2));
                } else {
                    percentageInput.val('');
                }
            });
        }

        // Add dynamic row trigger
        $('.btn-add-row').click(function() {
            var html = `
            <tr class="planning-land-row">
                <td>
                    <input type="text" name="loai_dat_quy_hoach_type[]" class="form-control planning-type" placeholder="Nhập loại đất quy hoạch..." required>
                </td>
                <td>
                    <input type="number" step="0.01" name="loai_dat_quy_hoach_area[]" class="form-control planning-area" placeholder="Diện tích..." required>
                </td>
                <td>
                    <input type="number" step="0.01" name="loai_dat_quy_hoach_percentage[]" class="form-control planning-percentage" placeholder="% tổng...">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-row" title="Xóa dòng"><i class="fa fa-trash"></i></button>
                </td>
            </tr>`;
            $('#planning-land-table tbody').append(html);
            calculatePercentages();
        });

        // Remove dynamic row
        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Triggers for recalculation
        $(document).on('input', '.planning-area', function() {
            calculatePercentages();
        });
        $('#dien_tich').on('input', function() {
            calculatePercentages();
        });

        // Submit form via AJAX
        $("#EditParcelForm").validate({
            rules: {
                dien_tich: {
                    number: true,
                    min: 0
                }
            },
            submitHandler: function (form) {
                var url = $(form).attr('action');
                var data = $(form).serialize();
                var $btn = $('#saveBtn');
                var originalText = $btn.html();

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang lưu...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'JSON',
                    data: data,
                })
                .done(function (res) {
                    if (res.result) {
                        alert_float('success', res.message);
                        $('#EditParcelForm').closest('.modal').modal('hide');
                        if (typeof oTable !== 'undefined') {
                            oTable.draw('page');
                        } else if (typeof oTableParcels !== 'undefined') {
                            oTableParcels.draw('page');
                        } else {
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    } else {
                        $btn.prop('disabled', false).html(originalText);
                        $(".show_error").html(res.message);
                        alert_float('error', res.message);
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
    });
</script>
