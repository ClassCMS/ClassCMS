<?php
if(!defined('ClassCms')) {exit();}
class shop {
    function init(){
        Return array(
            'template_dir' => 'template',
        );
    }
    function auth() {
        Return array('index'=>'浏览商店','downloadClass;installClass'=>'下载应用','upgradeClass;refreshClass'=>'更新应用','adminconfig'=>'显示依赖应用信息');
    }
    function hook() {
        $hooks=array();
        $hooks[]=array('hookname'=>'show','hookedfunction'=>'admin:body','enabled'=>1,'requires'=>'GLOBALS.C.admin.load=admin:class:index;p.index');
        $hooks[]=array('hookname'=>'configShow','hookedfunction'=>'admin:body','enabled'=>1,'requires'=>'GLOBALS.C.admin.load=admin:class:config;p.index');
        Return $hooks;
    }
    function show() {
        echo('<script>layui.use([\'index\'],function(){layui.$(\'#cms-right-top-button\').append(\'<a href="?do=shop:index" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-cart-simple"></i><b>应用商店</b></a>\');});</script>');
    }
    function configShow() {
        if($class=C('cms:class:get',@$_GET['hash'])) {
            $homeroute=array();
            foreach ($GLOBALS['route'] as $thisroute) {
                if(isset($thisroute['uri']) && $thisroute['uri']=='/' && !isset($thisroute['domain'])){
                    $homeroute[$thisroute['classhash']]=1;
                }
            }
            if(!$class['enabled'] || !$class['module']){
                $homeroute=array();
            }elseif(count($homeroute)<2){
                $homeroute=array();
            }elseif($domainbind=C('cms:class:get','domainbind')){
                if($domainbind && $domainbind['enabled']){
                    $homeroute=array();
                }
            }
            if(isset($_GET['nobread']) && $_GET['nobread']==1){
                $nobread=true;
            }else{
                $nobread=false;
            }
            V('config',array('hash'=>$class['hash'],'classname'=>$class['classname'],'homeroute'=>count($homeroute),'nobread'=>$nobread));
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
        if(isset($_GET['nobread'])){
            $array['breadcrumb']=false;
        }elseif(isset($GLOBALS['shop']['bread'])) {
            $array['breadcrumb']=array_merge(array(array('title'=>'应用商店','url'=>'?do=shop:index&action=home')),$GLOBALS['shop']['bread']);
        }else {
            $array['breadcrumb']=array(array('title'=>'应用商店','url'=>'?do=shop:index&action=home'));
        }
        Return V('index',$array);
    }
    function downloadClass() {
        if(!is_hash(@$_POST['classhash'])) {
            Return ;
        }
        $classhash=$_POST['classhash'];
        $url=$_POST['url'];
        
        if(C('cms:class:get',$classhash)) {
            return E('应用已存在');
        }
        if (!function_exists("curl_init")){
            return E('服务器未安装Curl组件,无法下载应用文件');
        }
        if(!function_exists('zip_open') || !class_exists('ZipArchive')) {
            return E('未安装zip组件,无法解压安装包');
        }
        $classdir=classDir($classhash);
        $path=cacheDir('shop');
        if(!cms_createdir($path)) {
            return E('创建缓存目录失败,无法下载');
        }
        $classfile=$path.md5($classhash.time()).'.class';
        if(!C('this:download',$url,$classfile)) {
            return E('下载失败');
        }
        if(isset($_POST['md5']) && !empty($_POST['md5']) && function_exists("md5_file")) {
            if($_POST['md5']!=@md5_file($classfile)) {
                return E('文件校验失败,请重新下载');
            }
        }
        if(C('cms:class:unzip',$classfile,$classdir)) {
            @unlink($classfile);
            if(C('cms:class:refresh',$classhash)) {
                return '下载完成,请点击"管理",安装此应用';
            }else {
                return E('安装包格式错误,请重试');
            }
        }else{
            @unlink($classfile);
            return E('安装包解压失败,请检查应用目录权限');
        }
        Return ;
    }
    function upgradeClass() {
        if(!is_hash(@$_POST['classhash'])) {
            Return ;
        }
        $classhash=$_POST['classhash'];
        if(!$classinfo=C('cms:class:get',$classhash)) {
            return E('应用不存在');
        }
        $old_version=$classinfo['classversion'];
        $new_version=@$_POST['version'];
        if($old_version>=$new_version) {
            return E('无需更新');
        }
        $url=$_POST['url'];
        if (!function_exists("curl_init")){
            return E('服务器未安装Curl组件,无法下载应用文件');
        }
        if(!function_exists('zip_open') || !class_exists('ZipArchive')) {
            return E('未安装zip组件,无法解压安装包');
        }
        $classdir=classDir($classhash);
        $path=cacheDir('shop');
        if(!cms_createdir($path)) {
            return E('创建缓存目录失败,无法下载');
        }
        $classfile=$path.md5($classhash.time()).'.class';
        if(!C('this:download',$url,$classfile)) {
            return E('下载失败');
        }
        if(isset($_POST['md5']) && !empty($_POST['md5']) && function_exists("md5_file")) {
            if($_POST['md5']!=@md5_file($classfile)) {
                return E('文件校验失败,请重新下载');
            }
        }
        if(C('cms:class:unzip',$classfile,$classdir)) {
            @unlink($classfile);
            C('cms:common:opcacheReset');
            return '下载完成';
        }else{
            @unlink($classfile);
            return E('安装包解压失败,请检查应用目录权限');
        }
    }
    function installClass() {
        if(!is_hash(@$_POST['classhash'])) {
            Return ;
        }
        $classhash=$_POST['classhash'];
        if(!$classinfo=C('cms:class:get',$classhash)) {
            return E('应用不存在');
        }
        if(!C('cms:class:requires',$classhash)) {
            return E('安装失败.请先安装依赖应用');
        }
        if($info=C('cms:class:install',$classhash)){
            if($info===true){ $info='安装成功'; }
            if(is_string($info)){
                return array('msg'=>$info,'popup'=>array('end'=>'reload','btns'=>array('好的'=>'reload')));
            }
            if(isset($info['popup']) && !isset($info['popup']['end'])){
                $info['popup']['end']='reload';
            }
            return $info;
        }else{
            if(E()){
                Return E(E());
            }
            Return E('安装失败');
        }
    }
    function refreshClass() {
        if(!is_hash(@$_POST['classhash'])) {
            Return ;
        }
        $classhash=$_POST['classhash'];
        if(!$classinfo=C('cms:class:get',$classhash)) {
            return E('应用不存在');
        }
        if($upgradeinfo=C('cms:class:upgrade',$classhash)){
            if($upgradeinfo===true){ $upgradeinfo='更新成功'; }
            if(is_string($upgradeinfo)){
                return array('msg'=>$upgradeinfo,'popup'=>array('end'=>'reload','btns'=>array('好的'=>'reload')));
            }
            if(isset($upgradeinfo['popup']) && !isset($upgradeinfo['popup']['end'])){
                $upgradeinfo['popup']['end']='reload';
            }
            return $upgradeinfo;
        }else{
            if(E()){
                Return E(E());
            }
            Return E('更新失败');
        }
    }
    function adminconfig() {
        if(!$class=C('cms:class:get',$_POST['hash'])) {
            return E('error');
        }
        $array=array();
        $array['requires']='';
        $array['msg']='';
        $array['error']=0;
        if(isset($class['requires']) && !empty($class['requires'])) {
            $requires=explode(';',$class['requires']);
            foreach($requires as $require) {
                @preg_match_all('/\[.*?\]/',$require,$requireversions);
                if(isset($requireversions[0][0])){
                    $requireclasshash=rtrim($require,$requireversions[0][0]);
                }else{
                    $requireclasshash=$require;
                }
                $thisclass=C('cms:class:get',$requireclasshash);
                if($thisclass) {
                    $versioncheck=true;
                    if(isset($requireversions[0][0])){
                        $thisversions=explode(',',rtrim(ltrim($requireversions[0][0],'['),']'));
                        foreach ($thisversions as $thisversion) {
                            if(!empty($thisversion)){
                                if(substr($thisversion,0,2)=='<='){
                                    if(!version_compare($thisclass['classversion'],substr($thisversion,2),'<=')){
                                        $versioncheck=false;
                                    }
                                }elseif(substr($thisversion,0,2)=='>='){
                                    if(!version_compare($thisclass['classversion'],substr($thisversion,2),'>=')){
                                        $versioncheck=false;
                                    }
                                }elseif(substr($thisversion,0,1)=='<'){
                                    if(!version_compare($thisclass['classversion'],substr($thisversion,1),'<')){
                                        $versioncheck=false;
                                    }
                                }elseif(substr($thisversion,0,1)=='>'){
                                    if(!version_compare($thisclass['classversion'],substr($thisversion,1),'>')){
                                        $versioncheck=false;
                                    }
                                }elseif(substr($thisversion,0,1)=='='){
                                    if(!version_compare($thisclass['classversion'],substr($thisversion,1),'=')){
                                        $versioncheck=false;
                                    }
                                }elseif(!version_compare($thisclass['classversion'],$thisversion,'=')){
                                    $versioncheck=false;
                                }
                            }
                        }
                    }
                    if($thisclass['enabled'] && $versioncheck){
                        $array['requires'].='<a class="layui-btn layui-btn-xs layui-btn-normal" data-state="1" data-hash="'.$requireclasshash.'"><i class="layui-icon layui-icon-ok"></i>'.$require.'</a> ';
                    }elseif(!$versioncheck){
                        $array['requires'].='<a class="layui-btn layui-btn-xs layui-btn-primary" data-state="3" data-hash="'.$requireclasshash.'"><i class="layui-icon layui-icon-ok"></i>'.$require.' [不兼容]</a> ';
                    }elseif(!$thisclass['installed']){
                        $array['requires'].='<a class="layui-btn layui-btn-xs layui-btn-primary" data-state="2" data-hash="'.$requireclasshash.'"><i class="layui-icon layui-icon-close"></i>'.$require.' [未安装]</a> ';
                    }elseif(!$thisclass['enabled']){
                        $array['requires'].='<a class="layui-btn layui-btn-xs layui-btn-primary" data-state="2" data-hash="'.$requireclasshash.'"><i class="layui-icon layui-icon-close"></i>'.$require.' [未启用]</a> ';
                    }
                }else {
                    $array['requires'].='<a class="layui-btn layui-btn-xs layui-btn-primary" data-state="4" data-hash="'.$requireclasshash.'"><i class="layui-icon layui-icon-close"></i>'.$require.' [未下载]</a> ';
                }
            }
        }
        Return $array;
    }
    function shopInfo($data=array()) {
        foreach($_GET as $key=>$val) {$data[$key]=$val;}
        foreach($_POST as $key=>$val) {$data[$key]=$val;}
        $class_configs=all(array('table'=>'config','limit'=>100,'where'=>array('classhash'=>__CLASS__)));
        if(is_array($class_configs) && count($class_configs)) {
            foreach($class_configs as $key=>$class_config) {if($key<100) {$data['_'.$class_config['hash']]=$class_config['value'];}}
        }
        $data['_domain']=@server_name();$data['_hash']=@$GLOBALS['C']['SiteHash'];$data['_ip']=C('cms:common:ip');$data['_uid']=C('admin:nowuser');$data['_referer']=@$_SERVER['HTTP_REFERER'];$data['_ua']=@$_SERVER['HTTP_USER_AGENT'];$data['_php']=@PHP_VERSION;$data['_os']=@php_uname('s');$data['_time']=time();if($aff=@file_get_contents(classDir(I()).'aff.txt')){$data['_aff']=$aff;}
        if($classes=C('cms:class:all')) {
            $data['_classes']='';
            foreach($classes as $key=>$class) {if($key<300) {$data['_classes'].=$class['hash'].','.$class['classversion'].','.$class['enabled'].'|';}}
        }
        if (function_exists("curl_init")){$data['_curl']=1;}else{$data['_curl']=0;}
        Return $data;
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
    function get() {
        $host=C('this:shopHost');
        $url='http://'.$host.'/shop/';
        if (function_exists("curl_init")){
            $content=C('cms:common:send',$url,C('this:shopInfo'),1,120,array('CURLOPT_CONNECTTIMEOUT'=>10,'CURLOPT_SSL_VERIFYPEER'=>FALSE,'CURLOPT_SSL_VERIFYHOST'=>FALSE,'CURLOPT_HTTP_VERSION'=>CURL_HTTP_VERSION_1_0,'CURLOPT_RETURNTRANSFER'=>1));
        }else{
            $options['http'] = array('timeout'=>120,'method' => 'POST','header' => 'Content-type:application/x-www-form-urlencoded','content' =>http_build_query(C('this:shopInfo')));
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
                    if($htmlconfig[1][$key]=='bread'){
                        $breads=explode(';',$htmlconfig[2][$key]);
                        foreach ($breads as $bread) {
                            if(!empty($bread)){
                                $thisbreads=explode('|',$bread);
                                if(isset($thisbreads[1])){
                                    $GLOBALS['shop']['bread'][]=array('title'=>$thisbreads[0],'url'=>$thisbreads[1]);
                                }else{
                                    $GLOBALS['shop']['bread'][]=array('title'=>$thisbreads[0]);
                                }
                            }
                        }
                    }else{
                        config($htmlconfig[1][$key],$htmlconfig[2][$key]);
                    }
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
        if(stripos($url,'@')){Return false;}
        $checkurl=parse_url($url);
        if(!isset($checkurl['host']) || !in_array($checkurl['host'],$hosts)) {
            Return false;
        }
        if(isset($checkurl['scheme'])){
            $checkurl['scheme']=strtolower($checkurl['scheme']);
            if($checkurl['scheme']!='http' && $checkurl['scheme']!='https'){
                Return false;
            }
        }
        Return C('cms:common:download',$url,$filepath,300,array('CURLOPT_CONNECTTIMEOUT'=>10,'CURLOPT_SSL_VERIFYPEER'=>FALSE,'CURLOPT_SSL_VERIFYHOST'=>FALSE,'CURLOPT_HTTP_VERSION'=>CURL_HTTP_VERSION_1_0,'CURLOPT_POST'=>1,'CURLOPT_POSTFIELDS'=>C('this:shopInfo')));
    }
}