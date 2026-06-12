<!DOCTYPE html>
<html lang="{{\Illuminate\Support\Facades\Lang::locale()}}">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Panel">
    <meta name="author" content="">
     @if(str_contains(request()->getHost(), 'trycloudflare.com'))
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @endif
    <link href="{{get_option('favicon')}}" rel="shortcut icon">
    <title>{{get_option('name_company')}}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ asset('') }}">

    <link href="admin/assets/plugins/bootstrap-tagsinput/css/bootstrap-tagsinput.css" rel="stylesheet"/>
    <link href="admin/assets/plugins/switchery/css/switchery.min.css" rel="stylesheet"/>
    <link href="admin/assets/plugins/multiselect/css/multi-select.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/select2/css4/select2.min.css" rel="stylesheet"/>
    <link href="admin/assets/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet"/>
    <link href="admin/assets/plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css" rel="stylesheet"/>
    <link href="admin/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="admin/assets/plugins/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/dataTables.colVis.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/fixedColumns.dataTables.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/ladda-buttons/css/ladda-themeless.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/ion-rangeslider/ion.rangeSlider.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/ion-rangeslider/ion.rangeSlider.skinFlat.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.3.7/jquery.datetimepicker.min.css"/>
    <link href="admin/assets/plugins/bootstrap-slider/css/bootstrap-slider.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="admin/assets/plugins/morris/morris.css">
    <link href="admin/assets/plugins/dropzone/dropzone.css" rel="stylesheet" type="text/css">
    <link href="admin/assets/plugins/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="admin/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/core.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/style.css?v=1.1" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/call_center.css?v=1.1" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/components.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/icons.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/pages.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/responsive.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="admin/assets/plugins/slick/css/slick.css"/>
    <link rel="stylesheet" type="text/css" href="admin/assets/plugins/slick/css/slick-theme.css"/>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lykmapipo/themify-icons@0.1.2/css/themify-icons.css">
    <!-- Modern Dashboard CSS -->
    <link href="admin/assets/css/dashboard.css?v=1.2" rel="stylesheet" type="text/css"/>
    <script src="admin/assets/js/modernizr.min.js"></script>
</head>
<body class="admin-modern">

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    @include('admin.layouts.sidebar')
</aside>

<!-- Overlay mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Top Header -->
<header class="admin-header" id="adminHeader">
    @include('admin.layouts.header')
</header>

<!-- Main Content -->
<div class="admin-main" id="adminMain">
    <div class="admin-content">
        <!-- Old hidden elements for compatibility -->
        <div style="display:none">
            <div id="loading"><img class="loading_img hide" src="admin/assets/images/loading.png"/><div id="loading-content"></div></div>
        </div>
        <div id="toast-container-new" class="toast-top-right-new"></div>
        <div class="content-call-center" id="draggable-call">@include('admin.layouts.call_center')</div>
        <div id="draggable-driver">
            <div class="card-call-center hide" style="display:flex;justify-content:center">
                <div class="btn_xac_nhan" style="display:flex;align-items:center;color:white;cursor:pointer"></div>
                <div class="driver_id"></div>
            </div>
        </div>

        @yield('content')

        <div class="modal fade" id="dtModal" role="dialog" aria-labelledby="myModalLabel"></div>
        <div class="modal fade" style="z-index:999999999999" id="dtModal2" role="dialog" aria-labelledby="myModalLabel"></div>
        <div id="data_profile"></div>
    </div>
    <footer class="admin-footer">© {{date('Y')}} {{get_option('name_company')}}. All rights reserved.</footer>
</div>

<video id="my-video" style="display:none;" autoplay playsinline muted></video>
<video id="peer-video" style="display:none;" autoplay playsinline></video>
<div id="preview-template" style="display:none">
    <div class="dz-preview dz-file-preview">
        <div class="dz-image"><img data-dz-thumbnail=""></div>
        <div class="dz-details"><div class="dz-size"><span data-dz-size=""></span></div><div class="dz-filename"><span data-dz-name=""></span></div></div>
        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div>
        <div class="dz-error-message"><span data-dz-errormessage=""></span></div>
    </div>
</div>
<div id="user_list"></div>
{!! Notify::render() !!}

