<?php
class cms {
    function init(){
        if(!isset($GLOBALS['C']['DbInfo'])) {Return array('template_class' =>'admin');}
    }
    function stop() {
        Return E('无法停用');
    }
    function uninstall() {
        Return E('无法卸载');
    }
    function initRoute($routekey) {
        $inited=false;
        $thisroute=$GLOBALS['route'][$routekey];
        if(!empty($thisroute['classfunction'])) {
            $thisroute['classfunction']=$thisroute['classhash'].':'.$thisroute['classfunction'];
        }else {
            $thisroute['classfunction']=$thisroute['classhash'];
        }
        if(isset($thisroute['modulehash']) && !empty($thisroute['modulehash'])) {
            $where=array('enabled'=>1,'modulehash'=>$thisroute['modulehash'],'classhash'=>$thisroute['classhash']);
            if(isset($GLOBALS['C']['GET']['$.id'])) {
                $where['id']=$GLOBALS['C']['GET']['$.id'];
            }
            if(isset($GLOBALS['C']['GET']['$.channelname'])) {
                $where['channelname']=$GLOBALS['C']['GET']['$.channelname'];
            }
            $channels=all(array('table'=>'channel','order'=>'channelorder asc,id asc','where'=>$where));
            foreach($channels as $channel) {
                $matched=true;
                $article=false;
                $article_where=array();
                unset($GLOBALS['C']['article']);
                unset($GLOBALS['C']['channel']);
                if(!$channel=C('this:channel:get',$channel['id'])) {
                    $matched=false;
                }
                if($matched && isset($channel['domain']) && !empty($channel['domain'])) {
                    $matched=macthDomain($channel['domain']);
                }
                if($matched) {
                    if(isset($GLOBALS['C']['GET'])) {
                        foreach($GLOBALS['C']['GET'] as $key=>$this_get) {
                            if(substr($key,0,2)=='$.') {
                                if($GLOBALS['C']['MatchUri']===1){
                                    $this_get=strtolower($this_get);
                                }
                                if($GLOBALS['C']['MatchUri']===1 && isset($channel[substr($key,2)])){
                                    $channel[substr($key,2)]=strtolower($channel[substr($key,2)]);
                                }
                                if(!isset($channel[substr($key,2)]) || $this_get!=$channel[substr($key,2)]) {
                                    $matched=false;
                                    break;
                                }
                            }elseif(substr($key,0,1)=='$') {
                                $article_where[substr($key,1)]=$this_get;
                            }
                        }
                    }
                }
                if($matched) {
                    $GLOBALS['C']['routekey']=$routekey;
                    $channel=C('this:nowChannel',$thisroute['classhash'],$channel);
                    $GLOBALS['C']['channel']=$channel;
                    if(count($article_where)){
                        if($article=C('this:article:getOne',array('cid'=>$channel['id'],'where'=>$article_where,'source'=>'route'))) {
                            $article=C('this:nowArticle',$GLOBALS['C']['channel'],$article);
                            $GLOBALS['C']['article']=$article;
                        }else{
                            unset($GLOBALS['C']['routekey']);
                            $matched=false;
                        }
                    }
                }
                if($matched) {
                    if(isset($GLOBALS['C']['GET'])) {
                        foreach($GLOBALS['C']['GET'] as $key=>$val) {
                            $_GET[$key]=$val;
                        }
                    }
                    if(isset($thisroute['classview']) && !empty($thisroute['classview'])) {
                        preg_match_all('/[(](.*)[)]/U',$thisroute['classview'],$classviewargs);
                        foreach ($classviewargs[1] as $key => $classviewarg) {
                            if(substr($classviewarg,0,2)=='$.') {
                                if(isset($channel[substr($classviewarg,2)])){
                                    $thisroute['classview']=str_replace($classviewargs[0][$key],$channel[substr($classviewarg,2)],$thisroute['classview']);
                                }
                            }elseif(substr($classviewarg,0,1)=='$') {
                                if(isset($article[substr($classviewarg,1)])){
                                    $thisroute['classview']=str_replace($classviewargs[0][$key],$article[substr($classviewarg,1)],$thisroute['classview']);
                                }
                            }
                        }
                        $GLOBALS['C']['route_view'][$thisroute['classfunction']]=$thisroute['classview'];
                        if(count($article_where)) {
                            $GLOBALS['C']['route_view_article'][$thisroute['classfunction']]=$article;
                        }
                        $inited=true;
                    }
                    $thisroute=C('this:nowRoute',$thisroute);
                    if(count($article_where)){
                        if($inited) {
                            C($thisroute['classfunction'],$channel,$article);
                        }else {
                            $inited=C($thisroute['classfunction'],$channel,$article);
                        }
                    }else{
                        if($inited) {
                            C($thisroute['classfunction'],$channel);
                        }else {
                            $inited=C($thisroute['classfunction'],$channel);
                        }
                    }
                    if(isset($GLOBALS['C']['GET'])) {
                        foreach($GLOBALS['C']['GET'] as $key=>$val) {
                            unset($_GET[$key]);
                        }
                    }
                    if($inited) {
                        Return true;
                    }
                }
            }
            Return false;
        }else {
            $GLOBALS['C']['routekey']=$routekey;
            if(isset($thisroute['classview']) && !empty($thisroute['classview'])) {
                $GLOBALS['C']['route_view'][$thisroute['classfunction']]=$thisroute['classview'];
                $inited=true;
            }
            if(isset($GLOBALS['C']['GET'])) {
                foreach($GLOBALS['C']['GET'] as $key=>$val) {
                    $_GET[$key]=$val;
                }
            }
            $thisroute=C('this:nowRoute',$thisroute);
            if($inited) {
                C($thisroute['classfunction']);
            }else {
                $inited=C($thisroute['classfunction']);
            }
            if(isset($GLOBALS['C']['GET'])) {
                foreach($GLOBALS['C']['GET'] as $key=>$val) {
                    unset($_GET[$key]);
                }
            }
            Return $inited;
        }
    }
    function nowRoute($route){
        return $route;
    }
    function nowChannel($classhash,$channel) {
        Return $channel;
    }
    function nowArticle($channel,$article) {
        Return $article;
    }
    function nowView($_file,$_vars=array(),$_classhash='') {
        if(empty($_classhash)) {$_classhash=i(-1);}
        $GLOBALS['C']['running_class'][]=$_classhash;
        if(!isset($GLOBALS['class_template'][$_classhash])) {C($_classhash);}
        $C_template_config=$GLOBALS['class_template'][$_classhash];
        if(isset($GLOBALS['class_config'][$_classhash]['template_class']) && $GLOBALS['class_config'][$_classhash]['template_class']!=$_classhash) {
            $GLOBALS['C']['running_class'][]=$GLOBALS['class_config'][$_classhash]['template_class'];
        }
        if(is_array($_vars)) {
            foreach($_vars as $_Temp_key=>$_Temp_val) {
                if(!is_int($_Temp_key)) {
                    $$_Temp_key=$_Temp_val;
                }
            }
        }
        if(stripos($_file,'}')===false && stripos($_file,'?')===false && stripos($_file,'>')===false) {
            $C_template_config['file']=$_file;
            $C_template_config['rootpath']=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['ClassDir'].DIRECTORY_SEPARATOR.$C_template_config['class'].DIRECTORY_SEPARATOR.$C_template_config['dir'];
            $U_tempfile=include_template($C_template_config);
            if($U_tempfile) {include($U_tempfile);}
        }else {
            $C_template_config['rootpath']=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['ClassDir'].DIRECTORY_SEPARATOR.$C_template_config['class'].DIRECTORY_SEPARATOR.$C_template_config['dir'];
            $C_template_config['code']=$_file;
            $U_tempfile=include_template($C_template_config);
            if($U_tempfile) {include($U_tempfile);}
        }
        if(isset($GLOBALS['class_config'][$_classhash]['template_class']) && $GLOBALS['class_config'][$_classhash]['template_class']!=$_classhash) {
            array_pop($GLOBALS['C']['running_class']);
        }
        array_pop($GLOBALS['C']['running_class']);
        Return true;
    }
    function nowUri() {
        if(isset($GLOBALS['C']['uri'])) {
            Return $GLOBALS['C']['uri'];
        }
        $noarguri=explode('?',$_SERVER['REQUEST_URI']);
        $uri='/'.ltrim($noarguri[0],'/');
        $uri=substr($uri,strlen($GLOBALS['C']['SystemDir'])-1);
        if(isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'],'iis')) {
            $uri=uridecode(urlencode(iconv("gbk","utf-8//IGNORE",$uri)));
        }
        if(!$GLOBALS['C']['UrlRewrite']) {
            if(empty($uri) || $uri=='/' || $uri=='/'.$GLOBALS['C']['Indexfile']) {$uri='/'.$GLOBALS['C']['Indexfile'].'/';}
            $uri=substr($uri,strlen('/'.$GLOBALS['C']['Indexfile']));
        }
        Return $uri;
    }
    function notFound() {
        @header("HTTP/1.1 404 Not Found");
        echo('<html><head><title>404 Not Found</title></head><body><center><h1>404 Not Found</h1></center><hr><center><a href="http://classcms.com" style="color:#000;text-decoration:none" target="_blank">ClassCMS</a></center></body></html>');
        Return true;
    }
    function ob_content($content) {
        Return $content;
    }
    function error($msg) {
        if($GLOBALS['C']['Debug']) {echo($msg);}
    }
    function homepage($class='') {
        if(empty($class)) {
            return $GLOBALS['C']['SystemDir'];
        }
        if(isset($GLOBALS['C']['homepage'][$class])) {
            Return $GLOBALS['C']['homepage'][$class];
        }
        if(!isset($GLOBALS['C']['DbInfo']) || !is_array($GLOBALS['C']['DbInfo'])) {Return '';}
        if($home=C('cms:channel:home',$class)) {
            $GLOBALS['C']['homepage'][$class]=$home['link'];
        }else {
            $GLOBALS['C']['homepage'][$class]='';
        }
        Return $GLOBALS['C']['homepage'][$class];
    }
    function upgrade($old_version) {
        if(version_compare($old_version,'2.2','<')) {
            C($GLOBALS['C']['DbClass'].':addField','hook','requires','varchar(255)');
        }
        if(version_compare($old_version,'4.6','<')) {
            C($GLOBALS['C']['DbClass'].':addField','user','last_update_time','bigint(11)');
            update('table','user','last_update_time',0);
        }
    }
}
function ClassCms_init() {
    if(!defined('ClassCms')){define('ClassCms',1);}
    if(!isset($GLOBALS['C']['UrlRewrite'])) {$GLOBALS['C']['UrlRewrite']=1;}
    if(!isset($GLOBALS['C']['SiteHash'])) {$GLOBALS['C']['SiteHash']=md5(dirname(__FILE__));}
    if(!isset($GLOBALS['C']['ClassDir'])) {$GLOBALS['C']['ClassDir']='class';}
    if(!isset($GLOBALS['C']['UploadDir'])) {$GLOBALS['C']['UploadDir']='upload';}
    if(!isset($GLOBALS['C']['CacheDir'])) {$GLOBALS['C']['CacheDir']='cache';}
    if(!isset($GLOBALS['C']['Debug'])) {$GLOBALS['C']['Debug']=false;}
    if(!isset($GLOBALS['C']['DbClass'])) {$GLOBALS['C']['DbClass']='cms:database';}
    if(!isset($GLOBALS['C']['MatchUri'])) {$GLOBALS['C']['MatchUri']=1;}
    if(!isset($GLOBALS['C']['LoadHooks'])) {$GLOBALS['C']['LoadHooks']=true;}
    if(!isset($GLOBALS['C']['LoadRoutes'])) {$GLOBALS['C']['LoadRoutes']=true;}
    if(!isset($GLOBALS['C']['Domain'])) {$GLOBALS['C']['Domain']='';}
    $GLOBALS['C']['start_time']=microtime(true);
    $GLOBALS['C']['start_memory']=round(memory_get_usage()/1024/1024, 2).'MB';
    if($GLOBALS['C']['Debug']) {ini_set('display_errors','On');error_reporting(E_ALL);}else {ini_set('display_errors','Off');}
    $GLOBALS['C']['DocumentRoot']=rtrim(rtrim(@$_SERVER['DOCUMENT_ROOT'],'/'),'\\').DIRECTORY_SEPARATOR;
    $GLOBALS['C']['SystemRoot']=dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;
    $ScriptInfo=(pathinfo(@$_SERVER['SCRIPT_NAME']));
    if($ScriptInfo['dirname']==="\\" || $ScriptInfo['dirname']==='/') {$ScriptInfo['dirname']='';}
    $GLOBALS['C']['SystemDir']=$ScriptInfo['dirname'].'/';
    $GLOBALS['C']['Indexfile']=$ScriptInfo['basename'];
    if(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['argv'])) {cli_parse();}
    _stripslashes();
    if(isset($GLOBALS['C']['DbInfo']) && is_array($GLOBALS['C']['DbInfo'])) {
        if($GLOBALS['C']['LoadHooks']) {
            $hooks=all(array('table'=>'hook','order'=>'hookorder desc,classorder desc,id asc','where'=>array('enabled'=>1,'classenabled'=>1)));
            foreach($hooks as $hook) {
                $hook['hookedfunction']=strtolower($hook['hookedfunction']);
                if(!empty($hook['hookedfunction']) && !isset($GLOBALS['hook'][$hook['hookedfunction']][$hook['classhash'].':'.$hook['hookname']])) {
                    $GLOBALS['hook'][$hook['hookedfunction']][$hook['classhash'].':'.$hook['hookname']]=@$hook['requires'];
                }
            }
        }
        if(!isset($GLOBALS['route'])) {$GLOBALS['route']=array();}
        if(isset($GLOBALS['C']['AdminDir'])) {
            $GLOBALS['route'][]=array('uri'=>'/'.$GLOBALS['C']['AdminDir'].'/','classhash'=>'admin','hash'=>'adminpath','modulehash'=>'','classfunction'=>'load');
            $GLOBALS['route'][]=array('uri'=>'/'.$GLOBALS['C']['AdminDir'],'classhash'=>'admin','hash'=>'adminpath2','modulehash'=>'','classfunction'=>'load');
        }
        if($GLOBALS['C']['LoadRoutes']) {
            $routes=all(array('table'=>'route','order'=>'classorder desc,moduleorder desc,routeorder desc,id asc','where'=>array('enabled'=>1,'classenabled'=>1,'moduleenabled'=>1)));
            if(is_array($routes)) {$GLOBALS['route']=array_merge($GLOBALS['route'],$routes);}
        }
    }else {
        if(isset($_SERVER['argv'])){
            $args=C('cms:common:parseArgv');
            if(isset($args['action']) && $args['action']=='install'){
                return C('cms:install:cli');
            }
        }
        $GLOBALS['route'][]=array('uri'=>'/','classhash'=>'cms','classfunction'=>'install:startup');
        $GLOBALS['route'][]=array('uri'=>'/'.$GLOBALS['C']['Indexfile'],'classhash'=>'cms','classfunction'=>'install:startup');
        $GLOBALS['route'][]=array('uri'=>'/class_cms_rewrite_test.html','classhash'=>'cms','classfunction'=>'install:rewrite');
        $GLOBALS['route'][]=array('uri'=>'/(classcms)','classhash'=>'cms','classfunction'=>'install:goInstall');
        $GLOBALS['route'][]=array('uri'=>'/(classcms)/','classhash'=>'cms','classfunction'=>'install:goInstall');
    }
    $GLOBALS['C']['uri']=C('cms:nowUri');
    C('cms:load:~');
    if($GLOBALS['C']['MatchUri']) {
        if(isset($GLOBALS['hook']['cms:ob_content'])) {ob_start('ob_content');}
        foreach($GLOBALS['route'] as $routekey=>$route) {
            $ifmatch=true;
            if(isset($route['domain']) && !macthDomain($route['domain'])) {$ifmatch=false;}
            if($ifmatch==false || (isset($route['uri']) && matchUri($route['uri'])===false)) {
            }else {
                if(C('cms:initRoute',$routekey)!==false) {
                    $GLOBALS['C']['route_matched']=$route;
                    break;
                }
            }
        }
        if(!isset($GLOBALS['C']['route_matched'])) {C('cms:notFound');}
    }
    C('cms:finish:~');
    if(isset($GLOBALS['C']['DbInfo']) && isset($GLOBALS['C']['console']) && $GLOBALS['C']['console'] && !C('cms:common:isAjax')) {
        $end_time=microtime(true);
        $total_time=substr($end_time-$GLOBALS['C']['start_time'],0,5);
        $GLOBALS['C']['runtime']=$total_time;
        $GLOBALS['C']['run_memory']=round(memory_get_usage()/1024/1024, 2).' MB';
        print_r($GLOBALS['C']);
    }
    if(isset($GLOBALS['hook']['cms:ob_content'])) {ob_end_flush();}
}
function A($config=array()){
    $args=func_get_args();
    if(count($args)>1) {
        $config=array();
        foreach($args as $key=>$val) {
            if($key%2==0 && isset($args[$key+1])) {
                $config[$val]=$args[$key+1];
            }
        }
    }else {
        if(!is_array($config)) {Return array();}
    }
    Return C('cms:article:get',$config);
}
function C() {
    $args=func_get_args();
    if(!isset($args[0])) {
        Return false;
    }
    $class=$args[0];
    $explode_class=explode(':',$class);
    $classhash=$explode_class[0];
    if($classhash=='this') {
        $classhash=I();
        $explode_class[0]=$classhash;
        $class=$classhash.substr($class,4);
    }
    if(isset($GLOBALS['clog']) && $GLOBALS['clog']) {$GLOBALS['C']['clog'][]=array($class,$args);}
    if(!is_hash($classhash)) {Return false;}
    $end_class=end($explode_class);
    if(empty($end_class)) {
        $class=rtrim($class,':');
        array_pop($explode_class);
    }
    if($end_class==='-') {
        $nohook=true;
        array_pop($explode_class);
        $class=rtrim($class,':-');
    }
    if($end_class==='~') {
        array_pop($explode_class);
        $class=rtrim($class,':~');
    }
    if(isset($GLOBALS['C']['route_view'][$class])) {
        $route_view=$GLOBALS['C']['route_view'][$class];
        if(isset($GLOBALS['C']['route_view_article'][$class])) {
            $route_view_article=$GLOBALS['C']['route_view_article'][$class];
            unset($GLOBALS['C']['route_view_article'][$class]);
        }
        unset($GLOBALS['C']['route_view'][$class]);
    }
    if(count($explode_class)===3) {
        if(!is_hash($explode_class[1]) && $end_class!=='~') {Return false;}
        $classname=$classhash.'_'.$explode_class[1];
        $classfile=$explode_class[1];
        $classfunction=$explode_class[2];
    }elseif(count($explode_class)===2) {
        $classname=$explode_class[0];
        $classfile=$explode_class[0];
        $classfunction=$explode_class[1];
    }elseif(count($explode_class)===1) {
        $classname=$explode_class[0];
        $classfile=$explode_class[0];
        $classfunction='';
    }else {
        Return false;
    }
    unset($GLOBALS['C']['c_lasterror']);
    if(isset($GLOBALS['C']['c_error'])){ array_pop($GLOBALS['C']['c_error']); }
    if($end_class!=='-' && isset($GLOBALS['hook'][strtolower($class)]) && count($GLOBALS['hook'][strtolower($class)])) {
        foreach($GLOBALS['hook'][strtolower($class)] as $hookclass=>$hookrequires) {
            $args[0]=$hookclass;
            if(hook_requires($args,$hookrequires)){
                $return=call_user_func_array('C', $args);
                if(is_array($return) && isset($return[0]) && is_string($return[0]) && strtolower($return[0])==strtolower($class)) {
                    $args=$return;
                }elseif(is_array($return) && isset($return['class']) && is_string($return['class']) && strtolower($return['class'])==strtolower($class)) {
                    $args=class_getParameters($return);
                }elseif($return!==null) {
                    if(isset($GLOBALS['hook'][strtolower($class).':=']) && count($GLOBALS['hook'][strtolower($class).':='])) {
                        foreach($GLOBALS['hook'][strtolower($class).':='] as $watchclass=>$hookrequires) {
                            $watchargs=$args;
                            unset($watchargs[0]);
                            if(hook_requires(array($watchclass,$class,array_values($watchargs),$return),$hookrequires)){
                                $GLOBALS['C']['c_error'][]=null;
                                $watchreturn=call_user_func_array('C',array($watchclass,$class,array_values($watchargs),$return));
                                if($watchreturn!==null) {
                                    $return=$watchreturn;
                                    $GLOBALS['C']['c_error'][count($GLOBALS['C']['c_error'])-2]=end($GLOBALS['C']['c_error']);
                                }
                                array_pop($GLOBALS['C']['c_error']);
                            }
                        }
                    }
                    if(isset($route_view)) {
                        if(isset($route_view_article) && is_array($return)) {
                            V($route_view,array_merge($route_view_article,$return),$classhash);
                        }elseif(isset($route_view_article)) {
                            V($route_view,array_merge($route_view_article),$classhash);
                        }else {
                            V($route_view,$return,$classhash);
                        }
                    }
                    Return $return;
                }
                $lastreturn=$args;
                if($return===null && $end_class==='~') {
                    $lastreturn=false;
                }
            }
        }
    }
    unset($args[0]);
    $return=false;
    $GLOBALS['C']['running_class'][]=$classhash;
    if(!isset($GLOBALS['class_config'][$classhash]) && $end_class!=='~') {
        if(!class_exists($classhash)) {
            include_once(classDir($classhash).$classhash.'.php');
        }
        if(class_exists($classhash)) {
            $GLOBALS['class'][$classhash]=new $classhash();
            if(method_exists($GLOBALS['class'][$classhash],'init')) {
                $GLOBALS['class_config'][$classhash]=$GLOBALS['class'][$classhash]->init();
                if(!is_array($GLOBALS['class_config'][$classhash])) {
                    $GLOBALS['class_config'][$classhash]=array();
                }
            }else {
                $GLOBALS['class_config'][$classhash]=array();
            }
            template_config($classhash);
        }else{
            $return=false;
        }
    }
    if(!isset($GLOBALS['class'][$classname]) && !empty($classfile) && $end_class!=='~') {
        if(!class_exists($classname)) {
            include_once(classDir($classhash).$classfile.'.php');
        }
        if(class_exists($classname)) {
            $GLOBALS['class'][$classname]=new $classname();
        }else{
            $return=false;
        }
    }
    if(!empty($classfunction) && $end_class!=='~') {
        if(!isset($GLOBALS['class'][$classname])) {
            $return=false;
        }else {
            if($classfunction[0]==='$') {
                $varname=substr($classfunction,1);
                if(isset($GLOBALS['class'][$classname]->$varname)) {
                    $return=$GLOBALS['class'][$classname]->$varname;
                }else {
                    $return=null;
                }
            }else {
                if(method_exists($GLOBALS['class'][$classname],$classfunction)) {
                    $return=call_user_func_array(array($GLOBALS['class'][$classname],$classfunction),$args);
                    if(isset($GLOBALS['C']['c_lasterror'])){
                        $GLOBALS['C']['c_error'][]=$GLOBALS['C']['c_lasterror'];
                        unset($GLOBALS['C']['c_lasterror']);
                    }else{
                        $GLOBALS['C']['c_error'][]=null;
                    }
                }else {
                    if(!isset($lastreturn)) {
                        $return=null;
                    }elseif(isset($lastreturn) && count($lastreturn)==2) {
                        $return=$lastreturn[1];
                    }else {
                        unset($lastreturn[0]);
                        $return=array_values($lastreturn);
                    }
                }
            }
        }
    }
    if($end_class!=='-' && isset($GLOBALS['hook'][strtolower($class).':=']) && count($GLOBALS['hook'][strtolower($class).':='])) {
        foreach($GLOBALS['hook'][strtolower($class).':='] as $watchclass=>$hookrequires) {
            if(hook_requires(array($watchclass,$class,array_values($args),$return),$hookrequires)){
                $GLOBALS['C']['c_error'][]=null;
                $watchreturn=call_user_func_array('C',array($watchclass,$class,array_values($args),$return));
                if($watchreturn!==null) {
                    $return=$watchreturn;
                    $GLOBALS['C']['c_error'][count($GLOBALS['C']['c_error'])-2]=end($GLOBALS['C']['c_error']);
                }
                array_pop($GLOBALS['C']['c_error']);
            }
        }
    }
    if(isset($route_view)) {
        if(isset($route_view_article) && is_array($return)) {
            V($route_view,array_merge($route_view_article,$return),$classhash);
        }elseif(isset($route_view_article)) {
            V($route_view,array_merge($route_view_article),$classhash);
        }else {
            V($route_view,$return,$classhash);
        }
    }
    array_pop($GLOBALS['C']['running_class']);
    if($end_class==='~' && isset($lastreturn)) {
        Return $lastreturn;
    }
    Return $return;
}
function I($level=0){
    if($level===0){
        Return @end($GLOBALS['C']['running_class']);
    }elseif($level===-1){
        if(!isset($GLOBALS['C']['running_class']) || !is_array($GLOBALS['C']['running_class'])) {
            Return false;
        }
        $running_class_count=count($GLOBALS['C']['running_class']);
        if($running_class_count==1) {
            Return $GLOBALS['C']['running_class'][0];
        }
        foreach($GLOBALS['C']['running_class'] as $key=>$classhash) {
            if($GLOBALS['C']['running_class'][$running_class_count-$key-1]!=$GLOBALS['C']['running_class'][$running_class_count-1]) {
                Return $GLOBALS['C']['running_class'][$running_class_count-$key-1];
            }
        }
        Return $GLOBALS['C']['running_class'][$running_class_count-1];
    }
    Return false;
}
function V($file,$vars=array(),$classhash='') {
    if(empty($classhash)) {$classhash=I();}
    return C('cms:nowView',$file,$vars,$classhash);
}
function P($do,$classhash=false,$userid=false) {
    if(!$classhash) {$classhash=I();}
    if(substr($do,0,strlen($classhash)+1)==$classhash.':'){
        Return C('admin:check',$do,$userid);
    }else{
        Return C('admin:check',$classhash.':'.$do,$userid);
    }
}
function E($msg=null){
    if($msg===null){
        if(!isset($GLOBALS['C']['c_error']) || !end($GLOBALS['C']['c_error'])){return null;}
        return end($GLOBALS['C']['c_error']);
    }elseif($msg===false){
        unset($GLOBALS['C']['c_lasterror']);
    }elseif(is_string($msg)){
        $GLOBALS['C']['c_lasterror']=L($msg);
    }
    return false;
}
function L($txt='',$language=0){
    if(!$classhash=I()){return $txt;}
    if(!isset($GLOBALS['class_config'][$classhash])){return $txt;}
    $config=$GLOBALS['class_config'][$classhash];
    if(!isset($config['languages']) || !is_array($config['languages']) || !count($config['languages'])){return $txt;}
    if(!$language){
        if(isset($GLOBALS['C']['language'])){
            $language=$GLOBALS['C']['language'];
        }elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $acceptLanguages=explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $language=$acceptLanguages[0];
            $GLOBALS['C']['language']=$acceptLanguages[0];
        }else{
            return $txt;
        }
    }
    if(!isset($config['languages'][$language])){return $txt;}
    if(isset($config['languages_dir']) && !empty($config['languages_dir'])){$config['languages_dir'].=DIRECTORY_SEPARATOR; }else{$config['languages_dir']=''; }
    if(!isset($GLOBALS['class_language'][$classhash][$language])) {
        if(stripos($config['languages'][$language],'/')===false && stripos($config['languages'][$language],'\\')===false){
            $languageFile=classDir($classhash).$config['languages_dir'].$language.'.php';
        }else{
            $languageFile=$config['languages'][$language];
        }
        if(!is_file($languageFile)){return $txt;}
        $GLOBALS['class_language'][$classhash][$language]=@require_once($languageFile);
    }
    if(isset($GLOBALS['class_language'][$classhash][$language][$txt])){
        return $GLOBALS['class_language'][$classhash][$language][$txt];
    }
    return $txt;
}
function U($channel,$routehash='',$article=array(),$args=array(),$fullurl=false) {
    Return C('cms:channel:url',$channel,$routehash,$article,$args,$fullurl);
}
function G($hash,$val=null,$classhash=''){
    if(empty($classhash)) {$classhash=I();}
    if($val==null){
        if(isset($GLOBALS['running_data'][$classhash][$hash])){
            return $GLOBALS['running_data'][$classhash][$hash];
        }
        return null;
    }
    $GLOBALS['running_data'][$classhash][$hash]=$val;
    return true;
}
function now_class() {
    Return I();
}
function last_class() {
    Return I(-1);
}
function route($routehash,$args=array(),$classhash='',$fullurl=false) {
    Return C('cms:route:url',$routehash,$args,$classhash,$fullurl);
}
function config($hash,$value=false,$classhash='') {
    if($value===false) {
        Return C('cms:config:get',$hash,$classhash);
    }else {
        Return C('cms:config:set',$hash,$value,0,$classhash);
    }
}
function nav($cid=0,$size=999999,$classhash=''){
    Return C('cms:channel:nav',$cid,$size,$classhash);
}
function bread($cid=0,$classhash=''){
    Return C('cms:channel:bread',$cid,$classhash);
}
function text($html,$length=0,$ellipsis=''){
    Return C('cms:common:text',$html,$length,$ellipsis);
}
function addlog($msg) {
    Return C('cms:common:addlog',$msg);
}
function cms_createdir($path){
    Return C('cms:common:createDir',$path);
}
function server_name() {
    Return C('cms:common:serverName');
}
function server_port($colon=true) {
    Return C('cms:common:serverPort',$colon);
}
function ob_content($content) {
    Return C('cms:ob_content',$content);
}
function rewriteUri($uri) {
    if(isset($GLOBALS['C']['UrlRewrite']) && $GLOBALS['C']['UrlRewrite']) {
        Return $GLOBALS['C']['SystemDir'].ltrim($uri,'/');
    }else {
        if($uri=='/' && in_array($GLOBALS['C']['Indexfile'],array('index.php','default.php'))) {
            Return $GLOBALS['C']['SystemDir'];
        }elseif($uri=='/') {
            Return $GLOBALS['C']['SystemDir'].$GLOBALS['C']['Indexfile'];
        }else {
            Return $GLOBALS['C']['SystemDir'].$GLOBALS['C']['Indexfile'].'/'.ltrim($uri,'/');
        }
    }
}
function matchUri($uri) {
    unset($GLOBALS['C']['GET']);
    if(substr_count($uri,'/')!=substr_count($GLOBALS['C']['uri'],'/')) {
        Return false;
    }
    $uri=uridecode(urlencode($uri));
    if(strpos($uri,')')===false) {
        if($GLOBALS['C']['MatchUri']===1 && strtolower($uri)==strtolower($GLOBALS['C']['uri'])){
            Return true;
        }elseif($uri==$GLOBALS['C']['uri']) {
            Return true;
        }else {
            Return false;
        }
    }
    preg_match_all('/[(](.*)[)]/U',$uri,$getarray);
    if(count($getarray)>0) {
        $uri=str_replace(array('/','?','($id)','($.id)'),array('\\/','\?','([1-9][0-9]*)','([1-9][0-9]*)'),$uri);
        foreach($getarray[0] as $getkey=>$getval) {
            $uri=str_replace($getval,'($+?)',$uri);
        }
        $uri=str_replace(array('.','($+?)'),array('\.','(.+?)'),$uri);
        if($GLOBALS['C']['MatchUri']===1){$i='i';}else{$i='';}
        @preg_match_all('/class-cms-uri-start-'.$uri.'-class-cms-uri-end/'.$i,'class-cms-uri-start-'.$GLOBALS['C']['uri'].'-class-cms-uri-end',$ifmatch);
        if(isset($ifmatch[1][0])) {
            foreach($getarray[1] as $getkey=>$getval) {
                $GLOBALS['C']['GET'][$getval]=urldecode($ifmatch[1+$getkey][0]);
            }
            Return true;
        }
    }
    Return false;
}
function macthDomain($domains) {
    if(empty($domains)) {Return true;}
    $domains=explode(';',strtolower($domains));
    foreach($domains as $val) {
        $val=trim($val);
        if($val==server_name()) {Return true;}
        if($val=='*') {Return true;}
        $thisstripos=stripos($val,'*');
        if($thisstripos===false) {}else {
            $topdomain=substr($val,$thisstripos+1,strlen($val)-$thisstripos);
            if(substr(server_name(),-strlen($topdomain))==$topdomain) {Return true;}
        }
    }
    Return false;
}
function classDir($classhash='') {
    if($classhash=='this') {
        $classhash=I();
    }
    if(empty($classhash)) {
        Return $GLOBALS['C']['SystemRoot'].$GLOBALS['C']['ClassDir'].DIRECTORY_SEPARATOR;
    }
    Return $GLOBALS['C']['SystemRoot'].$GLOBALS['C']['ClassDir'].DIRECTORY_SEPARATOR.$classhash.DIRECTORY_SEPARATOR;
}
function cacheDir($path='',$create=false) {
    $path=str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$path);
    if(stripos($GLOBALS['C']['CacheDir'],'/')!==false || stripos($GLOBALS['C']['CacheDir'],'\\')!==false) {
        if(empty($path)) {
            $fullpath=rtrim($GLOBALS['C']['CacheDir'],'/\\').DIRECTORY_SEPARATOR;
        }else{
            $fullpath=rtrim($GLOBALS['C']['CacheDir'],'/\\').DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR;
        }
    }else{
        if(empty($path)) {
            $fullpath=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['CacheDir'].DIRECTORY_SEPARATOR;
        }else{
            $fullpath=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['CacheDir'].DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR;
        }
    }
    if($create){
        if(!cms_createdir($fullpath)){
            return false;
        }
    }
    return $fullpath;
}
function class_getParameters($args) {
    $class=explode(':',$args['class']);
    if(count($class)>2) {
        if(!class_exists($class[0].'_'.$class[1])) {
            include_once(classDir($class[0]).$class[1].'.php');
        }
        $classhash=$class[0];
        $classname=$classhash.'_'.$class[1];
        $functionname=$class[2];
    }else {
        if(!class_exists($class[0])) {
            include_once(classDir($class[0]).DIRECTORY_SEPARATOR.$class[0].'.php');
        }
        $classname=$class[0];
        $functionname=$class[1];
    }
    $class_args=array();
    if(method_exists($classname,$functionname)) {
        $class_args[]='';
        $p = new ReflectionMethod($classname, $functionname);
        $Parameters=$p->getParameters();
        foreach($Parameters as $key=>$val) {
            if(isset($args[$val->name])) {
                $class_args[]=$args[$val->name];
            }else {
                $class_args[]='';
            }
        }
    }else {
        foreach($args as $val) {
            $class_args[]=$val;
        }
    }
    Return $class_args;
}
function hook_requires($args,$requires) {
    if(empty($requires)){return true;}
    if(!isset($args[0])){return true;}
    $classfunction=explode(':',$args[0]);
    $classhash=$classfunction[0];
    $requires=explode(';',$requires);
    if(isset($GLOBALS['C']['c_error'])){$old_c_error=$GLOBALS['C']['c_error'];}else{$old_c_error=false;}
    foreach ($requires as $require) {
        $thisrequire=explode('=',$require);
        $kind=substr($thisrequire[0],-1);
        if($kind!='<' && $kind!='>' && $kind!='!'){
            $kind='=';
        }else{
            $thisrequire[0]=rtrim($thisrequire[0],'<>!');
        }
        $names=explode('.',$thisrequire[0]);
        $names[0]=strtolower($names[0]);
        if(isset($thisrequire[1])){$value=$thisrequire[1];}else{$value=null;}
        if($names[0]=='get' && $kind=='='){
            if(!isset($names[1]) || !isset($_GET[$names[1]])){return false;}
            if($value!==null && $_GET[$names[1]]!=$value){return false;}
        }elseif($names[0]=='post' && $kind=='='){
            if(!isset($names[1]) || !isset($_POST[$names[1]])){return false;}
            if($value!==null && $_POST[$names[1]]!=$value){return false;}
        }elseif($names[0]=='p' && $kind=='='){
            if(!isset($names[1])){return false;}
            if($value===null){if(!P($names[1],$classhash)){if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false;}}elseif($value==0){if(P($names[1],$classhash)){if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false;}}else{if(!P($names[1],$classhash)){if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false;}}
        }elseif($names[0]=='config' && $kind=='='){
            if(!isset($names[1])){return false;}
            if($value!==null){if(config($names[1],false,$classhash)!=$value){if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false;}}elseif(!config($names[1],false,$classhash)){if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false;}
        }elseif($names[0]=='globals' && $kind=='='){
            if(count($names)==2){ if($value===null){if(!isset($GLOBALS[$names[1]]) || !$GLOBALS[$names[1]]){return false;}}elseif(!isset($GLOBALS[$names[1]]) || $GLOBALS[$names[1]]!=$value){return false;} }elseif(count($names)==3){ if($value===null){if(!isset($GLOBALS[$names[1]][$names[2]]) || !$GLOBALS[$names[1]][$names[2]]){return false;}}elseif(!isset($GLOBALS[$names[1]][$names[2]]) || $GLOBALS[$names[1]][$names[2]]!=$value){return false;} }elseif(count($names)==4){ if($value===null){if(!isset($GLOBALS[$names[1]][$names[2]][$names[3]]) || !$GLOBALS[$names[1]][$names[2]][$names[3]]){return false;}}elseif(!isset($GLOBALS[$names[1]][$names[2]][$names[3]]) || $GLOBALS[$names[1]][$names[2]][$names[3]]!=$value){return false;} }elseif(count($names)==5){ if($value===null){if(!isset($GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]) || !$GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]){return false;}}elseif(!isset($GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]) || $GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]!=$value){return false;} }
        }elseif($names[0]=='args' && $kind=='='){
            if(count($names)==2){ if($value===null){if(!isset($args[$names[1]]) || !$args[$names[1]]){return false;}}elseif(!isset($args[$names[1]]) || $args[$names[1]]!=$value){return false;} }elseif(count($names)==3){ if($value===null){if(!isset($args[$names[1]][$names[2]]) || !$args[$names[1]][$names[2]]){return false;}}elseif(!isset($args[$names[1]][$names[2]]) || $args[$names[1]][$names[2]]!=$value){return false;} }elseif(count($names)==4){ if($value===null){if(!isset($args[$names[1]][$names[2]][$names[3]]) || !$args[$names[1]][$names[2]][$names[3]]){return false;}}elseif(!isset($args[$names[1]][$names[2]][$names[3]]) || $args[$names[1]][$names[2]][$names[3]]!=$value){return false;} }elseif(count($names)==5){ if($value===null){if(!isset($args[$names[1]][$names[2]][$names[3]][$names[4]]) || !$args[$names[1]][$names[2]][$names[3]][$names[4]]){return false;}}elseif(!isset($args[$names[1]][$names[2]][$names[3]][$names[4]]) || $args[$names[1]][$names[2]][$names[3]][$names[4]]!=$value){return false;} }
        }elseif($names[0]=='!get' || ($names[0]=='get' && $kind=='!')){
            if(isset($_GET[@$names[1]]) && $value==null && $_GET[$names[1]]){return false;}
            if(isset($_GET[@$names[1]]) && $_GET[$names[1]]==$value){return false;}
        }elseif($names[0]=='!post' || ($names[0]=='post' && $kind=='!')){
            if(isset($_POST[@$names[1]]) && $value==null && $_POST[$names[1]]){return false;}
            if(isset($_POST[@$names[1]]) && $_POST[$names[1]]==$value){return false;}
        }elseif($names[0]=='!p' || ($names[0]=='p' && $kind=='!')){
            if(!isset($names[1])){return false;}
            if($value===null){ if(P($names[1],$classhash)){if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false;} }elseif($value==0){ if(!P($names[1],$classhash)){if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false;} }else{ if(P($names[1],$classhash)){if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false;} }
        }elseif($names[0]=='!config' || ($names[0]=='config' && $kind=='!')){
            if(!isset($names[1])){return false;}
            if($value!==null){ if(config($names[1],false,$classhash)==$value){ if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false; } }elseif(config($names[1],false,$classhash)){ if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false; }
        }elseif($names[0]=='!globals' || ($names[0]=='globals' && $kind=='!')){
            if(count($names)==2){ if($value===null){if(isset($GLOBALS[$names[1]]) && $GLOBALS[$names[1]]){return false;}}elseif(isset($GLOBALS[$names[1]]) && $GLOBALS[$names[1]]==$value){return false;} }elseif(count($names)==3){ if($value===null){if(isset($GLOBALS[$names[1]][$names[2]]) && $GLOBALS[$names[1]][$names[2]]){return false;}}elseif(isset($GLOBALS[$names[1]][$names[2]]) && $GLOBALS[$names[1]][$names[2]]==$value){return false;} }elseif(count($names)==4){ if($value===null){if(isset($GLOBALS[$names[1]][$names[2]][$names[3]]) && $GLOBALS[$names[1]][$names[2]][$names[3]]){return false;}}elseif(isset($GLOBALS[$names[1]][$names[2]][$names[3]]) && $GLOBALS[$names[1]][$names[2]][$names[3]]==$value){return false;} }elseif(count($names)==5){ if($value===null){if(isset($GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]) && $GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]){return false;}}elseif(isset($GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]) && $GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]==$value){return false;} }
        }elseif($names[0]=='!args' || ($names[0]=='args' && $kind=='!')){
            if(count($names)==2){ if($value===null){if(isset($args[$names[1]]) && $args[$names[1]]){return false;}}elseif(isset($args[$names[1]]) && $args[$names[1]]==$value){return false;} }elseif(count($names)==3){ if($value===null){if(isset($args[$names[1]][$names[2]]) && $args[$names[1]][$names[2]]){return false;}}elseif(isset($args[$names[1]][$names[2]]) && $args[$names[1]][$names[2]]==$value){return false;} }elseif(count($names)==4){ if($value===null){if(isset($args[$names[1]][$names[2]][$names[3]]) && $args[$names[1]][$names[2]][$names[3]]){return false;}}elseif(isset($args[$names[1]][$names[2]][$names[3]]) && $args[$names[1]][$names[2]][$names[3]]==$value){return false;} }elseif(count($names)==5){ if($value===null){if(isset($args[$names[1]][$names[2]][$names[3]][$names[4]]) && $args[$names[1]][$names[2]][$names[3]][$names[4]]){return false;}}elseif(isset($args[$names[1]][$names[2]][$names[3]][$names[4]]) && $args[$names[1]][$names[2]][$names[3]][$names[4]]==$value){return false;} }
        }elseif($names[0]=='get' && $kind=='>'){
            if(!isset($_GET[@$names[1]])){return false;}
            if($_GET[$names[1]]<$value){return false;}
        }elseif($names[0]=='post' && $kind=='>'){
            if(!isset($_POST[@$names[1]])){return false;}
            if($_POST[$names[1]]<$value){return false;}
        }elseif($names[0]=='config' && $kind=='>'){
            if(!isset($names[1])){return false;}
            if(config($names[1],false,$classhash)<$value){if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false;}
        }elseif($names[0]=='globals' && $kind=='>'){
            if(count($names)==2){ if(!isset($GLOBALS[$names[1]]) || $GLOBALS[$names[1]]<$value){ return false; } }elseif(count($names)==3){ if(!isset($GLOBALS[$names[1]][$names[2]]) || $GLOBALS[$names[1]][$names[2]]<$value){ return false; } }elseif(count($names)==4){ if(!isset($GLOBALS[$names[1]][$names[2]][$names[3]]) || $GLOBALS[$names[1]][$names[2]][$names[3]]<$value){ return false; } }elseif(count($names)==5){ if(!isset($GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]) || $GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]<$value){ return false; } }
        }elseif($names[0]=='args' && $kind=='>'){
            if(count($names)==2){ if(!isset($args[$names[1]]) || $args[$names[1]]<$value){ return false; } }elseif(count($names)==3){ if(!isset($args[$names[1]][$names[2]]) || $args[$names[1]][$names[2]]<$value){ return false; } }elseif(count($names)==4){ if(!isset($args[$names[1]][$names[2]][$names[3]]) || $args[$names[1]][$names[2]][$names[3]]<$value){ return false; } }elseif(count($names)==5){ if(!isset($args[$names[1]][$names[2]][$names[3]][$names[4]]) || $args[$names[1]][$names[2]][$names[3]][$names[4]]<$value){ return false; } }
        }elseif($names[0]=='get' && $kind=='<'){
            if(!isset($_GET[@$names[1]])){return false;}
            if($_GET[$names[1]]>$value){return false;}
        }elseif($names[0]=='post' && $kind=='<'){
            if(!isset($_POST[@$names[1]])){return false;}
            if($_POST[$names[1]]>$value){return false;}
        }elseif($names[0]=='config' && $kind=='<'){
            if(!isset($names[1])){return false;}
            if(config($names[1],false,$classhash)>$value){if($old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}return false;}
        }elseif($names[0]=='globals' && $kind=='<'){
            if(count($names)==2){ if(!isset($GLOBALS[$names[1]]) || $GLOBALS[$names[1]]>$value){ return false; } }elseif(count($names)==3){ if(!isset($GLOBALS[$names[1]][$names[2]]) || $GLOBALS[$names[1]][$names[2]]>$value){ return false; } }elseif(count($names)==4){ if(!isset($GLOBALS[$names[1]][$names[2]][$names[3]]) || $GLOBALS[$names[1]][$names[2]][$names[3]]>$value){ return false; } }elseif(count($names)==5){ if(!isset($GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]) || $GLOBALS[$names[1]][$names[2]][$names[3]][$names[4]]>$value){ return false; } }
        }elseif($names[0]=='args' && $kind=='<'){
            if(count($names)==2){ if(!isset($args[$names[1]]) || $args[$names[1]]>$value){ return false; } }elseif(count($names)==3){ if(!isset($args[$names[1]][$names[2]]) || $args[$names[1]][$names[2]]>$value){ return false; } }elseif(count($names)==4){ if(!isset($args[$names[1]][$names[2]][$names[3]]) || $args[$names[1]][$names[2]][$names[3]]>$value){ return false; } }elseif(count($names)==5){ if(!isset($args[$names[1]][$names[2]][$names[3]][$names[4]]) || $args[$names[1]][$names[2]][$names[3]][$names[4]]>$value){ return false; } }
        }
    }
    if(isset($old_c_error) && $old_c_error){$GLOBALS['C']['c_error']=$old_c_error;}
    return true;
}
function uridecode($uri) {
    Return str_replace(array('%28','%29','%7B','%7D','%5B','%5D','%2F','%3F','%3D','%26','+','%25','%24','%40','%21','%3A'),array('(',')','{','}','[',']','/','?','=','&','%20','%','$','@','!',':'),$uri);
}
function template_url($classhash='') {
    if(empty($classhash)) {
        $classhash=I();
    }
    $template_config=template_config($classhash);
    if(isset($template_config['httpdir'])) {
        Return $template_config['httpdir'];
    }
    Return false;
}
function template_config($classhash) {
    if(isset($GLOBALS['class_template'][$classhash])) {
        Return $GLOBALS['class_template'][$classhash];
    }
    $U_template_config=array();
    $U_template_config['cache']=0;
    if(isset($GLOBALS['class_config'][$classhash]['template_cache'])) {
        $U_template_config['cache']=$GLOBALS['class_config'][$classhash]['template_cache'];
    }
    if(!isset($U_template_config['class'])) {$U_template_config['class']=$classhash;}
    if(!isset($GLOBALS['class_config'][$classhash]['template_dir']) || empty($GLOBALS['class_config'][$classhash]['template_dir'])) {
        $U_template_config['dir']='';
        $U_template_config['httpdir']=$GLOBALS['C']['SystemDir'].$GLOBALS['C']['ClassDir'].'/'.$U_template_config['class'].'/';
    }else {
        $U_template_config['httpdir']=$GLOBALS['C']['SystemDir'].$GLOBALS['C']['ClassDir'].'/'.$U_template_config['class'].'/'.$GLOBALS['class_config'][$classhash]['template_dir'].'/';
        $U_template_config['dir']=$GLOBALS['class_config'][$classhash]['template_dir'].DIRECTORY_SEPARATOR;
    }
    if(isset($GLOBALS['class_config'][$classhash]['template_class']) && !empty($GLOBALS['class_config'][$classhash]['template_class'])) {
        C($GLOBALS['class_config'][$classhash]['template_class']);
        $U_template_config=template_config($GLOBALS['class_config'][$classhash]['template_class']);
    }
    $GLOBALS['class_template'][$classhash]=$U_template_config;
    Return $U_template_config;
}
function include_template($template_config) {
    if(isset($template_config['code'])) {$template_config['file']=md5($template_config['code']);}
    if(strtolower(substr($template_config['file'],-4))!='.php'){ $template_config['file'].='.php'; }
    $template_config['file']=str_replace("\\","/",$template_config['file']);
    if(!isset($template_config['nowpath'])){$template_config['nowpath']=$template_config['rootpath'];}
    $fileExplode=explode('/',$template_config['file']);
    if(count($fileExplode)===1){
        $template_config['filepath']=$template_config['nowpath'].$template_config['file'];
    }elseif(empty($fileExplode[0])){
        $template_config['file']=end($fileExplode);
        array_pop($fileExplode);
        array_shift($fileExplode);
        $template_config['nowpath']=$template_config['rootpath'].implode('/',$fileExplode).'/';
        $template_config['filepath']=$template_config['nowpath'].$template_config['file'];
    }else{
        $parentLevel=substr_count($template_config['file'],'../');
        if($parentLevel){
            $nowpaths=explode('/',str_replace("\\","/",$template_config['nowpath']));
            if(!end($nowpaths)){
                array_pop($nowpaths);
            }
            for ($i=0; $i <$parentLevel; $i++) {
                array_pop($nowpaths);
            }
            $template_config['nowpath']=implode('/',$nowpaths).'/';
            $rootpath=str_replace("\\","/",$template_config['rootpath']);
            if(substr($template_config['nowpath'],0,strlen($rootpath))!=$rootpath){
                Return false;
            }
            $template_config['file']=str_replace('../','',$template_config['file']);
            foreach ($fileExplode as $key => $thisdir) {
                if($thisdir=='..' || $thisdir=='.' || empty($thisdir)){unset($fileExplode[$key]);}
            }
        }
        if(count($fileExplode)>1){
            $template_config['file']=end($fileExplode);
            array_pop($fileExplode);
            $template_config['nowpath']=$template_config['nowpath'].implode('/',$fileExplode).'/';
        }
        $template_config['filepath']=$template_config['nowpath'].$template_config['file'];
    }
    $content=cms_template($template_config);
    if($content===false) {
        $content='file not found: '.str_replace(array($GLOBALS['C']['SystemRoot'],'/','\\'),'/',$template_config['filepath']);
        if(stripos($template_config['filepath'],'.php')===false){$content.='.php ';}
    }
    $cached=save_template($template_config['filepath'],$content,$template_config['cache'],'template'.DIRECTORY_SEPARATOR.$template_config['class']);
    if($cached && $content!==false) {
        Return dir_template($template_config['filepath'],'template'.DIRECTORY_SEPARATOR.$template_config['class']);
    }
    Return false;
}
function save_template($keyname,$value,$overtime=604800,$keykind='') {
    $filename=dir_template($keyname,$keykind);
    if(!is_dir(dirname($filename)) && !cms_createdir(dirname($filename))) {
        error($filename.' permission denied');
        Return false;
    }
    $fp = @fopen($filename,"w");
    if($fp===false) {
        error($filename.' permission denied');
        Return false;
    }
    if(@fwrite($fp,$value)===false){
        @fclose($fp);
        error($filename.' permission denied');
        Return false;
    }
    @fclose($fp);
    Return true;
}
function dir_template($keyname,$keykind='') {
    $md5=md5(server_name().server_port().$keyname.$GLOBALS['C']['SiteHash'].$GLOBALS['C']['UrlRewrite'].$GLOBALS['C']['Indexfile']);
    if(empty($keykind)) {
        $keykind=substr($md5,0,4).DIRECTORY_SEPARATOR;
    }else {
        $keykind.=DIRECTORY_SEPARATOR;
    }
    Return cacheDir().$keykind.substr($md5,8,20).'.html';
}
function echo_replace($str) {
    Return str_replace("'","\\'",$str);
}
function escape_temp_char($str,$encode=0) {
    if($encode) {
        Return str_replace(array('\\\'','\=','\,','\|','\(','\)'),array('---quotes---','---equal---','---comma---','---vertical---','---lbrackets---','---rbrackets---'),$str);
    }else {
        Return str_replace(array('---quotes---','---equal---','---comma---','---vertical---','---lbrackets---','---rbrackets---'),array('\'','=',',','|','(',')'),$str);
    }
}
function cms_template_is_varname($varname) {
    $varnames=explode('[',$varname);
    Return preg_match('/^\$[a-zA-Z_.\x7f-\xff][a-zA-Z0-9._\x7f-\xff]*$/',$varnames[0]);
}
function cms_template_varname($varname,$classhash='') {
    preg_match_all('/(\$[a-zA-Z_.\x7f-\xff][a-zA-Z0-9._\x7f-\xff]*)/',$varname,$namelist);
    if(count($namelist[0])==0) {
        Return $varname;
    }
    if(isset($GLOBALS['C']['channel']['classhash']) && empty($classhash)) {
        $classhash=$GLOBALS['C']['channel']['classhash'];
    }
    foreach($namelist[0] as $thisname) {
        if(substr($thisname,0,2)=='$.') {
            $varname_str='\'\'';
            $channel_vars=explode('.',$thisname);
            if(count($channel_vars)==2) {
                $varname_str='$GLOBALS[\'C\'][\'channel\'][\''.$channel_vars[1].'\']';
            }elseif(count($channel_vars)==3) {
                $var_channel=false;
                if($channel_vars[1]==='0') {
                    $var_channel=C('cms:channel:home',$classhash);
                }else{
                    if(is_numeric($channel_vars[1]) && isset($GLOBALS['channel'][$channel_vars[1]])) {
                        $var_channel=$GLOBALS['channel'][$channel_vars[1]];
                    }else {
                        $var_channel=C('cms:channel:get',$channel_vars[1],$classhash);
                    }
                }
                if($var_channel) {
                    $GLOBALS['channel'][$var_channel['id']]=$var_channel;
                    $varname_str='$GLOBALS[\'channel\']['.$var_channel['id'].'][\''.$channel_vars[2].'\']';
                }
            }elseif(count($channel_vars)==4) {
                $var_channel=false;
                if($channel_vars[2]==='0') {
                    $var_channel=C('cms:channel:home',$channel_vars[1]);
                }else{
                    $var_channel=C('cms:channel:get',$channel_vars[2],$channel_vars[1]);
                }
                if($var_channel) {
                    $GLOBALS['channel'][$var_channel['id']]=$var_channel;
                    $varname_str='$GLOBALS[\'channel\']['.$var_channel['id'].'][\''.$channel_vars[3].'\']';
                }
            }
            $varname=str_replace($thisname,$varname_str,$varname);
        }else {
            $thisname_explode=explode('.',$thisname);
            if(count($thisname_explode)>1) {
                $varname_str='';
                foreach($thisname_explode as $val) {
                    if($varname_str) {
                        if(is_numeric($val)) {
                            $varname_str.='['.$val.']';
                        }else {
                            $varname_str.='[\''.$val.'\']';
                        }
                    }else {
                        $varname_str.=$val;
                    }
                }
                $varname=str_replace($thisname,$varname_str,$varname);
            }
        }
    }
    Return $varname;
}
function cms_template($template_config) {
    if(isset($template_config['code'])) {
        $templatecontent=$template_config['code'];
    }else {
        $templatecontent=@file_get_contents($template_config['filepath']);
    }
    if($templatecontent===false) {Return false;}
    $templatecontent=str_ireplace(array('</head>','</body>','{php}','{/php}'),array('{cms:head:~('.$template_config['class'].')}</head>','{cms:body:~('.$template_config['class'].')}</body>','<?php ','?>'),$templatecontent);
    if(!isset($GLOBALS['C']['template_var']['host'])) {$GLOBALS['C']['template_var']['host']=server_name();}
    if(!isset($GLOBALS['C']['template_var']['cmsdir'])) {$GLOBALS['C']['template_var']['cmsdir']=$GLOBALS['C']['SystemDir'];}
    if(!isset($GLOBALS['C']['template_var']['br'])) {$GLOBALS['C']['template_var']['br']="\n";}
    $GLOBALS['C']['template_var']['this']=$template_config['class'];
    $GLOBALS['C']['template_var']['template']=$template_config['httpdir'];
    $GLOBALS['C']['system_syntax'][1][]='else';
    $GLOBALS['C']['system_syntax'][2][]='}else{';
    $GLOBALS['C']['system_syntax'][1][]='/if';
    $GLOBALS['C']['system_syntax'][2][]='}';
    $GLOBALS['C']['system_syntax'][1][]='/loop';
    $GLOBALS['C']['system_syntax'][2][]='}';
    $nofunction_array=array('if','return','new','switch','function','try');
    preg_match_all('/{[^ {}][^{}]*}/U',$templatecontent,$templist);
    foreach($templist[0] as $key=>$thistemp) {
        $templist[2][$key]=0;
        $thistemp=ltrim(rtrim($thistemp,'}'),'{');
        foreach($GLOBALS['C']['template_var'] as $template_var_key=>$thisval) {
            if($thistemp==$template_var_key) {
                $templist[2][$key]=1;
                $templist[1][$key]='echo(\''.echo_replace($thisval).'\');';
            }
        }
        foreach($GLOBALS['C']['system_syntax'][1] as $system_key=>$thisval) {
            if(!$templist[2][$key] && $thistemp==$thisval) {
                $templist[2][$key]=1;
                $templist[1][$key]=$GLOBALS['C']['system_syntax'][2][$system_key];
            }
        }
        if(!$templist[2][$key] && substr($thistemp,0,2)=='//') {
            $thisothertemp=substr($thistemp,2);
            $templist[2][$key]=1;
            $templist[1][$key]='';
        }
        if(!$templist[2][$key] && substr($thistemp,0,2)=='$.') {
            $thistemp=cms_template_varname($thistemp,$template_config['class']);
        }
        if(!$templist[2][$key] && substr($thistemp,0,3)=='if ') {
            $thisothertemp=cms_template_varname(substr($thistemp,3),$template_config['class']);
            $templist[2][$key]=1;
            $templist[1][$key]='if('.$thisothertemp.'){';
        }
        if(!$templist[2][$key] && substr($thistemp,0,7)=='elseif ') {
            $thisothertemp=cms_template_varname(substr($thistemp,7),$template_config['class']);
            $templist[2][$key]=1;
            $templist[1][$key]='}elseif('.$thisothertemp.'){';
        }
        if(!$templist[2][$key] && substr($thistemp,0,5)=='loop ') {
            $thisothertemp=cms_template_varname(substr($thistemp,5),$template_config['class']);
            $templist[2][$key]=1;
            $templist[1][$key]='foreach('.$thisothertemp.'){';
        }
        if(!$templist[2][$key] && substr($thistemp,0,9)=='template ') {
            $templist[2][$key]=1;
            $templist[1][$key]='echo(\''.template_url(substr($thistemp,9)).'\');';
        }
        if(!$templist[2][$key] && substr($thistemp,0,5)=='file ') {
            $thisothertemp=substr($thistemp,5);
            $templist[2][$key]=1;
            $template_config_new=$template_config;
            unset($template_config_new['filepath']);
            if(substr($thisothertemp,0,1)=='$') {
                $thisothertemp=cms_template_varname($thisothertemp,$template_config['class']);
                foreach($template_config_new as $template_config_new_key=>$template_config_new_val) {
                    $template_config_new[$template_config_new_key]=str_replace('\\','\\\\',$template_config_new_val);
                }
                $templist[1][$key]="include(include_template(array('file'=>{$thisothertemp},'cache'=>\"{$template_config_new['cache']}\",'nowpath'=>\"{$template_config_new['nowpath']}\",'class'=>\"{$template_config_new['class']}\",'dir'=>\"{$template_config_new['dir']}\",'httpdir'=>\"{$template_config_new['httpdir']}\",'rootpath'=>\"{$template_config_new['rootpath']}\")));";
            }else {
                $template_config_new['file']=$thisothertemp;
                $templist[1][$key]='include(\''.include_template($template_config_new).'\');';
            }
        }
        if(!$templist[2][$key]) {
            $thistemp=escape_temp_char($thistemp,1);
            $assignment=explode('=',$thistemp);
            if(count($assignment)>2) {
                $templist[2][$key]=0;
            }elseif(count($assignment)==2) {
                $templist[2][$key]=1;
                $charname=cms_template_varname($assignment[0],$template_config['class']);
                $thistemp=$assignment[1];
                if(!cms_template_is_varname($assignment[0])) {
                    $templist[2][$key]=0;
                    unset($charname);
                }
            }else {
                $templist[2][$key]=1;
                $charname=false;
            }
            if($templist[2][$key]) {
                $this_templist_val='';
                $this_templist_newval='';
                $everyfunction=explode('|',$thistemp);
                foreach($everyfunction as $function_key=>$thisfunction) {
                    $thisfunction=trim($thisfunction);
                    preg_match_all('/\((.*)\)/U',$thisfunction,$iffunction);
                    if(isset($iffunction[1][0])) {
                        $iffunction[1][0]=trim($iffunction[1][0]);
                        $this_function_temp=explode(')',$thisfunction);
                        if(!empty($this_function_temp[1])) {
                            $templist[2][$key]=0;
                            break;
                        }
                        if(stripos($iffunction[1][0],'(')===false) {}else {
                            $templist[2][$key]=0;
                            break;
                        }
                        $this_function_temp=explode('(',$thisfunction);
                        if(stripos($this_function_temp[0],':')===false) {$load_class=0;}else {$load_class=1;}
                        if($function_key==0) {
                            if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/',$this_function_temp[0])) {
                                $classfunction=explode(':',$this_function_temp[0]);
                                if(count($classfunction)<2) {
                                    $templist[2][$key]=0;
                                    break;
                                }
                                if(isset($classfunction[0]) && !is_hash($classfunction[0])) {
                                    $templist[2][$key]=0;
                                    break;
                                }
                                if(isset($classfunction[1]) && in_array(strtolower($classfunction[1]),$nofunction_array)) {
                                    $templist[2][$key]=0;
                                    break;
                                }
                                if(isset($classfunction[1]) && !preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/',$classfunction[1])) {
                                    $templist[2][$key]=0;
                                    break;
                                }
                            }else {
                                if(in_array(strtolower($this_function_temp[0]),$nofunction_array)) {
                                    $templist[2][$key]=0;
                                    break;
                                }
                            }
                        }
                        $this_function_args=explode(',',$iffunction[1][0]);
                        $this_function_args_new=array();
                        if($function_key>0 && !in_array('this',$this_function_args)) {$this_function_args_new[]='this';}
                        if($iffunction[1][0]!=='') {
                            $this_function_args_new=array_merge($this_function_args_new,$this_function_args);
                        }
                        if($load_class) {
                            if($function_key===0 && count($this_function_args_new)==0) {
                                $this_templist_newval='C(\''.$this_function_temp[0].'\'';
                            }else {
                                $this_templist_newval='C(\''.$this_function_temp[0].'\',';
                            }
                        }else {
                            $this_templist_newval=$this_function_temp[0].'(';
                        }
                        foreach($this_function_args_new as $args_key=>$val) {
                            $val=trim($val);
                            if($val=='this') {
                                $val=$this_templist_val;
                            }else {
                                $val=trim($val,'"');
                                $val=trim($val,'\'');
                                $val=escape_temp_char($val);
                                if(@$val[0]=='$') {
                                    $val=cms_template_varname($val,$template_config['class']);
                                }elseif(@$val[0]=='`') {
                                    $val=substr($val,1);
                                }else {
                                    $val=echo_replace($val);
                                    $val='\''.$val.'\'';
                                }
                            }
                            if(count($this_function_args_new)==($args_key+1)) {
                                $this_templist_newval.=$val.')';
                            }else {
                                $this_templist_newval.=$val.',';
                            }
                        }
                        if(count($this_function_args_new)==0) {
                            $this_templist_newval.=')';
                        }
                        $this_templist_val=$this_templist_newval;
                        $this_templist_newval='';
                    }else {
                        if($function_key==0) {
                            if(!$charname && !cms_template_is_varname($thisfunction)) {
                                $templist[2][$key]=0;
                                break;
                            }
                        }
                        if(substr($thisfunction,0,1)=='$') {
                            $thisfunction=cms_template_varname($thisfunction,$template_config['class']);
                        }elseif(substr($thisfunction,0,1)=='`') {
                            $thisfunction=substr($thisfunction,1);
                            $thisfunction=escape_temp_char($thisfunction);
                        }else {
                            $thisfunction=trim($thisfunction,'"');
                            $thisfunction=trim($thisfunction,'\'');
                            $thisfunction=escape_temp_char($thisfunction);
                            $thisfunction=echo_replace($thisfunction);
                            $thisfunction='\''.$thisfunction.'\'';
                        }
                        $this_templist_val=$thisfunction;
                    }
                }
            }
            if($templist[2][$key] && $charname) {
                $templist[1][$key]=$charname.'='.$this_templist_val.';';
            }elseif($templist[2][$key]) {
                $templist[1][$key]='echo('.$this_templist_val.');';
            }
        }
    }
    foreach($templist[0] as $key=>$val) {
        if($templist[2][$key]) {
            $templatecontent=str_replace($val,'<?php '.$templist[1][$key].' ?>',$templatecontent);
        }
    }
    if(!isset($template_config['code']) && isset($template_config['template']) && $template_hook_return=C($template_config['class'].':'.$template_config['template'].':~',$templatecontent)) {
        if($template_hook_return!==false && $template_hook_return!==true && $template_hook_return!==null) {
            if(is_array($template_hook_return) && isset($template_hook_return[1])) {
                $template_hook_return=$template_hook_return[1];
            }
            $templatecontent=$template_hook_return;
        }
    }
    Return $templatecontent;
}
function _stripslashes() {
    if(version_compare(PHP_VERSION,'5.4','<')){if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()){if (isset($_GET)){$_GET=_stripslashes_deep($_GET);}if (isset($_POST)){$_POST=_stripslashes_deep($_POST);}if (isset($_COOKIE)){$_COOKIE=_stripslashes_deep($_COOKIE);}}}if(!isset($GLOBALS['C']['PoweredBy']) || $GLOBALS['C']['PoweredBy']){@header(str_replace('U','Class','X-Powered-By:UCMS'));}
}
function _stripslashes_deep($value) {
    if(is_array($value)){return array_map('_stripslashes_deep',$value);}elseif(empty($value)){return $value;}else {Return stripslashes($value);}
}
function cli_parse(){
    if(!isset($_SERVER['argv'][1])) {$_SERVER['argv'][1]='/';}
    $GLOBALS['C']['SystemDir']='/';
    if(substr($_SERVER['argv'][1],0,2)=='//' || substr($_SERVER['argv'][1],0,7)=='http://' || substr($_SERVER['argv'][1],0,8)=='https://'){
        $urls=parse_url($_SERVER['argv'][1]);
        if(isset($urls['scheme']) && $urls['scheme']=='https'){
            $_SERVER["HTTPS"]='on';
        }
        if(isset($urls['host'])){
            $_SERVER['HTTP_HOST']=$urls['host'];
        }
        if(isset($urls['path'])){
            $_SERVER['REQUEST_URI']=$urls['path'];
        }else{
            $_SERVER['REQUEST_URI']='/';
        }
    }else{
        $_SERVER['REQUEST_URI']=$_SERVER['argv'][1];
    }
    return true;
}
function is_hash($hash) {
    if(empty($hash)){return false;}
    Return preg_match('/^[A-Za-z]{1}[A-Za-z0-9_]{0,31}$/',$hash);
}
function jump($url='',$time=0) {
    Return C('cms:common:jump',$url,$time);
}
function error($msg='') {
    if(empty($msg)) {
        $error = error_get_last();
        $msg='';
        if(isset($error['message'])) {$msg.=$error['message'];}
        if(isset($error['file'])) {$msg.=' '.$error['file'];}
        if(isset($error['line'])) {$msg.=' on line:'.$error['line'];}
    }
    Return C('cms:error',$msg);
}
function begin(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':begin',$args);
}
function commit(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':commit',$args);
}
function rollback(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':rollback',$args);
}
function prefix($table) {
    Return C($GLOBALS['C']['DbClass'].':prefix',$table);
}
function insert(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':insert',$args);
}
function update(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':update',$args);
}
function del(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':del',$args);
}
function where(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':where',$args);
}
function escape($str){
    Return C($GLOBALS['C']['DbClass'].':escape',$str);
}
function total($table='',$where=''){
    Return C($GLOBALS['C']['DbClass'].':total',$table,$where);
}
function one(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':one',$args);
}
function all(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':all',$args);
}
function page(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':page',$args);
}
function pagelist(){
    $args=func_get_args();
    Return C($GLOBALS['C']['DbClass'].':pagelist',$args);
}
function pageinfo(){
    Return C($GLOBALS['C']['DbClass'].':pageinfo');
}
function query($sql){
    Return C($GLOBALS['C']['DbClass'].':query',$sql);
}
function fetchone($query){
    Return C($GLOBALS['C']['DbClass'].':fetchone',$query);
}
function fetchall($query){
    Return C($GLOBALS['C']['DbClass'].':fetchall',$query);
}

