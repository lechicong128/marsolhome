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
        .inline-flex{
            display: inline-flex!important;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default waves-effect waves-light" href="admin/admin_website/feedback"><i class="fa fa-cog"></i> {{lang('c_setting_page_feedback')}}</a>
            </div>
            <h4 class="page-title">{{$title ?? ''}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/feedback/list">{{lang('c_title_feedback')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs">
                <li class="H-search active"><a data-toggle="tab" data-id="0">Tất cả (<b class="count_all">0</b>)</a></li>
                @php
                    for($i = 1; $i <= 5; $i++){
                        echo '<li class="H-search"><a data-toggle="tab" data-id="'.$i.'">'.lang('lang_star_like_short_' . $i).' (<b class="count_star_'.$i.'">0</b>)</a></li>';
                    }
                @endphp
            </ul>
            <span class="group_search">
                <input type="hidden" name="star_like_search" id="star_like_search" value="0">
            </span>
            <div class="card-box table-responsive">
                <table id="table_feedback" class="table table-bordered table_feedback">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('c_customer_review')}}</th>
                        <th class="text-center">{{lang('c_star_like')}}</th>
                        <th class="text-center">{{lang('content_feedback')}}</th>
                        <th class="text-center">{{lang('c_improve')}}</th>
                        <th class="text-center">{{lang('file_feedback')}}</th>
                        <th class="text-center">{{lang('date_create')}}</th>
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
        var oTable;
        var fnserverparams = {
            'star_like_search' : 'input[name="star_like_search"]',
            'active_search' : '#active_search',
            'date_search' : '#date_search',
        };
        $('.H-search').click(function() {
            $('input[name="star_like_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })

        $(function() {
            search_daterangepicker('date_search');
            oTable = InitDataTable('#table_feedback', 'admin/feedback/getTable', {
                'order': [
                    [5, 'desc']
                ],
                'responsive': false,
                "ajax": {
                    "type": "POST",
                    "url": "admin/feedback/getTable",
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
                    {data: 'clients', name: 'clients', width: "250px"},
                    {data: 'star_like', name: 'clients', width: "250px"},
                    {data: 'content_feedback', name: 'content_feedback'},
                    {data: 'improve', name: 'improve'},
                    {data: 'file_feedback', name: 'file_feedback'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

                ]
            })
            $.each(fnserverparams, function(filterIndex, filterItem) {
                $('' + filterItem).on('change', function() {
                    oTable.draw('page')
                });
            });
            $('#table_feedback').on('draw.dt', function () {
                countAll();
            });

            function countAll() {
                var data = {};
                $.each(fnserverparams, function(filterIndex, filterItem) {
                    data[filterIndex] = $(filterItem).val();
                });
                $.post('admin/feedback/countAll', data, function(response) {
                    var total = 0;
                    if(response.arrType.length > 0){
                        $.each(response.arrType, function(index, value) {
                            $(`.count_star_${value.id}`).text(formatNumber(value.total));
                            total += parseFloat(value.total);
                        })
                    }
                    $(`.count_all`).text(formatNumber(total));
                })
            }
        })
    </script>
@endsection
