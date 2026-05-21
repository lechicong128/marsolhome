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
                   aria-expanded="false" href="admin/terms/detail?types_terms={{$type ?? ''}}">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('c_terms'.$type)}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/settings?group={{$type}}">{{lang('c_terms' . $type)}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <input class="form-control" type="hidden" id="types_terms" name="types_terms" value="{{$type ?? 'terms'}}">
                <div class="checkbox checkbox-custom checkbox-inline mbot10">
                    <input type="checkbox" id="active_order_by" value="1">
                    <label for="active_order_by">{{lang('active_order_by')}}</label>
                </div>
                <hr/>
                <table id="table_terms" class="table table-bordered sortableMain">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_title_terms' . $type)}}</th>
                        <th class="text-center">{{lang('dt_active')}}</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
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
            'types_terms': '#types_terms',
        };
        var oTable;
        oTable = InitDataTable('#table_terms', 'admin/terms/getTable', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/terms/getTable",
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

                {
                    "render": function(data, type, row) {
                        return `<div class="text-center ">${row['DT_RowIndex']}</div>
                                <span class="row_stt hide">${row['order_by']}</span>
                                <input class="inputStt" type="hidden" data-id="${row['id']}" value="${row['order_by']}">
                                `;
                    },
                    // "render": function (data, type, row) {
                    //     return `<div class="text-center">${data}</data>`;
                    // },
                    data: 'order_by', name: 'order_by',width: "80px"
                },
                {data: 'title', name: 'title'},
                {data: 'active', name: 'active',width: "100px" },
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
                var limit = $('[name="table_terms_length"]').val();
                var page = $('#table_terms_paginate').find('.paginate_button.active').text();
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
                    url: 'admin/terms/order_by',
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
