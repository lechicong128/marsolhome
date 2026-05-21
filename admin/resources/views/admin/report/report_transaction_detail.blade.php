@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/transaction/list">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <label for="customer_search">Thành viên</label>
                        <select class="customer_search select2" id="customer_search"
                                data-placeholder="Chọn ..." name="customer_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="customer_leader_search">Thành viên Leader</label>
                        <select class="customer_leader_search select2" id="customer_leader_search"
                                data-placeholder="Chọn ..." name="customer_leader_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="product_search">Sản phẩm</label>
                        <select class="product_search select2" id="product_search"
                                data-placeholder="Chọn ..." name="product_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status_search">{{lang('dt_status')}}</label>
                        <select class="status_search select2" id="status_search"
                                data-placeholder="Chọn ..." name="status_search">
                            <option value="-1">Tất cả</option>
                            @foreach($dtStatus as $status)
                                <option value="{{$status['id']}}">{{$status['name']}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_search">Thời gian</label>
                        <input class="form-control date_search" type="text" id="date_search" name="date_search" value="" autocomplete="off">
                    </div>
                </div>
                <table id="table_report_transaction_detail" class="table table-bordered table_report_transaction_detail">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('Ngày')}}</th>
                        <th class="text-center">{{lang('Thành viên')}}</th>
                        <th class="text-center">{{lang('Thành viên Leader')}}</th>
                        <th class="text-center">{{lang('Số đơn hàng')}}</th>
                        <th class="text-center">{{lang('Trạng thái')}}</th>
                        <th class="text-center">{{lang('Sản phẩm')}}</th>
                        <th class="text-center">{{lang('Số lượng')}}</th>
                        <th class="text-center">{{lang('Đơn giá')}}</th>
                        <th class="text-center">{{lang('Thành tiền')}}</th>
                        <th class="text-center">{{lang('Tổng đơn hàng')}}</th>
                        <th class="text-center">{{lang('Khuyến mãi')}}</th>
                        <th class="text-center">{{lang('Giảm giá')}}</th>
                        <th class="text-center">{{lang('Tiền thuế')}}</th>
                        <th class="text-center">{{lang('Tổng tiền')}}</th>
                        <th class="text-center">{{lang('Tổng tiền Leader')}}</th>
                        <th class="text-center">{{lang('Tổng tích lũy')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="5" style="text-transform: uppercase;font-weight: bold">Tổng cộng</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="total_transaction" style="font-weight: bold;text-align: center"></td>
                        <td class="total_item" style="font-weight: bold;text-align: right"></td>
                        <td class="total" style="font-weight: bold;text-align: right"></td>
                        <td class="promotion" style="font-weight: bold;text-align: right"></td>
                        <td class="discount" style="font-weight: bold;text-align: right"></td>
                        <td class="total_vat" style="font-weight: bold;text-align: right"></td>
                        <td class="grand_total" style="font-weight: bold;text-align: right"></td>
                        <td class="total_leader" style="font-weight: bold;text-align: right"></td>
                        <td class="total_acc" style="font-weight: bold;text-align: right"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function (){
            searchAjaxSelect2('#customer_search','admin/category/searchCustomer')
            searchAjaxSelect2('#customer_leader_search','admin/category/searchCustomer')
            searchAjaxSelect2('#product_search','api/category/getListProduct')
            search_daterangetimepicker('date_search');
        })
        var fnserverparams = {
            'status_search': '#status_search',
            'customer_search': '#customer_search',
            'customer_leader_search': '#customer_leader_search',
            'product_search': '#product_search',
            'date_search': '#date_search',
        };
        var oTable;
        oTable = InitDataTable('#table_report_transaction_detail', 'admin/report/getListReportTransactionDetail', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/report/getListReportTransactionDetail",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    if(json.result == false){
                        alert_float('error',json.message);
                    }
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
                {data: 'date', name: 'date',width: "150px" },
                {data: 'customer', name: 'customer',orderable: false},
                {data: 'customer_leader', name: 'customer_leader',orderable: false},
                {data: 'reference_no', name: 'reference_no',width: "150px" },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'status', name: 'status',width: "200px"
                },
                {data: 'product_name', name: 'product_name',width: "150px" },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'quantity', name: 'quantity',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'price', name: 'price',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total_item', name: 'total_item',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total', name: 'total',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total_promotion', name: 'total_promotion',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total_discount', name: 'total_discount',width: "120px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total_vat', name: 'total_vat',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'grand_total', name: 'grand_total',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total_discount_leader', name: 'total_discount_leader',width: "140px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total_accumulate', name: 'total_accumulate',width: "140px"
                }

            ],
        });

        $('.H-search').click(function() {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })


        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        $('#table_report_transaction_detail').on('draw.dt', function () {
            var table = $(this).DataTable();
            var total =  table.column(10).data().sum();
            var promotion =  table.column(11).data().sum();
            var discount =  table.column(12).data().sum();
            var total_vat =  table.column(13).data().sum();
            var grand_total =  table.column(14).data().sum();
            var total_leader =  table.column(15).data().sum();
            var total_accumulate =  table.column(16).data().sum();
            $("#table_report_transaction_detail").find('tfoot .total').html(formatMoney(total));
            $("#table_report_transaction_detail").find('tfoot .promotion').html(formatMoney(promotion));
            $("#table_report_transaction_detail").find('tfoot .discount').html(formatMoney(discount));
            $("#table_report_transaction_detail").find('tfoot .total_vat').html(formatMoney(total_vat));
            $("#table_report_transaction_detail").find('tfoot .grand_total').html(formatMoney(grand_total));
            $("#table_report_transaction_detail").find('tfoot .total_leader').html(formatMoney(total_leader));
            $("#table_report_transaction_detail").find('tfoot .total_acc').html(formatMoney(total_accumulate));
        });

       
    </script>
@endsection
