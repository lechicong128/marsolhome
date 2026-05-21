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
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light" target="_blank" href="admin/clients_review/export"><i class="fa fa-download" aria-hidden="true"></i> {{lang('Export excel')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('c_list_clients_review')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/clients_review/list">{{lang('c_list_clients_review')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs">
                <li class="H-search active cursor" style="font-size:13px;white-space: nowrap;"><a data-toggle="tab" data-id="-1">{{lang('dt_length_menu_all')}} (<b
                            class="count_all">0</b>)</a></li>
                @foreach (status_product_review() as $key => $value)
                    <li class="H-search cursor">
                        <a style="color: {{ $value['color'] }} !important;white-space: nowrap;" data-toggle="tab"
                                                   data-id="{{ $value['id'] }}">{{ $value['name'] }}
                            (<b class="count_{{ $value['id'] }}">0</b>)
                        </a>
                    </li>
                @endforeach
            </ul>
            <span class="group_search">
                <input type="hidden" name="status_search" id="status_search" value="-1">
            </span>
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-3">
                        <label for="date_search">{{lang('dt_date_created_customer')}}</label>
                        <input class="form-control  date_search" type="text" id="date_search" name="date_search" value="" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="product_search">{{lang('c_products')}}</label>
                            <select class="product_search select2" id="product_search" data-placeholder="Chọn ..." name="product_search"></select>
                        </div>
                    </div>
                </div>
                <table id="table_clients_review" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('c_customer_review')}}</th>
                        <th class="text-center">{{lang('address')}}</th>
                        <th class="text-center">{{lang('c_code_review')}}</th>
                        <th class="text-center">{{lang('c_products')}}</th>
                        <th class="text-center">{{lang('c_status_review')}}</th>
                        <th class="text-center">{{lang('c_date_create_sign_up_review')}}</th>
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
            'status_search': '#status_search',
            'date_search': '#date_search',
            'product_search': '#product_search',
        };
        var oTable;
        oTable = InitDataTable('#table_clients_review', 'admin/clients_review/getTable', {
            'order': [
                [6, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/clients_review/getTable",
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
                {data: 'clients', name: 'clients', width: "250px"},
                {data: 'address', name: 'clients', width: "250px"},
                {data: 'code_review', name: 'code_review',width: "80px"},
                {data: 'products', name: 'products', searchable: false },
                {data: 'status', name: 'status',width: "100px"},
                {data: 'created_at', name: 'created_at',width: "100px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });


        $('.H-search').click(function() {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })


        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        $('#table_clients_review').on('draw.dt', function() {
            getCountAll();
        });

        function getCountAll() {
            var data = {};
            $.each(fnserverparams, function(filterIndex, filterItem) {
                data[filterIndex] = $(filterItem).val();
            });
            $.ajax({
                url: 'admin/clients_review/countAll',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: data
            })
                .done(function(response) {
                    var total = 0;
                    $.each(response.data, function(index, value) {
                        $(`.count_${index}`).text(formatNumber(value));
                        console.log(`.count_${index}`)
                        total += parseFloat(value);
                    })
                    $(`.count_all`).text(formatNumber(total));
                })
                .fail(function() {

                });
            return false;
        }


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
        $(document).on('shown.bs.collapse hidden.bs.collapse', '.collapse', function () {
            var $btn = $('[data-target="#' + this.id + '"]');
            if (!$btn.length) return;
            var $icon = $btn.find('i.fa');
            var $text = $btn.find('.toggle-text');
            if ($(this).hasClass('in')) {
                // Đang mở
                $icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                $text.text($btn.data('show-text') || 'Thu gọn');
            } else {
                // Đang đóng
                $icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                $text.text($btn.data('hide-text') || 'Xem thêm');
            }
        });


        function removeItems(id = '') {
            if (confirm('{{lang('you_want_delete_product_review')}}')) {
                var data = {};
                data['id'] = id;

                $.ajax({
                    type: "POST",
                    url: 'admin/clients_review/removeItems',
                    data: data,
                    dataType: "json",
                    success: function(response) {
                        if (response.result) {
                            alert_float('success', response?.message);
                        } else {
                            alert_float('error', response?.message);
                        }
                        oTable.draw(false);
                    }
                });
            } else {
                oTable.draw(false);
            }
        }
    </script>
@endsection
