<!--
    FILE: Modal Chi Tiết Thử Thách (Phiên bản tương thích Bootstrap 3)
-->

<style>
    /* --- 1. UTILITIES CHO BOOTSTRAP 3 (Tự định nghĩa Flexbox) --- */
    /* Vì BS3 không có flexbox, ta tự thêm class để căn chỉnh dễ hơn */
    .d-flex { display: -webkit-box; display: -webkit-flex; display: -ms-flexbox; display: flex; }
    .align-items-center { -webkit-box-align: center; -webkit-align-items: center; -ms-flex-align: center; align-items: center; }
    .align-items-start { -webkit-box-align: start; -webkit-align-items: flex-start; -ms-flex-align: start; align-items: flex-start; }
    .align-items-end { -webkit-box-align: end; -webkit-align-items: flex-end; -ms-flex-align: end; align-items: flex-end; }
    .justify-content-between { -webkit-box-pack: justify; -webkit-justify-content: space-between; -ms-flex-pack: justify; justify-content: space-between; }
    .flex-grow-1 { -webkit-box-flex: 1; -webkit-flex-grow: 1; -ms-flex-positive: 1; flex-grow: 1; }
    .flex-wrap { -webkit-flex-wrap: wrap; -ms-flex-wrap: wrap; flex-wrap: wrap; }

    /* Spacing Helpers (BS3 không có mb-4, p-4...) */
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
    /* Override lại style của Modal BS3 một chút cho hiện đại */
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        overflow: hidden; /* Để bo góc hoạt động */
    }

    /* Tạo bố cục 2 cột giống thiết kế cũ: Left Pane & Right Pane */
    .modal-body-custom {
        display: flex;
        padding: 0;
        height: 80vh; /* Chiều cao cố định để scroll */
    }

    @media (max-width: 991px) {
        .modal-body-custom { flex-direction: column; height: auto; }
        .right-pane { width: 100% !important; border-left: none !important; border-top: 1px solid #eee; }
    }

    /* LEFT PANE */
    .left-pane {
        flex: 1;
        background: #f3f6f9; /* Màu nền xám nhạt sang trọng */
        padding: 25px;
        overflow-y: auto;
    }

    /* RIGHT PANE */
    .right-pane {
        width: 360px;
        background: #fff;
        border-left: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        padding: 20px;
    }

    /* --- 3. CUSTOM CARD DESIGN (Haru Style) --- */
    .haru-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 25px;
        border: 1px solid #eef0f2;
    }

    /* Avatar */
    .haru-avatar-img {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        border: 3px solid #e0e7ff;
        object-fit: cover;
    }

    /* Text Styles */
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

    /* Info Grid */
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

    .haru-ref-code {
        font-family: monospace;
        background: #e9ecef;
        padding: 3px 6px;
        border-radius: 4px;
        color: #525f7f;
        font-size: 12px;
    }

    /* Progress Bar (Custom đẹp hơn BS3 mặc định) */
    .custom-progress {
        height: 10px;
        background-color: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
        margin-top: 5px;
        box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
    }
    .custom-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #5e72e4 0%, #825ee4 100%);
        transition: width .6s ease;
    }

    /* Feed Items */
    .feed-scroll { flex: 1; overflow-y: auto; padding-right: 5px; }
    .post-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: #fff;
    }
    .post-images img {
        width: 80px; height: 80px; object-fit: cover;
        border-radius: 6px; margin-right: 5px; margin-top: 5px;
        border: 1px solid #eee;
    }

    /* Scrollbar đẹp */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
    ::-webkit-scrollbar-track { background: transparent; }
</style>

