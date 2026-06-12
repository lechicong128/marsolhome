@extends('admin.layouts.index')
@section('page_title', $title)
@section('content')
<style>
    /* Prevent dropdown clipping in tables */
    .ul-card, 
    .ul-card .table-responsive {
        overflow: visible !important;
    }
</style>

<!-- Page-Title -->
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">{{ $title }}</h1>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="ul-toolbar">
    <div class="ul-search">
        <i class="fa fa-search"></i>
        <input type="text" id="ul-search-input" placeholder="Tìm theo số tờ, số thửa, chủ đất...">
    </div>
    <div class="dt-buttons btn-group">
        <a class="btn btn-default" href="admin/plandoffices/list">
            <span><i class="fa fa-arrow-left"></i> Quay lại danh sách</span>
        </a>
        <a class="btn btn-default btn-default-dt-options btn-dt-reload">
            <span><i class="fa fa-refresh"></i></span>
        </a>
    </div>
</div>

<!-- Table Card -->
<div class="ul-card">
    <div class="table-responsive">
        <table id="table_plandoffice_parcels" class="table">
            <thead>
                <tr>
                    <th class="text-center">{{lang('dt_stt')}}</th>
                    <th class="text-center">Số tờ</th>
                    <th class="text-center">Số thửa</th>
                    <th class="text-center">Diện tích (m²)</th>
                    <th class="text-center">Loại đất</th>
                    <th class="text-center">Công trình</th>
                    <th class="text-center">Tên chủ</th>
                    <th class="text-center">QH Chi tiết</th>
                    <th class="text-center">Mô tả thửa</th>
                    <th class="text-center">Tác vụ</th>
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
        oTable = InitDataTable('#table_plandoffice_parcels', 'admin/plandoffices/getParcels/{{ $plandoffice->id }}', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/plandoffices/getParcels/{{ $plandoffice->id }}",
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
                    data: 'DT_RowIndex', name: 'DT_RowIndex', width: "80px", orderable: false, searchable: false
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center font-bold">${data || '-'}</div>`;
                    },
                    data: 'so_to', name: 'so_to'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center font-bold">${data || '-'}</div>`;
                    },
                    data: 'so_thua', name: 'so_thua'
                },
                {data: 'dien_tich', name: 'dien_tich', width: "150px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data || '-'}</div>`;
                    },
                    data: 'loai_dat', name: 'loai_dat', width: "120px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data || '-'}</div>`;
                    },
                    data: 'cong_trinh', name: 'cong_trinh', width: "120px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div>${data || '-'}</div>`;
                    },
                    data: 'ten_chu', name: 'ten_chu'
                },
                {
                    data: 'loai_dat_quy_hoach', name: 'loai_dat_quy_hoach', width: "250px"
                },
                {
                    data: 'mo_ta_thua', name: 'mo_ta_thua', width: "200px"
                },
                {
                    data: 'options', name: 'options', orderable: false, searchable: false, width: "100px"
                }
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
