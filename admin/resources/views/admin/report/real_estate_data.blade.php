@extends('admin.layouts.index')
@section('page_title', $title)
@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false }
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

    .rpt-wrap {
        font-family: 'Inter', sans-serif !important;
    }

    /* ── Header Banner ── */
    .rpt-header {
        background: linear-gradient(135deg, #203da4 0%, #2b50bd 50%, #3e6bd6 100%);
        border-radius: 18px;
        padding: 28px 32px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 8px 32px rgba(32, 61, 164, 0.22);
    }
    .rpt-header-title {
        font-size: 24px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.02em;
        margin: 0;
    }
    .rpt-header-sub {
        font-size: 13px;
        color: rgba(255,255,255,0.7);
        margin-top: 4px;
        font-weight: 500;
    }
    .rpt-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .rpt-btn-export {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0 18px;
        height: 40px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1.5px solid rgba(255,255,255,0.25);
        background: rgba(255,255,255,0.1);
        color: #ffffff;
        backdrop-filter: blur(4px);
    }
    .rpt-btn-export:hover {
        background: rgba(255,255,255,0.2);
        border-color: rgba(255,255,255,0.5);
        transform: translateY(-1px);
    }
    .rpt-btn-export.excel { border-color: #34d399; color: #a7f3d0; }
    .rpt-btn-export.excel:hover { background: rgba(52, 211, 153, 0.15); }
    .rpt-btn-export.pdf { border-color: #fb7185; color: #fda4af; }
    .rpt-btn-export.pdf:hover { background: rgba(251, 113, 133, 0.15); }
    .rpt-btn-export.print { background: #ffffff; color: #1e3a8a; border-color: #ffffff; font-weight: 800; }
    .rpt-btn-export.print:hover { background: #e0e7ff; }

    /* ── Filter Bar ── */
    .rpt-filter-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 16px 20px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        margin-bottom: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .rpt-filter-item {
        flex: 1;
        min-width: 180px;
    }
    .rpt-filter-item select,
    .rpt-filter-item input {
        width: 100%;
        height: 42px;
        padding: 0 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        background: #f9fafb;
        outline: none;
        transition: all 0.15s ease;
    }
    .rpt-filter-item select:focus,
    .rpt-filter-item input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        background: #ffffff;
    }
    .rpt-filter-item .date-wrapper {
        position: relative;
    }
    .rpt-filter-item .date-wrapper i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 14px;
        pointer-events: none;
    }
    .rpt-filter-item .date-wrapper input {
        padding-left: 38px;
    }
    .rpt-filter-btns {
        display: flex;
        gap: 8px;
    }
    .rpt-btn-filter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 42px;
        padding: 0 22px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
    }
    .rpt-btn-filter.primary {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    .rpt-btn-filter.primary:hover {
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        transform: translateY(-1px);
    }
    .rpt-btn-filter.secondary {
        background: #ffffff;
        color: #6b7280;
        border: 1.5px solid #e5e7eb;
    }
    .rpt-btn-filter.secondary:hover {
        background: #f3f4f6;
        color: #374151;
    }

    /* ── KPI Cards ── */
    .rpt-kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }
    @media (max-width: 1200px) {
        .rpt-kpi-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .rpt-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    .rpt-kpi-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 18px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .rpt-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .rpt-kpi-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .rpt-kpi-icon.rpt-blue { background: #eff6ff; color: #3b82f6; }
    .rpt-kpi-icon.rpt-green { background: #f0fdf4; color: #10b981; }
    .rpt-kpi-icon.rpt-orange { background: #fff7ed; color: #f97316; }
    .rpt-kpi-icon.rpt-purple { background: #faf5ff; color: #8b5cf6; }
    .rpt-kpi-icon.rpt-teal { background: #f0fdfa; color: #14b8a6; }
    .rpt-kpi-icon.rpt-indigo { background: #eef2ff; color: #6366f1; }
    .rpt-kpi-label {
        font-size: 11px;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 2px;
    }
    .rpt-kpi-value {
        font-size: 22px;
        font-weight: 900;
        letter-spacing: -0.02em;
        line-height: 1.1;
    }
    .rpt-kpi-pct {
        font-size: 12px;
        font-weight: 700;
        margin-top: 2px;
    }

    /* ── Data Table Card ── */
    .rpt-table-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    }
    #realEstateTable {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
    }
    #realEstateTable thead th {
        background: linear-gradient(135deg, #1e3f9d, #1d4ed8) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        padding: 14px 10px !important;
        border: none !important;
        border-right: 1px solid rgba(255,255,255,0.1) !important;
        text-align: center !important;
        vertical-align: middle !important;
        white-space: nowrap;
    }
    #realEstateTable thead th:last-child {
        border-right: none !important;
    }
    #realEstateTable tbody td {
        padding: 13px 10px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        border-right: 1px solid #f8fafc !important;
        font-size: 14px !important;
        color: #374151 !important;
        vertical-align: middle !important;
        text-align: center;
        font-weight: 500;
    }
    #realEstateTable tbody td:last-child {
        border-right: none !important;
    }
    #realEstateTable tbody tr {
        transition: background 0.15s ease;
    }
    #realEstateTable tbody tr:hover td {
        background-color: #eff6ff !important;
    }
    #realEstateTable tbody tr:nth-child(even) td {
        background-color: #fafbfc;
    }
    #realEstateTable tbody tr:nth-child(even):hover td {
        background-color: #eff6ff !important;
    }
    #realEstateTable tfoot td {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%) !important;
        font-weight: 800 !important;
        color: #1e3a8a !important;
        padding: 14px 10px !important;
        border-top: 2px solid #93c5fd !important;
        font-size: 14px !important;
        text-align: center;
    }

    /* Progress bar inside table */
    .progress-cell {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
        min-width: 120px;
    }
    .progress-bar-track {
        flex: 1;
        height: 6px;
        background: #e5e7eb;
        border-radius: 99px;
        overflow: hidden;
        max-width: 70px;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.6s ease;
    }
    .progress-bar-fill.high { background: linear-gradient(90deg, #10b981, #34d399); }
    .progress-bar-fill.mid { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .progress-bar-fill.low { background: linear-gradient(90deg, #ef4444, #f87171); }
    .progress-pct {
        font-size: 12px;
        font-weight: 800;
        min-width: 38px;
        text-align: right;
    }
    .progress-pct.high { color: #059669; }
    .progress-pct.mid { color: #d97706; }
    .progress-pct.low { color: #dc2626; }

    /* Datatable overrides for this page */
    .rpt-table-card .dataTables_wrapper .dataTables_length,
    .rpt-table-card .dataTables_wrapper .dataTables_filter {
        display: none !important;
    }
    .rpt-table-card .dataTables_wrapper .dataTables_info {
        display: none !important;
    }
    .rpt-table-card .dataTables_wrapper .dataTables_paginate {
        display: none !important;
    }

    /* ── Footer Info Cards ── */
    .rpt-footer-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 10px;
    }
    @media (max-width: 992px) {
        .rpt-footer-grid { grid-template-columns: 1fr; }
    }
    .rpt-info-card {
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .rpt-info-card.warning {
        background: #fef2f2;
        border: 1px solid #fecaca;
    }
    .rpt-info-card.info {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
    }
    .rpt-info-card.note {
        background: #fffbeb;
        border: 1px solid #fde68a;
    }
    .rpt-info-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }
    .rpt-info-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .rpt-info-icon.warning { background: #fee2e2; color: #dc2626; }
    .rpt-info-icon.info { background: #dbeafe; color: #2563eb; }
    .rpt-info-icon.note { background: #fef3c7; color: #d97706; }
    .rpt-info-title {
        font-size: 13px;
        font-weight: 800;
    }
    .rpt-info-title.warning { color: #991b1b; }
    .rpt-info-title.info { color: #1e3a8a; }
    .rpt-info-title.note { color: #92400e; }
    .rpt-info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .rpt-info-list li {
        font-size: 12.5px;
        font-weight: 600;
        padding: 5px 0;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        line-height: 1.5;
    }
    .rpt-info-list.warning li { color: #991b1b; }
    .rpt-info-list.info li { color: #1e3a8a; }
    .rpt-info-list.note li { color: #92400e; }
    .rpt-info-list li .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        margin-top: 6px;
        flex-shrink: 0;
    }
    .rpt-info-list.warning li .dot { background: #ef4444; }
    .rpt-info-list.info li .dot { background: #3b82f6; }
    .rpt-info-list.note li .dot { background: #f59e0b; }
    .rpt-info-list li strong {
        font-weight: 800;
    }

    /* Eye button */
    .rpt-btn-eye {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .rpt-btn-eye:hover {
        background: #eff6ff;
        color: #2563eb;
        border-color: #93c5fd;
    }

    /* Select2 overrides */
    .rpt-wrap .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1.5px solid #e5e7eb !important;
        border-radius: 10px !important;
        padding: 6px 12px !important;
        background: #f9fafb !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }
    .rpt-wrap .select2-container--default .select2-selection--single:focus,
    .rpt-wrap .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.12) !important;
    }
    .rpt-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }

    @media print {
        body {
            background: #ffffff !important;
            color: #000000 !important;
        }
        aside,
        nav,
        footer,
        .main-sidebar,
        .main-header,
        .content-header,
        .breadcrumb,
        .rpt-header-actions,
        .rpt-filter-bar,
        .rpt-filter-btns,
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate,
        .btn-view-detail,
        th:last-child,
        td:last-child {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .rpt-wrap {
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        table {
            width: 100% !important;
        }
    }
    .modal .modal-dialog .modal-content{
        padding: unset;
    }
    .modal-content{
        max-width:unset;
    }
</style>

<div class="rpt-wrap">

    <!-- ════════ Header Banner ════════ -->
    <div class="rpt-header">
        <div>
            <h1 class="rpt-header-title">BÁO CÁO NGUỒN DATA BẤT ĐỘNG SẢN</h1>
            <p class="rpt-header-sub">Theo dõi data khách hàng theo 3 nguồn chính và hiệu suất sales</p>
        </div>
        <div class="rpt-header-actions">
            <button class="rpt-btn-export excel" onclick="exportExcel()">
                <i class="fa fa-file-excel-o"></i> Xuất Excel
            </button>
            <button class="rpt-btn-export pdf" onclick="exportPDF()">
                <i class="fa fa-file-pdf-o"></i> Xuất PDF
            </button>
            <button class="rpt-btn-export print" onclick="window.print()">
                <i class="fa fa-print"></i> In báo cáo
            </button>
        </div>
    </div>

    <!-- ════════ Filter Bar ════════ -->
    <form id="filterForm" class="rpt-filter-bar">
        <div class="rpt-filter-item">
            <div class="date-wrapper">
                <i class="fa fa-calendar"></i>
                <input type="text" id="dateFilter" name="date_filter" value="16/05/2024" autocomplete="off">
            </div>
        </div>
        <div class="rpt-filter-item">
            <select id="departmentFilter" name="department_id" class="select2">
                <option value="">Tất cả phòng ban</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="rpt-filter-item">
            <select id="groupFilter" name="group_id" class="select2">
                <option value="">Tất cả nhóm sales</option>
                @foreach($salesGroups as $group)
                    <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="rpt-filter-item">
            <select id="staffFilter" name="staff_id" class="select2">
                <option value="">Tất cả nhân viên</option>
                @foreach($staffList as $staff)
                    <option value="{{ $staff['id'] }}">{{ $staff['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="rpt-filter-btns">
            <button type="submit" class="rpt-btn-filter primary">
                <i class="fa fa-filter"></i> Lọc
            </button>
            <button type="button" class="rpt-btn-filter secondary" onclick="resetFilters()">
                <i class="fa fa-refresh"></i> Làm mới
            </button>
        </div>
    </form>

    <!-- ════════ KPI Cards ════════ -->
    <div class="rpt-kpi-grid">
        <div class="rpt-kpi-card">
            <div class="rpt-kpi-icon rpt-blue"><i class="fa fa-database"></i></div>
            <div>
                <div class="rpt-kpi-label">Tổng data</div>
                <div class="rpt-kpi-value" style="color:#3b82f6" id="kpiTotal">1.248</div>
                <div class="rpt-kpi-pct" style="color:#60a5fa">100%</div>
            </div>
        </div>
        <div class="rpt-kpi-card">
            <div class="rpt-kpi-icon rpt-green"><i class="fa fa-building"></i></div>
            <div>
                <div class="rpt-kpi-label">Nguồn 1 - Công ty đầu tư</div>
                <div class="rpt-kpi-value" style="color:#10b981">420</div>
                <div class="rpt-kpi-pct" style="color:#34d399">33,65%</div>
            </div>
        </div>
        <div class="rpt-kpi-card">
            <div class="rpt-kpi-icon rpt-orange"><i class="fa fa-briefcase"></i></div>
            <div>
                <div class="rpt-kpi-label">Nguồn 2 - Ký gửi độc quyền</div>
                <div class="rpt-kpi-value" style="color:#f97316">368</div>
                <div class="rpt-kpi-pct" style="color:#fb923c">29,49%</div>
            </div>
        </div>
        <div class="rpt-kpi-card">
            <div class="rpt-kpi-icon rpt-purple"><i class="fa fa-user"></i></div>
            <div>
                <div class="rpt-kpi-label">Nguồn 3 - Sales tự khai thác</div>
                <div class="rpt-kpi-value" style="color:#8b5cf6">460</div>
                <div class="rpt-kpi-pct" style="color:#a78bfa">36,86%</div>
            </div>
        </div>
        <div class="rpt-kpi-card">
            <div class="rpt-kpi-icon rpt-teal"><i class="fa fa-check-circle"></i></div>
            <div>
                <div class="rpt-kpi-label">Data hợp lệ</div>
                <div class="rpt-kpi-value" style="color:#14b8a6">1.036</div>
                <div class="rpt-kpi-pct" style="color:#2dd4bf">Tỷ lệ: 83,01%</div>
            </div>
        </div>
        <div class="rpt-kpi-card">
            <div class="rpt-kpi-icon rpt-indigo"><i class="fa fa-user-plus"></i></div>
            <div>
                <div class="rpt-kpi-label">Sales nhập data</div>
                <div class="rpt-kpi-value" style="color:#6366f1">28 / 32</div>
                <div class="rpt-kpi-pct" style="color:#818cf8">87,50%</div>
            </div>
        </div>
    </div>

    <!-- ════════ Main Table ════════ -->
    <div class="rpt-table-card">
        <div class="table-responsive">
            <table id="realEstateTable" class="table">
                <thead>
                    <tr>
                        <th style="width:50px">STT</th>
                        <th style="min-width:140px">Nhân viên</th>
                        <th style="min-width:120px">Phòng ban</th>
                        <th style="width:70px">Chỉ tiêu</th>
                        <th style="width:70px">Đã nhập</th>
                        <th style="min-width:130px">Tiến độ</th>
                        <th style="width:70px">Nguồn 1</th>
                        <th style="width:70px">Nguồn 2</th>
                        <th style="width:70px">Nguồn 3</th>
                        <th style="width:60px">Hợp lệ</th>
                        <th style="width:60px">Trùng</th>
                        <th style="width:70px">Thiếu TT</th>
                        <th style="width:90px">Tỷ lệ hợp lệ</th>
                        <th style="width:60px">Thao tác</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:left;font-weight:900;text-transform:uppercase;letter-spacing:0.03em">TỔNG CỘNG</td>
                        <td id="totalQuota">0</td>
                        <td id="totalEntered">0</td>
                        <td id="totalProgress">0%</td>
                        <td id="totalSource1">0</td>
                        <td id="totalSource2">0</td>
                        <td id="totalSource3">0</td>
                        <td id="totalValid">0</td>
                        <td id="totalDuplicate" style="color:#dc2626">0</td>
                        <td id="totalMissing" style="color:#d97706">0</td>
                        <td id="totalValidRate">0%</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- ════════ Footer Info Cards ════════ -->
    <div class="rpt-footer-grid">

        <!-- Warning -->
        <div class="rpt-info-card warning">
            <div class="rpt-info-header">
                <div class="rpt-info-icon warning"><i class="fa fa-exclamation-triangle"></i></div>
                <div class="rpt-info-title warning">Sales chưa nhập data trong ngày (4)</div>
            </div>
            <ul class="rpt-info-list warning">
                <li><span class="dot"></span> 1. Vũ Văn Kiên – KD Dự án 1</li>
                <li><span class="dot"></span> 2. Đặng Thị Hương – KD Dự án 2</li>
                <li><span class="dot"></span> 3. Lưu Văn Đức – KD Dự án 3</li>
                <li><span class="dot"></span> 4. Trịnh Minh Châu – KD Dự án 3</li>
            </ul>
        </div>

        <!-- Notes -->
        <div class="rpt-info-card note">
            <div class="rpt-info-header">
                <div class="rpt-info-icon note"><i class="fa fa-sticky-note"></i></div>
                <div class="rpt-info-title note">Ghi chú chất lượng data</div>
            </div>
            <ul class="rpt-info-list note">
                <li><span class="dot"></span> <strong>Hợp lệ:</strong> Data đầy đủ thông tin cơ bản (Họ tên, SĐT, Nguồn, Dự án quan tâm...)</li>
                <li><span class="dot"></span> <strong>Trùng:</strong> SĐT đã tồn tại trong hệ thống</li>
                <li><span class="dot"></span> <strong>Thiếu thông tin:</strong> Thiếu 1 hoặc nhiều thông tin bắt buộc</li>
                <li><span class="dot"></span> <strong>Tiến độ</strong> = Đã nhập / Chỉ tiêu × 100%</li>
            </ul>
        </div>

    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="staffDetailModal" tabindex="-1" role="dialog" aria-labelledby="staffDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; padding: 16px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8; font-size: 24px; border: none; background: transparent; float: right; cursor: pointer; padding: 0; outline: none; margin-top: -3px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="staffDetailModalLabel" style="font-weight: 700; margin: 0; color: white;">Chi tiết data nhập - <span id="modalStaffName"></span></h4>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <!-- KPI inside modal -->
                <div class="row" style="margin-bottom: 20px; display: flex; flex-wrap: wrap;">
                    <div class="col-md-3 col-sm-6" style="margin-bottom: 10px; flex: 1; min-width: 120px; padding: 0 10px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; text-align: center;">
                            <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Đã nhập</span>
                            <h3 id="modalKpiEntered" style="margin: 5px 0 0 0; font-weight: 800; color: #4338ca;">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6" style="margin-bottom: 10px; flex: 1; min-width: 120px; padding: 0 10px;">
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 10px; text-align: center;">
                            <span style="font-size: 11px; font-weight: 700; color: #16a34a; text-transform: uppercase;">Hợp lệ</span>
                            <h3 id="modalKpiValid" style="margin: 5px 0 0 0; font-weight: 800; color: #15803d;">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6" style="margin-bottom: 10px; flex: 1; min-width: 120px; padding: 0 10px;">
                        <div style="background: #fff1f2; border: 1px solid #fecdd3; padding: 12px; border-radius: 10px; text-align: center;">
                            <span style="font-size: 11px; font-weight: 700; color: #be123c; text-transform: uppercase;">Trùng lặp</span>
                            <h3 id="modalKpiDuplicate" style="margin: 5px 0 0 0; font-weight: 800; color: #be123c;">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6" style="margin-bottom: 10px; flex: 1; min-width: 120px; padding: 0 10px;">
                        <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 12px; border-radius: 10px; text-align: center;">
                            <span style="font-size: 11px; font-weight: 700; color: #b45309; text-transform: uppercase;">Thiếu TT</span>
                            <h3 id="modalKpiMissing" style="margin: 5px 0 0 0; font-weight: 800; color: #b45309;">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Table inside modal -->
                <div class="table-responsive" style="border-radius: 10px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <table class="table table-striped" style="margin-bottom: 0; width: 100%;">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th style="width: 100px;">Mã BĐS</th>
                                <th style="min-width: 180px;">Tên bất động sản</th>
                                <th style="min-width: 150px;">Địa chỉ</th>
                                <th style="width: 180px;">Nguồn</th>
                                <th style="width: 100px; text-align: center;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody id="modalTableBody">
                            <!-- JS populated -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 20px; text-align: right;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 600; border-radius: 8px;">Đóng</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    var oTable;

    $(document).ready(function() {
        // Select2
        if ($.fn.select2) {
            $('.rpt-wrap .select2').select2({
                minimumResultsForSearch: Infinity,
                width: '100%'
            });
        }

        // Datepicker
        if ($.fn.datepicker) {
            $('#dateFilter').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });
        }

        // DataTable
        oTable = $('#realEstateTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            searching: false,
            paging: false,
            info: false,
            ordering: false,
            ajax: {
                url: 'admin/report/getListRealEstateData',
                type: 'POST',
                data: function (d) {
                    d.department_id = $('#departmentFilter').val();
                    d.group_id = $('#groupFilter').val();
                    d.staff_id = $('#staffFilter').val();
                    d.date_filter = $('#dateFilter').val();
                },
                dataSrc: function (json) {
                    if (json.summary) {
                        $('#totalQuota').text(json.summary.quota);
                        $('#totalEntered').text(json.summary.entered);
                        $('#totalProgress').text(json.summary.progress);
                        $('#totalSource1').text(json.summary.source1);
                        $('#totalSource2').text(json.summary.source2);
                        $('#totalSource3').text(json.summary.source3);
                        $('#totalValid').text(json.summary.valid);
                        $('#totalDuplicate').text(json.summary.duplicate);
                        $('#totalMissing').text(json.summary.missing);
                        $('#totalValidRate').text(json.summary.valid_rate);
                    }
                    return json.data;
                }
            },
            columns: [
                { data: 'stt', name: 'stt' },
                { data: 'staff_name', name: 'staff_name' },
                { data: 'department', name: 'department' },
                { data: 'quota', name: 'quota' },
                { data: 'entered', name: 'entered' },
                { data: 'progress', name: 'progress' },
                { data: 'source1', name: 'source1' },
                { data: 'source2', name: 'source2' },
                { data: 'source3', name: 'source3' },
                { data: 'valid', name: 'valid' },
                { data: 'duplicate', name: 'duplicate' },
                { data: 'missing', name: 'missing' },
                { data: 'valid_rate', name: 'valid_rate' },
                { data: 'action', name: 'action' }
            ],
            language: lang.datatables
        });

        // Filter form
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            oTable.draw();
        });

        // View detail
        $(document).on('click', '.btn-view-detail', function() {
            var tr = $(this).closest('tr');
            var rowData = oTable.row(tr).data();
            if (!rowData) {
                console.log("No rowData found for this row");
                return;
            }

            var rowId = $(this).data('id');

            // Helper to strip HTML tags and get clean text
            function parseHtmlNum(htmlStr) {
                if (!htmlStr) return '';
                if (typeof htmlStr !== 'string') return String(htmlStr);
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlStr;
                return tempDiv.textContent || tempDiv.innerText || '';
            }

            var staffName = parseHtmlNum(rowData.staff_name);
            var enteredText = parseHtmlNum(rowData.entered);
            var validText = parseHtmlNum(rowData.valid);
            var duplicateText = parseHtmlNum(rowData.duplicate);
            var missingText = parseHtmlNum(rowData.missing);

            var entered = parseInt(enteredText) || 0;
            var valid = parseInt(validText) || 0;
            var duplicate = parseInt(duplicateText) || 0;
            var missing = parseInt(missingText) || 0;

            var source1 = parseInt(parseHtmlNum(rowData.source1)) || 0;
            var source2 = parseInt(parseHtmlNum(rowData.source2)) || 0;
            var source3 = parseInt(parseHtmlNum(rowData.source3)) || 0;

            $('#modalStaffName').text(staffName);
            $('#modalKpiEntered').text(entered);
            $('#modalKpiValid').text(valid);
            $('#modalKpiDuplicate').text(duplicate);
            $('#modalKpiMissing').text(missing);

            // Generate mock records for this staff
            var sources = [
                { name: 'Nguồn 1 - Công ty đầu tư', count: source1 },
                { name: 'Nguồn 2 - Ký gửi độc quyền', count: source2 },
                { name: 'Nguồn 3 - Sales tự khai thác', count: source3 }
            ];

            var statuses = [
                { label: 'Hợp lệ', class: 'success', count: valid },
                { label: 'Trùng lặp', class: 'danger', count: duplicate },
                { label: 'Thiếu thông tin', class: 'warning', count: missing }
            ];

            var mockBds = [
                { title: 'Căn hộ Vinhomes Grand Park 2PN', address: 'Quận 9, TP. Thủ Đức' },
                { title: 'Nhà phố thương mại Shophouse', address: 'KĐT Sala, Quận 2' },
                { title: 'Đất nền sổ hồng riêng', address: 'Linh Đông, Thủ Đức' },
                { title: 'Căn hộ chung cư Masteri Centre Point', address: 'Quận 9, TP. Thủ Đức' },
                { title: 'Biệt thự song lập compound', address: 'Phú Mỹ Hưng, Quận 7' },
                { title: 'Nhà hẻm xe hơi Phan Xích Long', address: 'Phú Nhuận, TP. HCM' },
                { title: 'Căn hộ dịch vụ Studio đầy đủ tiện nghi', address: 'Bình Thạnh, TP. HCM' },
                { title: 'Đất thổ cư mặt tiền đường nhựa', address: 'Hóc Môn, TP. HCM' },
                { title: 'Căn hộ chung cư cao cấp Landmark 81', address: 'Vinhomes Central Park' },
                { title: 'Nhà phố 1 trệt 2 lầu đúc', address: 'Gò Vấp, TP. HCM' }
            ];

            var listHtml = '';
            var count = 0;
            
            var currentSourceIndex = 0;
            var currentStatusIndex = 0;
            
            var maxToShow = Math.min(entered, 8);
            for (var i = 0; i < maxToShow; i++) {
                count++;
                
                while (currentSourceIndex < sources.length && sources[currentSourceIndex].count === 0) {
                    currentSourceIndex++;
                }
                var sourceName = 'Khác';
                if (currentSourceIndex < sources.length) {
                    sourceName = sources[currentSourceIndex].name;
                    sources[currentSourceIndex].count--;
                }

                while (currentStatusIndex < statuses.length && statuses[currentStatusIndex].count === 0) {
                    currentStatusIndex++;
                }
                var statusLabel = 'Không xác định';
                var statusClass = 'default';
                if (currentStatusIndex < statuses.length) {
                    statusLabel = statuses[currentStatusIndex].label;
                    statusClass = statuses[currentStatusIndex].class;
                    statuses[currentStatusIndex].count--;
                }

                var bds = mockBds[i % mockBds.length];
                var code = 'BĐS-' + (100000 + rowId * 100 + i);

                listHtml += '<tr>' +
                    '<td style="text-align: center; font-weight: 600;">' + count + '</td>' +
                    '<td style="font-weight: 700; color: #1e3a8a;">' + code + '</td>' +
                    '<td style="font-weight: 600; color: #334155;">' + bds.title + '</td>' +
                    '<td style="color: #64748b;">' + bds.address + '</td>' +
                    '<td><span class="badge" style="background-color: #f1f5f9; color: #475569; font-weight: 600; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 6px;">' + sourceName + '</span></td>' +
                    '<td style="text-align: center;"><span class="label label-' + statusClass + '" style="padding: 4px 8px; font-weight: 700; border-radius: 4px;">' + statusLabel + '</span></td>' +
                    '</tr>';
            }

            if (listHtml === '') {
                listHtml = '<tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px;">Không có dữ liệu chi tiết</td></tr>';
            }

            $('#modalTableBody').html(listHtml);
            $('#staffDetailModal').modal('show');
        });
    });

    function resetFilters() {
        $('#departmentFilter').val('').trigger('change');
        $('#groupFilter').val('').trigger('change');
        $('#staffFilter').val('').trigger('change');
        $('#dateFilter').val('16/05/2024');
        oTable.draw();
    }

    function exportExcel() {
        if (!oTable) return;
        
        var headers = [
            'STT', 'Nhân viên', 'Phòng ban', 'Chỉ tiêu', 'Đã nhập', 
            'Tiến độ', 'Nguồn 1 - Công ty đầu tư', 'Nguồn 2 - Ký gửi độc quyền', 
            'Nguồn 3 - Sales tự khai thác', 'Hợp lệ', 'Trùng lặp', 'Thiếu thông tin', 'Tỷ lệ hợp lệ'
        ];

        var rows = [];
        var data = oTable.rows().data().toArray();
        data.forEach(function(row, idx) {
            var pct = row.quota > 0 ? Math.round((row.entered / row.quota) * 100) : 0;
            var validPct = row.entered > 0 ? Math.round((row.valid / row.entered) * 100) : 0;
            rows.push([
                idx + 1,
                row.staff_name,
                row.department,
                row.quota,
                row.entered,
                pct + '%',
                row.source1,
                row.source2,
                row.source3,
                row.valid,
                row.duplicate,
                row.missing,
                validPct + '%'
            ]);
        });

        if (oTable.ajax.json() && oTable.ajax.json().summary) {
            var sum = oTable.ajax.json().summary;
            rows.push([
                'Tổng cộng',
                '',
                '',
                sum.quota,
                sum.entered,
                sum.progress,
                sum.source1,
                sum.source2,
                sum.source3,
                sum.valid,
                sum.duplicate,
                sum.missing,
                sum.valid_rate
            ]);
        }

        var csvContent = "\uFEFF"; // UTF-8 BOM
        csvContent += headers.map(function(h) { return '"' + h.replace(/"/g, '""') + '"'; }).join(",") + "\r\n";
        
        rows.forEach(function(row) {
            csvContent += row.map(function(val) { 
                var str = (val === null || val === undefined) ? '' : String(val);
                return '"' + str.replace(/"/g, '""') + '"'; 
            }).join(",") + "\r\n";
        });

        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement("a");
        var url = URL.createObjectURL(blob);
        
        var dateFilter = $('#dateFilter').val() || 'Báo cáo';
        var filename = 'Báo cáo nguồn data BĐS - ' + dateFilter.replace(/\//g, '-') + '.csv';
        
        link.setAttribute("href", url);
        link.setAttribute("download", filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function exportPDF() {
        if (typeof alert_float === 'function') {
            alert_float('success', 'Đang khởi tạo bản in PDF. Hãy chọn "Lưu dưới dạng PDF" (Save as PDF) tại mục chọn máy in.');
        }
        setTimeout(function() {
            window.print();
        }, 1000);
    }
</script>
@endsection
