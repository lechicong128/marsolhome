<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">{{lang('dt_content_report_comment')}}</h1>
    </div>
    <div class="mod-page-actions">
        <a href="admin/content_report_comment/detail" class="mod-btn mod-btn-primary dt-modal">
            <i class="fa fa-plus"></i> {{ lang('dt_add_content_report_comment') }}
        </a>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="ul-toolbar">
    <div class="ul-search">
        <i class="fa fa-search"></i>
        <input type="text" id="ul-search-input" placeholder="Tìm theo mã,tên...">
    </div>
    <div class="dt-buttons btn-group"><a class="btn btn-default btn-default-dt-options btn-dt-reload"><span><i class="fa fa-refresh"></i></span></a></div>
</div>

<!-- Table Card -->
<div class="ul-card" style="min-height:unset !important">
    <!-- Table -->
    <div class="table-responsive">
        <table id="table_content_report_comment" class="table">
            <thead>
                <tr>
                    <th class="text-center">{{lang('dt_stt')}}</th>
                    <th class="text-center">{{lang('dt_name_content_report_comment')}}</th>
                    <th class="text-center">{{lang('dt_actions')}}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
