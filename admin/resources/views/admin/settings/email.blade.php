<h4>Email quản trị nhận khi có đơn hàng mới</h4>
<hr/>
<div class="form-group">
    <label for="admin_email_orders">{{ lang('admin_email_orders') }}</label>
    <input type="text" name="admin_email_orders" id="admin_email_orders" value="{{!empty(get_option('admin_email_orders')) ? get_option('admin_email_orders') : ''}}" class="form-control">
</div>
<div class="form-group">
    <label for="cc_admin_email_orders">{{ lang('cc_admin_email_orders') }}</label>
    <input type="text" name="cc_admin_email_orders" id="cc_admin_email_orders" value="{{!empty(get_option('cc_admin_email_orders')) ? get_option('cc_admin_email_orders') : ''}}" class="form-control">
</div>