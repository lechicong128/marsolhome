@extends('admin.layouts.index')
@section('content')
<style>
  .stats-table {
        width: 100%;
        min-width: 190px;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding:unset !important;
    }
    .stats-table tr td {
        padding: 4px 8px !important;
        border-bottom: 1px solid #f3f4f6;
    }
    .stats-table tr:last-child td {
        border-bottom: none;
    }
    .label-cell {
        color: #6b7280;
        background-color: #f9fafb;
        width: 60%;
    }
    .value-cell {
        color: #6b7280;
        background-color: #f9fafb;
        text-align: right;
        font-weight: bold;
    }
</style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a  class="btn btn-default dt-modal" href="admin/transaction/detailTransaction/0">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/transaction/list">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs">
                <li class="H-search active cursor"><a data-toggle="tab" data-id="-1">{{lang('all')}} (<b class="count_all">0</b>)</a></li>
                <li class="H-search cursor"><a style="color: red !important;" data-toggle="tab" data-id="-2">Đang theo dõi (<b class="count_follow">0</b>)</a></li>
                @foreach (getListStatusTransaction() as $key => $value)
                    <li class="H-search cursor"><a style="color: {{$value['background']}} !important;" data-toggle="tab" data-id="{{$value['id']}}">{{$value['name']}}  (<b class="count_{{$value['id']}}">0</b>)</a></li>
                @endforeach
                <li class="H-search cursor"><a style="color: red !important;" data-toggle="tab" data-id="-3">Chưa duyệt kho (<b class="count_warehouse_status_0">0</b>)</a></li>
                <li class="H-search cursor"><a style="color: green !important;" data-toggle="tab" data-id="-4">Đã duyệt kho (<b class="count_warehouse_status_1">0</b>)</a></li>
            </ul>
            <span class="group_search">
                <input type="hidden" name="status_search" id="status_search" value="-1">
            </span>
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <label for="customer_search">Khách hàng</label>
                        <select class="customer_search select2" id="customer_search"
                                data-placeholder="Chọn ..." name="customer_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_search">Thời gian</label>
                        <input class="form-control date_search" type="text" id="date_search" name="date_search" value="" autocomplete="off">
                    </div>
                    <!-- <div class="col-md-2">
                        <label for="warehouse_status_search">Trạng thái kho</label>
                        <select class="form-control" id="warehouse_status_search" name="warehouse_status_search">
                            <option value="">-- Tất cả --</option>
                            <option value="0">Chưa duyệt kho</option>
                            <option value="1">Đã duyệt kho</option>
                        </select>
                    </div> -->
                </div>
                <table id="table_transaction" class="table table-bordered table_transaction">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_reference_no')}}</th>
                        <th class="text-center">{{lang('dt_date')}}</th>
                        <th class="text-center">{{lang('client')}}</th>
                        <th class="text-center">{{lang('Cấp khách hàng')}}</th>
                        <th class="text-center">{{lang('dt_infomation_delivery')}}</th>
                        <th class="text-center">{{lang('dt_status')}}</th>
                        <th class="text-center">{{lang('dt_total')}}</th>
                        <th class="text-center">{{lang('Khoản giảm trừ')}}</th>
                        <th class="text-center">{{lang('Tiền vat')}}</th>
                        <th class="text-center">{{lang('dt_total_all')}}</th>
                        <th class="text-center">{{lang('Chiết khấu Leader')}}</th>
                        <th class="text-center">{{lang('Chiết khấu F2')}}</th>
                        <th class="text-center">{{lang('Trạng thái ĐH')}}</th>
                        <th class="text-center">Duyệt kho</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2" style="text-transform: uppercase;font-weight: bold">Tổng cộng</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="total" style="font-weight: bold;text-align: right"></td>
                        <td></td>
                        <td class="total_vat" style="font-weight: bold;text-align: right"></td>
                        <td class="grand_total" style="font-weight: bold;text-align: right"></td>
                        <td class="total_discount_leader" style="font-weight: bold;text-align: right"></td>
                        <td class="total_discount_customer_f1" style="font-weight: bold;text-align: right"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
            </div>
        </div>
    </div>

