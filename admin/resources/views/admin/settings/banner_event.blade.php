<style>
    .nav.nav-tabs > li.tab-btn.active > a {
        background-color: #3a94ef;
        color: white !important;
        border: 0;
        border-radius: 10px;
    }
</style>

{{--<div class="col-md-12">--}}
{{--    <div class="form-group">--}}
{{--        <label for="image_banner_event">{{lang('c_image_banner_event')}}</label>--}}
{{--        <input type="file" name="image_banner_event" id="image_banner_event" class="filestyle image" data-buttonbefore="true">--}}
{{--        <div style="display: flex;justify-content:center;margin-top: 5px" class="show_image">--}}
{{--            <?php--}}
{{--            $imgBannerEvent = get_option('image_banner_event');--}}
{{--            $imgBannerEvent = !empty($imgBannerEvent) ? $imgBannerEvent : imgCameraDefault();--}}
{{--            ?>--}}
{{--            <img src="{{asset($imgBannerEvent)}}" data-imgdefault="{{$imgBannerEvent}}" alt="{{lang('image_banner_event')}}" class="img-responsive img-black" style="width: 150px;height: 150px">--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
<div class="clearfix"></div>
<hr/>
<ul class="nav nav-tabs nav-justified">
    @php
        $lang_default = '';
    @endphp
    @if(!empty($language))
        @foreach($language as $lang)
            <li class="tab-btn {{($lang->is_default == 1) ? 'active' : ''}}">
                <a data-toggle="tab" href="#tab-lang-{{$lang->code}}">{{$lang->name}}</a>
            </li>
        @endforeach
    @endif
</ul>

<div class="tab-content m-t-40">
    @if(!empty($language))
        @foreach($language as $lang)
            @php
                if(!empty($lang->is_default)) {
                    $lang_default = $lang->code;
                }
            @endphp
            <div id="tab-lang-{{$lang->code}}" class="tab-pane fade {{($lang->is_default == 1) ? 'in active' : ''}}">
                @php
                    $banner_event = get_option('banner_event_' . $lang->code);
                    $banner_event = json_decode($banner_event, true);
                @endphp

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="image_banner_event_{{$lang->code}}">{{lang('c_image_banner_event')}} - {{$lang->name}}</label>
                        <input type="file" name="image_banner_event_{{$lang->code}}" id="image_banner_event_{{$lang->code}}" class="filestyle image" data-buttonbefore="true">
                        <div style="display: flex;justify-content:center;margin-top: 5px" class="show_image">
                            <?php
                                $imgBannerEvent = get_option('image_banner_event_' .$lang->code);
                                $imgBannerEvent = !empty($imgBannerEvent) ? $imgBannerEvent : imgCameraDefault();
                            ?>
                            <img src="{{asset($imgBannerEvent)}}" data-imgdefault="{{$imgBannerEvent}}" alt="{{lang('image_banner_event')}}" class="img-responsive img-black" style="width: 100%;max-height: 600px">
                        </div>
                    </div>
                </div>

                <div class="hide">
                    <div class="form-group">
                        <label for="banner_event_{{$lang->code}}[name]">{{ lang('c_name_event_articles') }}</label>
                        <input type="text" name="banner_event_{{$lang->code}}[name]"
                               id="banner_event_{{$lang->code}}_name"
                               class="form-control"
                               value="{{!empty($banner_event['name']) ? $banner_event['name'] : ''}}"
                        >
                    </div>
                    <div class="form-group">
                        <label for="banner_event_{{$lang->code}}[title_one]">{{ lang('c_title_one') }}</label>
                        <input name="banner_event_{{$lang->code}}[title_one]"
                                  id="banner_event_{{$lang->code}}_title_one" class="form-control" value="{{!empty($banner_event['title_one']) ? $banner_event['title_one'] : ''}}"/>
                    </div>
                    <div class="form-group">
                        <label for="banner_event_{{$lang->code}}[title_two]">{{ lang('c_title_two') }}</label>
                        <input name="banner_event_{{$lang->code}}[title_two]"
                                  id="banner_event_{{$lang->code}}_title_two" class="form-control" value="{{!empty($banner_event['title_two']) ? $banner_event['title_two'] : ''}}"/>
                    </div>
                    <hr/>
                </div>
                <div class="clearfix"></div>
                <hr/>
                <h4>FOOTER</h4>
                <div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="title_section banner_event_{{$lang->code}}_footer_title">{{lang('c_title')}} - {{$lang->name}}</label>
                            <textarea type="text" name="banner_event_{{$lang->code}}[footer_title]"
                                      id="banner_event_{{$lang->code}}_footer_title"
                                      class="form-control editor_short">{{$banner_event['footer_title'] ?? ''}}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="title_section banner_event_{{$lang->code}}_footer_content">{{lang('c_content')}} - {{$lang->name}}</label>
                            <textarea type="text" name="banner_event_{{$lang->code}}[footer_content]"
                                      id="banner_event_{{$lang->code}}_footer_content"
                                      class="form-control editor_short">{{$banner_event['footer_content'] ?? ''}}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
            <div class="col-md-12">
                <div class="form-group">
                    <label for="image_footer_banner_event">{{lang('image_footer')}}</label>
                    <input type="file" name="image_footer_banner_event" id="image_footer_banner_event" class="filestyle image" data-buttonbefore="true">
                    <div style="display: flex;justify-content:center;margin-top: 5px" class="show_image">
                            <?php
                            $imgBannerEvent = get_option('image_footer_banner_event');
                            $imgBannerEvent = !empty($imgBannerEvent) ? $imgBannerEvent : imgCameraDefault();
                            ?>
                        <img src="{{asset($imgBannerEvent)}}" data-imgdefault="{{$imgBannerEvent}}" alt="{{lang('image_footer_banner_event')}}" class="img-responsive img-black" style="width: 450px;max-height: 350px">
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
    @endif
</div>

