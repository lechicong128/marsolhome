<div class="form-group">
    <label for="exchange_rate_haru_wallet">{{ lang('exchange_rate_haru_wallet') }}</label>
    <input type="text" name="exchange_rate_haru_wallet" id="exchange_rate_haru_wallet" value="{{!empty(get_option('exchange_rate_haru_wallet')) ? formatMoney(get_option('exchange_rate_haru_wallet')) : 0}}" onkeyup="formatNumBerKeyChange(this)" class="form-control">
</div>
<div class="form-group">
    <label for="withdrawal_limit">{{ lang('withdrawal_limit') }}</label>
    <input type="text" name="withdrawal_limit" id="withdrawal_limit" value="{{!empty(get_option('withdrawal_limit')) ? formatMoney(get_option('withdrawal_limit')) : 0}}" onkeyup="formatNumBerKeyChange(this)" class="form-control">
</div>