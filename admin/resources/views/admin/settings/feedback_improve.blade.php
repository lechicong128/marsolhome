<style>
    .nav.nav-tabs > li.tab-btn.active > a {
        background-color: #3a94ef;
        color: white !important;
        border: 0;
        border-radius: 10px;
    }
</style>
<div class="tab-content m-t-40">
    <table class="table dataTable no-footer">
        <thead>
            <tr>
                <th>{{ lang('dt_stt') }}</th>
                <th>{{ lang('c_star_like') }}</th>
                <th>{{ lang('feedback_improve') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $countSTT = 0;
            @endphp
            @for($i = 1; $i <= 5; $i++)
                <tr>
                    <td>{{$i}}</td>
                    <td>{{lang('lang_star_like_' . $i)}}</td>
                    <td>
                        <table class="table table-improve">
                            <thead>
                                <tr>
                                    <th>Key</th>
                                    @if(!empty($language))
                                        @foreach($language as $lang)
                                            <th>{{$lang->name}}</th>
                                        @endforeach
                                    @endif
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($data[$i]))
                                    @foreach($data[$i] as $key => $value)
                                        <tr class="item">
                                            <td>
                                                <input type="text" class="form-control" name="key_improve[{{$i}}][{{$countSTT}}]" value="{{$value['key_main'] ?? ''}}">
                                            </td>
                                            @if(!empty($language))
                                                @foreach($language as $lang)
                                                    <td>
                                                        <input type="text" class="form-control" name="improve[{{$i}}][{{$countSTT}}][{{$lang->code}}]" value="{{$value['translations'][$lang->code] ?? ''}}">
                                                    </td>
                                                @endforeach
                                            @endif
                                            <td>
                                                <a class="btn btn-icon btn-danger" onclick="removeItem(this)"><i class="fa fa-remove"></i></a>
                                            </td>
                                        </tr>
                                        @php
                                            $countSTT++;
                                        @endphp
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="text-center"><a onclick="appendKey(this, {{$i}})" class="btn btn-info"><i class="fa fa-plus"></i></a></div>
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
@section('script')
    <script>
        var keyCount = <?=($countSTT + 1)?>;
        function appendKey(_this, isStar) {
            $(_this).parents('td').find(`.table-improve`).find('tbody').append(`<tr class="item">
                                <td>
                                    <input type="text" class="form-control" name="key_improve[${isStar}][${keyCount}]" value="">
                                </td>
                                @if(!empty($language))
                                    @foreach($language as $lang)
                                        <td>
                                            <input type="text" class="form-control" name="improve[${isStar}][${keyCount}][{{$lang->code}}]" value="">
                                        </td>
                                    @endforeach
                                @endif
                        </tr>`);
            keyCount++;
        }

        function removeItem(_this) {
            $(_this).parents('tr.item').remove();
        }
    </script>
@endsection
