@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title text-capitalize">{{$title}}</h4>
        <ol class="breadcrumb">
            <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
            <li><a href="admin/invoice/waiting_invoice">{{$title}}</a></li>
            <li class="active">{{lang('dt_list')}}</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive">
            <div class="row m-b-10">
                <div class="col-md-2">
                    <label for="customer_search">Khách hàng</label>
                    <select class="customer_search select2" id="customer_search"
                            data-placeholder="Chọn ..." name="customer_search">
                        <option></option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="transaction_search">Đơn hàng</label>
                    <select class="transaction_search select2" id="transaction_search"
                            data-placeholder="Chọn ..." name="transaction_search">
                        <option></option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_search">Thời gian</label>
                    <input class="form-control date_search" type="text" id="date_search" name="date_search" value="" autocomplete="off">
                </div>
            </div>
            <table id="table_waiting_invoice" class="table table-bordered table_waiting_invoice">
                <thead>
                <tr>
                    <th class="text-center">{{lang('dt_stt')}}</th>
                    <th class="text-center">{{lang('Ngày tạo đơn hàng')}}</th>
                    <th class="text-center">{{lang('Số đơn hàng')}}</th>
                    <th class="text-center">{{lang('Khách hàng')}}</th>
                    <th class="text-center">{{lang('Tiền chưa thuế')}}</th>
                    <th class="text-center">{{lang('Tổng thuế')}}</th>
                    <th class="text-center">{{lang('Tổng tiền')}}</th>
                    <th class="text-center">{{lang('Duyệt xuất nháp')}}</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2" style="text-transform: uppercase;font-weight: bold">Tổng cộng</td>
                    <td></td>
                    <td></td>
                    <td class="total" style="font-weight: bold;text-align: right"></td>
                    <td class="total_vat" style="font-weight: bold;text-align: right"></td>
                    <td class="grand_total" style="font-weight: bold;text-align: right"></td>
                    <td></td>
                </tr>
                </tfoot>
            </table>
        </div>
        </div>
    </div>
</div>

@endsection
@section('script')
    <script>
        $(document).ready(function (){
            searchAjaxSelect2('#customer_search','admin/category/searchCustomer')
            searchAjaxSelect2('#transaction_search','admin/category/searchTransaction')
            search_daterangetimepicker('date_search');
        })
        var fnserverparams = {
            'transaction_search': '#transaction_search',
            'customer_search': '#customer_search',
            'date_search': '#date_search',
        };
        var oTable;
        oTable = InitDataTable('#table_waiting_invoice', 'admin/invoice/getListWaitingInvoice', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/invoice/getListWaitingInvoice",
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
                {data: 'reference_no', name: 'reference_no',width: "200px" },
                {data: 'customer', name: 'customer',orderable: false},
            
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'total_net', name: 'total_net',width: "140px"
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
                    data: 'total', name: 'total',width: "140px"
                },
                {
                    data: 'status_invoice', name: 'status_invoice', width: "110px",
                    "render": function(data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    }
                }

            ],
        });


        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        $('#table_waiting_invoice').on('draw.dt', function () {
        
            var table = $(this).DataTable();
            var total =  table.column(4).data().sum();
            var total_vat =  table.column(5).data().sum();
            var grand_total =  table.column(6).data().sum();
        
            $("#table_waiting_invoice").find('tfoot .total').html(formatNumber(total));
            $("#table_waiting_invoice").find('tfoot .total_vat').html(formatNumber(total_vat));
            $("#table_waiting_invoice").find('tfoot .grand_total').html(formatNumber(grand_total));
        });


        function changeStatusInvoice(invoice_id, status) {
            $.ajax({
                url: 'admin/invoice/detailInvoice',
                type: 'POST',
                dataType: 'html',
                data: {invoice_id: invoice_id, status: status},
            })
            .done(function (data) {
                $('#dtModal').html(data);
            })
            .fail(function () {
                alert_float('error', 'Có lỗi xảy ra');
            });
            $('#dtModal').modal({backdrop: 'static', keyboard: true});
            return false;
        }
    </script>
@endsection
