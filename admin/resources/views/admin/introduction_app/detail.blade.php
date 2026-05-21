<form id="introduction_appForm" action="admin/introduction_app/submit/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <style>
        .tab-btn {
            flex: 1;
            padding: 15px 20px;
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
    </style>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="id" value="{{!empty($introduction) ? $introduction->id : 0}}" >
                        <div class="show_error" style="color: red"></div>
                        <div class="form-group">
                            <label for="image_main">{{lang('dt_image_main')}}</label>
                            <input type="file" name="image_main" id="image_main" class="filestyle image"
                                   data-buttonbefore="true">
                            @if(!empty($introduction) && $introduction->image_main != null)
                                <input type="hidden" name="image_main_old" id="image_main_old"
                                       class="image_main_old"
                                       data-buttonbefore="true" value="{{!empty($introduction) ? $introduction->image_main : ''}}">
                                {!! loadImageNew(asset('storage/'.$introduction->image_main),'260px','','',false,'150px') !!}
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="image">{{lang('dt_image')}}</label>
                            <input type="file" name="image[]" multiple id="image" class="filestyle image"
                                   data-buttonbefore="true">
                            @if(!empty($introduction) && $introduction->image != null)
                                <input type="hidden" name="image_old" id="image_old"
                                       class="image_old"
                                       data-buttonbefore="true" value="{{!empty($introduction) ? $introduction->image : ''}}">
                                {!! loadImageNew(asset('storage/'.$introduction->image),'260px','','',false,'150px') !!}
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="screen_link">{{lang('screen_link')}}</label>
                            <select class="form-control select2" name="screen_link" id="screen_link"  style="width: 100%;height: 35px">
                                <option value=""></option>
                                @foreach($screen_link as $link)
                                    <option value="{{$link['id']}}" {{!empty($introduction) && $introduction->screen_link == $link['id'] ? 'selected' : ''}}>{{lang($link['name'])}}</option>
                                @endforeach
                            </select>
                        </div>
                        <hr/>
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
                        <div class="tab-content row" style="padding-left:10px;padding-right:10px;">
                            @foreach($language as $lang)
                                <div id="tab-info-{{$lang->code}}" class="tab-pane fade {{$lang->is_default ? 'in active' : ''}}">
                                    <div class="form-group">
                                        <label for="title{{$lang->code}}">{{lang('title')}}</label>
                                        <input type="text" name="title[{{$lang->code}}]"
                                                  id="title_{{$lang->code}}"
                                                  class="form-control" value="{{$introduction->translations[$lang->code]['title'] ?? ''}}"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="content_{{$lang->code}}">{{lang('content_banner')}}</label>
                                        <textarea type="text" name="content[{{$lang->code}}]"
                                                   id="content_{{$lang->code}}"
                                                   class="form-control editor_short">{{$introduction->translations[$lang->code]['content'] ?? ''}}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="description_{{$lang->code}}">{{lang('description_banner')}}</label>
                                        <textarea type="text" name="description[{{$lang->code}}]"
                                                   id="description_{{$lang->code}}"
                                                   class="form-control editor_short">{{$introduction->translations[$lang->code]['description'] ?? ''}}</textarea>
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
    </div>
</form>
<script>
    tinymce.remove('.editor_short');
    tinymce.init(editor_short_config);
    $("#introduction_appForm").validate({
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
