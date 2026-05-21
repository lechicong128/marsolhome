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
            <h4 class="page-title text-capitalize">{{lang('video_review')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/video/review">{{lang('video_review')}}</a></li>
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
                <table id="table_review" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('client_review')}}</th>
                        <th class="text-center">{{lang('c_name_video')}}</th>
                        <th class="text-center">{{lang('link_original_video')}}</th>
                        <th class="text-center">{{lang('link_video')}}</th>
                        <th class="text-center">{{lang('count_like')}}</th>
                        <th class="text-center">{{lang('count_share')}}</th>
                        <th class="text-center">{{lang('count_comment')}}</th>
                        <th class="text-center">{{lang('count_see')}}</th>
                        <th class="text-center">{{lang('evaluate')}}</th>
                        <th class="text-center">{{lang('relate_to_product')}}</th>
                        <th class="text-center">{{lang('dt_active')}}</th>
                        <th class="text-center">{{lang('Show ra trang chủ')}}</th>
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
        oTable = InitDataTable('#table_review', 'admin/video/getReview', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/video/getReview",
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
                {data: 'clients', name: 'clients'},
                {data: 'name', name: 'name'},
                {data: 'original_video', name: 'original_video',width: "150px"},
                {data: 'video', name: 'video'},
                {data: 'count_like', name: 'count_like'},
                {data: 'count_share', name: 'count_share'},
                {data: 'count_comment', name: 'count_comment'},
                {data: 'count_see', name: 'count_see'},
                {data: 'evaluate', name: 'evaluate'},
                {data: 'id_product', name: 'id_product'},
                {data: 'active', name: 'active',width: "100px" },
                {data: 'show_home', name: 'show_home',width: "100px" },
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
