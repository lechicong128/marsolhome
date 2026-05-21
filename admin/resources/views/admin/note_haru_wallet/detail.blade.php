<style>
    .tab-btn {
        flex: 1;
        padding: 10px 10px;
        border: none;
        background: transparent;
        cursor: pointer;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        color: #6c757d;
    }

    .tab-btn.active {
        /*background: #3a94ef;*/
        color: white;
        /*box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);*/
        margin-right: 5px;
        border: 1;
    }
    .nav.nav-tabs > li.tab-btn.active > a {
        background-color: #3a94ef;
        color: white !important;
        border: 0;
        border-radius: 10px;
    }

    .tab-btn:hover:not(.active) {
        background: rgba(79, 172, 254, 0.1);
        color: #4facfe;
    }
    .nav.nav-tabs > li > a, .nav.tabs-vertical > li > a{
        line-height: 25px;
    }
</style>
<form id="PaymentModeForm" action="admin/note_haru_wallet/submit/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog" style="width:50%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="show_error" style="color: red"></div>
                        <ul class="nav nav-tabs nav-justified row">
                            @foreach($language as $lang)
                                <li class="tab-btn {{$lang->is_default ? 'active' : ''}}">
                                    <a href="#tab-info-{{$lang->code}}" data-toggle="tab" aria-expanded="false">
                                        <span class="visible-xs"><i class="fa fa-home"></i></span>
                                        <span class="hidden-xs">{{$lang->name}}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="tab-content row" style="padding-left:10px;padding-right:10px;">
                        <div class="form-group hide">
                            <label for="image">{{lang('dt_image')}}</label>
                            <input type="file" name="image[]" multiple id="image" class="filestyle image"
                                   data-buttonbefore="true">
                            @if(!empty($dtData) && $dtData->image != null)
                                <input type="hidden" name="image_old" id="image_old"
                                       class="image_old"
                                       data-buttonbefore="true" value="{{!empty($dtData) ? $dtData->image : ''}}">
                                {!! loadImage(asset('storage/'.$dtData->image)) !!}
                            @endif
                        </div>
                        <div class="form-group hide">
                            <label for="screen_link">{{lang('screen_link')}}</label>
                            <select class="form-control select2" name="screen_link" id="screen_link"  style="width: 100%;height: 35px">
                                <option value=""></option>
                                @foreach($screen_link as $link)
                                    <option value="{{$link['id']}}" {{!empty($introduction) && $introduction->screen_link == $link['id'] ? 'selected' : ''}}>{{lang($link['name'])}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group hide">
                                <label for="color">{{lang('dt_color_v')}}</label>
                                <input type="color" name="color" id="colorPicker" class="form-control color" value="{{!empty($dtData) ? $dtData->color : ''}}">
                            </div>
                            <div class="form-group hide">
                                <label for="background">{{lang('dt_background')}}</label>
                                <input type="color" name="background" id="colorPicker" class="form-control background" value="{{!empty($dtData) ? $dtData->background : ''}}">
                            </div>
                        @foreach($language as $lang)
                            <div id="tab-info-{{$lang->code}}" class="tab-pane fade {{$lang->is_default ? 'in active' : ''}}">
                                <div class="form-group">
                                    <label for="title{{$lang->code}}">{{lang('dt_title')}}</label>
                                    <input readonly type="text" name="title[{{$lang->code}}]" class="form-control title_{{$lang->code}}" value="{{$dtData->translations[$lang->code]['title'] ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="content{{$lang->code}}">{{lang('c_content')}}</label>
                                    <textarea name="content[{{$lang->code}}]" class="form-control content_{{$lang->code}} editor">{{$dtData->translations[$lang->code]['content'] ?? ''}}</textarea>
                                </div>
                            </div>
                        @endforeach
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
    $("#type").select2();
    $("#PaymentModeForm").validate({
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
