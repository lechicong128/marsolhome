@php
    $statusMap = [
        'pending'  => ['label' => 'Chờ duyệt', 'class' => 'warning'],
        'approved' => ['label' => 'Đã duyệt',  'class' => 'success'],
        'rejected' => ['label' => 'Từ chối',   'class' => 'danger'],
    ];
    $s = $statusMap[$payment->status] ?? ['label' => $payment->status, 'class' => 'default'];

    $bookingStatusMap = [
        'pending'   => ['label' => 'Chờ xác nhận', 'class' => 'warning'],
        'confirmed' => ['label' => 'Đã xác nhận',  'class' => 'info'],
        'completed' => ['label' => 'Hoàn thành',   'class' => 'success'],
        'cancelled' => ['label' => 'Đã huỷ',       'class' => 'danger'],
    ];
    $bs = $bookingStatusMap[$payment->booking_status] ?? ['label' => $payment->booking_status, 'class' => 'default'];
@endphp

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title">
        <i class="fa fa-money"></i> Phiếu thanh toán
        <span class="text-primary">#{{ $payment->payment_code }}</span>
        <span class="label label-{{ $s['class'] }}" style="font-size:12px;vertical-align:middle;margin-left:6px">{{ $s['label'] }}</span>
    </h4>
</div>

<div class="modal-body">
    <div class="row">
        <!-- Khách hàng -->
        <div class="col-md-6">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-user"></i> Khách hàng
            </h5>
            <table class="table table-condensed">
                <tr>
                    <td style="width:120px;color:#888">Tên</td>
                    <td><strong>{{ $payment->customer_name }}</strong></td>
                </tr>
                <tr>
                    <td style="color:#888">Số điện thoại</td>
                    <td>
                        <a href="tel:{{ $payment->customer_phone }}">
                            <i class="fa fa-phone"></i> {{ $payment->customer_phone }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="color:#888">Chi nhánh</td>
                    <td>
                        @if(!empty($payment->branch_name))
                            <i class="fa fa-map-marker text-primary"></i> {{ $payment->branch_name }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Lịch hẹn -->
        <div class="col-md-6">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-calendar"></i> Lịch hẹn – <small class="text-muted">{{ $payment->booking_code }}</small>
            </h5>
            <table class="table table-condensed">
                <tr>
                    <td style="width:120px;color:#888">Ngày hẹn</td>
                    <td><strong>{{ $payment->booking_date ? date('d/m/Y', strtotime($payment->booking_date)) : '—' }}</strong></td>
                </tr>
                <tr>
                    <td style="color:#888">Giờ hẹn</td>
                    <td><strong>{{ $payment->booking_time ? \Carbon\Carbon::parse($payment->booking_time)->format('H:i') : '—' }}</strong></td>
                </tr>
                <tr>
                    <td style="color:#888">Trạng thái</td>
                    <td><span class="label label-{{ $bs['class'] }}">{{ $bs['label'] }}</span></td>
                </tr>
                <tr>
                    <td style="color:#888">Tổng booking</td>
                    <td><strong>{{ number_format($payment->booking_total, 0, ',', '.') }} đ</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Thông tin phiếu thanh toán -->
    <div class="row" style="margin-top:10px">
        <div class="col-md-12">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-credit-card"></i> Thông tin thanh toán
            </h5>
            <div class="row text-center">
                <div class="col-md-3">
                    <p class="text-muted" style="margin-bottom:4px">Số tiền thanh toán</p>
                    <strong style="font-size:22px;color:#285b23">
                        {{ number_format($payment->amount, 0, ',', '.') }} đ
                    </strong>
                </div>
                <div class="col-md-3">
                    <p class="text-muted" style="margin-bottom:4px">Hình thức thanh toán</p>
                    <span class="label label-primary" style="font-size:13px;padding:6px 12px">
                        <i class="fa fa-bank"></i>
                        {{ $payment->payment_method_name ?? '—' }}
                    </span>
                </div>
                <div class="col-md-3">
                    <p class="text-muted" style="margin-bottom:4px">Thời điểm duyệt</p>
                    <span>{{ $payment->approved_at ? date('d/m/Y H:i', strtotime($payment->approved_at)) : '—' }}</span>
                </div>
                <div class="col-md-3">
                    <p class="text-muted" style="margin-bottom:4px">Ngày tạo</p>
                    <span>{{ date('d/m/Y H:i', strtotime($payment->created_at)) }}</span>
                </div>
            </div>
            @if(!empty($payment->note))
            <div style="margin-top:12px;padding:10px;background:#f9f9f9;border-left:3px solid #ccc;border-radius:3px">
                <i class="fa fa-comment-o text-muted"></i>
                <span class="text-muted"> Ghi chú / Mã giao dịch:</span>
                <strong> {{ $payment->note }}</strong>
            </div>
            @endif
        </div>
    </div>

    <!-- Danh sách dịch vụ -->
    <div class="row" style="margin-top:16px">
        <div class="col-md-12">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-list"></i> Dịch vụ đã đặt
            </h5>
            <table class="table table-bordered table-condensed">
                <thead style="background:#f9f9f9">
                <tr>
                    <th class="text-center" style="width:40px">STT</th>
                    <th>Tên dịch vụ</th>
                    <th class="text-center" style="width:110px">Đơn giá</th>
                    <th class="text-center" style="width:60px">SL</th>
                    <th class="text-center" style="width:120px">Thành tiền</th>
                </tr>
                </thead>
                <tbody>
                @forelse($services as $i => $svc)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $svc->name }}</td>
                    <td class="text-right">{{ number_format($svc->price, 0, ',', '.') }} đ</td>
                    <td class="text-center">{{ $svc->quantity }}</td>
                    <td class="text-right"><strong>{{ number_format($svc->amount, 0, ',', '.') }} đ</strong></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Không có dịch vụ</td></tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Tổng booking:</strong></td>
                    <td class="text-right">
                        <strong style="color:#285b23;font-size:15px">
                            {{ number_format($payment->booking_total, 0, ',', '.') }} đ
                        </strong>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Hành động duyệt -->
    @if($payment->status === 'pending')
    <div class="row" style="margin-top:10px">
        <div class="col-md-12">
            <h5 class="text-uppercase" style="border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:12px">
                <i class="fa fa-check-square-o"></i> Xử lý phiếu thanh toán
            </h5>
            <button class="btn btn-success btn-sm btn-approve-payment" data-id="{{ $payment->id }}">
                <i class="fa fa-check"></i> Duyệt thanh toán
            </button>
            <button class="btn btn-danger btn-sm btn-reject-payment" data-id="{{ $payment->id }}" style="margin-left:8px">
                <i class="fa fa-times"></i> Từ chối
            </button>
        </div>
    </div>
    @elseif($payment->status === 'approved')
    <div class="row" style="margin-top:10px">
        <div class="col-md-12">
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i>
                Phiếu đã được duyệt lúc <strong>{{ $payment->approved_at ? date('d/m/Y H:i', strtotime($payment->approved_at)) : '' }}</strong>
            </div>
        </div>
    </div>
    @elseif($payment->status === 'rejected')
    <div class="row" style="margin-top:10px">
        <div class="col-md-12">
            <div class="alert alert-danger">
                <i class="fa fa-times-circle"></i> Phiếu đã bị từ chối.
            </div>
            <button class="btn btn-warning btn-sm btn-approve-payment" data-id="{{ $payment->id }}">
                <i class="fa fa-refresh"></i> Duyệt lại
            </button>
        </div>
    </div>
    @endif
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <i class="fa fa-times"></i> Đóng
    </button>
</div>
