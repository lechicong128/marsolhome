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
        content: "Quản lý danh sách bất động sản, trạng thái và thông tin liên hệ";
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
        display: grid;
        grid-template-columns: 1.25fr 1fr 1fr auto;
        gap: 12px;
        align-items: center;
    }

    .filter-control {
        position: relative;
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
    }

    .btn-dt-reload:hover {
        color: var(--home-primary);
        border-color: var(--home-primary);
        background: #eff6ff;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 44px !important;
        border: 1px solid var(--home-border) !important;
        border-radius: 12px !important;
        background: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 38px 0 14px !important;
        outline: none !important;
        transition: all .16s ease !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03) !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default .select2-selection--single:focus {
        border-color: var(--home-primary) !important;
        box-shadow: 0 0 0 4px rgba(0, 90, 224, 0.10) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        width: 100% !important;
        padding-left: 0 !important;
        line-height: 42px !important;
        color: var(--home-text) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8 !important;
        font-weight: 500 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
        right: 10px !important;
    }

    .select2-dropdown {
        border: 1px solid var(--home-border) !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14) !important;
    }

    .select2-results__option {
        padding: 9px 12px !important;
        font-size: 13px !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: var(--home-primary) !important;
    }

    #status_tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0 0 16px 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #status_tabs .tab-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid var(--home-border);
        background: #ffffff;
        color: #475569;
        font-size: 12.5px;
        font-weight: 800;
        text-decoration: none !important;
        transition: all .16s ease;
        cursor: pointer;
    }

    #status_tabs .tab-link:hover {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: var(--home-primary);
    }

    #status_tabs .tab-link.active {
        border-color: var(--home-primary);
        background: linear-gradient(135deg, var(--home-primary), #2563eb);
        color: #ffffff;
        box-shadow: 0 10px 18px rgba(0, 90, 224, 0.20);
    }

    #status_tabs .tab-link span {
        min-width: 23px;
        height: 22px;
        padding: 0 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
    }

    #status_tabs .tab-link.active span {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }

    .ul-card {
        padding: 0 !important;
        overflow: hidden !important;
        background: #ffffff !important;
        border: 1px solid #eef2f7 !important;
        border-radius: 18px !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.055) !important;
    }

    #table_manage_home {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

