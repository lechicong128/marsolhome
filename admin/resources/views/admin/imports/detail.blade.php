@extends('admin.layouts.index')

@section('content')
<style>
    .import-header-card {
        background: #fff;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
    }
    .import-header-card h4 { margin: 0; font-size: 18px; font-weight: 600; color: #333; }
    .import-header-card .breadcrumb { background: transparent; padding: 4px 0 0; margin: 0; }
    .import-header-card .breadcrumb > li + li::before { color: #aaa; }
    .import-header-card .breadcrumb li a { color: #667eea; }
    .import-header-card .breadcrumb li.active { color: #888; }

    .section-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 6px rgba(0,0,0,.08);
        padding: 24px;
        margin-bottom: 20px;
    }
    .section-title {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #555;
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f3f4;
    }
    .section-title i { margin-right: 6px; color: #667eea; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    .status-pending  { background: #fff8e1; color: #f59e0b; border: 1px solid #fde68a; }
    .status-approved { background: #ecfdf5; color: #10b981; border: 1px solid #6ee7b7; }
    .status-cancel   { background: #fef2f2; color: #ef4444; border: 1px solid #fca5a5; }

    /* Product picker area */
    .product-picker-wrap {
        background: #f8f9ff;
        border: 2px dashed #c7d2fe;
        border-radius: 8px;
        padding: 18px;
        margin-bottom: 20px;
    }
    .product-picker-wrap label { font-weight: 600; color: #4338ca; margin-bottom: 8px; display: block; }
    .btn-add-product {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 8px 18px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity .2s;
    }
    .btn-add-product:hover { opacity: .85; color: #fff; }

    /* Table styling */
    #product_items thead tr th {
        background: #f1f3f4;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #555;
        padding: 10px 12px;
    }
    #product_items tbody tr:hover { background: #fafbff; }
    #product_items tbody td { vertical-align: middle; padding: 10px 12px; }
    .item-badge {
        display: inline-block;
        background: #e0e7ff;
        color: #4338ca;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 700;
    }
    .qty-input {
        width: 90px !important;
        text-align: center;
        font-weight: 600;
        font-size: 15px;
        display: block;
    }


    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #aaa;
    }
    .empty-state i { font-size: 40px; margin-bottom: 10px; display: block; }
    .empty-state p { margin: 0; font-size: 14px; }

    .footer-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 16px;
        border-top: 2px solid #f1f3f4;
    }
    .total-summary { font-size: 14px; color: #555; }
    .total-summary strong { color: #4338ca; font-size: 16px; }

    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 10px 28px;
        font-weight: 700;
        font-size: 14px;
        transition: opacity .2s;
    }
    .btn-save:hover { opacity: .85; color: #fff; }
</style>

{{-- Page Title --}}
<div class="import-header-card">
    <div style="display:flex; justify-content:space-between; align-items:center">
        <div>
            <h4><i class="fa fa-file-text-o"></i> {{ $title }}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{ lang('dt_index') }}</a></li>
                <li><a href="admin/imports/list">Nhập kho</a></li>
                <li class="active">{{ $title }}</li>
            </ol>
        </div>
        @if($import)
        <div>
            @if($import->status == 0)
                <span class="status-badge status-pending"><i class="fa fa-clock-o"></i> Chờ duyệt</span>
            @elseif($import->status == 1)
                <span class="status-badge status-approved"><i class="fa fa-check-circle"></i> Đã duyệt</span>
            @else
                <span class="status-badge status-cancel"><i class="fa fa-times-circle"></i> Đã hủy</span>
            @endif
        </div>
        @endif
    </div>
</div>

<form id="import-form">
    @csrf

    {{-- Thông tin phiếu --}}
    <div class="section-card">
        <div class="section-title"><i class="fa fa-info-circle"></i> Thông tin phiếu nhập</div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Ngày nhập <span class="text-danger">*</span></label>
                    <input type="date" name="import_date" id="import_date"
                        class="form-control"
                        value="{{ $import ? $import->import_date : date('Y-m-d') }}"
                        {{ $import && $import->status != 0 ? 'disabled' : '' }}
                        required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nhà cung cấp <span class="text-danger">*</span></label>
                    <input type="text" name="supplier_name" id="supplier_name"
                        class="form-control"
                        value="{{ $import ? $import->supplier_name : '' }}"
                        {{ $import && $import->status != 0 ? 'disabled' : '' }}
                        placeholder="Nhập tên nhà cung cấp" required>
                </div>
            </div>
            @if($import && $import->import_code)
            <div class="col-md-4">
                <div class="form-group">
                    <label>Mã phiếu nhập</label>
                    <input type="text" class="form-control" value="{{ $import->import_code }}" disabled>
                </div>
            </div>
            @endif
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group" style="margin-bottom:0">
                    <label>Ghi chú</label>
                    <textarea name="note" id="import_note" class="form-control" rows="2"
                        {{ $import && $import->status != 0 ? 'disabled' : '' }}
                        placeholder="Ghi chú về lô hàng nhập (nếu có)...">{{ $import ? $import->note : '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Danh sách sản phẩm --}}
    <div class="section-card">
        <div class="section-title"><i class="fa fa-cube"></i> Sản phẩm nhập kho</div>

        @if(!$import || $import->status == 0)
        <div class="product-picker-wrap">
            <label><i class="fa fa-search"></i> Tìm và thêm sản phẩm</label>
            <div style="display:flex; gap:10px; align-items:flex-end">
                <div style="flex:1">
                    <select id="product_select" class="form-control select2" style="width:100%">
                        <option value="">-- Gõ tên hoặc mã sản phẩm để tìm --</option>
                        @foreach($products as $p)
                            <option
                                value="{{ $p->id }}"
                                data-name="{{ $p->name }}"
                                data-code="{{ $p->code ?? 'N/A' }}"
                            >{{ $p->code ? '['.$p->code.'] ' : '' }}{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <small style="color:#888; margin-top:6px; display:block">
                <i class="fa fa-lightbulb-o"></i> Chọn sản phẩm từ danh sách, sản phẩm sẽ tự động được thêm xuống bảng bên dưới.
            </small>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="product_items">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center">STT</th>
                        <th style="width:110px">Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th style="width:170px; text-align:center">Số lượng nhập</th>
                        @if(!$import || $import->status == 0)
                        <th style="width:80px; text-align:center">Xóa</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if($import && isset($import->details) && count($import->details) > 0)
                        @foreach($import->details as $index => $detail)
                        <tr data-id="{{ $detail->id_product }}">
                            <td style="text-align:center">
                                <span class="item-badge">{{ $index + 1 }}</span>
                            </td>
                            <td><code>{{ $detail->product_code ?? 'N/A' }}</code></td>
                            <td><strong>{{ $detail->product_name }}</strong></td>
                            <td style="text-align:center">
                                <input type="number"
                                    name="products[{{ $detail->id_product }}]"
                                    class="form-control qty-input"
                                    value="{{ $detail->quantity }}"
                                    min="1"
                                    style="width:90px;margin:auto"
                                    {{ ($import && $import->status != 0) ? 'disabled' : '' }}>
                            </td>
                            @if(!$import || $import->status == 0)
                            <td style="text-align:center">
                                <button type="button" class="btn btn-danger btn-xs remove-item" title="Xóa">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    @else
                        <tr id="empty-row">
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fa fa-inbox"></i>
                                    <p>Chưa có sản phẩm nào. Dùng ô tìm kiếm phía trên để thêm sản phẩm.</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="footer-actions">
            <div class="total-summary">
                Tổng: <strong id="total_items">{{ $import && isset($import->details) ? count($import->details) : 0 }}</strong> loại sản phẩm
                &nbsp;|&nbsp;
                Tổng số lượng: <strong id="total_qty">{{ $import && isset($import->details) ? $import->details->sum('quantity') : 0 }}</strong>
            </div>
            <div style="display:flex; gap:10px">
                @if(!$import || $import->status == 0)
                <button type="submit" class="btn-save">
                    <i class="fa fa-save"></i> Lưu phiếu nhập
                </button>
                @endif
                <a href="admin/imports/list" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@section('script')
<script>
$(document).ready(function () {

    // Ẩn loading overlay của layout (không có DataTable trên trang này)
    $('#loading').hide();
    $('#loading-content').html('');

    // Init Select2 với search
    $('#product_select').select2({
        placeholder: '-- Gõ tên hoặc mã sản phẩm để tìm --',
        allowClear: true,
        width: '100%',
    });

    // Chọn sản phẩm -> tự append xuống bảng
    $('#product_select').on('change', function () {
        var $sel = $(this);
        var val  = $sel.val();
        if (!val) return;

        var $opt = $sel.find('option[value="' + val + '"]');
        var name = $opt.data('name');
        var code = $opt.data('code');

        // Kiểm tra trùng
        if ($('#product_items tbody').find('tr[data-id="' + val + '"]').length > 0) {
            alert_float('warning', 'Sản phẩm <b>' + name + '</b> đã có trong danh sách!');
            $sel.val(null).trigger('change');
            return;
        }

        // Xóa hàng empty placeholder
        $('#empty-row').remove();

        var stt = $('#product_items tbody tr').length + 1;

        var html = `
        <tr data-id="${val}">
            <td style="text-align:center">
                <span class="item-badge stt-badge">${stt}</span>
            </td>
            <td><code>${code || 'N/A'}</code></td>
            <td><strong>${name}</strong></td>
            <td style="text-align:center">
                <input type="number" name="products[${val}]" class="form-control qty-input" value="1" min="1" style="width:90px;margin:auto">
            </td>
            <td style="text-align:center">
                <button type="button" class="btn btn-danger btn-xs remove-item" title="Xóa">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>`;

        $('#product_items tbody').prepend(html);

        // Reset về rỗng để chọn tiếp
        $sel.val(null).trigger('change');

        updateSummary();
    });

    // Nút xóa hàng
    $(document).on('click', '.remove-item', function () {
        var name = $(this).closest('tr').find('td:nth-child(3) strong').text();
        $(this).closest('tr').remove();
        reorderSTT();
        updateSummary();

        if ($('#product_items tbody tr').length === 0) {
            $('#product_items tbody').html(`
                <tr id="empty-row">
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fa fa-inbox"></i>
                            <p>Chưa có sản phẩm nào. Dùng ô tìm kiếm phía trên để thêm sản phẩm.</p>
                        </div>
                    </td>
                </tr>`);
        }
    });

    $(document).on('change', '.qty-input', updateSummary);

    function reorderSTT() {
        $('#product_items tbody tr').each(function (i) {
            $(this).find('.stt-badge').text(i + 1);
        });
    }

    function updateSummary() {
        var rows  = $('#product_items tbody tr:not(#empty-row)').length;
        var total = 0;
        $('.qty-input').each(function () { total += parseInt($(this).val()) || 0; });
        $('#total_items').text(rows);
        $('#total_qty').text(total);
    }

    // Submit form
    $('#import-form').on('submit', function (e) {
        e.preventDefault();

        var realRows = $('#product_items tbody tr:not(#empty-row)').length;
        if (realRows === 0) {
            alert_float('danger', 'Vui lòng thêm ít nhất 1 sản phẩm vào danh sách');
            return;
        }

        var $btn = $(this).find('.btn-save');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang lưu...');

        $.ajax({
            url : 'admin/imports/submit/{{ $id ?: 0 }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.result) {
                    alert_float('success', res.message);
                    setTimeout(function () {
                        window.location.href = 'admin/imports/list';
                    }, 1000);
                } else {
                    if (Array.isArray(res.message)) {
                        res.message.forEach(function (msg) { alert_float('danger', msg); });
                    } else {
                        alert_float('danger', res.message);
                    }
                    $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Lưu phiếu nhập');
                }
            },
            error: function () {
                alert_float('danger', 'Có lỗi xảy ra, vui lòng thử lại');
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Lưu phiếu nhập');
            }
        });
    });
});
</script>
@endsection
