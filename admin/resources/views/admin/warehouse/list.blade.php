@extends('admin.layouts.index')
@section('content')
<style>
    .badge-qty-low  { background:#fef2f2; color:#dc2626; border:1px solid #fca5a5; }
    .badge-qty-ok   { background:#ecfdf5; color:#16a34a; border:1px solid #86efac; }
    .badge-qty {
        display:inline-block; padding:3px 10px; border-radius:20px;
        font-weight:700; font-size:13px; cursor:pointer;
    }
    .btn-qty-detail { text-decoration:none !important; }

    #modal-import-history .modal-header {
        /* background: linear-gradient(135deg,#667eea,#764ba2); */
        color:#fff; border-radius:4px 4px 0 0;
    }
    #modal-import-history .modal-header .close { color:#fff; opacity:.9; }
    #modal-import-history .modal-title { font-weight:700; }

    #tbl-import-history thead th {
        background:#f8f9fa; font-size:12px; font-weight:700;
        text-transform:uppercase; letter-spacing:.3px; color:#555;
    }
    .lô-badge {
        display:inline-block;
        background:#e0e7ff; color:#4338ca;
        border-radius:4px; padding:2px 7px;
        font-size:11px; font-weight:700;
    }
    .remain-full  { color:#16a34a; font-weight:700; }
    .remain-warn  { color:#d97706; font-weight:700; }
    .remain-empty { color:#dc2626; font-weight:700; }
    .summary-bar {
        background:#f8f9ff; border-radius:6px;
        padding:10px 16px; margin-bottom:12px;
        display:flex; gap:24px; flex-wrap:wrap;
    }
    .summary-bar .s-item { font-size:13px; color:#555; }
    .summary-bar .s-item strong { color:#4338ca; font-size:15px; }
</style>

<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title text-capitalize">{{ $title }}</h4>
        <ol class="breadcrumb">
            <li><a href="admin/dashboard">{{ lang('dt_index') }}</a></li>
            <li class="active">{{ $title }}</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            <div style="margin-bottom:12px">
                <a href="admin/imports/list" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Tạo phiếu nhập
                </a>
                <small class="text-muted m-l-10">
                    <i class="fa fa-info-circle"></i>
                    Nhấn vào nút <strong>Chi tiết lô nhập</strong> bên cột thao tác để xem chi tiết
                </small>
            </div>
            <table class="table table-striped table-bordered" id="table_warehouse">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th style="text-align:center">Tổng tồn kho</th>
                        <th>Lần nhập gần nhất</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Modal chi tiết lô nhập --}}
<div class="modal fade" id="modal-import-history" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-history"></i>
                    Lịch sử nhập kho: <span id="modal-product-name"></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="summary-bar" id="modal-summary" style="display:none">
                    <div class="s-item">Tổng lô nhập: <strong id="s-lots">0</strong></div>
                    <div class="s-item">Tổng đã nhập: <strong id="s-total-in">0</strong></div>
                    <div class="s-item">Tổng còn lại: <strong id="s-total-remain">0</strong></div>
                </div>
                <div id="modal-loading" style="text-align:center;padding:30px">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted m-t-10">Đang tải dữ liệu...</p>
                </div>
                <div id="modal-content" style="display:none">
                    <table class="table table-bordered table-hover" id="tbl-import-history">
                        <thead>
                            <tr>
                                <th style="width:40px">STT</th>
                                <th>Ngày nhập</th>
                                <th>Mã phiếu</th>
                                <th>Nhà cung cấp</th>
                                <th style="text-align:center">SL nhập</th>
                                <th style="text-align:center">SL còn lại</th>
                                <th style="text-align:center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody id="modal-tbody"></tbody>
                    </table>
                </div>
                <div id="modal-empty" style="display:none;text-align:center;padding:30px;color:#aaa">
                    <i class="fa fa-inbox fa-3x"></i>
                    <p class="m-t-10">Chưa có lô nhập nào được duyệt cho sản phẩm này</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function () {
    $('#table_warehouse').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'admin/warehouse/getTable',
            type: 'POST',
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'product_code' },
            { data: 'product_name' },
            { data: 'quantity', className: 'text-center' },
            { data: 'last_import' },
            { data: 'options', orderable: false, searchable: false },
        ],
        language: lang.datatables,
        order: [[3, 'desc']],
    });
});

window.showImportHistory = function (idProduct, productName) {
    $('#modal-product-name').text(productName);
    $('#modal-loading').show();
    $('#modal-content').hide();
    $('#modal-empty').hide();
    $('#modal-summary').hide();
    $('#modal-tbody').html('');
    $('#modal-import-history').modal('show');

    $.get('admin/warehouse/getImportHistory/' + idProduct, function (res) {
        $('#modal-loading').hide();

        if (!res.result || res.data.length === 0) {
            $('#modal-empty').show();
            return;
        }

        var rows = res.data;
        var totalIn = 0, totalRemain = 0;
        var html = '';

        $.each(rows, function (i, r) {
            var remain   = parseInt(r.remaining_qty);
            var imported = parseInt(r.import_qty);
            totalIn     += imported;
            totalRemain += remain;

            var pct = imported > 0 ? Math.round((remain / imported) * 100) : 0;
            var cls = remain === 0 ? 'remain-empty' : (pct < 30 ? 'remain-warn' : 'remain-full');
            var statusHtml = remain === 0
                ? '<span class="label label-danger">Hết hàng</span>'
                : (pct < 30
                    ? '<span class="label label-warning">Sắp hết</span>'
                    : '<span class="label label-success">Còn hàng</span>');

            var importDate = r.import_date
                ? r.import_date.substring(0, 10).split('-').reverse().join('/')
                : '-';

            html += `<tr>
                <td style="text-align:center">${i + 1}</td>
                <td><strong>${importDate}</strong></td>
                <td><span class="lô-badge">${r.import_code || 'N/A'}</span></td>
                <td>${r.supplier_name || '-'}</td>
                <td style="text-align:center">${imported}</td>
                <td style="text-align:center">
                    <span class="${cls}">${remain}</span>
                    <br><small style="color:#aaa;font-size:11px">(${pct}% còn lại)</small>
                </td>
                <td style="text-align:center">${statusHtml}</td>
            </tr>`;
        });

        $('#modal-tbody').html(html);
        $('#s-lots').text(rows.length);
        $('#s-total-in').text(totalIn);
        $('#s-total-remain').text(totalRemain);
        $('#modal-summary').show();
        $('#modal-content').show();
    }).fail(function () {
        $('#modal-loading').hide();
        $('#modal-empty').show();
    });
}
</script>
@endsection
