@extends('admin.layouts.index')
@section('page_title', $title)
@section('content')
<style>
    :root {
        --blog-primary: #005ae0;
        --blog-primary-dark: #004ec4;
        --blog-bg: #f8fafc;
        --blog-card: #ffffff;
        --blog-border: #e2e8f0;
        --blog-text: #0f172a;
        --blog-muted: #64748b;
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
        color: var(--blog-text) !important;
        letter-spacing: -0.02em;
    }

    .mod-page-header-left:after {
        content: "Quản lý danh sách tin tức, bài viết, trạng thái hiển thị và nổi bật";
        display: block;
        margin-top: 5px;
        font-size: 13px;
        font-weight: 500;
        color: var(--blog-muted);
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
        background: linear-gradient(135deg, var(--blog-primary), #2563eb) !important;
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

    .ul-card {
        padding: 0 !important;
        overflow: hidden !important;
        background: #ffffff !important;
        border: 1px solid #eef2f7 !important;
        border-radius: 18px !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.055) !important;
    }

    #table_blog {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .dataTables_scrollHead #table_blog thead th,
    #table_blog:not(.dataTable) thead th {
        padding: 13px 14px !important;
        background: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 1px solid var(--blog-border) !important;
        border-top: none !important;
        font-size: 11px !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: .055em !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    #table_blog tbody td {
        padding: 13px 14px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155 !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        vertical-align: middle !important;
    }

    #table_blog tbody tr {
        transition: background .15s ease;
    }

    #table_blog tbody tr:hover {
        background: #f8fafc !important;
    }

    #table_blog tbody tr:last-child td {
        border-bottom: none !important;
    }

    /* Fix image styling */
    #table_blog img {
        width: 60px !important;
        height: 45px !important;
        object-fit: cover !important;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 14px 16px !important;
        color: var(--blog-muted) !important;
        font-size: 13px !important;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        height: 34px !important;
        border: 1px solid var(--blog-border) !important;
        border-radius: 10px !important;
        outline: none !important;
        padding: 0 10px !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding: 15px 16px !important;
        color: var(--blog-muted) !important;
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
        border: 1px solid var(--blog-border) !important;
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
        background: var(--blog-primary) !important;
        border-color: var(--blog-primary) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 6px rgba(30, 64, 175, 0.15) !important;
    }

    .dataTables_wrapper .dataTables_paginate li.paginate_button.disabled a,
    .dataTables_wrapper .dataTables_paginate a.paginate_button.disabled {
        opacity: .5 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        background: #ffffff !important;
        border-color: var(--blog-border) !important;
        color: #94a3b8 !important;
    }

    #table_blog tbody td .dropdown .dropdown-toggle {
        height: 34px !important;
        padding: 0 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        border: 1px solid var(--blog-border) !important;
        border-radius: 10px !important;
        background: #ffffff !important;
        color: #475569 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
        transition: all .16s ease !important;
    }

    #table_blog tbody td .dropdown .dropdown-toggle:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: var(--blog-text) !important;
    }

    #table_blog tbody td .dropdown .dropdown-toggle::after {
        display: none !important;
    }

    .dropdown-menu-right {
        right: 0 !important;
        left: auto !important;
    }

    .dropdown-menu.dropdown-menu-right {
        min-width: 170px !important;
        padding: 7px !important;
        border: 1px solid var(--blog-border) !important;
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
        color: var(--blog-primary) !important;
    }

    .dropdown-menu.dropdown-menu-right li a i {
        width: 16px;
        text-align: center;
        font-size: 14px;
    }

    .popover {
        border: 1px solid var(--blog-border) !important;
        border-radius: 14px !important;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.16) !important;
        padding: 8px !important;
        z-index: 999999 !important;
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
        border-color: var(--blog-border) !important;
        color: #475569 !important;
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
    }

    .blog-filter-card {
        padding: 16px;
        margin-bottom: 16px;
        background: var(--blog-card);
        border: 1px solid #eef2f7;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .blog-filter-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr auto;
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

    .blog-input {
        width: 100%;
        height: 44px;
        padding: 0 14px 0 40px;
        border: 1px solid var(--blog-border);
        border-radius: 12px;
        background: #ffffff;
        color: var(--blog-text);
        font-size: 13px;
        font-weight: 600;
        outline: none;
        transition: all .16s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .blog-input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .blog-input:focus {
        border-color: var(--blog-primary);
        box-shadow: 0 0 0 4px rgba(0, 90, 224, 0.10);
    }

    .btn-dt-reload {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: 1px solid var(--blog-border);
        background: #ffffff;
        color: var(--blog-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .16s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        cursor: pointer;
    }

    .btn-dt-reload:hover {
        color: var(--blog-primary);
        border-color: var(--blog-primary);
        background: #eff6ff;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 44px !important;
        border: 1px solid var(--blog-border) !important;
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
        border-color: var(--blog-primary) !important;
        box-shadow: 0 0 0 4px rgba(0, 90, 224, 0.10) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        width: 100% !important;
        padding-left: 0 !important;
        line-height: 42px !important;
        color: var(--blog-text) !important;
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
        border: 1px solid var(--blog-border) !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14) !important;
    }

    @media screen and (max-width: 640px) {
        .blog-filter-grid {
            grid-template-columns: 1fr;
        }
        .btn-dt-reload {
            width: 100%;
        }
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
        <h1 class="mod-page-title text-capitalize">{{ $title }}</h1>
    </div>
    <div class="mod-page-actions">
        <a class="mod-btn mod-btn-primary" href="admin/blog/detail">
            <i class="fa fa-plus"></i> {{lang('dt_create')}}
        </a>
    </div>
</div>

<div class="blog-filter-card">
    <div class="blog-filter-grid">
        <div class="filter-control">
            <span class="filter-icon">
                <i class="fa fa-search"></i>
            </span>
            <input
                type="text"
                id="filter_search_title"
                placeholder="Tìm theo tiêu đề, mô tả ngắn..."
                class="blog-input"
            >
        </div>

        <div class="filter-control">
            <select
                name="filter_type_blog"
                id="filter_type_blog"
                class="select2"
                data-placeholder="Chọn loại bài viết"
            >
                <option value="0">Tất cả</option>
                @foreach($blogCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <button type="button" class="btn-dt-reload" title="Tải lại dữ liệu">
                <i class="fa fa-refresh"></i>
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="ul-card table-responsive p-4 bg-white">
        <table id="table_blog" class="table table-bordered table_blog">
            <thead>
                <tr>
                    <th class="text-center">{{lang('dt_stt')}}</th>
                    <th class="text-center">{{lang('dt_image')}}</th>
                    <th class="text-center">{{lang('dt_title')}}</th>
                    <th class="text-center">{{lang('dt_descption')}}</th>
                    <th class="text-center">Loại bài viết</th>
                    <th class="text-center">Nổi bật</th>
                    <th class="text-center">{{lang('dt_status')}}</th>
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
        var fnserverparams = {
            'type_blog': '#filter_type_blog',
            'search_title': '#filter_search_title'
        };
        var oTable;
        oTable = InitDataTable('#table_blog', 'admin/blog/getBlog', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/blog/getBlog",
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
                {   "render": function (data, type, row) {
                        return `<div class="text-center">${row['DT_RowIndex']}</div>`;
                    },
                    data: 'id', name: 'id', width: "60px", orderable: true
                },
                {data: 'image', name: 'image', width: "100px"},
                {data: 'title', name: 'title', width: "200px"},
                {data: 'descption', name: 'descption'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'type_blog', name: 'type_blog', width: "120px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'hot', name: 'hot', width: "80px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'active', name: 'active', width: "80px"
                },
                {data: 'options', name: 'options', orderable: false, searchable: false, width: "120px" },
            ]
        });
        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        $('#filter_type_blog').select2({
            allowClear: true,
            placeholder: "-- Chọn loại bài viết --",
            width: '100%'
        });

        $('#filter_search_title').on('keyup', function() {
            oTable.draw('page');
        });

        $('.btn-dt-reload').on('click', function () {
            $('#filter_search_title').val('');
            $('#filter_type_blog').val('').trigger('change');
        });
    </script>
@endsection
