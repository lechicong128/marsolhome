@extends('admin.layouts.index')
@section('page_title', $title)
@section('content')
<style>
    :root {
        --blog-primary: #005ae0;
        --blog-primary-dark: #004ec4;
        --blog-bg: #f8fafc;
        --blog-card: #ffffff;
        --blog-border: #e2e8f0;
        --blog-text: #0f172a;
        --blog-muted: #64748b;
    }

    body {
        font-size: 14px !important;
    }

    label, input, select, textarea, button {
        font-size: 14px !important;
    }

    /* Custom input/select/textarea styling */
    input[type="text"],
    select,
    textarea {
        font-size: 14px !important;
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.75rem !important; /* rounded-xl */
        transition: all 0.2s ease-in-out !important;
    }

    input[type="text"]:focus,
    select:focus,
    textarea:focus {
        border-color: var(--blog-primary) !important;
        outline: none !important;
        box-shadow: 0 0 0 4px rgba(0, 90, 224, 0.1) !important;
    }

    .select2-container--default .select2-selection--single {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.75rem !important;
        height: 46px !important;
        display: flex !important;
        align-items: center !important;
        padding-left: 8px !important;
        padding-right: 8px !important;
        transition: all 0.2s ease-in-out !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: var(--blog-primary) !important;
        box-shadow: 0 0 0 4px rgba(0, 90, 224, 0.1) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        font-size: 13.5px !important;
        font-weight: 600 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 12px !important;
    }

    .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--blog-primary) !important;
        color: #ffffff !important;
    }

    .mod-page-header {
        margin-bottom: 20px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #e5edf8;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    }

    .mod-page-title {
        margin: 0 !important;
        font-size: 22px !important;
        line-height: 1.25 !important;
        font-weight: 800 !important;
        color: var(--blog-text) !important;
        letter-spacing: -0.02em;
    }

    .mod-btn.mod-btn-secondary {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        height: 42px !important;
        padding: 0 16px !important;
        border-radius: 12px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #334155 !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        text-decoration: none !important;
        transition: all .18s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }

    .mod-btn.mod-btn-secondary:hover {
        background-color: #f8fafc !important;
        border-color: #94a3b8 !important;
    }

    .mod-card {
        background: #ffffff !important;
        border: 1px solid #eef2f7 !important;
        border-radius: 20px !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04) !important;
        padding: 30px !important;
    }
</style>

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false
        },
        theme: {
            extend: {
                colors: {
                    brand: {
                        50: '#eff6ff',
                        100: '#dbeafe',
                        200: '#bfdbfe',
                        300: '#93c5fd',
                        400: '#60a5fa',
                        500: '#005ae0',
                        600: '#004ec4',
                        700: '#003ea1',
                        800: '#1e40af',
                        900: '#1e3a8a',
                        950: '#172554'
                    }
                }
            }
        }
    }
</script>