<div class="modal-dialog modal-lg" id="modalOrder" role="dialog" style="width: 80%; max-width: 1100px;">
    <div class="modal-content">
        <!-- HEADER (BS3 standard) -->
        <div class="modal-header" style="background: #fff; border-bottom: 1px solid #f0f0f0; padding: 15px 25px;">
            <!-- data-dismiss="modal" là chuẩn BS3 -->
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 28px;">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title text-bold" style="font-size: 18px;">Chi tiết thử thách</h4>
        </div>

        <!-- BODY -->
        <div class="modal-body-custom">
            @php
                $details = $dtData;
                $challengeBlock = $details['challenge'] ?? [];
                $customer = $challengeBlock['customer'] ?? [];
                $challenge = $challengeBlock['challenge'] ?? [];
                $submissions = $details['submissions'] ?? [];
                $days_left = $details['days_left'] ?? null;
                $completion = $challengeBlock['completion_rate'] ?? 0;
                $deposit = $challengeBlock['deposit'] ?? 0;
                $total_haru_xu = $challengeBlock['total_haru_xu'] ?? 0;
                $status = $challengeBlock['status'] ?? 0;

                // Logic màu sắc
                if ($completion >= 100 || $status == 1) {
                    $status_name = lang('step_success');
                    $status_color = 'white';
                    $status_bg = '#81c868';
                } elseif ($status == 2 || $status == 0) {
                    if($status == 2 || strtotime($challengeBlock['date_challenge']) < strtotime(date('Y-m-d'))) {
                        $status_name = lang('step_overdue');
                        $status_color = 'white';
                        $status_bg = 'red';
                    }
                    else {
                        $status_name = lang('step_pending');
                        $status_color = 'white';
                        $status_bg = '#ffbd4a';
                    }
                }
            @endphp

                <!-- LEFT COLUMN: THÔNG TIN CHI TIẾT -->
            <div class="left-pane">
                <div class="haru-card">

                    <!-- 1. Header: Avatar & Info -->
                    <div class="d-flex align-items-center mb-20">
                        <div class="mr-15">
                            <img src="{{ $customer['avatar_new'] ?? 'https://ui-avatars.com/api/?name='.urlencode($customer['fullname'] ?? 'U').'&size=128&background=6366f1&color=fff' }}"
                                 class="haru-avatar-img">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size: 18px; font-weight: 700; color: #333; line-height: 1.2;">
                                        {{ $customer['fullname'] ?? '—' }}
                                    </div>
                                    <div class="text-muted mt-5" style="font-size: 13px;">
                                        {{ $customer['email'] ?? '' }}
                                    </div>
                                    <div class="text-muted" style="font-size: 13px;">
                                        {{ $customer['phone'] ?? '' }}
                                    </div>
                                </div>
                                <span class="haru-badge" style="background:{{ $status_bg }}; color:{{ $status_color }}">
                                    {{ $status_name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Challenge Name -->
                    <div class="mb-20">
                        <div class="haru-label">Thử thách</div>
                        <div style="font-size: 16px; font-weight: 700; color: #5e72e4;">
                            <i class="fa fa-trophy mr-10"></i>{{ $challenge['name'] ?? '—' }}
                        </div>
                    </div>

                    <!-- 3. Grid Info (Sử dụng row/col-xs-6 của BS3) -->
                    <div class="haru-info-box">
                        <div class="row">

                            <!-- Row 4 -->
                            <div class="col-xs-12 mt-15">
                                <div class="d-flex align-items-center">
                                    <span class="haru-label mr-10" style="margin-bottom:0;">{{lang('code_challenge_me')}}:</span>
                                    <span class="haru-ref-code">{{ $challengeBlock['reference_no'] ?? '—' }}</span>
                                </div>
                            </div>
                            <hr/>
                            <!-- Row 1 -->
                            <div class="col-xs-6 mb-15">
                                <span class="haru-label">{{lang('dt_date_start')}}</span>
                                <div class="haru-value">
                                    {{ $challengeBlock['date'] ? _dthuan($challengeBlock['date']) : '—' }}
                                </div>
                            </div>
                            <div class="col-xs-6 mb-15">
                                <span class="haru-label">{{lang('dt_date_end')}}</span>
                                <div class="haru-value">
                                    {{ $challengeBlock['date_challenge'] ? _dthuan($challengeBlock['date_challenge']) : '—' }}
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="col-xs-6 mb-15">
                                <span class="haru-label">{{lang('number_of_days_remaining')}}</span>
                                <div class="haru-value" style="color: {{ ($days_left ?? 0) <= 3 ? '#e74c3c' : '#2ecc71' }}">
                                    <i class="fa fa-clock-o mr-10"></i>{{ $days_left ?? '—' }} {{lang('day')}}
                                </div>
                            </div>
                            <div class="col-xs-6 mb-15">
                                <span class="haru-label">{{lang('total_day')}}</span>
                                <div class="haru-value">{{ $challenge['days'] ?? '—' }} {{lang('day')}}</div>
                            </div>

                            <div class="clearfix"></div>
                            <div class="col-xs-12"><hr style="margin: 5px 0 15px 0; border-top: 1px solid #e0e0e0;"></div>

                            <!-- Row 3 -->
                            <div class="col-xs-6">
                                <span class="haru-label">Tiền cọc</span>
                                <div class="haru-value">{{ number_format($deposit) }} đ</div>
                            </div>
                            <div class="col-xs-6">
                                <span class="haru-label">{{lang('total_haru_xu')}}</span>
                                <div class="haru-value" style="color: #f39c12;">
                                    <i class="fa fa-money mr-10"></i>{{ number_format($total_haru_xu) }}
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- 4. Progress -->
                    <div>
                        <div class="d-flex justify-content-between align-items-end mb-5">
                            <span style="font-size: 13px; font-weight: 600; color: #333;">{{lang('c_progress_success')}}</span>
                            <span style="font-size: 13px; font-weight: 700; color: #5e72e4;">{{ $completion }}%</span>
                        </div>
                        <div class="custom-progress">
                            <div class="custom-progress-bar" style="width: {{ intval($completion) }}%"></div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT COLUMN: FEED (Lịch sử) -->
            <div class="right-pane">
                <div class="d-flex justify-content-between align-items-center mb-15" style="border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <div style="font-weight: 700; font-size: 15px; color: #333;">{{lang('history_submissions')}}</div>
                    <span class="badge" style="background: #f0f0f0; color: #555;">
                        <span class="haru-badge" style="background:#dbeafe; color:#1e40af">
                            {{ count($submissions) }}
                        </span>
                    </span>
                </div>

                <div class="feed-scroll">
                    @if(!empty($submissions))
                        @foreach($submissions as $s)
                            <div class="post-item">
                                <div class="d-flex justify-content-between mb-5">
                                    <strong style="color: #333; font-size: 13px;">{{ $customer['fullname'] ?? '' }}</strong>
                                    <small style="color: #999;">{{ $s['created_at'] ? _dthuan($s['created_at']) : '' }}</small>
                                </div>

                                @if(!empty($s['content']))
                                    <div style="font-size: 13px; color: #555; margin-bottom: 8px;">{{ $s['content'] }}</div>
                                @endif

                                @if(!empty($s['files']))
                                    <div class="post-images">
                                        @foreach($s['files'] as $f)
                                            @if(isset($f['type']) && $f['type'] === 'video')
                                                <!-- Video nhỏ gọn -->
                                                <video src="{{ $f['file_url'] }}" controls style="width: 100%; border-radius: 6px; margin-top: 5px; max-height: 150px;"></video>
                                            @else
                                                <a href="{{ $f['file_url'] }}" target="_blank">
                                                    <img src="{{ $f['file_url'] }}">
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="text-center" style="padding: 40px 0; color: #ccc;">
                            <i class="fa fa-inbox" style="font-size: 40px; margin-bottom: 10px;"></i>
                            <div>{{lang('submissions_404')}}</div>
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
