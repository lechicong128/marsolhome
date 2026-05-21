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
                        <label for="customer_search">Thành viên Leader</label>
                        <select class="customer_search select2" id="customer_search"
                                data-placeholder="Chọn ..." name="customer_search">
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
                <table id="table_report_transaction" class="table table-bordered table_report_transaction">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('Thành viên Leader')}}</th>
                        <th class="text-center">{{lang('Tổng đơn hàng')}}</th>
                        <th class="text-center">{{lang('Tổng tiền đơn hàng')}}</th>
                        <th class="text-center">{{lang('Tổng khuyến mãi')}}</th>
                        <th class="text-center">{{lang('Tổng chiết khấu')}}</th>
                        <th class="text-center">{{lang('Tổng thuế VAT')}}</th>
                        <th class="text-center">{{lang('Tổng tiền')}}</th>
                        <th class="text-center">{{lang('Tổng tiền Leader')}}</th>
                        <th class="text-center">{{lang('Tổng tích lũy')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2" style="text-transform: uppercase;font-weight: bold">Tổng cộng</td>
                        <td class="total_transaction" style="font-weight: bold;text-align: center"></td>
                        <td class="total" style="font-weight: bold;text-align: right"></td>
                        <td class="promotion" style="font-weight: bold;text-align: right"></td>
                        <td class="discount" style="font-weight: bold;text-align: right"></td>
                        <td class="total_vat" style="font-weight: bold;text-align: right"></td>
                        <td class="grand_total" style="font-weight: bold;text-align: right"></td>
                        <td class="total_leader" style="font-weight: bold;text-align: right"></td>
                        <td class="total_accumulate" style="font-weight: bold;text-align: right"></td>
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
            search_daterangetimepicker('date_search');
        })
        var fnserverparams = {
            'status_search': '#status_search',
            'customer_search': '#customer_search',
            'date_search': '#date_search',
        };
        var oTable;
        oTable = InitDataTable('#table_report_transaction', 'admin/report/getListReportTransaction', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/report/getListReportTransaction",
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
                {data: 'customer_leader', name: 'customer',orderable: false},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'total_transaction', name: 'total_transaction',width: "140px"
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
                    data: 'total_leader', name: 'total_leader',width: "140px"
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

        $('#table_report_transaction').on('draw.dt', function () {
            var table = $(this).DataTable();
            var total_transaction =  table.column(2).data().sum();
            var total =  table.column(3).data().sum();
            var promotion =  table.column(4).data().sum();
            var discount =  table.column(5).data().sum();
            var total_vat =  table.column(6).data().sum();
            var grand_total =  table.column(7).data().sum();
            var total_leader =  table.column(8).data().sum();
            var total_accumulate =  table.column(9).data().sum();
            $("#table_report_transaction").find('tfoot .total_transaction').html(formatNumber(total_transaction));
            $("#table_report_transaction").find('tfoot .total').html(formatMoney(total));
            $("#table_report_transaction").find('tfoot .promotion').html(formatMoney(promotion));
            $("#table_report_transaction").find('tfoot .discount').html(formatMoney(discount));
            $("#table_report_transaction").find('tfoot .total_vat').html(formatMoney(total_vat));
            $("#table_report_transaction").find('tfoot .grand_total').html(formatMoney(grand_total));
            $("#table_report_transaction").find('tfoot .total_leader').html(formatMoney(total_leader));
            $("#table_report_transaction").find('tfoot .total_accumulate').html(formatMoney(total_accumulate));
        });

       
    </script>
@endsection