<form action="admin/blog/submit/{{$id}}" method="post" id="blogForm" data-parsley-validate novalidate enctype="multipart/form-data">
    {{csrf_field()}}
    
    <div class="row">
        <div class="w-full md:w-10/12 lg:w-10/12 mx-auto bg-gray-100 p-4">
            <div class="mod-card space-y-6">
                <!-- 2 Column Layout for general details & image -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    
                    <!-- Left: Inputs -->
                    <div class="md:col-span-8 space-y-5">
                        
                        <!-- Title Input -->
                        <div>
                            <label for="title" class="block text-sm font-bold text-slate-700 uppercase mb-2">Tiêu Đề Bài Viết</label>
                            <input type="text" name="title" id="title" placeholder="Nhập tiêu đề tin tức..." autocomplete="off" value="{{!empty($blog) ? $blog->title : ''}}" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none font-semibold text-slate-800" required>
                        </div>

                        <!-- Type Blog -->
                        <div>
                            <label for="type_blog" class="block text-sm font-bold text-slate-700 uppercase mb-2">Loại Bài Viết</label>
                            <select class="select2 w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:outline-none" name="type_blog" id="type_blog">
                                @foreach($blogCategories as $category)
                                    <option {{!empty($blog) && ($blog->type_blog) == $category->id ? 'selected' : ''}} value="{{$category->id}}">{{$category->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Short Description Input -->
                        <div>
                            <label for="descption" class="block text-sm font-bold text-slate-700 uppercase mb-2">Mô Tả Ngắn <span class="text-red-500">*</span></label>
                            <textarea name="descption" id="descption" rows="4" placeholder="Nhập tóm tắt mô tả ngắn bài viết..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none leading-relaxed text-slate-700" required>{{!empty($blog) ? $blog->descption : ''}}</textarea>
                        </div>

                    </div>

                    <!-- Right: Image Upload -->
                    <div class="md:col-span-4 space-y-4">
                        <label class="block text-sm font-bold text-slate-700 uppercase mb-2">Hình Ảnh Đại Diện <span class="text-red-500">*</span></label>
                        
                        <div class="p-4 border-2 border-dashed border-slate-300 hover:border-brand-500 rounded-2xl bg-slate-50/50 hover:bg-brand-50/10 transition-all text-center cursor-pointer flex flex-col items-center justify-center relative overflow-hidden" id="image-upload-zone" style="height: 220px;">
                            <!-- Default Upload Content -->
                            <div id="upload-default-content" class="flex flex-col items-center justify-center {{ (!empty($blog) && $blog->image != null) ? 'hidden' : '' }}">
                                <div class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-brand-500 text-lg mb-3 border border-slate-100">
                                    <i class="fa fa-cloud-upload"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-800">Bấm để tải lên hình ảnh</p>
                                <p class="text-[11px] text-slate-400 mt-1">Định dạng hỗ trợ: PNG, JPG, JPEG, GIF. Kích thước đề xuất: 600x500px.</p>
                            </div>
                            
                            <!-- Image Preview inside the zone -->
                            <div id="image-preview-container" class="absolute inset-0 flex items-center justify-center p-2 {{ (!empty($blog) && $blog->image != null) ? '' : 'hidden' }}">
                                @if(!empty($blog) && $blog->image != null)
                                    @php
                                        $dtImage = asset('storage/' . $blog->image);
                                    @endphp
                                    <img id="image-preview" src="{{ $dtImage }}" class="w-full h-full object-cover rounded-xl" alt="Blog Image">
                                    <input type="hidden" name="image_old[]" value="{{ $blog->image }}">
                                @else
                                    <img id="image-preview" src="" class="w-full h-full object-cover rounded-xl" alt="Preview">
                                @endif
                            </div>

                            <input type="file" name="image" id="image" class="absolute inset-0 opacity-0 cursor-pointer z-10" accept="image/*" {{empty($blog) ? 'required' : ''}}>
                        </div>
                    </div>

                </div>

                <!-- Content Area -->
                <div>
                    <label for="content" class="block text-sm font-bold text-slate-700 uppercase mb-2">Nội Dung Chi Tiết</label>
                    <textarea name="content" id="content" autocomplete="off" cols="2" rows="12" class="form-control content editor w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none">{{!empty($blog) ? $blog->content : ''}}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="sticky bottom-4 bg-white/95 backdrop-blur-sm border border-slate-200 p-4 rounded-3xl flex items-center justify-center gap-4 z-40 shadow-[0_-8px_30px_rgb(0,0,0,0.08)] mt-6">
                    <a class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold text-xl rounded-xl transition-all flex items-center gap-2" href="admin/blog/list">
                        Hủy Bỏ
                    </a>
                    <button class="px-10 py-3.5 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white font-bold text-sm rounded-xl shadow-lg transition-all flex items-center gap-1.5 focus:outline-none border-0" type="submit">
                        Lưu Bài Viết
                    </button>
                </div>

            </div>
        </div>
    </div>
</form>
@endsection

@section('script')
    <script>
        // Image preview logic
        $('#image').change(function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').attr('src', e.target.result);
                    $('#image-preview-container').removeClass('hidden');
                    $('#upload-default-content').addClass('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        $("#blogForm").validate({
            ignore: "",
            rules: {
                title: {
                    required: true,
                },
                descption: {
                    required: true,
                },
                {{empty($blog) ? 'image: {
                    required: true,
                }' : '' }}
            },
            messages: {
                title: {
                    required: "{{lang('dt_required')}}",
                },
                descption: {
                    required: "{{lang('dt_required')}}",
                },
                image: {
                    required: "{{lang('dt_required')}}",
                },
            },
            invalidHandler: function(event, validator) {
                let errors = validator.numberOfInvalids();
                if (errors) {
                    let message = "";
                    validator.errorList.forEach(function(error) {
                        let fieldName = $(error.element).attr("id");
                        let label = $($("label[for='" + fieldName + "']")[0]).text();
                        if (!label) {
                            fieldName = $(error.element).attr("name");
                            label = $($("label[for='" + fieldName + "']")[0]).text();
                        }
                        message += `<div>${label} ${error.message}</div>`;
                    });

                    if (!message) {
                        message = 'Vui lòng kiểm tra các trường bắt buộc';
                    }
                    alert_float('error', message, 5000);
                }
            },
            submitHandler: function (form) {
                var url = form.action;
                var $form = $(form),
                    formData = new FormData(),
                    formParams = $form.serializeArray();

                $.each($form.find('input[type="file"]'), function (i, tag) {
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
                        alert_float('success', data.message);
                        window.location.href = 'admin/blog/list';
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
                        htmlError = 'Lỗi hệ thống khi lưu bài viết.';
                    }
                    alert_float('error', htmlError);
                });
                return false;
            }
        });
        
        setTimeout(function (){
            $(".content").closest('div').find('.tox-tinymce').css({
                height:"450px"
            })
        }, 300);
    </script>
@endsection
