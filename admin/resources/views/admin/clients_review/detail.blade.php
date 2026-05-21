@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <style>
        .b-r-1 {
            border-radius: 5px;
            border: 1px solid #eff0f3;
        }
        .pull-left {
            float: left !important;
        }
    </style>
    <style>
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }


        .language-tabs {
            display: flex;
            margin-bottom: 30px;
            background: #f8f9fa;
            border-radius: 15px;
            padding: 5px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

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

        .tab-content.active {
            display: block;
        }
        .font-30 {
            font-size: 30px;
        }
        .width-10-radio{
           width: 10%;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('c_products')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/products/list">{{lang('c_list_products')}}</a></li>
                <li class="active">{{$title ?? ''}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form action="admin/products/submit/{{!empty($id) ? $id : 0}}" method="post" id="ProductsForm" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <div class="row">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#tab-info" data-toggle="tab" aria-expanded="false">
                                <span class="visible-xs"><i class="fa fa-home"></i></span>
                                <span class="hidden-xs">Thông tin</span>
                            </a>
                        </li>
                        <li>
                            <a href="#tab-image" data-toggle="tab" aria-expanded="true">
                                <span class="visible-xs"><i class="fa fa-user"></i></span>
                                <span class="hidden-xs">{{lang('c_list_images_product')}}</span>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="tab-info" class="tab-pane fade in active">
                            <div class="card-box">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="image">{{lang('images_product_profile')}}</label>
                                        <input type="file" name="image" id="image" class="filestyle image"
                                               data-buttonbefore="true">
                                        @if(!empty($products) && $products->image != null)
                                            <input type="hidden" name="image_old" id="image_old"
                                                   class="image_old"
                                                   data-buttonbefore="true" value="{{!empty($products) ? $products->image : ''}}">
                                            <div style="display: flex;justify-content:center;margin-top: 5px"
                                                 class="show_image">
                                                <img src="{{asset('storage/'.$products->image)}}" alt="image"
                                                     class="img-responsive img-circle"
                                                     style="width: 150px;height: 150px">
                                            </div>
                                        @else
                                            <div style="display: flex;justify-content:center;margin-top: 5px"
                                                 class="show_image">
                                                <img src="admin/assets/images/not_available.jpg" alt="image"
                                                     class="img-responsive img-circle"
                                                     style="width: 150px;height: 150px">

                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="code">{{lang('c_code_products')}}</label>
                                        <input type="text" id="code" name="code" autocomplete="off"
                                               value="{{!empty($products) ? $products->code : ''}}" class="form-control code">
                                    </div>
                                    <div class="form-group">
                                        <label for="code">{{lang('c_slug_products')}}</label>
                                        <input type="text" id="slug" name="slug" autocomplete="off"
                                               value="{{!empty($products) ? $products->slug : ''}}" class="form-control slug">
                                    </div>
                                    <div class="form-group">
                                        <label for="code">{{lang('c_date_end_promotion')}}</label>
                                        <input type="text" id="date_end_promotion" name="date_end_promotion" autocomplete="off"
                                               value="{{!empty($products) ? _dt($products->date_end_promotion) : ''}}" class="form-control datetimepicker">
                                    </div>
                                    <div class="form-group">
                                        <label for="color_header">{{lang('c_color_header')}}</label>
                                        <input type="color" id="color_header" name="color_header" autocomplete="off"
                                               value="{{!empty($products->color_header) ? $products->color_header : ''}}" class="form-control color_header">
                                    </div>
                                    <div class="form-group">
                                        <label for="background_color">{{lang('c_background_color')}}</label>
                                        <input type="color" id="background_color" name="background_color" autocomplete="off"
                                               value="{{!empty($products->background_color) ? $products->background_color : ''}}" class="form-control background_color">
                                    </div>
                                    <div class="form-group">
                                        <label for="product_category">{{lang('c_category_products')}}</label>
                                        <select class="form-control select2" name="product_category[]" id="product_category" required style="width: 100%;height: 35px">
                                            <option value=""></option>
                                            @if(!empty($category_products))
                                                @foreach($category_products as $category)
                                                    <option value="{{$category->id}}" {{!empty($products) && is_numeric(array_search($category->id, $products->product_category)) ? 'selected' : ''}}>{{$category->name}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                </div>
                                <div class="col-md-9">
                                    <div class="col-md-12">
                                        <ul class="nav nav-tabs nav-justified">
                                            @foreach($language as $lang)
                                                <li class="tab-btn {{$lang['is_default'] ? 'active' : ''}}">
                                                    <a href="#tab-info-{{$lang->code}}" data-toggle="tab" aria-expanded="false">
                                                        <span class="visible-xs"><i class="fa fa-home"></i></span>
                                                        <span class="hidden-xs">{{$lang->name}}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="tab-content">
                                            @foreach($language as $lang)
                                                <div id="tab-info-{{$lang->code}}" class="tab-pane fade {{$lang['is_default'] ? 'in active' : ''}}">
                                                    <div class="form-group">
                                                        <label for="name">{{lang('c_name_products')}} - {{$lang->name}}</label>
                                                        <input type="text" name="name[{{$lang->code}}]" autocomplete="off"
                                                               value="{{!empty($products->translations[$lang->code]) ? $products->translations[$lang->code]['name'] : ''}}" class="form-control name">
                                                    </div>
                                                    <hr/>
                                                    <h3 style="margin-bottom: 25px; color: #495057; font-weight: 400;">🧩 {{lang('table_ingredients')}}</h3>
                                                    <table class="table table-bordered dataTable">
                                                        <thead>
                                                            <tr>
                                                                <th class="width-10-radio"></th>
                                                                <th>{{lang('c_info_ingredients')}}</th>
{{--                                                                <th>{{lang('c_content_ingredients')}}</th>--}}
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @for($i = 0; $i < 4; $i++)
                                                                <tr>
                                                                    <td><a class="font-30" onclick="toggleTr(this)"><i class="fa fa-caret-down" aria-hidden="true"></i></a></td>
                                                                    <td>
                                                                        <div class="form-group">
                                                                            <input type="text" name="ingredients[{{$lang->code}}][{{$i}}][title]" autocomplete="off"
                                                                                   value="{{!empty($products->ingredients[$lang->code][$i]) ? $products->ingredients[$lang->code][$i]['title'] : ''}}"
                                                                                   class="form-control"
                                                                                   placeholder="{{lang('c_title_ingredients')}}">
                                                                        </div>
                                                                        <div class="content-ingredients">
                                                                            <div class="form-group">
                                                                                <input type="text" name="ingredients[{{$lang->code}}][{{$i}}][name]" autocomplete="off"
                                                                                       value="{{!empty($products->ingredients[$lang->code][$i]) ? $products->ingredients[$lang->code][$i]['name'] : ''}}"
                                                                                       class="form-control"
                                                                                       placeholder="{{lang('c_name_ingredients')}}">
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <textarea type="text" name="ingredients[{{$lang->code}}][{{$i}}][content]" autocomplete="off"
                                                                                          cols="2" rows="5" class="form-control content editor_short"
                                                                                          placeholder="{{lang('c_content_ingredients')}}"
                                                                                    >{{!empty($products->ingredients[$lang->code][$i]) ? $products->ingredients[$lang->code][$i]['content'] : ''}}</textarea>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endfor
                                                        </tbody>
                                                    </table>
                                                    <hr/>
                                                    <div class="form-group">
                                                        <label for="content">{{lang('c_content_products')}} - {{$lang->name}}</label>
                                                        <textarea type="text" name="content[{{$lang->code}}]" autocomplete="off"
                                                                  cols="2" rows="5" class="form-control content editor">{{!empty($products->translations[$lang->code]) ? $products->translations[$lang->code]['content'] : ''}}</textarea>
                                                    </div>
                                                </div>
                                          @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div id="tab-image" class="tab-pane fade">
                            <div class="card-box">
                                <div class="form-group mtop40">
                                    <label for="images">{{lang('c_list_images_product')}}</label>
                                    <input type="file" class="filestyle images" name="images[]" id="images" multiple="true" data-buttonbefore="true">
                                </div>
                                <div class="clearfix"></div>
                                <hr/>
                                <div class="span_class c_galary_images">
                                    <div class="sortable_div">
                                        @php
                                            $stt = 0;
                                        @endphp
                                        @if (!empty($products->list_images))
                                            @foreach ($products->list_images as $key => $value)
                                             @php
                                                 $stt++;
                                             @endphp
                                                <div class="col-md-3 mbot10 cfile cfile_{{$value->id}}" data-id="{{$value->id}}" data-name="<?= $value->image ?>">
                                                    <input type="hidden" class="order_images" name="order_images[{{$value->id}}]" value="{{$stt}}">
                                                    <div class="pull-left">
                                                        {!!ViewImage(asset('storage/' . $value->image))!!}
                                                    </div>
                                                    <span class="pull-right text-danger pointer" title="<?= lang('c_delete_file') ?>" data-toggle="tooltip" onclick="deleteFile({{$value->id}}, this)"><i class="fa fa-remove"></i></span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <div id="imagesDelete"></div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="form-group text-right m-b-0">
                            <button class="btn btn-primary waves-effect waves-light" type="submit">
                                {{lang('dt_save')}}
                            </button>
                            <button type="reset" class="btn btn-default waves-effect waves-light m-l-5">
                                {{lang('dt_cancel')}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script>
        $("#ProductsForm").validate({
            rules: {
                name: {
                    required: true,
                },
                product_category: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: "{{lang('dt_required')}}",
                },
                product_category: {
                    required: "{{lang('dt_required')}}",
                },

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
                            window.location.href='admin/products/list';
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

        var countImg = 0;
        function AppendImgProduct() {
            $(`#row-img`).append(`<div class="col-md-3">
                                        <div class="form-group">
                                            <input name="img[${countImg}]" type="file" id="img_${countImg}" style="display: none;" accept="image/*">
                                            <input name="img_${countImg}_before" type="hidden" value="">
                                            <img data-id="img_${countImg}" class="preview-image"
                                                 onerror="this.onerror=null; this.src='admin/assets/images/not_available.jpg';"
                                                 src="{{asset('storage/')}}" title="Click vào để đổi ảnh">
                                        </div>
                                    </div>`);
            countImg++;
        }

        function toggleTr(_this) {
            $(_this).find('i').toggleClass('fa-caret-right fa-caret-down');
            $(_this).closest('tr').find('.content-ingredients').toggle();
        }


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

        $('.sortable_div').sortable({
            start:function() {},
            stop:function(){
                OrderImages();
            }
        });

        function OrderImages(){
            var stt = 0;
            $('.sortable_div .cfile').each(function(index, value){
                stt++;
                $(this).find('.order_images').val(stt);
            });
        }

        function deleteFile(id, _this) {
            $('#imagesDelete').append(`<input type="hidden" name="imagesDelete[]" value="${id}"/>`);
            $(_this).parents('.cfile').remove();
        }

    </script>
@endsection