class cms_database {
    public $kind,$connectError,$Stmt,$Sql,$databaselink;
    function __construct(){
        if(!isset($GLOBALS['C']['DbInfo']['showerror'])) {$GLOBALS['C']['DbInfo']['showerror']=@$GLOBALS['C']['Debug'];}
        if(!isset($GLOBALS['C']['DbInfo']['prefix'])) {$GLOBALS['C']['DbInfo']['prefix']='';}
        if(!isset($GLOBALS['C']['DbInfo']['engine'])) {$GLOBALS['C']['DbInfo']['engine']='MyISAM';}
        if(!isset($GLOBALS['C']['DbInfo']['charset'])) {$GLOBALS['C']['DbInfo']['charset']='utf8';}
        $db_info=$GLOBALS['C']['DbInfo'];
        $GLOBALS['C']['DbInfo']['querycount']=0;
        $this->kind=$db_info['kind'];
        $this->connectError=false;
        if($db_info['kind']=='sqlitepdo'){
            if(stripos($db_info['file'],'/')===false && stripos($db_info['file'],'\\')===false) {
                $db_info['file']=$GLOBALS['C']['SystemRoot'].$db_info['file'];
            }
            $this->databaselink = new PDO('sqlite:'.$db_info['file']);
            $this->databaselink->setAttribute(constant('PDO::ATTR_ORACLE_NULLS'),constant('PDO::NULL_TO_STRING'));
        }elseif($db_info['kind']=='mysqlpdo') {
            if(!isset($db_info['dbname']) || empty($db_info['dbname']) || isset($GLOBALS['C']['DbInfo']['createdb'])) {$dbinfo='';}else {$dbinfo='dbname='.$db_info['dbname'];}
            $db_info['hostinfo']=explode(':',$db_info['host']);
            if(count($db_info['hostinfo'])>1) {$db_info['host']=$db_info['hostinfo'][0];$db_info['port']=$db_info['hostinfo'][1];}else {$db_info['port']='3306';}
            try{
                @$this->databaselink = new PDO('mysql:host='.$db_info['host'].';port='.$db_info['port'].';'.$dbinfo,$db_info['user'],$db_info['password'],array(constant('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')=>true,constant('PDO::ATTR_ORACLE_NULLS')=>constant('PDO::NULL_TO_STRING')));
                $this->query("SET NAMES ".$GLOBALS['C']['DbInfo']['charset']);
            }catch(Exception $errinfo){
                $this->error('database connect error');
                $this->connectError=true;
            }
        }elseif($db_info['kind']=='mysql'){
            @$this->databaselink = mysql_connect($db_info['host'],$db_info['user'],$db_info['password']);
            if($this->databaselink) {
                if(isset($db_info['dbname']) && !empty($db_info['dbname'])) {
                    mysql_select_db($db_info['dbname'],$this->databaselink);
                }
                mysql_query("SET NAMES ".$GLOBALS['C']['DbInfo']['charset']);
            }else {
                $this->error('database connect error');
                $this->connectError=true;
            }
            
        }else{
            $this->error('database error');
            $this->connectError=true;
        }
    }
    function disconnect(){
        if($this->kind=='mysql') {
            mysql_close($this->databaselink); 
        }
        $this->DB = null;
        $this->Stmt = null;
    }
    function prefix($table){
        if(substr($table,0,10)=='no_perfix_') {
            Return substr($table,10);
        }
        Return $GLOBALS['C']['DbInfo']['prefix'].$this->escape($table);
    }
    function query($sql){
        if($this->connectError) {Return false;}
        @$GLOBALS['C']['DbInfo']['querycount']++;
        @$GLOBALS['C']['DbInfo']['sql'][]=$sql;
        if(!isset($this->databaselink) || $this->databaselink==null) {
            Return false;
        }
        if($this->kind=='mysql') {
            $res = mysql_query($sql,$this->databaselink); 
        }else {
            $res = $this->databaselink->query($sql);
        }
        if ($res) {
            $this->Stmt = $res;
            $this->Sql = $sql;
            return $this;
        }
        $this->error();
        Return false;
    }
    function where($args){
        if(count($args)>1) {
            $new_args=array();
            foreach($args as $key=>$val) {
                if($key%2==0 && isset($args[$key+1])) {
                    $new_args[$val]=$args[$key+1];
                }
            }
            $args[0]=$new_args;
        }
        $sql='';
        if(!is_array($args[0]) || count($args[0])==0) {
            Return '';
        }
        foreach($args[0] as $name=>$val) {
            $this_sql='';
            $symbol=substr($name,-2);
            if($symbol=='<>' || $symbol=='>=' || $symbol=='<=') {
                $name=substr($name,0,-2);
            }else {
                $symbol=substr($name,-1);
                if($symbol!='<' && $symbol!='>' && $symbol!='%' && $symbol!='=' && $symbol!='!' && $symbol!=';') {
                    $symbol='=';
                    $name.=$symbol;
                }
                if($symbol=='!') {$symbol='<>';}
                $name=substr($name,0,-1);
            }
            if($symbol=='=') {
                if(is_array($val) && count($val)) {
                    $this_sql.=$this->escape($name).' in(';
                    if(isset($val['table']) && isset($val['column'])){
                        $this_sql.=$this->subQuery($val);
                    }else{
                        $key=0;
                        foreach($val as $this_val) {
                            if($key) {
                                $this_sql.=',\''.$this->escape($this_val).'\'';
                            }else {
                                $this_sql.='\''.$this->escape($this_val).'\'';
                            }
                            $key++;
                        }
                    }
                    $this_sql.=')';
                }elseif(is_array($val) && !count($val)){
                    $this_sql.='(1=2)';
                }else {
                    $this_sql.=$this->escape($name).'=\''.$this->escape($val).'\'';
                }
            }
            if($symbol==';') {
                $this_sql.='(';
                if(is_array($val) && count($val)) {
                    $or=false;
                    foreach($val as $key=>$this_val) {
                        if($or) { $this_sql.=' or '; }
                        if(is_array($this_val)) {
                            $is_numeric_array=true;
                            foreach (array_keys($this_val) as $array_key) {
                                if(!is_numeric($array_key)){
                                    $is_numeric_array=false;
                                }
                            }
                            if($is_numeric_array){
                                $this_sql.=$this->where(array(array($key=>$this_val)));
                            }elseif(isset($this_val['table']) && isset($this_val['column'])){
                                $this_sql.=$this->where(array(array($key=>$this_val)));
                            }else{
                                $this_sql.=$this->where(array(array('1;'=>$this_val)));
                            }
                        }else {
                            $this_sql.=$this->where(array(array($key=>$this_val)));
                        }
                        $or=true;
                    }
                }else {
                    $this_sql.='1=1';
                }
                $this_sql.=')';
            }
            if($symbol=='<' || $symbol=='>' || $symbol=='<>' || $symbol=='<='  || $symbol=='>=') {
                if(is_array($val)) {
                    foreach($val as $key=>$this_val) {
                        if($key) {
                            $this_sql.=' and '.$this->escape($name).$this->escape($symbol).'\''.$this->escape($this_val).'\'';
                        }else {
                            $this_sql.=$this->escape($name).$this->escape($symbol).'\''.$this->escape($this_val).'\'';
                        }
                    }
                }else {
                    $this_sql.=$this->escape($name).$symbol.'\''.$this->escape($val).'\'';
                }
            }
            if($symbol=='%') {
                if(is_array($val)) {
                    foreach($val as $key=>$this_val) {
                        if(substr($this_val,0,1)!='%' && substr($this_val,-1)!='%') {
                            $this_val='%'.$this_val.'%';
                        }
                        if($key) {
                            $this_sql.=' and '.$this->escape($name).' like \''.$this->escape($this_val).'\'';
                        }else {
                            $this_sql.=$this->escape($name).' like \''.$this->escape($this_val).'\'';
                        }
                    }
                }else {
                    if(substr($val,0,1)!='%' && substr($val,-1)!='%') {
                        $val='%'.$val.'%';
                    }
                    $this_sql.=$this->escape($name).' like \''.$this->escape($val).'\'';
                }
            }
            if(empty($sql)) {
                $sql=$this_sql;
            }else {
                $sql.=' and '.$this_sql;
            }
        }
        Return $sql;
    }
    function subQuery($strarray){
        if(isset($strarray['table'])) {$table=$this->prefix($strarray['table']);}else {Return false;}
        if(isset($strarray['where']) && is_array($strarray['where'])) {
            $strarray['where']=$this->where(array($strarray['where']));
        }
        if(isset($strarray['where']) && !empty($strarray['where'])) {$where='where '.$strarray['where'];}else {$where='';}
        if(isset($strarray['group']) && !empty($strarray['group'])) {$group='group by '.$this->escape($strarray['group']);}else {$group='';}
        if(isset($strarray['offset']) && !empty($strarray['offset'])) {$offset=intval($strarray['offset']);}else {$offset='';}
        if(isset($strarray['limit']) && !empty($strarray['limit'])) {$limit=intval($strarray['limit']);}else {$limit='';}
        if(isset($strarray['order']) && !empty($strarray['order'])) {
            $order='order by '.$this->escape($strarray['order']);
            if(strtolower($order)=='order by rand'){
                if($this->kind=='sqlitepdo') {
                    $order='order by random()';
                }elseif($this->kind=='mysqlpdo' || $this->kind=='mysql') {
                    $order='order by rand()';
                }
            }
        }else {
            $order='';
        }
        if(isset($strarray['column'])) {$column=$this->escape($strarray['column']);}else {$column='*';}
        $limitsql='';
        if(!empty($limit) && !empty($offset)) {
            $limitsql='limit '.$offset.','.$limit;
        }elseif(!empty($limit) && empty($offset)) {
            $limitsql='limit '.$limit;
        }
        return "SELECT $column FROM $table $where $group $order $limitsql";
    }
    function insert($args){
        if(!is_array($args[0])) {
            $strarray=array();
            foreach($args as $key=>$val) {
                if($key%2==0 && isset($args[$key+1])) {
                    $strarray[$val]=$args[$key+1];
                }
            }
        }else {
            $strarray=$args[0];
        }
        if(!isset($strarray['table'])) {
            $this->error('no table');
            Return false;
        }
        $table=$this->prefix($strarray['table']);
        unset($strarray['table']);
        $str1='';
        $str2='';
        foreach($strarray as $key=>$val) {
            if($str1=='') {$str1='(`'.$this->escape($key).'`';}else {$str1.=',`'.$this->escape($key).'`';}
            if($str2=='') {$str2="('".$this->escape($val)."'";}else {$str2.=",'".$this->escape($val)."'";}
        }
        if($this->query('INSERT INTO `'.$table.'` '.$str1.') VALUES '.$str2.');')) {
            Return $this->lastId();
        }else {
            Return false;
        }
    }
    function update($args){
        if(!is_array($args[0])) {
            $strarray=array();
            foreach($args as $key=>$val) {
                if($key%2==0 && isset($args[$key+1])) {
                    $strarray[$val]=$args[$key+1];
                }
            }
        }else {
            $strarray=$args[0];
        }
        if(!isset($strarray['table'])) {
            $this->error('no table');
            Return false;
        }
        $table=$this->prefix($strarray['table']);
        unset($strarray['table']);
        if(isset($strarray['where']) && is_array($strarray['where'])) {
            $strarray['where']=$this->where(array($strarray['where']));
        }
        $where='';
        if(isset($strarray['where']) && !empty($strarray['where'])) {
            $where='where '.$strarray['where'];
        }
        unset($strarray['where']);
        $str='';
        foreach($strarray as $key=>$val) {
            if(substr($val,0,2)=='{{' && substr($val,-2)=='}}') {
                $val=ltrim($val,'{{');
                $val=rtrim($val,'}}');
                $str.=','.$this->escape($key)."=".$this->escape($val);
            }else {
                $str.=','.$this->escape($key)."='".$this->escape($val)."'";
            }
        }
        $str=ltrim($str,',');
        if($this->query('UPDATE '.$table.' SET '.$str.' '.$where)) {
            Return true;
        }else {
            Return false;
        }
    }
    function del($args){
        if(!is_array($args[0])) {
            $strarray=array();
            foreach($args as $key=>$val) {
                if($key%2==0 && isset($args[$key+1])) {
                    $strarray[$val]=$args[$key+1];
                }
            }
        }else {
            $strarray=$args[0];
        }
        if(!isset($strarray['table'])) {
            $this->error('no table');
            Return false;
        }
        $table=$this->prefix($strarray['table']);
        unset($strarray['table']);
        if(isset($strarray['where']) && is_array($strarray['where'])) {
            $strarray['where']=$this->where(array($strarray['where']));
        }
        $where='';
        if(isset($strarray['where']) && !empty($strarray['where'])) {
            $where='where '.$strarray['where'];
        }
        unset($strarray['where']);
        if($this->query("delete from $table $where")) {
            Return true;
        }else {
            Return false;
        }
    }
    function total($table='',$where=''){
        if(empty($table)) {
            Return false;
        }
        if(!empty($where)) {
            if(is_array($where)) {
                $where='where '.$this->where(array($where));
            }else {
                $where='where '.$where;
            }
        }
        $this->query('SELECT count(*) FROM '.$this->prefix($this->escape($table)).' '.$where.' limit 1');
        if($total=$this->fetchone()) {
            if(isset($total['count(*)'])) {
                Return intval($total['count(*)']);
            }
        }
        Return false;
    }
    function one($args){
        if(!is_array($args[0])) {
            $strarray=array();
            foreach($args as $key=>$val) {
                if($key%2==0 && isset($args[$key+1])) {
                    $strarray[$val]=$args[$key+1];
                }
            }
        }else {
            $strarray=$args[0];
        }
        if(count($strarray)<2) {Return false;}
        if(isset($strarray['table'])) {$table=$this->prefix($strarray['table']);}else {$this->error('no table');Return false;}
        if(isset($strarray['where']) && is_array($strarray['where'])) {
            $strarray['where']=$this->where(array($strarray['where']));
        }
        if(isset($strarray['where']) && !empty($strarray['where'])) {$where='where '.$strarray['where'];}else {$where='';}
        if(isset($strarray['group']) && !empty($strarray['group'])) {$group='group by '.$this->escape($strarray['group']);}else {$group='';}
        if(isset($strarray['offset']) && !empty($strarray['offset'])) {$offset=intval($strarray['offset']);}else {$offset='';}
        if(isset($strarray['column']) && !empty($strarray['column'])) {$column=$this->escape($strarray['column']);}else {$column='*';}
        if(isset($strarray['order']) && !empty($strarray['order'])) {
            $order='order by '.$this->escape($strarray['order']);
            if(strtolower($order)=='order by rand'){
                if($this->kind=='sqlitepdo') {
                    $order='order by random()';
                }elseif($this->kind=='mysqlpdo' || $this->kind=='mysql') {
                    $order='order by rand()';
                }
            }
        }else {
            $order='';
        }
        $limitsql='';
        if(!empty($offset)) {$limitsql='limit '.$offset.',1';}else{$limitsql='limit 1';};
        $this->query("SELECT $column FROM $table $where $group $order $limitsql");
        return $this->fetchone();
    }
    function all($args){
        if(!is_array($args[0])) {
            $strarray=array();
            foreach($args as $key=>$val) {
                if($key%2==0 && isset($args[$key+1])) {
                    $strarray[$val]=$args[$key+1];
                }
            }
        }else {
            $strarray=$args[0];
        }
        if(count($strarray)<1) {Return false;}
        if(isset($strarray['table'])) {$table=$this->prefix($strarray['table']);}else {$this->error('no table');Return array();}
        if(isset($strarray['where']) && is_array($strarray['where'])) {
            $strarray['where']=$this->where(array($strarray['where']));
        }
        if(isset($strarray['where']) && !empty($strarray['where'])) {$where='where '.$strarray['where'];}else {$where='';}
        if(isset($strarray['group']) && !empty($strarray['group'])) {$group='group by '.$this->escape($strarray['group']);}else {$group='';}
        if(isset($strarray['offset']) && !empty($strarray['offset'])) {$offset=intval($strarray['offset']);}else {$offset='';}
        if(isset($strarray['limit']) && !empty($strarray['limit'])) {$limit=intval($strarray['limit']);}else {$limit='';}
        if(isset($strarray['page'])) {$page=$strarray['page'];}else {$page='';}
        if(isset($strarray['optimize']) && $strarray['optimize']) {$optimize=$strarray['optimize'];}else {$optimize=false;}
        if(isset($strarray['order']) && !empty($strarray['order'])) {
            $order='order by '.$this->escape($strarray['order']);
            if(strtolower($order)=='order by rand'){
                if($this->kind=='sqlitepdo') {
                    $order='order by random()';
                }elseif($this->kind=='mysqlpdo' || $this->kind=='mysql') {
                    $order='order by rand()';
                }
            }
        }else {
            $order='';
        }
        if($optimize) {
            $column='id';
        }else {
            if(isset($strarray['column'])) {$column=$this->escape($strarray['column']);}else {$column='*';}
        }
        $limitsql='';
        if($page) {
            if($page['page']==0) {Return array();}
            $limit=$page['pagesize'];
            $offset=($page['page']-1)*$page['pagesize'];
        }
        if(!empty($limit) && !empty($offset)) {
            $limitsql='limit '.$offset.','.$limit;
        }elseif(!empty($limit) && empty($offset)) {
            $limitsql='limit '.$limit;
        }
        if($page) {
            $this->query("SELECT count(*) FROM $table $where");
            $articlecount=$this->fetchone();
            $GLOBALS['C']['page']['article']=$articlecount['count(*)'];
        }
        $this->query("SELECT $column FROM $table $where $group $order $limitsql");
        if(!$optimize) {
            return $this->fetchall();
        }
        $lists=$this->fetchall();
        if(!count($lists)) {Return array();}
        $ids=array();
        foreach($lists as $this_list) {
            $ids[]=$this_list['id'];
        }
        if(isset($strarray['column'])) {$column=$this->escape($strarray['column']);}else {$column='*';}
        $where='where '.$this->where(array(array('id'=>$ids)));
        $this->query("SELECT $column FROM $table $where $order");
        return $this->fetchall();
    }
    function page($args){
        unset($GLOBALS['C']['page']);
        if(!is_array($args[0])) {
            $config=array();
            foreach($args as $key=>$val) {
                if($key%2==0 && isset($args[$key+1])) {
                    $config[$val]=$args[$key+1];
                }
            }
        }else {
            $config=$args[0];
        }
        if(isset($config['pagesize']) && empty($config['pagesize'])) {unset($config['pagesize']);}
        if(!isset($config['pagesize'])) {$config['pagesize']=1;}
        if(!isset($config['page'])) {$config['page']='';}
        if(!isset($config['pagename']) || empty($config['pagename'])) {$config['pagename']='page';}
        if(empty($config['page'])) {
            if(isset($_GET[$config['pagename']]) && $_GET[$config['pagename']]>0) {
                $config['page']=intval($_GET[$config['pagename']]);
            }elseif(!isset($_GET[$config['pagename']])) {
                $config['page']=1;
            }else {
                $config['page']=0;
            }
        }
        if($config['page']==0) {$this->error('page error');}
        $GLOBALS['C']['page']=$config;
        Return $config;
    }
    function pageinfo() {
        if(!isset($GLOBALS['C']['page'])) {Return array();}
        Return $GLOBALS['C']['page'];
    }
    function pagelist($args=array()){
        if(!isset($GLOBALS['C']['page'])) {
            Return array();
        }
        $config=$GLOBALS['C']['page'];
        if(isset($args[0]) && !is_array($args[0])) {
            foreach($args as $key=>$val) {
                if($key%2==0 && isset($args[$key+1])) {
                    $config[$val]=$args[$key+1];
                }
            }
        }elseif(isset($args[0]) && is_array($args[0])) {
            foreach($args[0] as $args_key=>$args_val) {
                $config[$args_key]=$args_val;
            }
        }
        if(!isset($config['article'])) {
            if(!isset($GLOBALS['C']['page']['article'])) {
                Return array();
            }
            $config['article']=$GLOBALS['C']['page']['article'];
        }
        if(!isset($config['showpages'])) {
            if(isset($GLOBALS['C']['page']['showpages'])) {
                $config['showpages']=$GLOBALS['C']['page']['showpages'];
            }else {
                $config['showpages']=3;
            }
        }
        if(!isset($config['pagename'])) {
            if(isset($GLOBALS['C']['page']['pagename'])) {
                $config['pagename']=$GLOBALS['C']['page']['pagename'];
            }else {
                $config['pagename']='page';
            }
        }
        if(!isset($config['replace'])) {
            $config['replace']='('.$config['pagename'].')';
        }
        if(!isset($config['pagesize'])) {
            if(isset($GLOBALS['C']['page']['pagesize'])) {
                $config['pagesize']=$GLOBALS['C']['page']['pagesize'];
            }else {
                Return array();
            }
        }
        if(!isset($config['page'])) {
            if(isset($GLOBALS['C']['page']['page'])) {
                $config['page']=$GLOBALS['C']['page']['page'];
            }else {
                Return array();
            }
        }
        if(!isset($config['style_active'])) {
            if(isset($GLOBALS['C']['page']['style_active'])) {
                $config['style_active']=$GLOBALS['C']['page']['style_active'];
            }else {
                $config['style_active']='active';
            }
        }
        if(!isset($config['style_disabled'])) {
            if(isset($GLOBALS['C']['page']['style_disabled'])) {
                $config['style_disabled']=$GLOBALS['C']['page']['style_disabled'];
            }else {
                $config['style_disabled']='disabled';
            }
        }
        if(!isset($config['url']) || empty($config['url'])) {
            $config['url']=$_SERVER['REQUEST_URI'];
            if(stripos($config['url'],$config['pagename'].'=')===false) {
                if(stripos($config['url'],'?')===false) {
                    $config['url']=$config['url'].'?'.$config['pagename'].'='.$config['replace'];
                }else {
                    $config['url']=$config['url'].'&'.$config['pagename'].'='.$config['replace'];
                }
            }else {
                $config['url'] = preg_replace("/".$config['pagename']."=([0-9]+)/is", $config['pagename']."=".$config['replace'], $config['url']);
            }
        }
        if(!isset($config['channelurl']) || empty($config['channelurl'])) {
            $config['channelurl']=str_replace($config['replace'],'1',$config['url']);
        }
        if(!isset($config['article'])) {
            if(!isset($GLOBALS['C']['page']['article'])) {
                Return array();
            }
            $config['article']=$GLOBALS['C']['page']['article'];
        }
        $config['pagecount'] = intval(max(ceil($config['article']/$config['pagesize']),1));
        if(isset($config['maxpage']) && $config['maxpage']){
            if($config['pagecount']>$config['maxpage']){
                $config['pagecount']=$config['maxpage'];
            }
        }
        $config['now']=array('link'=>str_replace($config['replace'],$config['page'],$config['url']),'class'=>'now','page'=>$config['page']);
        $config['first']=array('link'=>$config['channelurl'],'class'=>'first','page'=>'1');
        $config['last']=array('link'=>str_replace($config['replace'],$config['pagecount'],$config['url']),'class'=>'last','page'=>$config['pagecount']);
        if($config['page']>1) {
            if($config['page']==2) {
                $config['prev']=array('link'=>$config['channelurl'],'class'=>'prev','page'=>$config['page']-1);
            }else {
                $config['prev']=array('link'=>str_replace($config['replace'],$config['page']-1,$config['url']),'class'=>'prev','page'=>$config['page']-1);
            }
        }
        if($config['page']<$config['pagecount']){
            $config['next']=array('link'=>str_replace($config['replace'],$config['page']+1,$config['url']),'class'=>'next','page'=>$config['page']+1);
        }
        if(!isset($config['pagecount'])) {
            $config['pagecount']=$GLOBALS['C']['page']['pagecount'];
        }
        $GLOBALS['C']['page']=$config;
        $pagesarray=array();
        $startpage=max($config['pagecount']-$config['showpages'],1);
        $startpage=min($startpage,$config['page']-1);
        if($startpage<1) {$startpage=1;}
        $endpage=min($startpage+$config['showpages'],$config['pagecount']);
        for($i=$startpage;$i<=$endpage;$i++)
        {
            if($i==1) {
                $thisurl=$config['channelurl'];
            }else {
                $thisurl=str_replace($config['replace'],$i,$config['url']);
            }
            if($config['page']==$i){
                $pagesarray[]=array('link'=>$thisurl,'title'=>$i,'class'=>$config['style_active'],'page'=>$i);
                
            }else{
                $pagesarray[]=array('link'=>$thisurl,'title'=>$i,'class'=>'','page'=>$i);
            }
        }
        Return $pagesarray;
    }
    function escape($str){
        if($str===null){ return null; }
        if($this->kind=='mysql' || $this->kind=='mysqlpdo') {
            Return str_replace(array("'",'\\'),array("''",'\\\\'),$str);
        }else {
            Return str_replace("'","''",$str);
        }
    }
    function createTable($table,$fields=array()){
        $sqlite_fields='';
        $mysql_fields='';
        if(is_array($fields) && count($fields)) {
            unset($fields['id']);
            foreach($fields as $fieldname=>$field) {
                if(is_array($field) && isset($field['Type'])) {
                    $sqlite_fields.=',['.$fieldname.'] '.$field['Type'];
                    $mysql_fields .=',`'.$fieldname.'` '.$field['Type'];
                }elseif(is_array($field)) {
                    if(!isset($field[1])) {$field[1]='';}
                    $sqlite_fields.=',['.$fieldname.'] '.$field[0].' '.$field[1];
                    $mysql_fields .=',`'.$fieldname.'` '.$field[0].' '.$field[1];
                }else {
                    if(stripos($field,'()')!==false) {
                        $field=str_replace('()','',$field);
                    }
                    $sqlite_fields.=',['.$fieldname.'] '.$field;
                    $mysql_fields .=',`'.$fieldname.'` '.$field;
                }
            }
        }
        $table=$this->prefix($table);
        if($this->kind=='sqlitepdo') {
            Return $this -> query("CREATE TABLE if not exists [".$table."]([id] INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT".$sqlite_fields.");");
        }elseif($this->kind=='mysqlpdo' || $this->kind=='mysql') {
            Return $this -> query("CREATE TABLE if not exists `".$table."` (`id` int(11) NOT NULL auto_increment".$mysql_fields.",PRIMARY KEY  (`id`)) ENGINE=".$GLOBALS['C']['DbInfo']['engine']." DEFAULT CHARSET=".$GLOBALS['C']['DbInfo']['charset'].";");
        }
    }
    function delTable($table){
        $tablename=$table;
        $table=$this->prefix($table);
        $table=$this->escape($table);
        if($this->kind=='sqlitepdo') {
            $indexs=fetchall(query("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='$table'"));
            foreach($indexs as $indexname) {
                $this->delIndex($tablename,@$indexname['name']);
            }
        }
        Return $this -> query("DROP TABLE if exists `".$table."`;");
    }
    function addField($table,$name,$type,$config=''){
        $table=$this->prefix($table);
        $name=$this->escape($name);
        $type=$this->escape($type);
        if(empty($config)) {$config='null';}
        if(stripos($type,'()')!==false) {
            $type=str_replace('()','',$type);
        }
        $this->query("alter table $table add $name $type $config;");
        Return true;
    }
    function editField($table,$name,$type,$config=''){
        if($this->kind=='sqlitepdo') {
            Return true;
        }
        $table=$this->prefix($table);
        $name=$this->escape($name);
        $type=$this->escape($type);
        if(empty($config)) {$config='null';}
        $this->query("alter table $table modify $name $type $config;");
        Return true;
    }
    function delField($table,$name){
        $name=$this->escape($name);
        if($this->kind=='sqlitepdo') {
            $fields=$this->getFields($table);
            $tablenew=$table.'_new_'.rand(9999,99999);
            $newfields=array();
            $fieldnames=array();
            foreach($fields as $key=>$field) {
                if($key!='id' && $key!=$name && isset($field['Type'])) {
                    $newfields[$key]=$field['Type'];
                }
                if($key!=$name && isset($field['Type'])) {
                    $fieldnames[]=$key;
                }
            }
            $this->createTable($tablenew,$newfields);
            $fieldnamessql=implode(',',$fieldnames);
            $this->query("INSERT INTO ".$this->prefix($tablenew)." SELECT $fieldnamessql FROM $table;");
            if($this->total($tablenew)!==$this->total($table)) {
                Return false;
            }
            $tableold=$table.'_old_'.rand(9999,99999);
            $this->query("alter table ".$this->prefix($table)." RENAME TO ".$this->prefix($tableold).";");
            $this->delTable($tableold);
            $this->query("alter table ".$this->prefix($tablenew)." RENAME TO ".$this->prefix($table).";");
            Return true;
        }
        $table=$this->prefix($table);
        $this->query("alter table $table drop $name;");
        Return true;
    }
    function addIndex($table,$name,$columns=''){
        $table=$this->prefix($table);
        $name=$this->escape($name);
        if(empty($columns)){
            $columns=$name;
        }else{
            $columns=$this->escape($columns);
        }
        if($this->kind=='sqlitepdo') {
            $this->query("CREATE INDEX {$table}__$name ON $table ($columns);");
        }elseif($this->kind=='mysqlpdo' || $this->kind=='mysql') {
            $this->query("alter table $table add INDEX $name ($columns);");
        }
        Return true;
    }
    function delIndex($table,$name){
        $table=$this->prefix($table);
        $name=$this->escape($name);
        if($this->kind=='sqlitepdo') {
            $this->query("drop INDEX if exists {$table}__$name;");
        }elseif($this->kind=='mysqlpdo' || $this->kind=='mysql') {
            $this->query("alter table $table drop INDEX $name;");
        }
        
        Return true;
    }
    function getFields($table){
        $showerror=$GLOBALS['C']['DbInfo']['showerror'];
        $GLOBALS['C']['DbInfo']['showerror']=0;
        $table=$this->prefix($table);
        $fields=array();
        if($this->kind=='mysqlpdo' || $this->kind=='mysql') {
            $query = $this -> query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = '".$GLOBALS['C']['DbInfo']['dbname']."' AND TABLE_NAME = '$table';");
            $link = $this -> fetchall($query);
            if(!count($link)) {
                Return $fields;
            }
            $query = $this -> query("show columns from `$table`");
            $link = $this -> fetchall($query);
            foreach($link as $key=>$val) {
                $fields[$val['Field']]=$val;
            }
        }elseif($this->kind=='sqlitepdo') {
            $query = $this -> query("SELECT * FROM sqlite_master WHERE type='table' and name='".$table."';");
            $link = $this -> fetchone($query);
            if(!isset($link['sql'])) {
                Return $fields;
            }
            preg_match("/(?:\()(.*)(?:\))/s",$link['sql'],$fieldssql);
            if(isset($fieldssql[1])) {
                $fieldsarray=explode(',',$fieldssql[1]);
                foreach($fieldsarray as $key=>$val) {
                    $val=trim(str_replace("  "," ",$val));
                    $vals=explode(' ',$val);
                    if(isset($vals[0]) && isset($vals[1])) {
                        $vals[0]=trim(str_replace("[","",str_replace("]","",$vals[0])));
                        $fields[$vals[0]]=array('Field'=>$vals[0],'Type'=>$vals[1]);
                    }
                }
            }
        }
        $GLOBALS['C']['DbInfo']['showerror']=$showerror;
        Return $fields;
    }
    function fetchall(){
        if($this->connectError) {Return array();}
        if($this->kind=='mysql')
        {
            $array=array();
            while($link = $this -> fetchone($this->Stmt)){
                $array[]=$link;
            }
            Return $array;
        }else {
            return @$this->Stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    function fetchone(){
        if($this->connectError) {Return false;}
        if($this->kind=='mysql')
        {
            Return @mysql_fetch_assoc($this->Stmt);
        }else {
            return @$this->Stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    function lastId(){
        if($this->connectError) {Return false;}
        if($this->kind=='mysql')
        {
            return intval(mysql_insert_id());
        }else {
            return intval($this->databaselink->lastInsertId());
        }
    }
    function begin(){
        if($this->connectError) {Return false;}
        if(!method_exists($this->databaselink,'inTransaction')) {
            return false;
        }
        return $this->databaselink->beginTransaction();
    }
    function commit(){
        if($this->connectError) {Return false;}
        if(!method_exists($this->databaselink,'inTransaction')) {
            return false;
        }
        if($this->databaselink->inTransaction()) {
            return $this->databaselink->commit();
        }
        return false;
    }
    function rollback(){
        if($this->connectError) {Return false;}
        if(!method_exists($this->databaselink,'inTransaction')) {
            return false;
        }
        if($this->databaselink->inTransaction()) {
            return $this->databaselink->rollBack();
        }
        return false;
    }
    function affectrows(){
        if($this->kind=='mysql') {
            return mysql_affected_rows();
        }else {
            return $this->Stmt->rowCount();
        }
    }
    function exec($sql){
        if($this->connectError) {Return false;}
        if($this->kind=='mysql') {
            $this->query($sql);
            Return true;
        }
        if ($this->databaselink->exec($sql)) {
            $this->Sql = $sql;
            return $this->lastId();
        }
        $this->error();
    }
    function error($msg='') {
        if(!isset($GLOBALS['C']['DbInfo']['showerror']) || $GLOBALS['C']['DbInfo']['showerror']==0) {
            Return false;
        }
        if(empty($msg)) {
            if($this->kind=='mysql'){
                $msgs = mysql_error();
                $msg=$msgs[2];
            }else{
                $msgs = $this->databaselink->errorInfo();
                $msg=$msgs[2];
            }
        }
        echo($msg);
        Return true;
    }
    function if_field_allow($fieldname) {
        Return !in_array(strtolower($fieldname),array('id','cid','uid','rowstyle','stepstyle','rowurl','csrf','link','like','add','all','alter','as','and','asc','before','between','bigint','binary','blob','both','by','call','cascade','case','change','char','check','column','create','cross','cursor','databases','database','dec','delete','default','desc','div','double','drop','each','else','elseif','exists','exit','explain','false','float','for','force','from','foreign','goto','group','if','in','index','inner','inout','insert','int','integer','into','is','join','key','kill','keys','left','limit','lines','load','lock','loop','long','mod','not','null','on','option','or','order','out','outer','outfile','primary','range','read','reads','real','set','show','sql','ssl','starting','then','table','to','undo','true','union','unlock','update','using','values','varchar','when','where','while','with','write','match'));
    }
}