@include('admin.introduction_app.list')
@section('script')
    <script>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_introduction_app', 'admin/introduction_app/getBanner', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/introduction_app/getTable",
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
                {data: 'title', name: 'title'},
                {data: 'content', name: 'content'},
                {data: 'image', name: 'image', width: "250px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'active', name: 'active',width: "80px"
                },
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },
            ]
        });
    </script>
@endsection
