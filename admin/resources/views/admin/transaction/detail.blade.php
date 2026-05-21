<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f4f4f4;
        padding: 20px;
    }

    /* Modal Custom Styling */
    .modal-container {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 1px solid #eee;
    }
    .modal-header {
        padding: 15px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }
    .modal-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
    }

    .close-btn {
        background: #f3f4f6;
        border: none;
        color: #6b7280;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        font-size: 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        position: absolute;
        right: 25px;
    }

    .modal-content-inner {
        padding: 25px;
    }
    
    /* Form Styling */
    .form-group label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 6px;
        display: block;
    }

    .form-control:focus {
        border-color: #2f80ed;
        box-shadow: 0 0 0 2px rgba(47, 128, 237, 0.1);
    }

    /* Table Styling */
    .table-custom {
        margin-top: 15px;
    }
    .table-custom thead th {
        border-bottom: 1px solid #eee !important;
        color: #828282;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 600;
        padding: 10px 8px;
        background: #fafafa;
    }
    .table-custom tbody td {
        border-top: 1px solid #f5f5f5 !important;
        padding: 10px 8px;
        vertical-align: middle;
    }
    
    /* Input widths in table */
    .item-id-input { width: 80px; }
    .item-qty-input { width: 70px; text-align: center; }
    .item-price-input { width: 110px; text-align: right; }
    .item-total-display { font-weight: 600; color: #1a1a1a; text-align: right; display: block; }

    /* Sidebar Summary */
    .summary-box {
        background: #fff;
        border-left: 1px solid #eee;
        padding-left: 20px;
        height: 100%;
    }
    .summary-box h4 {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #1a1a1a;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 13px;
    }
    .summary-label { color: #828282; }
    .summary-value { font-weight: 600; }
    
    .grand-total-box {
        border-top: 1px solid #eee;
        padding-top: 15px;
        margin-top: 15px;
    }
    .grand-total-label { font-size: 15px; font-weight: 700; }
    .grand-total-value {
        font-size: 18px;
        font-weight: 700;
        color: #d63384;
    }

    /* Action Buttons */
    .btn-modern {
        border-radius: 6px;
        font-weight: 600;
        padding: 8px 16px;
        font-size: 13px;
        transition: all 0.2s;
    }
    .btn-add {
        background: #f2f2f2;
        color: #4f4f4f;
        border: none;
        margin-top: 5px;
    }
    .btn-add:hover { background: #e0e0e0; }
    .btn-save {
        background: #2f80ed;
        color: #fff;
        border: none;
        width: 100%;
        margin-top: 15px;
        padding: 12px;
    }
    .btn-save:hover { background: #2d72d2; color: #fff; }
    
    .remove-row {
        color: #eb5757;
        cursor: pointer;
        opacity: 0.6;
        font-size: 14px;
    }
    .remove-row:hover { opacity: 1; }
</style>
<form id="TransactionForm" action="admin/transaction/submitTransaction/0" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
<div class="modal-dialog modal-lg" style="width: 70%" id="modalOrder">
    <div class="modal-container">
        <div class="modal-header">
            <h2>{{$title}}</h2>
            <button class="close-btn" id="closeModal" type="button">&times;</button>
        </div>

        <div class="modal-content" style="overflow-y: auto; height: 700px;">
            <div class="row">
                <!-- Cột trái: Nhập liệu sản phẩm -->
                <div class="col-md-9">
                    <!-- Thông tin chung -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Mã đơn hàng</label>
                                <input type="text" id="reference_no" class="form-control reference_no" name="reference_no" readonly placeholder="Hệ thống tự tạo...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ngày tạo đơn</label>
                                <input type="text" id="date_new" class="form-control date_new datetimepicker" name="date_new" value="{{date('d/m/Y H:i')}}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="customer_id">Khách hàng</label>
                                <select class="customer_id select2" id="customer_id" onchange="getDiscountCustomer();"
                                        data-placeholder="Chọn ..." name="customer_id">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Họ và tên người nhận</label>
                                <input type="text" id="name_delivery" class="form-control name_delivery" name="name_delivery" placeholder="Nhập họ và tên người nhận">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="text" id="phone_delivery" class="form-control phone_delivery" name="phone_delivery" placeholder="Nhập số điện thoại">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" id="email_delivery" class="form-control email_delivery" name="email_delivery" placeholder="Nhập email">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Địa chỉ</label>
                                <textarea id="address_delivery" class="form-control address_delivery" name="address_delivery" rows="3" placeholder="Nhập địa chỉ"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="product_id">Sản phẩm</label>
                            <select class="product_id select2" id="product_id" onchange="getProductVariant()"
                                    data-placeholder="Chọn ..." name="product_id">
                                <option></option>
                            </select>
                        </div>
                    </div>
                    <!-- Bảng sản phẩm -->
                    <div class="table-responsive">
                        <table class="table table-custom" id="orderTable">
                            <thead>
                                <tr>
                                    <th width="100">STT</th>
                                    <th class="text-center" width="100">Mã sản phẩm</th>
                                    <th class="text-center">Tên sản phẩm</th>
                                    <th class="text-center" width="150">Biến thể</th>
                                    <th class="text-center" width="100">Số lượng</th>
                                    <th class="text-right" width="150">Đơn giá</th>
                                    <th class="text-right" width="120">Thành tiền</th>
                                    <th class="text-center" width="50"></th>
                                </tr>
                            </thead>
                            <tbody>
                             
                            </tbody>
                        </table>
                    </div>
                    
                </div>

                <!-- Cột phải: Tổng kết đơn hàng -->
                <div class="col-md-3">
                    <div class="summary-box">
                        <h4>Tổng Đơn Hàng</h4>
                        
                        <div class="summary-item">
                            <span class="summary-label">Tạm tính</span>
                            <span class="summary-value" id="subTotal">0đ</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Chiết khấu</span>
                            <span class="summary-value" id="discountTotal">0đ</span>
                        </div>

                        <div class="summary-item">
                            <span class="summary-label">Thuế VAT ({{$vat}}%)</span>
                            <span class="summary-value" id="taxTotal">0đ</span>
                        </div>

                        <div class="grand-total-box">
                            <div class="summary-item">
                                <span class="grand-total-label">Tổng cộng</span>
                            </div>
                            <div class="text-right">
                                <span class="grand-total-value" id="grandTotal">0đ</span>
                            </div>
                        </div>

                        <button class="btn btn-modern btn-save" id="btnSave" type="submit">
                            Lưu đơn hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
<script>
    $(document).ready(function (){
        searchAjaxSelect2('#customer_id','admin/category/searchCustomer');
        searchAjaxSelect2('#product_id','api/category/getListProduct')
    })
    function optionVariant(variant_option) {
        var option = `<option value="">Chọn...</option>`;
        $.each(variant_option, function (index, value) {
            option += `<option value="${value.id}" data-price="${value.price}">${value.name}</option>`;
        });
        return option;
    }
    function getProductVariant() {
        var dataProduct = $('#product_id').select2('data');

        if (!dataProduct || !dataProduct.length || !dataProduct[0]) return;

        var item = dataProduct[0];

        var variant_option = item.variant_option || [];
        var option = optionVariant(variant_option);

        addItem(item, option);

        $('#product_id').empty().trigger('change');
    }
    var counter = 0;
    var percent_discount = 0;
    var vat = {{$vat}};
    function addItem(item, option) {
        var item = {
            product_id: item.id,
            product_name: item.name,
            product_code: item.code,
            product_price: item.price,
            product_variant_option: item.variant_option,
            product_id_variant: item.id_variant,
        };
        if(item.product_id_variant > 0){
            var variantOption = `<td><select class="form-control variant_id" name="variant_id[]" onchange="total();getPriceVariant(this.value);">
                        ${option}
                </select></td>`;
        } else {
            var variantOption = `<td> <input type="hidden" class="form-control variant_id" name="variant_id[]" value="0"></td>`;
        }
        $('#orderTable tbody').append(`
            <tr>
                <td class="text-center stt"></td>
                <td>
                    <input type="hidden" class="form-control product_id" name="product_id[]" value="${item.product_id}">
                    <input type="hidden" class="form-control product_id_variant" name="product_id_variant[]" value="${item.product_id_variant}">
                    ${item.product_code}
                </td>
                <td>${item.product_name}</td>
                ${variantOption}
                <td><input type="text" class="form-control quantity" onchange="formatNumBerKeyChange(this);total()" name="quantity[]" value="1"></td>
                <td class="text-right"><input type="text" class="form-control number-format price_new" readonly onchange="formatNumBerKeyChange(this);total()" name="price[]" value="${formatMoney(item.product_price)}">
                <td class="text-right"><span class="item-total-display">0đ</span></td>
                <td class="text-center"><span class="glyphicon glyphicon-trash remove-row" onclick="removeItem(this)"></span></td>
            </tr>
        `);
        counter++;
        total();
    }
    function getPriceVariant(variant_id) {
        total();
    }
    function removeItem(item) {
        $(item).parent().parent().remove();
        total();
    }
    function total(){
        var grand_total = 0;
        stt = 0;
        $('#orderTable tbody tr').each(function() {
            stt++;
            $(this).find('.stt').text(stt);
            var product_id_variant = $(this).find('input[name="product_id_variant[]"]').val() ?? 0;
            if(product_id_variant == 1){
                $(this).find('.price_new').val(formatMoney($(this).find('option[value="' + $(this).find('select[name="variant_id[]"]').val() + '"]').data('price')));
            } 
            var quantity = intVal($(this).find('input[name="quantity[]"]').val() ?? 0);
            var price = intVal($(this).find('input[name="price[]"]').val() ?? 0);
            total_item = quantity * price;
            grand_total += total_item;
            $(this).find('.item-total-display').text(formatMoney(total_item));
        });

        discountTotal = grand_total * percent_discount / 100;
        $('#discountTotal').text(formatMoney(discountTotal));
        $('#subTotal').text(formatMoney(grand_total));
        grand_total = grand_total - discountTotal;
        total_tax = grand_total * vat / 100;
        $('#taxTotal').text(formatMoney(total_tax));
        grand_total = grand_total + total_tax;
        $('#grandTotal').text(formatMoney(grand_total));
    }

    function getDiscountCustomer() {
        var customer_id = $('#customer_id').val();
        $.ajax({
            url: 'admin/transaction/getDiscountCustomer',
            type: 'POST',
            dataType: 'JSON',
            data: {customer_id: customer_id},
        })
        .done(function (data) {
            percent_discount = data.data.percent_discount;
            if(percent_discount == undefined || percent_discount == null || percent_discount == 0) {
                alert_float('error','Khách hàng nhập đầy đủ thông tin không hợp lệ!');
                $('#customer_id').empty();
                percent_discount = 0;
                return false;
            }
            subTotal = $('#subTotal').text();
            $('#discountTotal').text(formatMoney(subTotal * percent_discount / 100));
            $('#grandTotal').text(formatMoney(subTotal - subTotal * percent_discount / 100));
            total();
        })
        .fail(function (err) {
            alert_float('error',err.responseJSON.message);
            return false;
        });
    }
    $("#TransactionForm").validate({
        rules: {
            date_new: {
                required: true,
            },
            customer_id: {
                required: true,
            },
            name_delivery: {
                required: true,
            },
            phone_delivery: {
                required: true,
            },
            email_delivery: {
                required: true,
            },
            address_delivery: {
                required: true,
            },
        },
        messages: {
            date_new: {
                required: "{{lang('dt_required')}}",
            },
            customer_id: {
                required: "{{lang('dt_required')}}",
            },
            name_delivery: {
                required: "{{lang('dt_required')}}",
            },
            phone_delivery: {
                required: "{{lang('dt_required')}}",
            },
            email_delivery: {
                required: "{{lang('dt_required')}}",
            },
            address_delivery: {
                required: "{{lang('dt_required')}}",
            },
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
                        $('#dtModal').modal('hide');
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
    $('#closeModal').click(function() {
            $('#dtModal').modal('hide');
        });
</script>
