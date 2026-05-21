@extends('admin.layouts.index')
@section('content')

<style>
    /* ---- User Detail Page Styles ---- */
    .ud-container {
        width: 100%;
        margin: 0 auto;
        padding: 0 10px;
        padding-bottom: 50px;
    }

    /* Page Header */
    .ud-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding-top: 10px;
    }

    .ud-title-area {
        flex: 1;
    }

    .ud-title {
        font-size: 24px;
        font-weight: 700;
        color: #1a1d29;
        margin: 0;
    }

    .ud-subtitle {
        font-size: 14px;
        color: #6b7280;
        margin-top: 4px;
    }

    /* Card & Layout */
    .ud-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 24px;
    }

    /* Custom Tabs */
    .ud-tabs-nav {
        display: flex;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        padding: 0 24px;
    }

    .ud-tab-link {
        padding: 16px 20px;
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none !important;
        background: transparent;
        border-top: none;
        border-left: none;
        border-right: none;
    }

    .ud-tab-link:hover {
        color: #374151;
    }

    .ud-tab-link.active {
        color: #d4a017 !important;
        border-bottom-color: #d4a017 !important;
        background: transparent !important;
    }

    .ud-tab-content {
        padding: 32px;
    }

    /* Form Grid */
    .ud-form-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 60px;
    }

    @media (max-width: 768px) {
        .ud-form-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Avatar Upload */
    .ud-avatar-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .ud-avatar-wrapper {
        position: relative;
        width: 160px;
        height: 160px;
        margin-bottom: 16px;
    }

    .ud-avatar-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        background: #f3f4f6;
    }

    .ud-avatar-edit-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 42px;
        height: 42px;
        background: #d4a017;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 4px solid #fff;
        transition: all 0.2s;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 10;
    }

    .ud-avatar-edit-btn:hover {
        transform: scale(1.1);
    }

    /* Form Elements */
    .ud-form-group {
        margin-bottom: 24px;
    }

    .ud-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 8px;
    }

    .ud-input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        color: #1f2937;
        transition: all 0.2s;
        outline: none;
        background: #fff;
    }

    .ud-input:focus {
        border-color: #d4a017;
        box-shadow: 0 0 0 4px rgba(212, 160, 23, 0.08);
    }

    .ud-input-icon-wrapper {
        position: relative;
    }

    .ud-input-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        cursor: pointer;
    }

    /* Permissions Section */
    .ud-permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .ud-permission-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
    }

    .ud-permission-title {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .ud-checkbox-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .ud-checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #4b5563;
        cursor: pointer;
        margin: 0;
    }

    .ud-checkbox-item input {
        width: 16px;
        height: 16px;
        accent-color: #d4a017;
        margin: 0;
    }

    /* Footer Actions */
    .ud-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 8px;
        padding: 0 32px 32px 32px;
    }

    /* Select2 Modernization */
    .select2-container--default .select2-selection--multiple {
        border: 1.5px solid #e5e7eb !important;
        border-radius: 10px !important;
        min-height: 42px !important;
        padding: 2px 8px !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #d4a017 !important;
    }

    /* Switchery adjustments */
    .switchery {
        background-color: #fff;
        border: 1px solid #dfdfdf;
        border-radius: 20px;
        cursor: pointer;
        display: inline-block;
        height: 20px;
        position: relative;
        vertical-align: middle;
        width: 40px;
    }
</style>

