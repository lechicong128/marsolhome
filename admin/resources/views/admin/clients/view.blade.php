@extends('admin.layouts.index')
@section('page_title', lang('dt_client'))
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
        .tree ul {
            padding-top: 20px;
            position: relative;
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        .tree li {
            float: left;
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 20px 5px 0 5px;
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        .tree li::before, .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 1px solid #ccc;
            width: 50%;
            height: 20px;
        }

        .tree li::after {
            right: auto;
            left: 50%;
            border-left: 1px solid #ccc;
        }

        .tree li:only-child::before, .tree li:only-child::after {
            display: none;
        }

        .tree li:only-child {
            padding-top: 0;
        }

        .tree li:first-child::before, .tree li:last-child::after {
            border: 0 none;
        }

        .tree li:last-child::before {
            border-right: 1px solid #ccc;
            border-radius: 0 5px 0 0;
        }

        .tree li:first-child::after {
            border-radius: 5px 0 0 0;
        }
        .tree ul ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 1px solid #ccc;
            width: 0;
            height: 20px;
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
            <ul class="nav nav-tabs" id="tab_client">
                <li class="active">
                    <a href="#info" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">{{lang('general_information')}}</span>
                        <span class="hidden-xs">{{lang('general_information')}}</span>
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

                                        $htmlPartner = '';
                                        if ($client['type_partner'] == 1){
                                            $htmlPartner = 'Đại lý';
                                        } elseif($client['type_partner'] == 2){
                                            $htmlPartner = 'Spa';
                                        } elseif ($client['type_partner'] == 3){
                                            $htmlPartner = 'CTV';
                                        }
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
                                        <p class="text-dark member-info-detail">
                                            @php
                                                $classesT = 'btn-default';
                                                $contentT = lang('Chưa xác định');
                                                if($client['type_client'] == 0){
                                                    $classesT = 'btn-default';
                                                    $contentT = lang('Người xem');
                                                }elseif ($client['type_client'] == 1) {
                                                    $classesT = 'btn-info';
                                                    $contentT = lang('Môi giới');
                                                } elseif($client['type_client'] == 2){
                                                    $classesT = 'btn-danger';
                                                    $contentT = lang('Chính chủ');
                                                }
                                            $str = "<a class='text-center btn btn-xs $classesT'>$contentT</a>";
                                            @endphp
                                            <span>Loại thành viên: </span>{!! $str !!}
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 hide">
                            <div class="card-box">
                                <div class="title_driving_liscense">
                                    <h5 class="text-muted text-uppercase m-t-0 m-b-20" style="color: black"><b>Sơ đồ
                                            nhánh các cấp</b></h5>
                                </div>
                                <div>Tổng số thành viên: {{$countMember}}</div>
                                <div class="hide">Tổng số cấp: {{$level}}</div>
                                <div class="tree" style="overflow-x: auto;overflow-y: hidden; white-space: nowrap;">
                                    @php
                                        $html = get_parent_id_referral_level_html($dataReferralLevel)
                                    @endphp
                                    {!! $html !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
            $("#infomation_vat_form").validate({
            ignore: [],
            rules: {
                type_vat: {
                    required: true,
                },
                address: {
                    required: true,
                },
                payment_method: {
                    required: true,
                },
            },
            messages: {
                type_vat: {
                    required: "{{lang('dt_required')}}",
                },
                address: {
                    required: "{{lang('dt_required')}}",
                },
                payment_method: {
                    required: "{{lang('dt_required')}}",
                },
            },
            invalidHandler: function (event, validator) {
                if (validator.errorList.length) {

                    // Lấy input lỗi đầu tiên
                    var firstError = validator.errorList[0].element;

                    // Tìm tab-pane chứa input lỗi
                    var tabPane = $(firstError).closest('.tab-pane');

                    // Lấy id của tab-pane
                    var tabId = tabPane.attr('id');

                    // Active tab đó (Bootstrap tab)
                    $('a[href="#' + tabId + '"]').tab('show');
                }
            },
            submitHandler: function (form) {
                var url = form.action;
                var form = $(form),
                    formData = new FormData(),
                    formParams = form.serializeArray();

                $.each(form.find('input[type="file"]'), function (i, tag) {
                    $.each($(tag)[0].files, function (i, file) {
                        formData.append(tag.name, file);
                    });
                });
                $.each(formParams, function (i, val) {
                    formData.append(val.name, val.value);
                });

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'JSON',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                })
                    .done(function (data) {
                        if (data.result) {
                            alert_float('success', data.message);
                            setTimeout(function () {
                                localStorage.setItem('activeTab', 'infomation_vat');
                                location.reload();
                            }, 1000);
                        } else {
                            $(".show_error").html(data.message);
                            alert_float('error', data.message);
                        }
                    })
                    .fail(function (err) {
                    });
                return false;
            }
        });

        function changeTypeVat() {
            var type_vat = $('#type_vat').val();
            if (type_vat == 1) {
                $('.name_hide').addClass('hide');
                $('.company_hide').removeClass('hide');
                $('.vat_hide').removeClass('hide');
            } else {
                $('.name_hide').removeClass('hide');
                $('.company_hide').addClass('hide');
                $('.vat_hide').addClass('hide');
            }
        }
        
        changeTypeVat();
        $(document).ready(function () {
            searchAjaxSelect2('#customer_search_favourite', 'admin/category/searchCustomer')
            searchAjaxSelect2('#group_category_service_search_favourite', 'admin/category/searchGroupCategoryService')
            searchAjaxSelect2('#category_service_search_favourite', 'admin/category/searchCategoryService')
            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                $('#tab_client a[href="#' + activeTab + '"]').tab('show');
                localStorage.removeItem('activeTab'); // dùng xong thì xóa
            }
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
        var fnserverparamsReferralReview = {
            'customer_id': '#customer_id',
        };
        var fnserverparamsReferralOrder = {
            'customer_id': '#customer_id',
        };
        var fnserverparamsOrderLeader = {
            'customer_id': '#customer_id',
            'year_search': '#year_search'
        };
        var fnserverparamsTreatment = {
            'search_customer': '#customer_id',
        };
    </script>
    <script>
        var fnserverparams = {
            'id_customer': '#customer_id',
        };
        var oTable;
        oTable = InitDataTable('#table_clients_review', 'admin/clients_review/getTableReview', {
            'order': [
                [6, 'desc']
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

        var oTableReferralReview;
        var oTableReferralOrder;
        var oTableOrderLeader;
        function loadTableReferralReview() {
            oTableReferralReview = InitDataTable('#table_list_client_referral_review', 'admin/clients_review/getClientsIntroduceReview', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/clients_review/getClientsIntroduceReview",
                    "data": function (d) {
                        for (var key in fnserverparamsReferralReview) {
                            d[key] = $(fnserverparamsReferralReview[key]).val();
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
                    {data: 'code_review', name: 'code_review'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'active', name: 'active'
                    },
                ]
            });
        }

        function loadTableReferralOrder() {
            oTableReferralReview = InitDataTable('#table_list_client_referral_order', 'admin/clients/getClientsIntroduceOrder', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/clients/getClientsIntroduceOrder",
                    "data": function (d) {
                        for (var key in fnserverparamsReferralOrder) {
                            d[key] = $(fnserverparamsReferralOrder[key]).val();
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
                    {data: 'code_review', name: 'code_review'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'active', name: 'active'
                    },
                ]
            });
        }

        
        function loadTableOrderLeader() {
            oTableOrderLeader = InitDataTable('#table_list_client_leader', 'admin/clients/getClientsOrderLeader', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/clients/getClientsOrderLeader",
                    "data": function (d) {
                        for (var key in fnserverparamsOrderLeader) {
                            d[key] = $(fnserverparamsOrderLeader[key]).val();
                        }
                        d['_locale'] = '{{session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang)}}';
                    },
                    "dataSrc": function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {   "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'id', name: 'id',width: "80px" },
                    {data: 'date', name: 'date',width: "110px",},
                    {data: 'code_order', name: 'code_order'},
                    {data: 'customer', name: 'customer'},
                    {data: 'total_order', name: 'total_order',width: "110px",
                        "render": function (data, type, row) {
                            return `<div class="text-right">${data}</div>`;
                        },
                    },
                    {data: 'total_leader', name: 'total_leader',width: "110px",
                        "render": function (data, type, row) {
                            return `<div class="text-right">${data}</div>`;
                        },
                    },
                ]
            });
        }

        function loadTableTreatment() {
            InitDataTable('#table_buy_treatment_client', 'admin/buy_treatment/getTable', {
                'order': [[8, 'desc']],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/buy_treatment/getTable",
                    "data": function (d) {
                        for (var key in fnserverparamsTreatment) {
                            d[key] = $(fnserverparamsTreatment[key]).val();
                        }
                    },
                    "dataSrc": function (json) { return json.data; }
                },
                columnDefs: [
                    { data: 'DT_RowIndex',    name: 'DT_RowIndex',    width: '50px',
                      render: function(data){ return '<div class="text-center">'+data+'</div>'; }},
                    { data: 'purchase_code',  name: 'purchase_code',  width: '140px', render: function(data, type, row) {
                        return '<a class="dt-modal" href="admin/buy_treatment/view/' + row.id + '"><b>' + data + '</b></a>';
                    }},
                    { data: 'treatment_name', name: 'treatment_name' },
                    { data: 'category_name',  name: 'cs.name', render: function(data) {
                        return data ? data : '<span class="text-muted">Tất cả</span>';
                    }},
                    { data: 'total_sessions', name: 'total_sessions', width: '80px',  className: 'text-center' },
                    { data: 'used_sessions',  name: 'used_sessions',  width: '120px', className: 'text-center' },
                    { data: 'price',          name: 'price',          width: '120px', className: 'text-right' },
                    { data: 'status',         name: 'status',         width: '120px', className: 'text-center' },
                    { data: 'created_at',     name: 'created_at',     width: '130px', className: 'text-center' },
                    { data: 'options',        name: 'options',        orderable: false, searchable: false, width: '130px', 
                        render: function(data, type, row) {
                            return `<div class="text-center">
                                <a class='btn btn-default btn-xs dt-modal' href='admin/buy_treatment/view/${row.id}'><i class='fa fa-eye'></i> Xem</a>
                            </div>`;
                        }
                    },
                ]
            });
        }

        $(document).on('shown.bs.tab', 'a[href="#list_client_referral_review"]', function () {
            loadTableReferralReview();
        });

        $(document).on('shown.bs.tab', 'a[href="#list_client_referral_order"]', function () {
            loadTableReferralOrder();
        });

        $(document).on('shown.bs.tab', 'a[href="#list_client_leader"]', function () {
            loadTableOrderLeader();
        });

        $(document).on('shown.bs.tab', 'a[href="#list_client_treatment"]', function () {
            loadTableTreatment();
        });


        $('#table_list_client_leader').on('draw.dt', function () {
            var table = $(this).DataTable();
            var total =  table.column(4).data().sum();
            var total_leader =  table.column(5).data().sum();
            $("#table_list_client_leader").find('tfoot .total').html(formatNumber(total));
            $("#table_list_client_leader").find('tfoot .total_leader').html(formatNumber(total_leader));
        });

        $.each(fnserverparamsOrderLeader, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTableOrderLeader.draw('page')
            });
        });

    </script>
@endsection
