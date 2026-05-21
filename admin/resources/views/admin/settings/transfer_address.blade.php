@include('admin.transfer_address.list')
@section('script')
    <script>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_transfer_address', 'admin/transfer_address/getTransferAddress', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/transfer_address/getTransferAddress",
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
                {data: 'transfer_address', name: 'transfer_address'},
                {data: 'Network', name: 'Network',width: "150px"},
                {data: 'title', name: 'title',width: "150px"},
                {data: 'title_address', name: 'title_address',width: "150px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'active', name: 'active',width: "120px"},
                {data: 'image', name: 'image',width: "150px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
