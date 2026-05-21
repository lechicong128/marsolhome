<div class="form-group">
    <label for="version_app">Version Apple</label>
    <input type="text" name="version_app" id="version_app" value="{{(get_option('version_app'))}}" class="form-control">
</div>
<div class="form-group">
    <label for="link_apple">Link Apple</label>
    <input type="text" name="link_apple" id="link_apple" value="{{(get_option('link_apple'))}}" class="form-control">
</div>
<div class="form-group">
    <label for="version_app_android">Version App Android</label>
    <input type="text" name="version_app_android" id="version_app_android"
        value="{{(get_option('version_app_android'))}}" class="form-control">
</div>
<div class="form-group">
    <label for="link_android">Link App Android</label>
    <input type="text" name="link_android" id="link_android" value="{{(get_option('link_android'))}}" class="form-control">
</div>
<style>
    .nav.nav-tabs>li.tab-btn.active>a {
        background-color: #3a94ef;
        color: white !important;
        border: 0;
        border-radius: 10px;
    }
</style>
<!-- <ul class="nav nav-tabs nav-justified">
    @if(!empty($language))
        @foreach($language as $lang)
            <li class="tab-btn {{($lang->is_default == 1) ? 'active' : ''}}">
                <a data-toggle="tab" href="#tab-lang-{{$lang->code}}">{{$lang->name}}</a>
            </li>
        @endforeach
    @endif
</ul> -->
<div class="tab-content m-t-40" style="padding-left:0px;padding-right:0px;">
    @if(!empty($language))
        @foreach($language as $lang)
                <div id="tab-lang-{{$lang->code}}" class="tab-pane fade {{($lang->is_default == 1) ? 'in active' : ''}}">
                <div class="form-group">
                    <label for="{{ $lang->code }}_note_version_app">{{lang('note_version_app')}}</label>
                    <textarea class="{{ $lang->code }}_note_version_app form-control"
                        name="{{ $lang->code }}_note_version_app">{{get_option($lang->code . '_note_version_app')}}</textarea>
                </div>
            </div>
        @endforeach
    @endif
</div>