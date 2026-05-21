@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/branch/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('c_list_branches')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/branch/list">{{lang('c_list_branches')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <table id="table_branches" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">Hình QR Fanpage</th>
                        <th class="text-center">Tên chi nhánh</th>
                        <th class="text-center">Số điện thoại</th>
                        <th class="text-center">Địa chỉ</th>
                        <th class="text-center">Link Google Map</th>
                        <th class="text-center">{{lang('dt_active')}}</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_branches', 'admin/branch/getTable', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/branch/getTable",
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
                {   "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'DT_RowIndex', name: 'DT_RowIndex', width: "60px" },
                {data: 'icon', name: 'icon', orderable: false, searchable: false, width: "70px", className: 'text-center'},
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone', width: "130px"},
                {data: 'address', name: 'address'},
                {data: 'map_link', name: 'map_link', render: function(data) {
                    if (data) {
                        return `<a href="${data}" target="_blank"><i class="fa fa-map-marker"></i> Xem bản đồ</a>`;
                    }
                    return '—';
                }},
                {data: 'active', name: 'active', width: "100px"},
                {data: 'options', name: 'options', orderable: false, searchable: false, width: "150px"},
            ]
        });
    </script>
@endsection
