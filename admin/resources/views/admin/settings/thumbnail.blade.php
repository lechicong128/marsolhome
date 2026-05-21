<style>
    .nav.nav-tabs > li.tab-btn.active > a {
        background-color: #3a94ef;
        color: white !important;
        border: 0;
        border-radius: 10px;
    }
</style>
<ul class="nav nav-tabs nav-justified">
    @if(!empty($language))
        @foreach($language as $lang)
            <li class="tab-btn {{($lang->is_default == 1) ? 'active' : ''}}">
                <a data-toggle="tab" href="#tab-lang-{{$lang->code}}">{{$lang->name}}</a>
            </li>
        @endforeach
    @endif
</ul>


<div class="tab-content m-t-40" style="padding-left:0px;padding-right:0px;">
    @if(!empty($language))
        @foreach($language as $lang)
            <div id="tab-lang-{{$lang->code}}" class="tab-pane fade {{($lang->is_default == 1) ? 'in active' : ''}}">

                <div class="form-group">
                    <label for="logo">{{lang('images')}}</label>
                    <input type="file" name="{{$lang->code}}_image_thumbnal" id="{{$lang->code}}_image_thumbnal" class="filestyle image" data-buttonbefore="true">
                    <div style="display: flex;justify-content:center;margin-top: 5px" class="show_image">
                        <?php
                            $imgthumbnal = get_option($lang->code . '_image_thumbnal');
                            $imgthumbnal = !empty($imgthumbnal) ? $imgthumbnal : imgCameraDefault();
                        ?>
                        <img src="{{asset($imgthumbnal)}}" data-imgdefault="{{$imgthumbnal}}" alt="{{lang('images')}}" class="img-responsive img-black" style="width: 150px;height: 150px">
                    </div>
                </div>


                @php $value = get_option($lang->code . '_title_thumbnal'); @endphp
                <div class="form-group">
                    <label for="{{$lang->code}}_title_thumbnal">{{ lang('title_thumbnal') }}</label>
                    <input type="text" name="{{$lang->code}}_title_thumbnal"
                           id="{{$lang->code}}_title_thumbnal"
                           class="form-control"
                           value="{{$value ?? ''}}"/>
                </div>


                @php $value = get_option($lang->code . '_content_thumbnal'); @endphp
                <div class="form-group">
                    <label for="{{$lang->code}}_content_thumbnal">{{ lang('content_thumbnal') }}</label>
                    <textarea name="{{$lang->code}}_content_thumbnal"
                              id="{{$lang->code}}_content_thumbnal" class="form-control">{{$value ?? ''}}</textarea>
                </div>
            </div>
        @endforeach
    @endif
</div>

