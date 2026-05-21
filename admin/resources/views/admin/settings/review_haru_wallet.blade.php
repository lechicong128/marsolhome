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
    <label for="number_coins_received_review">{{ lang('number_coins_received_review') }}</label>
    <input type="text" name="number_coins_received_review" id="number_coins_received_review" value="{{!empty(get_option('number_coins_received_review')) ? formatMoney(get_option('number_coins_received_review')) : 0}}" onkeyup="formatNumBerKeyChange(this)" class="form-control">
</div>
@if(!empty($language))
        @foreach($language as $lang)
            <div id="tab-lang-{{$lang->code}}" class="tab-pane fade {{($lang->is_default == 1) ? 'in active' : ''}}">
                @php
                    $content_review = get_option('content_review_'.$lang->code);
                    $content_review = json_decode($content_review, true);
                @endphp

                <div class="form-group">
                    <label for="content_review_{{$lang->code}}[title]">{{ lang('dt_title_review') }}</label>
                    <input name="content_review_{{$lang->code}}[title]"
                           id="content_review_{{$lang->code}}_title" class="form-control editor" type="text" value="{{!empty($content_review['title']) ? $content_review['title'] : ''}}">
                </div>
                <div class="form-group">
                    <label for="content_review_{{$lang->code}}[content]">{{ lang('dt_content_review_noti') }}</label>
                    <textarea name="content_review_{{$lang->code}}[content]"
                              id="content_review_{{$lang->code}}_content" class="form-control editor">{{!empty($content_review['content']) ? $content_review['content'] : ''}}</textarea>
                </div>
                <hr/>
                <div class="form-group">
                    <label for="content_review_{{$lang->code}}[content_affiliate]">{{ lang('c_review_program_noti') }}</label>
                    <textarea name="content_review_{{$lang->code}}[content_affiliate]"
                              id="content_review_{{$lang->code}}_content_affiliate" class="form-control editor">{{!empty($content_review['content_affiliate']) ? $content_review['content_affiliate'] : ''}}</textarea>
                </div>
                <hr/>
            </div>
        @endforeach
    @endif
</div>
