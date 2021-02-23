<?php
if(!defined('ClassCms')) {exit();}
class shop {
    function init(){
        Return array(
            'template_dir' => 'template',
        );
    }
    function auth() {
        Return array('index'=>'浏览商店','downloadClass'=>'下载应用','upgradeClass;refreshClass;adminconfig'=>'更新应用');
    }
    function hook() {
        $hooks=array();
        $hooks[]=array('hookname'=>'show','hookedfunction'=>'admin:body','enabled'=>1);
        Return $hooks;
    }
    function show() {
        if(!P('index')) {Return ;}
        if(isset($GLOBALS['C']['admin']['load']) && $GLOBALS['C']['admin']['load']=='admin:class:index') {
            echo('<script>layui.use([\'index\'],function(){layui.$(\'#cms-right-top-button\').append(\'<a href="?do=shop:index" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-cart-simple"></i><b>应用商店</b></a>\');});</script>');
        }
        if(isset($GLOBALS['C']['admin']['load']) && $GLOBALS['C']['admin']['load']=='admin:class:config') {
            if($class=C('cms:class:get',@$_GET['hash'])) {
                V('config',array('hash'=>$class['hash'],'classname'=>$class['classname']));
            }
        }
    }
    function index() {
        if(!function_exists("curl_init") || !ini_get('allow_url_fopen')) {
            echo('您的主机不支持Curl组件,无法访问应用商店');
            Return ;
        }
        if(count($_GET)===1 && isset($_GET['do']) && $_GET['do']=='shop:index') {
            $array['content']='<meta http-equiv=refresh content=\'0; url=?do=shop:index&action=home\'><i class="layui-icon layui-icon-loading layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i>';
        }else {
            $array['content']=C('this:get');
        }
        if(isset($_GET['ajax']) && $_GET['ajax']) {
            if($array['content']===false) {$array['content']=json_encode(array('msg'=>'error','error'=>1));}
            echo($array['content']);
            Return ;
        }
        if($array['content']===false) {$array['content']='<meta http-equiv=refresh content=\'5; url=\'><i class="layui-icon layui-icon-loading layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i>重新连接中(多次错误后将自动切换服务器)';}
        if(isset($_GET['html']) && $_GET['html']==0) {
            $array['html']=0;
        }else {
            $array['html']=1;
        }
        if(isset($_GET['bread']) && !empty($_GET['bread'])) {
            $array['bread']=htmlspecialchars($_GET['bread']);
        }else {
            $array['bread']='';
        }
        V('index',$array);
        Return true;
    }
    function downloadClass() {
        if(!is_hash(@$_POST['classhash'])) {
            Return ;
        }
        $classhash=$_POST['classhash'];
        $url=$_POST['url'];
        if(C('cms:class:get',$classhash)) {
            echo(json_encode(array('msg'=>'应用已存在','error'=>1)));
            Return ;
        }
        if (!function_exists("curl_init")){
            echo(json_encode(array('msg'=>"服务器未安装Curl组件,无法下载应用文件",'error'=>1)));
            Return ;
        }
        if(!function_exists('zip_open') || !class_exists('ZipArchive')) {
            echo(json_encode(array('msg'=>"未安装zip组件,无法解压安装包",'error'=>1)));
            Return ;
        }
        $classdir=classDir($classhash);
        $path=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['CacheDir'].DIRECTORY_SEPARATOR.'shop'.DIRECTORY_SEPARATOR;
        if(!cms_createdir($path)) {
            echo(json_encode(array('msg'=>"创建缓存目录失败,无法下载",'error'=>1)));
            Return ;
        }
        $classfile=$path.md5($classhash.time()).'.class';
        if(!C('this:download',$url,$classfile)) {
            echo(json_encode(array('msg'=>"下载失败",'error'=>1)));
            Return ;
        }
        if(isset($_POST['md5']) && !empty($_POST['md5']) && function_exists("md5_file")) {
            if($_POST['md5']!=@md5_file($classfile)) {
                echo(json_encode(array('msg'=>"文件校验失败,请重新下载",'error'=>1)));
                Return ;
            }
        }
        if(C('cms:class:unzip',$classfile,$classdir)) {
            @unlink($classfile);
            if(C('cms:class:refresh',$classhash)) {
                echo(json_encode(array('msg'=>"下载完成,请在应用管理页面中安装此应用")));
                Return ;
            }else {
                echo(json_encode(array('msg'=>"安装包格式错误,请重试",'error'=>1)));
                Return ;
            }
        }else{
            @unlink($classfile);
            echo(json_encode(array('msg'=>"安装包解压失败,请重试",'error'=>1)));
            Return ;
        }
        Return ;
    }
    function upgradeClass() {
        if(!is_hash(@$_POST['classhash'])) {
            Return ;
        }
        $classhash=$_POST['classhash'];
        if(!$classinfo=C('cms:class:get',$classhash)) {
            echo(json_encode(array('msg'=>'应用不存在','error'=>1)));
            Return ;
        }
        $old_version=$classinfo['classversion'];
        $new_version=@$_POST['version'];
        if($old_version>=$new_version) {
            echo(json_encode(array('msg'=>"无需更新",'error'=>1)));
            Return ;
        }
        $url=$_POST['url'];
        if (!function_exists("curl_init")){
            echo(json_encode(array('msg'=>"服务器未安装Curl组件,无法下载应用文件",'error'=>1)));
            Return ;
        }
        if(!function_exists('zip_open') || !class_exists('ZipArchive')) {
            echo(json_encode(array('msg'=>"未安装zip组件,无法解压安装包",'error'=>1)));
            Return ;
        }
        $classdir=classDir($classhash);
        $path=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['CacheDir'].DIRECTORY_SEPARATOR.'shop'.DIRECTORY_SEPARATOR;
        if(!cms_createdir($path)) {
            echo(json_encode(array('msg'=>"创建缓存目录失败,无法下载",'error'=>1)));
            Return ;
        }
        $classfile=$path.md5($classhash.time()).'.class';
        if(!C('this:download',$url,$classfile)) {
            echo(json_encode(array('msg'=>"下载失败",'error'=>1)));
            Return ;
        }
        if(isset($_POST['md5']) && !empty($_POST['md5']) && function_exists("md5_file")) {
            if($_POST['md5']!=@md5_file($classfile)) {
                echo(json_encode(array('msg'=>"文件校验失败,请重新下载",'error'=>1)));
                Return ;
            }
        }
        if(C('cms:class:unzip',$classfile,$classdir)) {
            @unlink($classfile);
            C('cms:common:opcacheReset');
            echo(json_encode(array('msg'=>"下载完成")));
            Return ;
        }else{
            @unlink($classfile);
            echo(json_encode(array('msg'=>"安装包解压失败,请重试",'error'=>1)));
            Return ;
        }
        Return ;
    }
    function refreshClass() {
        if(!is_hash(@$_POST['classhash'])) {
            Return ;
        }
        $classhash=$_POST['classhash'];
        if(!$classinfo=C('cms:class:get',$classhash)) {
            echo(json_encode(array('msg'=>'应用不存在','error'=>1)));
            Return ;
        }
        $upgradeinfo=C('cms:class:upgrade',$classhash);
        if($upgradeinfo===true) {
            echo(json_encode(array('msg'=>"更新完成")));
            Return ;
        }else {
            echo(json_encode(array('msg'=>'更新失败.'.$upgradeinfo,'error'=>1)));
            Return ;
        }
    }
    function adminconfig() {
        if(!$class=C('cms:class:get',$_POST['hash'])) {
            echo(json_encode(array('msg'=>"error",'error'=>1)));
            Return ;
        }
        $array=array();
        $array['requires']='';
        $array['msg']='';
        $array['error']=0;
        if(isset($class['requires']) && !empty($class['requires'])) {
            $requires=explode(';',$class['requires']);
            foreach($requires as $require) {
                if(is_hash($require)) {
                    $thisclass=C('cms:class:get',$require);
                    if($thisclass && $thisclass['installed']) {
                        $array['requires'].='<a class="layui-btn layui-btn-xs layui-btn-normal" href="?do=admin:class:config&hash='.$require.'"><i class="layui-icon layui-icon-ok"></i>'.$require.'</a> ';
                    }elseif($thisclass) {
                        $array['requires'].='<a class="layui-btn layui-btn-xs layui-btn-primary" href="?do=admin:class:config&hash='.$require.'"><i class="layui-icon layui-icon-close"></i>'.$require.' [未安装]</a> ';
                    }else {
                        $array['requires'].='<a class="layui-btn layui-btn-xs layui-btn-primary" href="?do=shop:index&action=detail&classhash='.$require.'"><i class="layui-icon layui-icon-close"></i>'.$require.' [未下载]</a> ';
                    }
                }
            }
        }
        echo(json_encode($array));
        Return ;
    }
    function defaultHost() {
        Return 'classcms.com;classcms.uuu.la';
    }
    function shopHost() {
        if(!$defaulthost=config('defaulthost')) {$defaulthost=C('this:defaultHost');}
        $defaulthost=explode(';',$defaulthost);
        $lasthost=config('host');
        if(!$lasthost) {$host=$defaulthost[0];}
        if(config('errorcount')>5) {
            $host=$lasthost;
            $i=0;
            while($host==$lasthost && $i<10) {
                $i++;
                $host=$defaulthost[rand(0,count($defaulthost)-1)];
            }
            config('errorcount',0);
        }
        if(isset($host)) {
            config('host',$host);
            Return $host;
        }
        config('host',$lasthost);
        Return $lasthost;
    }
    function get($data=array()) {
        $host=C('this:shopHost');
        $url='http://'.$host.'/shop/';
        foreach($_GET as $key=>$val) {
            $data[$key]=$val;
        }
        foreach($_POST as $key=>$val) {
            $data[$key]=$val;
        }
        $class_config_query=array();
        $class_config_query['table']='config';
        $class_config_query['where']=array('classhash'=>__CLASS__);
        $class_config_query['limit']=50;
        $class_configs=all($class_config_query);
        if(is_array($class_configs) && count($class_configs)) {
            foreach($class_configs as $key=>$class_config) {
                if($key<300) {
                    $data['_'.$class_config['hash']]=$class_config['value'];
                }
            }
        }
        $data['_domain']=@server_name();
        $data['_hash']=@$GLOBALS['C']['SiteHash'];
        $data['_ip']=C('cms:common:ip');
        $data['_uid']=C('admin:nowuser');
        $data['_referer']=@$_SERVER['HTTP_REFERER'];
        $data['_ua']=@$_SERVER['HTTP_USER_AGENT'];
        $data['_php']=@PHP_VERSION;
        $data['_os']=@php_uname('s');
        $data['_time']=time();
        if($classes=C('cms:class:all')) {
            $data['_classes']='';
            foreach($classes as $key=>$class) {
                if($key<100) {
                    $data['_classes'].=$class['hash'].','.$class['classversion'].','.$class['enabled'].'|';
                }
            }
        }
        if (function_exists("curl_init")){
            $data['_curl']=1;
            $curl=curl_init();
            curl_setopt($curl,CURLOPT_URL,$url);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
            curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
            curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,10);
            curl_setopt($curl,CURLOPT_TIMEOUT,120);
            $content=curl_exec($curl);
            $httpinfo=curl_getinfo($curl);
            curl_close($curl);
            if($httpinfo['http_code']>=300) {$content=false;}
        }else{
            $data['_curl']=0;
            $options['http'] = array('timeout'=>120,'method' => 'POST','header' => 'Content-type:application/x-www-form-urlencoded','content' =>http_build_query($data));
            $content = @file_get_contents($url, false, stream_context_create($options));
        }
        if (!strlen($content)){
            if(!$errorcount=config('errorcount')) {$errorcount=0;}
            config('errorcount',$errorcount+1);
            Return false;
        }
        config('errorcount',0);
        preg_match_all('|<!-- \[\[(.+):(.*)\]\] -->|U',$content,$htmlconfig);
        if(isset($htmlconfig[0][0])) {
            foreach($htmlconfig[0] as $key=>$val) {
                if(is_hash($htmlconfig[1][$key])) {
                    config($htmlconfig[1][$key],$htmlconfig[2][$key]);
                    $content=str_replace($htmlconfig[0][$key],'',$content);
                }
            }
        }
        Return $content;
    }
    function download($url,$filepath) {
        $hosts=array_merge(explode(';',C('this:defaultHost')),array(config('host')));
        if($defaulthost=config('defaulthost')) {
            $hosts=array_merge($hosts,explode(';',$defaulthosts));
        }
        $checkurl=parse_url($url);
        if(!isset($checkurl['host']) || !in_array($checkurl['host'],$hosts)) {
            Return false;
        }
        $curl=curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        if(!$fp = @fopen ($filepath,'w+')) {
            Return false;
        }
        curl_setopt($curl,CURLOPT_FILE, $fp);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($curl,CURLOPT_TIMEOUT,300);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
        $info=curl_exec($curl);
        $httpinfo=curl_getinfo($curl);
        curl_close($curl);
        fclose($fp);
        if($httpinfo['http_code']>=300) {@unlink($filepath);Return false;}
        Return $info;
    }
}