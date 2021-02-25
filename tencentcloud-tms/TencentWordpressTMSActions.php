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

namespace TencentWordpressTMS;

use Exception;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Cms\V20190321\CmsClient;
use TencentCloud\Cms\V20190321\Models\TextModerationRequest;
use TencentCloud\Cms\V20190321\Models\TextModerationResponse;
use WP_Comment;
use WP_Error;
use TencentWordpressPluginsSettingActions;

class TencentWordpressTMSActions
{
    const PLUGIN_TYPE ='tms';
    /**
     * 插件初始化
     */
    public static function initPlugin()
    {
        static::addToPluginCenter();
        self::requirePluginCenterClass();
        TencentWordpressPluginsSettingActions::setWordPressSiteID();
        $staticData = self::getTencentCloudWordPressStaticData('activate');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($staticData);
    }

    /**
     * 禁用插件
     */
    public static function disablePlugin()
    {
        self::requirePluginCenterClass();
        TencentWordpressPluginsSettingActions::disableTencentWordpressPlugin(TENCENT_WORDPRESS_TMS_SHOW_NAME);
        $staticData = self::getTencentCloudWordPressStaticData('deactivate');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($staticData);
    }

    /**
     * 卸载插件
     */
    public static function uninstallPlugin()
    {
        self::requirePluginCenterClass();
        delete_option( TENCENT_WORDPRESS_TMS_OPTIONS);
        TencentWordpressPluginsSettingActions::deleteTencentWordpressPlugin(TENCENT_WORDPRESS_TMS_SHOW_NAME);
        $staticData = self::getTencentCloudWordPressStaticData('uninstall');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($staticData);
    }

    /**
     * 引入插件中心类
     */
    public static function requirePluginCenterClass()
    {
        require_once TENCENT_WORDPRESS_PLUGINS_COMMON_DIR . 'TencentWordpressPluginsSettingActions.php';
    }

    /**
     * 加入插件中心
     */
    public static function addToPluginCenter()
    {
        self::requirePluginCenterClass();
        $plugin = array(
            'plugin_name' => TENCENT_WORDPRESS_TMS_SHOW_NAME,
            'nick_name' => '腾讯云文本内容安全（TMS）插件',
            'plugin_dir' => TENCENT_WORDPRESS_TMS_BASENAME,
            'href' => "admin.php?page=TencentWordpressTMSSettingPage",
            'activation' => TencentWordpressPluginsSettingActions::ACTIVATION_INSTALL,
            'status' => TencentWordpressPluginsSettingActions::STATUS_OPEN,
            'download_url' => ''
        );
        TencentWordpressPluginsSettingActions::prepareTencentWordressPluginsDB($plugin);
    }


    public static function getTencentCloudWordPressStaticData($action)
    {
        self::requirePluginCenterClass();
        $staticData['action'] = $action;
        $staticData['plugin_type'] = self::PLUGIN_TYPE;
        $staticData['data']['site_id'] = TencentWordpressPluginsSettingActions::getWordPressSiteID();
        $staticData['data']['site_url'] = TencentWordpressPluginsSettingActions::getWordPressSiteUrl();
        $staticData['data']['site_app'] = TencentWordpressPluginsSettingActions::getWordPressSiteApp();
        $TMSOptions = self::getTMSOptionsObject();
        $staticData['data']['uin'] = TencentWordpressPluginsSettingActions::getUserUinBySecret($TMSOptions->getSecretID(), $TMSOptions->getSecretKey());
        $staticData['data']['cust_sec_on'] = $TMSOptions->getCustomKey() === $TMSOptions::CUSTOM_KEY ?1:2;
        return $staticData;
    }



    /**
     * 初始化插件中心设置页面
     */
    public function initCommonSettingPage()
    {
        self::requirePluginCenterClass();
        if ( class_exists('TencentWordpressPluginsSettingActions') ) {
            TencentWordpressPluginsSettingActions::init();
        }
    }


    /**
     * 评论入库前检测
     * @param $content
     * @return mixed
     * @throws Exception
     */
    public function examineCommentBeforeInsertDatabase($content)
    {
        $TMSOptions = self::getTMSOptionsObject();
        $response = $this->textModeration($TMSOptions, $content);
        //检测接口异常不影响用户提交评论
        if ( !($response instanceof TextModerationResponse) ) {
            return $content;
        }
        if ( $response->getData()->EvilFlag === 0 || $response->getData()->EvilType === 100 ) {
            return $content;
        }

        $error = new WP_Error(
            'comment_examined_fail',
            __('评论检测不通过，包含关键字<strong style="color: red;">' . $response->getData()->Keywords[0] . '</strong>请修改后重新提交')
        );
        wp_die($error, '评论检测不通过,请修改后重新提交.', ['back_link' => true]);
    }

