@extends('admin.layouts.index')
@section('content')
    <style>
        .text-danger {
            color: #f05050 !important;
        }
        .child-row td:first-child::before {
            /*content: "└─────";*/
            position: absolute;
            left: 15px;
            color: #0d6efd;
            font-weight: bold;
        }
        .child-row td {
            padding-left: 30px;
            font-size: 13px;
            color: #6c757d;
            background: #f8f9fa;
        }

        .not-sortable{
            background: #ededed;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" href="admin/variant/detail/0">{{ lang('dt_create') }}</a>
            </div>
            <h4 class="page-title text-capitalize">{{ lang('variant') }}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{ lang('dt_index') }}</a></li>
                <li><a href="admin/variant/list">{{ lang('c_list_variant') }}</a></li>
                <li class="active">{{ lang('dt_list') }}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="hide checkbox checkbox-custom checkbox-inline mbot10">
                    <input type="checkbox" id="active_order_by" value="1">
                    <label for="active_order_by">{{lang('active_order_by')}}</label>
                </div>
                <hr/>
                <table id="table_variant" class="table table-bordered table-condensed sortableMain table_render">
                    <thead>
                    <tr>
                        <th class="text-center">{{ lang('#') }}</th>
                        <th class="text-left">{{ lang('variant') }}</th>
                        <th class="text-center">{{ lang('c_active') }}</th>
                        <th class="text-center">{{ lang('dt_actions') }}</th>
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

        function changeType(_this, _id, _status) {

            $('.po-custom').popover('hide');
            var dataGET = {};
            dataGET['id'] = _id;
            dataGET['status'] = _status;

            $.ajax({
                type: "GET",
                url: 'admin/variant/changeType',
                data: dataGET,
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
        }

        oTable = InitDataTable('#table_variant', 'admin/variant/getTable', {
            'order': [
                [0, 'asc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/variant/getTable",
                "data": function(d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function(json) {
                    return json.data;
                }
            },
            columnDefs: [{
                "render": function(data, type, row) {
                    return `<div class="text-center">
                                    <a style="font-size: 25px; color: #0e3063;" href="javascript:void(0)" class="rows-child fa fa-caret-right" data-id="${row['id']}"></a>
                                </div>
                                `;
                },
                data: 'id',
                name: 'id',
                width: "50px"
            },
                {
                    data: 'name',
                    name: 'name',
                    width: "200px"
                },
                {
                    "render": function(data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'active',
                    name: 'active',
                    width: '80px'
                },
                {
                    data: 'options',
                    name: 'options',
                    orderable: false,
                    searchable: false,
                    width: "100px"
                },

            ]
        });


        var listTr = {};
        var activeFirst = 0;
        $('#table_variant').on('draw.dt', function () {
            if(activeFirst == 0) {
                $('td .rows-child').trigger('click');
                activeFirst = 1;
            }
            else {
                $.each(listTr, function (index, value) {
                    if (value == 1) {
                        $(`.rows-child[data-id="${index}"]`).trigger('click');
                    }
                })
            }
        });

        $('body').on('click', 'td .rows-child', function() {
            var tr = $(this).closest('tr');
            var row = oTable.row(tr);
            if (row.child.isShown()) {
                $(this).removeClass('fa-caret-down');
                $(this).addClass('fa-caret-right');
                row.child.hide();
                tr.removeClass('shown');
                listTr[tr.find('.rows-child').attr('data-id')] = 0;
            } else {
                // Open this row
                $(this).removeClass('fa-caret-right');
                $(this).addClass('fa-caret-down');
                row.child(loadInfoOrder(row.data())).show();
                var childRow = $(row.node()).next('tr').addClass('not-sortable');
                loadInitFileStyle();
                tr.addClass('shown');
                listTr[tr.find('.rows-child').attr('data-id')] = 1;
            }
        });

        function loadInfoOrder(cData) {
            if (typeof cData === "undefined" || cData == null || !cData) return '';
            var dataChild = JSON.parse(cData['child']);
            var tableChild = $(`<table class="table not-sortable"></table>`);
            var thead = $(`<thead></thead>`);
            thead.append(`<tr class="not-sortable">
                                <th class="text-center">{{lang('c_stt')}}</th>
                                <th>{{lang('c_name_variant_options')}}</th>
                                <th class="text-center">{{ lang('c_active') }}</th>
                                <th class="text-right">{{ lang('dt_actions') }}</th>
                            </tr>`);
            var tbody = $(`<tbody></tbody>`);
            $.each(dataChild, function(index, value) {
                tbody.append(`<tr class="not-sortable">
                            <td class="text-center">${index + 1}</td>
                            <td class="text-left">${value.name}</td>
                            <td>${value.active}</td>
                            <td class="text-right">
                                <a class="btn btn-icon btn-default dt-modal" href="admin/variant/detail_child/${value.id_variant}/${value.id}"><i class="fa fa-edit"></i></a>
                                <a type="button" class="btn btn-danger btn-icon po-delete" data-container="body" data-html="true"
                                    data-toggle="popover" data-placement="left"
                                    data-content="<button href='admin/variant/delete_child/${value.id}'
                                    class='btn btn-danger dt-delete' style='margin-right: 5px;'>{{lang('delete')}}</button><button class='btn btn-default po-close'>{{lang('close')}}</button>" data-original-title="" title=""><i class="fa fa-remove"></i>
                                </a>
                            </td>
                        </tr>`);
            })
            tableChild.append(thead);
            tableChild.append(tbody);
            var tDivRow = $(`<div class="col-md-1"></div>`);
            var tDiv = $(`<div class="col-md-11"></div>`);
            tDiv.append(tableChild);
            tDivShow = $(`<div></div>`);
            tDivShow.append(tDivRow);
            tDivShow.append(tDiv);
            return tDivShow;
        }

        // $('#active_order_by').change(function() {
        //     if($(this).prop('checked')) {
        //         $('.sortableMain tbody').sortable("enable");
        //     }
        //     else {
        //         $('.sortableMain tbody').sortable("disable");
        //     }
        // })
        // $('.sortableMain tbody').sortable({
        //     items: 'tr:not(.not-sortable)',
        //     distance: 20,
        //     start: function() {},
        //     stop: function() {
        //         EventUpdateSorMain(this);
        //     }
        // });
        //
        // $('#active_order_by').trigger('change');
        //
        //
        //
        // function EventUpdateSorMain(_this) {
        //     if (confirm('Bạn có chắc muốn sắp xếp danh mục?')) {
        //         var inputStt = $('.inputStt');
        //         var limit = $('[name="table_variant_length"]').val();
        //         var page = $('#table_products_filter_paginate').find('.paginate_button.active').text();
        //         var nextStt = (limit * page) - limit + 1;
        //         $.each(inputStt, function(index, value) {
        //             $(value).val((nextStt + index));
        //             $(value).parent('td').find('.row_stt').text((nextStt + index));
        //         })
        //         var data = {};
        //         if (typeof(csrfData) !== 'undefined') {
        //             data[csrfData['token_name']] = csrfData['hash'];
        //         }
        //         var list_order_by = {};
        //         $.each(inputStt, function(index, value) {
        //             list_order_by[$(value).attr('data-id')] = $(value).val();
        //         })
        //         data['list_order_by'] = list_order_by;
        //
        //         $.ajax({
        //             type: "POST",
        //             url: 'admin/variant/order_by',
        //             data: data,
        //             dataType: "json",
        //             success: function(response) {
        //                 if (response.result) {
        //                     alert_float('success', response?.message);
        //                 } else {
        //                     alert_float('error', response?.message);
        //                 }
        //                 oTable.draw(false);
        //             }
        //         });
        //     } else {
        //         oTable.draw(false);
        //     }
        // }
    </script>
@endsection
