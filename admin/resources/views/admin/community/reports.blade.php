@extends('admin.layouts.index')
@section('content')
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">{{ $title }}</h4>
        <ol class="breadcrumb">
            <li><a href="admin/dashboard">{{ lang('dt_index') }}</a></li>
            <li><a href="admin/community/list">Cộng đồng</a></li>
            <li class="active">Báo cáo vi phạm</li>
        </ol>
    </div>
</div>

{{-- Bộ lọc --}}
<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Loại báo cáo</label>
                        <select id="filter_type" class="form-control">
                            <option value="">-- Tất cả --</option>
                            <option value="post">Bài viết</option>
                            <option value="comment">Bình luận</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Lý do vi phạm</label>
                        <select id="filter_violation" class="form-control">
                            <option value="">-- Tất cả --</option>
                            @foreach($violations as $v)
                                <option value="{{ $v['id'] }}">{{ $v['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Ngày báo cáo</label>
                        <input type="text" id="filter_date" name="date_search" class="form-control datepicker_range" placeholder="dd/mm/yyyy - dd/mm/yyyy" autocomplete="off">
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

{{-- Bảng --}}
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive">
            <table id="table_reports" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th class="text-center" style="width:50px">STT</th>
                    <th class="text-center" style="width:110px">Loại</th>
                    <th>Người báo cáo</th>
                    <th>Nội dung bị báo cáo</th>
                    <th class="text-center" style="width:130px">Lý do</th>
                    <th>Ghi chú</th>
                    <th class="text-center" style="width:130px">Thời gian</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    var oTable;
    oTable = InitDataTable('#table_reports', 'admin/community/getReportList', {
        order: [[0, 'desc']],
        responsive: true,
        ajax: {
            type: 'POST',
            url: 'admin/community/getReportList',
            data: function (d) {
                d.type         = $('#filter_type').val();
                d.violation_id = $('#filter_violation').val();
                d.date_search  = $('#filter_date').val();
            },
            dataSrc: function (json) {
                if (json.result == false) {
                    alert_float('error', json.message);
                }
                return json.data;
            }
        },
        columnDefs: [
            { data: 'stt',            name: 'stt',            width: '50px',  orderable: false, searchable: false },
            { data: 'type_label',     name: 'type_label',     width: '110px', orderable: false, searchable: false },
            { data: 'reporter_info',  name: 'reporter_info',  orderable: false, searchable: false },
            { data: 'target_short',   name: 'target_short',   orderable: false, searchable: false },
            { data: 'violation_label',name: 'violation_label',width: '130px', orderable: false, searchable: false },
            { data: 'note_short',     name: 'note_short',     orderable: false, searchable: false },
            { data: 'created_at',     name: 'created_at',     width: '130px', className: 'text-center' },
        ]
    });

    $('#btn_search').on('click', function () { oTable.draw(); });
    $('#btn_reset').on('click', function () {
        $('#filter_type').val('');
        $('#filter_violation').val('');
        $('#filter_date').val('');
        oTable.draw();
    });

    // Daterangepicker cho filter ngày (nếu có)
    if (typeof $.fn.daterangepicker !== 'undefined') {
        $('#filter_date').daterangepicker({
            buttonClasses: ['btn', 'btn-sm'],
            applyClass: 'btn-default',
            cancelClass: 'btn-white',
            autoUpdateInput: false,
            locale: { format: 'DD/MM/YYYY', separator: ' -', cancelLabel: 'Xoá' }
        }, function (start, end) {
            $('#filter_date').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
        });
        $('#filter_date').on('cancel.daterangepicker', function () { $(this).val(''); });
    }
</script>
@endsection
