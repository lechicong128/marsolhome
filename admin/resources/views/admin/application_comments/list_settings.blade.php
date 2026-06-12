<style>
    .modal .modal-dialog .modal-content{
        padding:unset;
    }
    .mod-page-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
    }
</style>
<div class="mod-page-header">
    <div class="mod-page-header-left">
        <h1 class="mod-page-title">Mẫu góp ý ứng dụng</h1>
    </div>
    <div class="mod-page-actions">
        <a href="admin/application_comments/detail" class="mod-btn mod-btn-primary dt-modal">
            <i class="fa fa-plus"></i> Thêm mẫu góp ý
        </a>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="ul-toolbar">
    <div class="ul-search">
        <i class="fa fa-search"></i>
        <input type="text" id="ul-search-input" placeholder="Tìm kiếm...">
    </div>
    <div class="dt-buttons btn-group">
        <a class="btn btn-default btn-default-dt-options btn-dt-reload">
            <span><i class="fa fa-refresh"></i></span>
        </a>
    </div>
</div>

<!-- Table Card -->
<div class="ul-card" style="min-height:unset !important">
    <!-- Table -->
    <div class="table-responsive">
        <table id="table_application_comments" class="table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 80px;">STT</th>
                    <th class="text-center">Nội dung mẫu góp ý</th>
                    <th class="text-center" style="width: 150px;">Tác vụ</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
