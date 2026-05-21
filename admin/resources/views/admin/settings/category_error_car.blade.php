@include('admin.category_error_car.list')
@section('script')
<script>
    var fnserverparams = {};
    var oTable;
    oTable = InitDataTable('#table_category_error_car', 'admin/category_error_car/getCategoryErrorCar', {
        'order': [
            [0, 'desc']
        ],
        'responsive': true,
        "ajax": {
            "type": "POST",
            "url": "admin/category_error_car/getCategoryErrorCar",
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
            {data: 'name', name: 'name'},
            {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

        ]
    });
</script>
@endsection