<!-- Scripts -->
<script src="admin/assets/js/jquery.min.js"></script>
<script src="admin/assets/js/bootstrap.min.js"></script>
<script src="admin/assets/js/detect.js"></script>
<script src="admin/assets/js/fastclick.js"></script>
<script src="admin/assets/js/jquery.slimscroll.js"></script>
<script src="admin/assets/js/jquery.blockUI.js"></script>
<script src="admin/assets/js/waves.js"></script>
<script src="admin/assets/js/wow.min.js"></script>
<script src="admin/assets/js/jquery.nicescroll.js"></script>
<script src="admin/assets/js/jquery.scrollTo.min.js"></script>
<script src="admin/assets/plugins/accounting/accounting.min.js"></script>
<script src="admin/assets/plugins/bootstrap-filestyle/js/bootstrap-filestyle.min.js"></script>
<script src="admin/assets/plugins/switchery/js/switchery.min.js"></script>
<script src="admin/assets/plugins/multiselect/js/jquery.multi-select.js"></script>
<script src="admin/assets/plugins/jquery-quicksearch/jquery.quicksearch.js"></script>
<script src="admin/assets/plugins/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="admin/assets/plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js"></script>
<script src="admin/assets/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
<script src="admin/assets/pages/jquery.form-advanced.init.js"></script>
<script src="admin/assets/plugins/peity/jquery.peity.min.js"></script>
<script src="admin/assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
<script src="admin/assets/plugins/counterup/jquery.counterup.min.js"></script>
<script src="admin/assets/plugins/morris/morris.min.js"></script>
<script src="admin/assets/plugins/raphael/raphael-min.js"></script>
<script src="admin/assets/plugins/jquery-knob/jquery.knob.js"></script>
<script src="admin/assets/plugins/validate/js/jquery.validate.min.js"></script>
<script src="admin/assets/plugins/parsleyjs/parsley.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
<script src="admin/assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.bootstrap.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.buttons.min.js"></script>
<script src="admin/assets/plugins/datatables/buttons.bootstrap.min.js"></script>
<script src="admin/assets/plugins/datatables/jszip.min.js"></script>
<script src="admin/assets/plugins/datatables/pdfmake.min.js"></script>
<script src="admin/assets/plugins/datatables/vfs_fonts.js"></script>
<script src="admin/assets/plugins/datatables/buttons.html5.min.js"></script>
<script src="admin/assets/plugins/datatables/buttons.print.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.fixedHeader.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.keyTable.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.responsive.min.js"></script>
<script src="admin/assets/plugins/datatables/responsive.bootstrap.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.scroller.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.colVis.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.fixedColumns.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/api/sum().js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-rowgroup/1.1.1/dataTables.rowGroup.js"></script>
<script src="admin/assets/pages/datatables.init.js"></script>
<script src="admin/assets/plugins/moment/moment.js"></script>
<script src="admin/assets/plugins/timepicker/bootstrap-timepicker.js"></script>
<script src="admin/assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<script src="admin/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="admin/assets/plugins/clockpicker/js/bootstrap-clockpicker.min.js"></script>
<script src="admin/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
<script src="admin/assets/pages/jquery.form-pickers.init.js"></script>
<script src="admin/assets/plugins/ladda-buttons/js/spin.min.js"></script>
<script src="admin/assets/plugins/ladda-buttons/js/ladda.min.js"></script>
<script src="admin/assets/plugins/ladda-buttons/js/ladda.jquery.min.js"></script>
<script src="https://js.pusher.com/4.4/pusher.min.js"></script>
<script src="admin/assets/plugins/notifyjs/js/notify.js"></script>
<script src="admin/assets/plugins/notifications/notify-metro.js"></script>
<script src="admin/assets/plugins/dropzone/dropzone.js"></script>
<script src="admin/assets/plugins/lightbox/js/lightbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.5.0/tinymce.min.js" referrerpolicy="origin"></script>
<script src="admin/assets/plugins/ion-rangeslider/ion.rangeSlider.min.js"></script>
<script src="admin/assets/plugins/bootstrap-slider/js/bootstrap-slider.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="admin/assets/js/app.js?v=1.2"></script>
<script src="admin/assets/js/plugnis.js?v=1.0"></script>
<script src="admin/assets/js/jquery.core.js"></script>
<script src="admin/assets/js/jquery.app.js"></script>
<script src="admin/assets/plugins/select2/js4/select2.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
<script src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script src="admin/assets/plugins/slick/js/slick.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="admin/ckeditor/ckeditor.js"></script>

<script>
jQuery(document).ready(function ($) {
    $('.counter').counterUp({ delay: 100, time: 1200 });
    $(".knob").knob();

    // Sidebar toggle
    $('#sidebarToggle').on('click', function() {
        $('#adminSidebar').toggleClass('open');
        $('#sidebarOverlay').toggleClass('active');
    });
    $('#sidebarOverlay').on('click', function() {
        $('#adminSidebar').removeClass('open');
        $(this).removeClass('active');
    });

    // Sidebar submenu toggle
    $('.sidebar-nav-item.has-children > .sidebar-nav-link').on('click', function(e) {
        e.preventDefault();
        $(this).parent().toggleClass('open').siblings().removeClass('open');
    });
});
</script>
<script>
    TableManageButtons.init();
    <?php $length_table = get_option('length_table');?>
    var options = {
        tables_pagination_limit: "<?= !empty($length_table) ? $length_table : '15' ?>",
        scroll_responsive_tables: 1, decimal_places: 2,
        thousands_sep: '{{get_option('thousands_sep')}}',
        decimals_money: '{{get_option('decimals_money')}}',
        decimals_number: '{{get_option('decimals_number')}}',
        decimals_sep: '{{get_option('decimals_sep')}}',
    };
    var lang = {
        datatables: <?php echo json_encode(AppHelper::get_datatables_language_array()); ?>,
        dt_length_menu_all: "<?php echo lang('dt_length_menu_all'); ?>",
        dt_button_export: "<?php echo lang('dt_button_export'); ?>",
        dt_button_excel: "<?php echo lang('dt_button_excel'); ?>",
        dt_button_csv: "<?php echo lang('dt_button_csv'); ?>",
        dt_button_pdf: "<?php echo lang('dt_button_pdf'); ?>",
        dt_button_print: "<?php echo lang('dt_button_print'); ?>",
    }
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
</script>