.dataTables_scrollHead #table_manage_home thead th,
#table_manage_home:not(.dataTable) thead th {
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

    #table_manage_home tbody td {
        padding: 13px 14px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155 !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        vertical-align: middle !important;
    }

    #table_manage_home tbody tr {
        transition: background .15s ease;
    }

    #table_manage_home tbody tr:hover {
        background: #f8fafc !important;
    }

    #table_manage_home tbody tr:last-child td {
        border-bottom: none !important;
    }

    /* Expired listings row styling */
    #table_manage_home tbody tr.expired-row td {
        background-color: #fff1f2 !important; /* rose-50 */
    }
    #table_manage_home tbody tr.expired-row:hover td {
        background-color: #ffe4e6 !important; /* rose-100 */
    }

    #table_manage_home tbody td:first-child {
        font-weight: 800 !important;
        color: var(--home-primary-dark) !important;
    }

    /* Fix lỗi hình */
    #table_manage_home img {
        max-width: 72px !important;
        width: 45px !important;
        height: 45px !important;
        object-fit: cover !important;
        border-radius: 12px !important;
        border: 1px solid #e5e7eb !important;
        background: #f8fafc !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }

    #table_manage_home .home-image,
    #table_manage_home .property-image,
    #table_manage_home .thumb,
    #table_manage_home .thumbnail {
        width: 72px !important;
        height: 56px !important;
        object-fit: cover !important;
        border-radius: 12px !important;
        overflow: hidden !important;
    }

    #table_manage_home td:nth-child(2) img {
        margin-right: 10px !important;
        flex-shrink: 0 !important;
    }

    #table_manage_home td:nth-child(2) > div {
        max-width: 420px;
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

    /* Reset the parent li when Bootstrap datatables is used */
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

    /* Target the actual clickable page buttons */
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

    /* Hover styling */
    .dataTables_wrapper .dataTables_paginate li.paginate_button a:hover,
    .dataTables_wrapper .dataTables_paginate a.paginate_button:hover {
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    /* Current / Active page styling */
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

    /* Disabled styling */
    .dataTables_wrapper .dataTables_paginate li.paginate_button.disabled a,
    .dataTables_wrapper .dataTables_paginate a.paginate_button.disabled {
        opacity: .5 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        background: #ffffff !important;
        border-color: var(--home-border) !important;
        color: #94a3b8 !important;
    }

    #table_manage_home tbody td .dropdown .dropdown-toggle {
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

    #table_manage_home tbody td .dropdown .dropdown-toggle:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: var(--home-text) !important;
    }

    #table_manage_home tbody td .dropdown .dropdown-toggle::after {
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

    .status-badge-btn {
        transition: all .16s ease !important;
    }

    .status-badge-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.09) !important;
    }

    .status-dropdown-wrap {
        position: relative;
        display: inline-flex;
        justify-content: center;
    }

    .status-dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 170px;
        z-index: 9999;
        padding: 7px;
        border: 1px solid var(--home-border);
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.16);
    }

    .status-dropdown-menu.hidden {
        display: none !important;
    }

    .home-status-item {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 9px 10px !important;
        border-radius: 10px !important;
        color: #475569 !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        transition: all .14s ease !important;
    }

    .home-status-item:hover {
        background: #eff6ff !important;
        color: var(--home-primary) !important;
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

    .table-fit-wrap {
        width: 100%;
        max-width: 100%;
        overflow-x: visible !important;
    }

    body,
    .content,
    .content-wrapper,
    .ul-card {
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    .dataTables_wrapper,
    .dataTables_scroll,
    .dataTables_scrollHead {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    .dataTables_scrollBody {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: auto !important;
        overflow-y: auto !important;
    }

    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table,
    #table_manage_home {
        width: 100% !important;
        min-width: 1740px !important;
        table-layout: fixed !important;
    }

    .dataTables_scrollHeadInner th,
    #table_manage_home th,
    #table_manage_home td {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
    }

    .dataTables_scrollHeadInner th:nth-child(1), #table_manage_home th:nth-child(1), #table_manage_home td:nth-child(1) { width: 100px !important; }
    .dataTables_scrollHeadInner th:nth-child(2), #table_manage_home th:nth-child(2), #table_manage_home td:nth-child(2) { width: 280px; }
    .dataTables_scrollHeadInner th:nth-child(3), #table_manage_home th:nth-child(3), #table_manage_home td:nth-child(3) { width: 140px !important; }
    .dataTables_scrollHeadInner th:nth-child(4), #table_manage_home th:nth-child(4), #table_manage_home td:nth-child(4) { width: 100px !important; }
    .dataTables_scrollHeadInner th:nth-child(5), #table_manage_home th:nth-child(5), #table_manage_home td:nth-child(5) { width: 120px !important; }
    .dataTables_scrollHeadInner th:nth-child(6), #table_manage_home th:nth-child(6), #table_manage_home td:nth-child(6) { width: 180px !important; }
    .dataTables_scrollHeadInner th:nth-child(7), #table_manage_home th:nth-child(7), #table_manage_home td:nth-child(7) { width: 160px !important; }
    .dataTables_scrollHeadInner th:nth-child(8), #table_manage_home th:nth-child(8), #table_manage_home td:nth-child(8) { width: 90px !important; }
    .dataTables_scrollHeadInner th:nth-child(9), #table_manage_home th:nth-child(9), #table_manage_home td:nth-child(9) { width: 90px !important; }
    .dataTables_scrollHeadInner th:nth-child(10), #table_manage_home th:nth-child(10), #table_manage_home td:nth-child(10) { width: 90px !important; }
    .dataTables_scrollHeadInner th:nth-child(11), #table_manage_home th:nth-child(11), #table_manage_home td:nth-child(11) { width: 150px !important; }
    .dataTables_scrollHeadInner th:nth-child(12), #table_manage_home th:nth-child(12), #table_manage_home td:nth-child(12) { width: 120px !important; }
    .dataTables_scrollHeadInner th:nth-child(13), #table_manage_home th:nth-child(13), #table_manage_home td:nth-child(13) { width: 120px !important; }

    @media screen and (max-width: 991px) {
        .manage-home-filter-grid {
            grid-template-columns: 1fr 1fr;
        }

        .btn-dt-reload {
            width: 100%;
        }
    }

    @media screen and (max-width: 767px) {
        .mod-page-header {
            display: block;
        }

        .mod-page-actions {
            margin-top: 14px;
        }

        .mod-btn.mod-btn-primary {
            width: 100%;
        }

        .manage-home-filter-grid {
            grid-template-columns: 1fr;
        }

        #status_tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 5px !important;
        }

        #status_tabs .tab-link {
            white-space: nowrap;
        }

        .dt-buttons.btn-group {
            margin-left: 10px;
        }

        #table_manage_home img {
            width: 60px !important;
            height: 48px !important;
        }
    }
    
    /* Fix DataTables scroll sizing row bị hiện dư */
    .dataTables_scrollBody thead tr,
    .dataTables_scrollBody thead th {
        height: 0 !important;
        max-height: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        border-top: 0 !important;
        border-bottom: 0 !important;
        line-height: 0 !important;
        overflow: hidden !important;
        visibility: hidden !important;
    }

    .dataTables_scrollBody thead .dataTables_sizing {
        height: 0 !important;
        max-height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        visibility: hidden !important;
    }

    .dataTables_scrollBody thead {
        height: 0 !important;
        max-height: 0 !important;
        overflow: hidden !important;
    }

    /* Header thật của DataTables vẫn hiện đẹp */
    .dataTables_scrollHead table thead th {
        padding: 13px 14px !important;
        background: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 1px solid var(--home-border) !important;
        font-size: 11px !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: .055em !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
        visibility: visible !important;
        line-height: normal !important;
    }
    .peer:checked ~ .peer-checked\:after\:border-white::after {
            margin-top: -1.9px;
        }
        .peer ~ div::after {
            margin-top: -1.9px;
        }

    /* Search Autocomplete suggestions dropdown styles */
    .suggestion-item {
        transition: all 0.15s ease;
        padding: 10px 16px;
    }
    .suggestion-item.active {
        background-color: #f1f5f9; /* slate-100 */
    }
    .suggestion-item b {
        color: #0f172a; /* slate-900 */
        font-weight: 700;
    }
    .suggestion-text {
        font-size: 14px;
        font-weight: 500;
        color: #334155; /* slate-700 */
    }
    .suggestion-icon {
        font-size: 13px;
        color: #94a3b8; /* slate-400 */
    }
    .suggestion-count {
        font-size: 11px;
        font-weight: 600;
        color: #475569; /* slate-600 */
        background-color: #f1f5f9; /* slate-100 */
        padding: 2px 8px;
        border-radius: 9999px;
    }
    #search-suggestions-dropdown {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE 10+ */
    }
    #search-suggestions-dropdown::-webkit-scrollbar {
        display: none; /* WebKit */
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

