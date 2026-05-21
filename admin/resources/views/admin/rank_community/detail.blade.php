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
<form id="PaymentModeForm" action="admin/rank_community/detail/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog" style="min-width: 60%">
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
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="icon">{{lang('dt_icon')}}</label>
                                        <input type="file" name="icon" id="icon" class="filestyle image"
                                               data-buttonbefore="true">
                                        @if(!empty($dtData) && $dtData['icon'] != null)
                                            @php
                                                $dtIcon = $dtData['icon'];
                                            @endphp
                                            {!! loadImage($dtIcon, '150px', 'img-rounded',$dtData['icon'],false,'50px'); !!}
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="image">{{lang('dt_image')}}</label>
                                        <input type="file" name="image" id="image" class="filestyle image"
                                               data-buttonbefore="true">
                                        @if(!empty($dtData) && $dtData['image'] != null)
                                            @php
                                                $dtImage = $dtData['image'];
                                            @endphp
                                            {!! loadImage($dtImage, '150px', 'img-rounded',$dtData['image'],false,'150px'); !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @foreach($language as $lang)
                                <div id="tab-info-{{$lang->code}}" class="tab-pane fade {{$lang->is_default ? 'in active' : ''}}">
                                    <div class="form-group">
                                        <label for="name{{$lang->code}}">{{lang('dt_rank_community_name')}}</label>
                                        <input type="text" name="name[{{$lang->code}}]" class="form-control name_{{$lang->code}}" value="{{$dtData['translations'][$lang->code]['name'] ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="content_conditions{{$lang->code}}">{{lang('dt_content_conditions')}}</label>
                                        <textarea name="content_conditions[{{$lang->code}}]" class="form-control content_conditions_{{$lang->code}}">{{$dtData['translations'][$lang->code]['content_conditions'] ?? ''}}</textarea>
                                    </div>
                                </div>
                            @endforeach
                            <hr/>
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="percent">{{lang('dt_challenges_next_rank')}}</label>
                                    <input type="number" min="0" name="challenges_next_rank" class="form-control" value="{{!empty($dtData) ? ($dtData['challenges_next_rank']) : 0}}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="percent">{{lang('dt_betting_limit')}}</label>
                                <input type="text"  onchange="formatNumBerKeyChange(this)" name="betting_limit" class="form-control" value="{{!empty($dtData) ? number_format($dtData['betting_limit']) : 0}}">
                            </div>

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
