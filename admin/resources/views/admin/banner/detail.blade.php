<form id="bannerForm" action="admin/banner/submit/{{$id}}" method="post" data-parsley-validate novalidate>
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
            border: 1px;
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
    <div class="modal-dialog modal-lg" style="min-width: 70%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="id" value="{{!empty($banner) ? $banner['id'] : 0}}" >
                        <div class="show_error" style="color: red"></div>
                        <div class="form-group">
                            <label for="name">{{lang('dt_name_banner')}}</label>
                            <input type="text" name="name" class="form-control name" value="{{!empty($banner) ? $banner->name : ''}}">
                        </div>
                        <div class="form-group div_hide_app {{!empty($banner->is_app) ? 'hide' : ''}}">
                            <div class="checkbox">
                                <input type="checkbox" {{!empty($banner->is_background) ? 'checked' : ''}} id="is_background" value="1" name="is_background" data-parsley-multiple="active">
                                <label for="is_background">{{lang('is_background')}}</label>
                            </div>
                        </div>
                        <!-- <hr/> -->
                        <div class="form-group hide">
                            <label >{{lang('type_banner')}}</label><br/>
                            <div class="radio radio-custom radio-inline mbot10">
                                <input type="radio" id="is_app_0" name="is_app" value="0" {{empty($banner->is_app) ? 'checked' : ''}}>
                                <label for="is_app_0">{{lang('banner_website')}}</label>
                            </div>
                            <div class="radio radio-custom radio-inline mbot10">
                                <input type="radio" id="is_app_1" name="is_app" value="1" {{!empty($banner->is_app) ? 'checked' : ''}}>
                                <label for="is_app_1">{{lang('banner_app')}}</label>
                            </div>
                        </div>
                        <div class="form-group hide">
                            <div class="checkbox checkbox-custom radio-inline mbot10">
                                <input type="checkbox" id="hidden_button" name="hidden_button" value="1" {{!empty($banner->hidden_button) ? 'checked' : ''}}>
                                <label for="hidden_button">{{lang('hidden_button_to_website')}}</label>
                            </div>
                        </div>
                        <div class="form-group hide">
                            <div class="checkbox checkbox-custom radio-inline mbot10">
                                <input type="checkbox" id="show_web_app" name="show_web_app" value="1" {{!empty($banner->show_web_app) ? 'checked' : ''}}>
                                <label for="show_web_app">{{lang('show_web_app')}}</label>
                            </div>
                        </div>
                        <!-- <hr/> -->
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
                                        <label for="image">{{lang('dt_image')}}</label>
                                        <input type="file" name="images[{{$lang->code}}]" multiple id="images_{{$lang->code}}" class="filestyle image"
                                               data-buttonbefore="true">
                                        @if(!empty($banner->translations[$lang->code]['image']))
                                            <input type="hidden" name="images_{{$lang->code}}_old" id="images_{{$lang->code}}_old"
                                                   class="images_{{$lang->code}}_old"
                                                   data-buttonbefore="true" value="{{!empty($banner->translations[$lang->code]['image']) ? $banner->translations[$lang->code]['image'] : ''}}">
                                            {!! loadImageNew(asset('storage/'.$banner->translations[$lang->code]['image']),'260px','','',false,'150px') !!}
                                        @endif
                                    </div>

                                    <div class="form-group div_hide_app {{!empty($banner->is_app) ? 'hide' : ''}}">
                                        <label for="image">{{lang('c_banner_img_mobile')}}</label>
                                        <input type="file" name="images_website[{{$lang->code}}]" multiple id="images_website_{{$lang->code}}" class="filestyle image"
                                               data-buttonbefore="true">
                                        @if(!empty($banner->translations[$lang->code]['image']))
                                            <input type="hidden" name="images_website_{{$lang->code}}_old" id="images_website_{{$lang->code}}_old"
                                                   class="images_website_{{$lang->code}}_old"
                                                   data-buttonbefore="true" value="{{!empty($banner->translations[$lang->code]['image_website']) ? $banner->translations[$lang->code]['image_website'] : ''}}">
                                            {!! loadImageNew(asset('storage/'.$banner->translations[$lang->code]['image_website']),'260px','','',false,'150px') !!}
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="title{{$lang->code}}">{{lang('title')}}</label>
                                        <textarea type="text" name="title[{{$lang->code}}]"
                                                  id="title_{{$lang->code}}"
                                                  class="form-control editor_short">{{$banner->translations[$lang->code]['title'] ?? ''}}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="content_{{$lang->code}}">{{lang('content_banner')}}</label>
                                        <textarea type="text" name="content[{{$lang->code}}]"
                                                   id="content_{{$lang->code}}"
                                                   class="form-control editor_short">{{$banner->translations[$lang->code]['content'] ?? ''}}</textarea>
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
    @if(empty($banner->id))
        $(function() {
            $(`#is_app_${$('input[name="status_search"]').val()}`).prop('checked', true);
            isApp = $('input[name="status_search"]').val();
            if (isApp == 1) {
                $('.div_hide_app').addClass('hide');
            } else {
                $('.div_hide_app').removeClass('hide');
            }
        })
    @endif
    tinymce.remove('.editor_short');
    tinymce.init(editor_short_config);

    $('input[name="is_app"]').change(function() {
        var isApp = $(this).val();
        if (isApp == 1) {
            $('.div_hide_app').addClass('hide');
        } else {
            $('.div_hide_app').removeClass('hide');
        }
    });
    $("#bannerForm").validate({
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
