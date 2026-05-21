<style>
    .nav.nav-tabs > li.tab-btn.active > a {
        background-color: #3a94ef;
        color: white !important;
        border: 0;
        border-radius: 10px;
    }
</style>
<ul class="nav nav-tabs nav-justified hide">
    @if(!empty($language))
        @foreach($language as $lang)
            <li class="tab-btn {{($lang->is_default == 1) ? 'active' : ''}}">
                <a data-toggle="tab" href="#tab-lang-{{$lang->code_system}}">{{$lang->name}}</a>
            </li>
        @endforeach
    @endif
</ul>   
<div class="row">
    <div class="col-md-12">
        
    <div class="form-group">
        <label>{{lang('Apple test')}}</label><br>
        <div class="radio radio-custom radio-inline mbot10">
            <input type="radio" id="apple_test1" name="apple_test" value="1" {{get_option('apple_test') ? 'checked' : ''}}>
            <label for="apple_test1">{{lang('ON')}}</label>
        </div>
        <div class="radio radio-custom radio-inline mbot10">
            <input type="radio" id="apple_test0" name="apple_test" value="0" {{empty(get_option('apple_test')) ? 'checked' : ''}}>
            <label for="apple_test0">{{lang('OFF')}}</label>
        </div>
    </div>
</div>
<hr/>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="title_introduce_1">Title Introduce 1</label>
            <input type="text" name="title_introduce_1" id="title_introduce_1" value="{{get_option('title_introduce_1')}}" class="form-control">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="sub_title_introduce_1">Sub-title Introduce 1</label>
            <textarea name="sub_title_introduce_1" id="sub_title_introduce_1" class="form-control">{{get_option('sub_title_introduce_1')}}</textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="title_introduce_2">Title Introduce 2</label>
            <input type="text" name="title_introduce_2" id="title_introduce_2" value="{{get_option('title_introduce_2')}}" class="form-control">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="sub_title_introduce_2">Sub-title Introduce 2</label>
            <textarea name="sub_title_introduce_2" id="sub_title_introduce_2" class="form-control">{{get_option('sub_title_introduce_2')}}</textarea>
        </div>
    </div>
</div>
<div class="form-group">
    <label for="length_table">{{lang('c_length_table')}}</label>
    <input type="text" name="length_table" id="length_table"  value="{{get_option('length_table')}}" class="form-control">
</div>

<div class="form-group">
    <label for="google_api_key">{{lang('c_google_api_key')}}</label>
    <input type="text" name="google_api_key" id="google_api_key"  value="{{get_option('google_api_key')}}" class="form-control">
</div>
<hr/>

<div class="form-group">
    <label for="facebook_app_id">{{lang('c_facebook_app_id')}}</label>
    <input type="text" name="facebook_app_id" id="facebook_app_id"  value="{{get_option('facebook_app_id')}}" class="form-control">
</div>
<div class="form-group">
    <label for="facebook_secret">{{lang('c_facebook_secret')}}</label>
    <input type="text" name="facebook_secret" id="facebook_secret"  value="{{get_option('facebook_secret')}}" class="form-control">
</div>
<div class="form-group">
    <label for="version_sdk_facebook">{{lang('c_version_sdk_facebook')}}</label>
    <input type="text" name="version_sdk_facebook" id="version_sdk_facebook"  value="{{get_option('version_sdk_facebook')}}" class="form-control">
</div>


<hr/>
<div class="form-group">
    <label>{{lang('show_button_next_login')}}</label><br>
    <div class="radio radio-custom radio-inline mbot10">
        <input type="radio" id="show_button_next_login" name="show_button_next_login" value="1" {{get_option('show_button_next_login') ? 'checked' : ''}}>
        <label for="show_button_next_login">{{lang('ON')}}</label>
    </div>
    <div class="radio radio-custom radio-inline mbot10">
        <input type="radio" id="show_button_next_login_off" name="show_button_next_login" value="0" {{empty(get_option('show_button_next_login')) ? 'checked' : ''}}>
        <label for="show_button_next_login_off">{{lang('OFF')}}</label>
    </div>
</div>


