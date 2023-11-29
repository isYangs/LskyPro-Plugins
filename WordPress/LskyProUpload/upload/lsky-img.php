<?php
add_action('media_buttons', 'add_lsky_upload_button');
function add_lsky_upload_button() {
    if(!empty(wp_get_current_user()->roles) && in_array('administrator', wp_get_current_user()->roles)){
        $getdataDoMain = get_option('domain');
        $getdataTokens = get_option('tokens');
        $getdataPermission = get_option('permission');
    }
    else return 0;
?>
<link href="<?php echo plugin_dir_url(__FILE__ ); ?>assets/style.css" type="text/css" rel="stylesheet" />
<a href="javascript:;" class="lsky-upload"><input type="file" id="input-lsky-upload" multiple />上传图片</a>
<span id="tip">上传图片过程中请耐心等待！请勿刷新页面，如果等待时间过长没有上传成功可以刷新页面重试或检查插件设置</span>
<script src="//lib.baomitu.com/axios/0.27.2/axios.min.js"></script>
<script>
    const lsky_upload = document.getElementById('input-lsky-upload');
    lsky_upload.addEventListener("change", () => {
        const files = lsky_upload.files;
        for(let i = 0; i < files.length; i++) {
            const file = files[i];
            const formData = new FormData();
            formData.append('file', file);
            formData.append('permission', '<?php echo $getdataPermission; ?>');
            document.getElementById('input-lsky-upload').disabled = true;
            document.getElementById('tip').innerHTML = '正在上传中...';
            axios.defaults.crossDomain = true;
            axios({
                method:'post',
                url: '<?php echo $getdataDoMain; ?>/api/v1/upload',
                data:formData,
                headers: {
                    'Access-Control-Allow-Origin': '*',
                    'Access-Control-Allow-Methods': 'POST',
                    'Access-Control-Allow-Headers': 'Content-Type',
                    Accept: 'application/json',
                    'Content-Type': 'multipart/form-data',
                    Authorization: 'Bearer <?php echo $getdataTokens; ?>'
                }
            }).then(response => {
                const data = response.data;
                if (data.status) {
                    const url = data.data.links.url;
                    const name = data.data.origin_name;
                    wp.media.editor.insert('<a href="'+ url +'"><img src="'+ url +'" alt="'+ name +'" /></a>');
                    document.getElementById('input-lsky-upload').disabled = false;
                    document.getElementById('tip').innerHTML = '上传图片过程中请耐心等待！请勿刷新页面，如果等待时间过长没有上传成功可以刷新页面重试或检查插件设置';
                }
            }).catch(error => {
                console.log(error);
                document.getElementById('input-lsky-upload').disabled = false;
                document.getElementById('tip').innerHTML = '上传图片过程中请耐心等待！请勿刷新页面，如果等待时间过长没有上传成功可以刷新页面重试或检查插件设置';
            });
        }
    });
</script>
<?php }
add_action('add_meta_boxes', 'add_lsky_upload_box');
function add_lsky_upload_box() {
    if(!empty(wp_get_current_user()->roles) && in_array('administrator', wp_get_current_user()->roles)){
        add_meta_box('lsky_upload_box', '兰空图床(LskyPro)上传', 'lsky_upload_box', 'post', 'side', 'high');
    }
    else return 0;
}
function lsky_upload_box() {?>
    <link href="<?php echo plugin_dir_url(__FILE__ ); ?>assets/style.css" type="text/css" rel="stylesheet" />
    <div id="lsky-upload-box">点击此区域上传图片</div>
    <input type="file" multiple id="lsky-upload-box-input" />
    <script src="//lib.baomitu.com/axios/0.27.2/axios.min.js"></script>
    <script>
        const lsky_upload_input = document.getElementById('lsky-upload-box-input');
        lsky_upload_input.addEventListener("change", () => {
            const files = lsky_upload_input.files;
            for(let i = 0; i < files.length; i++) {
                const file = files[i];
                const formData = new FormData();
                formData.append('file', file);
                formData.append('permission', '<?php echo get_option('permission'); ?>');
                console.log(formData);
                document.getElementById('lsky-upload-box-input').disabled = true;
                document.getElementById('lsky-upload-box').innerHTML = '正在上传中...';
                axios({
                    method:'post',
                    url: '<?php echo get_option('domain'); ?>/api/v1/upload',
                    data:formData,
                    headers: {
                        'Access-Control-Allow-Origin': '*',
                        'Access-Control-Allow-Methods': 'POST',
                        'Access-Control-Allow-Headers': 'Content-Type',
                        Accept: 'application/json',
                        'Content-Type': 'multipart/form-data',
                        Authorization: 'Bearer <?php echo get_option('tokens'); ?>'
                    }
                }).then(response => {
                    const data = response.data;
                    if (data.status) {
                        const url = data.data.links.url;
                        const name = data.data.origin_name;
                        wp.media.editor.insert('<a href="'+ url +'"><img src="'+ url +'" alt="'+ name +'" /></a>');
                        document.getElementById('lsky-upload-box-input').disabled = false;
                        document.getElementById('lsky-upload-box').innerHTML = '点击此区域上传图片';
                    }
                }).catch(error => {
                    console.log(error);
                    document.getElementById('lsky-upload-box-input').disabled = false;
                    document.getElementById('lsky-upload-box').innerHTML = '点击此区域上传图片';
                });
            }
        });
    </script>
<?php }?>