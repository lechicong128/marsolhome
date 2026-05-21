@extends('admin.layouts.index')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">Thanh toán Spa</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li class="active">Thanh toán Spa</li>
            </ol>
        </div>
    </div>

    <!-- Filter -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Trạng thái duyệt</label>
                            <select id="filter_status" class="form-control">
                                <option value="">-- Tất cả --</option>
                                <option value="pending">Chờ duyệt</option>
                                <option value="approved">Đã duyệt</option>
                                <option value="rejected">Từ chối</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ngày hẹn</label>
                            <input type="date" id="filter_date" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button id="btn_search" class="btn btn-primary waves-effect waves-light">
                                    <i class="fa fa-search"></i> Tìm kiếm
                                </button>
                                <button id="btn_reset" class="btn btn-default waves-effect waves-light">
                                    <i class="fa fa-refresh"></i> Đặt lại
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <table id="table_payment_spa" class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="text-center" style="width:50px">STT</th>
                        <th class="text-center">Mã phiếu TT</th>
                        <th class="text-center">Mã lịch hẹn</th>
                        <th class="text-center">Khách hàng</th>
                        <th class="text-center">SĐT</th>
                        <th class="text-center">Chi nhánh</th>
                        <th class="text-center">Ngày hẹn</th>
                        <th class="text-center">Số tiền TT</th>
                        <th class="text-center">Trạng thái duyệt</th>
                        <th class="text-center">Thời điểm duyệt</th>
                        <th class="text-center">Ngày tạo</th>
                        <th class="text-center" style="width:80px">Tác vụ</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalViewPayment" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="modalViewPaymentContent">
                <div class="modal-body text-center" style="padding:40px">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    var oTable;
    oTable = InitDataTable('#table_payment_spa', 'admin/payment_spa/getTable', {
        'order': [[0, 'desc']],
        'responsive': true,
        "ajax": {
            "type": "POST",
            "url":  "admin/payment_spa/getTable",
            "data": function (d) {
                d.status       = $('#filter_status').val();
                d.booking_date = $('#filter_date').val();
            },
            "dataSrc": function (json) { return json.data; }
        },
        columnDefs: [
            { data: 'DT_RowIndex',    name: 'DT_RowIndex',    width: '50px',
              render: function(data){ return '<div class="text-center">'+data+'</div>'; } },
            { data: 'payment_code',   name: 'payment_code', render: function(data, type, row) { 
                return '<a href="javascript:void(0);" class="btn-view-payment" data-id="'+row.id+'"><b>'+data+'</b></a>'; 
            }},
            { data: 'booking_code',   name: 'booking_code' },
            { data: 'customer_name',  name: 'customer_name' },
            { data: 'customer_phone', name: 'customer_phone', width: '120px' },
            { data: 'branch_name',    name: 'branch_name',    width: '140px', defaultContent: '—' },
            { data: 'booking_date',   name: 'booking_date',   width: '100px', className: 'text-center' },
            { data: 'amount',              name: 'amount',              width: '120px', className: 'text-right' },
            { data: 'payment_method_name', name: 'payment_method_name', width: '140px', className: 'text-center', defaultContent: '—' },
            { data: 'status',              name: 'status',              width: '120px', className: 'text-center' },
            { data: 'approved_at',    name: 'approved_at',    width: '130px', className: 'text-center' },
            { data: 'created_at',     name: 'created_at',     width: '130px', className: 'text-center' },
            { data: 'options',        name: 'options',        orderable: false, searchable: false, width: '80px', className: 'text-center' },
        ]
    });

    $('#btn_search').on('click', function () { oTable.draw(); });
    $('#btn_reset').on('click', function () {
        $('#filter_status').val('');
        $('#filter_date').val('');
        oTable.draw();
    });

    // Mở modal
    $(document).on('click', '.btn-view-payment', function () {
        var id = $(this).data('id');
        $('#modalViewPaymentContent').html('<div class="modal-body text-center" style="padding:40px"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $('#modalViewPayment').modal('show');
        $.get('admin/payment_spa/view/' + id, function (html) {
            $('#modalViewPaymentContent').html(html);
        });
    });

    // Duyệt
    $(document).on('click', '.btn-approve-payment', function () {
        var id = $(this).data('id');
        if (!confirm('Xác nhận duyệt phiếu thanh toán này?')) return;
        $.post('admin/payment_spa/approve/' + id, { _token: $('meta[name=csrf-token]').attr('content') }, function (res) {
            if (res.result) { alert_float('success', res.message); oTable.draw(); $('#modalViewPayment').modal('hide'); }
            else { alert_float('error', res.message); }
        });
    });

    // Từ chối
    $(document).on('click', '.btn-reject-payment', function () {
        var id = $(this).data('id');
        if (!confirm('Từ chối phiếu thanh toán này?')) return;
        $.post('admin/payment_spa/reject/' + id, { _token: $('meta[name=csrf-token]').attr('content') }, function (res) {
            if (res.result) { alert_float('success', res.message); oTable.draw(); $('#modalViewPayment').modal('hide'); }
            else { alert_float('error', res.message); }
        });
    });
</script>
@endsection
