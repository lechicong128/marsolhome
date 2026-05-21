@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default waves-effect waves-light"
                   href="admin/buy_treatment/detail">Thêm liệu trình</a>
            </div>
            <h4 class="page-title text-capitalize">Mua liệu trình</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li class="active">Danh sách liệu trình đã mua</li>
            </ol>
        </div>
    </div>

    <!-- Filter -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Khách hàng</label>
                            <select id="search_customer" class="form-control select2" style="width:100%" data-placeholder="Chọn khách hàng...">
                                <option></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Danh mục dịch vụ</label>
                            <select id="filter_category" class="form-control select2" style="width:100%">
                                <option value="">-- Tất cả --</option>
                                @foreach($category_services as $cat)
                                    <option value="{{$cat->id}}">{{$cat->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 hide">
                        <div class="form-group">
                            <label>Chi nhánh</label>
                            <select id="filter_branch" class="form-control select2" style="width:100%">
                                <option value="">-- Tất cả --</option>
                                @foreach($branches as $br)
                                    <option value="{{$br->id}}">{{$br->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select id="filter_status" class="form-control">
                                <option value="">-- Tất cả --</option>
                                <option value="active">Đang dùng</option>
                                <option value="completed">Đã hoàn thành</option>
                                <option value="cancelled">Đã huỷ</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button id="btn_search" class="btn btn-primary waves-effect waves-light">
                                    <i class="fa fa-search"></i> Tìm kiếm
                                </button>
                                <button id="btn_reset" class="btn btn-default waves-effect waves-light">
                                    <i class="fa fa-refresh"></i> Đặt lại
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <table id="table_buy_treatment" class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="text-center">STT</th>
                        <th class="text-center">Mã liệu trình</th>
                        <th class="text-center">Khách hàng</th>
                        <th class="text-center">Tên liệu trình</th>
                        <th class="text-center">Danh mục Dịch vụ</th>
                        {{-- <th class="text-center">Chi nhánh áp dụng</th> --}}
                        <th class="text-center">Số buổi</th>
                        <th class="text-center">Đã dùng / Còn lại</th>
                        <th class="text-center">Giá trị</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Ngày mua</th>
                        <th class="text-center" style="width:130px">Tác vụ</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    var oTable;
    $(document).ready(function () {
        var fnserverparams = {};

        oTable = InitDataTable('#table_buy_treatment', 'admin/buy_treatment/getTable', {
            'order': [[0, 'desc']],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/buy_treatment/getTable",
                "data": function (d) {
                    d.search_customer = $('#search_customer').val();
                    d.id_category     = $('#filter_category').val();
                    d.status          = $('#filter_status').val();
                },
                "dataSrc": function (json) { return json.data; }
            },
            columnDefs: [
                { data: 'DT_RowIndex',    name: 'DT_RowIndex',    width: '50px',
                  render: function(data){ return '<div class="text-center">'+data+'</div>'; }},
                { data: 'purchase_code',  name: 'purchase_code',  width: '140px', render: function(data, type, row) {
                    return '<a class="dt-modal" href="admin/buy_treatment/view/' + row.id + '"><b>' + data + '</b></a>';
                }},
                { data: 'customer_name',  name: 'customer_name' },
                { data: 'treatment_name', name: 'treatment_name' },
                { data: 'category_name',  name: 'cs.name', render: function(data) {
                    return data ? data : '<span class="text-muted">Áp dụng tất cả</span>';
                }},
                // { data: 'branch_name',    name: 'br.name',    width: '140px' },
                { data: 'total_sessions', name: 'total_sessions', width: '80px',  className: 'text-center' },
                { data: 'used_sessions',  name: 'used_sessions',  width: '120px', className: 'text-center' },
                { data: 'price',          name: 'price',          width: '120px', className: 'text-right' },
                { data: 'status',         name: 'status',         width: '120px', className: 'text-center' },
                { data: 'created_at',     name: 'created_at',     width: '130px', className: 'text-center' },
                { data: 'options',        name: 'options',        orderable: false, searchable: false, width: '130px' },
            ]
        });

        // Khởi tạo combobox khách hàng
        searchAjaxSelect2('#search_customer', 'admin/category/searchCustomer');

        $('#btn_search').on('click', function () { oTable.draw(); });
        $('#btn_reset').on('click', function () {
            $('#search_customer').val('').trigger('change');
            $('#filter_category, #filter_status').val('').trigger('change');
            oTable.draw();
        });
    });
</script>
@endsection
