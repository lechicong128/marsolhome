@extends('admin.layouts.index')
@section('page_title', lang('dt_client'))
@section('content')
<style>
    .mod-page-header{
        margin-bottom: 15px !important;
    }
    .ul-toolbar{
        margin-bottom: 10px !important;
    }
    .nav.nav-tabs{
        box-shadow: none !important;
        margin-bottom: 4px !important;
    }
</style>
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">{{ $title }}</h1>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="ul-toolbar">
    <div class="ul-search">
        <i class="fa fa-search"></i>
        <input type="text" id="ul-search-input" placeholder="Tìm theo tên,sđt,email...">
    </div>
    <div class="dt-buttons btn-group"><a class="btn btn-default btn-default-dt-options btn-dt-reload"><span><i class="fa fa-refresh"></i></span></a></div>
</div>
<ul class="nav nav-tabs">
    <li class="H-search active"><a data-toggle="tab" data-id="-1">Tất cả (<b class="count_all">0</b>)</a></li>
    <li class="H-search"><a data-toggle="tab" data-id="0">Khách hàng (<b class="count_type_0">0</b>)</a></li>
    <li class="H-search"><a data-toggle="tab" data-id="1">Nhân viên sale (<b class="count_type_1">0</b>)</a></li>
    <li class="H-search"><a data-toggle="tab" data-id="2">Admin (<b class="count_type_2">0</b>)</a></li>
</ul>
<span class="group_search">
    <input type="hidden" name="type_client_search" id="type_client_search" value="-1">
</span>

<!-- Table Card -->
<div class="ul-card">
    <!-- Table -->
    <div class="">
        <table id="table_client" class="table">
            <thead>
                <tr>
                    <th style="text-align: center;">Ảnh đại diện</th>
                    <th style="text-align: center;">Tên thành viên</th>
                    <th style="text-align: center;">SĐT</th>
                    <th style="text-align: center;">Email</th>
                    <th style="text-align: center;">Loại thành viên</th>
                    <th style="text-align: center;">Ngày đăng ký</th>
                    <th style="text-align: center;">Trạng thái</th>
                    <th style="text-align: center;">Thao tác</th>
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
        var fnserverparams = {
            'type_client_search' : 'input[name="type_client_search"]',
            'active_search' : '#active_search',
            'date_search' : '#date_search',
        };
        $('.H-search').click(function() {
            $('input[name="type_client_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })

        $(function() {
            search_daterangepicker('date_search');
            oTable = InitDataTableNew('#table_client', 'api/customer/getListCustomer', {
                'order': [
                    [5, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "api/customer/getListCustomer",
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
                    {data: 'avatar', name: 'avatar',width: "90px",},
                    {data: 'fullname', name: 'fullname'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'phone', name: 'phone'
                    },
                    {data: 'email', name: 'email'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'type_client', name: 'type_client'
                    },
                    {data: 'created_at', name: 'created_at'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'active', name: 'active'
                    },
                    {data: 'options', name: 'options', orderable: false, searchable: false,width: "200px" },

                ]
            })
            $.each(fnserverparams, function(filterIndex, filterItem) {
                $('' + filterItem).on('change', function() {
                    oTable.draw('page')
                });
            });
            $('#table_client').on('draw.dt', function () {
                countAll();
            });

            function countAll() {
                var data = {};
                $.each(fnserverparams, function(filterIndex, filterItem) {
                    data[filterIndex] = $(filterItem).val();
                });
                $.post('api/customer/countAll', data, function(response) {
                    var total = 0;
                    if(response.arrType.length > 0){
                        $.each(response.arrType, function(index, value) {
                            $(`.count_type_${value.id}`).text(formatNumber(value.total));
                            total += parseFloat(value.total);
                        })
                    }
                    $(`.count_all`).text(formatNumber(total));
                })
            }

            oTable.on('init', function () {

                if (!oTable) return;

                let h = getTableHeight();
                console.log(h);

                $(oTable.table().container())
                    .find('.dataTables_scrollBody')
                    .css('height', h);
                $(oTable.table().container())
                    .find('.dataTables_scrollBody')
                    .css('max-height', h);
                oTable.columns.adjust();

            }); 
            $(window).on('resize', function () {
                if (!oTable) return;

                let h = getTableHeight();

                $(oTable.table().container())
                    .find('.dataTables_scrollBody')
                    .css('height', h);
                $(oTable.table().container())
                    .find('.dataTables_scrollBody')
                    .css('max-height', h);
                oTable.columns.adjust();
            });

        })

        function changeStatus(client_id,status){
            $.ajax({
                url: 'admin/clients/changeStatusTypeLeader',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    client_id: client_id,
                    status: status,
                },
            })
                .done(function (data) {
                    if(data.result){
                        alert_float('success',data.message);
                    } else {
                        alert_float('error',data.message);
                    }
                    oTable.draw('page');
                })
                .fail(function () {

                });
            return false;
        }
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

        function getTableHeight() {

            let windowHeight = $(window).height();

            let toolbar = $('.mod-page-header').outerHeight(true) || 0;
            let filters = $('.ul-toolbar').outerHeight(true) || 0;
            let tabs = $('.nav-tabs').outerHeight(true) || 0;

            let dataTablesExtras = 280; // search + length + padding + head

            let bottomPadding = 15; // pagination + margin

            let totalUsed =
                toolbar +
                filters +
                tabs +
                dataTablesExtras +
                bottomPadding;

            return (windowHeight - totalUsed) + 'px';
        }
          $('#table_client').on('shown.bs.dropdown', '.dropdown', function () {
            let dropdown = $(this);
            let rect = this.getBoundingClientRect();

            if (window.innerHeight - rect.bottom < 250) {
                dropdown.addClass('dropup');
            } else {
                dropdown.removeClass('dropup');
            }

            if (window.innerWidth - rect.right < 250) {
                dropdown.addClass('dropleft');
            } else {
                dropdown.removeClass('dropleft');
            }
        });
    </script>
@endsection
