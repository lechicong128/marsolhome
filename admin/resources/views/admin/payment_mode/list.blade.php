@extends('admin.layouts.index')
@section('page_title', lang('dt_payment_mode'))
@section('content')
<!-- Page-Title -->
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">{{lang('dt_payment_mode')}}</h1>
    </div>
    <div class="mod-page-actions">
        <a href="admin/payment_mode/detail" class="mod-btn mod-btn-primary dt-modal">
            <i class="fa fa-plus"></i> Thêm phương thức thanh toán
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
        <table id="table_payment_mode" class="table">
            <thead>
                <tr>
                    <th class="text-center">{{lang('dt_stt')}}</th>
                    <th class="text-center">{{lang('dt_image')}}</th>
                    <th class="text-center">{{lang('dt_name_payment_mode')}}</th>
                    <th class="text-center">{{lang('dt_type')}}</th>
                    <th class="text-center">{{lang('dt_note')}}</th>
                    <th class="text-center">{{lang('dt_status')}}</th>
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
        oTable = InitDataTable('#table_payment_mode', 'admin/payment_mode/getPaymentMode', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/payment_mode/getPaymentMode",
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
                {data: 'image', name: 'image',width: "150px"},
                {data: 'name', name: 'name',width: "200px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'type', name: 'type',width: "120px"},
                {data: 'note', name: 'note'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'active', name: 'active',width: "80px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
