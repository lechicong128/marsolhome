@extends('admin.layouts.index')
@section('content')
    @include('admin.featured_locations.list_settings')
@endsection
@section('script')
    <script>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_featured_locations', 'admin/featured_locations/getFeaturedLocations', {
            'searching': false,
            'paging': false,
            'info': false,
            'order': [
                [0, 'asc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/featured_locations/getFeaturedLocations",
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
                                <span class="row_stt hide">${row['display_order'] ?? 0}</span>
                                <input class="inputStt" type="hidden" data-id="${row['id']}" value="${row['display_order'] ?? 0}">`;
                    },
                    data: 'id', name: 'id', width: "60px", orderable: true },
                { data: 'province_id', name: 'province_id' },
                { data: 'custom_name', name: 'custom_name' },
                { data: 'image_url', name: 'image_url', width: "150px" },
                {
                    "render": function (data, type, row) {
                        return data;
                    },
                    data: 'is_active', name: 'is_active', width: "100px"
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
            if (confirm('Bạn có chắc muốn sắp xếp địa điểm nổi bật?')) {
                var inputStt = $('.inputStt');
                var len = inputStt.length;
                
                $.each(inputStt, function(index, value) {
                    var val = index + 1; // 1 to 6
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
                    url: 'admin/featured_locations/order_by',
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
