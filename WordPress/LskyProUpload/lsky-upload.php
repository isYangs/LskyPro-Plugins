<?php
/*
Plugin Name: 兰空图床上传
Plugin URI: 
Description: 可以直接在编辑时点击上传按钮上传图片至兰空图床(LskyPro)，安装完成后先在插件设置中填写对应参数后再使用，若在使用过程中出现问题或者Bug请截图保存反馈至作者邮箱
Version: 1.0.0
Author: isYangs
Author URI: https://wpa.qq.com/wpa_jump_page?v=3&uin=750837279&site=qq&menu=yes
*/

//-------------  还请各位大佬手下留情，不要改作者署名和作者链接，蟹蟹啦~~~ --------------
if (!defined('ABSPATH')) {
    die;
}
define('LskyPro_WordPress_FILE', __FILE__);
define('LskyPro_WordPress_DIRNAME', dirname(__FILE__));
define('LskyPro_WordPress_Plugin', plugins_url('',__FILE__));

require LskyPro_WordPress_DIRNAME . '/includes/lsky-config.php';
require LskyPro_WordPress_DIRNAME . '/upload/lsky-img.php';

add_action( 'admin_init', 'lsky_upload_admin_init' );
function lsky_upload_admin_init() {
	add_filter( 'plugin_action_links', 'lsky_upload_add_link', 10, 2 );
}
function lsky_upload_add_link( $actions, $plugin_file ) {
    static $plugin;
    if (!isset($plugin))
    $plugin = plugin_basename(__FILE__);
    if ($plugin == $plugin_file) {
        $settings = array('settings' => '<a href="admin.php?page=lskyupload_options">' . __('Settings') . '</a>');
        $site_link  = array('contact' => '<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=isYangs@foxmail.com" target="_blank">反馈</a>','lsky' => '<a href="https://www.lsky.pro/" target="_blank">兰空官网</a>');
        $actions  = array_merge($settings, $actions);
        $actions  = array_merge($site_link, $actions);
    }
    return $actions;
}

?>