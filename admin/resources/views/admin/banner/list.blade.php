@extends('admin.layouts.index')
@section('content')
    <style>
        .inline-flex {
            display: inline-flex !important;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/banner/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('dt_banner')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/settings?group=banner">{{lang('dt_banner')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
     <!--        <ul class="nav nav-tabs">
                <li class="H-search active cursor">
                    <a style=";white-space: nowrap;" data-toggle="tab"
                       data-id="0">{{lang('banner_website')}}
                    </a>
                </li>
                <li class="H-search cursor">
                    <a style=";white-space: nowrap;" data-toggle="tab"
                       data-id="1">{{lang('banner_app')}}
                    </a>
                </li>
            </ul> -->
            <span class="group_search">
                <input type="hidden" name="status_search" id="status_search" value="1">
            </span>
            <div class="card-box table-responsive" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #f3f4f6; padding: 20px; background: #ffffff;">
                <div class="checkbox checkbox-custom checkbox-inline mbot10">
                    <input type="checkbox" id="active_order_by" value="1">
                    <label for="active_order_by">{{lang('active_order_by')}}</label>
                </div>
                <hr/>
                <table id="table_banner" class="table table-striped table-bordered table-hover sortableMain" style="border-collapse: collapse; width: 100%;">
                    <thead>
                    <tr style="background: #f9fafb;">
                        <th class="text-center" style="font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; width: 80px;">{{lang('dt_stt')}}</th>
                        <th class="text-center" style="font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">{{lang('dt_image')}}</th>
                        <th class="text-center" style="font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; width: 120px;">{{lang('dt_active')}}</th>
                        <th class="text-center" style="font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; width: 150px;">{{lang('dt_actions')}}</th>
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
        var fnserverparams = {
            'status_search': '#status_search'
        };
        $('.H-search').click(function() {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })

        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        var oTable;
        oTable = InitDataTable('#table_banner', 'admin/banner/getBanner', {
            'searching': false,
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/banner/getBanner",
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
                        return `<div class="text-center" style="font-weight: 600; color: #4b5563;">${row['DT_RowIndex']}</div>
                                <span class="row_stt hide">${row['order_by'] ?? 0}</span>
                                <input class="inputStt" type="hidden" data-id="${row['id']}" value="${row['order_by'] ?? 0}">`;
                    },
                    data: 'id', name: 'id', width: "80px", orderable: true },
                { data: 'image', name: 'image' },
                { data: 'active', name: 'active', width: "120px" },
                { data: 'options', name: 'options', orderable: false, searchable: false, width: "150px" },
            ]
        });

        $('#active_order_by').change(function() {
            if($(this).prop('checked')) {
                $('.sortableMain tbody').sortable("enable");
            }
            else {
                $('.sortableMain tbody').sortable("disable");
            }
        })
        $('.sortableMain tbody').sortable({
            items: 'tr:not(.not-sortable)',
            distance: 20,
            start: function() {},
            stop: function() {
                EventUpdateSorMain(this);
            }
        });

        $('#active_order_by').trigger('change');

        function EventUpdateSorMain(_this) {
            if (confirm('Bạn có chắc muốn sắp xếp danh mục?')) {
                var inputStt = $('.inputStt');
                var limit = $('[name="table_banner_length"]').val() || 15;
                var page = $('#table_banner_paginate').find('.paginate_button.active').text() || 1;
                var nextStt = (limit * page) - limit + 1;
                var len = inputStt.length;
                
                $.each(inputStt, function(index, value) {
                    var val = nextStt + len - 1 - index;
                    $(value).val(val);
                    $(value).parent('td').find('.row_stt').text(val);
                });

                var data = {};
                var list_order_by = {};
                $.each(inputStt, function(index, value) {
                    list_order_by[$(value).attr('data-id')] = $(value).val();
                });
                data['list_order_by'] = list_order_by;

                $.ajax({
                    type: "POST",
                    url: 'admin/banner/order_by',
                    data: data,
                    dataType: "json",
                    success: function(response) {
                        if (response.result) {
                            alert_float('success', response.message);
                        } else {
                            alert_float('error', response.message);
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
