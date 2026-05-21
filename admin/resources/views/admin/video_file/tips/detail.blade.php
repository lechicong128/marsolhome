
<form id="dataForm" action="admin/video/submit_tips/{{$id}}" method="post" enctype="multipart/form-data">
    {{csrf_field()}}
    <style>
        .form-section-title {
            font-size: 13px;
            text-transform: uppercase;
            color: #34495e;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #3498db;
            font-weight: bold;
            display: inline-block;
        }

        /* Video Preview Area */
        .video-preview-container {
            width: 100%;
            height: 220px;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        .video-preview-container:hover { border-color: #3498db; background: #f1f5f9; }

        #main-video-preview {
            width: 100%;
            height: 100%;
            display: none; /* Hiện khi có video */
            object-fit: contain;
            background: #000;
        }

        .preview-placeholder { text-align: center; color: #94a3b8; }
        .preview-placeholder i { font-size: 48px; margin-bottom: 10px; }

        /* Progress Bar */
        #progress-wrapper { display: none; margin: 15px 0; }
        .progress { height: 12px; border-radius: 10px; background-color: #e2e8f0; box-shadow: none; }
        .progress-bar { line-height: 12px; font-size: 10px; font-weight: bold; }

        .show_error { color: #e74c3c; margin-top: 10px; font-size: 13px; padding: 10px; background: #fdf2f2; border-radius: 4px; display: none; }

        .btn-save { background-color: #27ae60; color: white; font-weight: bold; padding: 10px 30px; border-radius: 6px; transition: all 0.2s; }
        .btn-save:hover { background-color: #219150; transform: translateY(-1px); }

        .help-block { font-size: 12px; color: #7f8c8d; }
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
                        <div class="form-section-title">Thông tin cơ bản</div>
                        <div class="form-group">
                            <label for="name">Tên Video ({{lang('c_name_video')}})</label>
                            <input type="text" name="name" class="form-control name" value="{{!empty($video) ? $video->name : ''}}" placeholder="Nhập tiêu đề video...">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-section-title">Video & Mô tả</div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Chọn File Video</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-file-video-o"></i></span>
                                <input type="file" class="form-control" name="original_video" id="original_video" accept="video/mp4,video/x-m4v,video/*">
                            </div>
                            <p class="help-block"><i class="fa fa-info-circle"></i> Chấp nhận định dạng: .MP4, .MOV (Tối đa 500MB)</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label>Video</label>
                        @if(empty($video->original_video))
                            <div class="video-preview-container" id="video-preview-box">
                                <div class="preview-placeholder" id="placeholder-text">
                                    <i class="fa fa-cloud-upload"></i>
                                    <p>Chưa có video được chọn</p>
                                </div>
                                <video id="main-video-preview" controls src="">
                                    Trình duyệt của bạn không hỗ trợ xem video.
                                </video>
                            </div>
                        @else
                            <div class="video-preview-container" id="video-preview-box" style="border-style: solid;">
                                <div class="preview-placeholder" id="placeholder-text" style="display: none;">
                                    <i class="fa fa-cloud-upload"></i>
                                    <p>Chưa có video được chọn</p>
                                </div>
                                <video id="main-video-preview" controls="" src="{{$video->original_video ?? ''}}" style="display: inline-block;">
                                    Trình duyệt của bạn không hỗ trợ xem video.
                                </video>
                            </div>
                       @endif
                    </div>
                </div>

                <!-- Thanh tiến trình tải lên -->
                <div id="progress-wrapper">
                    <div class="d-flex justify-content-between">
                        <label>Tiến trình tải lên:</label>
                        <span id="percent-text" class="pull-right">0%</span>
                    </div>
                    <div class="progress">
                        <div id="upload-progress-bar" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        </div>
                    </div>
                </div>
                <div class="show_error"></div>

                <div class="form-group group_type">
                    <label for="id_product">{{lang('c_products')}}</label>
                    <select class="id_product select2" id="id_product" data-placeholder="Chọn ..." name="id_product" data-json="{{(!empty($product) ? json_encode($product) : '')}}">
                        @if(!empty($product))
                            <option value="{{$product->id}}" data-image="{{$product->image}}" selected><img class="img_option" src="{{$product->image}}"/>{{$product->name}}</option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label>Mô tả ngắn</label>
                    <textarea name="description" id="description" class="form-control editor_short" rows="4" placeholder="Giới thiệu nội dung video...">{{!empty($video) ? $video->description : ''}}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary waves-effect waves-light" type="submit">
                    <i class="fa fa-save"></i> Lưu dữ liệu
                </button>
                <button type="button" class="btn btn-default btn-cancel" data-dismiss="modal">Hủy bỏ</button>
            </div>
        </div>
    </div>
</form>
<script>
    searchAjaxSelect2Img('#id_product','api/category/getListProduct', 0, {
        'select2':true
    })

    // Giả lập khởi tạo Editor
    var editor_short_config = { selector: '.editor_short' };
    if(typeof tinymce !== 'undefined') {
        tinymce.remove('.editor_short');
        tinymce.init(editor_short_config);
    }

    $(document).ready(function() {
        // --- XỬ LÝ PREVIEW VIDEO ---
        $('#original_video').on('change', function(event) {
            var file = event.target.files[0];
            var $video = $('#main-video-preview');
            var $placeholder = $('#placeholder-text');
            var $previewBox = $('#video-preview-box');

            if (file) {
                // Kiểm tra nếu là file video
                if (file.type.match('video.*')) {
                    var fileURL = URL.createObjectURL(file);
                    $video.attr('src', fileURL).show();
                    $placeholder.hide();
                    $previewBox.css('border-style', 'solid');
                } else {
                    alert('Vui lòng chọn một file video hợp lệ!');
                    $(this).val('');
                }
            }
        });

        // --- LOGIC SUBMIT VỚI PROGRESS BAR ---
        $("#dataForm").validate({
            rules: {
                name: "required",
                original_video: {
                    required: function() {
                        // Nếu là sửa (có dữ liệu cũ) thì không bắt buộc, nếu là thêm mới thì bắt buộc
                        return $('input[name="name"]').val() === "";
                    }
                }
            },
            messages: {
                name: "Vui lòng nhập tên video",
                original_video: "Vui lòng chọn file video"
            },
            submitHandler: function (form) {
                var url = form.action;
                var $form = $(form);
                var formData = new FormData();
                var formParams = $form.serializeArray();

                // Reset & Hiện progress bar
                $('#progress-wrapper').fadeIn();
                $('#upload-progress-bar').css('width', '0%').removeClass('progress-bar-danger').addClass('progress-bar-success');
                $('#percent-text').text('0%');
                $(".show_error").hide().empty();

                // Thêm file video
                var videoFile = $('#original_video')[0].files[0];
                if (videoFile) {
                    formData.append('original_video', videoFile);
                }

                // Thêm các trường dữ liệu khác
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
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                $('#upload-progress-bar').css('width', percentComplete + '%');
                                $('#percent-text').text(percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                })
                    .done(function (data) {
                        if (data.result) {
                            if(data.id) {
                                $.get('admin/video/renderVideo/' + data.id);
                            }
                            oTable.draw(); // Nếu bạn dùng Datatables
                            $('.close').trigger('click');
                            alert_float('success', data.message);
                        } else {
                            $(".show_error").html(data.message).fadeIn();
                            alert_float('error', data.message);
                            $('#upload-progress-bar').addClass('progress-bar-danger');
                        }
                    })
                    .fail(function (err) {
                        var htmlError = '';
                        if (err.responseJSON && err.responseJSON.errors) {
                            for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                                htmlError += `<div><i class="fa fa-warning"></i> ${message}</div>`;
                            }
                        } else {
                            htmlError = 'Có lỗi xảy ra trong quá trình tải video lên máy chủ.';
                        }
                        $(".show_error").html(htmlError).fadeIn();
                        $('#upload-progress-bar').removeClass('progress-bar-success').addClass('progress-bar-danger');
                        $('#percent-text').text('Lỗi');
                    });

                return false;
            }
        });
    });
</script>

