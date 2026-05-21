@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default waves-effect waves-light" id="btn-open-modal-shift">
                    <i class="fa fa-plus"></i> Thêm thiết lập
                </a>
            </div>
            <h4 class="page-title text-capitalize">{{ $title }}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{ lang('dt_index') }}</a></li>
                <li class="active">{{ $title }}</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <table id="table_work_shifts" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{ lang('dt_stt') }}</th>
                        <th class="text-center">Ngày làm việc</th>
                        <th class="text-center">Thời gian bắt đầu</th>
                        <th class="text-center">Thời gian kết thúc</th>
                        <th class="text-center">{{ lang('dt_active') }}</th>
                        <th class="text-center">{{ lang('dt_actions') }}</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Thiết lập ca làm việc -->
    <div class="modal fade" id="modal-work-shift" tabindex="-1" role="dialog" aria-labelledby="modal-work-shift-label">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="color: #000; border-radius: 4px 4px 0 0;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#000; opacity:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modal-work-shift-label" style="font-weight: 600; letter-spacing: 0.5px;">
                        <i class="fa fa-calendar"></i> THÊM THIẾT LẬP NGÀY LÀM VIỆC
                    </h4>
                </div>
                <div class="modal-body">
                    <form id="form-work-shift">
                        @csrf
                        <div class="form-group">
                            <label>Chọn ngày <small class="text-muted">(để trống = áp dụng tất cả ngày)</small></label>
                            <select id="filter-day" class="form-control">
                                <option value="">Chọn ngày</option>
                                @foreach($days as $idx => $dayName)
                                    <option value="{{ $idx }}">{{ $dayName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tbl-shift-rows">
                                <thead style="background: #e8f5e9;">
                                <tr>
                                    <th class="text-center" style="color: #5a7d0a; width: 60px;">STT</th>
                                    <th class="text-center" style="color: #5a7d0a;">Ngày làm việc</th>
                                    <th class="text-center" style="color: #5a7d0a;">Thời gian bắt đầu</th>
                                    <th class="text-center" style="color: #5a7d0a;">Thời gian kết thúc</th>
                                </tr>
                                </thead>
                                <tbody id="shift-table-body">
                                @foreach($days as $idx => $dayName)
                                    <tr class="shift-row" data-day="{{ $idx }}">
                                        <td class="text-center">{{ $idx + 1 }}</td>
                                        <td>{{ $dayName }}</td>
                                        <td>
                                            <input type="time" name="shifts[{{ $idx }}][start_time]"
                                                   class="form-control shift-start"
                                                   value="10:30"
                                                   data-day="{{ $idx }}">
                                        </td>
                                        <td>
                                            <input type="time" name="shifts[{{ $idx }}][end_time]"
                                                   class="form-control shift-end"
                                                   value="20:30"
                                                   data-day="{{ $idx }}">
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">THOÁT</button>
                    <button type="button" class="btn btn-success" id="btn-save-shifts" style="background:#5a7d0a; border-color:#5a7d0a;">
                        <i class="fa fa-save"></i> SAVE
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    // ============ DataTable ============
    var oTable;
    oTable = InitDataTable('#table_work_shifts', 'admin/set_date/getTable', {
        order: [[0, 'asc']],
        responsive: true,
        ajax: {
            type: 'POST',
            url: 'admin/set_date/getTable',
            dataSrc: function (json) { return json.data; }
        },
        columnDefs: [
            { render: function (data) { return '<div class="text-center">' + data + '</div>'; }, data: 'DT_RowIndex', name: 'DT_RowIndex', width: '60px' },
            { data: 'day_name', name: 'day_name' },
            { data: 'start_time', name: 'start_time', render: function (d) { return '<div class="text-center">' + (d || '-') + '</div>'; } },
            { data: 'end_time',   name: 'end_time',   render: function (d) { return '<div class="text-center">' + (d || '-') + '</div>'; } },
            { data: 'active', name: 'active', render: function (data, type, row) {
                let labelClass = data == 1 ? 'label-success' : 'label-default';
                let labelText = data == 1 ? 'Active' : 'Inactive';
                return '<span class="label ' + labelClass + ' btn-status" style="cursor:pointer" data-id="' + row.id + '" data-status="' + data + '">' + labelText + '</span>';
            }, width: '90px' },
            { data: 'options', name: 'options', orderable: false, searchable: false, width: '130px' },
        ]
    });

    // ============ Mở modal & load dữ liệu hiện tại ============
    $('#btn-open-modal-shift').on('click', function () {
        // Load current shifts từ server để fill vào form
        $.get('admin/set_date/getCurrentShifts', function (res) {
            if (res.result && res.data) {
                res.data.forEach(function (shift) {
                    var row = $('.shift-row[data-day="' + shift.day_of_week + '"]');
                    row.find('.shift-start').val(shift.start_time || '10:30');
                    row.find('.shift-end').val(shift.end_time   || '20:30');
                    row.show(); // restore rows that might have been hidden
                });
            }
        });
        $('#modal-work-shift').modal('show');
    });

    // ============ Filter: chỉ hiện 1 ngày được chọn ============
    $('#filter-day').on('change', function () {
        var val = $(this).val();
        if (val === '') {
            $('.shift-row').show();
        } else {
            $('.shift-row').hide();
            $('.shift-row[data-day="' + val + '"]').show();
        }
    });

    // ============ Xóa hàng khỏi bảng (ẩn + đánh dấu bỏ qua) ============
    $(document).on('click', '.btn-remove-row', function () {
        var day = $(this).data('day');
        var row = $('.shift-row[data-day="' + day + '"]');
        row.hide();
        row.find('input').attr('disabled', true);
    });

    // ============ SAVE ============
    $('#btn-save-shifts').on('click', function () {
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang lưu...');

        // Chỉ bao gồm những row đang hiển thị (không bị xóa)
        var formData = {};
        $('.shift-row:visible').each(function () {
            var day   = $(this).data('day');
            var start = $(this).find('.shift-start').val();
            var end   = $(this).find('.shift-end').val();
            if (start && end) {
                formData['shifts[' + day + '][start_time]'] = start;
                formData['shifts[' + day + '][end_time]']   = end;
                formData['shifts[' + day + '][active]']     = 1;
            }
        });

        formData['_token'] = $('meta[name=csrf-token]').attr('content') || '{{ csrf_token() }}';

        $.post('admin/set_date/submit', formData, function (res) {
            if (res.result) {
                toastr.success(res.message || 'Lưu thành công!');
                $('#modal-work-shift').modal('hide');
                oTable.ajax.reload();
            } else {
                var msg = Array.isArray(res.message) ? res.message.join('<br>') : res.message;
                toastr.error(msg);
            }
        }).fail(function () {
            toastr.error('Có lỗi xảy ra, vui lòng thử lại!');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> SAVE');
        });
    });

    // ============ Đổi trạng thái ============
    $(document).on('click', '.btn-status', function () {
        var id = $(this).data('id');
        var status = $(this).data('status');

        $.get('admin/set_date/changeStatus/' + id, {status: status}, function (res) {
            if (res.result) {
                toastr.success(res.message);
                oTable.ajax.reload(null, false);
            } else {
                toastr.error(res.message);
            }
        });
    });
</script>
@endsection
