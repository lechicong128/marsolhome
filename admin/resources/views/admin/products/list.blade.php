@extends('admin.layouts.index')
@section('content')
    <style>
        .star{
            color: #fbbf24;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a  class="btn btn-default" href="admin/products/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('c_list_products')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/products/list">{{lang('c_list_products')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">

            <div class="card-box table-responsive">
                <table id="table_products" class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">{{lang('dt_stt')}}</th>
                            <th class="text-center">{{lang('dt_image')}}</th>
                            <th class="text-center">{{lang('c_code_products')}}</th>
                            <th class="text-center">{{lang('c_name_products')}}</th>
                            <th class="text-center">{{lang('c_category_products')}}</th>
                            <th class="text-center">{{lang('dt_price')}}</th>
                            <th class="text-center">{{lang('c_slug_products')}}</th>
                            <th class="text-center">{{lang('c_average_star')}}</th>
{{--                            <th class="text-center">{{lang('c_date_end_promotion')}}</th>--}}
{{--                            <th class="text-center">{{lang('c_count_quantity_join')}}</th>--}}
                            <th class="text-center">{{lang('dt_active')}}</th>
{{--                            <th class="text-center">{{lang('c_is_use_a_trial')}}</th>--}}
                            <th class="text-center">{{lang('dt_product_is_hot')}}</th>
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
        oTable = InitDataTable('#table_products', 'admin/products/getTable', {
            'order': [
                [7, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/products/getTable",
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
                {data: 'category', name: 'category'},
                {data: 'price', name: 'price'},
                {data: 'slug', name: 'slug'},
                {data: 'average_star', name: 'average_star'},
                // {data: 'date_end_promotion', name: 'date_end_promotion',width: "150px"},
                // {data: 'count_join', name: 'count_join',width: "100px"},
                {data: 'active', name: 'active',width: "80px"},
                // {data: 'is_use', name: 'is_use',width: "80px"},
                {data: 'is_hot', name: 'is_hot',width: "80px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });


    </script>
{{--    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>--}}
{{--    <script>--}}
{{--        //const socket = io('<?php //=get_option('link_connect_socket')?>//',{--}}
{{--        const socket = io('<?= get_option('link_connect_socket')?>',{--}}
{{--            extraHeaders: {--}}
{{--                'auth': 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiNDQiLCJ1c2VyX25hbWUiOiJMw6ogQ2jDrSBDw7RuZyIsImRiX25hbWUiOiJtNHUtYWRtaW4iLCJpYXQiOjE3NTg3MTA1NDcsImV4cCI6MTg0NTExMDU0N30.JYVTCnvEIe5HZAuhBm1-mNZW2_2w_GLJ_rtG7RjSI_QrzQ_5MiVRPhHrW8OCem66WUZa8eVJwDTNfAZ4pxdbC_I_y76vVZqOAsD28eudvlo3xlweM9EiQRPaLAKp2k65xUTGgbreJEO0uoIHyWQmbHFOjRio8GMOk07nbfeUsDqUjf4xmjVuVRhLnKvEsQ9d6rc2OrtJr4IeuffzRkMb8aUMXCi5-U_MaW5k1YZssHQvY6DecqmcRx57I3hVAfBDx09CK0pRlby7229tmgVh_f5G0Qmacwoc1FfNsY_9Owj-xuiRexoLHy78cp8kwaT21FZJHelo2PoPu0Z9xUUDYg'--}}
{{--            }--}}
{{--        });--}}

{{--        // Khi kết nối thành công--}}
{{--        socket.on('connect', () => {--}}
{{--            console.log('Connected to server as driver:', socket.id);--}}
{{--            socket.emit('connectedData', {--}}
{{--                user_id: 44, //id nhân viên--}}
{{--                user_name: 'Lê Chí Công', //tên người nhận--}}
{{--                // user_name: 'NAM MASTER', //tên người nhận--}}
{{--            });--}}
{{--        });--}}

{{--        socket.on('new_notification',(data) => {--}}
{{--            console.log('Data:', data);--}}
{{--        });--}}
{{--    </script>--}}
@endsection
