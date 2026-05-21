@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{lang('dt_setting_customer_class')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/setting_customer_class/list">{{lang('dt_setting_customer_class')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <table id="table_setting_customer_class" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_icon')}}</th>
                        <th class="text-center">{{lang('dt_image')}}</th>
                        <th class="text-center">{{lang('dt_image_background')}}</th>
                        <th class="text-center">{{lang('dt_setting_customer_class_name')}}</th>
                        <th class="text-center">{{lang('dt_setting_customer_class_rule')}}</th>
                        <th class="text-center">{{lang('dt_setting_customer_class_benefits')}}</th>
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
        oTable = InitDataTable('#table_setting_customer_class', 'admin/setting_customer_class/getList', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/setting_customer_class/getList",
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
                    data: 'id', name: 'id',width: "50px"
                },
                {data: 'icon', name: 'icon',width: "150px"},
                {data: 'image', name: 'image',width: "150px"},
                {data: 'image_background', name: 'image_background',width: "250px"},
                {data: 'name', name: 'name',width: "200px"},
                {data: 'rule', name: 'rule',width: "200px"},
                {data: 'benefits', name: 'benefits'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
