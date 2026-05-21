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
<form id="FormModal" action="admin/challenge/detail/{{$dtData['id'] ?? 0}}" method="post" data-parsley-validate novalidate>
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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="icon">{{lang('dt_icon')}}</label>
                                    <input type="file" name="icon" id="icon" class="filestyle image"
                                           data-buttonbefore="true">
                                    @if(!empty($dtData) && $dtData['icon'] != null)
                                        @php
                                            $dtIcon = $dtData['icon'];
                                        @endphp
                                        {!! loadImage($dtIcon, '150px', 'img-rounded',$dtData['icon'],false,'150px'); !!}
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12 group_type type_2"  style="{{!empty($dtData) && $dtData['type'] == 2 ? '' : 'display:none;'}}">
                                <div class="form-group">
                                    <label for="background">{{lang('background_detail')}}</label>
                                    <input type="file" name="background" id="background" class="filestyle image"
                                           data-buttonbefore="true">
                                    @if(!empty($dtData) && $dtData['background'] != null)
                                        @php
                                            $dtBG = $dtData['background'];
                                        @endphp
                                        {!! loadImage($dtBG, '150px', 'img-rounded',$dtData['background'],false,'150px'); !!}
                                    @endif
                                </div>
                            </div>
                        </div>
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
                            @foreach($language as $lang)
                                <div id="tab-info-{{$lang->code}}" class="tab-pane fade {{$lang->is_default ? 'in active' : ''}}">
                                    <div class="form-group">
                                        <label for="name{{$lang->code}}">{{lang('c_challenge_name')}} <i class="text-danger required">*</i></label>
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
                                <label for="type">{{lang('c_challenge_type')}}<i class="text-danger required">*</i></label>
                                <select class="form-control select2" name="type" id="type" required style="width: 100%;height: 35px">
                                    <option value="2" {{!empty($dtData) && $dtData['type'] == 2 ? 'selected' : ''}}>{{lang('daily')}}</option>
                                    <option value="1" {{!empty($dtData) && $dtData['type'] == 1 ? 'selected' : ''}}>{{lang('trademark')}}</option>
                                </select>
                            </div>
                            <div class="form-group group_type type_1" style="{{!empty($dtData) && $dtData['type'] == 1 ? '' : 'display:none;'}}">
                                <label for="id_product">{{lang('c_products')}}</label>
                                <select class="id_product select2" id="id_product" data-placeholder="Chọn ..." name="id_product" data-json="{{(!empty($product) ? json_encode($product) : '')}}">
                                    @if(!empty($product))
                                        <option value="{{$product->id}}" selected><img class="img_option" src="{{$product->image}}"/>{{$product->name}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="days">{{lang('c_challenge_day')}}</label>
                                    <input type="text" onchange="formatNumBerKeyChange(this)" name="days" class="form-control" value="{{!empty($dtData) ? number_format($dtData['days']) : ''}}">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="quantity_verification">{{lang('c_quantity_verification')}} <i class="text-danger required">*</i></label>
                                    <input type="text" onchange="formatNumBerKeyChange(this)" name="quantity_verification" class="form-control" value="{{!empty($dtData) ? number_format($dtData['quantity_verification']) : ''}}">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="limit_join">{{lang('c_challenge_limit_join')}}</label>
                                    <input type="text" onchange="formatNumBerKeyChange(this)" name="limit_join" class="form-control" value="{{!empty($dtData) ? number_format($dtData['limit_join']) : ''}}">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-group">
                                    <label for="coin_success">{{lang('c_challenge_coin_success')}}</label>
                                    <input type="text" onchange="formatNumBerKeyChange(this)" name="coin_success" class="form-control" value="{{!empty($dtData) ? number_format($dtData['coin_success']) : ''}}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="min_rank_join">{{lang('c_min_rank_join')}}</label>
                                <select class="min_rank_join select2" id="min_rank_join" data-placeholder="Chọn ..." name="min_rank_join" data-json="{{(json_encode($rank_community))}}">
                                    @if(!empty($rank_community))
                                        @foreach($rank_community as $rank)
                                            <option value="{{$rank['id']}}"
                                                    data-icon="{{$rank['icon']}}"
                                                {{!empty($dtData) && $dtData['min_rank_join'] == $rank['id'] ? 'selected' : ''}}
                                            >{{$rank['name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="id_event_articles">{{lang('event_articles_challenge_join')}} <i class="text-danger required">*</i></label>
                                <select class="id_event_articles select2" id="id_event_articles" data-placeholder="Chọn ..." name="id_event_articles" data-json="{{(json_encode($event_articles))}}">
                                    <option></option>
                                    @if(!empty($event_articles))
                                        @foreach($event_articles as $article)
                                            <option value="{{$article['id']}}"
                                                    data-image="{{$article['image']}}"
                                                {{!empty($dtData) && $dtData['id_event_articles'] == $article['id'] ? 'selected' : ''}}
                                            >{{$article['name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
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
    $(function() {
        searchAjaxSelect2Img('#id_product','api/category/getListProduct', 0, {
            'select2':true
        })
        $('#min_rank_join').select2({
            templateResult: formatStateIcon,
            templateSelection: formatStateIcon,
            escapeMarkup: function (markup) {
                return markup;
            }
        });

        $('#id_event_articles').select2({
            templateResult: formatStateImg,
            templateSelection: formatStateImg,
            escapeMarkup: function (markup) {
                return markup;
            }
        });

        $('#type').change(function(){
            var type = $(this).val();
            $('.group_type').hide();
            $('.type_'+type).show();
        });
    })


    function formatStateIcon(state) {
        if (!state.id) {
            return state.text ?? state.name;
        }

        const icon = $(state.element).data('icon');

        if (!icon) {
            return state.text ?? state.name;
        }

        return `
        <span>
            <img src="${icon}" class="img_option" style="width:20px;height:20px;margin-right:6px;" />
            ${state.text ?? state.name}
        </span>
    `;
    }
    function formatStateImg(state) {
        if (!state.id) {
            return state.text ?? state.name;
        }

        const image = $(state.element).data('image');

        if (!image) {
            return state.text ?? state.name;
        }

        return `
        <span>
            <img src="${image}" class="img_option" style="width:20px;height:20px;margin-right:6px;" />
            ${state.text ?? state.name}
        </span>
    `;
    }

    $("#type").select2();
    $("#FormModal").validate({
        rules: {
            name: {
                required: true,
            },
            id_event_articles: {
                required: true,
            },
        },
        messages: {
            name: {
                required: "{{lang('dt_required')}}",
            },
            id_event_articles: {
                required: "{{lang('dt_required')}}",
            }
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
