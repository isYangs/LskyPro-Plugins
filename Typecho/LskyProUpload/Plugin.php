<?php
namespace TypechoPlugin\LskyProUpload;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Common;
use Widget\Options;
use Widget\Upload;
use CURLFile;

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 可以直接在编辑时点击上传按钮上传图片至兰空图床(LskyPro)，安装完成后先在插件设置中填写对应参数后再使用，若在使用过程中出现问题或者Bug请截图保存反馈至作者邮箱
 *
 * @package LskyProUpload
 * @author isYangs
 * @version 1.0.0
 * @link https://wpa.qq.com/wpa_jump_page?v=3&uin=750837279&site=qq&menu=yes
 */

class Plugin implements PluginInterface
{
    const UPLOAD_DIR  = '/usr/uploads';
    const PLUGIN_NAME = 'LskyProUpload';

    public static function activate()
    {
        \Typecho\Plugin::factory('Widget_Upload')->uploadHandle     = __CLASS__.'::uploadHandle';
        \Typecho\Plugin::factory('Widget_Upload')->modifyHandle     = __CLASS__.'::modifyHandle';
        \Typecho\Plugin::factory('Widget_Upload')->deleteHandle     = __CLASS__.'::deleteHandle';
        \Typecho\Plugin::factory('Widget_Upload')->attachmentHandle = __CLASS__.'::attachmentHandle';
    }

    public static function deactivate()
    {

    }

    public static function config(Form $form)
    {
        $desc = new Text('desc', NULL, '', '插件介绍：', '<p>本插件由isYangs基于泽泽站长的插件修改而来的 &nbsp;&nbsp; <a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=isYangs@foxmail.com" target="_blank">点我反馈Bug</a> &nbsp;&nbsp; <a href="https://www.lsky.pro/" target="_blank">兰空官网</a></p>');
        $form->addInput($desc);

        $api = new Text('api', NULL, '', 'Api：', '只需填写域名包含 http 或 https 无需<code style="padding: 2px 4px; font-size: 90%; color: #c7254e; background-color: #f9f2f4; border-radius: 4px;"> / </code>结尾<br><code style="padding: 2px 4px; font-size: 90%; color: #c7254e; background-color: #f9f2f4; border-radius: 4px;">示例地址：https://lsky.pro</code>');
        $form->addInput($api);

        $token = new Text('token', NULL, '', 'Token：', '请按示例严格填写：<code style="padding: 2px 4px; font-size: 90%; color: #c7254e; background-color: #f9f2f4; border-radius: 4px;">1|UYsgSjmtTkPjS8qPaLl98dJwdVtU492vQbDFI6pg</code>');
        $form->addInput($token);
        
         $strategy_id = new Text('strategy_id', NULL, '', 'Strategy_id：', '如果为空，则为默认存储id');
        $form->addInput($strategy_id);
        

        echo '<script>window.onload = function(){document.getElementsByName("desc")[0].type = "hidden";}</script>';
    }

    public static function personalConfig(Form $form)
    {
    }

    public static function uploadHandle($file)
    {
        if (empty($file['name'])) {

            return false;
        }

        $ext = self::_getSafeName($file['name']);

        if (!Upload::checkFileType($ext) || Common::isAppEngine()) {

            return false;
        }

        if (self::_isImage($ext)) {

            return self::_uploadImg($file, $ext);
        }

        return self::_uploadOtherFile($file, $ext);
    }

    public static function deleteHandle(array $content): bool
    {
    
		$ext = $content['attachment']->type;

        if (self::_isImage($ext)) {

            return self::_deleteImg($content);
        }

        return unlink($content['attachment']->path);
    }

    public static function modifyHandle($content, $file)
    {
        if (empty($file['name'])) {

            return false;
        }
        $ext = self::_getSafeName($file['name']);
        if ($content['attachment']->type != $ext || Common::isAppEngine()) {

            return false;
        }

        if (!self::_getUploadFile($file)) {

            return false;
        }

        if (self::_isImage($ext)) {
            self::_deleteImg($content);

            return self::_uploadImg($file, $ext);
        }
      
        return self::_uploadOtherFile($file, $ext);
    }

    public static function attachmentHandle(array $content): string
    {
		$arr = unserialize($content['text']);
		$text = strstr($content['text'],'.');
		$ext = substr($text,1,3);
        if (self::_isImage($ext)) {

            return $content['attachment']->path ?? '';
        }

        $ret = explode(self::UPLOAD_DIR, $arr['path']);
        return Common::url(self::UPLOAD_DIR . @$ret[1], Options::alloc()->siteUrl);
    }

