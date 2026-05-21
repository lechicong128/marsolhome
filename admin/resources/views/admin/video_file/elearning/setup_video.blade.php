@extends('admin.layouts.index')
@section('content')
    <style>

        .header-course {
            background: #fff;
            padding: 20px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        /* Tùy biến DataTable để trông giống danh sách Card */
        #lessonTable { border: none; border-collapse: separate; border-spacing: 0 10px; background: transparent; width: 100% !important; }
        #lessonTable thead { display: none; } /* Ẩn tiêu đề bảng */
        #lessonTable tr { background: transparent !important; }

        /* Biến <td> thành container của card */
        .lesson-card-td {
            padding: 15px !important;
            border: 1px solid #e1e1e1 !important;
            border-radius: 8px !important;
            display: flex;
            align-items: center;
            background: #fff;
            /*margin-bottom: 10px;*/
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        .lesson-card-td:hover { border-color: #3498db !important; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

        .lesson-order {
            width: 40px;
            height: 40px;
            background: #34495e;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .lesson-info { flex-grow: 1; }
        .lesson-info h4 { margin: 0 0 5px 0; font-size: 16px; font-weight: bold; color: #2c3e50; }
        .lesson-stats { margin-top: 5px; color: #7f8c8d; font-size: 12px; }
        .lesson-stats span { margin-right: 10px; }

        .lesson-actions { display: flex; gap: 5px; flex-shrink: 0; }

        .btn-add-lesson {
            background: #27ae60;
            color: white;
            font-weight: bold;
            border-radius: 20px;
            padding: 8px 20px;
        }

        /* Video Preview Area */
        .video-preview-mini {
            width: 100%;
            height: 120px;
            background: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-top: 10px;
        }
        .video-preview-mini video { width: 100%; height: 100%; object-fit: cover; display: none; }

        #progress-wrapper { display: none; margin-top: 15px; }

        .dataTables_filter input {
            border-radius: 20px;
            padding: 5px 15px;
            border: 1px solid #ddd;
            outline: none;
            width: 250px !important;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="col-sm-8">
                <h4 class="page-title">{{lang('setup_detail_video_elearning')}}</h4>
                <ol class="breadcrumb">
                    <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                    <li><a href="admin/video/elearning">{{lang('elearning')}}</a></li>
                    <li class="active">{{$title ?? ''}}</li>
                </ol>
            </div>
            <div class="col-sm-4 text-right">
                <a class="btn btn-default dt-modal" href="admin/video/detail_video_elearning/{{$id}}">
                    <i class="fa fa-plus"></i> {{lang('c_add_video_elearning')}}
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="card-box">
                    <div class="col-md-12">
                        <div class="checkbox checkbox-custom checkbox-inline mbot10">
                            <input type="checkbox" id="active_order_by" value="1">
                            <label for="active_order_by">{{lang('active_order_by')}}</label>
                        </div>
                        <hr/>
                        <h4 style="margin-bottom: 20px;">Danh sách video</h4>
                        <table id="lessonTable" class="table sortableMain">
                            <thead>
                            <tr>
                                <th>Nội dung</th>
                            </tr>
                            </thead>
                            <tbody class="">
                            <!-- Dữ liệu sẽ được Load từ Ajax -->
                            </tbody>
                        </table>
                        <div class="show_error"></div>
                    </div>

                    <div class="col-md-4 hide">
                        <div class="panel panel-default">
                            <div class="panel-heading">Thống kê Elearning</div>
                            <div class="panel-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="border: none;">
                                        Tổng thời lượng: <strong>{{$elearning->hms}}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script>
        var fnserverparams = {
            'status_search': '#status_search'
        };
        $('.H-search').click(function() {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })

        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        var oTable;
        oTable = InitDataTable('#lessonTable', 'admin/video/get_video_elearning/{{$elearning->id}}', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/video/get_video_elearning/{{$elearning->id}}",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            "columnDefs": [
                {
                    "targets": 0,
                    "render": function (data, type, row) {
                        // CUSTOM RENDER: Đưa mọi thông tin vào cấu trúc Card đẹp
                        return `
                            <div class="lesson-card-td">
                                <div class="lesson-order row_stt">${row.DT_RowIndex}</div>
                                <input type="hidden" class="inputStt" data-id="${row.id}" value="${row.order_premium}">
                                <div class="lesson-info">
                                    <h4>${row.name}</h4>
                                    <p class="text-muted small" style="margin: 0;">
                                        <i class="fa fa-clock-o"></i>
                                            ${row.duration}
                                    </p>
                                    <div class="small text-muted">
                                        <i class="fa fa-link"></i> <a href="${row.original_video || 'N/A'}">Link</a>
                                    </div>
                                    <div class="lesson-stats">
                                        <span><i class="fa fa-heart"></i> ${row.count_like}</span>
                                        <span><i class="fa fa-eye"></i> ${row.count_see}</span>
                                        <span><i class="fa fa-comment"></i> ${row.count_comment}</span>
                                    </div>
                                </div>
                                <div class="lesson-actions">
                                    ${row.options}
                                </div>
                            </div>
                        `;
                    }
                }
            ],
        });

        $('#active_order_by').change(function() {
            if($(this).prop('checked')) {
                $('.sortableMain tbody').sortable("enable");
            }
            else {
                $('.sortableMain tbody').sortable("disable");
            }
        })
        $('.sortableMain tbody').sortable({
            items: 'tr:not(.not-sortable)',
            distance: 20,
            start: function() {},
            stop: function() {
                EventUpdateSorMain(this);
            }
        });

        $('#active_order_by').trigger('change');



        function EventUpdateSorMain(_this) {
            if (confirm('Bạn có chắc muốn sắp xếp danh mục?')) {
                var inputStt = $('.inputStt');
                var limit = $('[name="lessonTable_length"]').val();
                var page = $('#lessonTable_paginate').find('.paginate_button.active').text();
                var nextStt = (limit * page) - limit + 1;
                $.each(inputStt, function(index, value) {
                    $(value).val((nextStt + index));
                    $(value).parent('td').find('.row_stt').text((nextStt + index));
                })
                var data = {};
                if (typeof(csrfData) !== 'undefined') {
                    data[csrfData['token_name']] = csrfData['hash'];
                }
                var list_order_by = {};
                $.each(inputStt, function(index, value) {
                    list_order_by[$(value).attr('data-id')] = $(value).val();
                })
                data['list_order_by'] = list_order_by;

                $.ajax({
                    type: "POST",
                    url: 'admin/video/order_by_video',
                    data: data,
                    dataType: "json",
                    success: function(response) {
                        if (response.result) {
                            alert_float('success', response?.message);
                        } else {
                            alert_float('error', response?.message);
                        }
                        oTable.draw(false);
                    }
                });
            } else {
                oTable.draw(false);
            }
        }
    </script>
@endsection