<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">{{ $title }}</h1>
    </div>

    <div class="mod-page-actions">
        <a href="admin/manage_home/detail" class="mod-btn mod-btn-primary">
            <i class="fa fa-plus"></i>
            <span>Tạo mới</span>
        </a>
    </div>
</div>

<div class="manage-home-filter-card">
    <div class="manage-home-filter-grid">
        <div class="filter-control">
            <span class="filter-icon">
                <i class="fa fa-search"></i>
            </span>
            <input
                type="text"
                id="ul-search-input"
                placeholder="Tìm theo tên, mã BĐS..."
                class="home-input"
                autocomplete="off"
            >
            <!-- Search autocomplete dropdown -->
            <div id="search-suggestions-dropdown" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl z-[999] max-h-[300px] overflow-y-auto"></div>
        </div>

        <div class="filter-control">
            <select
                name="customer_search"
                id="customer_search"
                data-placeholder="Tìm khách hàng"
            >
                <option value="">-- Chọn khách hàng --</option>
            </select>
        </div>

        <div class="filter-control">
            <select
                name="property_type_search"
                id="property_type_search"
                data-placeholder="Tìm loại hình BĐS"
            >
                <option value="">-- Chọn loại hình BĐS --</option>
            </select>
        </div>

        <div>
            <button type="button" class="btn-dt-reload" title="Tải lại dữ liệu">
                <i class="fa fa-refresh"></i>
            </button>
        </div>
    </div>