    /**
     * 评论入库后检测
     * @param $id
     * @param $comment
     * @return bool
     * @throws Exception
     */
    public function examineCommentAfterInsertDatabase($id, $comment)
    {
        $TMSOptions = self::getTMSOptionsObject();
        if ( !($comment instanceof WP_Comment) ) {
            return false;
        }
        $response = $this->textModeration($TMSOptions, $comment->comment_content);
        //检测接口异常不影响用户提交评论
        if ( !($response instanceof TextModerationResponse) ) {
            return false;
        }

        if ( $response->getData()->EvilFlag === 0 || $response->getData()->EvilType === 100 ) {
            //是否需要人工审核
            if ( $TMSOptions->getAllowOption() === TencentWordpressTMSOptions::ALLOW_TO_PASS ) {
                wp_set_comment_status($id, 'approve');
            }
            return true;
        }

        if ( $TMSOptions->getFailOption() === TencentWordpressTMSOptions::FAIL_TO_TRASH ) {
            //标记为垃圾评论
            wp_spam_comment($id);
        } else {
            //移动到回收站
            wp_trash_comment($id);
        }
        $error = new WP_Error(
            'comment_examined_fail',
            __('评论检测不通过，包含关键字<strong style="color: red;">' . $response->getData()->Keywords[0] . '</strong>已屏蔽')
        );
        wp_die($error, '评论检测不通过,已屏蔽.', ['back_link' => true]);
    }

    /**
     *
     * @param $post_id
     * @return string
     */
    public function examineCommentWhenSaveArticle($post_id)
    {
        if (!$post_id)
        {
            $error = new WP_Error(
                'comment_examined_fail',
                __('文章不存在')
            );
            wp_die($error, '文章不存在。', ['back_link' => true]);
            exit;
        }
        $post = get_post( $post_id );
        if (!is_object($post)) {
            return true;
        }
        $content = '';
        if (isset($post->post_title)) {
            $content .= $post->post_title;
        }

        if (isset($post->post_content)) {
            $content .= $post->post_content;
        }
        $TMSOptions = self::getTMSOptionsObject();
        $response = $this->textModeration($TMSOptions, $content);
        //检测接口异常不影响用户提交评论
        if ( !($response instanceof TextModerationResponse) ) {
            return true;
        }
        if ( $response->getData()->EvilFlag === 0 || $response->getData()->EvilType === 100 ) {
            return true;
        }

        $error = new WP_Error(
            'comment_examined_fail',
            __('内容检测不通过，包含关键字<strong style="color: #ff0000;">' . $response->getData()->Keywords[0] . '</strong>请修改后重新提交')
        );
        wp_die($error, '内容检测不通过,请修改后重新提交.', ['back_link' => true]);
    }

    /**
     * 腾讯云文本检测
     * @param TencentWordpressTMSOptions $TMSOptions
     * @param $text
     * @return Exception|TextModerationResponse|TencentCloudSDKException
     * @throws Exception
     */
    private function textModeration($TMSOptions, $text)
    {
        try {
            $cred = new Credential($TMSOptions->getSecretID(), $TMSOptions->getSecretKey());
            $clientProfile = new ClientProfile();
            $client = new CmsClient($cred, "ap-shanghai", $clientProfile);
            $req = new TextModerationRequest();
            $params['Content'] = base64_encode($text);
            $req->fromJsonString(\GuzzleHttp\json_encode($params, JSON_UNESCAPED_UNICODE));
            return $client->TextModeration($req);
        } catch (TencentCloudSDKException $e) {
            return $e;
        }
    }

    /**
     * 评论表单
     */
    public function commentFormTips()
    {
        if ( $GLOBALS['pagenow'] === 'edit-comments.php' ) {
            echo '<p style="border-left: 4px solid #09e246;
    padding: 12px;
    margin-left: 0;
    margin-bottom: 20px;
    background-color: #fff;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">评论区内容检测，腾讯云文本安全/内容安全生效中<br></p>';
        }
    }


