<style>
    .svc-modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .svc-modal-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        flex: 1;
    }
    .svc-modal-header .close {
        font-size: 20px;
        color: #888;
        opacity: 1;
        line-height: 1;
    }
    .svc-badge-active   { background: #28a745; color: #fff; padding: 2px 10px; border-radius: 20px; font-size: 11px; }
    .svc-badge-inactive { background: #dc3545; color: #fff; padding: 2px 10px; border-radius: 20px; font-size: 11px; }

    /* Slider */
    .svc-slider-wrap { position: relative; width: 100%; background: #f0f0f0; border-radius: 10px; overflow: hidden; }
    .svc-main-img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        display: block;
        border-radius: 10px;
        cursor: zoom-in;
        transition: opacity .25s;
    }
    .svc-thumbs {
        display: flex;
        gap: 6px;
        margin-top: 8px;
        flex-wrap: wrap;
    }
    .svc-thumb {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #e9ecef;
        cursor: pointer;
        transition: border-color .2s, transform .2s;
        flex-shrink: 0;
    }
    .svc-thumb:hover   { border-color: #3a94ef; transform: scale(1.07); }
    .svc-thumb.active  { border-color: #3a94ef; box-shadow: 0 0 0 2px #3a94ef55; }

    /* Info table */
    .svc-info-table { width: 100%; font-size: 13px; border-collapse: collapse; }
    .svc-info-table tr td { padding: 7px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    .svc-info-table tr td:first-child { color: #888; white-space: nowrap; width: 42%; }
    .svc-info-table tr td:last-child  { font-weight: 500; color: #2d3748; }
    .svc-info-table tr:last-child td  { border-bottom: none; }

    /* Section title */
    .svc-section-title {
        font-size: 13px;
        font-weight: 700;
        color: #3a94ef;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin: 0 0 10px;
        padding-bottom: 6px;
        border-bottom: 2px solid #e9ecef;
    }
    .svc-desc {
        font-size: 13px;
        line-height: 1.75;
        color: #555;
    }
    /* Ảnh trong nội dung TinyMCE tự co vừa khung */
    .svc-desc img {
        max-width: 100% !important;
        height: auto !important;
        display: block;
        border-radius: 6px;
        margin: 6px 0;
    }
</style>

<div class="modal-dialog modal-lg" style="margin: 30px auto;">
    <div class="modal-content" style="border-radius: 10px; overflow: hidden; border: none; box-shadow: 0 8px 40px rgba(0,0,0,.15);">

        {{-- HEADER --}}
        <div class="svc-modal-header">
            <i class="fa fa-cube" style="font-size:18px; color:#3a94ef;"></i>
            <h4>{{ $service->name }}</h4>
            @if($service->active == 1)
                <span class="svc-badge-active"><i class="fa fa-check"></i> Hoạt động</span>
            @else
                <span class="svc-badge-inactive"><i class="fa fa-times"></i> Tắt</span>
            @endif
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>

        {{-- BODY --}}
        <div class="modal-body" style="padding: 20px; background: #fafbfc;">
            <div class="row">

                {{-- CỘT TRÁI: Slider + Thumbnails + Thông tin --}}
                <div class="col-md-5" style="padding-right: 10px;">

                    {{-- Slider ảnh chính --}}
                    @php
                        $mainImg = !empty($service->image)
                            ? asset('storage/'.$service->image)
                            : 'admin/assets/images/not_available.jpg';

                        // Gộp ảnh đại diện + gallery thành 1 mảng
                        $allImages = [];
                        if (!empty($service->image)) {
                            $allImages[] = asset('storage/'.$service->image);
                        }
                        if (!empty($images)) {
                            foreach ($images as $img) {
                                $allImages[] = asset('storage/'.$img->image);
                            }
                        }
                        if (empty($allImages)) {
                            $allImages[] = 'admin/assets/images/not_available.jpg';
                        }
                    @endphp

                    <div class="svc-slider-wrap">
                        <a id="svc-main-link" href="{{ $allImages[0] }}" data-lightbox="svc-gallery" data-title="{{ $service->name }}">
                            <img id="svc-main-img" class="svc-main-img" src="{{ $allImages[0] }}" alt="{{ $service->name }}">
                        </a>
                    </div>

                    {{-- Thumbnails (chỉ hiện nếu > 1 ảnh) --}}
                    @if(count($allImages) > 1)
                        <div class="svc-thumbs" id="svc-thumbs">
                            @foreach($allImages as $i => $src)
                                <img class="svc-thumb {{ $i === 0 ? 'active' : '' }}"
                                     src="{{ $src }}"
                                     alt="thumb"
                                     data-index="{{ $i }}"
                                     data-src="{{ $src }}"
                                     onclick="svcSwitchImg(this)">
                            @endforeach
                        </div>
                        {{-- Hidden lightbox links for gallery --}}
                        <div style="display:none">
                            @foreach($allImages as $i => $src)
                                @if($i > 0)
                                    <a href="{{ $src }}" data-lightbox="svc-gallery" data-title="{{ $service->name }}"></a>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Thông tin cơ bản --}}
                    <div style="margin-top: 16px; background:#fff; border-radius:8px; padding:2px 0; border:1px solid #eee;">
                        <table class="svc-info-table">
                            <tr>
                                <td><i class="fa fa-barcode" style="color:#3a94ef"></i> Mã</td>
                                <td>{{ $service->code ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-tag" style="color:#3a94ef"></i> Danh mục</td>
                                <td>{{ !empty($category) ? $category->name : '—' }}</td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-money" style="color:#3a94ef"></i> Đơn giá</td>
                                <td><strong style="color:#e74c3c">{{ number_format($service->price ?? 0) }} đ</strong></td>
                            </tr>
                            @if(!empty($service->discount_percent))
                            <tr>
                                <td><i class="fa fa-percent" style="color:#f39c12"></i> Giảm giá</td>
                                <td><span style="background:#fff3cd; color:#856404; padding:1px 8px; border-radius:10px; font-size:12px;">{{ $service->discount_percent }}%</span></td>
                            </tr>
                            @endif
                            <tr>
                                <td><i class="fa fa-clock-o" style="color:#3a94ef"></i> Thời gian</td>
                                <td>{{ $service->duration_minutes ?? 0 }} phút</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- CỘT PHẢI: Mô tả --}}
                <div class="col-md-7" style="padding-left: 10px;">
                    <p class="svc-section-title"><i class="fa fa-align-left"></i> {{lang('c_content_services')}}</p>
                    <div class="svc-desc" style="background:#fff; border-radius:8px; padding:14px; border:1px solid #eee;">
                        @if(!empty($service->content))
                            {!! $service->content !!}
                        @else
                            <span style="color:#bbb; font-style:italic;">Chưa có mô tả</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- FOOTER --}}
        <div class="modal-footer" style="border-top:1px solid #e9ecef; padding: 12px 20px;">
            <a href="admin/services/detail/{{ $service->id }}" class="btn btn-primary btn-sm">
                <i class="fa fa-pencil"></i> {{lang('c_edit_services')}}
            </a>
            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                <i class="fa fa-times"></i> {{lang('dt_close')}}
            </button>
        </div>
    </div>
</div>

<script>
    function svcSwitchImg(el) {
        var src = $(el).data('src');
        // Đổi ảnh chính + link lightbox
        $('#svc-main-img').css('opacity', 0);
        setTimeout(function () {
            $('#svc-main-img').attr('src', src).css('opacity', 1);
            $('#svc-main-link').attr('href', src);
        }, 150);
        // Active thumbnail
        $('.svc-thumb').removeClass('active');
        $(el).addClass('active');
    }
</script>
