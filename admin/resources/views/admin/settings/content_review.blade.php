@include('admin.content_review.list')
@section('script')
    <script>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_content_review', 'admin/content_review/getContentReview', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/content_review/getContentReview",
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
                        return `<div class="text-center">${row['DT_RowIndex']}</data>`;
                    },
                    data: 'id', name: 'id',width: "80px", orderable: true },
                {data: 'content', name: 'content'},
                {data: 'type', name: 'type',width: "120px",visible: false},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
