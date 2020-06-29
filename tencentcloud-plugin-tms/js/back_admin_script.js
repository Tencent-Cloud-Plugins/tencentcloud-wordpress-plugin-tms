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

});