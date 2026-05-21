<form id="report_violationForm" action="admin/report_violation/submit/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog" style="width: 50%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="id" value="{{!empty($report_violation) ? $report_violation['id'] : 0}}">
                        <div class="form-group">
                            <label for="type">Loại vi phạm<span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-control">
                                <option value="post" {{(!empty($report_violation) && $report_violation['type'] == 'post') ? 'selected' : ''}}>Bài viết</option>
                                <option value="comment" {{(!empty($report_violation) && $report_violation['type'] == 'comment') ? 'selected' : ''}}>Bình luận</option>
                            </select>
                        </div>
                        <ul class="nav nav-tabs nav-justified row hide">
                            @foreach($language as $lang)
                                <li class="tab-btn {{$lang->is_default ? 'active' : ''}}">
                                    <a href="#tab-info-{{$lang->code}}" data-toggle="tab" aria-expanded="false">
                                        <span class="visible-xs"><i class="fa fa-home"></i></span>
                                        <span class="hidden-xs">{{$lang->name}}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="show_error" style="color: red"></div>
                        <div class="tab-content row" style="padding-left:10px;padding-right:10px;">
                            @foreach($language as $lang)
                                <div id="tab-info-{{$lang->code}}" class="tab-pane fade {{$lang->is_default ? 'in active' : ''}}" style="margin-top:15px;">
                                    <div class="form-group">
                                        <label for="name{{$lang->code}}">{{lang('dt_name_report_violation')}}</label>
                                        <input type="text" name="name[{{$lang->code}}]"
                                                  id="name_{{$lang->code}}"
                                                  class="form-control" value="{{$report_violation->translations[$lang->code]['name'] ?? ''}}"/>
                                    </div>
                                </div>
                            @endforeach
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
    $("#type").select2();
    $("#report_violationForm").validate({
        rules: {
            name: {
                required: true,
            },
        },
        messages: {
            name: {
                required: "{{ lang('dt_required') }}",
            },
        },
        submitHandler: function(form) {
            var url = form.action;
            var form = $(form),
                formData = new FormData(),
                formParams = form.serializeArray();

            $.each(form.find('input[type="file"]'), function(i, tag) {
                $.each($(tag)[0].files, function(i, file) {
                    formData.append(tag.name, file);
                });
            });
            $.each(formParams, function(i, val) {
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
                .done(function(data) {
                    if (data.result) {
                        oTable.draw();
                        $('.modal-dialog .close').trigger('click');
                        alert_float('success', data.message);
                    } else {
                        $(".show_error").html(data.message);
                        alert_float('error', data.message);
                    }
                })
                .fail(function(err) {
                    htmlError = '';
                    for (var [el, message] of Object.entries(err.responseJSON.errors)) {
                        htmlError += `<div>${message}</div>`;
                    }
                    $(".show_error").html(htmlError);
                    alert_float('error', htmlError);
                });
            return false;
        }
    });
</script>
