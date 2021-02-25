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
/**
 * Plugin Name: tencentcloud-tms
 * Plugin URI: https://openapp.qq.com/docs/Wordpress/tms.html
 * Author URI: https://cloud.tencent.com/product/tms
 * Description: 通过腾讯云文本内容安全服务对评论提交的文字进行内容检测和过滤。
 * Version: 1.0.1
 * Author: 腾讯云
 *
 */
defined('TENCENT_WORDPRESS_TMS_VERSION')||define( 'TENCENT_WORDPRESS_TMS_VERSION', '1.0.1');
defined('TENCENT_WORDPRESS_TMS_OPTIONS')||define( 'TENCENT_WORDPRESS_TMS_OPTIONS', 'tencent_wordpress_tms_options' );
defined('TENCENT_WORDPRESS_TMS_DIR')||define( 'TENCENT_WORDPRESS_TMS_DIR', plugin_dir_path( __FILE__ ) );
defined('TENCENT_WORDPRESS_TMS_BASENAME')||define( 'TENCENT_WORDPRESS_TMS_BASENAME', plugin_basename(__FILE__));
defined('TENCENT_WORDPRESS_TMS_URL')||define( 'TENCENT_WORDPRESS_TMS_URL', plugins_url( 'tencentcloud-tms' ) );
defined('TENCENT_WORDPRESS_TMS_JS_DIR')||define( 'TENCENT_WORDPRESS_TMS_JS_DIR', TENCENT_WORDPRESS_TMS_URL .'/js/' );
defined('TENCENT_WORDPRESS_TMS_CSS_DIR')||define( 'TENCENT_WORDPRESS_TMS_CSS_DIR', TENCENT_WORDPRESS_TMS_URL .'/css/' );
//插件中心常量
defined('TENCENT_WORDPRESS_TMS_NAME')||define( 'TENCENT_WORDPRESS_TMS_NAME', 'tencentcloud-plugin-tms');
defined('TENCENT_WORDPRESS_COMMON_OPTIONS')||define( 'TENCENT_WORDPRESS_COMMON_OPTIONS', 'tencent_wordpress_common_options' );
defined('TENCENT_WORDPRESS_TMS_SHOW_NAME')||define( 'TENCENT_WORDPRESS_TMS_SHOW_NAME', 'tencentcloud-tms');
defined('TENCENT_WORDPRESS_PLUGINS_COMMON_URL')||define('TENCENT_WORDPRESS_PLUGINS_COMMON_URL', TENCENT_WORDPRESS_TMS_URL . '/common/');
defined('TENCENT_WORDPRESS_PLUGINS_COMMON_DIR')||define('TENCENT_WORDPRESS_PLUGINS_COMMON_DIR', TENCENT_WORDPRESS_TMS_DIR . '/common/');
defined('TENCENT_WORDPRESS_PLUGINS_COMMON_CSS_URL')||define('TENCENT_WORDPRESS_PLUGINS_COMMON_CSS_URL', TENCENT_WORDPRESS_PLUGINS_COMMON_URL . 'css/');

if (!is_file(TENCENT_WORDPRESS_TMS_DIR.'vendor/autoload.php')) {
    wp_die('缺少依赖文件，请确保安装了腾讯云sdk','缺少依赖文件',array('back_link'=>true));
}
require_once 'vendor/autoload.php';

use TencentWordpressTMS\TencentWordpressTMSActions;

$tmsPluginActions = new TencentWordpressTMSActions();
//启用
register_activation_hook(__FILE__, array($tmsPluginActions, 'initPlugin'));
//禁用
register_deactivation_hook(__FILE__,array($tmsPluginActions, 'disablePlugin'));
//卸载
register_uninstall_hook(__FILE__,array(TencentWordpressTMSActions::class, 'uninstallPlugin'));
//插件中心初始化
add_action('init',array($tmsPluginActions, 'initCommonSettingPage'));

//添加插件设置页面
add_action('admin_menu', array($tmsPluginActions, 'pluginSettingPage'));
// 插件列表加入设置按钮
add_filter('plugin_action_links', array($tmsPluginActions, 'pluginSettingPageLinkButton'), 101, 2);
//ajax保存配置
add_action('wp_ajax_update_TMS_options', array($tmsPluginActions, 'updateTMSOptions'));

add_action('all_admin_notices',array($tmsPluginActions,'commentFormTips'));
$TMSOptions = $tmsPluginActions::getTMSOptionsObject();
if ($TMSOptions->getFailOption() === $TMSOptions::FAIL_TO_FORBID_SUBMISSION) {
    //评论提交前审核
    add_action('pre_comment_content', array($tmsPluginActions, 'examineCommentBeforeInsertDatabase'));
} else {
    //评论提交后审核
    add_action('wp_insert_comment', array($tmsPluginActions, 'examineCommentAfterInsertDatabase'), 101, 2);
}

//新增或修改文章时对文章内容审核
add_action( 'publish_post', array($tmsPluginActions, 'examineCommentWhenSaveArticle'));

//js脚本引入
add_action('admin_enqueue_scripts', array($tmsPluginActions, 'loadMyScriptEnqueue'));
add_action('login_enqueue_scripts', array($tmsPluginActions, 'loadMyScriptEnqueue'));
add_action( 'admin_enqueue_scripts', array($tmsPluginActions, 'loadCSSEnqueue'),100,1);