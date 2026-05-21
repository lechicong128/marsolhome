@extends('admin.layouts.index')
@section('content')
    <style>
        .star{
            color: #fbbf24;
        }
        .status_now_2 {
            background: #2DD4BF;
            color: white;
        }
        .status_now_1 {
            background: #F47690;
            color: white;
        }
        .status_now_3 {
            background: #E5E7EB;
            color: #6B7280;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a  class="btn btn-default" href="admin/event_articles/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('c_list_event_articles')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/event_articles/list">{{lang('c_list_event_articles')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">

            <div class="card-box table-responsive">
                <table id="table_event_articles" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_image')}}</th>
                        <th class="text-center">{{lang('c_code_event_articles')}}</th>
                        <th class="text-center">{{lang('c_name_event_articles')}}</th>
                        <th class="text-center">{{lang('c_slug_event_articles')}}</th>
                        <th class="text-center">{{lang('dt_active')}}</th>
                        <th class="text-center">{{lang('c_event_hot')}}</th>
                        <th class="text-center">{{lang('status_now')}}</th>
                        <th class="text-center">{{lang('c_count_view')}}</th>
                        <th class="text-center">{{lang('c_count_join')}}</th>
                        <th class="text-center">{{lang('date_create')}}</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
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
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_event_articles', 'admin/event_articles/getTable', {
            'order': [
                [10, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/event_articles/getTable",
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
                    data: 'DT_RowIndex', name: 'DT_RowIndex',width: "80px" },
                {data: 'image', name: 'image',width: "100px"},
                {data: 'code', name: 'code', width: "100px"},
                {data: 'name', name: 'name'},
                {data: 'slug', name: 'slug', width: "100px"},
                {data: 'active', name: 'active',width: "80px"},
                {data: 'is_hot', name: 'is_hot',width: "80px"},
                {data: 'status_now', name: 'status_now',width: "80px"},
                {data: 'count_view', name: 'count_view',width: "80px"},
                {data: 'count_join', name: 'count_join',width: "80px"},
                {data: 'created_at', name: 'created_at',width: "80px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
