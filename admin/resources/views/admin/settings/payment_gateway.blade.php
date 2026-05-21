<h4>{{ lang('Pay2s') }}</h4>
<div class="form-group">
    <label for="pay2s_account_bank">{{ lang('pay2s_account_bank') }}</label>
    <input type="text" name="pay2s_account_bank" id="pay2s_account_bank" value="{{ get_option('pay2s_account_bank') }}" class="form-control">
</div>
<div class="form-group">
    <label for="pay2s_account_number">{{ lang('pay2s_account_number') }}</label>
    <input type="text" name="pay2s_account_number" id="pay2s_account_number" value="{{ get_option('pay2s_account_number') }}" class="form-control">
</div>
<div class="form-group">
    <label for="pay2s_account_name">{{ lang('pay2s_account_name') }}</label>
    <input type="text" name="pay2s_account_name" id="pay2s_account_name" value="{{ get_option('pay2s_account_name') }}" class="form-control">
</div>
<div class="form-group">
    <label for="pay2s_account_bank_short">{{ lang('pay2s_account_bank_short') }}</label>
    <input type="text" name="pay2s_account_bank_short" id="pay2s_account_bank_short" value="{{ get_option('pay2s_account_bank_short') }}" class="form-control">
</div>
<div class="form-group">
    <label for="pay2s_account_bank_long">{{ lang('pay2s_account_bank_long') }}</label>
    <input type="text" name="pay2s_account_bank_long" id="pay2s_account_bank_long" value="{{ get_option('pay2s_account_bank_long') }}" class="form-control">
</div>

<div class="form-group">
    <label for="logo">{{lang('pay2s_logo_bank')}}</label>
    <input type="file" name="pay2s_logo_bank" id="pay2s_logo_bank" class="filestyle image" data-buttonbefore="true">
    <div style="display: flex;justify-content:center;margin-top: 5px" class="show_image">
        <?php
        $imgLogo = get_option('pay2s_logo_bank');
        $imgLogo = !empty($imgLogo) ? $imgLogo : imgCameraDefault();
        ?>
        <img src="{{asset($imgLogo)}}" data-imgdefault="{{$imgLogo}}" alt="{{lang('pay2s_logo_bank')}}" class="img-responsive img-black" style="width: 150px;height: 150px">
    </div>
</div>
<hr/>

<div class="form-group">
    <label for="pay2s_account_number_show">{{ lang('pay2s_account_number_show') }}</label>
    <input type="text" name="pay2s_account_number_show" id="pay2s_account_number_show" value="{{ get_option('pay2s_account_number_show') }}" class="form-control">
</div>
<div class="form-group">
    <label for="pay2s_account_name_show">{{ lang('pay2s_account_name_show') }}</label>
    <input type="text" name="pay2s_account_name_show" id="pay2s_account_name_show" value="{{ get_option('pay2s_account_name_show') }}" class="form-control">
</div>
</hr>

<div class="form-group">
    <label for="pay2s_token_webhook_transaction">{{ lang('pay2s_token_webhook_transaction') }}</label>
    <input type="password" name="pay2s_token_webhook_transaction" id="pay2s_token_webhook_transaction" value="{{ get_option('pay2s_token_webhook_transaction') }}" class="form-control">
</div>
{{--<div class="form-group">--}}
{{--    <label for="pay2s_token_webhook">{{ lang('pay2s_token_webhook') }}</label>--}}
{{--    <input type="password" name="pay2s_token_webhook" id="pay2s_token_webhook" value="{{ get_option('pay2s_token_webhook') }}" class="form-control">--}}
{{--</div>--}}
<div class="form-group">
    <label for="pay2s_token_video_unlock">{{ lang('Token webhook thanh toán Elearning') }}</label>
    <input type="password" name="pay2s_token_video_unlock" id="pay2s_token_video_unlock" value="{{ get_option('pay2s_token_video_unlock') }}" class="form-control">
</div>
