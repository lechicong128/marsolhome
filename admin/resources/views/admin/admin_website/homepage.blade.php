@extends('admin.layouts.index')
@section('content')
    <style>
        .thumb_new > .bootstrap-filestyle{
            display: none;
        }
        .product-list-box{
            min-height: 590px !important;
        }
    </style>
    <style>
        #ti_text {
            height: 100%;
        }

        h2 {
            color: #003197;
            font-size: 24px;
            font-weight: bold;
        }

        .clearfix {
            margin-top: 15px !important;
        }

        .preview-image {
            max-width: 100%;
            max-height: 100%;
        }

        .preview-image:hover {
            cursor: pointer;
        }

        .input_main {
            text-align: center;
            height: 50px !important;
            line-height: 50px !important;
            font-size: 2rem !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input_sub {
            text-align: center;
        }

        .tnh-tb,
        .tnh-tb tr th,
        .tnh-tb tr td {
            /* border: 1px solid #9e9e9ea3 !important;
            padding: 10px !important; */
            vertical-align: text-top !important;
        }

        .mix-blend-difference {
            mix-blend-mode: difference !important;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(16px);
            -ms-transform: translateX(16px);
            transform: translateX(16px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
        .d-item {
            margin-top: 20px!important;
        }
        .preview-image {
            border: 1px solid #9b9898;
            padding: 5px;
            border-radius: 10px;
            width: 400px;
            height: 260px;
        }
        .bootstrap-filestyle {
            display: none!important;
        }
        .mce-panel {
            border-top: 1px solid #ddd!important;  /* thêm border trên */
        }
        .div-lang {display: none;}
        .div-lang.active {display: block;}
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('c_index')}}</a></li>
                <li><a>{{lang('dt_homepage')}}</a></li>
                <li class="active">{{lang('c_index')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form id="HomepageForm" action="admin/admin_website/submit_homepage" method="post" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <div class="card-box">
                    <div class="row">
                        <div class="">
                            <ul class="nav nav-tabs" role="tablist">
                                @php
                                    $lang_default = '';
                                @endphp
                                @if(!empty($lang_current))
                                    @foreach($lang_current as $key => $value)
                                        @php
                                            if(!empty($value['is_default'])) {
                                                $lang_default = $value['code'];
                                            }
                                        @endphp
                                        <li role="presentation" class="{{$value['is_default'] ? 'active' : ''}}">
                                            <a onclick="ShowTab('{{$value['code']}}')" href="#tab-{{$value['code']}}" aria-controls="tab" role="tab" data-toggle="tab">{{$value['name']}}</a>
                                        </li>
                                        <input type="hidden" name="allLang[{{$key}}]" value="{{$value['code']}}">
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                    <hr/>
                    <div class="tab-content">
                        <section>
                            <div class="col-md-12 title_section">Section 1 (Banner)</div>
                            @if(!empty($lang_current))
                                @foreach($lang_current as $key => $value)
                                    <div class="col-md-6 div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="section1_{{$value['code']}}_title">{{lang('dt_title')}}</label>
                                            <textarea type="text" name="section1[{{$value['code']}}][title]"
                                                      id="section1_{{$value['code']}}_title"
                                                      class="form-control editor_short">{{$homePage[$value['code']]->section1->title ?? ''}}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="section1_{{$value['code']}}_title_button">{{lang('c_title_button_sign_up')}}</label>
                                            <input type="text" name="section1[{{$value['code']}}][title_button]"
                                                   id="section1_{{$value['code']}}_title_button"
                                                   class="form-control" value="{{$homePage[$value['code']]->section1->title_button ?? ''}}"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6 div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="section1_{{$value['code']}}_content">{{lang('c_content_products')}}</label>
                                            <textarea type="text" name="section1[{{$value['code']}}][content]"
                                                      id="section1_{{$value['code']}}_content"
                                                      class="form-control editor_short">{{$homePage[$value['code']]->section1->content ?? ''}}</textarea>
                                        </div>
                                    </div>
{{--                                    <div class="col-md-12 div-lang div-{{$value['code']}}">--}}
{{--                                        <div class="form-group">--}}
{{--                                            <div class="checkbox">--}}
{{--                                                <input type="checkbox" {{!empty($homePage[$value['code']]->section1->is_background) ? 'checked' : ''}} id="section1_{{$value['code']}}_background" value="1" name="section1[{{$value['code']}}][is_background]" data-parsley-multiple="active">--}}
{{--                                                <label for="section1_{{$value['code']}}_background">{{lang('is_background')}}</label>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                @endforeach
                            @endif

                        </section>
                        <div class="clearfix"></div>
                        <hr/>
                        <section>
                            <div class="col-md-12 title_section">Section 2 ({{lang('c_title_step_home_2')}}...)</div>
                            @if(!empty($lang_current))
                                @foreach($lang_current as $key => $value)
                                    <div class="col-md-6  div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="section2_{{$value['code']}}_title">{{lang('dt_title')}}</label>
                                            <textarea type="text" name="section2[{{$value['code']}}][title]"
                                                      id="section2_{{$value['code']}}_title"
                                                      class="form-control editor_short">{{$homePage[$value['code']]->section2->title ?? ''}}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="section2_{{$value['code']}}_title_button">{{lang('c_title_button_sign_up')}}</label>
                                            <input type="text" name="section2[{{$value['code']}}][title_button]"
                                                   id="section2_{{$value['code']}}_title_button"
                                                   class="form-control" value="{{$homePage[$value['code']]->section2->title_button ?? ''}}"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6  div-lang div-{{$value['code']}}">
                                        <label for="section2_{{$value['code']}}_content">{{lang('c_content_products')}}</label>
                                        <textarea type="text"
                                                  name="section2[{{$value['code']}}][content]"
                                                  id="section2_{{$value['code']}}_content"
                                                  class="form-control editor_short">{{$homePage[$value['code']]->section2->content ?? ''}}</textarea>
                                    </div>
                                    <div class="col-md-12  div-lang div-{{$value['code']}}">
                                        <label for="section2_{{$value['code']}}_content_join">{{lang('c_title_customer_join')}}</label>
                                        <textarea type="text"
                                                  name="section2[{{$value['code']}}][content_join]"
                                                  id="section2_{{$value['code']}}_content_join"
                                                  class="form-control editor_short">{{$homePage[$value['code']]->section2->content_join ?? ''}}</textarea>
                                    </div>
                                @endforeach
                            @endif
                        </section>
                        <hr/>
                        <section>
                            <div class="col-md-12 title_section m-t-20">Section 3 ({{lang('c_title_step_home_3')}}...)</div>
                            @if(!empty($lang_current))
                                @foreach($lang_current as $key => $value)
                                    <div class="col-md-12  div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="section3_{{$value['code']}}_title">{{lang('dt_title')}}</label>
                                            <input type="text" name="section3[{{$value['code']}}][title]"
                                                      id="section3_{{$value['code']}}_title"
                                                      class="form-control" value="{{$homePage[$value['code']]->section3->title ?? ''}}"/>
                                        </div>
                                        <div class="form-group">
                                            <label for="section3_{{$value['code']}}_subtitle">{{lang('c_subtext_title')}}</label>
                                            <input type="text" name="section3[{{$value['code']}}][subtitle]"
                                                      id="section3_{{$value['code']}}_subtitle"
                                                      class="form-control" value="{{$homePage[$value['code']]->section3->subtitle ?? ''}}"/>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @for($i = 0; $i < 3; $i++)
                                <div class="col-md-4">
                                    @php
                                        $keyID = 'section3_tab_' . $i . '_img';
                                        $keyName = 'section3[tab][' . $i . '][img]';
                                        $keyBefore = 'section3[tab][' . $i . '][img_before]';
                                    @endphp
                                    <div class="form-group">
                                        <label for="{{$keyID}}">Hình Icon</label><br>
                                        <input name="{{$keyName}}" type="file" id="{{$keyID}}" style="display: none;" accept="image/*">
                                        <input name="{{$keyBefore}}" type="hidden" value="{{$homePage[$lang_default]->section3->tab[$i]->img ?? ''}}">
                                        <img data-id="{{$keyID}}" class="preview-image"
                                             onerror="this.onerror=null; this.src='admin/assets/images/not_available.jpg';"
                                             src="{{asset('storage/' . ($homePage[$lang_default]->section3->tab[$i]->img ?? ''))}}" title="{{lang('click_change_img')}}">
                                    </div>
                                    @if(!empty($lang_current))
                                        @foreach($lang_current as $key => $value)
                                            <div class="div-lang div-{{$value['code']}}">
                                                <div class="form-group">
                                                    <label for="section3_{{$value['code']}}_tab_{{$i}}_title">{{lang('dt_title')}}</label>
                                                    <input type="text" name="section3[{{$value['code']}}][tab][{{$i}}][title]"
                                                           id="section3_{{$value['code']}}_tab_{{$i}}_title"
                                                           class="form-control" value="{{$homePage[$value['code']]->section3->tab[$i]->title ?? ''}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="section3_{{$value['code']}}_tab_{{$i}}_subtitle">{{lang('dt_value_surcharge_car')}}</label>
                                                    <input type="text" name="section3[{{$value['code']}}][tab][{{$i}}][subtitle]"
                                                           id="section3_{{$value['code']}}_tab_{{$i}}_subtitle"
                                                           class="form-control" value="{{$homePage[$value['code']]->section3->tab[$i]->subtitle ?? ''}}">
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endfor
                        </section>
                        <div class="clearfix"></div>
                        <hr/>
                        <section>
                            <div class="col-md-12 title_section">Section 4 ({{lang('c_title_step_home_4')}}...)</div>
                            @if(!empty($lang_current))
                                @foreach($lang_current as $key => $value)
                                <div class="col-md-12  div-lang div-{{$value['code']}}">
                                    <div class="form-group">
                                        <label for="section4_{{$value['code']}}_title">{{lang('dt_title')}}</label>
                                        <textarea type="text" name="section4[{{$value['code']}}][title]"
                                               id="section4_{{$value['code']}}_title"
                                               class="form-control editor_short">{{$homePage[$value['code']]->section4->title ?? ''}}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="section4_{{$value['code']}}_subtitle">{{lang('c_subtext_title')}}</label>
                                        <input type="text" name="section4[{{$value['code']}}][subtitle]"
                                               id="section4_{{$value['code']}}_subtitle"
                                               class="form-control" value="{{$homePage[$value['code']]->section4->subtitle ?? ''}}">
                                    </div>
                                </div>
                              @endforeach
                            @endif
                            @for($i = 0; $i < 3; $i++)
                                <div class="col-md-4">
                                    <div class="form-group">
                                        @php
                                            $keyID = 'section4_tab_' . $i . '_icon';
                                            $keyName = 'section4[tab][' . $i . '][icon]';
                                            $keyBefore = 'section4[tab][' . $i . '][icon_before]';
                                        @endphp
                                        <label for="{{$keyID}}">Hình Icon</label><br>
                                        <input name="{{$keyName}}" type="file" id="{{$keyID}}" style="display: none;" accept="image/*">
                                        <input name="{{$keyBefore}}" type="hidden" value="{{$homePage[$lang_default]->section4->tab[$i]->icon ?? ''}}">
                                        <img data-id="{{$keyID}}" class="preview-image"
                                             onerror="this.onerror=null; this.src='admin/assets/images/not_available.jpg';"
                                             src="{{asset('storage/' . ($homePage[$lang_default]->section4->tab[$i]->icon ?? ''))}}" title="{{lang('click_change_img')}}">
                                    </div>
                                    @if(!empty($lang_current))
                                        @foreach($lang_current as $key => $value)
                                            <div class="div-lang div-{{$value['code']}}">
                                                <div class="form-group">
                                                    <label for="section4_{{$value['code']}}_tab_title_{{$i}}_header">{{lang('title_header')}}</label>
                                                    <input type="text" name="section4[{{$value['code']}}][tab][{{$i}}][title_header]"
                                                           id="section4_{{$value['code']}}_tab_title_{{$i}}_header"
                                                           class="form-control" value="{{$homePage[$value['code']]->section4->tab[$i]->title_header ?? ''}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="section4_{{$value['code']}}_tab_{{$i}}_title">{{lang('dt_title')}}</label>
                                                    <input type="text" name="section4[{{$value['code']}}][tab][{{$i}}][title]"
                                                           id="section4_{{$value['code']}}_tab_{{$i}}_title"
                                                           class="form-control" value="{{$homePage[$value['code']]->section4->tab[$i]->title ?? ''}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="section4_{{$value['code']}}_tab_{{$i}}_subtitle">{{lang('dt_value_surcharge_car')}}</label>
                                                    <input type="text" name="section4[{{$value['code']}}][tab][{{$i}}][subtitle]"
                                                           id="section4_{{$value['code']}}_tab_{{$i}}_subtitle"
                                                           class="form-control" value="{{$homePage[$value['code']]->section4->tab[$i]->subtitle ?? ''}}">
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    <div class="form-group">
                                        @php
                                            $keyID = 'section4_tab_' . $i . '_img';
                                            $keyName = 'section4[tab][' . $i . '][img]';
                                            $keyBefore = 'section4[tab][' . $i . '][img_before]';
                                        @endphp
                                        <label for="{{$keyID}}">Hình Icon</label><br>
                                        <input name="{{$keyName}}" type="file" id="{{$keyID}}" style="display: none;" accept="image/*">
                                        <input name="{{$keyBefore}}" type="hidden" value="{{$homePage[$lang_default]->section4->tab[$i]->img ?? ''}}">
                                        <img data-id="{{$keyID}}" class="preview-image"
                                             onerror="this.onerror=null; this.src='admin/assets/images/not_available.jpg';"
                                             src="{{asset('storage/' . ($homePage[$lang_default]->section4->tab[$i]->img ?? ''))}}" title="{{lang('click_change_img')}}">
                                    </div>
                                </div>
                            @endfor
                        </section>
                        <div class="clearfix"></div>
                        <hr/>
                        <section>
                            <div class="col-md-12 title_section">Section 5 ({{lang('c_title_step_home_5')}}...)</div>
                            @if(!empty($lang_current))
                                @foreach($lang_current as $key => $value)
                                    <div class="col-md-6 div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="section5_{{$value['code']}}_title">{{lang('dt_title')}}</label>
                                            <textarea type="text" name="section5[{{$value['code']}}][title]"
                                                      id="section5_{{$value['code']}}_title"
                                                      class="form-control editor_short">{{$homePage[$value['code']]->section5->title ?? ''}}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="section5_{{$value['code']}}_subtitle">{{lang('c_subtext_title')}}</label>
                                            <input type="text" name="section5[{{$value['code']}}][subtitle]"
                                                   id="section5_{{$value['code']}}_subtitle"
                                                   class="form-control" value="{{$homePage[$value['code']]->section5->subtitle ?? ''}}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="section5_{{$value['code']}}_title_button">{{lang('c_title_button_sign_up')}}</label>
                                            <input type="text" name="section5[{{$value['code']}}][title_button]"
                                                   id="section5_{{$value['code']}}_title_button"
                                                   class="form-control" value="{{$homePage[$value['code']]->section5->title_button ?? ''}}"/>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                @endforeach
                            @endif
                            <div class="col-md-1"></div>
                            @for($i = 0; $i < 5; $i++)
                                <div class="col-md-2">
                                    <div class="form-group">
                                        @php
                                            $keyID = 'section5_tab_' . $i . '_img';
                                            $keyName = 'section5[tab][' . $i . '][img]';
                                            $keyBefore = 'section5[tab][' . $i . '][img_before]';
                                        @endphp

                                        <label for="{{$keyID}}">Hình Icon</label><br>
                                        <input name="{{$keyName}}" type="file" id="{{$keyID}}" style="display: none;" accept="image/*">
                                        <input name="{{$keyBefore}}" type="hidden" value="{{$homePage[$lang_default]->section5->tab[$i]->img ?? ''}}">
                                        <img data-id="{{$keyID}}" class="preview-image"
                                             onerror="this.onerror=null; this.src='admin/assets/images/not_available.jpg';"
                                             src="{{asset('storage/' . ($homePage[$lang_default]->section5->tab[$i]->img ?? ''))}}" title="{{lang('click_change_img')}}">
                                    </div>
                                    @if(!empty($lang_current))
                                        @foreach($lang_current as $key => $value)
                                            <div class=" div-lang div-{{$value['code']}}">
                                                <div class="form-group">
                                                    <label for="section5_{{$value['code']}}_tab_{{$i}}_title">{{lang('dt_title')}}</label>
                                                    <input type="text" name="section5[{{$value['code']}}][tab][{{$i}}][title]"
                                                           id="section5_{{$value['code']}}_tab_{{$i}}_title"
                                                           class="form-control" value="{{$homePage[$value['code']]->section5->tab[$i]->title ?? ''}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="section5_{{$value['code']}}_tab_{{$i}}_subtitle">{{lang('dt_detail')}}</label>
                                                    <textarea type="text" name="section5[{{$value['code']}}][tab][{{$i}}][subtitle]"
                                                           id="section5_{{$value['code']}}_tab_{{$i}}_subtitle"
                                                           class="form-control">{{$homePage[$value['code']]->section5->tab[$i]->subtitle ?? ''}}</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="section5_{{$value['code']}}_tab_{{$i}}_name_step">{{lang('c_step')}}</label>
                                                    <input type="text" name="section5[{{$value['code']}}][tab][{{$i}}][name_step]"
                                                           id="section5_{{$value['code']}}_tab_{{$i}}_name_step"
                                                           class="form-control" value="{{$homePage[$value['code']]->section5->tab[$i]->name_step ?? ''}}">
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endfor
                            <div class="col-md-1"></div>
                        </section>
                        <div class="clearfix"></div>
                        <hr/>
                        <section>
                            <div class="col-md-12 title_section">Section 6 ({{lang('c_title_step_home_6')}}...)</div>
                            @if(!empty($lang_current))
                                @foreach($lang_current as $key => $value)
                                    <div class="col-md-12 div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="section6_{{$value['code']}}_title">{{lang('dt_title')}}</label>
                                            <textarea type="text" name="section6[{{$value['code']}}][title]"
                                                      id="section6_{{$value['code']}}_title"
                                                      class="form-control editor_short">{{$homePage[$value['code']]->section6->title ?? ''}}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="section6_{{$value['code']}}_subtitle">{{lang('c_subtext_title')}}</label>
                                            <input type="text" name="section6[{{$value['code']}}][subtitle]"
                                                   id="section6_{{$value['code']}}_subtitle"
                                                   class="form-control" value="{{$homePage[$value['code']]->section6->subtitle ?? ''}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="section6_{{$value['code']}}_title_button">{{lang('title_button_view_all')}}</label>
                                            <input type="text" name="section6[{{$value['code']}}][title_button]"
                                                   id="section6_{{$value['code']}}_title_button"
                                                   class="form-control" value="{{$homePage[$value['code']]->section6->title_button ?? ''}}"/>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </section>
                        <div class="clearfix"></div>
                        <hr/>
                        <section>
                            <div class="col-md-12 title_section">Section 7 ({{lang('c_title_step_home_7')}}...)</div>
                            @if(!empty($lang_current))
                                @foreach($lang_current as $key => $value)
                                    <div class="col-md-12 div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="section7_{{$value['code']}}_title">{{lang('dt_title')}}</label>
                                            <textarea type="text" name="section7[{{$value['code']}}][title]"
                                                      id="section7_{{$value['code']}}_title"
                                                      class="form-control editor_short">{{$homePage[$value['code']]->section7->title ?? ''}}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="section7_{{$value['code']}}_subtitle">{{lang('c_subtext_title')}}</label>
                                            <input type="text" name="section7[{{$value['code']}}][subtitle]"
                                                   id="section7_{{$value['code']}}_subtitle"
                                                   class="form-control" value="{{$homePage[$value['code']]->section7->subtitle ?? ''}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="section7_{{$value['code']}}_title_button">{{lang('title_button_view_all')}}</label>
                                            <input type="text" name="section7[{{$value['code']}}][title_button]"
                                                   id="section7_{{$value['code']}}_title_button"
                                                   class="form-control" value="{{$homePage[$value['code']]->section7->title_button ?? ''}}"/>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </section>
                        <div class="clearfix"></div>
                        <hr/>
                        <section>
                            <div class="col-md-12 title_section">Section 8 ({{lang('c_title_step_home_8')}}...)</div>
                            @if(!empty($lang_current))
                                @foreach($lang_current as $key => $value)
                                    <div class="col-md-12 div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="section8_{{$value['code']}}_title">{{lang('dt_title')}}</label>
                                            <textarea type="text" name="section8[{{$value['code']}}][title]"
                                                      id="section8_{{$value['code']}}_title"
                                                      class="form-control editor_short">{{$homePage[$value['code']]->section8->title ?? ''}}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="section8_{{$value['code']}}_subtitle">{{lang('quantity_qg')}}</label>
                                            <input type="number" name="section8[{{$value['code']}}][subtitle]"
                                                   id="section8_{{$value['code']}}_subtitle"
                                                   class="form-control" value="{{$homePage[$value['code']]->section8->subtitle ?? ''}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="section8_{{$value['code']}}_title_button">{{lang('title_button')}}</label>
                                            <input type="text" name="section8[{{$value['code']}}][title_button]"
                                                   id="section8_{{$value['code']}}_title_button"
                                                   class="form-control" value="{{$homePage[$value['code']]->section8->title_button ?? ''}}"/>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </section>
                        <section>
                            <div class="col-md-12 title_section">Section 9 ({{lang('c_title_step_home_9')}}...)</div>
                            @if(!empty($lang_current))
                                @foreach($lang_current as $key => $value)
                                    <div class="col-md-12 div-lang div-{{$value['code']}}">
                                        <div class="form-group">
                                            <label for="section9_{{$value['code']}}_title">{{lang('dt_title')}}</label>
                                            <textarea type="text" name="section9[{{$value['code']}}][title]"
                                                      id="section9_{{$value['code']}}_title"
                                                      class="form-control editor_short">{{$homePage[$value['code']]->section9->title ?? ''}}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="section9_{{$value['code']}}_subtitle">{{lang('c_subtext_title')}}</label>
                                            <input type="text" name="section9[{{$value['code']}}][subtitle]"
                                                   id="section9_{{$value['code']}}_subtitle"
                                                   class="form-control" value="{{$homePage[$value['code']]->section9->subtitle ?? ''}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="section9_{{$value['code']}}_title_button">{{lang('title_button')}}</label>
                                            <input type="text" name="section9[{{$value['code']}}][title_button]"
                                                   id="section9_{{$value['code']}}_title_button"
                                                   class="form-control" value="{{$homePage[$value['code']]->section9->title_button ?? ''}}"/>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </section>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="form-group text-right m-b-0 m-t-10">
                    <button class="btn btn-primary waves-effect waves-light" type="submit">
                        {{lang('dt_save')}}
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
        ShowTab('{{$lang_default ?? 'vi'}}');
        function ShowTab(lang) {
            $('.div-lang').removeClass('active');
            $(`.div-${lang}`).addClass('active');
        }

        $("#HomepageForm").validate({
            ignore: "",
            rules: {
            },
            messages: {

            },
            invalidHandler: function(event, validator) {
                let errors = validator.numberOfInvalids();
                if (errors) {
                    let message = "";
                    validator.errorList.forEach(function(error) {
                        let fieldName = $(error.element).attr("id");
                        var fieldNameLang = '';
                        if (fieldName.endsWith("_en")) {
                            fieldNameLang = 'tiếng anh';
                        }

                        let label = $($("label[for='" + fieldName + "']")[0]).text();
                        if (!label) {
                            fieldName = $(error.element).attr("name");
                            label = $($("label[for='" + fieldName + "']")[0]).text();
                        }

                        message += `<div>${label} ${fieldNameLang} ${error.message}</div>`;
                    });

                    if (!message) {
                        message = 'Bạn chưa nhập các trường';
                    }
                    alert_float('error', message, 5000);
                }
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
                        alert_float('error',htmlError);
                    });
                return false;
            }
        });
        setTimeout(function (){
            $(".content").closest('div').find('.tox-tinymce').css({
                height:"450px"
            })
        },300);


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
        $(".section_2_image").change(function (event){
            id = $(this).attr('data-id');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();

                // Khi đọc tệp xong, hiển thị hình ảnh
                reader.onload = function(e) {
                    $(`.section_2_image_preview_${id}`).attr('src', e.target.result).show();
                };

                reader.readAsDataURL(file);
            }
        });

        function clickImage3(_this){
            id = $(_this).attr('data-id');
            $(`#section_3_image_${id}`).click();
        }
        $(".section_3_image").change(function (event){
            id = $(this).attr('data-id');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();

                // Khi đọc tệp xong, hiển thị hình ảnh
                reader.onload = function(e) {
                    $(`.section_3_image_preview_${id}`).attr('src', e.target.result).show();
                };

                reader.readAsDataURL(file);
            }
        });


    </script>
@endsection
