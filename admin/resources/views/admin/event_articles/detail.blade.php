@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <style>
        .b-r-1 {
            border-radius: 5px;
            border: 1px solid #eff0f3;
        }
        .pull-left {
            float: left !important;
        }
    </style>
    <style>
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }


        .language-tabs {
            display: flex;
            margin-bottom: 30px;
            background: #f8f9fa;
            border-radius: 15px;
            padding: 5px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

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
        .font-30 {
            font-size: 30px;
        }
        .width-10-radio{
           width: 10%;
        }
        .input-sm {
            padding-top:5px;
            padding-bottom:5px;
        }
    </style>
    <style>
        .brs-20 {
            border-radius: 20px;
        }
        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .customer-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            /*background: linear-gradient(135deg, #ff6b6b, #feca57);*/
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }

        .customer-details h4 {
            margin-top: 0px;
            margin-bottom: 0px;
            font-size: 15px;
            color: #2c3e50;
        }

        .customer-details span {
            color: #7f8c8d;
            font-size: 12px;
        }


        .product-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e9ecef;
        }
        .rating {
            /*display: flex;*/
            gap: 2px;
        }

        .star {
            color: #ffd700;
            font-size: 16px;
        }

        .star.empty {
            color: #ddd;
        }
        .media-badge {
            padding: 6px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .media-photos {
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            color: #2d3436;
        }

        .media-video {
            background: linear-gradient(135deg, #ff9a9e, #fecfef);
            color: #2d3436;
        }
    </style>
    <style>
        .product-info .product-img img { border: 1px solid #eef1f5; }
        .product-info strong { line-height: 1.15; }
        .see-more-toggle { font-weight: 600; transition: all .2s ease; margin-top: 10px;}
        .see-more-toggle:hover { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('c_products')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/event_articles/list">{{lang('c_event_articles')}}</a></li>
                <li class="active">{{$title ?? ''}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form action="admin/event_articles/submit/{{!empty($id) ? $id : 0}}" method="post" id="EventArticlesForm" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}

            <div class="col-lg-12">
                <div class="row">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#tab-info" data-toggle="tab" aria-expanded="false">
                                <span class="visible-xs"><i class="fa fa-home"></i></span>
                                <span class="hidden-xs">{{lang('info')}}</span>
                            </a>
                        </li>
                        <li>
                            <a href="#tab-sponsor" data-toggle="tab" aria-expanded="true">
                                <span class="visible-xs"><i class="fa fa-user"></i></span>
                                <span class="hidden-xs">{{lang('is_sponsor')}}</span>
                            </a>
                        </li>
                        <li>
                            <a href="#tab-image" data-toggle="tab" aria-expanded="true">
                                <span class="visible-xs"><i class="fa fa-user"></i></span>
                                <span class="hidden-xs">{{lang('c_list_images')}}</span>
                            </a>
                        </li>
                        @if(!empty($event_articles['id']))
                            <li>
                                <a href="#tab-challenge_me_false" id="tabChallengeFalse" data-toggle="tab" aria-expanded="true">
                                    <span class="hidden-xs">{{lang('c_list_challenge_me_false_donations')}}</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                    <div class="tab-content">
                        <div id="tab-info" class="tab-pane fade in active">
                            <div class="card-box">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="image">{{lang('image_main')}}</label>
                                        <input type="file" name="image" id="image" class="filestyle image"
                                               data-buttonbefore="true">
                                        @if(!empty($event_articles) && $event_articles['image'] != null)
                                            <input type="hidden" name="image_old" id="image_old"
                                                   class="image_old"
                                                   data-buttonbefore="true" value="{{!empty($event_articles) ? $event_articles['image'] : ''}}">
                                            <div style="display: flex;justify-content:center;margin-top: 5px"
                                                 class="show_image">
                                                <img src="{{$event_articles['image'] ?? ''}}" alt="image"
                                                     class="img-responsive img-circle"
                                                     style="width: 150px;height: 150px">
                                            </div>
                                        @else
                                            <div style="display: flex;justify-content:center;margin-top: 5px"
                                                 class="show_image">
                                                <img src="admin/assets/images/not_available.jpg" alt="image"
                                                     class="img-responsive img-circle"
                                                     style="width: 150px;height: 150px">

                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="code">{{lang('c_code_event_articles')}}</label>
                                        <input type="text" id="code" name="code" autocomplete="off"
                                               value="{{!empty($event_articles) ? $event_articles['code'] : ''}}" class="form-control code">
                                    </div>
                                    <div class="form-group">
                                        <label for="code">{{lang('c_slug_event_articles')}}</label>
                                        <input type="text" id="slug" name="slug" autocomplete="off"
                                               value="{{!empty($event_articles) ? $event_articles['slug'] : ''}}" class="form-control slug">
                                    </div>
                                    <div class="form-group">
                                        <label for="type_event_articles">{{lang('c_type_event_articles')}}</label>
                                        <select class="form-control select2" name="type_event_articles" id="type_event_articles" required style="width: 100%;height: 35px">
                                            <option value="1" {{!empty($event_articles) && $event_articles['type_event_articles'] == 1 ? 'selected' : ''}}>{{lang('event')}}</option>
                                            <option value="2" {{!empty($event_articles) && $event_articles['type_event_articles'] == 2 ? 'selected' : ''}}>{{lang('challenge')}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="code">{{lang('c_date_start_event')}}</label>
                                        <input type="text" id="date_start_event" name="date_start_event" autocomplete="off"
                                               value="{{!empty($event_articles) ? _dt($event_articles['date_start_event']) : ''}}" class="form-control datetimepicker">
                                    </div>
                                    <div class="form-group">
                                        <label for="code">{{lang('c_date_end_event')}}</label>
                                        <input type="text" id="date_end_event" name="date_end_event" autocomplete="off"
                                               value="{{!empty($event_articles) ? _dt($event_articles['date_end_event']) : ''}}" class="form-control datetimepicker">
                                    </div>
                                    <div class="form-group">
                                        <label for="background_color">{{lang('c_background_color')}}</label>
                                        <input type="color" id="background_color" name="background_color" autocomplete="off"
                                               value="{{!empty($event_articles) ? $event_articles['background_color'] : ''}}" class="form-control background_color">
                                    </div>
                                    <div class="form-group">
                                        <label for="type_sponsor">{{lang('c_sponsor')}}</label>
                                        <select class="form-control select2" name="type_sponsor" id="type_sponsor" required style="width: 100%;height: 35px">
                                            <option value="1" {{!empty($event_articles) && $event_articles['sponsor'] == 1 ? 'selected' : ''}}>{{lang('in_prizes')}}</option>
                                            <option value="2" {{!empty($event_articles) && $event_articles['sponsor'] == 2 ? 'selected' : ''}}>{{lang('in_product')}}</option>
                                        </select>
                                    </div>

                                    <div class="form-group type_sponsor type_sponsor_1 {{empty($event_articles['sponsor']) || $event_articles['type_sponsor'] == 1 ? '' : 'hide'}}">
                                        <label for="prizes">{{lang('total_prizes')}}</label>
                                        <input type="text" onchange="formatNumBerKeyChange(this)" id="prizes" name="prizes" autocomplete="off"
                                               value="{{!empty($event_articles) ? number_format($event_articles['prizes']) : ''}}" class="form-control">
                                    </div>
                                    <div class="form-group type_sponsor type_sponsor_2 {{(!empty($event_articles['sponsor']) && $event_articles['type_sponsor'] == 2) ? '' : 'hide'}}">
                                        <label for="total_product">{{lang('c_total_product')}}</label>
                                        <input type="text" onchange="formatNumBerKeyChange(this)" id="total_product" name="total_product" autocomplete="off"
                                               value="{{!empty($event_articles) ? number_format($event_articles['total_product']) : ''}}" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="total_prizes">{{lang('total_money_prizes')}}</label>
                                        <input type="text" onchange="formatNumBerKeyChange(this)" id="total_money_prizes" name="total_money_prizes" autocomplete="off"
                                               value="{{!empty($event_articles) ? number_format($event_articles['total_money_prizes']) : ''}}" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="product_id">{{lang('c_products')}}</label>
                                        <select class="product_id select2" id="product_id" data-placeholder="Chọn ..." name="product_id[]" multiple data-json="{{(json_encode($products))}}">
                                            @if(!empty($products))
                                                @foreach($products as $product)
                                                    <option value="{{$product->id}}" selected><img class="img_option" src="{{$product->image}}"/>{{$product->name}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="col-md-12">
                                        <ul class="nav nav-tabs nav-justified hide">
                                            @foreach($language as $lang)
                                                <li class="tab-btn {{$lang['is_default'] ? 'active' : ''}}">
                                                    <a href="#tab-info-{{$lang->code}}" data-toggle="tab" aria-expanded="false">
                                                        <span class="visible-xs"><i class="fa fa-home"></i></span>
                                                        <span class="hidden-xs">{{$lang->name}}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="tab-content">
                                            @foreach($language as $lang)
                                                <div id="tab-info-{{$lang->code}}" class="tab-pane fade {{$lang['is_default'] ? 'in active' : ''}}">
                                                    <div class="form-group">
                                                        <label for="name">{{lang('c_name_event_articles')}} - {{$lang->name}}</label>
                                                        <input type="text" name="name[{{$lang->code}}]" autocomplete="off"
                                                               value="{{!empty($event_articles['translations'][$lang->code]) ? $event_articles['translations'][$lang->code]['name'] : ''}}" class="form-control name">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="name">{{lang('c_content_short')}} - {{$lang->name}}</label>
                                                        <textarea type="text" name="content[{{$lang->code}}]" autocomplete="off" class="form-control content">{{!empty($event_articles['translations'][$lang->code]) ? $event_articles['translations'][$lang->code]['content'] : ''}}</textarea>
                                                    </div>
                                                    <hr/>
                                                    <h3 style="margin-bottom: 25px; color: #495057; font-weight: 400;">🧩 {{lang('table_info_event')}}</h3>
                                                    <ul class="nav nav-tabs">
                                                        <li class="active">
                                                            <a data-toggle="tab" href="#tab-{{$lang->code}}-info-event_1">
                                                                {{!empty($event_articles['info_event'][$lang->code][1]) ? $event_articles['info_event'][$lang->code][1]['title'] : langStran('event_rules', $lang->code_system)}}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a data-toggle="tab" href="#tab-{{$lang->code}}-info-event_2">
                                                                {{!empty($event_articles['info_event'][$lang->code][2]) ? $event_articles['info_event'][$lang->code][2]['title'] : langStran('eligibility', $lang->code_system)}}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a data-toggle="tab" href="#tab-{{$lang->code}}-info-event_3">
                                                                {{!empty($event_articles['info_event'][$lang->code][3]) ? $event_articles['info_event'][$lang->code][3]['title'] : langStran('new_user_task', $lang->code_system)}}
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content row">
                                                        <div id="tab-{{$lang->code}}-info-event_1" class="tab-pane fade in active">
                                                            @php $i = 1; @endphp
                                                            <div class="form-group">
                                                                <input type="text"
                                                                       data-class="tab-{{$lang->code}}-info-event_{{$i}}"
                                                                       name="info_event[{{$lang->code}}][{{$i}}][title]" autocomplete="off"
                                                                       value="{{!empty($event_articles['info_event'][$lang->code][$i]) ? $event_articles['info_event'][$lang->code][$i]['title'] : langStran('event_rules', $lang->code_system)}}"
                                                                       class="form-control title_event"
                                                                       placeholder="{{lang('c_title_info_event')}}">
                                                            </div>
                                                            <div class="form-group">
                                                                <textarea type="text" name="info_event[{{$lang->code}}][{{$i}}][content]" autocomplete="off"
                                                                          cols="2" rows="5" class="form-control content editor_content"
                                                                          placeholder="{{lang('c_content')}}"
                                                                >{{!empty($event_articles['info_event'][$lang->code][$i]) ? $event_articles['info_event'][$lang->code][$i]['content'] : ''}}</textarea>
                                                            </div>
                                                        </div>
                                                        <div id="tab-{{$lang->code}}-info-event_2" class="tab-pane fade">
                                                            @php $i = 2; @endphp
                                                            <div class="form-group">
                                                                <input type="text"
                                                                       data-class="tab-{{$lang->code}}-info-event_{{$i}}"
                                                                       name="info_event[{{$lang->code}}][{{$i}}][title]" autocomplete="off"
                                                                       value="{{!empty($event_articles['info_event'][$lang->code][$i]) ? $event_articles['info_event'][$lang->code][$i]['title'] : langStran('eligibility', $lang->code_system)}}"
                                                                       class="form-control title_event"
                                                                       placeholder="{{lang('c_title_info_event')}}">
                                                            </div>
                                                            <div class="form-group">
                                                                <textarea type="text" name="info_event[{{$lang->code}}][{{$i}}][content]" autocomplete="off"
                                                                          cols="2" rows="5" class="form-control content editor_content"
                                                                          placeholder="{{lang('c_content')}}"
                                                                >{{!empty($event_articles['info_event'][$lang->code][$i]) ? $event_articles['info_event'][$lang->code][$i]['content'] : ''}}</textarea>
                                                            </div>
                                                        </div>
                                                        <div id="tab-{{$lang->code}}-info-event_3" class="tab-pane fade">
                                                            @php $i = 3; @endphp
                                                            <div class="form-group">
                                                                <input type="text"
                                                                       data-class="tab-{{$lang->code}}-info-event_{{$i}}"
                                                                       name="info_event[{{$lang->code}}][{{$i}}][title]" autocomplete="off"
                                                                       value="{{!empty($event_articles['info_event'][$lang->code][$i]) ? $event_articles['info_event'][$lang->code][$i]['title'] : langStran('new_user_task', $lang->code_system)}}"
                                                                       class="form-control title_event"
                                                                       placeholder="{{lang('c_title_info_event')}}">
                                                            </div>
                                                            <div class="form-group">
                                                                <textarea type="text" name="info_event[{{$lang->code}}][{{$i}}][content]" autocomplete="off"
                                                                          cols="2" rows="5" class="form-control content editor_content"
                                                                          placeholder="{{lang('c_content')}}"
                                                                >{{!empty($event_articles['info_event'][$lang->code][$i]) ? $event_articles['info_event'][$lang->code][$i]['content'] : ''}}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                          @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div id="tab-sponsor" class="tab-pane fade">
                            <div class="card-box">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="sponsor">{{lang('c_sponsor')}}</label>
                                        <select class="form-control select2" name="sponsor" id="sponsor" required style="width: 100%;height: 35px">
                                            <option value="0">{{lang('not_sponsor')}}</option>
                                            <option value="1" {{!empty($event_articles) && $event_articles['sponsor'] == 1 ? 'selected' : ''}}>{{lang('sponsor_m4u')}}</option>
                                            <option value="2" {{!empty($event_articles) && $event_articles['sponsor'] == 2 ? 'selected' : ''}}>{{lang('sponsor_other')}}</option>
                                        </select>
                                    </div>

                                    <div class="form-group sponsor {{empty($event_articles['sponsor']) ? 'hide' : ''}}">
                                        <label for="name_sponsor">{{lang('name_sponsor')}}</label>
                                        <input type="text" id="name_sponsor" name="name_sponsor" autocomplete="off"
                                               value="{{!empty($event_articles) ? $event_articles['name_sponsor'] : ''}}" class="form-control name_sponsor">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group sponsor {{empty($event_articles['sponsor']) ? 'hide' : ''}}">
                                        <label for="image">{{lang('logo_sponsor')}}</label>
                                        <input type="file" name="image_sponsor" id="image_sponsor" class="filestyle image"
                                               data-buttonbefore="true">
                                        @if(!empty($event_articles) && $event_articles['image_sponsor'] != null)
                                            <input type="hidden" name="image_sponsor_old" id="image_sponsor_old"
                                                   class="image_old"
                                                   data-buttonbefore="true" value="{{!empty($event_articles) ? $event_articles['image_sponsor'] : ''}}">
                                            <div style="display: flex;justify-content:center;margin-top: 5px"
                                                 class="show_image_sponsor">
                                                <img src="{{$event_articles['image_sponsor'] ?? ''}}" alt="image"
                                                     class="img-responsive img-circle"
                                                     style="width: 150px;height: 150px">
                                            </div>
                                        @else
                                            <div style="display: flex;justify-content:center;margin-top: 5px"
                                                 class="show_image_sponsor">
                                                <img src="admin/assets/images/not_available.jpg" alt="image"
                                                     class="img-responsive img-circle"
                                                     style="width: 150px;height: 150px">

                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <hr/>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div id="tab-image" class="tab-pane fade">
                            <div class="card-box">
                                <div class="form-group mtop40">
                                    <label for="images">{{lang('c_list_images')}}</label>
                                    <input type="file" class="filestyle images" name="images[]" id="images" multiple="true" data-buttonbefore="true">
                                </div>
                                <div class="clearfix"></div>
                                <hr/>
                                <div class="span_class c_galary_images">
                                    <div class="sortable_div">
                                        @php
                                            $stt = 0;
                                        @endphp
                                        @if (!empty($event_articles['list_images']))
                                            @foreach ($event_articles['list_images'] as $key => $value)
                                                @php $stt++; @endphp
                                                <div class="col-md-3 mbot10 cfile cfile_{{$value['id']}}" data-id="{{$value['id']}}" data-name="<?= $value['image'] ?>">
                                                    <input type="hidden" class="order_images" name="order_images[{{$value['id']}}]" value="{{$stt}}">
                                                    <div class="pull-left">
                                                        {!!ViewImage($value['image'])!!}
                                                    </div>
                                                    <span class="pull-right text-danger pointer" title="<?= lang('c_delete_file') ?>" data-toggle="tooltip" onclick="deleteFile({{$value['id']}}, this)"><i class="fa fa-remove"></i></span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <div id="imagesDelete"></div>
                                <div class="clearfix"></div>
                                <hr/>
                            </div>
                        </div>
                        @if(!empty($event_articles['id']))
                            <div id="tab-challenge_me_false" class="tab-pane fade">
                                <div class="card-box">
                                    <table id="table_challenge_me_false" class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th class="text-center">{{lang('c_stt')}}</th>
                                            <th class="text-center">{{lang('c_customer_join')}}</th>
                                            <th class="text-center">{{lang('challenge')}}</th>
                                            <th class="text-center">{{lang('c_deposit')}}</th>
                                            <th class="text-center">{{lang('c_completion_rate')}}</th>
                                            <th class="text-center">{{lang('dt_date_join')}}</th>
                                            <th class="text-center">{{lang('c_status_challenge_me')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                        <tr>
                                            <td class="text-center" colspan="3"><b>{{lang('dt_total')}}</b></td>
                                            <td class="text-right"><b id="total_deposit"></b></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @endif
                        <div class="form-group text-right m-b-0 submit-div">
                            <button class="btn btn-primary waves-effect waves-light" type="submit">
                                {{lang('dt_save')}}
                            </button>
                            <button type="reset" class="btn btn-default waves-effect waves-light m-l-5">
                                {{lang('dt_cancel')}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <input type="hidden" id="id_event_articles" value="{{!empty($id) ? $id : 0}}">
    <input type="hidden" id="status_challenge_me_search" value="2">
    <!-- end row -->
@endsection
@section('script')
    <script>
        editor_config['height'] = 600;
        $(function() {
            searchAjaxSelect2Img('#product_id','api/category/getListProduct', 0, {
                'select2':true
            })
        })

        $('.select_2').select2();
        $('#type_sponsor').change(function() {
            $('.type_sponsor').addClass('hide');
            if($(this).val() == 1) {
                $('.type_sponsor_1').removeClass('hide');
            }
            else {
                $('.type_sponsor_2').removeClass('hide');
            }
        })

        $('#sponsor').change(function() {
            $('.sponsor').addClass('hide');
            if($(this).val() > 0) {
                $('.sponsor').removeClass('hide');
            }
        })
        $('.title_event').change(function() {
            let idTabName = $(this).attr('data-class');
            var nameTitle = $(this).val().trim();
            if(nameTitle == '') {
                nameTitle = '-';
            }
            $(`a[href="#${idTabName}"]`).text(nameTitle);
        })


        $("#EventArticlesForm").validate({
            rules: {
                name: {
                    required: true,
                },
                type_event_articles: {
                    required: true,
                },
                sponsor: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: "{{lang('dt_required')}}",
                },
                type_event_articles: {
                    required: "{{lang('dt_required')}}",
                },
                sponsor: {
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
                            window.location.href='admin/event_articles/list';
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



        var countImg = 0;
        function AppendImgProduct() {
            $(`#row-img`).append(`<div class="col-md-3">
                                        <div class="form-group">
                                            <input name="img[${countImg}]" type="file" id="img_${countImg}" style="display: none;" accept="image/*">
                                            <input name="img_${countImg}_before" type="hidden" value="">
                                            <img data-id="img_${countImg}" class="preview-image"
                                                 onerror="this.onerror=null; this.src='admin/assets/images/not_available.jpg';"
                                                 src="{{asset('storage/')}}" title="Click vào để đổi ảnh">
                                        </div>
                                    </div>`);
            countImg++;
        }

        function toggleTr(_this) {
            $(_this).find('i').toggleClass('fa-caret-right fa-caret-down');
            $(_this).closest('tr').find('.content-ingredients').toggle();
        }


        $('body').on('click', '.preview-image', function() {
            var idInput = $(this).attr('data-id');
            $(`#${idInput}`).click();
            $(`#${idInput}`).change(function(event) {
                var input = event.target;
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(`.preview-image[data-id="${idInput}"]`).attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            });
        })



        function clickImage(_this){
            id = $(_this).attr('data-id');
            $(`#section_2_image_${id}`).click();
        }

        $('.sortable_div').sortable({
            start:function() {},
            stop:function(){
                OrderImages();
            }
        });

        function OrderImages(){
            var stt = 0;
            $('.sortable_div .cfile').each(function(index, value){
                stt++;
                $(this).find('.order_images').val(stt);
            });
        }

        function deleteFile(id, _this) {
            $('#imagesDelete').append(`<input type="hidden" name="imagesDelete[]" value="${id}"/>`);
            $(_this).parents('.cfile').remove();
        }

    </script>

    <script>
        $('#tabChallengeFalse').on('shown.bs.tab', function (e) {
            console.log(1);
            $('.submit-div').hide();
        });

        $('#tabChallengeFalse').on('hidden.bs.tab', function (e) {
            console.log(2);
            $('.submit-div').show();
        });

        var fnserverparams = {
            'event_articles_search': '#id_event_articles',
            'status_search': '#status_challenge_me_search',
        };
        var oTable;
        oTable = InitDataTable('#table_challenge_me_false', 'admin/challenge_me/getList', {
            'order': [
                [5, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/challenge_me/getList",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            columnDefs: [
                {   "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'DT_RowIndex', name: 'DT_RowIndex',width: "80px" },
                {data: 'customer', name: 'customer',width: "80px"},
                {data: 'information_challenge', name: 'information_challenge'},
                {data: 'deposit', name: 'deposit'},
                {data: 'completion_rate', name: 'completion_rate'},
                {data: 'date', name: 'date'},
                {data: 'status', name: 'status'},

            ]
        });
        $('#table_challenge_me_false').on('draw.dt', function () {
            var table = $(this).DataTable();
            let total = table
                .column(3, { page: 'current' })
                .data()
                .reduce(function (sum, val) {
                    // bỏ HTML
                    val = $('<div>').html(val).text();

                    // bỏ dấu ,
                    val = val.replace(/,/g, '');

                    return sum + (parseInt(val, 10) || 0);
                }, 0);
            $("#table_challenge_me_false").find('tfoot #total_deposit').html(formatNumber(total));
        });
    </script>
@endsection
