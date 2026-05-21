@extends('admin.layouts.index')
@section('page_title', lang('dt_department'))
@section('content')
    <!-- Page-Title -->
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">{{lang('dt_department')}}</h1>
    </div>
    <div class="mod-page-actions">
        <a href="admin/department/detail" class="mod-btn mod-btn-primary dt-modal">
            <i class="fa fa-plus"></i> Thêm phòng ban
        </a>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="ul-toolbar">
    <div class="ul-search">
        <i class="fa fa-search"></i>
        <input type="text" id="ul-search-input" placeholder="Tìm theo mã,tên...">
    </div>
    <div class="dt-buttons btn-group"><a class="btn btn-default btn-default-dt-options btn-dt-reload"><span><i class="fa fa-refresh"></i></span></a></div>
</div>

<!-- Table Card -->
<div class="ul-card">
    <!-- Table -->
    <div class="table-responsive">
        <table id="table_department" class="table">
            <thead>
                <tr>
                <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_code_department')}}</th>
                        <th class="text-center">{{lang('dt_name_department')}}</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection
@section('script')
    <script>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_department', 'admin/department/getDepartment', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/department/getDepartment",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'DT_RowIndex', name: 'DT_RowIndex',width: "80px" },
                {data: 'code', name: 'code'},
                {data: 'name', name: 'name'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
        var searchTimer;
        $('#ul-search-input').on('keyup', function() {
            clearTimeout(searchTimer);
            var val = this.value;
            searchTimer = setTimeout(function() {
                oTable.search(val).draw();
            }, 400);
        });
        $('.btn-dt-reload').click(function() {
            oTable.draw('page');
        });
    </script>
@endsection
