@extends('admin.layouts.index')
@section('content')
    <style>
        .inline-flex {
            display: inline-flex !important;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/banner/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('dt_banner')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/settings?group=banner">{{lang('dt_banner')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
     <!--        <ul class="nav nav-tabs">
                <li class="H-search active cursor">
                    <a style=";white-space: nowrap;" data-toggle="tab"
                       data-id="0">{{lang('banner_website')}}
                    </a>
                </li>
                <li class="H-search cursor">
                    <a style=";white-space: nowrap;" data-toggle="tab"
                       data-id="1">{{lang('banner_app')}}
                    </a>
                </li>
            </ul> -->
            <span class="group_search">
                <input type="hidden" name="status_search" id="status_search" value="1">
            </span>
            <div class="card-box table-responsive">
                <table id="table_banner" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_image')}}</th>
                        <th class="text-center">{{lang('dt_name_banner')}}</th>
                        <th class="text-center">{{lang('dt_title_banner')}}</th>
                        <th class="text-center">{{lang('dt_content_banner')}}</th>
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
        var fnserverparams = {
            'status_search': '#status_search'
        };
        $('.H-search').click(function() {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })

        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        var oTable;
        oTable = InitDataTable('#table_banner', 'admin/banner/getBanner', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/banner/getBanner",
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
                {data: 'image', name: 'image',width: "250px"},
                {data: 'name', name: 'name'},
                {data: 'title', name: 'title'},
                {data: 'content', name: 'content'},
                {data: 'active', name: 'active',width: "100px" },
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
