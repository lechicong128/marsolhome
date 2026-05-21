
<style>
    /* Scope styles to modal to avoid affecting whole admin */
    .modal-elearning-view { font-family: 'Segoe UI', Arial, sans-serif; }

    /* Video Viewer Container */
    .video-view-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 30px;
    }

    /* Player Section */
    .player-wrapper {
        background: #000;
        position: relative;
        padding-bottom: 56.25%; /* Tỷ lệ 16:9 */
        height: 0;
        overflow: hidden;
    }
    .player-wrapper video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        outline: none;
    }

    /* Content Section */
    .video-content-body { padding: 25px; }

    .video-view-title {
        font-size: 24px;
        font-weight: bold;
        color: #2c3e50;
        margin-top: 0;
        margin-bottom: 15px;
        line-height: 1.4;
    }

    .video-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
        color: #7f8c8d;
        font-size: 13px;
    }
    .video-chip{
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        padding: 6px 10px;
        color: #475569;
        white-space: nowrap;
    }

    .description-box {
        color: #4a5568;
        line-height: 1.8;
        font-size: 15px;
    }
    .description-box h3, .description-box h4 { color: #2d3748; margin-top: 20px; }

    /* Sidebar suggestions */
    .sidebar-title {
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 20px;
        color: #34495e;
        border-left: 4px solid #3498db;
        padding-left: 10px;
    }
    .sidebar-list{
        max-height: 520px;
        overflow: auto;
        padding-right: 6px;
    }
    .sidebar-list::-webkit-scrollbar{ width: 8px; }
    .sidebar-list::-webkit-scrollbar-thumb{ background: #e2e8f0; border-radius: 999px; }
    .related-item {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        text-decoration: none !important;
        padding: 10px;
        border-radius: 10px;
        border: 1px solid transparent;
        transition: .2s ease;
    }
    .related-item:hover{
        background: #f8fafc;
        border-color: #e2e8f0;
    }
    .related-thumb {
        width: 120px;
        height: 68px;
        border-radius: 4px;
        object-fit: cover;
        flex-shrink: 0;
        background: #0b1220;
    }
    .related-thumb-wrap{
        position: relative;
        width: 120px;
        height: 68px;
        flex-shrink: 0;
    }
    .related-thumb-wrap .related-thumb{
        width: 100%;
        height: 100%;
        display: block;
    }
    .related-play{
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        opacity: .9;
        transition: .2s ease;
    }
    .related-play i{
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: rgba(0,0,0,.55);
        box-shadow: 0 6px 16px rgba(0,0,0,.25);
    }
    .related-item:hover .related-play{ opacity: 1; transform: scale(1.02); }
    .related-item.is-active{
        background: #eff6ff;
        border-color: #bfdbfe;
    }
    .related-item.is-active .related-info h5{ color: #1d4ed8; }
    .related-info h5 {
        margin: 0;
        font-size: 13px;
        font-weight: bold;
        color: #2c3e50;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Modal Customization */
    .modal-video-viewer .modal-content { border-radius: 12px; border: none; overflow: hidden; }
    .modal-video-viewer .modal-body { padding: 0; }
    .modal-video-viewer .close-btn-overlay {
        position: absolute;
        right: 15px;
        top: 15px;
        z-index: 10;
        color: white;
        background: rgba(0,0,0,0.5);
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.3s;
    }
    .modal-video-viewer .close-btn-overlay:hover { background: rgba(0,0,0,0.8); }
</style>
<div class="modal-dialog modal-lg modal-elearning-view" style="min-width: 60%">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$title}}</h4>
        </div>
        <div class="modal-body">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#tab-info">Thông tin</a></li>
                    <li><a data-toggle="tab" href="#tab-user-unlock">Danh sách User đã mở khóa</a></li>
                </ul>

                <div class="tab-content">
                    <div id="tab-info" class="tab-pane fade in active">
                        <div class="video-view-card">
                            <div class="row" style="margin:0;">
                                <div class="col-md-7" style="padding:0;">
                                    <div class="player-wrapper m-t-30">
                                        <video id="mainPlayer" controls playsinline preload="metadata" poster="">
                                            <source src="{{$elearning->video_trailer->original_video}}" type="video/mp4">
                                            Trình duyệt của bạn không hỗ trợ phát video.
                                        </video>
                                    </div>
                                    <div class="video-content-body">
                                        <h1 class="video-view-title" id="viewVideoName">{{$elearning->title}}</h1>
                                        <div class="video-meta">
                                            <span class="video-chip"><i class="fa fa-calendar"></i> {{_dt($elearning->created_at)}}</span>
                                            <span class="video-chip"><i class="fa fa-eye"></i> {{number_format($elearning->count_see)}} lượt xem</span>
                                            <span class="video-chip"><i class="fa fa-thumbs-up"></i> {{number_format($elearning->count_like)}} lượt thích</span>
                                            <span class="video-chip"><i class="fa fa-share"></i> {{number_format($elearning->count_share)}} lượt chia sẻ</span>
                                            <span class="video-chip"><i class="fa fa-comment"></i> {{number_format($elearning->count_comment)}} bình luận</span>
                                        </div>
                                        <div class="description-box" id="viewVideoDescription">
                                            {!! $elearning->description !!}
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-5" style="padding:20px 20px 10px 20px; border-left: 1px solid #eef2f7;">
                                    <div class="sidebar-title">Video Trailer</div>
                                    <div class="sidebar-list">
                                        <a href="#" class="related-item js-related-video is-active" data-video-src="{{$elearning->video_trailer->original_video}}" data-video-title="{{e($elearning->video_trailer->name)}}" data-video-description="{{e($elearning->video_trailer->description ?? '')}}">
                                            <span class="related-thumb-wrap">
                                                <img class="related-thumb" src="{{!empty($elearning->video_trailer->thumbnail) ? $elearning->video_trailer->thumbnail : 'admin/assets/images/not_available.jpg'}}" alt="Thumb" onerror="this.onerror=null; this.src='admin/assets/images/not_available.jpg';"/>
                                                <span class="related-play"><i class="fa fa-play"></i></span>
                                            </span>
                                        </a>
                                    </div>
                                    <div class="sidebar-title">Danh sách video</div>
                                    <div class="sidebar-list">
                                        @foreach($elearning->list_videos as $key => $video)
                                            <a href="#"
                                               class="related-item js-related-video"
                                               data-video-src="{{$video->original_video}}"
                                               data-video-title="{{e($video->name)}}"
                                               data-video-description="{{e($video->description ?? '')}}">
                                                <span class="related-thumb-wrap">
                                                    <img class="related-thumb"
                                                         src="{{!empty($video->thumbnail) ? $video->thumbnail : 'admin/assets/images/not_available.jpg'}}"
                                                         alt="Thumb"
                                                         onerror="this.onerror=null; this.src='admin/assets/images/not_available.jpg';"/>
                                                    <span class="related-play"><i class="fa fa-play"></i></span>
                                                </span>
                                                <div class="related-info">
                                                    <h5>{{$video->name}}</h5>
                                                    <div class="text-muted" style="font-size:12px; display:flex; gap:10px; flex-wrap:wrap; margin-top:6px;">
                                                        <span><i class="fa fa-eye"></i> {{number_format($video->count_see)}}</span>
                                                        <span><i class="fa fa-thumbs-up"></i> {{number_format($video->count_like)}}</span>
                                                        <span><i class="fa fa-share"></i> {{number_format($video->count_share)}}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab-user-unlock" class="tab-pane fade in">
                        <div class="card-box table-responsive row">
                            <input type="hidden" name="id_elearning" id="id_elearning" value="{{$elearning->id}}">
                            <table id="table_customer_unlock" class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center">{{lang('dt_stt')}}</th>
                                    <th class="text-center">{{lang('Khách hàng')}}</th>
                                    <th class="text-center">{{lang('Thời gian mở khóa')}}</th>
                                    <th class="text-center">{{lang('dt_actions')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn-cancel" data-dismiss="modal">Đóng</button>
        </div>
    </div>
</div>

<script>
    var fnserverparams = {
        "id_elearning" : "#id_elearning"
    };
    var oTablePayment;
    oTablePayment = InitDataTable('#table_customer_unlock', 'admin/video/getCustomerUnlock', {
        'order': [
            [1, 'desc']
        ],
        'responsive': true,
        "ajax": {
            "type": "POST",
            "url": "admin/video/getCustomerUnlock",
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
                    return `<div class="text-center">${data}</data>`;
                },
                data: 'DT_RowIndex', name: 'DT_RowIndex',width: "80px" },
            {data: 'id_client', name: 'id_client'},
            {data: 'created_at', name: 'created_at',width: "150px"},
            {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

        ]
    });

    // Click item -> load video into main player
    $(document).on('click', '.js-related-video', function(e){
        e.preventDefault();
        var $item = $(this);
        var src = $item.data('video-src');
        if(!src) return;

        $('.js-related-video').removeClass('is-active');
        $item.addClass('is-active');

        var player = document.getElementById('mainPlayer');
        if (!player) return;

        // Replace source and reload
        player.pause();
        var source = player.querySelector('source');
        if (!source) {
            source = document.createElement('source');
            source.type = 'video/mp4';
            player.appendChild(source);
        }
        source.src = src;
        player.load();
        player.play().catch(function(){});

        // Update title/description (optional, keeps layout nice)
        var title = $item.data('video-title');
        if (title) $('#viewVideoName').text(title);

        var desc = $item.data('video-description');
        if (typeof desc !== 'undefined') {
            // plain text -> show as text, avoid injecting HTML
            $('#viewVideoDescription').text(desc);
        }
    });
</script>