<script>
    var notificationsWrapper = $('.dropdown-menu-lg');
    var notificationsCountElem = $('.noti-count-badge');
    var notificationsCount = parseInt(notificationsCountElem.data('count') || 0);
    var notifications = notificationsWrapper.find('div.div-data-noti');
    pusher_key = "{{get_option('pusher')}}";
    Pusher.logToConsole = true;
    var pusher = new Pusher(`${pusher_key}`, { cluster: 'ap1' });
    var channel = pusher.subscribe('notifications-channel-<?php echo get_staff_user_id(); ?>-staff');
    channel.bind('notification', function (data) {
        var classes = '', href = '';
        json_data = JSON.parse(data.json_data);
        if (json_data.object != undefined) {
            if (json_data.object == 'transaction') { classes = 'dt-modal'; href = `href=admin/transaction/view/${data.object_id}?type=${json_data.type}`; }
            else if(json_data.object == 'transaction_driver') { classes = 'dt-modal'; href = `href=admin/transaction_driver/view/${data.object_id}?type=${json_data.type}`; }
        }
        var existingNotifications = notifications.html();
        var newNotificationHtml = `<a ${href} class="list-group-item ${classes}" style="background:aliceblue"><div class="media"><div class="pull-left p-r-10"><em onclick=readSingleNoti(this,${data.id}) style="cursor:pointer" class="fa fa-bell-o noti-custom-not-read"></em></div><div class="media-body"><h5 class="media-heading">${data.title}</h5><p class="m-0"><small>${data.content}</small></p><p class="m-0" style="color:#8496AE"><small>${data.created_at_new}</small></p></div></div></a>`;
        notifications.html(newNotificationHtml + existingNotifications);
        notificationsCount += 1;
        notificationsCountElem.attr('data-count', notificationsCount);
        notificationsCountElem.text(notificationsCount);
        if (Notification.permission !== 'granted') { Notification.requestPermission(); }
        else {
            var notification = new Notification(`${data.title}`, { icon: "{{asset('').get_option('favicon')}}", body: data.content });
            if (json_data.object == 'transaction') { notification.onclick = function () { window.open(`{{asset('')}}admin/transaction/list?type=${json_data.type}`); }; }
        }
    });

    pageNoti = 1; isCall = 1;
    function loadNoti() {
        $.ajax({ url: 'admin/notification/loadNoti', type: 'POST', dataType: 'html', cache: false, data: { pageNoti: pageNoti } })
        .done(function (data) { $(".div-data-noti").html(data); if ($('.slimscroll-noti') > $('.div-data-noti').height()) { loadMoreNoti(); } });
    }
    function loadMoreNoti() {
        next = $(".next_noti").val(); if (next == 0) return;
        pageNoti++;
        $.ajax({ type: "POST", url: 'admin/notification/loadMoreNoti', data: { page: pageNoti }, dataType: "html",
            success: function (data) { if (data) { $(`.div-data-noti`).append(data); if ($('.slimscroll-noti') > $('.div-data-noti').height()) { loadMoreNoti(); } } }
        });
    }
    $(".clickNoti").click(function () { if (isCall == 1) { loadNoti(); isCall = 0; } if ($(".keep-inside-clicks-open").hasClass('show')) { isCall = 0; } else { isCall = 1; } });
    $('.slimscroll-noti').scroll(function () { if ($('.slimscroll-noti').scrollTop() >= ($('.div-data-noti').height() - $('.slimscroll-noti').height())) { loadMoreNoti(); } });
    function readSingleNoti(_this, id) {
        $.ajax({ type: "POST", url: 'admin/notification/readSingleNoti', data: { notification_id: id }, dataType: "json",
            success: function (data) { if (data.result) { alert_float('success', data.message); $(_this).closest('a.list-group-item').css({ background: "white" }); $(_this).closest('a.list-group-item').find('em.fa-bell-o').removeClass('noti-custom-not-read').addClass('noti-custom'); } else { alert_float('error', data.message); } }
        });
    }
    function readAllNoti(_this) {
        $.ajax({ type: "POST", url: 'admin/notification/readAllNoti', data: { 'type': 'staff' }, dataType: "json",
            success: function (data) { if (data.result) { alert_float('success', data.message); $(".div-data-noti").html(''); loadMoreNoti(); } else { alert_float('error', data.message); } }
        });
    }
</script>

@yield('script')
</body>
</html>
