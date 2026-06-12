@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default waves-effect waves-light" href="admin/promotion/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('dt_promotion')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/promotion/list">{{lang('dt_promotion')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs">
                <li class="H-search active"><a data-toggle="tab" data-id="0">{{lang('all')}} (<b class="count_all">0</b>)</a></li>
                <li class="H-search"><a data-toggle="tab" data-id="1">{{lang('dt_coming_soon_new')}}  (<b class="count_1">0</b>)</a></li>
                <li class="H-search"><a data-toggle="tab" data-id="2">{{lang('dt_applying')}}   (<b class="count_2">0</b>)</a></li>
                <li class="H-search"><a data-toggle="tab" data-id="3">{{lang('dt_expired')}}   (<b class="count_3">0</b>)</a></li>
            </ul>
            <span class="group_search">
                <input type="hidden" name="status_search" id="status_search" value="0">
            </span>
            <div class="card-box table-responsive">
                <table id="table_promotion" class="table table-bordered table_promotion">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_image')}}</th>
                        <th class="text-center">{{lang('dt_code_promotion')}}</th>
                        <th class="text-center">{{lang('dt_name_promotion')}}</th>
                        <th class="text-center">{{lang('dt_type_promotion')}}</th>
                        <th class="text-center">Thời gian</th>
                        <th class="text-center">{{lang('dt_date_start')}}</th>
                        <th class="text-center">{{lang('dt_date_end')}}</th>
                        <th class="text-center">{{lang('dt_detail')}}</th>
                        <th class="text-center">{{lang('dt_status')}}</th>
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
            'status_search' : 'input[name="status_search"]',
        };
        $('.H-search').click(function() {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })
        var oTable;
        oTable = InitDataTable('#table_promotion', 'admin/promotion/getList', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/promotion/getList",
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
                {data: 'image', name: 'image'},
                {data: 'code', name: 'code'},
                {data: 'name', name: 'name'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'type', name: 'type'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'indefinite', name: 'indefinite'},
                {data: 'date_start', name: 'date_start'},
                {data: 'date_end', name: 'date_end'},
                {data: 'detail', name: 'detail',width: '150px'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'active', name: 'active'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "120px" },

            ]
        });
        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });
        $('#table_promotion').on('draw.dt', function () {
            getCountAll();
        });
        function getCountAll() {
            var data = {};
            $.each(fnserverparams, function(filterIndex, filterItem) {
                data[filterIndex] = $(filterItem).val();
            });
            $.ajax({
                url: 'admin/promotion/countAll',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: data
            })
                .done(function (response) {
                    var total = 0;
                    if(response.arr.length > 0){
                        $.each(response.arr, function(index, value) {
                            $(`.count_${value.id}`).text(formatNumber(value.count));
                            total += parseFloat(value.count);
                        })
                    }
                    $(`.count_all`).text(formatNumber(total));
                })
                .fail(function () {

                });
            return false;
        }
    </script>
@endsection
