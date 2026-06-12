@extends('admin.layouts.index')
@section('page_title', $title)
@section('content')
<style>
    /* Prevent dropdown clipping in tables */
    .ul-card, 
    .ul-card .table-responsive {
        overflow: visible !important;
    }
    .brs-20 {
        border-radius: 20px;
    }
    .inline-flex {
        display: inline-flex!important;
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
</style>

<!-- Page-Title -->
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">{{ $title }}</h1>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="ul-toolbar">
    <div class="ul-search">
        <i class="fa fa-search"></i>
        <input type="text" id="ul-search-input" placeholder="Tìm theo tên thành viên, số phiếu...">
    </div>
    <div class="dt-buttons btn-group">
        <a class="btn btn-default btn-default-dt-options btn-dt-reload">
            <span><i class="fa fa-refresh"></i></span>
        </a>
    </div>
</div>

<!-- Table Card -->
<div class="ul-card">
    <!-- Table -->
    <div class="table-responsive">
        <table id="table_application_comments" class="table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">STT</th>
                    <th class="text-center" style="width: 150px;">Ngày góp ý</th>
                    <th class="text-center" style="width: 120px;">Số phiếu</th>
                    <th class="text-center" style="width: 180px;">Thành viên</th>
                    <th class="text-center" style="width: 100px;">Đánh giá</th>
                    <th class="text-center">Nội dung góp ý</th>
                    <th class="text-center" style="width: 150px;">Danh sách ảnh</th>
                    <th class="text-center" style="width: 80px;">Tác vụ</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('script')
    <script>
        var oTable;
        $(function() {
            oTable = InitDataTable('#table_application_comments', 'admin/application_comments/getTable', {
                'order': [
                    [1, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/application_comments/getTable",
                    "data": function (d) {
                        d['_locale'] = '{{session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang)}}';
                    },
                    "dataSrc": function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'DT_RowIndex', name: 'DT_RowIndex', width: "50px", orderable: false, searchable: false
                    },
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'comment_date', name: 'comment_date', width: "150px"
                    },
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'ticket_number', name: 'ticket_number', width: "120px"
                    },
                    {data: 'member_name', name: 'member_name', width: "180px"},
                    {data: 'rating', name: 'rating', width: "100px"},
                    {data: 'content', name: 'content'},
                    {data: 'images', name: 'images', width: "150px"},
                    {data: 'options', name: 'options', orderable: false, searchable: false, width: "80px"}
                ]
            });

            var searchTimer;
            $('#ul-search-input').on('keyup', function() {
                clearTimeout(searchTimer);
                var val = this.value;
                searchTimer = setTimeout(function() {
                    oTable.search(val).draw();
                }, 400);
            });

            $('.btn-dt-reload').click(function() {
                oTable.draw('page');
            });
        });
    </script>
@endsection
