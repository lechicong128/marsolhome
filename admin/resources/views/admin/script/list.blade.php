@extends('admin.layouts.index')
@section('content')
    <style>
        .slick-track{
            margin-left: 0 !important;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal hide" data-toggle="dropdown"
                   aria-expanded="false" href="admin/script/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/script/list">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <span class="group_search">
                <input type="hidden" name="status_search" id="status_search" value="-1">
            </span>
            <div class="card-box table-responsive">
                <table id="table_script" class="table table-bordered table_script">
                    <thead>
                        <tr>
                            <th class="text-center">{{lang('dt_stt')}}</th>
                            <th class="text-center">{{lang('c_name_script')}}</th>
                            <th class="text-center">{{lang('c_icon')}}</th>
                            <th class="text-center">{{lang('dt_status')}}</th>
                            <th class="text-center">{{lang('c_status_default')}}</th>
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
        var fnserverparams = {
            'status_search': '#status_search',
        };
        var oTable;
        oTable = InitDataTable('#table_script', 'admin/script/getTable', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/script/getTable",
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
                        return `<div class="text-center">${row['DT_RowIndex']}</data>`;
                    },
                    data: 'id', name: 'id',width: "50px",orderable: true
                },
                {data: 'name', name: 'name',width: "120px" },
                {data: 'icon', name: 'icon',width: "150px" },
                {data: 'active', name: 'active',width: "150px"},
                {data: 'chat_default', name: 'chat_default',width: "150px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ],
        });

        $('.H-search').click(function() {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })


        $.each(fnserverparams, function(filterIndex, filterItem) {
            $(filterItem).on('change', function() {
                oTable.draw('page')
            });
        });
    </script>
@endsection
