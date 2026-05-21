@extends('admin.layouts.index')
@section('content')
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
            padding: 10px 14px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #3a94ef;
            outline: none;
        }
        .nav.nav-tabs > li.active > a {
            background-color: #3a94ef;
            color: white !important;
            border: 0;
            border-radius: 8px 8px 0 0;
        }
        .thumb-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
            border: 2px solid #e9ecef;
        }
    </style>

    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('c_services')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/services/list">{{lang('c_list_services')}}</a></li>
                <li class="active">{{$title ?? ''}}</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <form action="admin/services/submit/{{!empty($id) ? $id : 0}}" method="post"
              id="ServiceForm" data-parsley-validate novalidate enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <div class="row">
                    <!-- ===== TABS ===== -->
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#tab-info" data-toggle="tab" aria-expanded="true">
                                <span class="hidden-xs">Thông tin</span>
                            </a>
                        </li>
                        <li>
                            <a href="#tab-images" data-toggle="tab" aria-expanded="false">
                                <span class="hidden-xs">{{lang('images')}}</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">

                        {{-- ===== TAB THÔNG TIN ===== --}}
                        <div id="tab-info" class="tab-pane fade in active">
                            <div class="card-box">
                                <div class="row">

                                    {{-- Cột trái: ảnh đại diện + mã --}}
                                    <div class="col-md-3">

                                        <div class="form-group">
                                            <label for="image">{{lang('images_product_profile')}}</label>
                                            <input type="file" name="image" id="image" class="filestyle" data-buttonbefore="true" accept="image/*">
                                            @if(!empty($service) && !empty($service->image))
                                                <input type="hidden" name="image_old" value="{{$service->image}}">
                                                <img src="{{asset('storage/'.$service->image)}}" alt="image" class="thumb-preview" id="thumb-main">
                                            @else
                                                <img src="admin/assets/images/not_available.jpg" alt="image" class="thumb-preview" id="thumb-main">
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="code">{{lang('c_code_services')}}</label>
                                            <input type="text" id="code" name="code" autocomplete="off"
                                                   value="{{!empty($service) ? $service->code : ''}}"
                                                   class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label for="id_category">{{lang('category_service')}}</label>
                                            <select name="id_category" id="id_category" class="form-control select2" style="width:100%">
                                                <option value="">-- Chọn danh mục --</option>
                                                @foreach($category_services as $cat)
                                                    <option value="{{$cat->id}}"
                                                        {{(!empty($service) && $service->id_category == $cat->id) ? 'selected' : ''}}>
                                                        {{$cat->name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>

                                    {{-- Cột phải: các trường thông tin --}}
                                    <div class="col-md-9">

                                        <div class="form-group">
                                            <label for="name">{{lang('c_name_services')}} <i class="text-danger">*</i></label>
                                            <input type="text" id="name" name="name" autocomplete="off"
                                                   value="{{!empty($service) ? $service->name : ''}}"
                                                   class="form-control" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="price">{{lang('c_price_services')}}</label>
                                                    <input type="text" id="price" name="price" autocomplete="off"
                                                           value="{{!empty($service) ? number_format($service->price ?? 0) : '0'}}"
                                                           class="form-control text-right"
                                                           onkeyup="formatNumBerKeyChange(this)">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="discount_percent">{{lang('c_discount_percent_services')}} (%)</label>
                                                    <input type="number" id="discount_percent" name="discount_percent"
                                                           min="0" max="100" step="0.01" autocomplete="off"
                                                           value="{{!empty($service) ? $service->discount_percent : '0'}}"
                                                           class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="duration_minutes">{{lang('c_duration_minutes_services')}} (phút)</label>
                                                    <input type="number" id="duration_minutes" name="duration_minutes"
                                                           min="0" step="1" autocomplete="off"
                                                           value="{{!empty($service) ? $service->duration_minutes : '0'}}"
                                                           class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="content">{{lang('c_content_services')}}</label>
                                            <textarea name="content" id="content" cols="2" rows="6"
                                                      class="form-control editor">{{!empty($service) ? $service->content : ''}}</textarea>
                                        </div>

                                    </div>{{-- /col-md-9 --}}

                                </div>{{-- /row --}}
                            </div>{{-- /card-box --}}
                        </div>{{-- /tab-info --}}

                        {{-- ===== TAB HÌNH ẢNH ===== --}}
                        <div id="tab-images" class="tab-pane fade">
                            <div class="card-box">
                                <div class="form-group">
                                    <label for="images">{{lang('c_list_images_product')}}</label>
                                    <input type="file" class="filestyle" name="images[]" id="images"
                                           multiple accept="image/*" data-buttonbefore="true">
                                </div>
                                <div class="clearfix"></div>
                                <hr/>
                                <div class="sortable_div row">
                                    @if(!empty($service) && !empty($service->id))
                                        @php
                                            $serviceImages = \Illuminate\Support\Facades\DB::table('tbl_services_images')
                                                ->where('id_service', $service->id)
                                                ->orderBy('id', 'asc')->get();
                                            $stt = 0;
                                        @endphp
                                        @foreach($serviceImages as $img)
                                            @php $stt++ @endphp
                                            <div class="col-md-3 mbot10 cfile cfile_{{$img->id}}" data-id="{{$img->id}}">
                                                <input type="hidden" class="order_images" name="order_images[{{$img->id}}]" value="{{$stt}}">
                                                <div class="pull-left">
                                                    {!!ViewImage(asset('storage/' . $img->image))!!}
                                                </div>
                                                <span class="pull-right text-danger pointer"
                                                      title="{{lang('c_delete_file')}}"
                                                      data-toggle="tooltip"
                                                      onclick="deleteServiceImg({{$img->id}}, this)">
                                                    <i class="fa fa-remove"></i>
                                                </span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <div id="imagesDelete"></div>
                                <div class="clearfix"></div>
                            </div>
                        </div>{{-- /tab-images --}}

                        {{-- Nút lưu --}}
                        <div class="form-group text-right m-b-0 m-t-10" style="padding: 0 15px;">
                            <button class="btn btn-primary waves-effect waves-light" type="submit">
                                {{lang('dt_save')}}
                            </button>
                            <a href="admin/services/list" class="btn btn-default waves-effect waves-light m-l-5">
                                {{lang('dt_cancel')}}
                            </a>
                        </div>

                    </div>{{-- /tab-content --}}
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        // Preview ảnh đại diện ngay khi chọn
        document.getElementById('image').addEventListener('change', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('thumb-main').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Xóa ảnh gallery
        function deleteServiceImg(id, _this) {
            $('#imagesDelete').append(`<input type="hidden" name="imagesDelete[]" value="${id}"/>`);
            $(_this).parents('.cfile').remove();
        }

        // Sortable gallery
        $('.sortable_div').sortable({
            stop: function () {
                var stt = 0;
                $('.sortable_div .cfile').each(function () {
                    stt++;
                    $(this).find('.order_images').val(stt);
                });
            }
        });

        // Validate & Submit
        $("#ServiceForm").validate({
            rules: {
                name: {required: true},
            },
            messages: {
                name: {required: "{{lang('dt_required')}}"},
            },
            submitHandler: function (form) {
                var url  = form.action;
                var $form = $(form);
                var formData   = new FormData();
                var formParams = $form.serializeArray();

                $.each($form.find('input[type="file"]'), function (i, tag) {
                    $.each($(tag)[0].files, function (i, file) {
                        formData.append(tag.name, file);
                    });
                });
                $.each(formParams, function (i, val) {
                    formData.append(val.name, val.value);
                });

                // Lấy nội dung TinyMCE
                if (typeof tinymce !== 'undefined') {
                    formData.set('content', tinymce.get('content') ? tinymce.get('content').getContent() : $('#content').val());
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'JSON',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                }).done(function (data) {
                    if (data.result) {
                        alert_float('success', data.message);
                        window.location.href = 'admin/services/list';
                    } else {
                        alert_float('error', data.message);
                    }
                }).fail(function (err) {
                    var htmlError = '';
                    if (err.responseJSON && err.responseJSON.errors) {
                        for (var [el, message] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                    }
                    alert_float('error', htmlError || 'Lỗi không xác định');
                });
                return false;
            }
        });
    </script>
@endsection
