<form id="departmentForm" action="admin/department/submit/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                 <h2 class="modal-title">{{ $title }}</h2>
                <button type="button" class="close" data-dismiss="modal" title="Đóng">
                    <!-- Sử dụng SVG cho nút Close sắc nét -->
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 1L1 13M1 1L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="id" value="{{!empty($department) ? $department['id'] : 0}}" >
                        <div class="show_error" style="color: red"></div>
                        <div class="form-group">
                            <label for="code">{{lang('dt_code_department')}}</label>
                            <input type="text" id="code" name="code" required value="{{!empty($department) ? $department['code'] : ''}}" class="form-control code">
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('dt_name_department')}}</label>
                            <input type="text" id="name" name="name" required class="form-control name" value="{{!empty($department) ? $department['name'] : ''}}">
                        </div>
                    </div>
                </div>

            <div class="modal-footer">
                <button class="btn btn-default" id="saveBtn">Lưu Lại</button>
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy bỏ</button>
            </div>
        </div>
    </div>
</form>
<script>
    $("#departmentForm").validate({
        rules: {
        },
        messages: {
        },
        submitHandler: function (form) {
            var url = form.action;
            var form = $(form),
                formData = new FormData(),
                formParams = form.serializeArray();

            $.each(form.find('input[type="file"]'), function (i, tag) {
                $.each($(tag)[0].files, function (i, file) {
                    formData.append(tag.name, file);
                });
            });
            $.each(formParams, function (i, val) {
                formData.append(val.name, val.value);
            });

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
            })
                .done(function (data) {
                    if (data.result) {
                        oTable.draw();
                        $('.modal-overlay .close').trigger('click');
                        alert_float('success',data.message);
                    } else {
                        htmlError = '';
                        if (data.message.length > 0){
                            $.each(data.message,function (k,v){
                                htmlError += `<div>${v}</div>`;
                            })
                        }
                        $(".show_error").html(htmlError);
                        alert_float('error',htmlError);
                    }
                })
                .fail(function (err) {
                    htmlError = '';
                    for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                        htmlError += `<div>${message}</div>`;
                    }
                    $(".show_error").html(htmlError);
                    alert_float('error',htmlError);
                });
            return false;
        }
    });
</script>
