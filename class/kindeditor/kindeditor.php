<?php
if(!defined('ClassCms')) {exit();}
class kindeditor {
    function install() {
        if(!C('cms:input:add','this:editor')) {
            Return 'KindEditor安装失败';
        }
    }
    function editor($action,$config=array()) {
        switch($action) {
            case "name":
                Return 'KindEditor编辑器';
            case "hash":
                Return 'kindeditor';
            case "group":
                Return '编辑器';
            case "sql":
                Return 'longtext';
            case "form":
                if(empty($config['style'])) {$config['style']='width:100%;height:400px;';}
                V('kindeditor.template',$config);
                Return '';
            case "ajax":
                if(isset($config['disabled']) && $config['disabled']) {Return array('message'=>'无权限','error'=>1);}
                if(isset($_FILES['imgFile']['name']) && isset($_GET['dir']) && $_GET['dir']=='image') {
                    $file_parse=explode('.',$_FILES['imgFile']['name']);
                    if(!isset($file_parse[1])) {
                        Return array('message'=>'图片后缀有误','error'=>1);
                    }else {
                        if(!in_array($file_parse[1],array('png','bmp','jpg','jpeg','gif','webp','tif'))) {
                            Return array('message'=>'图片后缀有误','error'=>1);
                        }
                    }
                }
                if($file_upload=C('cms:common:upload','imgFile',$config['filepath'])) {
                    if(!$file_upload['error']) {
                        Return array('url'=>$file_upload['url'][0],'error'=>0);
                    }else {
                        if(isset($file_upload['message'])) {
                            Return array('message'=>$file_upload['message'],'error'=>1);
                        }else {
                            Return array('message'=>'error','error'=>1);
                        }
                    }
                }
                Return array('error'=>1,'message'=>'error');
            case "post":
                if(!isset($config['auth']['safehtml']) || !$config['auth']['safehtml']) {
                    Return C('cms:common:safeHtml',@$_POST[$config['name']]);
                }
                Return @$_POST[$config['name']];
            case "auth":
                Return array('safehtml'=>'不过滤危险代码');
            case "config":
                Return array(
                            array('configname'=>'简洁模式','hash'=>'simple','inputhash'=>'switch','tips'=>'','defaultvalue'=>'0'),
                            array('configname'=>'样式','hash'=>'style','inputhash'=>'text','tips'=>'编辑器样式,如:width:60%;height:400px','defaultvalue'=>''),
                            array('configname'=>'目录','hash'=>'filepath','inputhash'=>'text','tips'=>'上传文件保存目录,如: /upload/(Y)(m)(d)/ 请确保此目录有写入权限,不填则为默认目录')
                        );
        }
        Return false;
    }
}