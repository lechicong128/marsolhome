<form id="InvoiceForm" action="admin/invoice/submitDetailInvoice" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog modal-lg" style="width: 70%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="show_error" style="color: red"></div>
                        <div class="form-group">
                            <label for="reference_no">{{lang('Số hóa đơn hệ thống')}}</label>
                            <input type="text" name="reference_no" class="form-control reference_no" value="Theo hệ thống" readonly>
                        </div>
                        <div class="form-group">
                            <label for="date">{{lang('Ngày hóa đơn')}}</label>
                            <input type="text" name="date_invoice" class="form-control date_invoice" value="{{date('d/m/Y')}}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="customer_id">{{lang('Khách hàng')}}</label>
                            <select class="customer_id select2" id="customer_id" name="customer_id" onchange="getTransaction()">
                                    data-placeholder="Chọn ..." name="customer_id">
                                <option></option>
                                @if(!empty($transaction))
                                    @if(!empty($transaction['customer']))
                                    <option value="{{$transaction['customer']['id']}}"
                                    selected>{{$transaction['customer']['fullname']}} - {{!empty($transaction['customer']['phone']) ? $transaction['customer']['phone'] : '' }}</option>
                                    @endif
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="transaction_id">{{lang('Đơn hàng')}}</label>
                                <select class="transaction_id select2" multiple id="transaction_id" onchange="getTransactionItem()"
                                    data-placeholder="Chọn ..." name="transaction_id">
                                <option></option>
                                @if(!empty($transaction))
                                    <option value="{{$transaction['id']}}"
                                    selected>{{$transaction['reference_no']}} - {{formatMoney($transaction['grand_total'])}} VNĐ</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <table class="table dataTable table_item table-bordered">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Đơn hàng</th>
                                        <th>Mã hàng</th>
                                        <th>Tên hàng</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Tổng tiền</th>
                                        <th>Tiền giảm giá</th>
                                        <th>Thành tiền</th>
                                        <th>Thuế</th>
                                        <th>Tiền thuế</th>
                                        <th>Tổng cộng</th>
                                    </tr>
                                </thead>
                                <tbody class="html_item">

                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12 hide" style="margin-top: 10px">
                            <div class="form-group">
                                <div style="color: red">Tổng tiền : <span class="subtotal">0</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary waves-effect waves-light"
                        type="submit">{{lang('dt_save')}}</button>
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">{{lang('dt_close')}}</button>
            </div>
        </div>
    </div>
</form>
<script>
    $(document).ready(function () {
        searchAjaxSelect2('#customer_id', 'admin/category/searchCustomer', 0, {type_client: -1})
        searchAjaxSelect2('#transaction_id', 'admin/category/searchTransactionInvoice', 0, {customer_id: $('#customer_id').val()})
        $('#transaction_id').trigger('change');
    })
    function getTransaction() {
        $('#transaction_id').empty();
        $('#transaction_id').append('<option value="">Chọn đơn hàng</option>');
        var customer_id = $('#customer_id').val();
        searchAjaxSelect2('#transaction_id', 'admin/category/searchTransactionInvoice', 0, {customer_id: customer_id})
    }

    function getTransactionItem() {
        var transaction_id = $('#transaction_id').val();
        if(transaction_id == '') {
            $('.html_item').html('');
            return;
        }
        $.ajax({
            url: 'admin/category/searchTransactionItem',
            type: 'POST',
            data: {transaction_id: transaction_id},
        })
            .done(function (data) {
                if (data.result) {
                    html_item = '';
                    $.each(data.data, function(index, item) {
                        html_item += `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${item.data_transaction.reference_no}
                                    <input type="hidden" name="transaction_id[]" value="${item.data_transaction.id}">
                                    <input type="hidden" name="transaction_item_id[]" value="${item.id}">
                                    <input type="hidden" name="product_id[]" value="${item.product.id}">
                                    <input type="hidden" name="variant_id[]" value="${item.variant_id}">
                                    <input type="hidden" name="quantity[]" value="${item.quantity}">
                                    <input type="hidden" name="total[]" value="${item.total}">
                                    <input type="hidden" name="price[]" value="${item.price}">
                                    <input type="hidden" name="discount_item[]" value="${item.discount_item}">
                                    <input type="hidden" name="vat[]" value="${item.vat}">
                                    <input type="hidden" name="total_amount_item[]" value="${item.total_amount_item}">
                                    <input type="hidden" name="total_amount_vat_item[]" value="${item.total_amount_vat_item}">
                                    <input type="hidden" name="total_amount[]" value="${item.total_amount_item + item.total_amount_vat_item}">
                                    <input type="hidden" name="code_item[]" value="${item.product.code}">
                                    <input type="hidden" name="name_item[]" value="${item.product.name}">
                                    <input type="hidden" name="unit_id[]" value="${item.product.unit.id}">
                                    <input type="hidden" name="unit_name[]" value="${item.product.unit.name}">
                                </td>
                                <td>${item.product.code}</td>
                                <td>${item.product.name}</td>
                                <td class="text-center">${formatNumber(item.quantity)}</td>
                                <td class="text-right">${formatMoney(item.price)}</td>
                                <td class="text-right">${formatMoney(item.total)}</td>
                                <td class="text-right">${formatMoney(item.discount_item)}</td>
                                <td class="text-right">${formatMoney(item.total_amount_item)}</td>
                                <td class="text-center">${(item.vat)}</td>
                                <td class="text-right">${formatMoney(item.total_amount_vat_item)}</td>
                                <td class="text-right">${formatMoney(item.total_amount_item + item.total_amount_vat_item)}</td>
                            </tr>
                        `;
                    });
                    grand_total_item = 0;
                    grand_total_vat_item = 0;
                    $.each(data.data, function(index, item) {
                        grand_total_vat_item += item.total_amount_vat_item;
                        grand_total_item += item.total_amount_item + item.total_amount_vat_item;
                    });
                    html_item += `
                        <tr>
                            <td colspan="10">Tổng cộng</td>
                            <td class="text-right">${formatMoney(grand_total_vat_item)}</td>
                            <td class="text-right">${formatMoney(grand_total_item)}</td>
                        </tr>
                    `;
                    $('.html_item').html(html_item);
                    $('.subtotal').text(formatMoney(grand_total_item));
                } else {
                    $('.html_item').html('');
                }

            })
            .fail(function (err) {
                $('.html_item').html('');
            });
    }
    $("#InvoiceForm").validate({
        rules: {
        },
        messages: {
        },
        submitHandler: function (form) {
            var url = form.action;
            var form = $(form),
                formData = new FormData(),
                formParams = form.serializeArray();

            $.each(form.find('input[type="file"]'), function (i, tag) {
                $.each($(tag)[0].files, function (i, file) {
                    formData.append(tag.name, file);
                });
            });
            $.each(formParams, function (i, val) {
                formData.append(val.name, val.value);
            });

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
            })
                .done(function (data) {
                    if (data.result) {
                        oTable.draw();
                        $('.modal-dialog .close').trigger('click');
                        alert_float('success',data.message);
                    } else {
                        $(".show_error").html(data.message);
                        alert_float('error',data.message);
                    }
                })
                .fail(function (err) {
                    htmlError = '';
                    for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                        htmlError += `<div>${message}</div>`;
                    }
                    $(".show_error").html(htmlError);
                    alert_float('error',htmlError);
                });
            return false;
        }
    });
</script>
