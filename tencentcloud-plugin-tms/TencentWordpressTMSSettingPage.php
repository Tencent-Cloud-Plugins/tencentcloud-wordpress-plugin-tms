<?php
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
use TencentWordpressTMS\TencentWordpressTMSActions;

function TencentWordpressTMSSettingPage()
{
    $ajaxUrl = admin_url('admin-ajax.php');
    $TMSOptions = TencentWordpressTMSActions::getTMSOptionsObject();
    $secretID = $TMSOptions->getSecretID();
    $secretKey = $TMSOptions->getSecretKey();
    $allowOption = $TMSOptions->getAllowOption();
    $failOption = $TMSOptions->getFailOption();
    $customKey = $TMSOptions->getCustomKey();
    $optionsUrl = admin_url('options-discussion.php');
?>
    <link rel="stylesheet" href="<?php echo TENCENT_WORDPRESS_TMS_CSS_DIR . 'bootstrap.min.css' ?>">
    <style type="text/css">
        .dashicons {
            vertical-align: middle;
            position: relative;
            right: 30px;
        }
    </style>
    <?php if ( !empty($secretID) && !empty($secretKey) ) { ?>
    <div id="message" class="updated notice is-dismissible" style="margin-bottom: 1%;margin-left:0;"><p>
            腾讯云内容安全（TMS）插件启用生效中。</p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此通知。</span></button>
    </div>
<?php } ?>
    <div class="bs-docs-section">
        <div class="row">
            <div class="col-lg-12">
                <div class="page-header ">
                    <h1 id="forms">腾讯云内容安全（TMS）插件</h1>
                </div>
                <p>对用户在评论提交的文字出现违规涉黄、爆、恐的内容，进行内容检测和过滤</p>
            </div>
        </div>
        <div class="alert alert-dismissible alert-success" style="display: none;">
            <button type="button" id="close-tms-ajax-return-msg" class="close" data-dismiss="alert">&times;</button>
            <div id="show-tms-ajax-return-msg">操作成功.</div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="javascript:void(0);" id="sub-tab-settings">插件配置</a>
            </li>
        </ul>
        <div class="postbox">
            <div class="inside">
                <div class="row">
                    <div class="col-lg-9">
                        <form method="post" id="tms-options-form" action="" data-ajax-url="<?php echo $ajaxUrl ?>">

                            <div class="form-group">
                                <label class="col-form-label col-lg-2 lable_padding_left" for="tms-option-custom-key">自定义密钥</label>
                                <div class="custom-control custom-switch div_custom_switch_padding_top"
                                     style="margin-top: -2.3rem;margin-left: 13rem;">
                                    <input type="checkbox" class="custom-control-input"
                                           id="tms-option-custom-key" <?php if ( $customKey === $TMSOptions::CUSTOM_KEY ) {
                                        echo 'checked';
                                    } ?> >
                                    <label class="custom-control-label"
                                           for="tms-option-custom-key">为该插件配置单独定义的腾讯云密钥</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label col-lg-2" for="txc-tms-secret-id">SecretId</label>
                                <input id="txc-tms-secret-id" type="password" class="col-lg-5 is-invalid"
                                       placeholder="SecretId" <?php if ( $customKey !== $TMSOptions::CUSTOM_KEY ) {
                                    echo 'disabled="disabled"';
                                } ?>
                                       value="<?php echo $secretID; ?>">
                                <span id="tms-secret-id-change-type" class="dashicons dashicons-hidden"></span>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label col-lg-2" for="txc-tms-secret-key">SecretKey</label>
                                <input id="txc-tms-secret-key" type="password" class="col-lg-5 is-invalid"
                                       placeholder="SecretKey" <?php if ( $customKey !== $TMSOptions::CUSTOM_KEY ) {
                                    echo 'disabled="disabled"';
                                } ?>
                                       value="<?php echo $secretKey ?>">
                                <span id="tms-secret-key-change-type" class="dashicons dashicons-hidden"></span>
                                <div class="offset-lg-2">
                                    <p class="description">访问 <a href="https://console.qcloud.com/cam/capi"
                                                                 target="_blank">密钥管理</a>获取
                                        SecretId和SecretKey或通过"新建密钥"创建密钥串</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label col-lg-2 lable_padding_left"
                                       for="tms-allow-option">系统审核通过</label>
                                <div class="custom-control custom-switch div_custom_switch_padding_top"
                                     style="margin-top: -2.3rem;margin-left: 13rem;">
                                    <input type="checkbox" id="tms-allow-option"
                                           class="custom-control-input" <?php if ( $allowOption === $TMSOptions::ALLOW_TO_REVIEW ) {
                                        echo 'checked';
                                    } ?> >
                                    <label class="custom-control-label" for="tms-allow-option">人工复审</label>
                                </div>
                                <p class="description" style="margin-left: 13rem;">默认不需要人工复审。如开启将会把<a
                                            href="<?php echo $optionsUrl; ?>">讨论设置</a>中《在评论显示之前》的两个设置项一并开启</p>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label col-lg-2" for="tms-fail-option-forbid">系统审核不通过</label>
                                <input type="radio" id="tms-fail-option-forbid" value="forbid_submission"
                                       name="tms-fail-option"
                                    <?php if ( $failOption === $TMSOptions::FAIL_TO_FORBID_SUBMISSION ) {
                                        echo 'checked';
                                    } ?>
                                >
                                <label for="tms-fail-option-forbid">不允许提交</label>
                                <input type="radio" id="tms-fail-option-rejection" value="recycle_bin"
                                       name="tms-fail-option"
                                    <?php if ( $failOption === $TMSOptions::FAIL_TO_RECYCLE_BIN ) {
                                        echo 'checked';
                                    } ?>
                                >
                                <label for="tms-fail-option-rejection">移动回收站</label>
                                <input type="radio" id="tms-fail-option-trash" value="trash" name="tms-fail-option"
                                    <?php if ( $failOption === $TMSOptions::FAIL_TO_TRASH ) {
                                        echo 'checked';
                                    } ?>
                                >
                                <label for="tms-fail-option-trash">标记为垃圾评论</label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <button id="tms-options-update-button" type="button" class="btn btn-primary">保存配置</button>
        <div style="text-align: center;flex: 0 0 auto;margin-top: 3rem;">
            <a href="https://openapp.qq.com/Wordpress/tms.html" target="_blank">文档中心</a> | <a href="https://github.com/Tencent-Cloud-Plugins/tencentcloud-wordpress-plugin-tms" target="_blank">GitHub</a> | <a
                    href="https://support.qq.com/product/164613" target="_blank">意见反馈</a>
        </div>
    </div>
<?php
}