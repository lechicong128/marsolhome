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
                        <label for="customer_search">Khách hàng</label>
                        <select class="customer_search select2" id="customer_search"
                                data-placeholder="Chọn ..." name="customer_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status_search">{{lang('Trạng thái')}}</label>
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
                <table id="table_report_booking" class="table table-bordered table_report_booking">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('Khách hàng')}}</th>
                        <th class="text-center">{{lang('Tổng số lịch hẹn')}}</th>
                        <th class="text-center">{{lang('Tổng số dịch vụ làm')}}</th>
                        <th class="text-center">{{lang('Tổng tiền')}}</th>
                        <th class="text-center">{{lang('Tùy chọn')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2" style="text-transform: uppercase;font-weight: bold">Tổng cộng</td>
                        <td class="total_bookings" style="font-weight: bold;text-align: center"></td>
                        <td class="total_services" style="font-weight: bold;text-align: center"></td>
                        <td class="total_amount" style="font-weight: bold;text-align: right"></td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Xem Chi Tiết Lịch Hẹn -->
    <div class="modal fade" id="modal_view_detail" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Chi tiết danh sách lịch hẹn</h4>
                </div>
                <div class="modal-body" id="content_modal_view_detail">
                    <div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script>
        $(document).ready(function (){
            searchAjaxSelect2('#customer_search','admin/category/searchCustomer');
            search_daterangetimepicker('date_search');
        })
        var fnserverparams = {
            'branch_search': '#branch_search',
            'status_search': '#status_search',
            'customer_search': '#customer_search',
            'date_search': '#date_search',
        };
        var oTable;
        oTable = InitDataTable('#table_report_booking', 'admin/report/getListReportBooking', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/report/getListReportBooking",
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
                {data: 'customer', name: 'customer', orderable: false},
                {data: 'total_bookings', name: 'total_bookings', width: "120px", orderable: false},
                {data: 'total_services', name: 'total_services', width: "130px", orderable: false},
                {data: 'total_amount', name: 'total_amount', width: "130px", orderable: false},
                {data: 'options', name: 'options', width: "80px", orderable: false, className: "text-center"}
            ],
        });

        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        $('#table_report_booking').on('draw.dt', function () {
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
            var total_services = sumColumn(3);
            var total_amount = sumColumn(4);

            $("#table_report_booking").find('tfoot .total_bookings').html(formatNumber(total_bookings));
            $("#table_report_booking").find('tfoot .total_services').html(formatNumber(total_services));
            $("#table_report_booking").find('tfoot .total_amount').html(formatMoney(total_amount));
        });

        // Event listener for showing modal
        $('body').on('click', '.btn-view-detail', function() {
            var clientId = $(this).attr('data-client-id') || '';
            var customerPhone = $(this).attr('data-customer-phone') || '';
            
            $('#modal_view_detail').modal('show');
            $('#content_modal_view_detail').html('<div class="text-center" style="padding: 20px;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');

            var postData = {
                client_id: clientId,
                customer_phone: customerPhone
            };

            for (var key in fnserverparams) {
                postData[key] = $(fnserverparams[key]).val();
            }

            $.ajax({
                url: 'admin/report/getModalBookingDetail',
                type: 'POST',
                data: postData,
                success: function(response) {
                    $('#content_modal_view_detail').html(response);
                },
                error: function(err) {
                    $('#content_modal_view_detail').html('<div class="alert alert-danger">Đã có lỗi xảy ra.</div>');
                }
            });
        });
    </script>
@endsection
