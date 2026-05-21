@extends('admin.layouts.index')
@section('content')
    <style>
        .inline-flex {
            display: inline-flex !important;
        }

        .btn-cancel {
            background-color: #6f6c6c !important;
            border: 1px solid #c1c1c1 !important;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/video/detail_elearning/0">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('elearning')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/video/elearning">{{lang('elearning')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <span class="group_search">
                <input type="hidden" name="status_search" id="status_search" value="0">
            </span>
            <div class="card-box table-responsive">
                <table id="table_elearning" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('c_title_elearning')}}</th>
                        <th class="text-center">{{lang('c_video_trailer')}}</th>
                        <th class="text-center">{{lang('c_price')}}</th>
                        <th class="text-center">{{lang('c_total_duration_video')}}</th>
                        <th class="text-center">{{lang('count_video')}}</th>
                        <th class="text-center">{{lang('count_user_unlock')}}</th>
                        <th class="text-center">{{lang('is_check_new')}}</th>
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
        oTable = InitDataTable('#table_elearning', 'admin/video/getElearning', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/video/getElearning",
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
                {data: 'title', name: 'title'},
                {data: 'video_trailer', name: 'video_trailer',width: "150px"},
                {data: 'price', name: 'price'},
                {data: 'duration', name: 'duration'},
                {data: 'count_video', name: 'count_video'},
                {data: 'unlock', name: 'unlock'},
                {data: 'is_check_new', name: 'active',width: "100px" },
                {data: 'active', name: 'active',width: "100px" },
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
