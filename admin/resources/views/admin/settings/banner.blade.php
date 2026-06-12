@include('admin.banner.list_settings')
@section('script')
    <script>
        var fnserverparams = {};
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
                    data: 'id', name: 'id', width: "60px", orderable: true },
                { data: 'image', name: 'image', width: "150px" },
                {
                    "render": function (data, type, row) {
                        return data;
                    },
                    data: 'active', name: 'active', width: "100px"
                },
                { data: 'options', name: 'options', orderable: false, searchable: false, width: "100px" },
            ]
        });

        $('.btn-dt-reload').click(function(e) {
            e.preventDefault();
            oTable.draw('page');
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
