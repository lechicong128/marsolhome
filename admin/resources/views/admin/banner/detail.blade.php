<form id="bannerForm" action="admin/banner/submit/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    
    <!-- Hidden Inputs for other fields to support backend logic -->
    <input type="hidden" name="name" value="{{ !empty($banner->name) ? $banner->name : 'Banner '.uniqid() }}">
    <input type="hidden" name="title[vi]" value="{{ !empty($banner->translations['vi']['title']) ? $banner->translations['vi']['title'] : 'Banner' }}">
    <input type="hidden" name="content[vi]" value="{{ !empty($banner->translations['vi']['content']) ? $banner->translations['vi']['content'] : '' }}">
    <input type="hidden" name="is_app" value="{{ isset($banner->is_app) ? $banner->is_app : 0 }}">
    <input type="hidden" name="is_background" value="{{ !empty($banner->is_background) ? $banner->is_background : 0 }}">
    <input type="hidden" name="hidden_button" value="{{ !empty($banner->hidden_button) ? $banner->hidden_button : 0 }}">
    <input type="hidden" name="show_web_app" value="{{ !empty($banner->show_web_app) ? $banner->show_web_app : 0 }}">
    
    @if(!empty($banner->translations['vi']['image']))
        <input type="hidden" name="images_vi_old" id="images_vi_old" value="{{ $banner->translations['vi']['image'] }}">
    @endif

    <div class="modal-dialog modal-lg" role="document" style="margin: 8% auto;">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); background: #ffffff;max-width: 100%;!important">
            
            <!-- Modal Header -->
            <div class="modal-header" style="position: relative !important; display: flex !important; justify-content: space-between !important; align-items: center !important; padding: 20px 24px; border-bottom: 1px solid #f3f4f6; background: #ffffff; min-height: 60px !important; width: 100% !important;">
                <h4 class="modal-title" style="font-weight: 700; font-size: 18px; color: #111827; margin: 0 !important; font-family: 'Inter', sans-serif;">
                    {{ $title }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="border: none !important; background: transparent !important; font-size: 24px !important; color: #9ca3af !important; line-height: 1 !important; padding: 0 !important; cursor: pointer !important; transition: all 0.2s !important; opacity: 0.8 !important;" onmouseover="this.style.color='#111827'; this.style.transform='scale(1.1)'" onmouseout="this.style.color='#9ca3af'; this.style.transform='scale(1)'">&times;</button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body" style="padding: 24px;">
                <div class="show_error" style="color: #ef4444; margin-bottom: 15px; font-size: 13px; font-weight: 500;"></div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-weight: 600; font-size: 14px; color: #374151; margin-bottom: 10px; display: block; font-family: 'Inter', sans-serif;">
                        {{lang('dt_image')}} @if(empty($banner->translations['vi']['image'])) <span style="color: #ef4444;">*</span> @endif
                    </label>
                    
                    <!-- Premium Drag and Drop Styled File Upload -->
                    <div style="position: relative; border: 2px dashed #d1d5db; border-radius: 12px; padding: 20px; text-align: center; background: #f9fafb; cursor: pointer; transition: all 0.25s ease;" 
                         id="upload_drop_zone"
                         onmouseover="this.style.borderColor='#3a94ef'; this.style.background='#f0f7ff';" 
                         onmouseout="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';">
                        
                        <input type="file" name="images[vi]" id="images_vi" class="image" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" {{ empty($banner->translations['vi']['image']) ? 'required' : '' }}>
                        
                        <div id="upload_prompt" style="pointer-events: none;">
                            <i class="fa fa-cloud-upload" style="font-size: 36px; color: #9ca3af; margin-bottom: 8px;"></i>
                            <p style="margin: 0; font-size: 13px; color: #4b5563; font-weight: 500;">Click để chọn ảnh hoặc kéo thả ảnh vào đây</p>
                            <p style="margin: 4px 0 0 0; font-size: 11px; color: #9ca3af;">Định dạng: JPG, PNG, GIF, WEBP</p>
                        </div>
                    </div>
                    
                    <!-- Live Image Preview Frame -->
                    <div class="image-preview-wrapper" style="margin-top: 20px; text-align: center;">
                        <div style="position: relative; display: inline-block; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #ffffff; padding: 8px; width: 100%; min-height: 120px; display: flex; align-items: center; justify-content: center;">
                            <img id="image_preview_el" src="{{ !empty($banner->translations['vi']['image']) ? asset('storage/'.$banner->translations['vi']['image']) : '' }}" 
                                 style="max-width: 100%; max-height: 180px; object-fit: contain; border-radius: 8px; display: {{ !empty($banner->translations['vi']['image']) ? 'block' : 'none' }}; margin: 0 auto;" />
                            
                            <div id="no_image_placeholder" style="color: #9ca3af; padding: 20px 0; display: {{ !empty($banner->translations['vi']['image']) ? 'none' : 'block' }}; text-align: center; width: 100%;">
                                <i class="fa fa-picture-o" style="font-size: 40px; margin-bottom: 8px; display: block; color: #d1d5db;"></i>
                                <span style="font-size: 13px; font-weight: 500;">Chưa chọn hình ảnh</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer" style="padding: 16px 24px; border-top: 1px solid #f3f4f6; display: flex; justify-content: flex-end; gap: 12px; background: #f9fafb; margin: 0;">
                <button class="btn" type="button" data-dismiss="modal" style="background: #ffffff; color: #4b5563; border: 1px solid #d1d5db; font-weight: 600; padding: 9px 18px; border-radius: 8px; font-size: 13px; cursor: pointer; transition: all 0.2s; outline: none;" onmouseover="this.style.background='#f3f4f6'; this.style.color='#111827';" onmouseout="this.style.background='#ffffff'; this.style.color='#4b5563';">
                    Hủy bỏ
                </button>
                <button class="btn" id="saveBtn" type="submit" style="background: #d4a017; color: white; border: none; font-weight: 600; padding: 9px 20px; border-radius: 8px; font-size: 13px; cursor: pointer; transition: all 0.2s; outline: none; box-shadow: 0 2px 4px rgba(212, 160, 23, 0.2);" onmouseover="this.style.background='#b8860b'; this.style.boxShadow='0 4px 8px rgba(212, 160, 23, 0.3)';" onmouseout="this.style.background='#d4a017'; this.style.boxShadow='0 2px 4px rgba(212, 160, 23, 0.2)';">
                    Lưu Lại
                </button>
            </div>
            
        </div>
    </div>
</form>

<script>
    // Live image preview handler
    $('#images_vi').change(function(e) {
        var input = e.target;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image_preview_el').attr('src', e.target.result).show();
                $('#no_image_placeholder').hide();
            }
            reader.readAsDataURL(input.files[0]);
        }
    });

    // Form validation and submit
    $("#bannerForm").validate({
        rules: {
            @if(empty($banner->translations['vi']['image']))
                "images[vi]": {
                    required: true
                }
            @endif
        },
        messages: {
            "images[vi]": {
                required: "Vui lòng chọn hình ảnh banner"
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
                    if (typeof oTable !== 'undefined') {
                        oTable.draw();
                    }
                    $('.modal-dialog .close').trigger('click');
                    alert_float('success', data.message);
                } else {
                    $(".show_error").html(data.message);
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
                    htmlError = 'Đã xảy ra lỗi, vui lòng thử lại.';
                }
                $(".show_error").html(htmlError);
                alert_float('error', htmlError);
            });
            return false;
        }
    });
</script>
