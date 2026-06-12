@extends('admin.layouts.index')
@section('page_title', $title)
@section('content')
<style>
    :root {
        --home-primary: #005ae0;
        --home-primary-dark: #004ec4;
        --home-bg: #f8fafc;
        --home-card: #ffffff;
        --home-border: #e2e8f0;
        --home-text: #0f172a;
        --home-muted: #64748b;
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
        color: var(--home-text) !important;
        letter-spacing: -0.02em;
    }

    .mod-page-header-left:after {
        content: "Quản lý danh sách bản đồ quy hoạch, ranh giới và tệp tin KML";
        display: block;
        margin-top: 5px;
        font-size: 13px;
        font-weight: 500;
        color: var(--home-muted);
    }

    .mod-btn.mod-btn-primary {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        height: 42px !important;
        padding: 0 16px !important;
        border-radius: 12px !important;
        border: 0 !important;
        color: #ffffff !important;
        background: linear-gradient(135deg, var(--home-primary), #2563eb) !important;
        box-shadow: 0 10px 18px rgba(0, 90, 224, 0.22) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        transition: all .18s ease;
    }

    .mod-btn.mod-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(0, 90, 224, 0.26) !important;
        color: #ffffff !important;
        text-decoration: none !important;
    }

    .manage-home-filter-card {
        padding: 16px;
        margin-bottom: 16px;
        background: var(--home-card);
        border: 1px solid #eef2f7;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .manage-home-filter-grid {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .filter-control {
        position: relative;
        flex-grow: 1;
    }

    .filter-control .filter-icon {
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
        cursor: pointer;
    }

    .btn-dt-reload:hover {
        color: var(--home-primary);
        border-color: var(--home-primary);
        background: #eff6ff;
    }

    .ul-card {
        padding: 0 !important;
        overflow: hidden !important;
        background: #ffffff !important;
        border: 1px solid #eef2f7 !important;
        border-radius: 18px !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.055) !important;
    }

    .ul-card .table-responsive {
        overflow: visible !important;
    }

    #table_planning {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    #table_planning thead th {
        padding: 13px 14px !important;
        background: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 1px solid var(--home-border) !important;
        border-top: none !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: .055em !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    #table_planning tbody td {
        padding: 13px 14px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155 !important;
        font-size: 14.5px !important;
        font-weight: 500 !important;
        vertical-align: middle !important;
    }

    #table_planning tbody tr {
        transition: background .15s ease;
    }

    #table_planning tbody tr:hover {
        background: #f8fafc !important;
    }

    #table_planning tbody tr:last-child td {
        border-bottom: none !important;
    }

    #table_planning tbody td:first-child {
        font-weight: 800 !important;
        color: var(--home-primary-dark) !important;
    }

    #table_planning tbody td .dropdown .dropdown-toggle {
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

    #table_planning tbody td .dropdown .dropdown-toggle:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: var(--home-text) !important;
    }

    #table_planning tbody td .dropdown .dropdown-toggle::after {
        display: none !important;
    }

    .dropdown-menu-right {
        right: 0 !important;
        left: auto !important;
    }

    .dropdown-menu.dropdown-menu-right {
        min-width: 170px !important;
        padding: 7px !important;
        border: 1px solid var(--home-border) !important;
        border-radius: 14px !important;
        background: #ffffff !important;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.16) !important;
    }

    .dropdown-menu.dropdown-menu-right li a {
        display: flex !important;
        align-items: center !important;
        gap: 9px !important;
        padding: 9px 10px !important;
        border-radius: 10px !important;
        color: #475569 !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        transition: all .14s ease !important;
    }

    .dropdown-menu.dropdown-menu-right li a:hover {
        background: #eff6ff !important;
        color: var(--home-primary) !important;
    }

    .dropdown-menu.dropdown-menu-right li a i {
        width: 16px;
        text-align: center;
        font-size: 14px;
    }

    .dropdown-menu.dropdown-menu-right .divider {
        height: 1px !important;
        margin: 6px 0 !important;
        background: #f1f5f9 !important;
        border: 0 !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 14px 16px !important;
        color: var(--home-muted) !important;
        font-size: 13px !important;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        height: 34px !important;
        border: 1px solid var(--home-border) !important;
        border-radius: 10px !important;
        outline: none !important;
        padding: 0 10px !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding: 15px 16px !important;
        color: var(--home-muted) !important;
        font-size: 13px !important;
        font-weight: 500 !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding: 12px 16px !important;
        display: flex !important;
        justify-content: flex-end !important;
    }

    .dataTables_wrapper .dataTables_paginate li.paginate_button,
    .dataTables_wrapper .dataTables_paginate li.paginate_button.active,
    .dataTables_wrapper .dataTables_paginate li.paginate_button.disabled {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        height: auto !important;
        min-width: auto !important;
        box-shadow: none !important;
    }

    .dataTables_wrapper .dataTables_paginate li.paginate_button a,
    .dataTables_wrapper .dataTables_paginate a.paginate_button {
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

    .dataTables_wrapper .dataTables_paginate li.paginate_button a:hover,
    .dataTables_wrapper .dataTables_paginate a.paginate_button:hover {
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    .dataTables_wrapper .dataTables_paginate li.paginate_button.active a,
    .dataTables_wrapper .dataTables_paginate a.paginate_button.current {
        background: var(--home-primary) !important;
        border-color: var(--home-primary) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 6px rgba(30, 64, 175, 0.15) !important;
    }

    .dataTables_wrapper .dataTables_paginate li.paginate_button.active a:hover,
    .dataTables_wrapper .dataTables_paginate a.paginate_button.current:hover {
        background: var(--home-primary) !important;
        border-color: var(--home-primary) !important;
        color: #ffffff !important;
    }

    .dataTables_wrapper .dataTables_paginate li.paginate_button.disabled a,
    .dataTables_wrapper .dataTables_paginate a.paginate_button.disabled {
        opacity: .5 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        background: #ffffff !important;
        border-color: var(--home-border) !important;
        color: #94a3b8 !important;
    }

    .popover {
        border: 1px solid var(--home-border) !important;
        border-radius: 14px !important;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.16) !important;
        padding: 8px !important;
    }

    .popover .popover-content {
        padding: 4px !important;
    }

    .popover .btn {
        border-radius: 8px !important;
        padding: 5px 10px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
    }

    .popover .btn-danger {
        background: #ef4444 !important;
        border-color: #ef4444 !important;
        color: #ffffff !important;
    }

    .popover .btn-default {
        background: #ffffff !important;
        border-color: var(--home-border) !important;
        color: #475569 !important;
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

<!-- Page-Title -->
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">{{ $title }}</h1>
    </div>
    <div class="mod-page-actions">
        <a href="admin/plannings/detail" class="mod-btn mod-btn-primary dt-modal">
            <i class="fa fa-plus"></i> Thêm quy hoạch
        </a>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="manage-home-filter-card">
    <div class="manage-home-filter-grid">
        <div class="filter-control">
            <span class="filter-icon">
                <i class="fa fa-search"></i>
            </span>
            <input
                type="text"
                id="ul-search-input"
                placeholder="Tìm theo tên quy hoạch..."
                class="home-input"
                autocomplete="off"
            >
        </div>
        <div>
            <button type="button" class="btn-dt-reload" title="Tải lại dữ liệu">
                <i class="fa fa-refresh"></i>
            </button>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="ul-card">
    <!-- Table -->
    <div class="table-responsive">
        <table id="table_planning" class="table">
            <thead>
                <tr>
                    <th class="text-center">{{lang('dt_stt')}}</th>
                    <th class="text-center">Tên quy hoạch</th>
                    <th class="text-center">Khu vực</th>
                    <th class="text-center">Số quyết định</th>
                    <th class="text-center">Loại quy hoạch</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Quy mô / Diện tích</th>
                    <th class="text-center">File KML/KMZ</th>
                    <th class="text-center">Hiển thị</th>
                    <th class="text-center">{{lang('dt_actions')}}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection
@section('script')
    <script>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_planning', 'admin/plannings/getPlannings', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/plannings/getPlannings",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'DT_RowIndex', name: 'DT_RowIndex', width: "50px"
                },
                {data: 'name', name: 'name'},
                {data: 'province_name', name: 'province_name', width: "180px"},
                {data: 'decision_info', name: 'decision_info', width: "160px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'planning_type_text', name: 'planning_type_text', width: "120px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'status_text', name: 'status_text', width: "120px"
                },
                {data: 'area', name: 'area', width: "150px"},
                {data: 'kml_file', name: 'kml_file', width: "130px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'active', name: 'active', width: "80px"
                },
                {data: 'options', name: 'options', orderable: false, searchable: false, width: "140px"}
            ]
        });
        var searchTimer;
        $('#ul-search-input').on('keyup', function() {
            clearTimeout(searchTimer);
            var val = this.value;
            searchTimer = setTimeout(function() {
                oTable.search(val).draw();
            }, 400);
        });
        $('.btn-dt-reload').click(function() {
            oTable.draw('page');
        });
    </script>
@endsection
