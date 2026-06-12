@extends('admin.layouts.index')
@section('page_title', lang('dt_user'))
@section('content')

<style>
    /* ---- User List Page Styles ---- */

    /* Search & Filter Bar */
    .ul-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0;
        margin-bottom: 20px;
        background: transparent;
    }

    .ul-search {
        flex: 1;
        position: relative;
        max-width: 600px;
    }

    .ul-search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 14px;
        pointer-events: none;
    }

    .ul-search input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        color: #1a1d29;
        background: #fff;
        outline: none;
        transition: all 0.2s ease;
    }

    .ul-search input:focus {
        border-color: #005ae0;
        box-shadow: 0 0 0 3px rgba(0, 90, 224, 0.10);
    }

    .ul-search input::placeholder {
        color: #b0b5bf;
    }

    .ul-filter-tabs {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
        flex-shrink: 0;
    }

    .ul-filter-tab {
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        background: transparent;
        border: 1.5px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        font-family: inherit;
    }

    .ul-filter-tab:hover {
        background: #f9fafb;
        color: #374151;
    }

    .ul-filter-tab.active {
        background: #005ae0;
        color: #fff;
        border-color: #005ae0;
    }

    /* Table Card Override */
    .ul-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    /* Hide DataTables default controls */
    .ul-card .dataTables_wrapper .top,
    .ul-card .dataTables_length,
    .ul-card .dataTables_filter,
    .ul-card .dt-buttons {
        display: none !important;
    }

    /* Table styles */
    .ul-card table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0 !important;
    }

    .ul-card table thead th {
        background: #f9fafb !important;
        border: none !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 14px 20px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #9ca3af !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
        white-space: nowrap;
    }

    .ul-card table tbody td {
        padding: 14px 20px !important;
        border: none !important;
        border-bottom: 1px solid #f3f4f6 !important;
        color: #374151;
        font-size: 14px;
        vertical-align: middle !important;
    }

    .ul-card table tbody tr:last-child td {
        border-bottom: none !important;
    }

    .ul-card table tbody tr:hover td {
        background: #fafbfc !important;
    }

    /* User cell with avatar */
    .ul-user-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ul-user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        border: 2px solid #f3f4f6;
        background: #f3f4f6;
    }

    .ul-user-info {
        min-width: 0;
    }

    .ul-user-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ul-user-email {
        font-size: 12px;
        color: #9ca3af;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Role badge */
    .ul-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        background: #f3f4f6;
        color: #6b7280;
    }

    .ul-role-badge.admin {
        background: #fef3c7;
        color: #b45309;
    }

    .ul-role-badge.user {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .ul-role-badge.poster {
        background: #ede9fe;
        color: #6d28d9;
    }

    /* Status badge */
    .ul-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .ul-status.active {
        background: #ecfdf5;
        color: #059669;
    }

    .ul-status.inactive {
        background: #fef2f2;
        color: #dc2626;
    }

    .ul-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    /* Action buttons */
    .ul-actions {
        display: flex;
        align-items: center;
        gap: 4px;
        justify-content: flex-end;
    }

    .ul-action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .ul-action-btn:hover {
        border-color: #3b82f6;
        color: #3b82f6;
        background: #eff6ff;
    }

    .ul-action-btn.delete:hover {
        border-color: #ef4444;
        color: #ef4444;
        background: #fef2f2;
    }

    /* Bottom bar: fix the actual DataTables DOM structure */
    .ul-card .dataTables_wrapper>.row.pull-left {
        float: left !important;
        width: auto !important;
        padding: 16px 20px !important;
        margin: 0 !important;
        border-top: 1px solid #f3f4f6;
    }

    .ul-card .dataTables_wrapper>.row.pull-right {
        float: right !important;
        width: auto !important;
        padding: 16px 20px !important;
        margin: 0 !important;
        border-top: 1px solid #f3f4f6;
    }

    /* Clearfix to contain the floated rows */
    .ul-card .dataTables_wrapper::after {
        content: '';
        display: table;
        clear: both;
    }

    .ul-card .dataTables_info {
        font-size: 13px;
        color: #9ca3af;
        padding: 0 !important;
        line-height: 32px;
        /* Align with pagination height */
    }

    .ul-card .dataTables_paginate {
        padding: 0 !important;
        margin: 0 !important;
    }

    .ul-card .dataTables_paginate ul.pagination {
        margin: 0 !important;
        padding: 0 !important;
        display: flex !important;
        align-items: center;
        gap: 4px;
        list-style: none !important;
    }

    /* Hide colvis and page jump if not needed */
    .ul-card #colvis,
    .ul-card .dt-page-jump {
        display: none !important;
    }

    .ul-card .dataTables_paginate .paginate_button {
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
        border: none !important;
        background: transparent !important;
    }

    .ul-card .dataTables_paginate .paginate_button a {
        display: block !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 6px !important;
        padding: 6px 14px !important;
        font-size: 13px !important;
        color: #6b7280 !important;
        background: #fff !important;
        transition: all 0.2s ease;
        cursor: pointer;
        line-height: 1.4 !important;
        text-decoration: none !important;
    }

    .ul-card .dataTables_paginate .paginate_button:hover a {
        background: #f9fafb !important;
        border-color: #005ae0 !important;
        color: #005ae0 !important;
        box-shadow: none !important;
    }

    .ul-card .dataTables_paginate .paginate_button.active a,
    .ul-card .dataTables_paginate .paginate_button.current a {
        background: #005ae0 !important;
        border-color: #005ae0 !important;
        color: #fff !important;
        font-weight: 600 !important;
    }

    .ul-card .dataTables_paginate .paginate_button.disabled a {
        opacity: 0.35 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
    }

    /* Empty state */
    .ul-empty {
        text-align: center;
        padding: 50px 20px;
        color: #b0b5bf;
        font-size: 14px;
    }

    /* Table responsive */
    .ul-card .table-responsive {
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ul-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .ul-filter-tabs {
            margin-left: 0;
            overflow-x: auto;
        }

        .ul-search {
            max-width: 100%;
        }
    }
</style>

<!-- Page Header -->
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">Quản lý người dùng</h1>
        <div class="mod-page-subtitle" id="total-count">0 tài khoản</div>
    </div>
    <div class="mod-page-actions">
        <a href="admin/user/detail" class="mod-btn mod-btn-primary">
            <i class="fa fa-plus"></i> Tạo tài khoản
        </a>
    </div>
</div>

@if(session('success'))
<div class="mod-alert mod-alert-success">
    <i class="fa fa-check-circle"></i> {{session('success')}}
</div>
@endif

<!-- Search & Filter Toolbar -->
<div class="ul-toolbar">
    <div class="ul-search">
        <i class="fa fa-search"></i>
        <input type="text" id="ul-search-input" placeholder="Tìm theo tên...">
    </div>
</div>

<!-- Table Card -->
<div class="ul-card">
    <!-- Table -->
    <div class="table-responsive">
        <table id="table_user" class="table">
            <thead>
                <tr>
                    <th>Người dùng</th>
                    <th>SĐT</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th class="text-right">Thao tác</th>
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

    oTable = InitDataTable('#table_user', 'admin/user/getUsers', {
        'order': [
            [4, 'desc']
        ],
        'responsive': true,
        "ajax": {
            "type": "POST",
            "url": "admin/user/getUsers",
            "data": function(d) {
                for (var key in fnserverparams) {
                    d[key] = $(fnserverparams[key]).val();
                }
                d.role_filter = $('.ul-filter-tab.active').data('filter') || 'all';
            },
            "dataSrc": function(json) {
                var count = json.recordsTotal || json.data.length || 0;
                $('#total-count').text(count + ' tài khoản');
                return json.data;
            }
        },
        columnDefs: [{
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    var imgUrl = 'admin/assets/images/users/default.png';
                    if (row.image && typeof row.image === 'string') {
                        var match = row.image.match(/src="([^"]+)"/);
                        imgUrl = match ? match[1] : row.image;
                    }
                    var email = row.email || '';
                    var name = data || '';
                    return '<div class="ul-user-cell">' +
                        '<img src="' + imgUrl + '" class="ul-user-avatar">' +
                        '<div class="ul-user-info">' +
                        '<div class="ul-user-name">' + name + '</div>' +
                        '<div class="ul-user-email">' + email + '</div>' +
                        '</div>' +
                        '</div>';
                }
            },
            {
                data: 'phone',
                name: 'phone',
                render: function(data) {
                    return '<span style="color: #6b7280;">' + (data || '—') + '</span>';
                }
            },
            {
                data: 'role',
                name: 'role',
                render: function(data) {
                    if (!data) return '';
                    var cls = '';
                    var lower = data.toLowerCase();
                    if (lower.indexOf('admin') >= 0 || lower.indexOf('quản trị') >= 0) cls = 'admin';
                    else if (lower.indexOf('poster') >= 0 || lower.indexOf('đăng tin') >= 0) cls = 'poster';
                    else cls = 'user';
                    return '<span class="ul-role-badge ' + cls + '">' + data + '</span>';
                }
            },
            {
                data: 'active',
                name: 'active',
                render: function(data) {
                    var isActive = false;
                    if (data == 1 || data == 'active') isActive = true;
                    else if (typeof data === 'string' && (data.indexOf('Hoạt động') >= 0 || data.indexOf('active') >= 0 || data.indexOf('fa-check') >= 0)) {
                        isActive = true;
                    }

                    if (isActive) {
                        return '<span class="ul-status active"><span class="ul-status-dot"></span>Hoạt động</span>';
                    }
                    return '<span class="ul-status inactive"><span class="ul-status-dot"></span>Khóa</span>';
                }
            },
            {
                data: 'department',
                name: 'department',
                render: function(data) {
                    return '<span style="color: #9ca3af; font-size: 13px;">' + (data || '—') + '</span>';
                }
            },
            {
                data: 'options',
                name: 'options',
                orderable: false,
                searchable: false,
                className: 'text-right'
            },
        ]
    });

    // Custom search
    var searchTimer;
    $('#ul-search-input').on('keyup', function() {
        clearTimeout(searchTimer);
        var val = this.value;
        searchTimer = setTimeout(function() {
            oTable.search(val).draw();
        }, 400);
    });

    // Filter tabs
    $('.ul-filter-tab').on('click', function() {
        $('.ul-filter-tab').removeClass('active');
        $(this).addClass('active');
        oTable.ajax.reload();
    });
</script>
@endsection