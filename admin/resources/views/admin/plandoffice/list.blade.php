@extends('admin.layouts.index')
@section('page_title', $title)
@section('content')
<style>
    :root {
        --home-border: #e2e8f0;
    }
    /* Prevent dropdown clipping in tables */
    .ul-card, 
    .ul-card .table-responsive {
        overflow: visible !important;
    }

    /* ===== Left Panel: Office Card List ===== */
    .office-list-panel {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        margin-bottom: 20px;
    }
    .office-list-header {
        padding: 14px 16px;
        border-bottom: 1px solid #eef1f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .office-list-header h4 {
        font-weight: 600;
        font-size: 14px;
        color: #333;
        margin: 0;
    }
    .office-list-header .office-count {
        background: #eef2ff;
        color: #4361ee;
        font-size: 12px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
    }
    .office-search-wrap {
        padding: 10px 12px;
        border-bottom: 1px solid #f0f3f8;
    }
    .office-search-wrap .search-box {
        position: relative;
    }
    .office-search-wrap .search-box input {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 7px 12px 7px 32px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
        background: #fafbfc;
    }
    .office-search-wrap .search-box input:focus {
        border-color: #4361ee;
        background: #fff;
    }
    .office-search-wrap .search-box i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
        font-size: 13px;
    }
    .office-scroll-area {
        min-height: 320px;
        max-height: calc(100vh - 280px);
        overflow-y: auto;
        padding: 6px 6px 80px 6px;
    }
    .office-scroll-area::-webkit-scrollbar {
        width: 5px;
    }
    .office-scroll-area::-webkit-scrollbar-track {
        background: transparent;
    }
    .office-scroll-area::-webkit-scrollbar-thumb {
        background: #d0d5dd;
        border-radius: 4px;
    }

    /* ===== Office Item Card ===== */
    .office-item {
        padding: 12px 14px;
        border: 1px solid #eef1f6;
        border-radius: 8px;
        margin-bottom: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fff;
        position: relative;
    }
    .office-item:hover {
        border-color: #c7d2fe;
        background: #f8faff;
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.06);
    }
    .office-item.active {
        border-color: #4361ee;
        background: #f0f4ff;
        box-shadow: 0 2px 10px rgba(67, 97, 238, 0.12);
    }
    .office-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 8px;
        bottom: 8px;
        width: 3px;
        border-radius: 0 3px 3px 0;
        background: #4361ee;
    }
    .office-item .office-name {
        font-weight: 600;
        font-size: 13px;
        color: #1a202c;
        margin-bottom: 6px;
        line-height: 1.4;
        word-wrap: break-word;
        padding-right: 70px;
    }
    .office-item .office-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 12px;
        font-size: 11px;
        color: #718096;
    }
    .office-item .office-meta span {
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .office-item .office-meta i {
        font-size: 11px;
        width: 13px;
        text-align: center;
    }
    .office-item .office-meta .meta-province { color: #e67e22; }
    .office-item .office-meta .meta-parcels { color: #3498db; }
    .office-item .office-meta .meta-kml { color: #27ae60; }
    .office-item .office-actions {
        position: absolute;
        top: 8px;
        right: 8px;
    }
    .office-item .office-actions .dropdown .dropdown-toggle {
        height: 34px !important;
        padding: 0 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        border: 1px solid var(--home-border) !important;
        border-radius: 10px !important;
        background: #ffffff !important;
        color: #475569 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
        transition: all .16s ease !important;
    }
    .office-item .office-status-badge {
        display: inline-block;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 8px;
        border-radius: 10px;
        margin-left: 4px;
        vertical-align: middle;
    }
    .office-item .office-status-badge.status-on {
        background: #d1fae5;
        color: #065f46;
    }
    .office-item .office-status-badge.status-off {
        background: #fee2e2;
        color: #991b1b;
    }
    .office-loading {
        text-align: center;
        padding: 40px 20px;
        color: #a0aec0;
    }
    .office-loading i { font-size: 24px; }
    .office-empty {
        text-align: center;
        padding: 40px 20px;
        color: #a0aec0;
    }
    .office-empty i { font-size: 32px; display: block; margin-bottom: 10px; }

    /* ===== Right: Placeholder & Content ===== */
    .right-placeholder {
        background: #fdfdfd;
        border: 2px dashed #e0e6ed;
        border-radius: 8px;
        padding: 80px 20px;
        text-align: center;
        margin-top: 0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    .right-placeholder i {
        font-size: 54px;
        color: #a0aec0;
        margin-bottom: 20px;
        display: inline-block;
    }
    .right-placeholder h4 {
        font-size: 18px;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 10px;
    }
    .right-placeholder p {
        font-size: 14px;
        color: #718096;
        max-width: 400px;
        margin: 0 auto;
    }
    .right-content-card {
        display: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-radius: 8px;
    }

    :root {
        --home-primary: #005ae0;
        --home-primary-dark: #004ec4;
        --home-bg: #f8fafc;
        --home-card: #ffffff;
        --home-text: #0f172a;
        --home-muted: #64748b;
    }

    /* Target the right column card (land parcels list) styling to match manage_home list */
    #parcels_container.ul-card {
        padding: 0 !important;
        overflow: hidden !important;
        background: #ffffff !important;
        border: 1px solid #eef2f7 !important;
        border-radius: 18px !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.055) !important;
    }

    #table_plandoffice_parcels {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .dataTables_scrollHead #table_plandoffice_parcels thead th,
    #table_plandoffice_parcels thead th {
        padding: 13px 14px !important;
        background: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 1px solid var(--home-border) !important;
        border-top: none !important;
        font-size: 11px !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: .055em !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    #table_plandoffice_parcels tbody td {
        padding: 13px 14px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155 !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        vertical-align: middle !important;
    }

    #table_plandoffice_parcels tbody tr {
        transition: background .15s ease;
    }

    #table_plandoffice_parcels tbody tr:hover {
        background: #f8fafc !important;
    }

    #table_plandoffice_parcels tbody tr:last-child td {
        border-bottom: none !important;
    }

    #table_plandoffice_parcels tbody td:first-child {
        font-weight: 800 !important;
        color: var(--home-primary-dark) !important;
    }

    .table-fit-wrap {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden !important;
    }

    /* Style the toolbar elements */
    .manage-home-filter-card {
        padding: 16px;
        margin-bottom: 16px;
        background: #ffffff;
        border: 1px solid #eef2f7;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .filter-control {
        position: relative;
    }

    .filter-icon {
        position: absolute;
        top: 50%;
        left: 14px;
        z-index: 2;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 13px;
        pointer-events: none;
    }

    .home-input {
        width: 100%;
        height: 44px;
        padding: 0 14px 0 40px;
        border: 1px solid var(--home-border);
        border-radius: 12px;
        background: #ffffff;
        color: var(--home-text);
        font-size: 13px;
        font-weight: 600;
        outline: none;
        transition: all .16s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .home-input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .home-input:focus {
        border-color: var(--home-primary);
        box-shadow: 0 0 0 4px rgba(0, 90, 224, 0.10);
    }

    .btn-dt-reload {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: 1px solid var(--home-border);
        background: #ffffff;
        color: var(--home-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .16s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .btn-dt-reload:hover {
        color: var(--home-primary);
        border-color: var(--home-primary);
        background: #eff6ff;
    }

    .btn-action-primary {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        height: 36px !important;
        padding: 0 14px !important;
        border-radius: 10px !important;
        border: 1px solid var(--home-border) !important;
        background: #ffffff !important;
        color: #475569 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        transition: all .16s ease !important;
        text-decoration: none !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
        cursor: pointer;
    }
    
    .btn-action-primary:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: var(--home-primary) !important;
        text-decoration: none !important;
    }

    /* Nút Sửa cấu hình - xanh dương */
    .btn-action-edit {
        background: #eff6ff !important;
        border-color: #bfdbfe !important;
        color: #1d4ed8 !important;
    }
    .btn-action-edit:hover {
        background: #dbeafe !important;
        border-color: #93c5fd !important;
        color: #1e40af !important;
    }
    .btn-action-edit i {
        color: #3b82f6 !important;
    }

    /* Nút Xem bản đồ - xanh lá */
    .btn-action-map {
        background: #f0fdf4 !important;
        border-color: #bbf7d0 !important;
        color: #15803d !important;
    }
    .btn-action-map:hover {
        background: #dcfce7 !important;
        border-color: #86efac !important;
        color: #166534 !important;
    }
    .btn-action-map i {
        color: #22c55e !important;
    }

    /* Nút Trích xuất tờ thửa - cam */
    .btn-action-extract {
        background: #fffbeb !important;
        border-color: #fde68a !important;
        color: #b45309 !important;
    }
    .btn-action-extract:hover {
        background: #fef3c7 !important;
        border-color: #fcd34d !important;
        color: #92400e !important;
    }
    .btn-action-extract i {
        color: #f59e0b !important;
    }

    /* Nút Reload - tím nhạt */
    .btn-dt-reload-parcels {
        background: #f5f3ff !important;
        border-color: #ddd6fe !important;
        color: #6d28d9 !important;
    }
    .btn-dt-reload-parcels:hover {
        background: #ede9fe !important;
        border-color: #c4b5fd !important;
        color: #5b21b6 !important;
    }

    #table_plandoffice_parcels tbody td .btn-default {
        height: 32px !important;
        width: 32px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid var(--home-border) !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        color: #475569 !important;
        font-size: 13px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
        transition: all .16s ease !important;
    }

    #table_plandoffice_parcels tbody td .btn-default:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: var(--home-primary) !important;
    }

    /* Style DataTables helper layouts like page inputs, selection lengths, and page buttons */
    #parcels_container .dataTables_wrapper .dataTables_length,
    #parcels_container .dataTables_wrapper .dataTables_filter {
        padding: 14px 16px !important;
        color: var(--home-muted) !important;
        font-size: 13px !important;
    }

    #parcels_container .dataTables_wrapper .dataTables_length select,
    #parcels_container .dataTables_wrapper .dataTables_filter input {
        height: 34px !important;
        border: 1px solid var(--home-border) !important;
        border-radius: 10px !important;
        outline: none !important;
        padding: 0 10px !important;
    }

    #parcels_container .dataTables_wrapper .dataTables_info {
        padding: 15px 16px !important;
        color: var(--home-muted) !important;
        font-size: 13px !important;
        font-weight: 500 !important;
    }

    #parcels_container .dataTables_wrapper .dataTables_paginate {
        padding: 12px 16px !important;
        display: flex !important;
        justify-content: flex-end !important;
    }

    #parcels_container .dataTables_wrapper .dataTables_paginate li.paginate_button,
    #parcels_container .dataTables_wrapper .dataTables_paginate li.paginate_button.active,
    #parcels_container .dataTables_wrapper .dataTables_paginate li.paginate_button.disabled {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        height: auto !important;
        min-width: auto !important;
        box-shadow: none !important;
    }

    #parcels_container .dataTables_wrapper .dataTables_paginate li.paginate_button a,
    #parcels_container .dataTables_wrapper .dataTables_paginate a.paginate_button {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 36px !important;
        height: 36px !important;
        padding: 0 14px !important;
        margin-left: 6px !important;
        border: 1px solid var(--home-border) !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        color: #475569 !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        transition: all .15s ease !important;
        text-decoration: none !important;
        box-sizing: border-box !important;
        vertical-align: middle !important;
    }

    #parcels_container .dataTables_wrapper .dataTables_paginate li.paginate_button a:hover,
    #parcels_container .dataTables_wrapper .dataTables_paginate a.paginate_button:hover {
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    #parcels_container .dataTables_wrapper .dataTables_paginate li.paginate_button.active a,
    #parcels_container .dataTables_wrapper .dataTables_paginate a.paginate_button.current {
        background: var(--home-primary) !important;
        border-color: var(--home-primary) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 6px rgba(30, 64, 175, 0.15) !important;
    }
    
    #parcels_container .dataTables_wrapper .dataTables_paginate li.paginate_button.active a:hover,
    #parcels_container .dataTables_wrapper .dataTables_paginate a.paginate_button.current:hover {
        background: var(--home-primary) !important;
        border-color: var(--home-primary) !important;
        color: #ffffff !important;
    }

    #parcels_container .dataTables_wrapper .dataTables_paginate li.paginate_button.disabled a,
    #parcels_container .dataTables_wrapper .dataTables_paginate a.paginate_button.disabled {
        opacity: .5 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        background: #ffffff !important;
        border-color: var(--home-border) !important;
        color: #94a3b8 !important;
    }
