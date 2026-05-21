@extends('admin.layouts.index')
@section('content')
    <style>
        .thumb_new > .bootstrap-filestyle{
            display: none;
        }
        .product-list-box{
            min-height: 590px !important;
        }
    </style>
    <style>
        #ti_text {
            height: 100%;
        }

        h2 {
            color: #003197;
            font-size: 24px;
            font-weight: bold;
        }

        .clearfix {
            margin-top: 15px !important;
        }

        .preview-image {
            max-width: 100%;
            max-height: 100%;
        }

        .preview-image:hover {
            cursor: pointer;
        }

        .input_main {
            text-align: center;
            height: 50px !important;
            line-height: 50px !important;
            font-size: 2rem !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input_sub {
            text-align: center;
        }

        .tnh-tb,
        .tnh-tb tr th,
        .tnh-tb tr td {
            /* border: 1px solid #9e9e9ea3 !important;
            padding: 10px !important; */
            vertical-align: text-top !important;
        }

        .mix-blend-difference {
            mix-blend-mode: difference !important;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(16px);
            -ms-transform: translateX(16px);
            transform: translateX(16px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
        .d-item {
            margin-top: 20px!important;
        }
        .preview-image {
            border: 1px solid #9b9898;
            padding: 5px;
            border-radius: 10px;
            width: 400px;
            height: 260px;
        }
        .bootstrap-filestyle {
            display: none!important;
        }
        .mce-panel {
            border-top: 1px solid #ddd!important;  /* thêm border trên */
        }
        .div-lang {display: none;}
        .div-lang.active {display: block;}
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('c_index')}}</a></li>
                <li><a>{{lang('website')}}</a></li>
                <li class="active">{{lang('c_helpcentre')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form id="HelpcentreForm" action="admin/admin_website/submit_reviewhub" method="post" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <div class="card-box">
                    <div class="row">
                        <div class="">
                            <ul class="nav nav-tabs" role="tablist">
                                @php
                                    $lang_default = '';
                                @endphp
                                @if(!empty($lang_current))
                                    @foreach($lang_current as $key => $value)
                                        @php
                                            if(!empty($value['is_default'])) {
                                                $lang_default = $value['code'];
                                            }
                                        @endphp
                                        <li role="presentation" class="{{$value['is_default'] ? 'active' : ''}}">
                                            <a onclick="ShowTab('{{$value['code']}}')" href="#tab-{{$value['code']}}" aria-controls="tab" role="tab" data-toggle="tab">{{$value['name']}}</a>
                                        </li>
                                        <input type="hidden" name="allLang[{{$key}}]" value="{{$value['code']}}">
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                    <hr/>
                    <div class="tab-content">
                        <section>
                            <div class="col-md-12 title_section"></div>
                            @if(!empty($lang_current))
                                @foreach($lang_current as $key => $value)
                                    <div class="col-md-8 div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="title_section section_{{$value['code']}}_title">{{lang('c_title')}} - {{$value['name']}}</label>
                                            <textarea type="text" name="section[{{$value['code']}}][title]"
                                                      id="section_{{$value['code']}}_title"
                                                      class="form-control editor_short">{{$reviewhub[$value['code']]->title ?? ''}}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="subtitle_section section_{{$value['code']}}_title">{{lang('c_sub_title')}} - {{$value['name']}}</label>
                                            <textarea type="text" name="section[{{$value['code']}}][subtitle]"
                                                      id="section_{{$value['code']}}_subtitle"
                                                      class="form-control">{{$reviewhub[$value['code']]->subtitle ?? ''}}</textarea>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="col-md-4">
                                    <div class="form-group">
                                        @php
                                            $keyID = 'image';
                                            $keyName = 'image';
                                            $keyBefore = 'image_before';
                                        @endphp
                                        <label for="{{$keyID}}">{{lang('images')}}</label><br>
                                        <input name="{{$keyName}}" type="file" id="{{$keyID}}" style="display: none;" accept="image/*">
                                        <input name="{{$keyBefore}}" type="hidden" value="{{$reviewhub[$lang_default]->image ?? ''}}">
                                        <img data-id="{{$keyID}}" class="preview-image"
                                             onerror="this.onerror=null; this.src='admin/assets/images/not_available.jpg';"
                                             src="{{asset('storage/' . ($reviewhub[$lang_default]->image ?? ''))}}" title="{{lang('c_click_in_change_image')}}">
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            @endif
                        </section>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group text-right m-b-0 m-t-10">
                        <button class="btn btn-primary waves-effect waves-light" type="submit">
                            {{lang('dt_save')}}
                        </button>
                    </div>
                </div>
            </div>
    </form>
</div>
    <!-- end row -->
@endsection
@section('script')
    <script>
        ShowTab('{{$lang_default ?? 'vi'}}');
        function ShowTab(lang) {
            $('.div-lang').removeClass('active');
            $(`.div-${lang}`).addClass('active');
        }

        $("#HelpcentreForm").validate({
            ignore: "",
            rules: {
            },
            messages: {

            },
            invalidHandler: function(event, validator) {
                let errors = validator.numberOfInvalids();
                if (errors) {
                    let message = "";
                    validator.errorList.forEach(function(error) {
                        let fieldName = $(error.element).attr("id");
                        var fieldNameLang = '';
                        if (fieldName.endsWith("_en")) {
                            fieldNameLang = 'tiếng anh';
                        }

                        let label = $($("label[for='" + fieldName + "']")[0]).text();
                        if (!label) {
                            fieldName = $(error.element).attr("name");
                            label = $($("label[for='" + fieldName + "']")[0]).text();
                        }

                        message += `<div>${label} ${fieldNameLang} ${error.message}</div>`;
                    });

                    if (!message) {
                        message = 'Bạn chưa nhập các trường';
                    }
                    alert_float('error', message, 5000);
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
                            alert_float('success',data.message);
                            window.location.reload();
                        } else {
                            alert_float('error',data.message);
                        }
                    })
                    .fail(function (err) {
                        htmlError = '';
                        for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                        alert_float('error',htmlError);
                    });
                return false;
            }
        });
        setTimeout(function (){
            $(".content").closest('div').find('.tox-tinymce').css({
                height:"450px"
            })
        },300);


        $('body').on('click', '.preview-image', function() {
            var idInput = $(this).attr('data-id');
            $(`#${idInput}`).click();
            $(`#${idInput}`).change(function(event) {
                var input = event.target;
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(`.preview-image[data-id="${idInput}"]`).attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            });
        })


        function clickImage(_this){
            id = $(_this).attr('data-id');
            $(`#section_2_image_${id}`).click();
        }
        $(".section_2_image").change(function (event){
            id = $(this).attr('data-id');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();

                // Khi đọc tệp xong, hiển thị hình ảnh
                reader.onload = function(e) {
                    $(`.section_2_image_preview_${id}`).attr('src', e.target.result).show();
                };

                reader.readAsDataURL(file);
            }
        });

        function clickImage3(_this){
            id = $(_this).attr('data-id');
            $(`#section_3_image_${id}`).click();
        }
        $(".section_3_image").change(function (event){
            id = $(this).attr('data-id');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();

                // Khi đọc tệp xong, hiển thị hình ảnh
                reader.onload = function(e) {
                    $(`.section_3_image_preview_${id}`).attr('src', e.target.result).show();
                };

                reader.readAsDataURL(file);
            }
        });


    </script>
@endsection
