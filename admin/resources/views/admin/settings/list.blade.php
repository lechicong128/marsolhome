@extends('admin.layouts.index')
@section('content')

<style>
    /* ---- Settings Page Styles ---- */
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

    /* Settings Layout */
    .ud-settings-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .ud-settings-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Sidebar Navigation */
    .ud-settings-sidebar {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        padding: 20px 16px;
    }

    .ud-settings-sidebar-title {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 16px;
        padding: 0 8px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 12px;
    }

    .ud-settings-nav {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .ud-settings-nav-link {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 600;
        color: #4b5563;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none !important;
    }

    .ud-settings-nav-link:hover {
        background: #f9fafb;
        color: #111827;
    }

    .ud-settings-nav-link.active {
        background: rgba(212, 160, 23, 0.1);
        color: #d4a017 !important;
    }

    /* Card & Content */
    .ud-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .ud-tab-content {
        padding: 32px;
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

    /* Footer Actions */
    .ud-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 8px;
        padding: 0 32px 32px 32px;
        border-top: 1px solid #e5e7eb;
        padding-top: 24px;
    }

    .mod-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }

    .mod-btn-primary {
        background: #d4a017;
        color: #fff;
    }

    .mod-btn-primary:hover {
        background: #b8860b;
        color: #fff;
    }

    /* Alerts */
    .mod-alert {
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mod-alert-success {
        background: #ecfdf5;
        border: 1px solid #d1fae5;
        color: #065f46;
    }

    /* Settings specific */
    .ud-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .ud-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0 24px;
    }

    @media (max-width: 768px) {
        .ud-grid-2 {
            grid-template-columns: 1fr;
        }
    }

    .ud-avatar-upload-box {
        border: 2px dashed #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        background: #f9fafb;
        transition: all 0.2s;
    }

    .ud-avatar-upload-box:hover {
        border-color: #d4a017;
    }
</style>

<div class="ud-container">
    <!-- Page Header -->
    <div class="ud-header">
        <div class="ud-title-area">
            <h1 class="ud-title">{{lang('c_title_settings')}}</h1>
            <div class="ud-subtitle">{{lang('dt_index')}} / {{lang('c_title_settings')}} / {{$title}}</div>
        </div>
    </div>

    @if(session('success'))
    <div class="mod-alert mod-alert-success">
        <i class="fa fa-check-circle"></i> {{session('success')}}
    </div>
    @endif

    <div class="ud-settings-layout">
        <!-- Sidebar Navigation -->
        <div class="ud-settings-sidebar">
            <div class="ud-settings-sidebar-title">Danh mục cài đặt</div>
            <ul class="ud-settings-nav">
                <li>
                    <a href="admin/settings?group=info" class="ud-settings-nav-link {{(empty($group) || $group == 'info') ? 'active' : ''}}">
                        <i class="fa fa-info-circle" style="width: 24px; text-align: center; margin-right: 8px;"></i> {{lang('c_setting_info')}}
                    </a>
                </li>
                <li>
                    <a href="admin/settings?group=other" class="ud-settings-nav-link {{(empty($group) || $group == 'other') ? 'active' : ''}}">
                        <i class="fa fa-shield" style="width: 24px; text-align: center; margin-right: 8px;"></i> {{lang('c_setting_other')}}
                    </a>
                </li>
                <li>
                    <a href="admin/settings?group=version_app" class="ud-settings-nav-link {{(empty($group) || $group == 'version_app') ? 'active' : ''}}">
                        <i class="fa fa-shield" style="width: 24px; text-align: center; margin-right: 8px;"></i> {{lang('c_setting_version_app')}}
                    </a>
                </li>
            </ul>
        </div>

        <!-- Content Area -->
        <div class="ud-settings-content">
            <form action="admin/settings/submit/{{$group}}" method="post" id="formSettings" data-parsley-validate novalidate enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="ud-card">
                    <div class="ud-tab-content">
                        @include('admin.settings.' . (empty($group) ? 'info' : $group))
                    </div>

                    <div class="ud-footer">
                        <button type="submit" class="mod-btn mod-btn-primary" style="padding: 10px 32px; font-size: 15px;">
                            <i class="fa fa-save" style="margin-right: 8px;"></i> {{lang('c_save_settings')}}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    function previewSettingsImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            var imgPreview = $(input).closest('.ud-avatar-upload-box').find('.img-preview');
            reader.onload = function(e) {
                imgPreview.attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            var imgPreview = $(input).closest('.ud-avatar-upload-box').find('.img-preview');
            var imgDefault = imgPreview.data('imgdefault');
            if (imgDefault != "" && imgDefault != undefined) {
                imgPreview.attr('src', imgDefault);
            }
        }
    }

    function changeTypeTransferAddress(_this) {
        var transfer_address = $(_this).val();
        $.ajax({
                url: 'admin/settings/changeTypeTransferAddress',
                type: 'GET',
                dataType: 'JSON',
                data: {
                    transfer_address: transfer_address,
                },
            })
            .done(function(data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
                location.reload();
            })
            .fail(function(data) {
                alert_float('error', 'errors');
                $(index).removeAttr('disabled');
            });
    }
</script>
@endsection