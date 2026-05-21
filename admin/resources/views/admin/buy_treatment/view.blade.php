<div class="modal-dialog modal-lg" role="document">
<div class="modal-content">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
    <h4 class="modal-title">Chi tiết liệu trình: <b>{{$purchase->purchase_code}}</b></h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered table-condensed">
                <tr>
                    <th style="width:40%">Khách hàng</th>
                    <td>{{$purchase->customer_name}} <br><small>{{$purchase->customer_phone}}</small></td>
                </tr>
                <tr>
                    <th>Gói liệu trình</th>
                    <td>
                        <strong>{{$purchase->treatment_name}}</strong><br>
                        <small class="text-muted">
                            Danh mục: {{!empty($purchase->id_category) ? $purchase->category_name : 'Áp dụng tất cả danh mục'}}
                        </small>
                    </td>
                </tr>
                <tr style="display: none;">
                    <th>Chi nhánh áp dụng</th>
                    <td>{{$purchase->branch_name ?? 'Toàn hệ thống'}}</td>
                </tr>
                <tr>
                    <th>Tổng số buổi</th>
                    <td><b>{{$purchase->total_sessions}} buổi</b></td>
                </tr>
                <tr>
                    <th>Đã dùng</th>
                    <td>{{$purchase->used_sessions}} buổi</td>
                </tr>
                <tr>
                    <th>Còn lại</th>
                    <td><b class="text-success">{{$purchase->total_sessions - $purchase->used_sessions}} buổi</b></td>
                </tr>
                <tr>
                    <th>Giá trị liệu trình</th>
                    <td>{{number_format($purchase->price ?? 0)}} VNĐ</td>
                </tr>
                <tr>
                    <th>Trạng thái</th>
                    <td>
                        @if($purchase->status == 'active')
                            <span class="label label-success">Đang dùng</span>
                        @elseif($purchase->status == 'completed')
                            <span class="label label-primary">Đã hoàn thành</span>
                        @else
                            <span class="label label-danger">Đã huỷ</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Ghi chú</th>
                    <td>{{$purchase->note ?? '—'}}</td>
                </tr>
                <tr>
                    <th>Ngày mua</th>
                    <td>{{date('d/m/Y H:i', strtotime($purchase->created_at))}}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5><b>Lịch sử sử dụng buổi</b></h5>
            <!-- @if($purchase->status == 'active' && ($purchase->total_sessions - $purchase->used_sessions) > 0)
            <div class="m-b-10">
                <button class="btn btn-sm btn-success btn-use-session" data-id="{{$purchase->id}}">
                    <i class="fa fa-check-circle"></i> Ghi nhận sử dụng 1 buổi
                </button>
            </div>
            @endif -->
            @if($sessions->count() > 0)
            <table class="table table-bordered table-condensed table-sm">
                <thead>
                <tr>
                    <th class="text-center">STT</th>
                    <th class="text-center">Ngày dùng</th>
                    <th>Ghi chú</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sessions as $k => $s)
                <tr>
                    <td class="text-center">{{$k + 1}}</td>
                    <td class="text-center">{{date('d/m/Y H:i', strtotime($s->created_at))}}</td>
                    <td>
                        @if(!empty($s->note) && strpos($s->note, '(Đã hoàn lại do huỷ lịch hẹn)') !== false)
                            {{ str_replace(' (Đã hoàn lại do huỷ lịch hẹn)', '', $s->note) }}
                            <span class="text-danger"> (Đã hoàn lại do huỷ lịch hẹn)</span>
                        @else
                            {{ $s->note ?? '—' }}
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
                <p class="text-muted">Chưa có buổi nào được sử dụng.</p>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    @if(($purchase->used_sessions ?? 0) == 0)
    <a href="admin/buy_treatment/detail/{{$purchase->id}}" class="btn btn-default">
        <i class="fa fa-pencil"></i> Chỉnh sửa
    </a>
    @endif
    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
</div>
</div>
</div>

<script>
$('.btn-use-session').on('click', function () {
    var id = $(this).data('id');
    if (!confirm('Xác nhận ghi nhận 1 buổi sử dụng?')) return;
    $.post('admin/buy_treatment/useSession/' + id, { _token: $('meta[name=csrf-token]').attr('content') }, function (res) {
        if (res.result) {
            alert_float('success', res.message);
            setTimeout(function () { location.reload(); }, 800);
        } else {
            alert_float('error', res.message);
        }
    }, 'json');
});
</script>
