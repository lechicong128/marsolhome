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

<div class="tab-content m-t-40">
    <div class="form-group">
        <label for="percent_person_referral">{{ lang('dt_percent_person_referral') }}</label>
        <input name="percent_person_referral"
               id="percent_person_referral" class="form-control editor" type="number" min="0" max="100" value="{{!empty(get_option('percent_person_referral')) ? get_option('percent_person_referral') : 0}}">
    </div>
    <div class="form-group">
        <label for="percent_referral">{{ lang('dt_percent_referral') }}</label>
        <input name="percent_referral"
               id="percent_referral" class="form-control editor" type="number" min="0" max="100" value="{{!empty(get_option('percent_referral')) ? get_option('percent_referral') : 0}}">
    </div>
    <div class="form-group">
        <label for="money_max_referral">{{ lang('dt_money_max_referral') }}</label>
        <input name="money_max_referral"
               id="money_max_referral" class="form-control" type="text" value="{{!empty(get_option('money_max_referral')) ? formatMoney(get_option('money_max_referral')) : 0}}" onkeyup="formatNumBerKeyChange(this)">
    </div>
    @if(!empty($language))
        @foreach($language as $lang)
            <div id="tab-lang-{{$lang->code}}" class="tab-pane fade {{($lang->is_default == 1) ? 'in active' : ''}}">
                @php
                    $referral_program = get_option('referral_program_'.$lang->code);
                    $referral_program = json_decode($referral_program, true);

                    $content_referral = get_option('content_referral_'.$lang->code);
                    $content_referral = json_decode($content_referral, true);
                @endphp

                <div class="form-group">
                    <label for="content_referral_{{$lang->code}}[title]">{{ lang('dt_title_referral') }}</label>
                    <input name="content_referral_{{$lang->code}}[title]"
                           id="content_referral_{{$lang->code}}_title" class="form-control editor" type="text" value="{{!empty($content_referral['title']) ? $content_referral['title'] : ''}}">
                </div>
                <div class="form-group">
                    <label for="content_referral_{{$lang->code}}[content]">{{ lang('dt_content_referral') }}</label>
                    <textarea name="content_referral_{{$lang->code}}[content]"
                              id="content_referral_{{$lang->code}}content" class="form-control editor">{{!empty($content_referral['content']) ? $content_referral['content'] : ''}}</textarea>
                </div>
                <hr/>
                <div class="form-group">
                    <label for="referral_program_{{$lang->code}}[one][title]">{{ lang('c_referral_step_one') }}</label>
                    <input type="text" name="referral_program_{{$lang->code}}[one][title]"
                           id="referral_program_{{$lang->code}}_one_title"
                           class="form-control"
                           value="{{!empty($referral_program['one']['title']) ? $referral_program['one']['title'] : ''}}"
                    >
                </div>
                <div class="form-group">
                    <label for="referral_program_{{$lang->code}}[one][content]">{{ lang('c_referral_program') }}</label>
                    <textarea name="referral_program_{{$lang->code}}[one][content]"
                              id="referral_program_{{$lang->code}}_one_content" class="form-control editor">{{!empty($referral_program['one']['content']) ? $referral_program['one']['content'] : ''}}</textarea>
                </div>
                <hr/>

                <div class="form-group">
                    <label for="referral_program_{{$lang->code}}[two][title]">{{ lang('c_referral_step_two') }}</label>
                    <input type="text" name="referral_program_{{$lang->code}}[two][title]"
                           id="referral_program_{{$lang->code}}_two_title"
                           class="form-control"
                           value="{{!empty($referral_program['two']['title']) ? $referral_program['two']['title'] : ''}}"
                    >
                </div>
                <div class="form-group">
                    <label for="referral_program_{{$lang->code}}[two][content]">{{ lang('c_referral_program') }}</label>
                    <textarea name="referral_program_{{$lang->code}}[two][content]"
                              id="referral_program_{{$lang->code}}_two_content" class="form-control editor">{{!empty($referral_program['two']['content']) ? $referral_program['two']['content'] : ''}}</textarea>
                </div>

                <hr/>
                <div class="form-group">
                    <label for="referral_program_{{$lang->code}}[three][title]">{{ lang('c_referral_step_three') }}</label>
                    <input type="text" name="referral_program_{{$lang->code}}[three][title]"
                           id="referral_program_{{$lang->code}}_three_title"
                           class="form-control"
                           value="{{!empty($referral_program['three']['title']) ? $referral_program['three']['title'] : ''}}"
                    >
                </div>
                <div class="form-group">
                    <label for="referral_program_{{$lang->code}}[three][content]">{{ lang('c_referral_program') }}</label>
                    <textarea name="referral_program_{{$lang->code}}[three][content]"
                              id="referral_program_{{$lang->code}}_three_content " class="form-control editor">{{!empty($referral_program['three']['content']) ? $referral_program['three']['content'] : ''}}</textarea>
                </div>

                <hr/>
            </div>
        @endforeach
    @endif
</div>


{{--<div class="form-group">--}}
{{--    <label for="link_messenger">{{ lang('link_messenger') }}</label>--}}
{{--    <input type="text" name="link_messenger" id="link_messenger" value="{{ get_option('link_messenger') }}" class="form-control">--}}
{{--</div>--}}
{{--<div class="form-group">--}}
{{--    <label for="link_telegram">{{ lang('link_telegram') }}</label>--}}
{{--    <input type="text" name="link_telegram" id="link_telegram" value="{{ get_option('link_telegram') }}" class="form-control">--}}
{{--</div>--}}
{{--<div class="form-group hide">--}}
{{--    <label for="link_facebook">{{ lang('link_facebook') }}</label>--}}
{{--    <input type="text" name="link_facebook" id="link_facebook" value="{{ get_option('link_facebook') }}" class="form-control">--}}
{{--</div>--}}
