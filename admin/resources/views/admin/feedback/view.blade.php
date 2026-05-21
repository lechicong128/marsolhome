@extends('admin.layouts.index')
@section('content')
    <style>
        .card-box {
            min-height: 180px;
        }
    </style>

    <style>
        .brs-20 {
            border-radius: 20px;
        }
        ..customer-info {
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
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{lang('c_title_client')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/clients/list">{{lang('c_title_client')}}</a></li>
                <li class="active">{{ lang('dt_view_client') }}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#info" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">{{lang('general_information')}}</span>
                        <span class="hidden-xs">{{lang('general_information')}}</span>
                    </a>
                </li>
                <li>
                    <a href="#favourite" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">{{lang('list_product_review')}}</span>
                        <span class="hidden-xs">{{lang('list_product_review')}}</span>
                    </a>
                </li>
                <li>
                    <a href="#list_client_referral" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">{{lang('list_client_referral')}}</span>
                        <span class="hidden-xs">{{lang('list_client_referral')}}</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="info">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card-box">
                                <h5 class="text-muted text-uppercase m-t-0 m-b-20" style="color: black"><b>Thông tin chung</b></h5>
                                <div class="contact-card" style="display: flex;flex-wrap: wrap">
                                    @php
                                        $src = !empty($client['avatar']) ? ($client['avatar']) : asset('admin/assets/images/users/avatar-1.jpg');
                                        $classesT = 'btn-danger';
                                        $contentT = lang('type_client_1');
                                        if($client['type_client'] == 2) {
                                            $classesT = 'btn-info';
                                            $contentT = lang('type_client_2');
                                        }
                                        $str = "<a class='text-center btn btn-xs $classesT'>$contentT</a>";

                                        $classes = $client['active'] == 1 ? "btn-info" : "btn-danger";
                                        $content = $client['active'] == 1 ? "Hoạt động" : "Khoá";
                                        $strStatus = "<a class='dt-update text-center btn btn-xs $classes'>$content</a>";

                                    @endphp
                                    <div class="member-img"
                                         style="display: flex;flex-direction: column;align-items: center">
                                        <a href="{{$src}}" data-lightbox="customer-profile"
                                           class="display-block mbot5 pull-left">
                                            <img class="img-circle" src="{{$src}}" alt=""
                                                 style="width: 100px;height: 100px">
                                        </a>
                                        <h4 class="m-t-0 m-b-5"><b>{{$client['fullname']}}</b></h4>
                                    </div>
                                    <div class="member-info-new">
                                        <p class="text-dark member-info-detail"><span>{{lang('dt_phone_user')}}: </span><span>{{$client['phone']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail"><span>{{lang('dt_email_user')}}: </span><span>{{$client['email']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail"><span>{{lang('dt_date_created_customer')}}: </span><span>{{_dt($client['created_at'])}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Địa chỉ : </span><span>{{$client['address']}}</span></p>
                                        <p class="text-dark member-info-detail">
                                            <span>{{lang('c_active_client')}}: </span><span>{!! $strStatus !!}</span>
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-box">
                                <div class="member-info-new">
                                    <p class="text-dark member-info-detail">
                                        <span>Số cccd: </span><span>{{($client['number_cccd'])}}</span>
                                    </p>
                                    <p class="text-dark member-info-detail">
                                        <span>Ngày cấp cccd: </span><span>{{ !empty($client['date_cccd']) ? _dthuan($client['date_cccd']) : ''}}</span>
                                    </p>
                                    <p class="text-dark member-info-detail">
                                        <span>Nơi cấp cccd: </span><span>{{($client['issued_cccd'])}}</span>
                                    </p>
                                    <p class="text-dark member-info-detail">
                                        <span>Số passport: </span><span>{{($client['number_passport'])}}</span>
                                    </p>
                                    <p class="text-dark member-info-detail">
                                        <span>Ngày cấp passport: </span><span>{{!empty($client['date_passport']) ? _dthuan(($client['date_passport'])) : ''}}</span>
                                    </p>
                                    <p class="text-dark member-info-detail">
                                        <span>Nơi cấp passport: </span><span>{{($client['issued_passport'])}}</span>
                                    </p>
                                    <p class="text-dark member-info-detail">
                                        <span>Số sản phẩm đã đăng ký: </span><span>{{($client['count_product_review'])}}</span>
                                    </p>
                                    <p class="text-dark member-info-detail">
                                        <span>Số video đã review: </span><span>0</span>
                                    </p>
{{--                                    <p class="text-dark member-info-detail hide">--}}
{{--                                        <span>{{lang('dt_province')}}: </span><span>{{($client['province']['Name'] ?? '')}}</span>--}}
{{--                                    </p>--}}
{{--                                    <p class="text-dark member-info-detail hide">--}}
{{--                                        <span>{{lang('dt_wards')}}: </span><span>{{($client['wards']['Name'] ?? '')}}</span>--}}
{{--                                    </p>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="favourite">
                    <div class="row">
                        <input type="hidden" name="customer_id" class="customer_id" id="customer_id"
                               value="{{$client['id']}}">
                        <div class="card-box">
                            <table id="table_clients_review" class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center">{{lang('dt_stt')}}</th>
                                    <th class="text-center">{{lang('c_code_review')}}</th>
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
                <div class="tab-pane" id="list_client_referral">
                    <div class="row">
                        <div class="card-box">
                            <table id="table_list_client_referral" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{lang('c_avatar_client')}}</th>
                                        <th class="text-center">{{lang('c_code_client')}}</th>
                                        <th class="text-center">{{lang('c_fullname_client')}}</th>
                                        <th class="text-center">{{lang('c_phone_client')}}</th>
                                        <th class="text-center">{{lang('c_type_client')}}</th>
                                        <th class="text-center">{{lang('dt_date_created_customer')}}</th>
                                        <th class="text-center">{{lang('dt_status')}}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            searchAjaxSelect2('#customer_search_favourite', 'admin/category/searchCustomer')
            searchAjaxSelect2('#group_category_service_search_favourite', 'admin/category/searchGroupCategoryService')
            searchAjaxSelect2('#category_service_search_favourite', 'admin/category/searchCategoryService')
        });
        var fnserverparamsNew = {
            'customer_favourite': '#customer_id',
            'group_category_service_search': '#group_category_service_search_favourite',
            'category_service_search': '#category_service_search_favourite',
            'status_search': '#status_search_favourite',
            'customer_search': '#customer_search_favourite',
        };
        var fnserverparamsClient = {
            'customer_id': '#customer_id',
        };
    </script>
    <script>
        var fnserverparams = {
            'id_customer': '#customer_id',
        };
        var oTable;
        oTable = InitDataTable('#table_clients_review', 'admin/clients_review/getTableReview', {
            'order': [
                [0, 'desc']
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
                {data: 'code_review', name: 'code_review',width: "80px"},
                {data: 'product', name: 'product'},
                {data: 'evaluate', name: 'evaluate'},
                {data: 'video_review', name: 'video_review'},
                // {data: 'status', name: 'status',width: "100px"},
                {data: 'active', name: 'active',width: "100px"},
                {data: 'date_review', name: 'date_review',width: "100px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
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

    </script>

    <script>
        oTableIntroduce = InitDataTable('#table_list_client_referral', 'api/customer/getClientsIntroduce', {
            'order': [
                [6, 'desc']
            ],
            'responsive': false,
            "ajax": {
                "type": "POST",
                "url": "api/customer/getClientsIntroduce",
                "data": function (d) {
                    for (var key in fnserverparamsClient) {
                        d[key] = $(fnserverparamsClient[key]).val();
                    }
                    d['_locale'] = '{{session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang)}}';
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            columnDefs: [
                {data: 'avatar', name: 'avatar',width: "90px",},
                {data: 'code', name: 'code',width: "110px",},
                {data: 'fullname', name: 'fullname'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'phone', name: 'phone'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'type_client', name: 'type_client'
                },
                {data: 'created_at', name: 'created_at'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'active', name: 'active'
                },
            ]
        })
    </script>
@endsection
