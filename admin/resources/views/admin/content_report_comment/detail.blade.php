<form id="ContentReportCommentForm" action="admin/content_report_comment/submit/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{{ $title }}</h2>
                <button type="button" class="close" data-dismiss="modal" title="Đóng">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 1L1 13M1 1L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="id" value="{{!empty($content_report_comment) ? $content_report_comment['id'] : 0}}" >
                        <div class="form-group">
                            <label for="content">{{lang('dt_name_content_report_comment')}}</label>
                            <textarea name="content" class="form-control" rows="4" style="resize: vertical; border-radius: 8px;" required>{{$content_report_comment->content ?? ''}}</textarea>
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
    $("#ContentReportCommentForm").validate({
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
                        $('.modal-overlay .close').trigger('click');
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
