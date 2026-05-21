<style>
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        animation: fadeIn 0.2s ease;
    }
    .modal-container {
        margin: 30px auto;
        background: white;
        border-radius: 16px;
        height: 85vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        z-index: 1001;
        animation: slideUp 0.3s ease;
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

    .close-btn:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .modal-content {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    .modal-left {
        flex: 1;
        background: #fff;
        padding: 20px;
        display: flex;
        flex-direction: column;
        overflow: auto;
    }

    .modal-right {
        width: 320px;
        background: white;
        padding: 32px;
        border-left: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }

    .order-id {
        display: inline-block;
        background: #eff6ff;
        color: #2563eb;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 20px;
        flex-shrink: 0;
    }

    .info-grid {
        display: flex;
        width: 100%;
        gap: 10px;
        margin-bottom: 15px;
        flex-shrink: 0;
        flex-wrap: wrap;
    }

    .info-item {
        width: 48%;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .info-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        font-size: 15px;
        color: #1f2937;
        font-weight: 500;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        width: fit-content;
    }

    .status-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.pending::before {
        background: #f59e0b;
    }

    .status-badge.processing {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-badge.processing::before {
        background: #3b82f6;
    }

    .status-badge.completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.completed::before {
        background: #10b981;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e5e7eb;
        flex-shrink: 0;
    }

    .products-table-container {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: unset !important;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .products-table thead {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .products-table th {
        padding: 14px 16px;
        text-align: left;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .products-table th:last-child,
    .products-table td:last-child {
        text-align: right;
    }

    .products-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.2s ease;
    }

    .products-table tbody tr:last-child {
        border-bottom: none;
    }

    .products-table tbody tr:hover {
        background: #f9fafb;
    }

    .products-table td {
        padding: 6px;
        font-size: 14px;
        color: #1f2937;
    }
    .product-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .products-container {
        flex: 1;
        overflow-y: auto;
        padding-right: 8px;
    }

    .product-item {
        display: flex;
        gap: 16px;
        padding: 16px;
        background: white;
        border-radius: 12px;
        margin-bottom: 12px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .product-item:hover {
        border-color: #6366f1;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
    }

    .product-image {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        background: #f3f4f6;
        flex-shrink: 0;
    }

    .product-details {
        flex: 1;
        min-width: 0;
    }

    .product-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 6px;
        font-size: 15px;
    }

    .product-meta {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .product-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-quantity {
        color: #6b7280;
        font-size: 13px;
    }

    .product-price {
        color: #1f2937;
        font-weight: 600;
        font-size: 15px;
    }

    .summary-section {
        flex-shrink: 0;
    }

    .summary-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 20px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
        align-items: center;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-label {
        font-size: 14px;
        color: #6b7280;
        font-weight: 500;
    }

    .summary-value {
        font-size: 14px;
        color: #1f2937;
        font-weight: 600;
    }

    .summary-value.discount {
        color: #ef4444;
    }

    .summary-value.shipping {
        color: #10b981;
    }

    .total-row {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 2px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .total-label {
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
    }

    .total-value {
        font-size: 20px;
        font-weight: 700;
        color: #F466AA;
    }

    .action-buttons {
        margin-top: 24px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* Scrollbar cho products */
    .products-container::-webkit-scrollbar {
        width: 6px;
    }

    .products-container::-webkit-scrollbar-track {
        background: transparent;
    }

    .products-container::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    .products-container::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Scrollbar cho modal-right */
    .modal-right::-webkit-scrollbar {
        width: 6px;
    }

    .modal-right::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-right::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    .modal-right::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    .wrap_order{
        display: flex;
        justify-content: space-between;
    }

    @media (max-width: 768px) {
        .modal-container {
            width: 95%;
            height: 90vh;
        }

        .modal-content {
            flex-direction: column;
        }

        .modal-left {
            padding: 10px;
        }
        .modal-left {
            overflow-y: auto;
            max-height: 50vh;
        }
        .products-container{
            overflow-y:unset;
        }
        .modal-right {
            width: 100%;
            border-left: none;
            border-top: 1px solid #e5e7eb;
            padding: 20px;
        }
    }
</style>
<div class="modal-dialog modal-lg" style="width: 70%" id="modalOrder">
    <div class="modal-container">
        <div class="modal-header">
            <h2>{{lang('dt_view_transaction')}}</h2>
            <button class="close-btn" id="closeModal">&times;</button>
        </div>

        <div class="modal-content">
            <div class="modal-left">
                <div class="wrap_order">
                    <div class="order-id">#{{$dtData['reference_no']}}</div>
                    <div class="status">
                        <span class="status-badge" style="color: {{$dtData['status']['color']}};background: {{$dtData['status']['background']}}">{{$dtData['status']['name']}}</span>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">{{lang('dt_date_order')}}</span>
                        <span class="info-value">{{_dt($dtData['date'])}}</span>
                    </div>
                    <div class="info-item">
                        @php
                            if($dtData['check_leader'] == 1){
                                $level = '<span class="label label-success">Leader</span>';
                            } else {
                                $level = '<span class="label label-primary">'.(!empty($dtData['level']) ? 'F'.$dtData['level'] : '').'</span>';
                            }   
                        @endphp
                        <span class="info-label">{{lang('client')}}</span>
                        <span class="info-value">{{$dtData['customer']['fullname'] ?? ''}} {!! $level !!}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{lang('c_phone_company')}}</span>
                        <span class="info-value">{{$dtData['info_delivery']['phone_delivery'] ?? null}}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{lang('dt_name_delivery')}}</span>
                        <span class="info-value">{{$dtData['info_delivery']['name_delivery'] ?? null}}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{lang('dt_address_delivery')}}</span>
                        <span class="info-value">{{$dtData['info_delivery']['address_delivery'] ?? null}}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{lang('dt_payment_mode')}}</span>
                        <span class="info-value">{{$dtData['payment_mode']['name'] ?? ''}}</span>
                    </div>
                
                    @if(isset($warehouseStatus) && $warehouseStatus == 1)
                    <div class="info-item">
                        <span class="info-label">Người duyệt kho</span>
                        <span class="info-value" style="color: #2d812d;">{{$approverName ?? 'N/A'}}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ngày duyệt kho</span>
                        <span class="info-value">{{!empty($approvedAt) ? _dt($approvedAt) : 'N/A'}}</span>
                    </div>
                    @endif
                </div>

                <h3 class="section-title">{{lang('c_products')}} ({{count($dtData['items'])}})</h3>
                <div class="products-table-container">
                    <table class="products-table">
                        <thead>
                        <tr>
                            <th>{{lang('c_stt')}}</th>
                            <th>{{lang('products')}}</th>
                            <th>{{lang('dt_quantity')}}</th>
                            @if(isset($warehouseStatus) && $warehouseStatus == 1)
                            <th>Kho hàng</th>
                            @endif
                            <th>{{lang('dt_price')}}</th>
                            <th>{{lang('dt_amount')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!empty($dtData['items']))
                            @foreach($dtData['items'] as $key => $value)
                                @php
                                $variant_option = $value['product']['variant_option'] ?? [];
                                $title_variant = $variant_option['category']['name'] ?? '';
                                $value_variant = $variant_option['name'] ?? '';
                                @endphp
                                <tr>
                                    <td class="text-center">{{(++$key)}}</td>
                                    <td>
                                        <div class="product-cell">
                                            <a href="{{$value['product']['image']}}" data-lightbox="customer-profile" class="display-block mbot5">
                                                <img src="{{$value['product']['image']}}" alt="Product" class="product-image">
                                            </a>
                                            <div class="product-info">
                                                <div class="product-name">J{{$value['product']['name']}}</div>
                                                <div class="product-meta">{{$title_variant}}: {{$value_variant}}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="product-quantity text-center">{{$value['quantity']}}</td>
                                    @if(isset($warehouseStatus) && $warehouseStatus == 1)
                                    <td>
                                        @php
                                            $pId = $value['product']['id'] ?? $value['product_id'] ?? 0;
                                        @endphp
                                        @if(isset($warehouseLogs[$pId]) && count($warehouseLogs[$pId]) > 0)
                                            @foreach($warehouseLogs[$pId] as $log)
                                                <div style="font-size: 13px; color: #4338ca; margin-bottom: 3px;">Lô: {{date('d/m/Y', strtotime($log->import_date))}}: <b>{{$log->qty_take}}</b></div>
                                            @endforeach
                                        @else
                                            <div style="font-size: 12px; color: #9ca3af;">Không có dữ liệu kho</div>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="product-price">{{formatMoney($value['price'])}} đ</td>
                                    <td class="product-price">{{formatMoney($value['total'])}} đ</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-right">
                <div class="summary-section">
                    <h3 class="summary-title">{{lang('dt_total_order')}}</h3>

                    <div class="summary-row">
                        <span class="summary-label">{{lang('dt_total_new')}}</span>
                        <span class="summary-value">{{formatMoney($dtData['total'])}}đ</span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-label">{{lang('dt_total_promotion')}}</span>
                        <span class="summary-value discount">-{{formatMoney($dtData['total_promotion'])}}đ</span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-label">{{lang('Chiết khấu')}}( {{$dtData['percent_discount']}} %)</span>
                        <span class="summary-value discount">-{{formatMoney($dtData['total_discount'])}}đ</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">{{lang('Thuế VAT')}}( {{$dtData['vat']}} %)</span>
                        <span class="summary-value">{{formatMoney($dtData['total_vat'])}}đ</span>
                    </div>

                    <div class="summary-row hide">
                        <span class="summary-label">{{lang('dt_haru_xu')}}</span>
                        <span class="summary-value discount">
                            <div>-{{formatMoney($dtData['point'])}}</div>
                            <div>~{{formatMoney($dtData['point_total'])}}đ</div>
                        </span>
                    </div>

                    <div class="summary-row hide">
                        <span class="summary-label">{{lang('dt_cost_delivery')}}</span>
                        <span class="summary-value">{{formatMoney($dtData['cost_delivery'])}}đ</span>
                    </div>

                    <div class="summary-row hide">
                        <span class="summary-label">{{lang('dt_discount_cost_delivery')}}</span>
                        <span class="summary-value shipping">-{{formatMoney($dtData['discount_cost_delivery'])}}đ</span>
                    </div>

                    <div class="total-row">
                        <span class="total-label">{{lang('dt_grand_total')}}</span>
                        <span class="total-value">{{formatMoney($dtData['grand_total'])}}đ</span>
                    </div>
                    <div class="total-row">
                        <span class="" style="color: black !important;">Leader {{$dtData['customer_leader']['fullname'] ?? ''}}</span>
                    </div>
                    <div style="display: flex;align-items: center;justify-content: space-between;">
                        <span class="total-label">{{lang('Chiết khấu Leader')}}</span>
                        <span class="total-value" style="color: green !important;">{{formatMoney($dtData['total_discount_leader'])}}đ</span>
                    </div>
                    @if(!empty($dtData['customer_f1']))
                    <div class="total-row">
                        <span class="" style="color: black !important;">F2 {{$dtData['customer_f1']['fullname'] ?? ''}}</span>
                    </div>
                    <div style="display: flex;align-items: center;justify-content: space-between;"
                        <span class="total-label">{{lang('Chiết khấu F2')}}</span>
                        <span class="total-value" style="color: green !important;">{{formatMoney($dtData['total_discount_customer_f1'])}}đ</span>
                    </div>
                    @endif
                    <div class="total-row">
                        <span class="total-label">{{lang('Tích lũy Leader')}}</span>
                        <span class="total-value" style="color: red !important;">{{formatMoney($dtData['total_accumulate'])}}đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#closeModal').click(function() {
        $('#dtModal').modal('hide');
    });
</script>
