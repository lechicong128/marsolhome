<div class="modal-dialog library_review-modal" id="library_review_modal" style="width: 85%;">
    <style>
        .library_review-modal .container {
            margin: 0 auto;
            display: flex;
            gap: 10px;
        }

        .library_review-modal .back-button {
            color: #666;
            text-decoration: none;
            margin-bottom: 30px;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
        }

        .library_review-modal .back-button::before {
            content: "← ";
            margin-right: 5px;
        }

        .library_review-modal .left-panel {
            flex: 0 0 235px;
        }

        .library_review-modal .title {
            color: #2d3748;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 40px;
            line-height: 1.2;
        }

        .library_review-modal .product-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            position: relative;
            text-align: center;
            border: 3px solid;
            padding-top:10px;
        }

        .library_review-modal .product-card.pink {
            border-color: #ff69b4;
            /*background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);*/
        }

        .library_review-modal .product-card.green {
            border-color: #4ecdc4;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }

        .library_review-modal .heart-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 24px;
            height: 24px;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff69b4;
            font-size: 14px;
        }

        .library_review-modal .product-image {
            /*width: 90px;*/
            height: 150px;
            background: white;
            border-radius: 8px;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .library_review-modal .product-image img {
            height: 90%;
        }

        .library_review-modal .product-mockup {
            width: 70px;
            height: 90px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .library_review-modal .pink-mockup {
            background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%);
        }

        .library_review-modal .green-mockup {
            background: linear-gradient(135deg, #4ecdc4 0%, #2d9687 100%);
            font-size: 14px;
        }

        .library_review-modal .brand-name {
            font-size: 11px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .library_review-modal .product-name {
            font-size: 13px;
            font-weight: 500;
            color: #2d3748;
            line-height: 1.4;
        }

        .library_review-modal .right-panel {
            flex: 1;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            padding: 15px;
        }

        .library_review-modal .section-header {
            /*background: linear-gradient(90deg, #ff1493, #ff69b4);*/
            color: #050505;
            padding: 18px 30px;
            font-size: 18px;
            font-weight: 600;
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .library_review-modal .section-header::before {
            content: "";
            position: absolute;
            left: 0px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(90deg, #ff1493, #ff69b4);
            border-radius: 2px;
        }


        .library_review-modal .panel-content {
            padding: 30px;
        }

        .library_review-modal .upload-section {
            margin-bottom: 30px;
        }

        .library_review-modal .upload-label {
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 500;
        }



        .library_review-modal .upload-area {
            border: 2px solid #d1d5db;
            border-radius: 12px;
            padding: 10px 10px;
            text-align: center;
            color: #6b7280;
            background: #fafbfc;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            margin-top: -10px;
            text-align: left;
        }

        .library_review-modal .upload-area video {
            height: 150px;
            width: 150px;
        }

        .library_review-modal .upload-area:hover {
            border-color: #eeeeee;
            background: #fef7f7;
        }

        .library_review-modal .upload-icon {
            width: 48px;
            height: 48px;
            background: #e5e7eb;
            border-radius: 8px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .library_review-modal .photo-section {
            margin-bottom: 35px;
        }

        .library_review-modal .photo-label {
            font-size: 16px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .library_review-modal .optional {
            color: #6b7280;
        }

        .library_review-modal .photo-grid {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .library_review-modal .photo-item {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .library_review-modal .photo-placeholder {
            border: 2px dashed #d1d5db;
            color: #9ca3af;
            font-size: 24px;
            background: #fafbfc;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .library_review-modal .photo-placeholder:hover {
            border-color: #ff69b4;
            background: #fef7f7;
        }

        .library_review-modal .photo-sample {
            /*background: linear-gradient(135deg, #fbbf24, #f59e0b);*/
            color: white;
            font-weight: bold;
            font-size: 14px;
            border: 1px solid #cecece;
        }

        .library_review-modal .remove-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 20px;
            height: 20px;
            background: #374151;
            color: white;
            border-radius: 50%;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
        }

        .library_review-modal  .rating-section {
            margin-bottom: 30px;
        }

        .library_review-modal .rating-header {
            /*background: linear-gradient(90deg, #ff1493, #ff69b4);*/
            color: #050505;
            padding: 18px 30px;
            margin: 0 -30px 30px -30px;
            font-size: 16px;
            font-weight: 600;
        }

        .library_review-modal .rating-content {
            display: flex;
            justify-content: space-between;
            gap: 40px;
        }

        .library_review-modal  .rating-left {
            /*flex: 1;*/
        }

        .library_review-modal .rating-right {
            flex: 0 0 200px;
            text-align: center;
        }

        .library_review-modal  .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        .library_review-modal .rating-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .library_review-modal .rating-label {
            font-size: 14px;
            color: #333;
            margin-left: 30px;
        }

        .library_review-modal .stars {
            display: flex;
            gap: 3px;
        }

        .library_review-modal .star {
            color: #fbbf24;
            font-size: 18px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .library_review-modal .star:hover {
            transform: scale(1.1);
        }

        .library_review-modal .star.empty {
            color: #d1d5db;
        }

        .library_review-modal  .overall-stars {
            margin-bottom: 8px;
        }

        .library_review-modal .overall-label {
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }

        .library_review-modal .review-section {
            margin-bottom: 35px;
        }

        .library_review-modal .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .library_review-modal .review-title {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .library_review-modal  .review-tips {
            color: #3b82f6;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .library_review-modal  .review-tips::before {
            content: "💡";
        }

        .library_review-modal .review-textarea {
            min-height: 120px;
            padding: 16px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 14px;
            resize: vertical;
            font-family: inherit;
            background: #fafbfc;
        }

        .library_review-modal .review-textarea::placeholder {
            color: #9ca3af;
        }

        .library_review-modal .review-textarea:focus {
            outline: none;
            border-color: #ff69b4;
            background: white;
        }
        .photo-item >video {
            width: 100px;
            height: 100px;
        }

        @media (max-width: 768px) {
            .library_review-modal .container {
                flex-direction: column;
            }

            .library_review-modal .left-panel {
                flex: none;
            }

            .library_review-modal .rating-content {
                flex-direction: column;
                gap: 20px;
            }

            .library_review-modal .rating-right {
                flex: none;
            }
        }
        .review-input {
            margin-bottom: 50px;
        }
        .product-card.tab-readonly {
            opacity: 0.6;
            pointer-events: none;
            cursor: not-allowed;
            filter: grayscale(30%);
        }
        .pull-left {
            float: left;
        }
        .m-l-20 {
            margin-left: 20px;
        }
        .m-t-60 {
            margin-top: 60px;
        }
        .info-video {
            color: black;
            font-size: 14px;
        }
        .library_review-modal .container {
            width: 100%;
        }
        .product-image {
            height: 90%;
        }
        .photo-item img{
            height: 100%;
        }
        .photo-item video{
            height: 100%;
        }
    </style>
    <div class="modal-content" style="background: #eee">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{ $title }}</h4>
        </div>
        <div class="modal-body">
            <div class="container">
                <div class="left-panel">
                    @php
                        $tabDefault = '';
                    @endphp
                    @foreach ($product_review as $key => $review)
                        @php
                            $viewTabEvent = '';
                            if($review->is_review == 1 && empty($tabDefault)) {
                                $tabDefault = $review->id;
                            }
                            if($review->is_review == 1) {
                                $viewTabEvent = 'onclick="ViewTabReview('.$review->id.')"';
                            }
                        @endphp
                        <div class="product-card {!! empty($review->is_review) ? 'tab-readonly cItems-review' : '' !!}"
                             data-color="{{$review->background_color}}"
                             data-id="{{$review->id}}" {!! $viewTabEvent !!}>
                            <div class="product-image" style="background: {{$review->color_header}}">
                                <img src="{{$review->image_product}}"/>
                            </div>
                            <div class="brand-name">{{$review->code_product}}</div>
                            <div class="product-name">{{$review->name}}</div>
                        </div>
                    @endforeach
                </div>

                <div class="right-panel">
                    @foreach ($product_review as $keyReview => $review)
                    @php
                        if(empty($review->is_review)) {
                            continue;
                        }
                    @endphp
                        <div class="tab-review hide" data-id="{{$review->id}}">
                            <div class="section-header">Video Review - Sản phẩm: {{$review->code_product}}</div>
                            <div class="upload-area">
                                <div class="pull-left">
                                    <video controls="" style="border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                        <source src="{{$review->video_review}}" type="video/mp4">
                                        <source src="{{$review->video_review}}" type="video/ogg">
                                    </video>
                                </div>
                                @php
                                    $nameVideo = explode('/', $review->video_review);
                                    $nameVideo = $nameVideo[count($nameVideo) - 1];
                                @endphp
                                <div class="pull-left m-l-20 m-t-60">
                                    <div class="info-video">{{$nameVideo}}</div>
                                    <div class="date-video"><i>{{_dt($review->date_review)}}</i></div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            @if(!empty($review->media_other))
                                <div class="photo-section">
                                    <div class="photo-label">Hình Ảnh/Video khách review</div>
                                    <div class="photo-grid">
                                        @foreach($review->media_other as $key => $value)
                                            <div class="photo-item photo-sample">
                                                @if($value['type'] == 'video')
                                                    <video controls="" style="border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                                        <source src="{{$value['media']}}" type="video/mp4">
                                                        <source src="{{$value['media']}}" type="video/ogg">
                                                    </video>
                                                @else
                                                    <img width="100%" src="{{$value['media']}}">
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                                @if(!empty($review->list_evaluate))
                                    <div class="rating-section">
                                        <div class="section-header">Rating</div>
                                        <div class="rating-content">
                                            <div class="rating-left">
                                                <div class="section-title">Product quality</div>
                                                @if(!empty($review->list_evaluate))
                                                    @foreach($review->list_evaluate as $kevaluate => $vevaluate)
                                                        @php
                                                            if($vevaluate->type == 2) {
                                                                continue;
                                                            }
                                                        @endphp
                                                        <div class="rating-row">
                                                            <div class="stars">
                                                                @for($i = 0; $i < $vevaluate->star; $i++)
                                                                    <span class="star">★</span>
                                                                @endfor
                                                                @for($i = $vevaluate->star; $i < 5; $i++)
                                                                    <span class="star">☆</span>
                                                                @endfor
                                                            </div>
                                                            <div class="rating-label">{{$vevaluate->name}}</div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="rating-right">
                                                <div class="section-title">Overall experience</div>
                                                @if(!empty($review->list_evaluate))
                                                    @foreach($review->list_evaluate as $kevaluate => $vevaluate)
                                                        @php
                                                            if($vevaluate->type == 1) {
                                                                continue;
                                                            }
                                                        @endphp
                                                        <div class="overall-stars">
                                                            @for($i = 0; $i < $vevaluate->star; $i++)
                                                                <span class="star">★</span>
                                                            @endfor
                                                            @for($i = $vevaluate->star; $i < 5; $i++)
                                                                <span class="star">☆</span>
                                                            @endfor
                                                        </div>
                                                        <div class="overall-label">{{$vevaluate->name}}</div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="review-section">
                                    <div class="review-header">
                                        <div class="review-title">Nội dung khách đánh giá</div>
                                        <div class="review-tips">Review tips</div>
                                    </div>
                                    <div class="review-textarea">{{$review->content_evaluate ?? ''}}</div>
                                </div>
                            </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ lang('dt_close') }}</button>
        </div>
    </div>
</div>
<script>
    function ViewTabReview(idTab = '') {
        $('.tab-review').addClass('hide');
        $('.product-card').removeClass('pink');
        $('.product-card').css('background', '');
        $(`.tab-review[data-id="${idTab}"]`).removeClass('hide');
        $(`.product-card[data-id="${idTab}"]`).addClass('pink');

        var colorHeader = $(`.product-card[data-id="${idTab}"]`).attr('data-color');
        $(`.product-card[data-id="${idTab}"]`).css('background', colorHeader);
    }
    ViewTabReview({{$tabDefault}});



</script>