<div class="ud-container">
    <!-- Page Header -->
    <div class="ud-header">
        <div class="ud-title-area">
            <h1 class="ud-title">{{!empty($user) ? 'Chỉnh sửa người dùng' : 'Thêm người dùng mới'}}</h1>
            <div class="ud-subtitle">{{lang('dt_user')}} / {{!empty($user) ? $user->name : 'Thành viên mới'}}</div>
        </div>
        <div class="ud-actions">
            <a href="admin/user/list" class="mod-btn">
                <i class="fa fa-chevron-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mod-alert mod-alert-success" style="margin-bottom: 20px;">
        <i class="fa fa-check-circle"></i> {{session('success')}}
    </div>
    @endif

    <form action="admin/user/submit/{{$id}}" method="post" data-parsley-validate novalidate enctype="multipart/form-data">
        {{csrf_field()}}

        <div class="ud-card">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs ud-tabs-nav">
                <li class="active">
                    <a href="#tab-info" class="ud-tab-link active" data-toggle="tab">Thông tin cơ bản</a>
                </li>
                <li>
                    <a href="#tab-permission" class="ud-tab-link" data-toggle="tab">Phân quyền</a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Basic Info Tab -->
                <div class="tab-pane active" id="tab-info">
                    <div class="ud-tab-content">
                        <div class="ud-form-grid">
                            <!-- Left: Avatar & Status -->
                            <div class="ud-avatar-section">
                                <div class="ud-avatar-wrapper">
                                    @php
                                    $avatar = !empty($user) && $user->image ? asset('storage/'.$user->image) : 'admin/assets/images/users/default.png';
                                    @endphp
                                    <img src="{{$avatar}}" alt="Avatar" class="ud-avatar-img" id="avatar-preview">
                                    <label for="image-upload" class="ud-avatar-edit-btn">
                                        <i class="fa fa-camera"></i>
                                    </label>
                                    <input type="file" name="image" id="image-upload" class="hidden" style="position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none;" onchange="previewImage(this)">
                                </div>
                                <input type="hidden" name="image_old" value="{{!empty($user) ? $user->image : ''}}">

                                <div class="ud-form-group" style="width: 100%; text-align: left; background: #f9fafb; padding: 16px; border-radius: 12px; border: 1px solid #eee;">
                                    <label class="ud-label" style="margin-bottom: 12px;">{{lang('dt_active_user')}}</label>
                                    <div style="display: flex; gap: 20px;">
                                        <label class="ud-checkbox-item">
                                            <input type="radio" name="active" value="1" {{(!empty($user) && $user->active == 1) || empty($user) ? 'checked' : ''}}>
                                            <span>Hoạt động</span>
                                        </label>
                                        <label class="ud-checkbox-item">
                                            <input type="radio" name="active" value="0" {{(!empty($user) && $user->active == 0) ? 'checked' : ''}}>
                                            <span>Tạm khóa</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="ud-form-group" style="width: 100%; text-align: left; display: flex; align-items: center; justify-content: space-between; background: #f9fafb; padding: 16px; border-radius: 12px; border: 1px solid #eee;">
                                    <label class="ud-label" style="margin-bottom: 0;">{{lang('dt_admin')}} (Toàn quyền)</label>
                                    <input type="checkbox" name="admin" {{!empty($user) && $user->admin == 1 ? 'checked' : ''}} data-plugin="switchery" data-color="#d4a017" data-size="small" />
                                </div>
                            </div>

                            <!-- Right: Fields -->
                            <div class="ud-fields-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="ud-form-group">
                                            <label class="ud-label" for="code">{{lang('dt_code_user')}}</label>
                                            <input type="text" name="code" id="code" required autocomplete="off" value="{{!empty($user) ? $user->code : ''}}" class="ud-input" placeholder="Nhập mã nhân viên">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ud-form-group">
                                            <label class="ud-label" for="name">{{lang('dt_name_user')}}</label>
                                            <input type="text" name="name" id="name" required autocomplete="off" value="{{!empty($user) ? $user->name : ''}}" class="ud-input" placeholder="Nhập họ tên">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="ud-form-group">
                                            <label class="ud-label" for="email">{{lang('dt_email_user')}}</label>
                                            <input type="email" name="email" id="email" required autocomplete="off" value="{{!empty($user) ? $user->email : ''}}" class="ud-input" placeholder="email@congty.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ud-form-group">
                                            <label class="ud-label" for="phone">Số điện thoại</label>
                                            <input type="text" name="phone" id="phone" value="{{!empty($user) ? $user->phone : ''}}" class="ud-input" placeholder="Nhập số điện thoại">
                                        </div>
                                    </div>
                                </div>

                                <div class="ud-form-group">
                                    <label class="ud-label" for="department">{{lang('dt_department')}}</label>
                                    <select multiple class="select2 select2-multiple" id="department" data-placeholder="Chọn phòng ban làm việc..." name="department[]">
                                        @foreach($department as $dept)
                                        <option value="{{$dept->id}}"
                                            @if(!empty($user))
                                            @foreach($user->department as $user_dept)
                                            @if($user_dept->id == $dept->id) selected @endif
                                            @endforeach
                                            @endif
                                            >{{$dept->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="ud-form-group">
                                    <label class="ud-label" for="password">{{lang('dt_password_user')}}</label>
                                    <div class="ud-input-icon-wrapper">
                                        <input type="password" class="ud-input" id="password" name="password" placeholder="{{!empty($user) ? 'Bỏ trống nếu không thay đổi mật khẩu' : 'Nhập mật khẩu truy cập'}}">
                                        <i class="fa fa-eye ud-input-icon" onclick="togglePassword(this)"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions Tab -->
                <div class="tab-pane" id="tab-permission">
                    <div class="ud-tab-content">
                        <div class="ud-form-group">
                            <label class="ud-label" for="role">{{lang('dt_role')}} (Vai trò hệ thống)</label>
                            <select multiple class="role select2 select2-multiple" id="role" data-placeholder="Chọn một hoặc nhiều vai trò..." name="role[]">
                                @foreach($role as $r)
                                <option value="{{$r->id}}"
                                    @if(!empty($user))
                                    @foreach($user->role as $user_role)
                                    @if($user_role->id == $r->id) selected @endif
                                    @endforeach
                                    @endif
                                    >{{$r->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ud-permission-grid" id="permission-container">
                            <!-- Dynamic Permissions will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="ud-footer">
                <button type="submit" class="mod-btn mod-btn-primary" style="padding: 10px 32px; font-size: 15px;">
                    <i class="fa fa-save" style="margin-right: 8px;"></i> {{lang('dt_save')}}
                </button>
            </div>
        </div>
    </form>
</div>

@endsection
@section('script')
<script>
    function togglePassword(icon) {
        var x = document.getElementById("password");
        if (x.type === "password") {
            x.type = "text";
            $(icon).removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            x.type = "password";
            $(icon).removeClass('fa-eye-slash').addClass('fa-eye');
        }
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#avatar-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).ready(function() {
        $(".role").trigger('change');

        // Ensure tabs work correctly with our custom styles
        $('.ud-tab-link').on('click', function() {
            $('.ud-tab-link').removeClass('active');
            $(this).addClass('active');
        });

        // Force hide any file inputs that might be shown by global plugins after load
        setTimeout(function() {
            $('#image-upload').attr('style', 'display: none !important; position: absolute; opacity: 0;');
            $('.bootstrap-filestyle').attr('style', 'display: none !important;');
        }, 500);
        setTimeout(function() {
            $('#image-upload').attr('style', 'display: none !important; position: absolute; opacity: 0;');
            $('.bootstrap-filestyle').attr('style', 'display: none !important;');
        }, 100);
        setTimeout(function() {
            $('#image-upload').attr('style', 'display: none !important; position: absolute; opacity: 0;');
            $('.bootstrap-filestyle').attr('style', 'display: none !important;');
        }, 2000);
    });

    $(".role").change(function() {
        var role = $(this).val();
        var user_id = "{{$id}}";
        $.ajax({
                url: 'admin/user/getPermissonByRole',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    role: role,
                    user_id: user_id,
                },
            })
            .done(function(data) {
                var html = '';
                var permission = data.permission != undefined ? data.permission : [];
                if (data.roles != undefined) {
                    $.each(data.roles, function(k, v) {
                        var html_child = '';
                        if (v.permission.length > 0) {
                            $.each(v.permission, function(k_child, v_child) {
                                html_child += `
                                <label class="ud-checkbox-item">
                                    <input id="checkbox_${v_child.id}" ${permission.includes(v_child.id) == false ? '' : 'checked'} type="checkbox"
                                           name="permission[${v.id}][]"
                                           class="permission_${v.id}"
                                           value="${v_child.id}">
                                    <span>${v_child.name}</span>
                                </label>`;
                            });
                            html += `
                            <div class="ud-permission-card">
                                <div class="ud-permission-title">${v.name}</div>
                                <input type="hidden" name="group_permission[]" value="${v.id}">
                                <div class="ud-checkbox-list">
                                    ${html_child}
                                </div>
                                <div style="margin-top: 16px; padding-top: 12px; border-top: 1px dashed #e5e7eb; display: flex; gap: 12px;">
                                    <a href="javascript:;" onclick="checkAll(this,${v.id})" style="font-size: 12px; color: #d4a017; font-weight: 600;">Chọn tất cả</a>
                                    <a href="javascript:;" onclick="cancelAll(this,${v.id})" style="font-size: 12px; color: #9ca3af;">Bỏ chọn</a>
                                </div>
                            </div>`;
                        }
                    });
                }
                $("#permission-container").html(html);
            });
    });

    function checkAll(_this, id) {
        $(".permission_" + id).prop('checked', true);
    }

    function cancelAll(_this, id) {
        $(".permission_" + id).prop('checked', false);
    }
</script>
@endsection