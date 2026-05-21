<!--
    FILE: Modal Chi Tiết Bài Viết Cộng Đồng (Phiên bản tương thích Bootstrap 3)
-->

<style>
    /* --- 1. UTILITIES CHO BOOTSTRAP 3 (Tự định nghĩa Flexbox) --- */
    .d-flex { display: -webkit-box; display: -webkit-flex; display: -ms-flexbox; display: flex; }
    .align-items-center { -webkit-box-align: center; -webkit-align-items: center; -ms-flex-align: center; align-items: center; }
    .align-items-start { -webkit-box-align: start; -webkit-align-items: flex-start; -ms-flex-align: start; align-items: flex-start; }
    .align-items-end { -webkit-box-align: end; -webkit-align-items: flex-end; -ms-flex-align: end; align-items: flex-end; }
    .justify-content-between { -webkit-box-pack: justify; -webkit-justify-content: space-between; -ms-flex-pack: justify; justify-content: space-between; }
    .flex-grow-1 { -webkit-box-flex: 1; -webkit-flex-grow: 1; -ms-flex-positive: 1; flex-grow: 1; }
    .flex-wrap { -webkit-flex-wrap: wrap; -ms-flex-wrap: wrap; flex-wrap: wrap; }

    /* Spacing Helpers */
    .mb-5 { margin-bottom: 5px !important; }
    .mb-10 { margin-bottom: 10px !important; }
    .mb-15 { margin-bottom: 15px !important; }
    .mb-20 { margin-bottom: 20px !important; }
    .mt-5 { margin-top: 5px !important; }
    .mt-10 { margin-top: 10px !important; }
    .mr-10 { margin-right: 10px !important; }
    .mr-15 { margin-right: 15px !important; }
    .p-20 { padding: 20px !important; }

    /* --- 2. MODAL LAYOUT --- */
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        overflow: hidden;
    }

    .modal-body-custom {
        display: flex;
        padding: 0;
        height: 80vh;
    }

    @media (max-width: 991px) {
        .modal-body-custom { flex-direction: column; height: auto; }
        .right-pane { width: 100% !important; border-left: none !important; border-top: 1px solid #eee; }
    }

    .left-pane {
        flex: 1;
        background: #f3f6f9;
        padding: 25px;
        overflow-y: auto;
    }

    .right-pane {
        width: 400px;
        background: #fff;
        border-left: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        padding: 20px;
    }

    /* --- 3. CUSTOM CARD DESIGN --- */
    .haru-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 25px;
        border: 1px solid #eef0f2;
    }

    .haru-avatar-img {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        border: 3px solid #e0e7ff;
        object-fit: cover;
    }

    .text-bold { font-weight: 700; }
    .text-muted { color: #8898aa; }
    .text-dark { color: #32325d; }

    .haru-badge {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
        letter-spacing: 0.5px;
    }

    .haru-info-box {
        background: #f8f9fe;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #f0f2f5;
    }

    .haru-label {
        font-size: 11px;
        text-transform: uppercase;
        color: #8898aa;
        font-weight: 600;
        margin-bottom: 4px;
        display: block;
    }

    .haru-value {
        font-size: 14px;
        font-weight: 600;
        color: #32325d;
        word-wrap: break-word;
    }

    /* Post Content */
    .post-content {
        font-size: 14px;
        line-height: 1.6;
        color: #333;
        word-wrap: break-word;
        white-space: pre-wrap;
    }

    /* Media Gallery */
    .media-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 15px;
    }
    .media-gallery img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #eee;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .media-gallery img:hover {
        transform: scale(1.05);
    }
    .media-gallery video {
        width: 100%;
        max-height: 300px;
        border-radius: 8px;
        margin-top: 5px;
    }

    /* Comment Items */
    .feed-scroll { flex: 1; overflow-y: auto; padding-right: 5px; }
    .comment-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        background: #fff;
    }
    .reply-item {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 10px;
        margin-top: 8px;
        margin-left: 20px;
        border-left: 3px solid #e0e7ff;
    }
    .comment-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 8px;
    }
    .comment-media img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 4px;
        margin-top: 4px;
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
    ::-webkit-scrollbar-track { background: transparent; }
</style>

