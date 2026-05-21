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
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title ?? ''}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/clients_review/library_review">{{lang('c_library_review')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <input type="hidden" name="filter_is_review" id="filter_is_review" value="1"/>
                <div class="row m-b-10">
                    <div class="col-md-3">
                        <label for="date_search">{{lang('c_date_review')}}</label>
                        <input class="form-control  date_search" type="text" id="date_search" name="date_search" value="" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="product_search">{{lang('c_products')}}</label>
                            <select class="product_search select2" id="product_search" data-placeholder="Chọn ..." name="product_search"></select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="type_object_search">{{lang('review_to')}}</label>
                            <select class="type_object_search select2" id="type_object_search" data-placeholder="Chọn ..." name="type_object_search">
                                <option value="0">{{lang('all')}}</option>
                                <option value="sign_up">{{lang('type_object_sign_up')}}</option>
                                <option value="transaction">{{lang('type_object_transaction')}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <table id="table_clients_review" class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">{{lang('dt_stt')}}</th>
                            <th class="text-center">{{lang('c_customer_review')}}</th>
                            <th class="text-center">{{lang('c_code_review')}}</th>
                            <th class="text-center">{{lang('review_to')}}</th>
                            <th class="text-center">{{lang('c_products')}}</th>
                            <th class="text-center">{{lang('c_evaluate')}}</th>
                            <th class="text-center">{{lang('c_media_review')}}</th>
    {{--                        <th class="text-center">{{lang('c_status_review')}}</th>--}}
                            <th class="text-center">{{lang('c_admin_active_review')}}</th>
                            <th class="text-center">{{lang('c_date_review')}}</th>
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
        search_daterangepicker('date_search');
        $(function () {
            searchAjaxSelect2Img('#product_search','api/category/getListProduct', 0, {
                'select2':true
            })
        })
        var fnserverparams = {
            "filter_is_review" : "#filter_is_review",
            'date_search': '#date_search',
            'product_search': '#product_search',
            'type_object_search': '#type_object_search',
        };
        var oTable;
        oTable = InitDataTable('#table_clients_review', 'admin/clients_review/getTableReview', {
            'order': [
                [8, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/clients_review/getTableReview",
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
                {data: 'code_review', name: 'code_review',width: "80px"},
                {data: 'type_object', name: 'type_object',width: "100px"},
                {data: 'product', name: 'product'},
                {data: 'evaluate', name: 'evaluate'},
                {data: 'video_review', name: 'video_review'},
                // {data: 'status', name: 'status',width: "100px"},
                {data: 'active', name: 'active',width: "100px"},
                {data: 'date_review', name: 'date_review',width: "100px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });


        function changeStatus(id, status) {
            $.ajax({
                url: 'admin/clients_review/changeStatus',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    id: id,
                    status: status,
                },
            }).done(function(data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
                oTable.draw('page');
            }).fail(function() {});
            return false;
        }
        function changeActive(id, active) {
            if (active == 2) {
                $.ajax({
                    url: 'admin/clients_review/modal_cancel_review/' + id,
                    type: 'GET',
                    dataType: 'JSON',
                    data: {
                        status: status
                    },
                    cache: false,
                }).done(function (data) {
                    console.log(data.data);
                    $('#dtModal2').html(data.data);
                    if(data.result) {
                        $('#dtModal2').modal({
                            backdrop: 'static',
                            keyboard: true
                        });
                    }
                }).fail(function () {

                });

                return false;
            }
            else {
                $.ajax({
                    url: 'admin/clients_review/active_review',
                    type: 'POST',
                    dataType: 'JSON',
                    cache: false,
                    data: {
                        id: id,
                        active: active,
                    },
                }).done(function (data) {
                    if (data.result) {
                        alert_float('success', data.message);
                    } else {
                        alert_float('error', data.message);
                    }
                    oTable.draw('page');
                }).fail(function () {
                });
                return false;
            }
        }

    </script>
@endsection
