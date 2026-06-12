@include('admin.application_comments.list_settings')
@section('script')
    <script>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_application_comments', 'admin/application_comments/getTable', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/application_comments/getTabletem",
                "data": function (d) {
                    d['_locale'] = '{{session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang)}}';
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
                    "render": function (data, type, row) {
                        return `<div class="text-center">${row['DT_RowIndex']}</div>`;
                    },
                    data: 'id', name: 'id', width: "80px", orderable: true
                },
                {data: 'content', name: 'content'},
                {data: 'options', name: 'options', orderable: false, searchable: false, width: "150px"}
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
    </script>
@endsection
