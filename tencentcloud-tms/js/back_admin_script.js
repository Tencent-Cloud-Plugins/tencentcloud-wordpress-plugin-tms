/*
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
jQuery(function ($) {
    var ajaxUrl = $('#tms-options-form').data('ajax-url');
    var pageSize = 10;
    //展示接口返回
    function showAjaxReturnMsg(msg,success) {
        var parent = $('#show-tms-ajax-return-msg').parent();
        if (!success) {
            parent.removeClass('alert-success');
            parent.hasClass('alert-danger') || parent.addClass('alert-danger');
        } else {
            parent.removeClass('alert-danger');
            parent.hasClass('alert-success') || parent.addClass('alert-success');
        }
        $('#show-tms-ajax-return-msg').text(msg);
        parent.show();
    }
    //关闭提示条
    $('#close-tms-ajax-return-msg').click(function () {
        $(this).parent().hide();
    });

    $("#tms-option-custom-key").change(function() {
        var disabled = !($(this).is(':checked'));
        $("#txc-tms-secret-id").attr('disabled',disabled);
        $("#txc-tms-secret-key").attr('disabled',disabled);

    });

    function changeInputType(inputElement, spanEye) {
        if(inputElement.attr('type') === 'password') {
            inputElement.attr('type','text');
            spanEye.addClass('dashicons-visibility').removeClass('shicons-hidden');
        } else {
            inputElement.attr('type','password');
            spanEye.addClass('shicons-hiddenda').removeClass('dashicons-visibility');
        }
    }

    $('#tms-secret-id-change-type').click(function () {
        changeInputType($('#txc-tms-secret-id'), $(this));
    });

    $('#tms-secret-key-change-type').click(function () {
        changeInputType($('#txc-tms-secret-key'), $(this));
    });

    $('#sub-tab-settings').click(function () {
        $('#sub-tab-settings').removeClass('active').addClass('active');
        $('#sub-tab-whitelist').removeClass('active');
        $('#sub-tab-records').removeClass('active');

        $('#body-sub-tab-settings').addClass('active show');
        $('#body-sub-tab-whitelist').removeClass('active show');
        $('#body-sub-tab-records').removeClass('active show');

    });

    $('#sub-tab-whitelist').click(function () {
        $('#sub-tab-settings').removeClass('active');
        $('#sub-tab-whitelist').removeClass('active').addClass('active');
        $('#sub-tab-records').removeClass('active');

        $('#body-sub-tab-settings').removeClass('active show');
        $('#body-sub-tab-whitelist').addClass('active show');
        $('#body-sub-tab-records').removeClass('active show');
    });

    $('#sub-tab-records').click(function () {
        $('#sub-tab-settings').removeClass('active');
        $('#sub-tab-whitelist').removeClass('active');
        $('#sub-tab-records').removeClass('active').addClass('active');

        $('#body-sub-tab-settings').removeClass('active show');
        $('#body-sub-tab-whitelist').removeClass('active show');
        $('#body-sub-tab-records').addClass('active show');

        getTmsData(1,pageSize)
        $("#tms_current_page")[0].innerHTML = '1';
        $('#record_previous_page').removeClass('disabled').addClass('disabled');
        $('#record_previous_page').removeAttr('disabled');
        $('#record_next_page').removeAttr('disabled');
        $('#record_next_page').removeClass('disabled');
    });
    //ajax保存配置
    $('#tms-options-update-button').click(function () {
        var secretID = $("#txc-tms-secret-id").val()
        var secretKey = $("#txc-tms-secret-key").val()
        var allowOption = $("#tms-allow-option").is(":checked")?1:0;
        var customKey = $("#tms-option-custom-key").is(":checked")?1:0
        var failOption = $("input[name='tms-fail-option']:checked").val();

        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "update_TMS_options",
                secretID: secretID,
                secretKey: secretKey,
                allowOption: allowOption,
                failOption: failOption,
                customKey: customKey,
            },
            success: function(response) {
                showAjaxReturnMsg(response.data.msg,response.success)
                if (response.success){
                    setTimeout(function(){
                        window.location.reload();//刷新当前页面.
                    },2000)
                }
            }
        });
    });

    $('#tms-options-whitelist-button').click(function () {
        var whitelist = $("#txc-tms-whitelist").val()
        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "update_whitelist",
                whitelist: whitelist
            },
            success: function(response) {
                showAjaxReturnMsg(response.data.msg,response.success)
                if (response.success){
                    setTimeout(function(){
                        window.location.reload();//刷新当前页面.
                    },2000)
                }
            }
        });
    });

    //命中记录
    $('#search_tms_keyword_button').click(function () {
        $("#more_list  tr:not(:first)").remove();
        getTmsData(1,pageSize)
        $("#tms_current_page")[0].innerHTML = '1';
        $('#record_previous_page').removeClass('disabled').addClass('disabled');
        $('#record_previous_page').removeAttr('disabled');
        $('#record_next_page').removeAttr('disabled');
        $('#record_next_page').removeClass('disabled');
    });

    //获取上一页
    $('#record_previous_page').click(function () {
         if ($(this).attr('disabled') === 'disabled') {
            return;
         }
        var currentPage = $(this).attr('data-current-page');
        if (currentPage === '1' || currentPage < '1') {
            return;
        }
        $("#more_list  tr:not(:first)").remove();
        currentPage--
        getTmsData(currentPage,pageSize);
        $("#tms_current_page")[0].innerHTML = currentPage;
        $(this).attr('data-current-page',currentPage);
        $('#record_next_page').removeAttr('disabled');
        $('#record_next_page').removeClass('disabled');
    });

    //获取下一页
    $('#record_next_page').click(function () {
        if ($(this).attr('disabled') === 'disabled') {
            return;
        }
        var currentPage = $('#record_previous_page').attr('data-current-page');
        currentPage++
        $("#more_list  tr:not(:first)").remove();
        getTmsData(currentPage,pageSize);
        $("#tms_current_page")[0].innerHTML = currentPage;
        $('#record_previous_page').removeAttr('disabled');
        $('#record_previous_page').attr('data-current-page',currentPage).removeClass('disabled');
    });

    //ajax获取短信记录
    function getTmsData(page,pageSize){
        var searchKeyword = $("#search_keyword_list").val()
        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "get_tms_keyword_list",
                keyword: searchKeyword,
                page:page,
                page_size:pageSize
            },
            success: function(response) {
                var list = response.data.list;
                var html = '';
                var status = '';
                if (!response.success) {
                    alert(response.data.msg);
                    return;
                }
                //填充短信记录表格
                $.each(list, function(i, item) {
                    html += '<tr>';
                    html += '<td>' +item['user_login']+'('+ item['user_nicename'] +')'+'</td>';
                    html += '<td>' +item['user_email']+'</td>';
                    html += '<td>' +item['user_role']+'</td>';
                    html += '<td>' +item['type']+'</td>';
                    html += '<td>' +item['content']+'</td>';
                    html += '<td>' +item['post_title']+'</td>';
                    html += '<td>' +item['evil_label']+'</td>';
                    html += '<td>' +item['create_time']+'</td>';
                    html += '</tr>';
                });
                $('#more_list').append(html);

                if (page <= 1) {
                    $("#record_previous_page").attr('disabled','disabled').addClass('disabled');
                }
                if (!response.data.hasNext){
                    $("#record_next_page").attr('disabled','disabled').addClass('disabled');
                }
            }
        });
    }
});