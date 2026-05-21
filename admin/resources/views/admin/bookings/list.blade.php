@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">Lịch hẹn spa</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li class="active">Danh sách lịch hẹn</li>
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
                            <label>Trạng thái lịch hẹn</label>
                            <select id="filter_status" class="form-control">
                                <option value="">-- Tất cả --</option>
                                <option value="pending">Chờ xác nhận</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="cancelled">Đã huỷ</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Hình thức thanh toán</label>
                            <select id="filter_payment" class="form-control">
                                <option value="">-- Tất cả --</option>
                                <option value="transfer">Chuyển khoản</option>
                                <option value="pay_later">Thanh toán sau</option>
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
                <table id="table_bookings" class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="text-center">STT</th>
                        <th class="text-center">Mã lịch hẹn</th>
                        <th class="text-center">Khách hàng</th>
                        <th class="text-center">SĐT</th>
                        <th class="text-center">Chi nhánh</th>
                        <th class="text-center">Ngày hẹn</th>
                        <th class="text-center">Tổng tiền</th>
                        <th class="text-center">Hình thức TT</th>
                        <th class="text-center">TT Thanh toán</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center" style="width:130px">Tác vụ</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal xem chi tiết -->
    <div class="modal fade" id="modalViewBooking" tabindex="-1" role="dialog" aria-labelledby="modalViewBookingLabel">
        <div class="modal-dialog modal-lg" role="document" style="width:90%;max-width:1200px;">
            <div class="modal-content" id="modalViewBookingContent">
                <div class="modal-body text-center">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Hidden iframe for printing -->
    <iframe id="print_frame" name="print_frame" style="width:0;height:0;border:0;display:none;"></iframe>
@endsection

@section('script')
<script>
    function printBill(id) {
        var iframe = document.getElementById('print_frame');
        iframe.src = 'admin/booking/print-bill/' + id;
    }

    var fnserverparams = {
        'status'         : '#filter_status',
        'payment_method' : '#filter_payment',
        'booking_date'   : '#filter_date',
    };
    var oTable;

    oTable = InitDataTable('#table_bookings', 'admin/booking/getTable', {
        'order': [[0, 'desc']],
        'responsive': true,
        "ajax": {
            "type": "POST",
            "url":  "admin/booking/getTable",
            "data": function (d) {
                d.status         = $('#filter_status').val();
                d.payment_method = $('#filter_payment').val();
                d.booking_date   = $('#filter_date').val();
            },
            "dataSrc": function (json) { return json.data; }
        },
        columnDefs: [
            { data: 'DT_RowIndex',     name: 'DT_RowIndex',     width: '50px',
              render: function(data){ return '<div class="text-center">'+data+'</div>'; } },
            { data: 'booking_code',    name: 'booking_code', render: function(data, type, row) {
                return '<a href="javascript:void(0);" class="btn-view-booking" data-id="'+row.id+'"><b>'+data+'</b></a>';
            }},
            { data: 'customer_name',   name: 'customer_name' },
            { data: 'customer_phone',  name: 'customer_phone',  width: '120px' },
            { data: 'branch_name',     name: 'branch_name',     width: '140px' },
            { data: 'created_at',      name: 'created_at',      width: '110px', className: 'text-center',
              render: function(data){ return data ? data.substring(0, 10) : '—'; } },
            { data: 'total_amount',    name: 'total_amount',    width: '120px', className: 'text-right' },
            { data: 'payment_method_name', name: 'payment_method_name', width: '130px', className: 'text-center', defaultContent: '—' },
            { data: 'payment_status',  name: 'payment_status',  width: '130px', className: 'text-center' },
            { data: 'status',          name: 'status',          width: '130px', className: 'text-center' },
            { data: 'options',         name: 'options',         orderable: false, searchable: false, width: '130px', className: 'text-center' },
        ]
    });

    // Tìm kiếm / Đặt lại
    $('#btn_search').on('click', function () { oTable.draw(); });
    $('#btn_reset').on('click', function () {
        $('#filter_status, #filter_payment').val('');
        $('#filter_date').val('');
        oTable.draw();
    });

    // Mở modal xem chi tiết
    $(document).on('click', '.btn-view-booking', function () {
        var id = $(this).data('id');
        $('#modalViewBookingContent').html('<div class="modal-body text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $('#modalViewBooking').modal('show');
        $.get('admin/booking/view/' + id, function (html) {
            $('#modalViewBookingContent').html(html);
        });
    });

    // Đổi trạng thái từ modal
    $(document).on('click', '.btn-change-status', function () {
        var id     = $(this).data('id');
        var status = $(this).data('status');
        if (!confirm('Bạn có chắc muốn đổi trạng thái?')) return;
        $.post('admin/booking/changeStatus/' + id, { status: status, _token: $('meta[name=csrf-token]').attr('content') }, function (res) {
            if (res.result) {
                alert_float('success', res.message);
                oTable.draw();
                $('#modalViewBooking').modal('hide');
            } else {
                alert_float('error', res.message);
            }
        });
    });

    // Xóa booking
    $(document).on('click', '.btn-delete-booking', function () {
        var id = $(this).data('id');
        if (!confirm('Bạn có chắc muốn xóa lịch hẹn này?')) return;
        $.get('admin/booking/delete/' + id, function (res) {
            if (res.result) {
                alert_float('success', res.message);
                oTable.draw();
                $('#modalViewBooking').modal('hide');
            } else {
                // Có phiếu thanh toán đã duyệt → hiển thị cảnh báo rõ ràng
                alert_float('error', res.message);
            }
        });
    });
</script>
@endsection
