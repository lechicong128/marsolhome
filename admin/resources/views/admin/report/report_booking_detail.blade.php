@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li class="active">{{$title}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <label for="branch_search">{{lang('Chi nhánh')}}</label>
                        <select class="branch_search select2" id="branch_search"
                                data-placeholder="Chọn ..." name="branch_search">
                            <option value="-1">Tất cả</option>
                            @foreach($branches as $branch)
                                <option value="{{$branch->id}}">{{$branch->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_search">Thời gian</label>
                        <input class="form-control date_search" type="text" id="date_search" name="date_search" value="" autocomplete="off">
                    </div>
                </div>
                <table id="table_report_booking_detail" class="table table-bordered table_report_booking_detail">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('Dịch vụ')}}</th>
                        <th class="text-center">{{lang('Tổng lượt đã booking')}}</th>
                        <th class="text-center">{{lang('Số lượt dùng thẻ liệu trình')}}</th>
                        <th class="text-center">{{lang('Tổng tiền')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2" style="text-transform: uppercase;font-weight: bold;text-align: right">Tổng cộng</td>
                        <td class="total_bookings" style="font-weight: bold;text-align: center"></td>
                        <td class="total_treatment_used" style="font-weight: bold;text-align: center"></td>
                        <td class="total_amount" style="font-weight: bold;text-align: right"></td>
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
            search_daterangetimepicker('date_search');
        })
        var fnserverparams = {
            'branch_search': '#branch_search',
            'date_search': '#date_search',
        };
        var oTable;
        oTable = InitDataTable('#table_report_booking_detail', 'admin/report/getListReportBookingDetail', {
            'order': [
                [2, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/report/getListReportBookingDetail",
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
                    data: 'DT_RowIndex', name: 'DT_RowIndex', width: "50px", orderable: false, searchable: false,
                    className: "text-center"
                },
                {data: 'service_name', name: 'service_name', orderable: false},
                {data: 'total_bookings', name: 'total_bookings', width: "160px", orderable: true},
                {data: 'total_treatment_used', name: 'total_treatment_used', width: "200px", orderable: true},
                {data: 'total_amount', name: 'total_amount', width: "150px", orderable: true}
            ],
        });

        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        $('#table_report_booking_detail').on('draw.dt', function () {
            var table = $(this).DataTable();
            
            var sumColumn = function(colIndex) {
                var data = table.column(colIndex).data();
                var sum = 0;
                for (var i = 0; i < data.length; i++) {
                    var val = data[i];
                    if (typeof val === 'string') {
                        val = val.replace(/<\/?[^>]+(>|$)/g, "");
                        val = val.replace(/\./g, "").replace(/,/g, "");
                    }
                    sum += parseFloat(val) || 0;
                }
                return sum;
            };

            var total_bookings = sumColumn(2);
            var total_treatment_used = sumColumn(3);
            var total_amount = sumColumn(4);

            $("#table_report_booking_detail").find('tfoot .total_bookings').html(formatNumber(total_bookings));
            $("#table_report_booking_detail").find('tfoot .total_treatment_used').html(formatNumber(total_treatment_used));
            $("#table_report_booking_detail").find('tfoot .total_amount').html(formatMoney(total_amount));
        });
    </script>
@endsection