    /**
     * 加载js脚本
     */
    public function loadMyScriptEnqueue()
    {
        wp_register_script('TMS_front_user_script', TENCENT_WORDPRESS_TMS_JS_DIR . 'front_user_script.js', array('jquery'), '2.1', true);
        wp_enqueue_script('TMS_front_user_script');

        wp_register_script('TMS_back_admin_script', TENCENT_WORDPRESS_TMS_JS_DIR . 'back_admin_script.js', array('jquery'), '2.1', true);
        wp_enqueue_script('TMS_back_admin_script');

    }


    public function pluginSettingPage()
    {
        require_once 'TencentWordpressTMSSettingPage.php';
        TencentWordpressPluginsSettingActions::addTencentWordpressCommonSettingPage();
        add_submenu_page('TencentWordpressPluginsCommonSettingPage', '文本内容安全', '文本内容安全', 'manage_options', 'TencentWordpressTMSSettingPage', 'TencentWordpressTMSSettingPage');

    }

    /**
     * 加载css
     * @param $hookSuffix
     */
    public function loadCSSEnqueue($hookSuffix)
    {
        //只在后台配置页引入
        if (strpos($hookSuffix,'page_TencentWordpressTMSSettingPage') !== false){
            wp_register_style('TMS_back_admin_css', TENCENT_WORDPRESS_TMS_CSS_DIR . 'bootstrap.min.css');
            wp_enqueue_style('TMS_back_admin_css');
        }
    }


    /**
     * 参数过滤
     * @param $key
     * @param string $default
     * @return string|void
     */
    public function filterPostParam($key, $default = '')
    {
        return isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : $default;
    }

    /**
     * 获取配置对象
     * @return TencentWordpressTMSOptions
     */
    public static function getTMSOptionsObject()
    {
        $TMSOptions = get_option(TENCENT_WORDPRESS_TMS_OPTIONS);
        if ( $TMSOptions instanceof TencentWordpressTMSOptions ) {
            return $TMSOptions;
        }
        return new TencentWordpressTMSOptions();
    }


    /**
     * 保存插件配置
     */
    public function updateTMSOptions()
    {
        try {
            if ( !current_user_can('manage_options') ) {
                wp_send_json_error(array('msg' => '当前用户无权限'));
            }
            $TMSOptions = new TencentWordpressTMSOptions();
            $TMSOptions->setCustomKey($this->filterPostParam('customKey'));
            $TMSOptions->setSecretID($this->filterPostParam('secretID'));
            $TMSOptions->setSecretKey($this->filterPostParam('secretKey'));
            $TMSOptions->setAllowOption($this->filterPostParam('allowOption', TencentWordpressTMSOptions::ALLOW_TO_PASS));
            $TMSOptions->setFailOption($this->filterPostParam('failOption', TencentWordpressTMSOptions::FAIL_TO_FORBID_SUBMISSION));
            update_option(TENCENT_WORDPRESS_TMS_OPTIONS, $TMSOptions, true);
            //更新系统讨论设置（在评论显示之前）
            $this->updateOptionsDiscussion($TMSOptions);
            self::requirePluginCenterClass();
            $staticData = self::getTencentCloudWordPressStaticData('save_config');
            TencentWordpressPluginsSettingActions::sendUserExperienceInfo($staticData);
            wp_send_json_success(array('msg' => '保存成功'));
        } catch (Exception $exception) {
            wp_send_json_error(array('msg' => $exception->getMessage()));
        }
    }

    /**
     * 更新系统讨论设置
     * @param TencentWordpressTMSOptions $TMSOptions
     */
    private function updateOptionsDiscussion($TMSOptions)
    {
        $commentWhitelist = 0;
        $commentModeration = 0;
        if ( $TMSOptions->getAllowOption() === $TMSOptions::ALLOW_TO_REVIEW ) {
            $commentWhitelist = 1;
            $commentModeration = 1;
        }
        update_option('comment_whitelist', $commentWhitelist, true);
        update_option('comment_moderation', $commentModeration, true);
    }

    /**
     * 添加设置按钮
     * @param $links
     * @param $file
     * @return mixed
     */
    public function pluginSettingPageLinkButton($links, $file)
    {
        if ( $file === TENCENT_WORDPRESS_TMS_BASENAME ) {
            $links[] = '<a href="admin.php?page=TencentWordpressTMSSettingPage">设置</a>';
        }
        return $links;
    }

}




