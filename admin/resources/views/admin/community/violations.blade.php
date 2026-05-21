@extends('admin.layouts.index')
@section('content')
{{-- Page-Title --}}
<div class="row">
    <div class="col-sm-12">
        <div class="btn-group pull-right m-t-15">
            <button type="button" class="btn btn-primary waves-effect waves-light" id="btn-add-violation">
                <i class="fa fa-plus"></i> Thêm mới
            </button>
        </div>
        <h4 class="page-title text-capitalize">{{ $title }}</h4>
        <ol class="breadcrumb">
            <li><a href="admin/dashboard">{{ lang('dt_index') }}</a></li>
            <li><a href="admin/community/list">Cộng đồng</a></li>
            <li class="active">{{ $title }}</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive">
            <table id="table_violations" class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">{{ lang('dt_stt') }}</th>
                        <th>Lý do vi phạm</th>
                        <th class="text-center">{{ lang('dt_actions') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal thêm / sửa --}}
<div class="modal fade" id="modal-violation" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="modal-vio-title">Thêm lý do vi phạm</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Tên lý do <span class="text-danger">*</span></label>
                    <input type="text" id="vio-name-input" class="form-control" placeholder="VD: Nội dung phản cảm, Spam...">
                </div>
                <input type="hidden" id="vio-edit-id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary waves-effect waves-light" id="btn-save-violation">
                    <i class="fa fa-save"></i> Lưu
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    var _token  = $('meta[name=csrf-token]').attr('content');
    var oTable;

    oTable = InitDataTable('#table_violations', 'admin/community/getViolationTable', {
        order: [[0, 'desc']],
        responsive: true,
        ajax: {
            type: 'POST',
            url: 'admin/community/getViolationTable',
            dataSrc: function (json) { return json.data; }
        },
        columnDefs: [
            {
                data: 'DT_RowIndex', name: 'DT_RowIndex', width: '80px',
                render: function (data) { return '<div class="text-center">' + data + '</div>'; }
            },
            { data: 'name', name: 'name' },
            { data: 'options', name: 'options', orderable: false, searchable: false, width: '160px' }
        ]
    });

    // Mở modal thêm mới
    $('#btn-add-violation').on('click', function () {
        $('#modal-vio-title').text('Thêm lý do vi phạm');
        $('#vio-name-input').val('');
        $('#vio-edit-id').val('');
        $('#modal-violation').modal('show');
    });

    // Mở modal sửa (delegate từ DataTable)
    $(document).on('click', '.btn-edit-vio', function () {
        var id   = $(this).data('id');
        var name = $(this).data('name');
        $('#modal-vio-title').text('Sửa lý do vi phạm');
        $('#vio-name-input').val(name);
        $('#vio-edit-id').val(id);
        $('#modal-violation').modal('show');
    });

    // Lưu
    $('#btn-save-violation').on('click', function () {
        var name = $('#vio-name-input').val().trim();
        var id   = $('#vio-edit-id').val();
        if (!name) { alert_float('error', 'Vui lòng nhập tên lý do vi phạm.'); return; }

        var url = id ? 'admin/community/violations/update/' + id : 'admin/community/violations/store';
        $.post(url, { name: name, _token: _token }, function (res) {
            if (res.result) {
                alert_float('success', res.message);
                $('#modal-violation').modal('hide');
                oTable.draw();
            } else {
                alert_float('error', res.message);
            }
        }).fail(function () { alert_float('error', 'Có lỗi xảy ra!'); });
    });

    // Xoá
    $(document).on('click', '.btn-del-vio', function () {
        var id   = $(this).data('id');
        var name = $(this).data('name');
        if (!confirm('Bạn có chắc muốn xoá lý do "' + name + '" không?')) return;
        $.get('admin/community/violations/delete/' + id, function (res) {
            if (res.result) {
                alert_float('success', res.message);
                oTable.draw();
            } else {
                alert_float('error', res.message);
            }
        }).fail(function () { alert_float('error', 'Có lỗi xảy ra!'); });
    });

    // Enter để lưu
    $('#vio-name-input').on('keydown', function (e) {
        if (e.key === 'Enter') $('#btn-save-violation').click();
    });
</script>
@endsection
