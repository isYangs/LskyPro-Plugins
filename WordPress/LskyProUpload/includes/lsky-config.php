<?php
add_action('admin_menu', 'lskyupload_menu_page');
function lskyupload_menu_page(){
    add_menu_page('兰空图床设置', '兰空图床设置', 'administrator', 'lskyupload_options', 'lskyupload_options', 'dashicons-format-image', 99);
}
//设置页面
function lskyupload_options(){?>
<link href="<?php echo plugin_dir_url(__FILE__ ); ?>assets/style.css" type="text/css" rel="stylesheet" />
<h2>兰空图床(LskyPro)上传设置</h2>
<?php update_lskyupload_options() ?>
<form method="post"> 
	<table class="tb-set" width="100%" style='padding:0;margin:0;' cellspacing='0' cellpadding='0'>	
<tr>
	<td align="right"><b>API网址设置</b><br />(填写要对接图床的域名，必须带有http://或https://)</td>
    <td colspan="3"><input type="url" class="txt txt-sho"  style="width:300px;font-weight:bold;" name="domain" value="<?php if(get_option('domain')==''){echo '';}else{echo get_option('domain');} ?>" />例如：https://www.lsky.pro</td></td>
</tr>
<tr>
    <td align="right" width="30%"><b>图床Tokens</b><br />(填写<b>图床后台获取的Tokens</b>)</td>
    <td colspan="3"><input type="text" class="txt txt-sho"  style="width:300px;font-weight:bold;" name="tokens" value="<?php if(get_option('tokens')==''){echo '';}else{echo get_option('tokens');} ?>" />例如：1|1bJbwlqBfnggmOMEZqXT5XusaIwqiZjCDs7r1Ob5</td>
</tr>

<tr>
	<td align="right"><b>是否公开图片</b><br />(填写0或1，0为私有图片，1为公开图片)</td>
	<td colspan="3"><input type="number" class="txt txt-sho"  style="width:300px;font-weight:bold;" name="permission" value="<?php if(get_option('permission')==''){echo '';}else{echo get_option('permission');} ?>" /></td></td>
</tr>

<tr>
    <td align="right" width="30%"></td>
	<td colspan="4"><input onclick="load();" type="submit" class="button" name="submit" id="submit" value="保存设置" /></td>
</tr>
	</table>
</form>
<script>
function load() {document.getElementById("submit").value="保存中，请稍候...";setTimeout(function() {document.getElementById("submit").disabled="false";}, 1)}</script>
<?php }
function update_lskyupload_options(){
	if(isset($_POST['submit'])){
		$updated = true;
		update_option('domain',$_POST['domain']);
        update_option('tokens',$_POST['tokens']);
        update_option('permission',$_POST['permission']);
		if($updated){
			echo '<div class="message success">设置成功！</div>';
		}else{
			echo '<div class="message error">保存失败！请检查网络或设置！</div>';
		}
	}
}
?>