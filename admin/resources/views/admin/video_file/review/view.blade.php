
<style>
    body { background-color: #f4f7f6; font-family: 'Segoe UI', Arial, sans-serif; padding-top: 30px; }

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
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
        color: #7f8c8d;
        font-size: 13px;
    }
    .video-meta span { margin-right: 15px; }

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
    .related-item {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        text-decoration: none !important;
    }
    .related-thumb {
        width: 120px;
        height: 68px;
        border-radius: 4px;
        object-fit: cover;
        flex-shrink: 0;
    }
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
<div class="modal-dialog modal-lg" style="min-width: 60%">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$title}}</h4>
        </div>
        <div class="modal-body">
            <div class="col-md-12">
                <div class="video-view-card">
                    <div class="col-md-12">
                        <div class="col-md-4">
                            <div class="player-wrapper  m-t-30">
                                <video id="mainPlayer" controls poster="">
                                    <source src="{{$video->original_video}}" type="video/mp4">
                                    Trình duyệt của bạn không hỗ trợ phát video.
                                </video>
                            </div>
                        </div>
                    <!-- Vùng Nội dung -->
                        <div class="col-md-8">
                            <div class="video-content-body">
                                <h1 class="video-view-title" id="viewVideoName">
                                    {{$video->name}}
                                </h1>

                                <div class="video-meta">
                                    <span><i class="fa fa-calendar"></i> {{_dt($video->created_at)}}</span>
                                    <span><i class="fa fa-eye"></i> {{number_format($video->count_see)}} lượt xem</span>
                                    <span><i class="fa fa-like"></i> {{number_format($video->count_like)}} lượt thích</span>
                                    <span><i class="fa fa-share"></i> {{number_format($video->count_share)}} lượt chia sẽ</span>
                                    <span><i class="fa fa-comment"></i> {{number_format($video->count_comment)}} lượt bình luận</span>
                                    <span class="label label-info">Mẹo hữu ích</span>
                                </div>

                                @if(!empty($product->id))
                                    <div class="description-box" id="viewVideoDescription">
                                        <h4>Liên Quan Đến Sản Phẩm</h4>
                                        <div class="product-info">
                                            <div class="product-img">
                                                <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                                     style="width:35px;height:35px;" src="{{e($product->image)}}"/>
                                            </div>
                                            <div>
                                                <strong>{{e($product->name)}}</strong>
                                                <br><small>{{e($product->code)}}</small>
                                            </div>
                                        </div>
                                    </div>
                               @endif
                                <div class="description-box" id="viewVideoDescription">
                                    {!! $video->description !!}
                                </div>
                            </div>
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

