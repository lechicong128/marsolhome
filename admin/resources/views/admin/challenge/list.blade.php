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
                <a type="button" class="btn btn-default waves-effect waves-light dt-modal" href="admin/challenge/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('c_challenge')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/challenge/list">{{lang('c_challenge')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <table id="table_challenge" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_icon')}}</th>
                        <th class="text-center">{{lang('c_challenge_name')}}</th>
                        <th class="text-center">{{lang('c_challenge_type')}}</th>
                        <th class="text-center">{{lang('event_articles_challenge_join')}}</th>
                        <th class="text-center">{{lang('c_challenge_product')}}</th>
                        <th class="text-center">{{lang('c_challenge_limit_join')}}</th>
                        <th class="text-center">{{lang('c_challenge_day')}}</th>
                        <th class="text-center">{{lang('c_quantity_verification')}}</th>
                        <th class="text-center">{{lang('c_min_rank_join')}}</th>
                        <th class="text-center">{{lang('c_status')}}</th>
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
        oTable = InitDataTable('#table_challenge', 'admin/challenge/getList', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/challenge/getList",
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
                    data: 'id', name: 'id',width: "30px"
                },
                {data: 'icon', name: 'icon',width: "100px"},
                {data: 'name', name: 'name',width: "200px"},
                {data: 'type', name: 'type',width: "100px"},
                {data: 'event_articles', name: 'event_articles',width: "250px"},
                {data: 'product', name: 'product',width: "250px", searchable: false},
                {data: 'limit_join', name: 'limit_join'},
                {data: 'days', name: 'days'},
                {data: 'quantity_verification', name: 'quantity_verification',},
                {data: 'min_rank_join', name: 'min_rank_join',width: "150px"},
                {data: 'status', name: 'status',width: "150px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });



        function changeStatus(id, status) {
            $.ajax({
                url: 'admin/challenge/changeStatus',
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
@endsection