    private static function _getUploadDir($ext = ''): string
    {
        if (self::_isImage($ext)) {
            $url = parse_url(Options::alloc()->siteUrl);
            $DIR = str_replace('.', '_', $url['host']);
            return '/' . $DIR . self::UPLOAD_DIR;
        } elseif (defined('__TYPECHO_UPLOAD_DIR__')) {
            return __TYPECHO_UPLOAD_DIR__;
        } else {
            $path = Common::url(self::UPLOAD_DIR, __TYPECHO_ROOT_DIR__);
            return $path;
        }
    }

    private static function _getUploadFile($file): string
    {
        return $file['tmp_name'] ?? ($file['bytes'] ?? ($file['bits'] ?? ''));
    }

    private static function _getSafeName(&$name): string
    {
        $name = str_replace(array('"', '<', '>'), '', $name);
        $name = str_replace('\\', '/', $name);
        $name = false === strpos($name, '/') ? ('a' . $name) : str_replace('/', '/a', $name);
        $info = pathinfo($name);
        $name = substr($info['basename'], 1);

        return isset($info['extension']) ? strtolower($info['extension']) : '';
    }

    private static function _makeUploadDir($path): bool
    {
        $path    = preg_replace("/\\\+/", '/', $path);
        $current = rtrim($path, '/');
        $last    = $current;

        while (!is_dir($current) && false !== strpos($path, '/')) {
            $last    = $current;
            $current = dirname($current);
        }

        if ($last == $current) {
            return true;
        }

        if (!@mkdir($last)) {
            return false;
        }

        $stat  = @stat($last);
        $perms = $stat['mode'] & 0007777;
        @chmod($last, $perms);

        return self::_makeUploadDir($path);
    }

    private static function _isImage($ext): bool
    {
        $img_ext_arr = array('gif','jpg','jpeg','png','tiff','bmp','ico','psd','webp','JPG','BMP','GIF','PNG','JPEG','ICO','PSD','TIFF','WEBP');
        return in_array($ext, $img_ext_arr);
    }

    private static function _uploadOtherFile($file, $ext)
    {
        $dir = self::_getUploadDir($ext) . '/' . date('Y') . '/' . date('m');
        if (!self::_makeUploadDir($dir)) {

            return false;
        }

        $path = sprintf('%s/%u.%s', $dir, crc32(uniqid()), $ext);
        if (!isset($file['tmp_name']) || !@move_uploaded_file($file['tmp_name'], $path)) {

            return false;
        }

        return [
            'name' => $file['name'],
            'path' => $path,
            'size' => $file['size'] ?? filesize($path),
            'type' => $ext,
            'mime' => @Common::mimeContentType($path)
        ];
    }

    private static function _uploadImg($file, $ext)
    {
       
    
        $options = Options::alloc()->plugin(self::PLUGIN_NAME);
        $api     = $options->api . '/api/v1/upload';
		$token   = 'Bearer '.$options->token;
        $strategyId = $options->strategy_id;
        
        $tmp     = self::_getUploadFile($file);
        if (empty($tmp)) {

            return false;
        }

		$img = $file['name'];
        if (!rename($tmp, $img)) {

            return false;
        }
        $params = ['file' => new CURLFile($img)];
        if ($strategyId) {
            $params['strategy_id'] = $strategyId;
        }

        $res = self::_curlPost($api, $params, $token);
          
          
        unlink($img);

        if (!$res) {

            return false;
        }
      

        $json = json_decode($res, true);
     
        
        if ($json['status'] === false) {
            file_put_contents('./usr/plugins/'.self::PLUGIN_NAME.'/msg.log', json_encode($json, 256) . PHP_EOL, FILE_APPEND);
            return false;
        }
        
        $data = $json['data'];
        return [
            'img_key' => $data['key'],
            'img_id' => $data['md5'],
            'name'   => $data['origin_name'],
            'path'   => $data['links']['url'],
            'size'   => $data['size']*1024,
            'type'   => $data['extension'],
            'mime'   => $data['mimetype'],
			'description'  => $data['mimetype'],
        ];
    }

    private static function _deleteImg(array $content): bool
    {
      
        $options = Options::alloc()->plugin(self::PLUGIN_NAME);
  
        $api     = $options->api . '/api/v1/images';
        $token   = 'Bearer '.$options->token;
     

        $id = $content['attachment']->img_key;
        
        if (empty($id)) {
            return false;
        }
        
        $res  = self::_curlDelete($api . '/' . $id, ['key' => $id], $token);
        $json = json_decode($res, true);
    
        if (!is_array($json)) {

            return false;
        }

        return true;
    }

    private static function _curlDelete($api, $post, $token)
    {
        $headers = array(
            "Content-Type: multipart/form-data",
            "Accept: application/json",
            "Authorization: ".$token,
            );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
    
    private static function _curlPost($api, $post, $token)
    {
        $headers = array(
            "Content-Type: multipart/form-data",
            "Accept: application/json",
            "Authorization: ".$token,
            );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}