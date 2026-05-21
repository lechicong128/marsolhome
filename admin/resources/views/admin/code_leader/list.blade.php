@extends('admin.layouts.index')
@section('content')
    <style>
        .product-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .product-img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e9ecef;
        }
        .product-img img {
            border: 1px solid #eef1f5;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/code_leader/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/code_leader/list">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs">
                <li class="H-search active cursor"><a data-toggle="tab" data-id="-1">{{lang('all')}} (<b
                            class="count_all">0</b>)</a></li>
                <li class="H-search cursor"><a style="color: #ffbd4a !important;" data-toggle="tab" data-id="0">Chưa sử dụng
                        (<b class="count_0">0</b>)</a></li>
                <li class="H-search cursor"><a style="color: #81c868 !important;" data-toggle="tab" data-id="1">Đã sử dụng
                        (<b class="count_1">0</b>)</a></li>
            </ul>
            <span class="group_search">
                <input type="hidden" name="status_search" id="status_search" value="-1">
            </span>
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <label for="customer_search">{{lang('c_title_client')}}</label>
                        <select class="customer_search select2" id="customer_search" data-placeholder="Chọn ..."
                            name="customer_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_search">Thời gian</label>
                        <input class="form-control date_search" type="text" id="date_search" name="date_search" value=""
                            autocomplete="off">
                    </div>
                </div>
                <table id="table_code_leader" class="table table-bordered table_code_leader">
                    <thead>
                        <tr>
                            <th class="text-center">{{lang('dt_stt')}}</th>
                            <th class="text-center">Mã Leader</th>
                            <th class="text-center">{{lang('dt_status')}}</th>
                            <th class="text-center">{{lang('c_title_client')}}</th>
                            <th class="text-center">Ngày tạo</th>
                            <th class="text-center">Ngày sử dụng</th>
                            <th class="text-center">{{lang('dt_actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script>
        $(document).ready(function () {
            searchAjaxSelect2('#customer_search', 'admin/category/searchCustomer');
            searchAjaxSelect2('#add_client_customer_id', 'admin/category/searchCustomer');
            search_daterangepicker('date_search');
        });

        var fnserverparams = {
            'status_search': '#status_search',
            'customer_search': '#customer_search',
            'date_search': '#date_search',
            'date_search_end': '#date_search_end',
        };

        var oTable;
        oTable = InitDataTable('#table_code_leader', 'admin/code_leader/getList', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/code_leader/getList",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    if (json.result == false) {
                        alert_float('error', json.message);
                    }
                    return json.data;
                }
            },
            columnDefs: [
                { data: 'stt', name: 'stt', width: "50px", orderable: false },
                { data: 'code', name: 'code', width: "150px" },
                { data: 'status', name: 'status', width: "150px" },
                { data: 'customer', name: 'customer', orderable: false },
                { data: 'created_at', name: 'created_at', width: "150px" },
                { data: 'used_at', name: 'used_at', width: "150px" },
                { data: 'options', name: 'options', orderable: false, searchable: false, width: "150px" },
            ],
        });

        // Tab search
        $('.H-search').click(function () {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        });

        // Filter change
        $.each(fnserverparams, function (filterIndex, filterItem) {
            $('' + filterItem).on('change', function () {
                oTable.draw('page');
            });
        });

        // Count all
        $('#table_code_leader').on('draw.dt', function () {
            getCountAll();
        });

        function getCountAll() {
            var data = {};
            $.each(fnserverparams, function(filterIndex, filterItem) {
                data[filterIndex] = $(filterItem).val();
            });
            $.ajax({
                url: 'admin/code_leader/countAll',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: data
            })
            .done(function (response) {
                if(response.data && response.data.length > 0){
                    $.each(response.data, function(index, value) {
                        $(`.count_${value.status}`).text(formatNumber(value.count));
                    });
                }
                var total = response.total || 0;
                $(`.count_all`).text(formatNumber(total));
            })
            .fail(function () {});
            return false;
        }

    </script>
@endsection
