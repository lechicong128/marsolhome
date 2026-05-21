@extends('admin.layouts.index')
@section('content')
    <style>
        .brs-20 {
            border-radius: 20px;
        }
        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .customer-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            /*background: linear-gradient(135deg, #ff6b6b, #feca57);*/
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }

        .customer-details h4 {
            margin-top: 0px;
            margin-bottom: 0px;
            font-size: 15px;
            color: #2c3e50;
        }

        .customer-details span {
            color: #7f8c8d;
            font-size: 12px;
        }


        .product-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e9ecef;
        }
        .rating {
            /*display: flex;*/
            gap: 2px;
        }

        .star {
            color: #ffd700;
            font-size: 16px;
        }

        .star.empty {
            color: #ddd;
        }
        .media-badge {
            padding: 6px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .media-photos {
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            color: #2d3436;
        }

        .media-video {
            background: linear-gradient(135deg, #ff9a9e, #fecfef);
            color: #2d3436;
        }
    </style>
    <style>
        .product-info .product-img img { border: 1px solid #eef1f5; }
        .product-info strong { line-height: 1.15; }
        .see-more-toggle { font-weight: 600; transition: all .2s ease; margin-top: 10px;}
        .see-more-toggle:hover { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/bonus_payment/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/bonus_payment/list">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs">
                <li class="H-search active cursor"><a data-toggle="tab" data-id="-1">{{lang('all')}} (<b
                            class="count_all">0</b>)</a></li>
                <li class="H-search cursor"><a style="color: #ffbd4a !important;" data-toggle="tab" data-id="0">{{lang('Chưa chi')}}
                        (<b class="count_0">0</b>)</a></li>
                <li class="H-search cursor"><a style="color: #81c868 !important;" data-toggle="tab" data-id="1">{{lang('Đã chi')}}
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
                <table id="table_bonus_payment" class="table table-bordered table_bonus_payment">
                    <thead>
                        <tr>
                            <th class="text-center">{{lang('dt_stt')}}</th>
                            <th class="text-center">{{lang('dt_reference_no')}}</th>
                            <th class="text-center">{{lang('dt_date')}}</th>
                            <th class="text-center">{{lang('client')}}</th>
                            <th class="text-center">{{lang('dt_status')}}</th>
                            <th class="text-center">{{lang('Tổng tiền')}}</th>
                            <th class="text-center">{{lang('payment_mode')}}</th>
                            <th class="text-center">{{lang('Ghi chú')}}</th>
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
            searchAjaxSelect2('#customer_search', 'admin/category/searchCustomer')
            search_daterangepicker('date_search');
        })
        var fnserverparams = {
            'status_search': '#status_search',
            'customer_search': '#customer_search',
            'date_search': '#date_search',
            'date_search_end': '#date_search_end',
        };
        var oTable;
        oTable = InitDataTable('#table_bonus_payment', 'admin/bonus_payment/getList', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/bonus_payment/getList",
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
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'id', name: 'id', width: "50px"
                },
                { data: 'reference_no', name: 'reference_no', width: "150px" },
                { data: 'date', name: 'date', width: "150px" },
                { data: 'customer', name: 'customer', orderable: false },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'status', name: 'status', width: "200px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total', name: 'total', width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'payment_mode_id', name: 'payment_mode_id', width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-left">${data}</div>`;
                    },
                    data: 'note', name: 'note', width: "140px"
                },
                { data: 'options', name: 'options', orderable: false, searchable: false, width: "150px" },

            ],
        });

        $('.H-search').click(function () {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })


        $.each(fnserverparams, function (filterIndex, filterItem) {
            $('' + filterItem).on('change', function () {
                oTable.draw('page')
            });
        });

        $('#table_bonus_payment').on('draw.dt', function () {
            getCountAll();
        });

        function getCountAll() {
            var data = {};
            $.each(fnserverparams, function(filterIndex, filterItem) {
                data[filterIndex] = $(filterItem).val();
            });
            $.ajax({
                url: 'admin/bonus_payment/countAll',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: data
            })
                .done(function (response) {
                    if(response.arr.length > 0){
                        $.each(response.arr, function(index, value) {
                            $(`.count_${value.status}`).text(formatNumber(value.count));
                        })
                    }
                    $(`.count_all`).text(formatNumber(response.all));
                })
                .fail(function () {

                });
            return false;
        }
    </script>
@endsection
