<form id="ApplicationCommentForm" action="admin/application_comments/submit/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog" role="document" style="max-width: 480px; margin: 10% auto;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header" style="position: relative !important; text-align: left !important; display: block !important; padding: 15px 20px; border-bottom: 1px solid #f3f4f6; width: 100% !important; min-height: 50px !important;">
                <h4 class="modal-title" style="font-weight: 700; font-size: 16px; color: #1f2937; margin: 0 !important; float: left !important; text-align: left !important; line-height: 1.4 !important; width: auto !important; display: inline-block !important;">{{ $title }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: absolute !important; right: 20px !important; top: 12px !important; border: none !important; background: transparent !important; font-size: 22px !important; color: #9ca3af !important; line-height: 1 !important; padding: 0 !important; cursor: pointer !important; transition: color 0.2s !important; float: none !important; opacity: 1 !important;" onmouseover="this.style.color='#4b5563'" onmouseout="this.style.color='#9ca3af'">&times;</button>
                <div style="clear: both !important;"></div>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="id" value="{{!empty($comment) ? $comment['id'] : 0}}" >
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="content" style="font-weight: 600; font-size: 13px; color: #4b5563; margin-bottom: 8px; display: block;">Nội dung mẫu gợi ý</label>
                            <textarea name="content" class="form-control" rows="4" style="resize: vertical; border-radius: 8px; border: 1.5px solid #e5e7eb; padding: 10px; width: 100%; font-size: 14px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#d4a017'" onblur="this.style.borderColor='#e5e7eb'" required>{{$comment->content ?? ''}}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid #f3f4f6; display: flex; justify-content: flex-end; gap: 10px; background: #f9fafb;">
                <button class="btn btn-secondary" type="button" data-dismiss="modal" style="background: #f3f4f6; color: #4b5563; border: none; font-weight: 600; padding: 8px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">Hủy bỏ</button>
                <button class="btn btn-default" id="saveBtn" style="background: #d4a017; color: white; border: none; font-weight: 600; padding: 8px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#b8860b'" onmouseout="this.style.background='#d4a017'">Lưu Lại</button>
            </div>
        </div>
    </div>
</form>
<script>
    $("#ApplicationCommentForm").validate({
        rules: {
            content: {
                required: true
            }
        },
        messages: {
            content: {
                required: "{{lang('dt_required')}}"
            }
        },
        submitHandler: function (form) {
            var url = form.action;
            var form = $(form),
                formData = new FormData(),
                formParams = form.serializeArray();

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
                        $('#dtModal').modal('hide');
                        alert_float('success', data.message);
                    } else {
                        alert_float('error', data.message);
                    }
                })
                .fail(function (err) {
                    var htmlError = '';
                    if (err.responseJSON && err.responseJSON.errors) {
                        for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                    } else {
                        htmlError = 'Đã xảy ra lỗi';
                    }
                    alert_float('error', htmlError);
                });
            return false;
        }
    });
</script>