</style>

<!-- Page-Title -->
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">{{ $title }}</h1>
    </div>
    <div class="mod-page-actions">
        <a href="admin/plandoffices/detail" class="mod-btn mod-btn-primary dt-modal">
            <i class="fa fa-plus"></i> Thêm mới
        </a>
    </div>
</div>

<div class="row">
    <!-- Left Column: Planning Offices (Card List) -->
    <div class="col-md-4 col-lg-3">
        <div class="office-list-panel">
            <div class="office-list-header">
                <h4><i class="fa fa-building-o text-primary" style="margin-right: 5px;"></i> Văn phòng QH</h4>
                <span class="office-count" id="office_total_count">0</span>
            </div>
            <div class="office-search-wrap">
                <div class="search-box">
                    <i class="fa fa-search"></i>
                    <input type="text" id="ul-search-input" placeholder="Tìm kiếm văn phòng...">
                </div>
            </div>
            <div class="office-scroll-area" id="office_list_container">
                <div class="office-loading" id="office_loading">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p style="margin-top: 10px; font-size: 13px;">Đang tải danh sách...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Land Parcels -->
    <div class="col-md-8 col-lg-9">
        <!-- Placeholder when no office is selected -->
        <div id="parcels_placeholder" class="right-placeholder">
            <i class="fa fa-map-o"></i>
            <h4>Chọn văn phòng quy hoạch</h4>
            <p>Vui lòng click chọn một văn phòng quy hoạch ở danh sách bên trái để xem danh sách tờ thửa chi tiết.</p>
        </div>

        <!-- Real content when selected -->
        <!-- Search & Filter Toolbar (Separate Card with Title) -->
        <div id="parcels_filter_card" class="manage-home-filter-card" style="display: none;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                <!-- Left: Title & Action Buttons -->
                <div style="display: flex; align-items: center; gap: 16px; flex-shrink: 0; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-list text-success" style="font-size: 15px;"></i>
                        <h4 style="font-weight: 700; font-size: 15px; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <span id="selected_office_title"></span>
                        </h4>
                    </div>
                    <div id="office_action_buttons" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;"></div>
                </div>
                
                <!-- Right: Search & Reload -->
                <div style="display: flex; align-items: center; gap: 12px; flex-grow: 1; justify-content: flex-end; max-width: 600px;">
                    <div class="filter-control" style="flex-grow: 1; margin: 0; position: relative; width: 100%;">
                        <span class="filter-icon">
                            <i class="fa fa-search"></i>
                        </span>
                        <input type="text" id="ul-search-input-parcels" placeholder="Tìm theo số tờ, số thửa, chủ đất..." class="home-input" autocomplete="off" style="width: 100%;">
                    </div>
                    <div style="margin: 0; flex-shrink: 0;">
                        <button type="button" class="btn-dt-reload btn-dt-reload-parcels" title="Tải lại">
                            <i class="fa fa-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div id="parcels_container" class="ul-card right-content-card" style="display: none;">
            <div class="table-fit-wrap" style="padding: 0 0 10px 0;">
                <table id="table_plandoffice_parcels" class="table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">{{lang('dt_stt')}}</th>
                            <th class="text-center" style="width: 80px;">Số tờ</th>
                            <th class="text-center" style="width: 80px;">Số thửa</th>
                            <th class="text-right" style="width: 120px;">Diện tích (m²)</th>
                            <th class="text-center" style="width: 100px;">Loại đất</th>
                            <th class="text-center" style="width: 100px;">Công trình</th>
                            <th>Tên chủ</th>
                            <th class="text-center" style="width: 150px;">QH Chi tiết</th>
                            <th class="text-center" style="width: 150px;">Mô tả thửa</th>
                            <th class="text-center" style="width: 80px;">Tác vụ</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script>
        var oTableParcels = null;
        var activeOfficeId = null;
        var allOffices = [];

        // Load office list via AJAX (reuse existing API)
        function loadOfficeList() {
            $('#office_loading').show();
            $.ajax({
                url: 'admin/plandoffices/getPlannings',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                dataType: 'JSON',
                success: function(res) {
                    allOffices = res.data || [];
                    $('#office_total_count').text(allOffices.length);
                    renderOfficeList(allOffices);
                    
                    // Automatically click and select the first office on load
                    if (activeOfficeId === null && allOffices.length > 0) {
                        var $firstItem = $('#office_list_container .office-item').first();
                        if ($firstItem.length) {
                            $firstItem.click();
                        }
                    } else if (activeOfficeId !== null) {
                        renderOfficeActions(activeOfficeId);
                    }
                },
                error: function() {
                    $('#office_list_container').html('<div class="office-empty"><i class="fa fa-exclamation-triangle"></i><p>Lỗi tải dữ liệu</p></div>');
                }
            });
        }

        // Render office cards from data
        function renderOfficeList(offices) {
            var $container = $('#office_list_container');
            $container.empty();

            if (!offices.length) {
                $container.html('<div class="office-empty"><i class="fa fa-inbox"></i><p>Không có văn phòng quy hoạch nào</p></div>');
                return;
            }

            offices.forEach(function(office) {
                var cleanName = $('<div>').html(office.name || '-').text();
                var provinceName = $('<div>').html(office.province_name || '').text();
                
                // Parse parcels count from HTML text
                var parcelsCount = '0';
                var parcelsRaw = $('<div>').html(office.parcels_count || '').text();
                var parcelsMatch = parcelsRaw.match(/([\d,.]+)/);
                if (parcelsMatch) {
                    parcelsCount = parcelsMatch[1];
                }
                
                // Parse KML count
                var kmlCount = 0;
                var kmlRaw = $('<div>').html(office.kml_file || '').text();
                var kmlMatch = kmlRaw.match(/(\d+)\s*file/i);
                if (kmlMatch) {
                    kmlCount = parseInt(kmlMatch[1]);
                } else if (kmlRaw && kmlRaw.indexOf('Không') === -1) {
                    kmlCount = kmlRaw.split('||').filter(function(f) { return f.trim(); }).length;
                }

                // Parse active status
                var isActive = false;
                var activeHtml = office.active || '';
                if (typeof activeHtml === 'string') {
                    isActive = activeHtml.indexOf('checked') !== -1;
                } else {
                    isActive = activeHtml == 1;
                }

                var statusBadge = isActive 
                    ? '<span class="office-status-badge status-on">Hoạt động</span>'
                    : '<span class="office-status-badge status-off">Tắt</span>';

                // Build dropdown options (reuse from server, but adjust style and items to match exactly)
                var $optionsTemp = $('<div>').html(office.options || '');
                
                // Remove the "Danh sách tờ thửa", "Sửa cấu hình", "Xem bản đồ", and "Trích xuất tờ thửa" menu items
                $optionsTemp.find('a[href*="/parcels/"]').closest('li').remove();
                $optionsTemp.find('a[href*="/detail/"]').closest('li').remove();
                $optionsTemp.find('a[href*="/view-map/"]').closest('li').remove();
                $optionsTemp.find('a.btn-extract-kml').closest('li').remove();
                
                // Get all remaining <a> tags to reconstruct the dropdown menu
                var menuItems = [];
                var remainingItems = $optionsTemp.find('ul.dropdown-menu li');
                
                remainingItems.each(function(index) {
                    var $li = $(this);
                    var $a = $li.find('a');
                    if ($a.length) {
                        // Clear inline styles if any
                        $a.removeAttr('style');
                        $a.find('i').removeAttr('style');

                        // Get the icon classes
                        var $i = $a.find('i');
                        var iconClasses = '';
                        if ($i.length) {
                            var classList = $i.attr('class').split(/\s+/);
                            var faClasses = classList.filter(function(c) {
                                return c.indexOf('fa-') === 0 || c === 'fa';
                            });
                            iconClasses = faClasses.join(' ');
                        }

                        if ($a.hasClass('po-delete')) {
                            // For delete option
                            $a.attr('class', 'po-delete flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50/50 transition-colors');
                            $i.attr('class', iconClasses + ' text-red-400');
                            
                            // Add a divider before delete if it's not the first item
                            if (index > 0) {
                                menuItems.push('<li class="divider" style="margin: 4px 0"></li>');
                            }
                        } else {
                            // For edit/view/extract options
                            var extraClasses = '';
                            if ($a.hasClass('dt-modal')) extraClasses += ' dt-modal';
                            if ($a.hasClass('btn-extract-kml')) extraClasses += ' btn-extract-kml';
                            
                            $a.attr('class', 'flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors' + extraClasses);
                            $i.attr('class', iconClasses + ' text-slate-400');
                        }
                        
                        menuItems.push(`<li style="cursor: pointer">${$li.html()}</li>`);
                    }
                });

                // Build the active status toggle item inside the dropdown menu
                var statusToggleItem = '';
                if (isActive) {
                    statusToggleItem = `
                        <li>
                            <a class="change-status-btn flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50/50 transition-colors" data-id="${office.id}" data-status="1" style="cursor: pointer;">
                                <i class="fa fa-ban text-red-400"></i> Ngừng hoạt động
                            </a>
                        </li>
                        <li class="divider" style="margin: 4px 0"></li>
                    `;
                } else {
                    statusToggleItem = `
                        <li>
                            <a class="change-status-btn flex items-center gap-2 px-4 py-2 text-sm text-emerald-600 hover:bg-emerald-50/50 transition-colors" data-id="${office.id}" data-status="0" style="cursor: pointer;">
                                <i class="fa fa-check text-emerald-400"></i> Bật hoạt động
                            </a>
                        </li>
                        <li class="divider" style="margin: 4px 0"></li>
                    `;
                }
                menuItems.unshift(statusToggleItem);

                var optionsHtml = '';
                if (menuItems.length) {
                    optionsHtml = `
                        <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-sm transition-all" type="button" id="dropdownMenu${office.id}" data-toggle="dropdown" aria-expanded="true">
                                Tác vụ
                                <i class="fa fa-chevron-down text-[9px] opacity-60"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right rounded-2xl shadow-xl border border-slate-100 py-1.5 min-w-[160px]" role="menu" aria-labelledby="dropdownMenu${office.id}">
                                ${menuItems.join('')}
                            </ul>
                        </div>
                    `;
                }

                var card = `
                    <div class="office-item" data-id="${office.id}" data-name="${cleanName}">
                        <div class="office-actions">
                            ${optionsHtml}
                        </div>
                        <div class="office-name">${cleanName} ${statusBadge}</div>
                        <div class="office-meta">
                            ${provinceName ? `<span class="meta-province"><i class="fa fa-map-marker"></i> ${provinceName}</span>` : ''}
                            <span class="meta-parcels"><i class="fa fa-th"></i> ${parcelsCount} thửa</span>
                            ${kmlCount > 0 ? `<span class="meta-kml"><i class="fa fa-file-code-o"></i> ${kmlCount} KML</span>` : ''}
                        </div>
                    </div>
                `;
                $container.append(card);
            });

            // Restore active selection
            if (activeOfficeId !== null) {
                $container.find('.office-item[data-id="' + activeOfficeId + '"]').addClass('active');
            }

            // Reinit Switchery if needed
            if (typeof Switchery !== 'undefined') {
                var elems = Array.prototype.slice.call($container[0].querySelectorAll('.dt-active'));
                elems.forEach(function(html) {
                    if(!html.hasAttribute('data-switchery-initialized')) {
                        var switchery = new Switchery(html, {
                            color: html.getAttribute('data-color') || '#0050c8',
                            size: 'small'
                        });
                        html.setAttribute('data-switchery-initialized', 'true');
                    }
                });
            }
        }

        function renderOfficeActions(officeId) {
            var office = allOffices.find(function(o) { return o.id == officeId; });
            var actionsHtml = '';
            if (office) {
                // 1. Sửa cấu hình
                actionsHtml += `<a href="admin/plandoffices/detail/${office.id}" class="btn-action-primary btn-action-edit dt-modal"><i class="fa fa-pencil"></i> Sửa cấu hình</a>`;
                
                // 2. Xem bản đồ & Trích xuất tờ thửa (only if there is KML)
                var kmlCount = 0;
                var kmlRaw = $('<div>').html(office.kml_file || '').text();
                var kmlMatch = kmlRaw.match(/(\d+)\s*file/i);
                if (kmlMatch) {
                    kmlCount = parseInt(kmlMatch[1]);
                } else if (kmlRaw && kmlRaw.indexOf('Không') === -1) {
                    kmlCount = kmlRaw.split('||').filter(function(f) { return f.trim(); }).length;
                }
                
                if (kmlCount > 0) {
                    actionsHtml += `<a href="admin/plandoffices/view-map/${office.id}" class="btn-action-primary btn-action-map dt-modal"><i class="fa fa-map-marker"></i> Xem bản đồ</a>`;
                    actionsHtml += `<button type="button" class="btn-action-primary btn-action-extract btn-extract-kml" data-id="${office.id}"><i class="fa fa-database"></i> Trích xuất tờ thửa</button>`;
                }
            }
            $('#office_action_buttons').html(actionsHtml);
        }

        // Click office card
        $(document).on('click', '.office-item', function(e) {
            // Skip click if on dropdown/action items
            if ($(e.target).closest('a, button, input, .dropdown-menu, .popover, .switchery, .dropdown-toggle, .office-actions').length) {
                return;
            }

            var officeId = $(this).data('id');
            var officeName = $(this).data('name');

            // Highlight
            $('.office-item').removeClass('active');
            $(this).addClass('active');
            activeOfficeId = officeId;

            // Load parcels
            $('#parcels_placeholder').hide();
            $('#parcels_filter_card').show();
            $('#parcels_container').show();
            $('#selected_office_title').html('<span class="text-primary">' + officeName + '</span>');

            renderOfficeActions(officeId);

            loadParcelsTable(officeId);
        });

        // Initialize or reload parcels DataTable
        function loadParcelsTable(officeId) {
            var url = 'admin/plandoffices/getParcels/' + officeId;
            
            if (oTableParcels) {
                oTableParcels.ajax.url(url).load();
            } else {
                oTableParcels = InitDataTable('#table_plandoffice_parcels', url, {
                    'order': [
                        [0, 'desc']
                    ],
                    'responsive': true,
                    "ajax": {
                        "type": "POST",
                        "url": url,
                        "dataSrc": function (json) {
                            return json.data;
                        }
                    },
                    columnDefs: [
                        {
                            "render": function (data, type, row) {
                                return `<div class="text-center">${data}</div>`;
                            },
                            data: 'DT_RowIndex', name: 'DT_RowIndex', width: "50px", orderable: false, searchable: false
                        },
                        {
                            "render": function (data, type, row) {
                                return `<div class="text-center font-bold">${data || '-'}</div>`;
                            },
                            data: 'so_to', name: 'so_to', width: "80px"
                        },
                        {
                            "render": function (data, type, row) {
                                return `<div class="text-center font-bold">${data || '-'}</div>`;
                            },
                            data: 'so_thua', name: 'so_thua', width: "80px"
                        },
                        {
                            data: 'dien_tich', name: 'dien_tich', width: "120px"
                        },
                        {
                            "render": function (data, type, row) {
                                return `<div class="text-center">${data || '-'}</div>`;
                            },
                            data: 'loai_dat', name: 'loai_dat', width: "100px"
                        },
                        {
                            "render": function (data, type, row) {
                                return `<div class="text-center">${data || '-'}</div>`;
                            },
                            data: 'cong_trinh', name: 'cong_trinh', width: "100px"
                        },
                        {
                            "render": function (data, type, row) {
                                return `<div>${data || '-'}</div>`;
                            },
                            data: 'ten_chu', name: 'ten_chu'
                        },
                        {
                            data: 'loai_dat_quy_hoach', name: 'loai_dat_quy_hoach', width: "150px"
                        },
                        {
                            data: 'mo_ta_thua', name: 'mo_ta_thua', width: "150px"
                        },
                        {
                            data: 'options', name: 'options', orderable: false, searchable: false, width: "80px"
                        }
                    ]
                });
            }
        }
        
        // Office search (client-side filter)
        var searchTimer;
        $('#ul-search-input').on('keyup', function() {
            clearTimeout(searchTimer);
            var val = this.value.toLowerCase().trim();
            searchTimer = setTimeout(function() {
                if (!val) {
                    renderOfficeList(allOffices);
                } else {
                    var filtered = allOffices.filter(function(o) {
                        var name = $('<div>').html(o.name || '').text().toLowerCase();
                        var province = $('<div>').html(o.province_name || '').text().toLowerCase();
                        return name.indexOf(val) !== -1 || province.indexOf(val) !== -1;
                    });
                    renderOfficeList(filtered);
                }
            }, 300);
        });

        // Parcels search & reload
        var searchTimerParcels;
        $('#ul-search-input-parcels').on('keyup', function() {
            clearTimeout(searchTimerParcels);
            var val = this.value;
            searchTimerParcels = setTimeout(function() {
                if (oTableParcels) {
                    oTableParcels.search(val).draw();
                }
            }, 400);
        });
        $('.btn-dt-reload-parcels').click(function() {
            if (oTableParcels) {
                oTableParcels.draw('page');
            }
        });
        
        // Extract KML Action
        $(document).on('click', '.btn-extract-kml', function() {
            var $btn = $(this);
            var id = $btn.data('id');
            var originalHtml = $btn.html();
            
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang xử lý...');
            
            $.ajax({
                url: 'admin/plandoffices/extract-parcels',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id
                },
                dataType: 'JSON',
                success: function(res) {
                    $btn.prop('disabled', false).html(originalHtml);
                    if (res.result) {
                        alert_float('success', res.message);
                        loadOfficeList(); // Reload office cards
                        if (activeOfficeId === id && oTableParcels) {
                            oTableParcels.draw('page');
                        }
                    } else {
                        alert_float('error', res.message);
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                    alert_float('error', 'Có lỗi xảy ra, vui lòng thử lại.');
                }
            });
        });

        // Custom delete click handler to reload office list
        $('body').on('click', '.dt-delete', function(e) {
            var $btn = $(this);
            var link = $btn.attr('href');
            
            if (link && link.indexOf('admin/plandoffices/delete/') !== -1) {
                e.preventDefault();
                e.stopPropagation();
                $('.po-delete').popover('hide');
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: link,
                    type: 'GET',
                    dataType: 'JSON',
                })
                .done(function(res) {
                    if (res.result) {
                        alert_float('success', res.message);
                        
                        var deletedId = link.split('/').pop();
                        if (activeOfficeId == deletedId) {
                            activeOfficeId = null;
                            $('#parcels_placeholder').show();
                            $('#parcels_filter_card').hide();
                            $('#parcels_container').hide();
                        }
                        
                        loadOfficeList();
                    } else {
                        alert_float('error', res.message);
                    }
                })
                .fail(function() {
                    alert_float('error', 'Có lỗi xảy ra khi xóa quy hoạch.');
                });
                return false;
            }
        });

        // Toggle status click handler
        $(document).on('click', '.change-status-btn', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var currentStatus = $(this).data('status');
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.ajax({
                url: 'admin/plandoffices/changeStatus/' + id,
                type: 'GET',
                dataType: 'JSON',
                data: {
                    status: currentStatus
                },
                success: function(res) {
                    if (res.result) {
                        alert_float('success', res.message);
                        loadOfficeList();
                    } else {
                        alert_float('error', res.message);
                    }
                },
                error: function() {
                    alert_float('error', 'Có lỗi xảy ra, vui lòng thử lại.');
                }
            });
        });

        function loadDataSetup() {
            loadOfficeList();
        }

        // Init
        loadOfficeList();
    </script>
@endsection