<!-- Modal Duyệt Kho -->
<div class="modal fade" id="modal-warehouse-approve" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="display: block !important;">
            <div class="modal-header" style="display: block !important;">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" style="margin:0;"><i class="fa fa-cubes"></i> Duyệt xuất kho đơn hàng <span id="wh-order-id"></span></h4>
            </div>
            <div class="modal-body" id="wh-modal-body">
                <div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Đang tải...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" id="btn-confirm-warehouse" onclick="confirmWarehouseApprove()">
                    <i class="fa fa-check"></i> Xác nhận duyệt kho
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script>
        $(document).ready(function (){
            searchAjaxSelect2('#customer_search','admin/category/searchCustomer')
            search_daterangetimepicker('date_search');
        })
        var fnserverparams = {
            'status_search': '#status_search',
            'customer_search': '#customer_search',
            'date_search': '#date_search',
            'date_search_end': '#date_search_end',
            'warehouse_status_search': '#warehouse_status_search',
        };
        var oTable;
        oTable = InitDataTable('#table_transaction', 'admin/transaction/getList', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/transaction/getList",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    if(json.result == false){
                        alert_float('error',json.message);
                    }
                    return json.data;
                }
            },
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'id', name: 'id',width: "50px"
                },
                {data: 'reference_no', name: 'reference_no',width: "150px" },
                {data: 'date', name: 'date',width: "150px" },
                {data: 'customer', name: 'customer',orderable: false},
                {data: 'customer_level', name: 'customer_level',orderable: false,width: "100px"},
                {data: 'information_delivery', name: 'information_delivery',orderable: false,width: "250px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'status', name: 'status',width: "200px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total', name: 'total',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-left">${data}</div>`;
                    },
                    data: 'reduction', name: 'reduction',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total_vat', name: 'total_vat',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'grand_total', name: 'grand_total',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total_discount_leader', name: 'total_discount_leader',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total_discount_customer_f1', name: 'total_discount_customer_f1',width: "140px"
                },
                {
                    data: 'status_invoice', name: 'status_invoice', width: "110px",
                    "render": function(data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    }
                },
                {
                    data: 'warehouse_status', name: 'warehouse_status', orderable: false, searchable: false, width: "110px",
                    "render": function(data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    }
                },
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ],
        });

        $('.H-search').click(function() {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })


        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        $('#table_transaction').on('draw.dt', function () {
            getCountAll();
            var table = $(this).DataTable();
            var total =  table.column(7).data().sum();
            var total_vat =  table.column(9).data().sum();
            var grand_total =  table.column(10).data().sum();
            var total_discount_leader =  table.column(11).data().sum();
            var total_discount_customer_f1 =  table.column(12).data().sum();
            $("#table_transaction").find('tfoot .total').html(formatNumber(total));
            $("#table_transaction").find('tfoot .total_vat').html(formatNumber(total_vat));
            $("#table_transaction").find('tfoot .grand_total').html(formatNumber(grand_total));
            $("#table_transaction").find('tfoot .total_discount_leader').html(formatNumber(total_discount_leader));
            $("#table_transaction").find('tfoot .total_discount_customer_f1').html(formatNumber(total_discount_customer_f1));
        });

        function getCountAll() {
            var data = {};
            $.each(fnserverparams, function(filterIndex, filterItem) {
                data[filterIndex] = $(filterItem).val();
            });
            $.ajax({
                url: 'admin/transaction/countAll',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: data
            })
                .done(function (response) {
                    var total = 0;
                    if(response.arr.length > 0){
                        $.each(response.arr, function(index, value) {
                            $(`.count_${value.id}`).text(formatNumber(value.count));
                            total += parseFloat(value.count);
                        })
                    }
                    $(`.count_all`).text(formatNumber(total));
                    $(`.count_follow`).text(formatNumber(response.follow));
                    $(`.count_warehouse_status_0`).text(formatNumber(response.warehouse_status_0));
                    $(`.count_warehouse_status_1`).text(formatNumber(response.warehouse_status_1));
                })
                .fail(function () {

                });
            return false;
        }

        function changeStatus(transaction_id,status){
            $.ajax({
                url: 'admin/transaction/changeStatus',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    transaction_id: transaction_id,
                    status: status,
                },
            })
                .done(function (data) {
                    if (data.result) {
                        alert_float('success', data.message);
                    } else {
                        alert_float('error', data.message);
                    }
                    oTable.draw('page');
                })
                .fail(function () {

                });
            return false;
        }

        var _currentWarehouseId = null;
        var _currentAllocation  = [];

        function openWarehouseModal(transactionId) {
            _currentWarehouseId = transactionId;
            _currentAllocation  = [];
            $('#wh-order-id').text('#' + transactionId);
            $('#wh-modal-body').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Đang tải...</div>');
            $('#modal-warehouse-approve').modal('show');

            $.get('admin/transaction/getOrderItemsForWarehouse/' + transactionId)
                .done(function(res) {
                    if (!res.result) {
                        $('#wh-modal-body').html('<div class="alert alert-danger">' + res.message + '</div>');
                        return;
                    }
                    if (res.is_cancelled) {
                        $('#btn-confirm-warehouse').hide();
                        $('#wh-modal-body').html('<div class="alert alert-danger"><i class="fa fa-ban"></i> <strong>Đơn hàng đã bị hủy</strong>, không thể duyệt kho!</div>');
                        return;
                    }
                    renderWarehouseModal(res.items, res.warehouse_status);
                })
                .fail(function() {
                    $('#wh-modal-body').html('<div class="alert alert-danger">Lỗi kết nối</div>');
                });
        }

        function renderWarehouseModal(items, warehouseStatus) {
            if (warehouseStatus == 1) {
                $('#btn-confirm-warehouse').hide();
            } else {
                $('#btn-confirm-warehouse').show();
            }

            if (!items || items.length === 0) {
                $('#wh-modal-body').html('<div class="alert alert-warning">Đơn hàng này không có sản phẩm hoặc không thể đọc chi tiết.</div>');
                return;
            }

            var html = '';
            var allocationData = {};

            items.forEach(function(item, idx) {
                var stockBadge = item.enough_stock
                    ? '<span class="label label-success">Đủ kho (' + item.total_stock + ')</span>'
                    : '<span class="label label-danger">Thiếu kho (tồn: ' + item.total_stock + ', cần: ' + item.qty_ordered + ')</span>';

                html += '<div class="panel panel-default" data-qty-ordered="' + item.qty_ordered + '" style="margin-bottom:10px">';
                html += '<div class="panel-heading" style="padding:8px 12px"><strong>' + (item.product_code ? '['+item.product_code+'] ' : '') + item.product_name + '</strong>';
                html += ' &nbsp; Cần: <b>' + item.qty_ordered + '</b>';
                html += ' &nbsp; ' + stockBadge + '</div>';
                html += '<div class="panel-body" style="padding:10px">';

                if (!item.lots || item.lots.length === 0) {
                    html += '<div class="alert alert-warning" style="margin:0">Không có lô kho nào còn hàng cho sản phẩm này.</div>';
                } else {
                    html += '<table class="table table-bordered table-condensed" style="margin:0">';
                    html += '<thead><tr>';
                    html += '<th>Mã lô</th><th>Ngày nhập</th><th>Nhà cung cấp</th><th class="text-center">Tồn lô</th><th class="text-center" style="width:100px">SL xuất</th>';
                    html += '</tr></thead><tbody>';

                    // Build allocation map
                    var allocMap = {};
                    if (item.allocation) {
                        item.allocation.forEach(function(a) { allocMap[a.detail_id] = a.suggested_qty; });
                    }

                    item.lots.forEach(function(lot) {
                        var suggested = allocMap[lot.detail_id] || 0;
                        var rowClass  = suggested > 0 ? 'success' : '';
                        html += '<tr class="' + rowClass + '">';
                        html += '<td>' + lot.import_code + '</td>';
                        html += '<td>' + (lot.import_date ? lot.import_date.substring(0,10) : '') + '</td>';
                        html += '<td>' + (lot.supplier_name || '') + '</td>';
                        html += '<td class="text-center">' + lot.remaining_qty + '</td>';
                        html += '<td class="text-center">';
                        html += '<input type="number" min="0" max="' + lot.remaining_qty + '" value="' + suggested + '"';
                        html += ' class="form-control input-sm wh-qty-input" style="width:70px;display:inline-block"';
                        html += ' data-detail-id="' + lot.detail_id + '" data-max="' + lot.remaining_qty + '">';
                        html += '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody>';
                    // Summary row
                    html += '<tfoot><tr><td colspan="4" class="text-right"><b>Tổng cần xuất:</b></td>';
                    html += '<td class="text-center"><b class="wh-total-allocated" data-idx="'+idx+'">0</b> / ' + item.qty_ordered + '</td></tr></tfoot>';
                    html += '</table>';
                }
                html += '</div></div>';
            });

            $('#wh-modal-body').html(html);

            // Tính tổng live khi nhập
            updateAllTotals();
            $('#wh-modal-body').on('input', '.wh-qty-input', function() {
                var $input = $(this);
                var max = parseInt($input.data('max')) || 0;
                var val = parseInt($input.val()) || 0;
                if (val > max) { $input.val(max); val = max; }
                updateAllTotals();
            });
        }

        function updateAllTotals() {
            var isValid = true;
            // Group inputs by their panel (product)
            $('#wh-modal-body .panel').each(function(idx) {
                var total = 0;
                var needed = parseInt($(this).data('qty-ordered')) || 0;
                $(this).find('.wh-qty-input').each(function() {
                    total += parseInt($(this).val()) || 0;
                });
                
                var $totalText = $(this).find('.wh-total-allocated');
                $totalText.text(total);
                
                if (total !== needed) {
                    isValid = false;
                    $totalText.css('color', 'red');
                } else {
                    $totalText.css('color', '#3c763d');
                }
            });
            $('#btn-confirm-warehouse').prop('disabled', !isValid);
        }

        function confirmWarehouseApprove() {
            var allocation = [];
            $('#wh-modal-body .wh-qty-input').each(function() {
                var qty = parseInt($(this).val()) || 0;
                if (qty > 0) {
                    allocation.push({
                        detail_id: $(this).data('detail-id'),
                        qty_take:  qty
                    });
                }
            });

            if (allocation.length === 0) {
                alert_float('error', 'Vui lòng nhập số lượng xuất kho cho ít nhất 1 lô');
                return;
            }

            if ($('#btn-confirm-warehouse').prop('disabled')) {
                return;
            }

            $('#btn-confirm-warehouse').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang xử lý...');

            $.ajax({
                url: 'admin/transaction/approveWarehouse/' + _currentWarehouseId,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    allocation: allocation,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            }).done(function(res) {
                if (res.result) {
                    alert_float('success', res.message);
                    $('#modal-warehouse-approve').modal('hide');
                    oTable.draw('page');
                } else {
                    alert_float('error', res.message);
                }
            }).fail(function() {
                alert_float('error', 'Lỗi kết nối');
            }).always(function() {
                $('#btn-confirm-warehouse').prop('disabled', false).html('<i class="fa fa-check"></i> Xác nhận duyệt kho');
            });
        }
        function cancelWarehouseApprove(transactionId) {
            if (confirm("Thao tác này sẽ hoàn trả lại số lượng tồn kho cho các lô đã trừ. Bạn có chắc chắn muốn bỏ duyệt kho?")) {
                $.ajax({
                    url: 'admin/transaction/cancelWarehouseApprove/' + transactionId,
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }
                }).done(function(res) {
                    if (res.result) {
                        alert_float('success', res.message);
                        oTable.draw('page');
                    } else {
                        alert_float('error', res.message);
                    }
                }).fail(function() {
                    alert_float('error', 'Lỗi kết nối khi hủy duyệt kho');
                });
            }
        }
    </script>
@endsection
