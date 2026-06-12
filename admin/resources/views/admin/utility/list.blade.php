@extends('admin.layouts.index')
@section('page_title', $title)
@section('content')
<!-- Page-Title -->
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">{{ $title }}</h1>
    </div>
    <div class="mod-page-actions">
        <a href="admin/utilities/detail" class="mod-btn mod-btn-primary dt-modal">
            <i class="fa fa-plus"></i> Thêm tiện ích bất động sản
        </a>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="ul-toolbar">
    <div class="ul-search">
        <i class="fa fa-search"></i>
        <input type="text" id="ul-search-input" placeholder="Tìm theo tên...">
    </div>
    <div class="dt-buttons btn-group"><a class="btn btn-default btn-default-dt-options btn-dt-reload"><span><i class="fa fa-refresh"></i></span></a></div>
</div>

<!-- Table Card -->
<div class="ul-card">
    <!-- Table -->
    <div class="table-responsive">
        <table id="table_utility" class="table">
            <thead>
                <tr>
                    <th class="text-center">{{lang('dt_stt')}}</th>
                    <th class="text-center">{{lang('Icon')}}</th>
                    <th class="text-center">{{lang('Tên tiện ích')}}</th>
                    <th class="text-center">{{lang('Đơn vị')}}</th>
                    <th class="text-center">{{lang('Loại nhập liệu')}}</th>
                    <th class="text-center">{{lang('Áp dụng cho')}}</th>
                    <th class="text-center">{{lang('Trạng thái')}}</th>
                    <th class="text-center">{{lang('Hiển thị trên danh sách')}}</th>
                    <th class="text-center">{{lang('Áp dụng bộ lọc')}}</th>
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
        oTable = InitDataTable('#table_utility', 'admin/utilities/getUtilities', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/utilities/getUtilities",
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
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'DT_RowIndex', name: 'DT_RowIndex', width: "80px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'icon', name: 'icon', width: "120px"
                },
                {data: 'name', name: 'name'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data ? data : '-'}</div>`;
                    },
                    data: 'unit', name: 'unit', width: "100px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'input_type', name: 'input_type', width: "150px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'transaction_type_text', name: 'transaction_type', width: "150px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'active', name: 'active', width: "120px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'show_list', name: 'show_list', width: "80px"
                },
                 {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'show_filter', name: 'show_filter', width: "80px"
                },
                {data: 'options', name: 'options', orderable: false, searchable: false, width: "150px"}
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
