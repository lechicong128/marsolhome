@extends('admin.layouts.index')
@section('content')
    <style>
        .inline-flex {
            display: inline-flex !important;
        }
        .CodeMirror pre.CodeMirror-line, .CodeMirror pre.CodeMirror-line-like
        {
            padding-left:33px!important;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title ?? ''}}</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <ul class="nav nav-tabs nav-justified row">
                    @foreach($language as $lang)
                        <li class="tab-btn {{$lang->is_default ? 'active' : ''}}">
                            <a href="#tab-info-{{$lang->code}}" data-toggle="tab" aria-expanded="false">
                                <span class="visible-xs"><i class="fa fa-home"></i></span>
                                <span class="hidden-xs">{{$lang->name}}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <form id="LangForm" action="admin/lang/submit" method="post" data-parsley-validate novalidate>
                    <div class="tab-content row" style="padding-left:10px;padding-right:10px;">
                        @foreach($language as $lang)
                            <div id="tab-info-{{$lang->code}}" class="tab-pane fade {{$lang->is_default ? 'in active' : ''}}">
                                <div class="form-group">
                                    <textarea id="sql_query_{{$lang->code}}"
                                              name="sql_query[{{$lang->code}}]">{!! (!empty($data_language[$lang->code]) ? $data_language[$lang->code] : '{}') !!}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary waves-effect waves-light"
                                type="submit">{{lang('dt_save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/theme/dracula.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/sql/sql.min.js"></script>
    <script>
        var editors = {};
        @foreach($language as $lang)
            (function(code) {
                var textarea = document.getElementById("sql_query_" + code);
                if (!textarea) return;
                editors[code] = CodeMirror.fromTextArea(textarea, {
                    mode: "text/x-sql",
                    theme: "dracula",
                    lineNumbers: true,
                    matchBrackets: true,
                    autoCloseBrackets: true
                });
            })("{{$lang->code}}");
        @endforeach

        $("#LangForm").validate({
            rules: {},
            messages: {},
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
                            alert_float('success',data.message);
                            window.location.reload();
                        } else {
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
    </script>
@endsection
