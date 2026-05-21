@extends('admin.layouts.index')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a class="btn btn-default" href="admin/imports/detail"><i class="fa fa-plus"></i> {{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">Danh sách nhập kho</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li class="active">Nhập kho</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <table id="table_imports" class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">{{lang('dt_stt')}}</th>
                            <th class="text-center">Mã phiếu</th>
                            <th class="text-center">Ngày nhập</th>
                            <th class="text-center">Nhà cung cấp</th>
                            <th class="text-center">Mặt hàng - Số lượng</th>
                            <th class="text-center">Ghi chú</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">{{lang('dt_actions')}}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <div id="modal-consumed" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Chi tiết xuất kho sản phẩm</h4>
                </div>
                <div class="modal-body">
                    <p id="consumed-msg" class="alert alert-danger"></p>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th class="text-center">Số lượng lấy</th>
                                <th class="text-center">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody id="consumed-tbody"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Đóng</button>
                    <a href="admin/transaction/list" class="btn btn-primary waves-effect waves-light">Tới danh sách đơn hàng</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        var oTable = InitDataTable('#table_imports', 'admin/imports/getTable', {
            'order': [[0, 'desc']],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/imports/getTable",
                "data": function (d) {},
                "dataSrc": function (json) { return json.data; }
            },
            columnDefs: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', width: "50px", className: "text-center" },
                { data: 'import_code', name: 'import_code', width: "150px" },
                { data: 'import_date', name: 'import_date', width: "100px", className: "text-center" },
                { data: 'supplier_name', name: 'supplier_name' },
                { data: 'items', name: 'items' },
                { data: 'note', name: 'note' },
                { data: 'status', name: 'status', width: "100px", className: "text-center" },
                { data: 'options', name: 'options', orderable: false, searchable: false, width: "150px", className: "text-center" },
            ]
        });

        function approveImport(id) {
            if (confirm('Bạn có chắc chắn muốn duyệt phiếu nhập này? Kho sẽ được cập nhật số lượng.')) {
                $.post('admin/imports/approve/' + id, {}, function(res) {
                    if (res.result) {
                        alert_float('success', res.message);
                        oTable.ajax.reload();
                    } else {
                        alert_float('danger', res.message);
                    }
                });
            }
        }

        function unapproveImport(id) {
            if (confirm('Bạn có chắc chắn muốn bỏ duyệt? Số lượng tồn kho sẽ bị trừ đi. Hệ thống sẽ kiểm tra xem hàng đã bị xuất kho chưa trước khi thực hiện.')) {
                $.post('admin/imports/unapprove/' + id, {}, function(res) {
                    if (res.result) {
                        alert_float('success', res.message);
                        oTable.ajax.reload();
                    } else {
                        if (res.type == 'consumed_error') {
                            $('#consumed-msg').text(res.message);
                            var html = '';
                            res.consumed_by.forEach(function(item) {
                                html += '<tr>' +
                                            '<td><b>' + item.order_code + '</b></td>' +
                                            '<td class="text-center">' + item.qty_take + '</td>' +
                                            '<td class="text-center">' + formatDateTime(item.created_at) + '</td>' +
                                        '</tr>';
                            });
                            $('#consumed-tbody').html(html);
                            $('#modal-consumed').modal('show');
                        } else {
                            alert_float('danger', res.message);
                        }
                    }
                });
            }
        }

        function formatDateTime(str) {
            if (!str) return '';
            var d = new Date(str.replace(' ', 'T'));
            if (isNaN(d)) return str;
            var pad = function(n) { return String(n).padStart(2, '0'); };
            return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear()
                 + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
        }

        function deleteImport(id) {
            if (confirm('Bạn có chắc chắn muốn xóa phiếu nhập này?')) {
                $.get('admin/imports/delete/' + id, function(res) {
                    if (res.result) {
                        alert_float('success', res.message);
                        oTable.ajax.reload();
                    } else {
                        alert_float('danger', res.message);
                    }
                });
            }
        }

        function toggleItems(btn, id) {
            var rows = $('.hidden-row-' + id);
            if (rows.is(':visible')) {
                rows.hide();
                $(btn).html('Xem thêm (' + rows.length + ') <i class="fa fa-angle-down"></i>');
            } else {
                rows.show();
                $(btn).html('Thu gọn <i class="fa fa-angle-up"></i>');
            }
        }
    </script>
@endsection