<div class="modal-dialog modal-lg" id="modalOrder" role="dialog" style="width: 85%; max-width: 1200px;">
    <div class="modal-content">
        <!-- HEADER -->
        <div class="modal-header" style="background: #fff; border-bottom: 1px solid #f0f0f0; padding: 15px 25px;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 28px;">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title text-bold" style="font-size: 18px;">Chi tiết bài viết</h4>
        </div>

        <!-- BODY -->
        <div class="modal-body-custom">
            @php
                $post = $dtData;
                $author = $post['author'] ?? [];
                $media = $post['media'] ?? [];
                $comments = $post['comments'] ?? [];
                $isHidden = $post['is_hidden'] ?? 0;
                $likesCount = $post['likes_count'] ?? 0;
                $commentsCount = $post['comments_count'] ?? 0;
                $visibility = $post['visibility'] ?? 'everyone';

                $visibilityMap = [
                    'everyone' => ['name' => 'Mọi người', 'bg' => '#81c868', 'color' => 'white'],
                    'friends' => ['name' => 'Bạn bè', 'bg' => '#5e72e4', 'color' => 'white'],
                    'justme' => ['name' => 'Chỉ mình tôi', 'bg' => '#ffbd4a', 'color' => 'white'],
                ];
                $vis = $visibilityMap[$visibility] ?? $visibilityMap['everyone'];
            @endphp

            <!-- LEFT COLUMN: THÔNG TIN CHI TIẾT -->
            <div class="left-pane">
                <div class="haru-card">

                    <!-- 1. Header: Avatar & Info -->
                    <div class="d-flex align-items-center mb-20">
                        <div class="mr-15">
                            <img src="{{ $author['avatar'] ?? 'https://ui-avatars.com/api/?name='.urlencode($author['fullname'] ?? 'U').'&size=128&background=6366f1&color=fff' }}"
                                 class="haru-avatar-img"
                                 onerror="this.src='https://ui-avatars.com/api/?name=U&size=128&background=6366f1&color=fff'">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size: 18px; font-weight: 700; color: #333; line-height: 1.2;">
                                        {{ $author['fullname'] ?? '—' }}
                                    </div>
                                    <div class="text-muted mt-5" style="font-size: 13px;">
                                        {{ $author['email'] ?? '' }}
                                    </div>
                                    <div class="text-muted" style="font-size: 13px;">
                                        {{ $author['phone'] ?? '' }}
                                    </div>
                                </div>
                                <div>
                                    @if($isHidden)
                                        <span class="haru-badge" style="background:red; color:white">Đã ẩn</span>
                                    @else
                                        <span class="haru-badge" style="background:#81c868; color:white">Hiển thị</span>
                                    @endif
                                    <span class="haru-badge" style="background:{{ $vis['bg'] }}; color:{{ $vis['color'] }}">
                                        {{ $vis['name'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Nội dung bài viết -->
                    <div class="mb-20">
                        <div class="haru-label">Nội dung bài viết</div>
                        <div class="post-content">{{ $post['content'] ?? '' }}</div>
                    </div>

                    <!-- 3. Media Gallery -->
                    @if(!empty($media))
                        <div class="mb-20">
                            <div class="haru-label">Media ({{ count($media) }})</div>
                            <div class="media-gallery">
                                @foreach($media as $m)
                                    @if(($m['media_type'] ?? '') === 'video')
                                        <video src="{{ $m['media_url'] }}" controls></video>
                                    @else
                                        <a href="{{ $m['media_url'] }}" data-lightbox="gallery-{{ $post['id'] }}">
                                            <img src="{{ $m['media_url'] }}" onerror="this.style.display='none'">
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- 4. Thống kê -->
                    <div class="haru-info-box">
                        <div class="row">
                            <div class="col-xs-6 mb-15">
                                <span class="haru-label">Lượt thích</span>
                                <div class="haru-value" style="color: #e74c3c;">
                                    <i class="fa fa-heart mr-10"></i>{{ number_format($likesCount) }}
                                </div>
                            </div>
                            <div class="col-xs-6 mb-15">
                                <span class="haru-label">Bình luận</span>
                                <div class="haru-value" style="color: #3498db;">
                                    <i class="fa fa-comment mr-10"></i>{{ number_format($commentsCount) }}
                                </div>
                            </div>

                            <div class="clearfix"></div>
                            <div class="col-xs-12"><hr style="margin: 5px 0 15px 0; border-top: 1px solid #e0e0e0;"></div>

                            <div class="col-xs-6">
                                <span class="haru-label">Ngày đăng</span>
                                <div class="haru-value">
                                    {{ !empty($post['created_at']) ? date('d/m/Y H:i', strtotime($post['created_at'])) : '—' }}
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <span class="haru-label">Cập nhật lần cuối</span>
                                <div class="haru-value">
                                    {{ !empty($post['updated_at']) ? date('d/m/Y H:i', strtotime($post['updated_at'])) : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Báo cáo vi phạm -->
                    @if(!empty($post['reports']) && is_array($post['reports']) && count((array)$post['reports']) > 0)
                        <div style="margin-top: 20px;">
                            <div class="haru-label" style="color: #e74c3c; font-size: 13px; margin-bottom: 10px;">
                                <i class="fa fa-warning mr-5"></i> Báo cáo vi phạm ({{ count((array)$post['reports']) }})
                            </div>
                            <div style="background: #fff; border-radius: 8px; border: 1px solid #fee2e2; overflow: hidden;">
                                <table class="table table-bordered mb-0" style="font-size: 13px; margin-bottom: 0;">
                                    <thead style="background: #fef2f2;">
                                        <tr>
                                            <th>Người báo cáo</th>
                                            <th>Lý do</th>
                                            <th>Ghi chú</th>
                                            <th>Thời gian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach((array)$post['reports'] as $report)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $report['user']['avatar'] ?? 'https://ui-avatars.com/api/?name=U' }}" style="width: 24px; height: 24px; border-radius: 50%; margin-right: 8px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=U'">
                                                        <span>{{ $report['user']['fullname'] ?? '—' }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="label label-danger">{{ $report['violation']['name'] ?? 'Khác' }}</span>
                                                </td>
                                                <td>{{ $report['note'] ?? '—' }}</td>
                                                <td class="text-muted">{{ !empty($report['created_at']) ? date('d/m/Y H:i', strtotime($report['created_at'])) : '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- RIGHT COLUMN: COMMENTS -->
            <div class="right-pane">
                <div class="d-flex justify-content-between align-items-center mb-15" style="border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <div style="font-weight: 700; font-size: 15px; color: #333;">Bình luận</div>
                    <span class="haru-badge" style="background:#dbeafe; color:#1e40af">
                        {{ count($comments) }}
                    </span>
                </div>

                <div class="feed-scroll">
                    @if(!empty($comments))
                        @foreach($comments as $c)
                            <div class="comment-item">
                                <div class="d-flex align-items-center mb-5">
                                    <img src="{{ $c['author']['avatar'] ?? 'https://ui-avatars.com/api/?name=U&size=64&background=6366f1&color=fff' }}"
                                         class="comment-avatar"
                                         onerror="this.src='https://ui-avatars.com/api/?name=U&size=64&background=6366f1&color=fff'">
                                    <div class="flex-grow-1">
                                        <strong style="color: #333; font-size: 13px;">{{ $c['author']['fullname'] ?? '' }}</strong>
                                        <br>
                                        <small style="color: #999;">{{ !empty($c['created_at']) ? date('d/m/Y H:i', strtotime($c['created_at'])) : '' }}</small>
                                    </div>
                                    <div>
                                        <small class="text-muted">
                                            <i class="fa fa-heart text-danger"></i> {{ $c['likes_count'] ?? 0 }}
                                            &nbsp;
                                            <i class="fa fa-reply"></i> {{ $c['replies_count'] ?? 0 }}
                                        </small>
                                    </div>
                                </div>

                                @if(!empty($c['content']))
                                    <div style="font-size: 13px; color: #555; margin-bottom: 8px;">{{ $c['content'] }}</div>
                                @endif

                                @if(!empty($c['media']))
                                    <div class="comment-media">
                                        @foreach($c['media'] as $cm)
                                            @if(($cm['media_type'] ?? '') === 'video')
                                                <video src="{{ $cm['media_url'] }}" controls style="width: 100%; border-radius: 6px; margin-top: 5px; max-height: 120px;"></video>
                                            @else
                                                <a href="{{ $cm['media_url'] }}" data-lightbox="gallery-{{ $post['id'] }}">
                                                    <img src="{{ $cm['media_url'] }}">
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Replies --}}
                                @if(!empty($c['replies']))
                                    @foreach($c['replies'] as $r)
                                        <div class="reply-item">
                                            <div class="d-flex align-items-center mb-5">
                                                <img src="{{ $r['author']['avatar'] ?? 'https://ui-avatars.com/api/?name=U&size=64&background=6366f1&color=fff' }}"
                                                     class="comment-avatar"
                                                     onerror="this.src='https://ui-avatars.com/api/?name=U&size=64&background=6366f1&color=fff'">
                                                <div>
                                                    <strong style="font-size: 12px; color: #333;">{{ $r['author']['fullname'] ?? '' }}</strong>
                                                    <br>
                                                    <small style="color: #aaa;">{{ !empty($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '' }}</small>
                                                </div>
                                            </div>
                                            @if(!empty($r['content']))
                                                <div style="font-size: 12px; color: #666;">{{ $r['content'] }}</div>
                                            @endif
                                            @if(!empty($r['media']))
                                                <div class="comment-media">
                                                    @foreach($r['media'] as $rm)
                                                        @if(($rm['media_type'] ?? '') === 'video')
                                                            <video src="{{ $rm['media_url'] }}" controls style="width: 100%; max-height: 100px;"></video>
                                                        @else
                                                            <a href="{{ $rm['media_url'] }}" data-lightbox="gallery-{{ $post['id'] }}">
                                                                <img src="{{ $rm['media_url'] }}">
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="text-center" style="padding: 40px 0; color: #ccc;">
                            <i class="fa fa-comment-o" style="font-size: 40px; margin-bottom: 10px;"></i>
                            <div>Chưa có bình luận nào</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#modalOrder').modal('show');
    })
</script>
