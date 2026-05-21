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
                   aria-expanded="false" href="admin/tag_product/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('dt_tag_product')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/settings?group={{$type}}">{{lang('dt_tag_product')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <table id="table_tag_product" class="table table-bordered sortableMain">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_tag_product_name')}}</th>
                        <th class="text-center">{{lang('dt_color')}}</th>
                        <th class="text-center">{{lang('dt_background')}}</th>
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
        };
        var oTable;
        oTable = InitDataTable('#table_tag_product', 'admin/tag_product/getTable', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/tag_product/getTable",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                    d['_locale'] = '{{session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang)}}';
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            columnDefs: [

                {
                    "render": function(data, type, row) {
                        return `<div class="text-center ">${row['DT_RowIndex']}</div>`;
                    },
                    data: 'id', name: 'id',width: "80px"
                },
                {data: 'name', name: 'name'},
                {data: 'color', name: 'color'},
                {data: 'background', name: 'background'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });

    </script>
@endsection
