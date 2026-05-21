<style>
    .div_contract a{
        color: unset !important;
    }
</style>
<div class="row">
    <div class="col-lg-12">
        <div class="title_contract">{{lang('c_setting_contract_template')}}</div>
        <div class="wrap_contract">
            @php
                $counterContract = 0;
            @endphp
            @forelse($dtContract as $key => $value)
                <div>
                    <div><input type="file" id="uploadContract{{$value->id}}" name="fileContract[{{$counterContract}}]">
                        <input type="hidden" name="counterContract[]" value="{{$counterContract}}">
                        <input type="hidden" name="idContract[{{$counterContract}}]" value="{{$value->id}}">
                    </div>
                    <div class="div_contract">
                       <a href="admin/settings/down/{{$value->id}}" target="_blank">
                           {!! $value->icon !!}
                           <p>{{$value->name}}</p>
                       </a>
                    </div>
                </div>
                @php
                    $counterContract ++;
                @endphp
            @empty
            @endforelse
        </div>
    </div>
</div>