</div>

<ul id="status_tabs">
    <li class="status-tab" data-id="0">
        <a class="tab-link active">
            Tất cả
            <span class="count_all">0</span>
        </a>
    </li>

    <?php foreach(getListStatusHome() as $key => $value) { ?>
        <li class="status-tab" data-id="<?= $value['id'] ?>">
            <a class="tab-link">
                <?= $value['name'] ?>
                <span class="count_status_<?= $value['id'] ?>">0</span>
            </a>
        </li>
    <?php } ?>
</ul>

<input type="hidden" name="status_search" id="status_search" value="0">

<div class="ul-card" style="margin-top:5px">
    <div class="table-fit-wrap">
        <table id="table_manage_home" class="table">
            <thead>
                <tr>
                    <th>Mã BĐS</th>
                    <th>Bất động sản</th>
                    <th>Giao dịch/Loại nhà</th>
                    <th>Diện tích</th>
                    <th>Giá trị</th>
                    <th>Người đăng</th>
                    <th>Liên hệ</th>
                    <th>Nổi bật</th>
                    <th>Vip</th>
                    <th>Mới</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@endsection

@section('script')
<script>
    var oTable;
    var searchTimer;
    var activeSuggestionFilters = {
        ward_id: null,
        is_new_address: null,
        type: null,
        to_price: null,
        suggestion_text: '',
        suggestion_street: ''
    };

    var fnserverparams = {
        'customer_search': '#customer_search',
        'property_type_search': '#property_type_search',
        'status_search': '#status_search'
    };

    $(function () {
        searchAjaxSelect2('#customer_search', 'admin/category/searchCustomer');
        searchAjaxSelect2('#property_type_search', 'admin/category/searchPropertyType');

        if (typeof search_daterangepicker === 'function') {
            search_daterangepicker('date_search');
        }

        $('.status-tab').on('click', function () {
            var $this = $(this);

            $('#status_tabs .tab-link').removeClass('active');
            $this.find('.tab-link').addClass('active');

            $('#status_search').val($this.data('id')).trigger('change');
        });

        oTable = InitDataTableNew('#table_manage_home', 'admin/manage_home/getList', {
            order: [
                [11, 'desc']
            ],
            responsive: false,
            scrollX: true,
            ajax: {
                type: 'POST',
                url: 'admin/manage_home/getList',
                data: function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }

                    // Append active suggestion filters if they exist
                    if (activeSuggestionFilters.ward_id) {
                        d['suggestion_ward_id'] = activeSuggestionFilters.ward_id;
                        d['suggestion_is_new_address'] = activeSuggestionFilters.is_new_address;
                    }
                    if (activeSuggestionFilters.type) {
                        d['suggestion_type'] = activeSuggestionFilters.type;
                    }
                    if (activeSuggestionFilters.to_price) {
                        d['suggestion_to_price'] = activeSuggestionFilters.to_price;
                    }
                    if (activeSuggestionFilters.suggestion_text) {
                        d['suggestion_text'] = activeSuggestionFilters.suggestion_text;
                    }
                    if (activeSuggestionFilters.suggestion_street) {
                        d['suggestion_street'] = activeSuggestionFilters.suggestion_street;
                    }

                    d['_locale'] = '{{ session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang) }}';
                },
                dataSrc: function (json) {
                    return json.data;
                }
            },
            columnDefs: [
                {
                    data: 'code',
                    name: 'code',
                    width: '90px'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    render: function (data) {
                        return '<div class="text-center">' + (data || '') + '</div>';
                    },
                    data: 'type',
                    name: 'type'
                },
                {
                    render: function (data) {
                        return '<div class="text-center">' + (data || '') + '</div>';
                    },
                    data: 'area',
                    name: 'area'
                },
                {
                    render: function (data) {
                        return '<div class="text-right">' + (data || '') + '</div>';
                    },
                    data: 'price',
                    name: 'price',
                    width: '180px'
                },
                {
                    render: function (data) {
                        return '<div class="text-left">' + (data || '') + '</div>';
                    },
                    data: 'poster',
                    name: 'poster',
                    width: '150px'
                },
                {
                    render: function (data) {
                        return '<div class="text-left">' + (data || '') + '</div>';
                    },
                    data: 'customer',
                    name: 'customer',
                    width: '150px'
                },
                {
                    data: 'is_featured',
                    name: 'is_featured',
                    orderable: false,
                    searchable: false,
                           width: '90px'
                },
                {
                    data: 'is_vip',
                    name: 'is_vip',
                    orderable: false,
                    searchable: false,
                           width: '90px'
                },
                {
                    data: 'is_new',
                    name: 'is_new',
                    orderable: false,
                    searchable: false,
                    width: '90px'
                },
                {
                    render: function (data) {
                        return '<div class="text-center">' + (data || '') + '</div>';
                    },
                    data: 'status',
                    name: 'status',
                    width: '150px'
                },
                {
                    render: function (data) {
                        return '<div class="text-center">' + (data || '') + '</div>';
                    },
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'options',
                    name: 'options',
                    orderable: false,
                    searchable: false,
                    width: '150px'
                }
            ]
        });

        $.each(fnserverparams, function (filterIndex, filterItem) {
            $(filterItem).on('change', function () {
                oTable.draw('page');
            });
        });

        $('#table_manage_home').on('draw.dt', function () {
            countAll();
        });

        oTable.on('init', function () {
            updateTableHeight();

            setTimeout(function () {
                oTable.columns.adjust();
            }, 100);
        });

        $(window).on('resize', function () {
            updateTableHeight();

            if (oTable) {
                oTable.columns.adjust();
            }
        });

        var activeSuggestionIndex = -1;
        var suggestionsData = [];
        var lastSearchValue = '';

        function renderSuggestions(data) {
            var $dropdown = $('#search-suggestions-dropdown');
            $dropdown.empty();
            suggestionsData = data;
            activeSuggestionIndex = -1;

            if (!data || data.length === 0) {
                $dropdown.addClass('hidden');
                return;
            }

            var html = '';
            $.each(data, function (index, item) {
                var countText = item.listing_count > 0 ? (item.listing_count + ' tin') : '';
                
                html += '<div class="suggestion-item hover:bg-slate-50 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-100 last:border-b-0" data-index="' + index + '" data-suggestion="' + item.suggestion + '">';
                html += '  <div class="flex items-center gap-2.5 min-w-0">';
                html += '    <i class="fa fa-search suggestion-icon flex-shrink-0"></i>';
                html += '    <span class="suggestion-text truncate">' + item.highlightText + '</span>';
                html += '  </div>';
                if (countText) {
                    html += '  <span class="suggestion-count flex-shrink-0">' + countText + '</span>';
                }
                html += '</div>';
            });

            $dropdown.html(html).removeClass('hidden');
        }

        function fetchSuggestions(query) {
            if (!query || query.trim().length < 2) {
                renderSuggestions([]);
                return;
            }

            $.ajax({
                url: 'admin/manage_home/getSearchSuggestions',
                type: 'GET',
                dataType: 'json',
                data: { q: query },
                success: function (response) {
                    if (response && response.data) {
                        renderSuggestions(response.data);
                    } else {
                        renderSuggestions([]);
                    }
                },
                error: function () {
                    renderSuggestions([]);
                }
            });
        }

        // Close dropdown when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#ul-search-input, #search-suggestions-dropdown').length) {
                $('#search-suggestions-dropdown').addClass('hidden');
            }
        });

        // Click or focus to trigger dropdown
        $('#ul-search-input').on('focus click', function () {
            var val = this.value;
            if (val && val.trim().length >= 2) {
                if ($('#search-suggestions-dropdown').children().length > 0) {
                    $('#search-suggestions-dropdown').removeClass('hidden');
                } else {
                    fetchSuggestions(val);
                }
            }
        });

        function selectSuggestion(item) {
            var suggestion = item.suggestion;
            $('#ul-search-input').val(suggestion);
            lastSearchValue = suggestion;

            // Set suggestion filters
            activeSuggestionFilters.ward_id = item.ward_id;
            activeSuggestionFilters.is_new_address = item.is_new_address;
            activeSuggestionFilters.type = item.type;
            activeSuggestionFilters.to_price = item.to_price;
            activeSuggestionFilters.suggestion_text = suggestion;

            // Extract street name if present
            var streetMatch = suggestion.match(/Đường\s+([^,]+)/);
            if (streetMatch && streetMatch[1]) {
                activeSuggestionFilters.suggestion_street = streetMatch[1].trim();
            } else {
                activeSuggestionFilters.suggestion_street = '';
            }

            $('#search-suggestions-dropdown').addClass('hidden');
            oTable.search('').draw(); // Clear DataTable global text search and draw using suggestion filters
        }

        // Click/mousedown suggestion item
        $(document).on('mousedown click', '#search-suggestions-dropdown .suggestion-item', function (e) {
            e.preventDefault();
            var index = $(this).data('index');
            var item = suggestionsData[index];
            if (item) {
                selectSuggestion(item);
            }
        });

        // Keyboard navigation
        $('#ul-search-input').on('keydown', function (e) {
            var $dropdown = $('#search-suggestions-dropdown');
            var $items = $dropdown.find('.suggestion-item');

            if ($dropdown.hasClass('hidden') || $items.length === 0) {
                if (e.key === 'Enter') {
                    clearTimeout(searchTimer);
                    // Clear suggestion filters on manual search
                    activeSuggestionFilters = { ward_id: null, is_new_address: null, type: null, to_price: null, suggestion_text: '', suggestion_street: '' };
                    oTable.search(this.value).draw();
                }
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeSuggestionIndex++;
                if (activeSuggestionIndex >= $items.length) {
                    activeSuggestionIndex = 0;
                }
                $items.removeClass('active');
                $($items[activeSuggestionIndex]).addClass('active');
                
                var activeItem = $items[activeSuggestionIndex];
                if (activeItem) {
                    activeItem.scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeSuggestionIndex--;
                if (activeSuggestionIndex < 0) {
                    activeSuggestionIndex = $items.length - 1;
                }
                $items.removeClass('active');
                $($items[activeSuggestionIndex]).addClass('active');

                var activeItem = $items[activeSuggestionIndex];
                if (activeItem) {
                    activeItem.scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSuggestionIndex >= 0 && activeSuggestionIndex < $items.length) {
                    var item = suggestionsData[activeSuggestionIndex];
                    if (item) {
                        selectSuggestion(item);
                    }
                } else {
                    $dropdown.addClass('hidden');
                    activeSuggestionFilters = { ward_id: null, is_new_address: null, type: null, to_price: null, suggestion_text: '', suggestion_street: '' };
                    oTable.search(this.value).draw();
                }
            } else if (e.key === 'Escape') {
                $dropdown.addClass('hidden');
            }
        });

        // Keyup input changes
        $('#ul-search-input').on('keyup', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === 'Escape') {
                return;
            }

            var val = this.value;
            if (val === lastSearchValue) {
                return;
            }
            lastSearchValue = val;

            clearTimeout(searchTimer);
            fetchSuggestions(val);

            searchTimer = setTimeout(function () {
                // Clear suggestion filters on manual search
                activeSuggestionFilters = { ward_id: null, is_new_address: null, type: null, to_price: null, suggestion_text: '', suggestion_street: '' };
                oTable.search(val).draw();
            }, 400);
        });

        $('.btn-dt-reload').on('click', function () {
            if (oTable) {
                oTable.draw('page');
            }
        });

        $('#table_manage_home').on('shown.bs.dropdown', '.dropdown', function () {
            var dropdown = $(this);
            var rect = this.getBoundingClientRect();

            if (window.innerHeight - rect.bottom < 250) {
                dropdown.addClass('dropup');
            } else {
                dropdown.removeClass('dropup');
            }

            if (window.innerWidth - rect.right < 250) {
                dropdown.addClass('dropleft');
            } else {
                dropdown.removeClass('dropleft');
            }
        });
        $(document).on('click', '.home-status-item', function (e) {
            e.preventDefault();

            var $a = $(this);
            var homeId = $a.data('id');
            var status = $a.data('status');
            var $wrap = $a.closest('.status-dropdown-wrap');
            var $btn = $wrap.find('.status-badge-btn');

            $wrap.find('.status-dropdown-menu').addClass('hidden');

            $.ajax({
                url: 'admin/manage_home/changeStatus',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: homeId,
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    if (res.success) {
                        alert_float('success', res.message);

                        $btn
                            .attr('data-current', res.status)
                            .removeClass()
                            .addClass(
                                'status-badge-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-semibold border rounded-full cursor-pointer shadow-sm hover:shadow transition-all ' +
                                res.badge_class
                            )
                            .html(
                                '<span class="w-1.5 h-1.5 rounded-full" style="background-color: ' + res.color + '"></span>' +
                                '<span>' + res.status_name + '</span>' +
                                '<i class="fa fa-chevron-down text-[8px] ml-0.5 opacity-60"></i>'
                            );

                        $wrap
                            .find('.home-status-item')
                            .removeClass('font-bold bg-slate-50 text-slate-900')
                            .addClass('text-slate-600');

                        $wrap.find('.home-status-item').find('.fa-check').remove();

                        var $activeItem = $wrap.find('.home-status-item[data-status="' + res.status + '"]');

                        $activeItem
                            .addClass('font-bold bg-slate-50 text-slate-900')
                            .removeClass('text-slate-600')
                            .append('<i class="fa fa-check text-[10px] ml-auto text-slate-500"></i>');

                        countAll();
                    } else {
                        alert_float('error', res.message);
                    }
                },
                error: function () {
                    alert_float('error', 'Có lỗi xảy ra, vui lòng thử lại!');
                }
            });
        });
        $(document).on('change', '.toggle-featured-checkbox', function () {
            var id = $(this).data('id');
            var is_checked = $(this).is(':checked') ? 1 : 0;
            var checkbox = $(this);

            $.ajax({
                url: 'admin/manage_home/changeFeatured/' + id,
                type: 'POST',
                data: {
                    is_featured: is_checked,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'JSON',
            })
            .done(function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                    checkbox.prop('checked', !is_checked);
                }
                oTable.draw('page');
            })
            .fail(function () {
                alert_float('error', 'Có lỗi xảy ra');
                checkbox.prop('checked', !is_checked);
            });
        });

        $(document).on('click', '.status-badge-btn', function (e) {
            e.stopPropagation();

            var $wrap = $(this).closest('.status-dropdown-wrap');

            $('.status-dropdown-menu')
                .not($wrap.find('.status-dropdown-menu'))
                .addClass('hidden');

            $wrap.find('.status-dropdown-menu').toggleClass('hidden');
        });

        $(document).on('click', function () {
            $('.status-dropdown-menu').addClass('hidden');
        });
    });

    function countAll() {
        var data = {
            customer_search: $('#customer_search').val(),
            property_type_search: $('#property_type_search').val()
        };

        $.post('admin/manage_home/countAll', data, function (response) {
            if (response.success) {
                if (response.arrType && response.arrType.length > 0) {
                    $.each(response.arrType, function (index, value) {
                        $('.count_status_' + value.id).text(formatNumber(value.total));
                    });
                }

                $('.count_all').text(formatNumber(response.all));
            }
        });
    }

    function updateTableHeight() {
        if (!oTable) {
            return;
        }

        var h = getTableHeight();
        $(oTable.table().container())
            .find('.dataTables_scrollBody')
            .css({
                height: h,
                maxHeight: h,
                overflowX: 'auto',
                overflowY: 'auto'
            });
    }

    function getTableHeight() {
        var windowHeight = $(window).height();

        var header = $('.mod-page-header').outerHeight(true) || 0;
        var filter = $('.manage-home-filter-card').outerHeight(true) || 0;
        var tabs = $('#status_tabs').outerHeight(true) || 0;

        var dataTablesExtras = 220;
        var bottomPadding = 24;

        var totalUsed = header + filter + tabs + dataTablesExtras + bottomPadding;
        var height = windowHeight - totalUsed;

        if (height < 260) {
            height = 260;
        }

        return height + 'px';
    }

    function changeStatus(id, status) {
        $.ajax({
            url: 'admin/manage_home/changeStatus/' + id,
            type: 'POST',
            dataType: 'JSON',
            cache: false,
            data: {
                status: status,
                _token: '{{ csrf_token() }}'
            }
        })
        .done(function (data) {
            if (data.result) {
                alert_float('success', data.message);
            } else {
                alert_float('error', data.message);
            }

            oTable.draw('page');
        });

        return false;
    }
</script>
@endsection
