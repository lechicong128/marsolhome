<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title">
        <i class="fa fa-calendar"></i> Chi tiết lịch hẹn
        <span class="text-primary">#{{ $booking->booking_code }}</span>
    </h4>
</div>
<div class="modal-body">

    <!-- Thông tin khách hàng & lịch hẹn -->
    <div class="row">
        <div class="col-md-6">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-user"></i> Thông tin khách hàng
            </h5>
            <table class="table table-condensed table-borderless">
                <tr>
                    <td style="width:130px;color:#888">Tên khách hàng</td>
                    <td><strong>{{ $booking->customer_name }}</strong></td>
                </tr>
                <tr>
                    <td style="color:#888">Số điện thoại</td>
                    <td>
                        <a href="tel:{{ $booking->customer_phone }}">
                            <i class="fa fa-phone"></i> {{ $booking->customer_phone }}
                        </a>
                    </td>
                </tr>
                @if(!empty($booking->note))
                <tr>
                    <td style="color:#888">Ghi chú</td>
                    <td><em>{{ $booking->note }}</em></td>
                </tr>
                @endif
            </table>
        </div>
        <div class="col-md-6">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-clock-o"></i> Thông tin lịch hẹn
            </h5>
            <table class="table table-condensed table-borderless">
                <tr>
                    <td style="width:130px;color:#888">Ngày tạo</td>
                    <td><strong>{{ date('d/m/Y', strtotime($booking->created_at)) }}</strong></td>
                </tr>
                <tr>
                    <td style="color:#888">Chi nhánh</td>
                    <td>
                        @if(!empty($booking->branch_name))
                            <i class="fa fa-map-marker text-primary"></i> {{ $booking->branch_name }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="color:#888">Trạng thái</td>
                    <td>
                        @php
                            $statusMap = [
                                'pending'   => ['label' => 'Chờ xác nhận', 'class' => 'warning'],
                                'confirmed' => ['label' => 'Đã xác nhận',  'class' => 'info'],
                                'completed' => ['label' => 'Hoàn thành',   'class' => 'success'],
                                'cancelled' => ['label' => 'Đã huỷ',       'class' => 'danger'],
                            ];
                            $s = $statusMap[$booking->status] ?? ['label' => $booking->status, 'class' => 'default'];
                        @endphp
                        <span class="label label-{{ $s['class'] }}">{{ $s['label'] }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="color:#888">Ngày tạo</td>
                    <td>{{ date('d/m/Y H:i', strtotime($booking->created_at)) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Thông tin thanh toán -->
    <div class="row" style="margin-top:10px">
        <div class="col-md-12">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-credit-card"></i> Thanh toán
            </h5>
            <div class="row">
                <div class="col-md-4">
                    <p style="color:#888;margin-bottom:4px">Hình thức</p>
                    <span class="label label-primary" style="font-size:13px">
                        {{ $booking->payment_method_name ?? '—' }}
                    </span>
                </div>
                <div class="col-md-4">
                    <p style="color:#888;margin-bottom:4px">Trạng thái TT</p>
                    @php
                        $pmMap = [
                            'pending'   => ['label' => 'Chờ thanh toán', 'class' => 'warning'],
                            'paid'      => ['label' => 'Đã thanh toán',  'class' => 'success'],
                            'pay_later' => ['label' => 'Thanh toán sau', 'class' => 'default'],
                            'transfer'  => ['label' => 'Chuyển khoản',   'class' => 'primary'],
                        ];
                        $pm = $pmMap[$booking->payment_status] ?? ['label' => $booking->payment_status, 'class' => 'default'];
                    @endphp
                    <span class="label label-{{ $pm['class'] }}" style="font-size:13px">{{ $pm['label'] }}</span>
                </div>
                <div class="col-md-4">
                    <p style="color:#888;margin-bottom:4px">Tổng tiền</p>
                    <strong style="font-size:18px;color:#285b23" id="booking-total-display">
                        {{ number_format($booking->total_amount, 0, ',', '.') }} đ
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách dịch vụ -->
    <div class="row" style="margin-top:16px">
        <div class="col-md-12">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-list"></i> Danh sách dịch vụ
            </h5>

            @php
                $isUnpaid = ($booking->payment_status !== 'paid');
                $hasAppliedTreatment = collect($services)->contains(fn($svc) => !empty($svc->id_treatment_purchase));
                $showTreatmentCol = ($isUnpaid || $hasAppliedTreatment);
                $allPurchases = $treatmentPurchases->values();

                // Group theo ngày hẹn, sort tăng dần
                $groupedByDate = collect($services)->groupBy(function($svc) {
                    return !empty($svc->booking_date) ? $svc->booking_date : '9999-12-31';
                })->sortKeys();
                $svcIndex = 0;
            @endphp

            @if($services->isEmpty())
                <p class="text-muted text-center" style="padding:16px 0"><em>Không có dịch vụ</em></p>
            @else

            @foreach($groupedByDate as $dateKey => $dateServices)
            @php
                $dateLabel = $dateKey === '9999-12-31'
                    ? '<span class="text-muted">Chưa xác định ngày</span>'
                    : \Carbon\Carbon::parse($dateKey)->format('d/m/Y') . ' (' . \Carbon\Carbon::parse($dateKey)->isoFormat('dddd') . ')';
            @endphp

            {{-- Header ngày --}}
            <div style="display:flex;align-items:center;gap:10px;margin:{{ $loop->first ? '0' : '20px' }} 0 10px">
                <div style="background:#285b23;color:#fff;border-radius:6px;padding:5px 14px;font-size:13px;font-weight:600;white-space:nowrap">
                    <i class="fa fa-calendar"></i> {!! $dateLabel !!}
                </div>
                <div style="flex:1;height:1px;background:#ddd"></div>
                <span style="font-size:12px;color:#999;white-space:nowrap">{{ $dateServices->count() }} dịch vụ</span>
            </div>

            {{-- Cards dịch vụ --}}
            @foreach($dateServices as $svc)
            @php $svcIndex++; @endphp
            <div style="border:1px solid #e5e5e5;border-radius:8px;margin-bottom:8px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden">
                <div style="display:flex;align-items:stretch">

                    {{-- Badge số + giờ --}}
                    <div style="background:#f0f5f0;border-right:1px solid #e0e8e0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:14px 16px;min-width:72px">
                        <span style="font-size:20px;font-weight:700;color:#285b23;line-height:1">{{ $svcIndex }}</span>
                        @if(!empty($svc->booking_time))
                        <span style="font-size:12px;color:#285b23;margin-top:5px;font-weight:600">
                            <i class="fa fa-clock-o"></i> {{ \Carbon\Carbon::parse($svc->booking_time)->format('H:i') }}
                        </span>
                        @endif
                    </div>

                    {{-- Nội dung --}}
                    <div style="flex:1;padding:12px 16px">
                        <div style="font-size:14px;font-weight:600;color:#222;margin-bottom:5px">{{ $svc->name }}</div>
                        <div style="font-size:12px;color:#888">
                            <span><i class="fa fa-shopping-cart"></i> SL: <strong>{{ $svc->quantity }}</strong></span>
                            &nbsp;·&nbsp;
                            <span>Đơn giá: <strong>{{ number_format($svc->price, 0, ',', '.') }} đ</strong></span>
                            @if(!empty($svc->discount_percent) && $svc->discount_percent > 0)
                            <!-- &nbsp;·&nbsp; -->
                            <!-- <span class="text-danger"><i class="fa fa-tag"></i> Giảm {{ $svc->discount_percent }}%</span> -->
                            @endif
                        </div>

                        @if($showTreatmentCol)
                        <div style="margin-top:8px">
                            @if(!empty($svc->id_treatment_purchase))
                                <span class="label label-success" style="font-size:11px;padding:3px 7px">
                                    <i class="fa fa-check"></i> Đã áp dụng liệu trình
                                </span>
                                <div style="margin-top:5px;font-size:13px;font-weight:600;color:#333">{{ $svc->applied_treatment_name }}</div>
                                <small class="text-muted">
                                    {{ $svc->applied_purchase_code }}
                                    &nbsp;·&nbsp; Đã dùng: <strong>{{ $svc->applied_used }}</strong>/{{ $svc->applied_total }} buổi
                                    &nbsp;·&nbsp; Còn lại: <strong class="text-success">{{ $svc->applied_total - $svc->applied_used }}</strong> buổi
                                </small>
                            @elseif($isUnpaid)
                                @php
                                    $validPurchases = $allPurchases->filter(fn($opt) => empty($opt->id_category) || $opt->id_category == 0 || $opt->id_category == $svc->id_category);
                                @endphp
                                @if($validPurchases->isNotEmpty())
                                    <div class="input-group input-group-sm" style="max-width:380px">
                                        <select class="form-control select-treatment" data-service-id="{{ $svc->id }}">
                                            <option value="">-- Chọn thẻ liệu trình --</option>
                                            @foreach($validPurchases as $opt)
                                            @php $remaining = $opt->total_sessions - $opt->used_sessions; @endphp
                                            <option value="{{ $opt->id }}">
                                                {{ $opt->treatment_name }} — Còn {{ $remaining }} buổi ({{ $opt->category_name ?? 'Áp dụng tất cả' }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <span class="input-group-btn">
                                            <button class="btn btn-success btn-sm btn-apply-treatment" data-service-id="{{ $svc->id }}" title="Xác nhận áp dụng liệu trình">
                                                <i class="fa fa-check"></i> Xác nhận
                                            </button>
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size:12px"><i class="fa fa-info-circle"></i> Không có liệu trình phù hợp</span>
                                @endif
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Thành tiền --}}
                    <div style="display:flex;align-items:center;justify-content:flex-end;padding:12px 16px;min-width:120px;border-left:1px solid #f0f0f0;background:#fafafa">
                        <div class="text-right">
                            <div style="font-size:15px;font-weight:700;color:#285b23;white-space:nowrap">{{ number_format($svc->amount, 0, ',', '.') }} đ</div>
                            @if(!empty($svc->id_treatment_purchase))
                            <div><span class="label label-default" style="font-size:10px;margin-top:3px">Liệu trình</span></div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
            @endforeach

            @endforeach

            {{-- Tổng cộng --}}
            <div style="border-top:2px solid #e0e0e0;padding-top:12px;margin-top:12px;display:flex;justify-content:flex-end;align-items:center;gap:8px">
                <span style="font-size:14px;color:#666;font-weight:600">Tổng cộng:</span>
                <strong style="font-size:18px;color:#285b23">{{ number_format($booking->total_amount, 0, ',', '.') }} đ</strong>
            </div>

            @if($isUnpaid)
                @if($treatmentPurchases->isNotEmpty())
                <div class="alert alert-info" style="margin-top:12px;padding:8px 12px;font-size:13px">
                    <i class="fa fa-info-circle"></i>
                    Khách có <strong>{{ $treatmentPurchases->count() }}</strong> liệu trình còn buổi.
                    Chọn liệu trình muốn áp dụng và bấm <strong>Xác nhận</strong>.
                </div>
                @elseif(!empty($booking->id_client))
                <div class="alert alert-warning" style="margin-top:12px;padding:8px 12px;font-size:13px">
                    <i class="fa fa-exclamation-triangle"></i>
                    Khách chưa có liệu trình nào còn buổi.
                </div>
                @endif
            @endif

            @endif
        </div>
    </div>

    <!-- Đổi trạng thái -->
    @if($booking->status === 'pending')
    <div class="row" style="margin-top:8px">
        <div class="col-md-12">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-exchange"></i> Cập nhật trạng thái
            </h5>
            <button class="btn btn-info btn-sm btn-change-status"
                    data-id="{{ $booking->id }}" data-status="confirmed">
                <i class="fa fa-check"></i> Xác nhận lịch hẹn
            </button>
            <button class="btn btn-success btn-sm btn-change-status"
                    data-id="{{ $booking->id }}" data-status="completed">
                <i class="fa fa-check-circle"></i> Hoàn thành
            </button>
            <button class="btn btn-danger btn-sm btn-change-status"
                    data-id="{{ $booking->id }}" data-status="cancelled">
                <i class="fa fa-times"></i> Huỷ lịch
            </button>
        </div>
    </div>
    @elseif($booking->status === 'confirmed')
    <div class="row" style="margin-top:8px">
        <div class="col-md-12">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-exchange"></i> Cập nhật trạng thái
            </h5>
            <button class="btn btn-success btn-sm btn-change-status"
                    data-id="{{ $booking->id }}" data-status="completed">
                <i class="fa fa-check-circle"></i> Hoàn thành
            </button>
            <button class="btn btn-danger btn-sm btn-change-status"
                    data-id="{{ $booking->id }}" data-status="cancelled">
                <i class="fa fa-times"></i> Huỷ lịch
            </button>
        </div>
    </div>
    @endif
</div>
<div class="modal-footer">
    <button class="btn btn-danger btn-sm btn-delete-booking" data-id="{{ $booking->id }}">
        <i class="fa fa-trash"></i> Xoá lịch hẹn
    </button>
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <i class="fa fa-times"></i> Đóng
    </button>
</div>

{{-- JS xử lý áp dụng liệu trình --}}
<script>
(function () {
    var _token = $('meta[name=csrf-token]').attr('content');

    // Bấm nút xác nhận áp dụng
    $(document).off('click.applyTreatment').on('click.applyTreatment', '.btn-apply-treatment', function () {
        var $btn       = $(this);
        var serviceId  = $btn.data('service-id');
        var $select    = $('.select-treatment[data-service-id="' + serviceId + '"]');
        var purchaseId = $select.val();

        if (!purchaseId) {
            alert('Vui lòng chọn liệu trình trước khi xác nhận!');
            return;
        }

        if (!confirm('Xác nhận áp dụng liệu trình này cho dịch vụ?')) return;

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.post('admin/booking/applyTreatment', {
            _token               : _token,
            id_booking_service   : serviceId,
            id_treatment_purchase: purchaseId,
        }, function (res) {
            if (res.result) {
                alert_float('success', res.message);
                // Reload lại nội dung modal
                var bookingId = {{ $booking->id }};
                $('#modalViewBookingContent').html('<div class="modal-body text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
                $.get('admin/booking/view/' + bookingId, function (html) {
                    $('#modalViewBookingContent').html(html);
                });
                // Refresh bảng danh sách nếu có
                if (typeof oTable !== 'undefined') { oTable.draw(); }
            } else {
                alert_float('error', Array.isArray(res.message) ? res.message.join('<br>') : res.message);
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i>');
            }
        }).fail(function () {
            alert_float('error', 'Có lỗi xảy ra, vui lòng thử lại!');
            $btn.prop('disabled', false).html('<i class="fa fa-check"></i>');
        });
    });
})();
</script>
