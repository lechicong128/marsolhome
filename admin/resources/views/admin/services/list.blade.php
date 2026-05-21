@extends('admin.layouts.index')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default waves-effect waves-light"
                   href="admin/services/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('c_list_services')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/services/list">{{lang('c_list_services')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box">
                <div class="row m-b-10">
                    <div class="col-sm-3">
                        <label>Danh mục dịch vụ</label>
                        <select id="filter_category" class="form-control">
                            <option value="">-- Tất cả danh mục --</option>
                            @foreach($category_services as $cat)
                                <option value="{{$cat->id}}">{{$cat->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            <div class="table-responsive">
                <table id="table_services" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_image')}}</th>
                        <th class="text-center">{{lang('c_code_services')}}</th>
                        <th class="text-center">{{lang('c_name_services')}}</th>
                        <th class="text-center">Danh mục</th>
                        <th class="text-center">{{lang('c_price_services')}}</th>
                        <th class="text-center">{{lang('c_discount_percent_services')}}</th>
                        <th class="text-center">{{lang('c_duration_minutes_services')}}</th>
                        <th class="text-center">{{lang('dt_active')}}</th>
                        <th class="text-center">Nổi bật</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var fnserverparams = {
            'filter_category': '#filter_category',
        };
        var oTable;
        oTable = InitDataTable('#table_services', 'admin/services/getTable', {
            'order': [[0, 'desc']],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/services/getTable",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) { return json.data; }
            },
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'DT_RowIndex', name: 'DT_RowIndex', width: "60px"
                },
                {data: 'image',            name: 'image',            width: "80px"},
                {data: 'code',             name: 'code',             width: "110px"},
                {data: 'name',             name: 'name'},
                {data: 'category_name',    name: 'category_name',    width: "150px"},
                {data: 'price',            name: 'price',            width: "120px"},
                {data: 'discount_percent', name: 'discount_percent', width: "100px"},
                {data: 'duration_minutes', name: 'duration_minutes', width: "100px"},
                {data: 'active',           name: 'active',           width: "90px"},
                {data: 'is_hot',           name: 'is_hot',           width: "90px"},
                {data: 'options',          name: 'options',          orderable: false, searchable: false, width: "130px"},
            ]
        });

        $('#filter_category').on('change', function () {
            oTable.draw();
        });
    </script>
@endsection
