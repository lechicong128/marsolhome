<style>
    .modal .modal-dialog .modal-content{
        padding: unset;
        border-radius: 12px;
    }
    .mod-page-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
        margin-bottom: 20px;
    }
    .mod-page-title {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
        margin: 0;
        font-family: 'Inter', sans-serif;
    }
    .mod-btn-primary {
        background: #d4a017 !important;
        color: white !important;
        border: none !important;
        font-weight: 600 !important;
        padding: 9px 18px !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        transition: all 0.2s ease-in-out !important;
        box-shadow: 0 2px 4px rgba(212, 160, 23, 0.2) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    .mod-btn-primary:hover {
        background: #b8860b !important;
        box-shadow: 0 4px 8px rgba(212, 160, 23, 0.3) !important;
        transform: translateY(-1px);
    }
    .ul-toolbar {
        background: #ffffff;
        border: 1px solid #f3f4f6;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .ul-search {
        position: relative;
        width: 260px;
    }
    .ul-search i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 14px;
    }
    #ul-search-input {
        width: 100%;
        padding: 8px 12px 8px 36px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13px;
        color: #374151;
        outline: none;
        transition: all 0.2s;
    }
    #ul-search-input:focus {
        border-color: #3a94ef;
        box-shadow: 0 0 0 3px rgba(58, 148, 239, 0.15);
    }
    .btn-dt-reload {
        border-radius: 8px !important;
        border: 1px solid #d1d5db !important;
        background: #ffffff !important;
        padding: 8px 12px !important;
        color: #4b5563 !important;
        transition: all 0.2s !important;
    }
    .btn-dt-reload:hover {
        background: #f3f4f6 !important;
        color: #111827 !important;
    }
    .ul-card {
        background: #ffffff;
        border: 1px solid #f3f4f6;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        padding: 16px;
    }
    .table-responsive {
        border: none !important;
    }
    #table_banner {
        width: 100% !important;
        border-collapse: collapse;
    }
    #table_banner th {
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        font-size: 13px;
        border-bottom: 2px solid #e5e7eb;
        padding: 12px;
    }
    #table_banner td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    #table_banner tbody tr:hover {
        background-color: #f9fafb;
    }
    #images_vi-error {
        color: red;
        font-weight: 700;
    }
</style>
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">Quản lý Banner</h1>
    </div>
    <div class="mod-page-actions">
        <a href="admin/banner/detail" class="mod-btn-primary dt-modal">
            <i class="fa fa-plus"></i> Thêm Banner
        </a>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="ul-toolbar" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; margin-bottom: 16px; background: #ffffff; border: 1px solid #f3f4f6; border-radius: 10px;">
    <div class="checkbox checkbox-custom checkbox-inline" style="margin: 0;">
        <input type="checkbox" id="active_order_by" value="1">
        <label for="active_order_by" style="font-weight: 600; color: #4b5563; font-size: 13px;">{{lang('active_order_by')}}</label>
    </div>
    <div class="dt-buttons btn-group">
        <a class="btn-dt-reload">
            <span><i class="fa fa-refresh"></i></span>
        </a>
    </div>
</div>

<!-- Table Card -->
<div class="ul-card" style="min-height:unset !important">
    <!-- Table -->
    <div class="table-responsive">
        <table id="table_banner" class="table sortableMain">
            <thead>
                <tr>
                    <th class="text-center" style="width: 80px;">STT</th>
                    <th class="text-center">Banner</th>
                    <th class="text-center" style="width: 120px;">Hoạt Động</th>
                    <th class="text-center" style="width: 150px;">Tác vụ</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
