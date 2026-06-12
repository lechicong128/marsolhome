<style>
    .modal .modal-dialog .modal-content {
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
    .btn-dt-reload {
        border-radius: 8px !important;
        border: 1px solid #d1d5db !important;
        background: #ffffff !important;
        padding: 8px 12px !important;
        color: #4b5563 !important;
        transition: all 0.2s !important;
        cursor: pointer;
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
    #table_featured_locations {
        width: 100% !important;
        border-collapse: collapse;
    }
    #table_featured_locations th {
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        font-size: 13px;
        border-bottom: 2px solid #e5e7eb;
        padding: 12px;
    }
    #table_featured_locations td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    #table_featured_locations tbody tr:hover {
        background-color: #f9fafb;
    }
    .select2-container {
        width: 100% !important;
    }
</style>
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">Quản lý Địa điểm nổi bật</h1>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="ul-toolbar">
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
        <table id="table_featured_locations" class="table sortableMain">
            <thead>
                <tr>
                    <th class="text-center" style="width: 80px;">STT</th>
                    <th class="text-center">Tỉnh / Thành phố</th>
                    <th class="text-center">Tên tùy chỉnh</th>
                    <th class="text-center">Ảnh nền</th>
                    <th class="text-center" style="width: 120px;">Hoạt Động</th>
                    <th class="text-center" style="width: 150px;">Tác vụ</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
