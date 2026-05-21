
    <div class="modal-dialog modal-lg" style="width: 70%" id="modalViewInvoice">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <iframe 
                            src="data:application/pdf;base64,{{$base64pdf}}" 
                            width="100%" 
                            height="100%">
                        </iframe>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">{{lang('dt_close')}}</button>
            </div>
        </div>
    </div>

<script>

</script>
