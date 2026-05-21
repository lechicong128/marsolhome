{{-- 1. CSS CHO MODULE NHẬP LIỆU --}}
<style>
    /* Tab Style của bạn */
    .nav.nav-tabs > li.tab-btn.active > a {
        background-color: #3a94ef;
        color: white !important;
        border: 0;
        border-radius: 10px;
    }

    /* --- STYLE MỚI CHO REPEATER (Bắt đầu bằng tm-) --- */
    .tm-repeater-wrapper {
        border: 1px solid #ddd;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 5px;
        /*max-height: 500px;*/
        overflow-y: auto;
    }
    .tm-item {
        background: #fff;
        border: 1px solid #e1e1e1;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 10px;
        position: relative;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .tm-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        border-bottom: 1px solid #eee;
        padding-bottom: 8px;
    }
    .tm-label { font-weight: bold; font-size: 13px; color: #555; text-transform: uppercase; }
    .tm-btn-delete-block {
        color: #ff4d4f; cursor: pointer; border: none; background: none; font-size: 16px;
    }

    /* Input Styling */
    .tm-input-group { margin-bottom: 8px; display: flex; gap: 5px; }
    .tm-form-control {
        width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;
    }
    .tm-form-control:focus { border-color: #3a94ef; outline: none; }
    .tm-title-input { font-weight: bold; color: #333; }

    /* Content Lines */
    .tm-content-list { margin-top: 10px; padding-left: 10px; border-left: 2px solid #eee; }
    .tm-btn-remove-line {
        color: #999; cursor: pointer; border: 1px solid #ddd; background: #fff;
        padding: 0 8px; border-radius: 4px;
    }
    .tm-btn-remove-line:hover { color: #ff4d4f; border-color: #ff4d4f; }

    /* Add Buttons */
    .tm-btn-add-line {
        font-size: 12px; color: #3a94ef; cursor: pointer; background: none; border: none;
        display: inline-flex; align-items: center; margin-top: 5px; font-weight: 600;
    }
    .tm-btn-add-block {
        width: 100%; padding: 10px; border: 2px dashed #3a94ef; color: #3a94ef;
        background: #f0f7ff; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px;
        transition: all 0.2s;
    }
    .tm-btn-add-block:hover { background: #3a94ef; color: #fff; }
</style>

<div class="form-group">
    <label>{{ lang('radio_refund_challenge') }}</label>
    <input type="text" class="form-control" name="radio_refund"
           value="{{get_option('radio_refund') ?? ''}}">
</div>
<div class="form-group">
    <label>{{ lang('radio_challenge_success') }}</label>
    <input type="text" class="form-control" name="radio_challenge_success"
           value="{{get_option('radio_challenge_success') ?? ''}}">
</div>

{{-- 2. HTML BLADE TEMPLATE --}}
<ul class="nav nav-tabs nav-justified">
    @if(!empty($language))
        @foreach($language as $lang)
            <li class="tab-btn {{($lang->is_default == 1) ? 'active' : ''}}">
                <a data-toggle="tab" href="#tab-lang-{{$lang->code_system}}">{{$lang->name}}</a>
            </li>
        @endforeach
    @endif
</ul>

<div class="row">
    <div class="col-md-12">
        <div class="tab-content m-t-40">
            @if(!empty($language))
                @foreach($language as $lang)
                    <div id="tab-lang-{{$lang->code_system}}" class="tab-pane fade {{($lang->is_default == 1) ? 'in active' : ''}}">
                        @php
                            $policy_challenge = get_option('policy_challenge_'.$lang->code_system);
                        @endphp
                        <div class="form-group">
                            <label for="policy_challenge_{{$lang->code_system}}">{{ lang('policy_challenge') }}</label>
                            <textarea name="policy_challenge_{{$lang->code_system}}"
                                      id="policy_challenge_{{$lang->code_system}}" class="form-control">{{!empty($policy_challenge) ? $policy_challenge : ''}}</textarea>
                        </div>
                        <hr/>
                        <div class="row">
                            {{-- Cột bên trái: Success Challenge --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ lang('title_when_success_challenge') }}</label>
                                    <input type="text" class="form-control" name="title_when_success_challenge_{{$lang->code_system}}"
                                           value="{{get_option('title_when_success_challenge_'.$lang->code_system) ?? ''}}">
                                </div>
                                @php $when_success_challenge = get_option('when_success_challenge_'.$lang->code_system); @endphp
                                <div class="form-group">
                                    <label>{{ lang('when_success_challenge') }}</label>
                                    <textarea name="when_success_challenge_{{$lang->code_system}}" class="form-control editor">{{!empty($when_success_challenge) ? $when_success_challenge : ''}}</textarea>
                                </div>
                            </div>

                            {{-- Cột bên phải: Fail Challenge --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ lang('title_when_fail_challenge') }}</label>
                                    <input type="text" class="form-control" name="title_when_fail_challenge_{{$lang->code_system}}"
                                           value="{{get_option('title_when_fail_challenge_'.$lang->code_system) ?? ''}}">
                                </div>
                                @php $when_fail_challenge = get_option('when_fail_challenge_'.$lang->code_system); @endphp
                                <div class="form-group">
                                    <label>{{ lang('when_fail_challenge') }}</label>
                                    <textarea name="when_fail_challenge_{{$lang->code_system}}" class="form-control editor">{{!empty($when_fail_challenge) ? $when_fail_challenge : ''}}</textarea>
                                </div>
                            </div>

                            <div class="clearfix"></div>
                            <hr/>

                            <div class="col-md-12"><h4>{{lang('c_how_to_join')}}</h4></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ lang('challenge') }} {{ lang('daily') }}</label>

                                    {{-- Container cho giao diện nhập liệu JS --}}
                                    <div id="container_daily_{{$lang->code_system}}" class="tm-repeater-wrapper">
                                        <!-- JS sẽ render nội dung vào đây -->
                                    </div>

                                    {{-- Input Hidden lưu dữ liệu JSON thực tế để gửi về Server --}}
                                    @php
                                        $valDaily = get_option('how_to_join_daily_'.$lang->code_system);
                                        // Kiểm tra nếu chưa có data hoặc data rỗng thì để mảng rỗng []
                                        $jsonDaily = !empty($valDaily) ? $valDaily : '[]';
                                    @endphp
                                    <textarea style="display:none;" name="how_to_join_daily_{{$lang->code_system}}"
                                              id="input_daily_{{$lang->code_system}}">{{ $jsonDaily }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ lang('challenge') }} {{ lang('trademark') }}</label>

                                    <div id="container_trademark_{{$lang->code_system}}" class="tm-repeater-wrapper">
                                        <!-- JS sẽ render nội dung vào đây -->
                                    </div>

                                    @php
                                        $valTrade = get_option('how_to_join_trademark_'.$lang->code_system);
                                        $jsonTrade = !empty($valTrade) ? $valTrade : '[]';
                                    @endphp
                                    <textarea style="display:none;" name="how_to_join_trademark_{{$lang->code_system}}"
                                              id="input_trademark_{{$lang->code_system}}">{{ $jsonTrade }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
<hr/>

@section('script')
    <script>
        $(document).ready(function() {

            // Helper: Escape HTML để tránh lỗi khi render value trong input
            function escapeHtml(text) {
                if (text === null || text === undefined) return '';
                return text
                    .toString()
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // Helper: Safe JSON Parse
            function safeJsonParse(jsonString) {
                try {
                    var res = JSON.parse(jsonString);
                    if (Array.isArray(res)) return res;
                    return [];
                } catch(e) {
                    return [];
                }
            }

            /**
             * Hàm khởi tạo bộ nhập liệu Repeater
             */
            function initTmRepeater(containerId, inputId) {
                var $container = $(containerId);
                var $input = $(inputId);

                // Lấy dữ liệu ban đầu
                var data = safeJsonParse($input.val());

                // Hàm render toàn bộ danh sách
                function render() {
                    $container.empty();

                    data.forEach(function(item, index) {
                        var contentHtml = '';
                        var safeTitle = escapeHtml(item.title || '');

                        // Render các dòng nội dung con
                        if (item.content && Array.isArray(item.content)) {
                            item.content.forEach(function(line, lineIdx) {
                                var safeLine = escapeHtml(line);
                                contentHtml += `
                                <div class="tm-input-group">
                                    <input type="text" class="tm-form-control line-input" value="${safeLine}" data-index="${index}" data-line="${lineIdx}" placeholder="Nội dung...">
                                    <button type="button" class="tm-btn-remove-line" onclick="removeLine('${containerId}', ${index}, ${lineIdx})">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            `;
                            });
                        }

                        // Render khối Item
                        var itemHtml = `
                        <div class="tm-item">
                            <div class="tm-item-header">
                                <span class="tm-label">{{lang('step')}} ${index + 1}</span>
                                <button type="button" class="tm-btn-delete-block" onclick="removeBlock('${containerId}', ${index})" title="Xóa bước này">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                            <div class="form-group" style="margin-bottom:5px;">
                                <input type="text" class="tm-form-control tm-title-input title-input" value="${safeTitle}" data-index="${index}" placeholder="Tiêu đề bước (VD: Chụp ảnh)...">
                            </div>
                            <div class="tm-content-list">
                                ${contentHtml}
                                <button type="button" class="tm-btn-add-line" onclick="addLine('${containerId}', ${index})">
                                    <i class="fa fa-plus"></i> {{lang('c_append_success')}}
                                </button>
                            </div>
                        </div>
                    `;
                        $container.append(itemHtml);
                    });

                    // Nút thêm Block mới
                    $container.append(`
                    <button type="button" class="tm-btn-add-block" onclick="addBlock('${containerId}')">
                        <i class="fa fa-plus-circle"></i> {{lang('c_append_step_new')}}
                    </button>
                `);

                    // Cập nhật lại input hidden
                    $input.val(JSON.stringify(data));
                }

                // --- CÁC HÀM XỬ LÝ SỰ KIỆN (Global Scope Helpers) ---

                // Hàm lấy element input từ containerID (Đã FIX lỗi selector ##)
                function getInputEl(cId) {
                    // cId dạng "#container_..." -> replace "container_" bằng "input_" -> "#input_..."
                    var sel = cId.replace('container_', 'input_');
                    return $(sel);
                }

                // 1. Thêm Block
                window.addBlock = window.addBlock || function(cId) {
                    var _input = getInputEl(cId);
                    var _data = safeJsonParse(_input.val());

                    _data.push({ title: '', content: [''] });
                    _input.val(JSON.stringify(_data)).trigger('change');
                };

                // 2. Xóa Block
                window.removeBlock = window.removeBlock || function(cId, idx) {
                    if(!confirm('Xóa bước này?')) return;
                    var _input = getInputEl(cId);
                    var _data = safeJsonParse(_input.val());
                    _data.splice(idx, 1);
                    _input.val(JSON.stringify(_data)).trigger('change');
                };

                // 3. Thêm Dòng
                window.addLine = window.addLine || function(cId, idx) {
                    var _input = getInputEl(cId);
                    var _data = safeJsonParse(_input.val());
                    if(!_data[idx].content) _data[idx].content = [];
                    _data[idx].content.push('');
                    _input.val(JSON.stringify(_data)).trigger('change');
                };

                // 4. Xóa Dòng
                window.removeLine = window.removeLine || function(cId, idx, lineIdx) {
                    var _input = getInputEl(cId);
                    var _data = safeJsonParse(_input.val());
                    _data[idx].content.splice(lineIdx, 1);
                    _input.val(JSON.stringify(_data)).trigger('change');
                };

                // Lắng nghe sự kiện change trên input hidden để re-render
                $input.on('change', function() {
                    data = safeJsonParse($(this).val());
                    render();
                });

                // Lắng nghe sự kiện nhập liệu để cập nhật data
                $container.on('input', '.title-input', function() {
                    var idx = $(this).data('index');
                    data[idx].title = $(this).val();
                    $input.val(JSON.stringify(data));
                });

                $container.on('input', '.line-input', function() {
                    var idx = $(this).data('index');
                    var lineIdx = $(this).data('line');
                    data[idx].content[lineIdx] = $(this).val();
                    $input.val(JSON.stringify(data));
                });

                // Render lần đầu
                render();
            }

            // --- KHỞI TẠO VÒNG LẶP CHO TẤT CẢ NGÔN NGỮ ---
            @if(!empty($language))
            @foreach($language as $lang)
            initTmRepeater('#container_daily_{{$lang->code_system}}', '#input_daily_{{$lang->code_system}}');
            initTmRepeater('#container_trademark_{{$lang->code_system}}', '#input_trademark_{{$lang->code_system}}');
            @endforeach
            @endif

        });
    </script>
@endsection
