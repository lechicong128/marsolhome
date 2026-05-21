<div class="table-responsive">
    <table class="table table-bordered table-striped" style="background-color: #fff;">
        <thead>
            <tr style="background-color: #f1f1f1;">
                <th class="text-center" style="width: 40px;">#</th>
                <th>Mã lịch hẹn</th>
                <th>Thời gian hẹn</th>
                <th>Chi nhánh</th>
                <th class="text-center">Số lượng DV</th>
                <th class="text-right">Tổng tiền</th>
                <th class="text-center">Trạng thái</th>
                <th class="text-center">Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $key => $booking)
                @php
                    $statusClass = 'default';
                    $statusLabel = $booking->status;
                    if ($booking->status == 'pending') {
                        $statusClass = 'warning';
                        $statusLabel = 'Chờ xác nhận';
                    } elseif ($booking->status == 'confirmed') {
                        $statusClass = 'info';
                        $statusLabel = 'Đã xác nhận';
                    } elseif ($booking->status == 'completed') {
                        $statusClass = 'success';
                        $statusLabel = 'Hoàn thành';
                    } elseif ($booking->status == 'cancelled') {
                        $statusClass = 'danger';
                        $statusLabel = 'Đã huỷ';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td>
                        <a href="admin/booking/view/{{ $booking->id }}" target="_blank" class="text-primary" style="font-weight: bold;">
                            {{ $booking->booking_code }}
                        </a>
                    </td>
                    <td>
                        {{ date('d/m/Y', strtotime($booking->booking_date)) }} {{ $booking->booking_time }}
                    </td>
                    <td>{{ $booking->branch_name ?? '—' }}</td>
                    <td class="text-center">{{ $booking->total_services }}</td>
                    <td class="text-right">{{ number_format($booking->total_amount, 0, ',', '.') }} đ</td>
                    <td class="text-center">
                        <span class="label label-{{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-xs btn-primary btn-toggle-services" data-id="{{ $booking->id }}">
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </td>
                </tr>
                <tr id="services_child_{{ $booking->id }}" style="display: none; background-color: #fcfcfc;">
                    <td colspan="8">
                        <div style="padding: 10px 20px;">
                            <strong style="color: #337ab7;">Danh sách dịch vụ:</strong>
                            @if($booking->services->isEmpty())
                                <p class="text-muted m-t-5 m-b-0">Không có dịch vụ nào.</p>
                            @else
                                <ul style="margin-top: 5px; margin-bottom: 0;">
                                @foreach($booking->services as $svc)
                                    <li>
                                        {{ $svc->service_name }} ({{ number_format($svc->amount, 0, ',', '.') }} đ)
                                        @if($svc->treatment_name)
                                            - <span class="label label-info" style="font-weight: normal;">Sử dụng thẻ liệu trình: {{ $svc->treatment_name }} ({{ $svc->purchase_code }})</span>
                                        @endif
                                    </li>
                                @endforeach
                                </ul>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Không có lịch hẹn nào</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    $('.btn-toggle-services').click(function() {
        var id = $(this).data('id');
        var tr = $('#services_child_' + id);
        if (tr.is(':visible')) {
            tr.hide();
            $(this).find('i').removeClass('fa-caret-up').addClass('fa-caret-down');
        } else {
            tr.show();
            $(this).find('i').removeClass('fa-caret-down').addClass('fa-caret-up');
        }
    });
</script>
