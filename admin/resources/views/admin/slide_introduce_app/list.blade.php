@extends('admin.layouts.index')
@section('content')
    <style>
        .star{
            color: #fbbf24;
        }
        .status_now_2 {
            background: #2DD4BF;
            color: white;
        }
        .status_now_1 {
            background: #F47690;
            color: white;
        }
        .status_now_3 {
            background: #E5E7EB;
            color: #6B7280;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a  class="btn btn-default dt-modal" href="admin/slide_introduce_app/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('slide_introduce_app')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/slide_introduce_app/list">{{lang('slide_introduce_app')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">

            <div class="card-box table-responsive">
                <div class="checkbox checkbox-custom checkbox-inline mbot10">
                    <input type="checkbox" id="active_order_by" value="1">
                    <label for="active_order_by">{{lang('active_order_by')}}</label>
                </div>
                <hr/>
                <table id="table_slide_introduce_app" class="table table-bordered sortableMain">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_image')}}</th>
                        <th class="text-center">{{lang('c_title')}}</th>
                        <th class="text-center">{{lang('dt_active')}}</th>
                        <th class="text-center">{{lang('date_create')}}</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
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
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_slide_introduce_app', 'admin/slide_introduce_app/getTable', {
            'order': [
                [0, 'asc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/slide_introduce_app/getTable",
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
                        return `<div class="text-center">${data}</div>
                                <span class="row_stt hide">${row['order_by']}</span>
                                <input class="inputStt" type="hidden" data-id="${row['id']}" value="${row['order_by']}">
                                `;
                    },
                    data: 'DT_RowIndex', name: 'DT_RowIndex',width: "80px" },
                {data: 'image', name: 'image',width: "200px"},
                {data: 'title', name: 'title'},
                {data: 'active', name: 'active',width: "80px"},
                {data: 'created_at', name: 'created_at',width: "80px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

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
                var limit = $('[name="table_slide_introduce_app_length"]').val();
                var page = $('#table_slide_introduce_app_paginate').find('.paginate_button.active').text();
                var nextStt = (limit * page) - limit + 1;
                $.each(inputStt, function(index, value) {
                    $(value).val((nextStt + index));
                    $(value).parent('td').find('.row_stt').text((nextStt + index));
                })
                var data = {};
                if (typeof(csrfData) !== 'undefined') {
                    data[csrfData['token_name']] = csrfData['hash'];
                }
                var list_order_by = {};
                $.each(inputStt, function(index, value) {
                    list_order_by[$(value).attr('data-id')] = $(value).val();
                })
                data['list_order_by'] = list_order_by;

                $.ajax({
                    type: "POST",
                    url: 'admin/slide_introduce_app/order_by',
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
