@include('admin.content_report_comment.list')
@section('script')
    <script>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_content_report_comment', 'admin/content_report_comment/getContentReportComment', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/content_report_comment/getContentReportComment",
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
                        return `<div class="text-center">${row['DT_RowIndex']}</div>`;
                    },
                    data: 'id', name: 'id',width: "80px", orderable: true },
                {data: 'content', name: 'content'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

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
