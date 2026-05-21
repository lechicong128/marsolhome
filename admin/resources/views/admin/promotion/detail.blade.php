@extends('admin.layouts.index')
@section('content')
    <style>
        .tab-btn {
            flex: 1;
            padding: 15px 20px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: #6c757d;
        }

        .tab-btn.active {
            /*background: #3a94ef;*/
            color: white;
            /*box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);*/
            margin-right: 5px;
            border: 1;
        }
        .nav.nav-tabs > li.tab-btn.active > a {
            background-color: #3a94ef;
            color: white !important;
            border: 0;
            border-radius: 10px;
        }

        .tab-btn:hover:not(.active) {
            background: rgba(79, 172, 254, 0.1);
            color: #4facfe;
        }

        .tab-content.active {
            display: block;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('dt_promotion')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/promotion/list">{{lang('dt_promotion')}}</a></li>
                <li class="active">{{!empty($promotion) ? lang('dt_edit') : lang('dt_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form action="admin/promotion/detail/{{$id}}" method="post" id="promotionForm" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <div class="card-box">
                    <div class="row">
                        <ul class="nav nav-tabs nav-justified row hide" style="margin-bottom: 10px">
                            @foreach($language as $lang)
                                <li class="tab-btn {{$lang->is_default ? 'active' : ''}}">
                                    <a href="#tab-info-{{$lang->code}}" data-toggle="tab" aria-expanded="false">
                                        <span class="visible-xs"><i class="fa fa-home"></i></span>
                                        <span class="hidden-xs">{{$lang->name}}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="col-md-12">
                            <div class="col-md-6">
                                <div class="tab-content row" style="padding-left:10px;padding-right:10px;">
                                @foreach($language as $lang)
                                    <div id="tab-info-{{$lang->code}}" class="tab-pane fade {{$lang->is_default ? 'in active' : ''}}">
                                        <div class="form-group">
                                            <label for="code{{$lang->code}}">{{lang('dt_code_promotion')}}</label>
                                            <input required type="text" name="code[{{$lang->code}}]" autocomplete="off"
                                                   value="{{$promotion['translations'][$lang->code]['code'] ?? ''}}" class="form-control code{{$lang->code}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="name{{$lang->code}}">{{lang('dt_name_promotion')}}</label>
                                            <input required type="text" name="name[{{$lang->code}}]" autocomplete="off"
                                                   value="{{$promotion['translations'][$lang->code]['name'] ?? ''}}" class="form-control name{{$lang->code}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="detail">{{lang('dt_detail')}}</label>
                                            <input type="text" name="detail[{{$lang->code}}]" autocomplete="off"
                                                   value="{{$promotion['translations'][$lang->code]['detail'] ?? ''}}" class="form-control detail{{$lang->code}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="note{{$lang->code}}">{{lang('dt_content')}}</label>
                                            <textarea type="text" name="note[{{$lang->code}}]" autocomplete="off"
                                                      cols="2" rows="3" class="form-control note{{$lang->code}} editor">{{$promotion['translations'][$lang->code]['note'] ?? ''}}
                                    </textarea>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image">{{lang('dt_image')}}</label>
                                    <input type="file" name="image" id="image" class="filestyle image"
                                           data-buttonbefore="true">
                                    @if(!empty($promotion) && $promotion['image'] != null)
                                        @php
                                            $dtImage = $promotion['image'];
                                        @endphp
                                        {!! loadImage($dtImage, '200px', 'img-rounded',$promotion['image'],false,'150px'); !!}
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="name">{{lang('dt_type_promotion')}}</label>
                                    <div class="radio radio-info radio-inline">
                                        <input type="radio" id="type1" value="0" name="type" {{!empty($promotion) && $promotion['type'] == 0  ? 'checked' : 'checked'}}>
                                        <label for="type1">{{lang('dt_percent')}}</label>
                                    </div>
                                    <div class="radio radio-info radio-inline">
                                        <input type="radio" id="type2" value="1" name="type" {{!empty($promotion) && $promotion['type'] == 1  ? 'checked' : ''}}>
                                        <label for="type2">{{lang('dt_cash')}}</label>
                                    </div>
                                </div>
                                <div class="row show_percent {{!empty($promotion) ? ($promotion['type'] == 0  ? '' : 'hide') : '' }}">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="percent">{{lang('dt_percent')}}</label>
                                            <input type="text" name="percent" autocomplete="off" id="percent" min="0" max="100" onchange="formatNumBerKeyChange(this)"
                                                   value="{{!empty($promotion) ? $promotion['percent'] : 0}}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="money_max">{{lang('dt_money_max')}}</label>
                                            <input type="text" name="money_max" autocomplete="off"
                                                   value="{{!empty($promotion) ? formatMoney($promotion['money_max']) : ''}}" onchange="formatNumBerKeyChange(this)" class="form-control money_max">
                                        </div>
                                    </div>
                                </div>
                                <div class="row show_cash {{!empty($promotion) && $promotion['type'] == 1  ? '' : 'hide'}} ">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="cash">{{lang('dt_cash')}}</label>
                                            <input type="text" name="cash" autocomplete="off" id="cash"
                                                   value="{{!empty($promotion) ? formatMoney($promotion['cash']) : 0}}" onchange="formatNumBerKeyChange(this)" class="form-control cash">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="indefinite">{{lang('dt_indefinite')}}</label>
                                        <br>
                                        <input type="checkbox" name="indefinite" {{!empty($promotion) && $promotion['indefinite'] == 1 ? 'checked' : ''}} class="indefinite" data-plugin="switchery" data-color="#5fbeaa" data-switchery="true"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="indefinite">{{lang('dt_use_one')}}</label>
                                        <br>
                                        <input type="checkbox" name="type_use_one" {{!empty($promotion) && $promotion['type_use_one'] == 1 ? 'checked' : ''}} class="type_use_one" data-plugin="switchery" data-color="#5fbeaa" data-switchery="true"></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_start">{{lang('dt_date_start')}}</label>
                                            <input type="text" name="date_start" autocomplete="off" id="date_start"
                                                   value="{{!empty($promotion) ? _dthuan($promotion['date_start']) : ''}}" {{!empty($promotion) && $promotion['indefinite'] == 1 ? 'readonly' : ''}} class="form-control date_start datepicker">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_end">{{lang('dt_date_end')}}</label>
                                            <input type="text" name="date_end" autocomplete="off"
                                                   value="{{!empty($promotion) ?  _dthuan($promotion['date_end']) : ''}}" {{!empty($promotion) && $promotion['indefinite'] == 1 ? 'readonly' : ''}}  class="form-control date_end datepicker">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="name">{{lang('dt_type')}}</label>
                                    <div class="radio radio-info radio-inline">
                                        <input type="radio" id="type_customer1" value="0" name="type_customer" {{!empty($promotion) && $promotion['type_customer'] == 0  ? 'checked' : 'checked'}}>
                                        <label for="type_customer1">{{lang('dt_all_customer')}}</label>
                                    </div>
                                    <div class="radio radio-info radio-inline">
                                        <input type="radio" id="type_customer2" value="1" name="type_customer" {{!empty($promotion) && $promotion['type_customer'] == 1  ? 'checked' : ''}}>
                                        <label for="type_customer2">{{lang('dt_one_customer')}}</label>
                                    </div>
                                </div>
                                <div class="row show-customer {{!empty($promotion) && $promotion['type_customer'] == 1  ? '' : 'hide'}} ">
                                    <div class="col-md-12">
                                        <label for="customer_id">{{lang('client')}}</label>
                                        <select class="customer_id select2" id="customer_id"
                                                data-placeholder="Chọn ..." name="customer_id">
                                            <option></option>
                                            @if(!empty($promotion))
                                                @if(!empty($promotion['customer']))
                                                    @foreach($promotion['customer'] as $key => $value)
                                                        @php
                                                        $customer = $value['customer'];
                                                        @endphp
                                                        <option value="{{$customer['id']}}"
                                                                selected>{{$customer['fullname']}} - {{!empty($customer['phone']) ? $customer['phone'] : '' }}</option>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right m-b-0">
                        <button class="btn btn-primary waves-effect waves-light" type="submit">
                            {{lang('dt_save')}}
                        </button>
                        <button type="reset" class="btn btn-default waves-effect waves-light m-l-5">
                            {{lang('dt_cancel')}}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            searchAjaxSelect2('#customer_id', 'admin/category/searchCustomer', 0, {type_client: -1})
        })
        function formatRepo (repo) {
            return `${repo.text} - ${repo.phone != null ? repo.phone : ''}`;
        }
        function formatRepoSelection (repo) {
            return `${repo.text} - ${repo.phone != null ? repo.phone : ''}`;
        }

        $(document).on('change', '[name="type"]', function (e) {
            type = $(this).val();
            if(type == 0){
                $(".show_percent").removeClass('hide');
                $(".show_cash").addClass('hide');
            } else if(type == 1){
                $(".show_percent").addClass('hide');
                $(".show_cash").removeClass('hide');
            }
        })
        $(document).on('change', '[name="type_customer"]', function (e) {
            type_customer = $(this).val();
            if(type_customer == 0){
                $(".show-customer").addClass('hide');
                $(".customer_id").select2('val',' ');
            } else if(type_customer == 1){
                $(".show-customer").removeClass('hide');
            }
        })

        $(document).on('change', '[name="indefinite"]', function (e) {
            checked = $(this).is(':checked');
            if(checked){
                $(".date_start").val('');
                $(".date_end").val('');
                $(".date_start").attr('readonly',true);
                $(".date_end").attr('readonly',true);
            } else {
                $(".date_start").attr('readonly',false);
                $(".date_end").attr('readonly',false);
            }
        })
        $("#promotionForm").validate({
            rules: {
                name: {
                    required: true,
                },
                code: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: "{{lang('dt_required')}}",
                },
                code: {
                    required: "{{lang('dt_required')}}",
                },

            },
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
                            window.location.href='admin/promotion/list';
                        } else {
                            alert_float('error',data.message);
                        }
                    })
                    .fail(function (err) {
                        htmlError = '';
                        for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                        alert_float('error',htmlError);
                    });
                return false;
            }
        });
    </script>
@endsection
