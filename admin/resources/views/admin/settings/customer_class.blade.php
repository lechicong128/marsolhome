<style>
    .image_customer_class{
        display: none !important;
    }
</style>
<div class="title-statistic title_left">
    <span class="mleft5 mright5">
        {{lang('c_setting_customer_class')}}
    </span>
</div>
<div class="col-md-12">
    <label> {{lang('c_setting_customer_class')}}</label>
    <table class="table-bordered table" id="table_customer_class">
        <thead>
        <tr>
            <th style="width: 50px;text-align: center">STT</th>
            <th class="text-center" style="width: 90px">Hình ảnh</th>
            <th class="text-center">Hạng</th>
            <th class="text-center" style="width: 150px">Số tiền từ (USD)</th>
            <th class="text-center" style="width: 150px">Số tiền đến (USD)</th>
            <th class="text-center" style="width: 150px">Phần trăm thưởng</th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>

</div>
@section('script')
<script>
    let loadCustomerClass =  function(){
        $.ajax({
            url: 'admin/settings/loadCustomerClass',
            type: 'POST',
            dataType: 'json',
            cache: false,
        })
            .done(function (data) {
              $("#table_customer_class").find('tbody').html(data.html);
                $(".image_customer_class").change(function (event){
                    id = $(this).attr('data-id');
                    console.log(id);
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();

                        // Khi đọc tệp xong, hiển thị hình ảnh
                        reader.onload = function(e) {
                            $(`.image_preview_${id}`).attr('src', e.target.result).show();
                        };

                        reader.readAsDataURL(file);
                    }
                });
            })
            .fail(function () {

            });
        return false;
    }
    loadCustomerClass();
    function clickImageCustomerClass(_this){
        id = $(_this).attr('data-id');
        $(`#image_${id}`).click();
    }
</script>
@endsection
