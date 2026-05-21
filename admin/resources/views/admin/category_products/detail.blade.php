<form id="CategoryForm" action="admin/category_products/submit/{{!empty($id) ? $id : '0'}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="show_error" style="color: red"></div>
                        <div class="form-group">
                            <label for="code">{{lang('c_code_category_products')}}</label>
                            <input type="text" name="code" class="form-control code" value="{{!empty($category) ? $category->code : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('c_name_category_products')}} <i class="text-danger required">*</i></label>
                            <input type="text" name="name" class="form-control name" value="{{!empty($category) ? $category->name : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="image">{{lang('dt_image')}}</label>
                            <input type="file" name="image[]" multiple id="image" class="filestyle image"
                                   data-buttonbefore="true">
                            @if(!empty($category) && $category->image != null)
                                <input type="hidden" name="image_old" id="image_old"
                                       class="image_old"
                                       data-buttonbefore="true" value="{{!empty($category) ? $category->image : ''}}">
                                {!! loadImageNew(asset('storage/'.$category->image),'260px','','',false,'150px') !!}
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="max_product_review">{{lang('max_product_review')}}</label>
                            <input type="text" name="max_product_review" class="form-control max_product_review" value="{{!empty($category) ? $category->max_product_review : ''}}">
                        </div>

                        <div class="form-group">
                            <label for="content">{{lang('c_content_category_products')}}</label>
                            <textarea name="content" class="form-control content">{{!empty($category) ? $category->content : ''}}</textarea>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary waves-effect waves-light"
                        type="submit">{{lang('dt_save')}}</button>
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">{{lang('dt_close')}}</button>
            </div>
        </div>
    </div>
</form>
<script>
    $("#CategoryForm").validate({
        rules: {
            'name' : "required"
        },
        messages: {
            firstname: "{{lang('Please enter name')}}",
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
                        $('.modal-dialog .close').trigger('click');
                        alert_float('success',data.message);
                    } else {
                        $(".show_error").html(data.message);
                        alert_float('error',data.message);
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
