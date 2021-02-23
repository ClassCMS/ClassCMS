<?php
class cms {
    function init(){
        if(!isset($GLOBALS['C']['DbInfo'])) {Return array('template_class' =>'admin');}
    }
    function stop() {
        Return '无法停止';
    }
    function uninstall() {
        Return '无法卸载';
    }
    function initRoute($routekey) {
        $inited=false;
        $thisroute=$GLOBALS['route'][$routekey];
        if(!isset($thisroute['classhash']) || empty($thisroute['classhash'])) {
            $thisroute['classhash']=$GLOBALS['C']['TemplateClass'];
        }
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
                unset($GLOBALS['C']['article']);
                unset($GLOBALS['C']['channel']);
                if(!$channel=C('this:channel:get',$channel['id'])) {
                    $matched=false;
                }
                if($matched && isset($channel['domain']) && !empty($channel['domain'])) {
                    $matched=macthDomain($channel['domain']);
                }elseif(!empty($GLOBALS['C']['Domain'])) {
                    $matched=macthDomain($GLOBALS['C']['Domain']);
                }
                $article_where=array();
                if($matched) {
                    if(isset($GLOBALS['C']['GET'])) {
                        foreach($GLOBALS['C']['GET'] as $key=>$this_get) {
                            if(substr($key,0,2)=='$.') {
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
                if($matched && count($article_where)) {
                    if(!$article=C('this:article:getOne',array('cid'=>$channel['id'],'where'=>$article_where,'source'=>'route'))) {
                        $matched=false;
                    }
                }
                if($matched) {
                    if(isset($GLOBALS['C']['GET'])) {
                        foreach($GLOBALS['C']['GET'] as $key=>$val) {
                            $_GET[$key]=$val;
                        }
                    }
                    $GLOBALS['C']['channel']=C('this:nowChannel',$thisroute['classhash'],$channel);
                    if(isset($thisroute['classview']) && !empty($thisroute['classview'])) {
                        $thisroute['classview']=C('this:nowView',$thisroute['classhash'],$thisroute['classview'],1);
                        $GLOBALS['C']['route_view'][$thisroute['classfunction']]=$thisroute['classview'];
                        if(isset($article) && $article) {
                            $GLOBALS['C']['route_view_article'][$thisroute['classfunction']]=C('this:nowArticle',$GLOBALS['C']['channel'],$article);
                        }
                        $inited=true;
                    }
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
                    if($inited) {
                        Return true;
                    }
                }
            }
            Return false;
        }else {
            if(!empty($GLOBALS['C']['Domain']) && !macthDomain($GLOBALS['C']['Domain'])) {
                Return false;
            }
            if(isset($thisroute['classview']) && !empty($thisroute['classview'])) {
                $thisroute['classview']=C('this:nowView',$thisroute['classhash'],$thisroute['classview'],0);
                $GLOBALS['C']['route_view'][$thisroute['classfunction']]=$thisroute['classview'];
                $inited=true;
            }
            if(isset($GLOBALS['C']['GET'])) {
                foreach($GLOBALS['C']['GET'] as $key=>$val) {
                    $_GET[$key]=$val;
                }
            }
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
    function nowChannel($classhash,$channel) {
        Return $channel;
    }
    function nowArticle($channel,$article) {
        Return $article;
    }
    function nowView($classhash,$view,$module=1) {
        Return $view;
    }
    function nowUri() {
        if(isset($GLOBALS['C']['uri'])) {
            Return $GLOBALS['C']['uri'];
        }
        if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
        }elseif(isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
        }elseif(isset($_SERVER['HTTP_REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_REQUEST_URI'];
        }
        $noarguri=explode('?',$_SERVER['REQUEST_URI']);
        $uri='/'.ltrim($noarguri[0],'/');
        $uri=substr($uri,strlen($GLOBALS['C']['SystemDir'])-1);
        if(!$GLOBALS['C']['UrlRewrite']) {
            if(empty($uri) || $uri=='/' || $uri=='/'.$GLOBALS['C']['Indexfile']) {$uri='/'.$GLOBALS['C']['Indexfile'].'/';}
            if(stripos(@$_SERVER['SERVER_SOFTWARE'],'iis')) {
                $uri=uridecode(urlencode(iconv("gb2312","utf-8//IGNORE",$uri)));
            }
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
    function homepage($class='') {
        if(empty($class)) {
            $class=$GLOBALS['C']['TemplateClass'];
        }
        if(isset($GLOBALS['C']['homepage'][$class])) {
            Return $GLOBALS['C']['homepage'][$class];
        }
        if(!isset($GLOBALS['C']['DbInfo']) || !is_array($GLOBALS['C']['DbInfo'])) {Return '';}
        if($home=C('cms:channel:home',$class)) {
            $GLOBALS['C']['homepage'][$class]=$home['link'];
        }else {
            $GLOBALS['C']['homepage'][$class]='#';
        }
        Return $GLOBALS['C']['homepage'][$class];
    }
    function error($msg) {
        if($GLOBALS['C']['Debug']) {echo($msg);}
    }
}
function ClassCms_init() {
    define('ClassCms',1);
    if(!isset($GLOBALS['C']['UrlRewrite'])) {$GLOBALS['C']['UrlRewrite']=1;}
    if(!isset($GLOBALS['C']['SiteHash'])) {$GLOBALS['C']['SiteHash']=md5(dirname(__FILE__));}
    if(!isset($GLOBALS['C']['Domain'])) {$GLOBALS['C']['Domain']='';}
    if(!isset($GLOBALS['C']['ClassDir'])) {$GLOBALS['C']['ClassDir']='class';}
    if(!isset($GLOBALS['C']['UploadDir'])) {$GLOBALS['C']['UploadDir']='upload';}
    if(!isset($GLOBALS['C']['CacheDir'])) {$GLOBALS['C']['CacheDir']='cache';}
    if(!isset($GLOBALS['C']['Debug'])) {$GLOBALS['C']['Debug']=false;}
    if(!isset($GLOBALS['C']['DbClass'])) {$GLOBALS['C']['DbClass']='cms:database';}
    if(!isset($GLOBALS['C']['MatchUri'])) {$GLOBALS['C']['MatchUri']=true;}
    if(!isset($GLOBALS['C']['LoadHooks'])) {$GLOBALS['C']['LoadHooks']=true;}
    if(!isset($GLOBALS['C']['LoadRoutes'])) {$GLOBALS['C']['LoadRoutes']=true;}
    if(!isset($GLOBALS['C']['MatchUri'])) {$GLOBALS['C']['MatchUri']=true;}
    if(!isset($GLOBALS['C']['TemplateClass'])) {$GLOBALS['C']['TemplateClass']='template';}
    $GLOBALS['C']['start_time']=microtime(true);
    $GLOBALS['C']['start_memory']=round(memory_get_usage()/1024/1024, 2).'MB';
    if($GLOBALS['C']['Debug']) {ini_set('display_errors','On');error_reporting(E_ALL);}else {ini_set('display_errors','Off');}
    $GLOBALS['C']['SystemRoot']=dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;
    $ScriptInfo=(pathinfo(@$_SERVER['SCRIPT_NAME']));
    if($ScriptInfo['dirname']==="\\" || $ScriptInfo['dirname']==='/') {$ScriptInfo['dirname']='';}
    $GLOBALS['C']['SystemDir']=$ScriptInfo['dirname'].'/';
    $GLOBALS['C']['Indexfile']=$ScriptInfo['basename'];
    if(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['argv'])) {
        if(!isset($_SERVER['argv'][1])) {$_SERVER['argv'][1]='/';}
        $_SERVER['REQUEST_URI']=$_SERVER['argv'][1];
        $GLOBALS['C']['SystemDir']='/';
    }
    _stripslashes();
    if(isset($GLOBALS['C']['DbInfo']) && is_array($GLOBALS['C']['DbInfo'])) {
        if($GLOBALS['C']['LoadHooks']) {
            $hooks=all(array('table'=>'hook','order'=>'classorder desc,hookorder desc,id asc','where'=>array('enabled'=>1,'classenabled'=>1)));
            foreach($hooks as $hook) {
                $hook['hookedfunction']=strtolower($hook['hookedfunction']);
                if(!empty($hook['hookedfunction']) && !isset($GLOBALS['hook'][$hook['hookedfunction']][$hook['classhash'].':'.$hook['hookname']])) {
                    $GLOBALS['hook'][$hook['hookedfunction']][$hook['classhash'].':'.$hook['hookname']]=$hook['classhash'].':'.$hook['hookname'];
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
                    $GLOBALS['C']['route_matched']=true;
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
function U($channel,$routehash='',$article='') {
    if(!is_array($channel)) {
        if(is_numeric($channel)) {
            if($channel==0) {
                $channel=C('cms:channel:home');
            }else {
                $channel=C('cms:channel:get',$channel);
            }
        }else {
            $channel=C('cms:channel:get',$channel,now_class());
        }
    }
    if(!$channel) {Return '';}
    if(empty($routehash)) {$routehash='channel';}
    if(isset($channel['link']) && !empty($channel['link']) && $routehash=='channel') {Return $channel['link'];}
    if(isset($article['link']) && !empty($article['link']) && $routehash=='article') {Return $article['link'];}
    if(isset($GLOBALS['route'])) {
        foreach($GLOBALS['route'] as $thisroute) {
            if(isset($thisroute['classhash']) && isset($thisroute['modulehash']) && isset($thisroute['hash']) && $thisroute['classhash']==$channel['classhash'] && $thisroute['modulehash']==$channel['modulehash'] && $thisroute['hash']==$routehash) {
                $route=$thisroute;
                break;
            }
        }
    }
    if(!isset($route) || !$route) {Return '#';}
    preg_match_all('/[{|\[|(](.*)[}|\]|)]/U',$route['uri'],$getarray);
    foreach($getarray[1] as $key=>$val) {
        if(substr($val,0,2)=='$.') {
            $val=substr($val,2);
            if(isset($channel[$val])) {
                $route['uri']=str_replace($getarray[0][$key],$channel[$val],$route['uri']);
            }
        }elseif(substr($val,0,1)=='$') {
            $val=substr($val,1);
            if(isset($article[$val])) {
                $route['uri']=str_replace($getarray[0][$key],$article[$val],$route['uri']);
            }
        }
    }
    $route['uri']=rewriteUri($route['uri']);
    if(!isset($channel['domain']) || empty($channel['domain'])) {
        $channel['domain']=$GLOBALS['C']['Domain'];
    }elseif(isset($route['domain']) && !empty($route['domain'])) {
        $channel['domain']=$route['domain'];
    }
    if(macthDomain($channel['domain'])) {
        Return $route['uri'];
    }
    $domains=explode(';',strtolower($channel['domain']));
    foreach($domains as $domain) {
        if(stripos($domain,'*')===false) {
            break;
        }
    }
    Return '//'.$domain.server_port().$route['uri'];
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
        $classhash=now_class();
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
    if($end_class!=='-' && isset($GLOBALS['hook'][strtolower($class)]) && count($GLOBALS['hook'][strtolower($class)])) {
        foreach($GLOBALS['hook'][strtolower($class)] as $hookclass) {
            $args[0]=$hookclass;
            if($GLOBALS['C']['Debug']) {
                $return=call_user_func_array('C', $args);
            }else {
                $return=@call_user_func_array('C', $args);
            }
            if(is_array($return) && isset($return[0]) && strtolower($return[0])==strtolower($class)) {
                $args=$return;
            }elseif(is_array($return) && isset($return['class']) && strtolower($return['class'])==strtolower($class)) {
                $args=class_getParameters($return);
            }elseif($return!==null) {
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
        }
        if($return===null && $end_class==='~') {
            $lastreturn=false;
        }
    }
    unset($args[0]);
    $return=false;
    $GLOBALS['C']['runing_class'][]=$classhash;
    if(!isset($GLOBALS['class_config'][$classhash]) && $end_class!=='~') {
        if(!class_exists($classhash)) {
            if($GLOBALS['C']['Debug']) {
                include_once(classDir($classhash).$classhash.'.php');
            }else {
                @include_once(classDir($classhash).$classhash.'.php');
            }
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
            if($GLOBALS['C']['Debug']) {
                include_once(classDir($classhash).$classfile.'.php');
            }else {
                @include_once(classDir($classhash).$classfile.'.php');
            }
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
                    if($GLOBALS['C']['Debug']) {
                        $return=call_user_func_array(array($GLOBALS['class'][$classname],$classfunction),$args);
                    }else {
                        $return=@call_user_func_array(array($GLOBALS['class'][$classname],$classfunction),$args);
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
        foreach($GLOBALS['hook'][strtolower($class).':='] as $watchclass) {
            if($GLOBALS['C']['Debug']) {
                $watchreturn=call_user_func_array('C',array($watchclass,$class,array_values($args),$return));
            }else {
                $watchreturn=@call_user_func_array('C',array($watchclass,$class,array_values($args),$return));
            }
            if($watchreturn!==null) {
                $return=$watchreturn;
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
    array_pop($GLOBALS['C']['runing_class']);
    if($end_class==='~' && isset($lastreturn)) {
        Return $lastreturn;
    }
    Return $return;
}
function now_class() {
    Return @end($GLOBALS['C']['runing_class']);
}
function last_class() {
    if(!isset($GLOBALS['C']['runing_class']) || !is_array($GLOBALS['C']['runing_class'])) {
        Return false;
    }
    $runing_class_count=count($GLOBALS['C']['runing_class']);
    if($runing_class_count==1) {
        Return $GLOBALS['C']['runing_class'][0];
    }
    $now_class=$GLOBALS['C']['runing_class'][$runing_class_count-1];
    foreach($GLOBALS['C']['runing_class'] as $key=>$classhash) {
        if($GLOBALS['C']['runing_class'][$runing_class_count-$key-1]!=$now_class) {
            Return $GLOBALS['C']['runing_class'][$runing_class_count-$key-1];
        }
    }
    Return $now_class;
}
function V($Temp_file,$Temp_var=array(),$Temp_classhash='') {
    if(empty($Temp_classhash)) {$Temp_classhash=now_class();}
    $GLOBALS['C']['runing_class'][]=$Temp_classhash;
    if(!isset($GLOBALS['class_template'][$Temp_classhash])) {C($Temp_classhash);}
    $C_template_config=$GLOBALS['class_template'][$Temp_classhash];
    if(isset($GLOBALS['class_config'][$Temp_classhash]['template_class']) && $GLOBALS['class_config'][$Temp_classhash]['template_class']!=$Temp_classhash) {
        $GLOBALS['C']['runing_class'][]=$GLOBALS['class_config'][$Temp_classhash]['template_class'];
    }
    if(is_array($Temp_var)) {
        foreach($Temp_var as $Temp_key=>$Temp_val) {
            if(!is_int($Temp_key)) {
                $$Temp_key=$Temp_val;
            }
        }
    }
    if(stripos($Temp_file,'}')===false) {
        $C_templates=explode(';',$Temp_file);
        foreach($C_templates as $C_template) {
            if(!empty($C_template)) {
                $C_template_config['template']=$C_template;
                $C_template_config['file']=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['ClassDir'].DIRECTORY_SEPARATOR.$C_template_config['class'].DIRECTORY_SEPARATOR.$C_template_config['dir'].$C_template;
                $C_template_config['filedir']=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['ClassDir'].DIRECTORY_SEPARATOR.$C_template_config['class'].DIRECTORY_SEPARATOR.$C_template_config['dir'];
                $U_tempfile=include_template($C_template_config);
                if($U_tempfile) {if($GLOBALS['C']['Debug']) {include($U_tempfile);}else {@include($U_tempfile);}}
            }
        }
    }else {
        $C_template_config['filedir']=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['ClassDir'].DIRECTORY_SEPARATOR.$C_template_config['class'].DIRECTORY_SEPARATOR.$C_template_config['dir'];
        $C_template_config['code']=$Temp_file;
        $U_tempfile=include_template($C_template_config);
        if($U_tempfile) {if($GLOBALS['C']['Debug']) {include($U_tempfile);}else {@include($U_tempfile);}}
    }
    if(isset($GLOBALS['class_config'][$Temp_classhash]['template_class']) && $GLOBALS['class_config'][$Temp_classhash]['template_class']!=$Temp_classhash) {
        array_pop($GLOBALS['C']['runing_class']);
    }
    array_pop($GLOBALS['C']['runing_class']);
    Return true;
}
function P($do,$classhash=false,$userid=false) {
    if($classhash) {
        $do=$classhash.':'.$do;
    }else {
        $do=now_class().':'.$do;
    }
    Return C('admin:check',$do,$userid);
}
function L($name,$language='',$classhash='') {
    if(empty($classhash)) {$classhash=now_class();}
    if(empty($language) && isset($_COOKIE['u_language_'.$classhash])) {
        $language=$_COOKIE['u_language_'.$classhash];
    }
    if(empty($language) && isset($_COOKIE['u_language'])) {
        $language=$_COOKIE['u_language'];
    }
    if(!isset($GLOBALS['class_config'][$classhash])) {
        C($classhash);
    }
    if(isset($GLOBALS['class_config'][$classhash]['language'])) {
        $class_languages=explode(';',$GLOBALS['class_config'][$classhash]['language']);
    }else {
        $class_languages=array();
    }
    if(empty($language) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $_SERVER['HTTP_ACCEPT_LANGUAGE']=strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $HTTP_ACCEPT_LANGUAGE=explode(';q=',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach($class_languages as $class_language) {
            if(stripos($HTTP_ACCEPT_LANGUAGE[0],$class_language)===false) {
            }else {
                $language=$class_language;
                break;
            }
        }
    }
    if(!in_array($language,$class_languages)) {
        if(count($class_languages)>0) {
            $language=$class_languages[0];
        }else {
            Return false;
        }
    }
    if(empty($language)) {
        Return false;
    }
    if(!isset($GLOBALS['class_language'][$classhash][$language])) {
        $GLOBALS['class_language'][$classhash][$language]=require_once(classDir($classhash).'language'.DIRECTORY_SEPARATOR.$language.'.php');
    }
    if(isset($GLOBALS['class_language'][$classhash][$language][$name])) {
        Return $GLOBALS['class_language'][$classhash][$language][$name];
    }else {
        Return false;
    }
}
function class_getParameters($args) {
    $class=explode(':',$args['class']);
    if(count($class)>2) {
        if(!class_exists($classhash.'_'.$class[1])) {
            if($GLOBALS['C']['Debug']) {
                include_once(classDir($class[0]).$class[1].'.php');
            }else {
                @include_once(classDir($class[0]).$class[1].'.php');
            }
        }
        $classhash=$class[0];
        $classname=$classhash.'_'.$class[1];
        $functionname=$class[2];
    }else {
        if(!class_exists($class[0])) {
            if($GLOBALS['C']['Debug']) {
                include_once(classDir($class[0]).DIRECTORY_SEPARATOR.$class[0].'.php');
            }else {
                @include_once(classDir($class[0]).DIRECTORY_SEPARATOR.$class[0].'.php');
            }
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
function route($routehash,$classhash='') {
    if(empty($classhash)) {
        $classhash=now_class();
    }
    if(isset($GLOBALS['route'])) {
        foreach($GLOBALS['route'] as $thisroute) {
            if(isset($thisroute['classhash']) && isset($thisroute['modulehash']) && isset($thisroute['hash']) && empty($thisroute['modulehash']) && $thisroute['classhash']==$classhash && $thisroute['hash']==$routehash) {
                $route=$thisroute;
                break;
            }
        }
    }
    if(!isset($route) || !$route) {Return '';}
    $route['uri']=rewriteUri($route['uri']);
    if(!isset($route['domain']) || empty($route['domain'])) {
        $route['domain']=$GLOBALS['C']['Domain'];
    }
    if(macthDomain($route['domain'])) {
        Return $route['uri'];
    }
    $domains=explode(';',strtolower($route['domain']));
    foreach($domains as $domain) {
        if(stripos($domain,'*')===false) {
            break;
        }
    }
    Return '//'.$domain.server_port().$route['uri'];
}
function matchUri($uri) {
    unset($GLOBALS['C']['GET']);
    if(substr_count($uri,'/')!=substr_count($GLOBALS['C']['uri'],'/')) {
        Return false;
    }
    $uri=uridecode(urlencode($uri));
    if(strpos($uri,')')===false) {
        if($uri==$GLOBALS['C']['uri']) {
            Return true;
        }else {
            Return false;
        }
    }
    preg_match_all('/[(](.*)[)]/U',$uri,$getarray);
    if(count($getarray)>0) {
        $uri=str_replace(array('/','?','($id)','($.id)'),array('\\/','\?','([1-9][0-9]*)','([1-9][0-9]*)'),$uri);
        foreach($getarray[0] as $getkey=>$getval) {
            $uri=str_replace($getval,'(.+?)',$uri);
        }
        @preg_match_all('/class-cms-uri-start-'.$uri.'-class-cms-uri-end/','class-cms-uri-start-'.$GLOBALS['C']['uri'].'-class-cms-uri-end',$ifmatch);
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
        $classhash=now_class();
    }
    if(empty($classhash)) {
        Return $GLOBALS['C']['SystemRoot'].$GLOBALS['C']['ClassDir'].DIRECTORY_SEPARATOR;
    }
    Return $GLOBALS['C']['SystemRoot'].$GLOBALS['C']['ClassDir'].DIRECTORY_SEPARATOR.$classhash.DIRECTORY_SEPARATOR;
}
function uridecode($uri) {
    Return str_replace(array('%28','%29','%7B','%7D','%5B','%5D','%2F','%3F','%3D','%26','+','%25','%24'),array('(',')','{','}','[',']','/','?','=','&','%20','%','$'),$uri);
}
function template_url($classhash='') {
    if(empty($classhash)) {
        $classhash=now_class();
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
    if(isset($template_config['code'])) {
        $template_config['file']=md5($template_config['code']);
    }
    $cachefile=dir_template($template_config['file'],'template'.DIRECTORY_SEPARATOR.$template_config['class']);
    $cachefiletime=@filemtime($cachefile);
    if(($cachefiletime+$template_config['cache'])>time()) {
        Return $cachefile;
    }else {
        $content=cms_template($template_config);
        if($content===false) {
            $content='template file not found: '.str_replace($GLOBALS['C']['SystemRoot'],DIRECTORY_SEPARATOR,$template_config['file']);
            if(stripos($template_config['file'],'.php')===false){$content.='.php ';}
        }
        $cached=save_template($template_config['file'],$content,$template_config['cache'],'template'.DIRECTORY_SEPARATOR.$template_config['class']);
        if($cached && $content) {
            Return $cachefile;
        }
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
    Return $GLOBALS['C']['SystemRoot'].$GLOBALS['C']['CacheDir'].DIRECTORY_SEPARATOR.$keykind.substr($md5,8,20).'.html';
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
        if(stripos($template_config['file'],'.php')===false) {$template_config['file'].='.php';$template_config['template'].='.php';}
        $templatecontent=@file_get_contents($template_config['file']);
    }
    if($templatecontent===false) {Return false;}
    $templatecontent=str_ireplace(array('</head>','</body>'),array('{cms:head:~('.$template_config['class'].')}</head>','{cms:body:~('.$template_config['class'].')}</body>'),$templatecontent);
    if(!isset($GLOBALS['C']['template_var']['host'])) {$GLOBALS['C']['template_var']['host']=server_name();}
    if(!isset($GLOBALS['C']['template_var']['cmsdir'])) {$GLOBALS['C']['template_var']['cmsdir']=$GLOBALS['C']['SystemDir'];}
    if(!isset($GLOBALS['C']['template_var']['br'])) {$GLOBALS['C']['template_var']['br']="\r\n";}
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
            if(substr($thisothertemp,0,1)=='$') {
                $thisothertemp=cms_template_varname($thisothertemp,$template_config['class']);
                foreach($template_config_new as $template_config_new_key=>$template_config_new_val) {
                    $template_config_new[$template_config_new_key]=str_replace('\\','\\\\',$template_config_new_val);
                }
                $templist[1][$key]="include(include_template(array('template'=>{$thisothertemp},'file'=>\"{$template_config_new['filedir']}\".{$thisothertemp},'cache'=>\"{$template_config_new['cache']}\",'class'=>\"{$template_config_new['class']}\",'dir'=>\"{$template_config_new['dir']}\",'httpdir'=>\"{$template_config_new['httpdir']}\",'filedir'=>\"{$template_config_new['filedir']}\")));";
            }else {
                $template_config_new['file']=$template_config_new['filedir'].$thisothertemp;
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
                        if(!empty($iffunction[1][0])) {
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
    if(version_compare(PHP_VERSION,'5.4','<')){if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()){if (isset($_GET)){$_GET=_stripslashes_deep($_GET);}if (isset($_POST)){$_POST=_stripslashes_deep($_POST);}if (isset($_COOKIE)){$_COOKIE=_stripslashes_deep($_COOKIE);}}}@header(str_replace('U','Class','X-Powered-By:UCMS'));
}
function _stripslashes_deep($value) {
    if (empty($value)){return $value;}else{return is_array($value) ? array_map('_stripslashes_deep', $value) : stripslashes($value);}
}
function is_hash($hash) {
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
function config($hash,$value=false,$classhash=false) {
    if($classhash===false) {
        $classhash=now_class();
    }
    if($value===false) {
        Return C('cms:config:get',$hash,$classhash);
    }else {
        Return C('cms:config:set',$hash,$value,0,$classhash);
    }
}
function nav($cid=0,$size=999999,$classhash=''){
    if(empty($classhash)) {$classhash=now_class();}
    if($cid) {
        if(!$channel=C('cms:channel:get',$cid,$classhash)) {
            Return array();
        }
        $cid=$channel['id'];
    }
    $channels=C('cms:channel:all',$cid,$classhash,$size,1,1);
    $parents=array();
    if(isset($GLOBALS['C']['channel']['id'])) {
        $parents[]=$GLOBALS['C']['channel']['id'];
        if(isset($GLOBALS['C']['channel']['fid']) && $GLOBALS['C']['channel']['fid']>0) {
            $parents_channels=C('cms:channel:parents',$GLOBALS['C']['channel']['id'],$classhash);
            foreach($parents_channels as $parents_channel) {
                $parents[]=$parents_channel['id'];
            }
        }
    }
    foreach($channels as $key=>$channel) {
        if(in_array($channel['id'],$parents)) {
            $channels[$key]['active']=1;
        }else {
            $channels[$key]['active']=0;
        }
        
    }
    Return $channels;
}
function bread($cid=0,$classhash=''){
    if(empty($classhash)) {
        $classhash=now_class();
    }
    if(!$cid && isset($GLOBALS['C']['channel'])) {
        $channel=$GLOBALS['C']['channel'];
    }elseif($cid) {
        if(!$channel=C('cms:channel:get',$cid,$classhash)) {
            Return array();
        }
    }else {
        Return array();
    }
    $channels=C('cms:channel:parents',$channel['id'],$classhash);
    if($home=C('cms:channel:home',$classhash)) {
        array_unshift($channels,$home);
    }
    foreach($channels as $key=>$this_channel) {
        $channels[$key]['active']=0;
    }
    $channel['active']=1;
    $channels[]=$channel;
    Return $channels;
}
function text($html,$length=0,$ellipsis=''){
    Return C('cms:common:text',$html,$length,$ellipsis);
}
function addlog($msg) {
    Return C('cms:common:addlog',$msg);
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
function cms_createdir($path){
    Return C('cms:common:createDir',$path);
}
function server_name() {
    Return C('cms:common:serverName');
}
function server_port($colon=true) {
    Return C('cms:common:serverPort',$colon);
}
class cms_database {
    function __construct(){
        if(!isset($GLOBALS['C']['DbInfo']['showerror'])) {$GLOBALS['C']['DbInfo']['showerror']=@$GLOBALS['C']['Debug'];}
        if(!isset($GLOBALS['C']['DbInfo']['prefix'])) {$GLOBALS['C']['DbInfo']['prefix']='';}
        if(!isset($GLOBALS['C']['DbInfo']['engine'])) {$GLOBALS['C']['DbInfo']['engine']='MyISAM';}
        if(!isset($GLOBALS['C']['DbInfo']['charset'])) {$GLOBALS['C']['DbInfo']['charset']='utf8';}
        $db_info=$GLOBALS['C']['DbInfo'];
        $GLOBALS['C']['DbInfo']['querycount']=0;
        $this->kind=$db_info['kind'];
        if($db_info['kind']=='sqlitepdo'){
            $this->databaselink = new PDO('sqlite:' . $db_info['file']);
        }elseif($db_info['kind']=='mysqlpdo') {
            if(!isset($db_info['dbname']) || empty($db_info['dbname']) || isset($GLOBALS['C']['DbInfo']['createdb'])) {$dbinfo='';}else {$dbinfo='dbname='.$db_info['dbname'];}
            $db_info['hostinfo']=explode(':',$db_info['host']);
            if(count($db_info['hostinfo'])>1) {$db_info['host']=$db_info['hostinfo'][0];$db_info['port']=$db_info['hostinfo'][1];}else {$db_info['port']='3306';}
            try{
                @$this->databaselink = new PDO('mysql:host='.$db_info['host'].';port='.$db_info['port'].';'.$dbinfo,$db_info['user'],$db_info['password'],array(constant('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')=>true));
                $this->query("SET NAMES ".$GLOBALS['C']['DbInfo']['charset']);
            }catch(Exception $errinfo){
                $this->error('database connect error');
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
            }
            
        }else{
            $this->error('database error');
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
        if(count($args[0])==0) {
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
                if(is_array($val)) {
                    $this_sql.=$this->escape($name).' in(';
                    foreach($val as $key=>$this_val) {
                        if($key) {
                            $this_sql.=',\''.$this->escape($this_val).'\'';
                        }else {
                            $this_sql.='\''.$this->escape($this_val).'\'';
                        }
                    }
                    $this_sql.=')';
                }else {
                    $this_sql.=$this->escape($name).'=\''.$this->escape($val).'\'';
                }
            }
            if($symbol==';') {
                $this_sql.='(';
                if(is_array($val) && count($val)) {
                    $or=false;
                    foreach($val as $key=>$this_val) {
                        if(is_array($this_val)) {
                            if($or) {
                                $this_sql.=' or '.$this->where(array(array('1;'=>$this_val)));
                            }else {
                                $this_sql.=$this->where(array(array('1;'=>$this_val)));
                            }
                        }else {
                            if($or) {
                                $this_sql.=' or '.$this->where(array(array($key=>$this_val)));
                            }else {
                                $this_sql.=$this->where(array(array($key=>$this_val)));
                            }
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
                        if(substr($this_val,0,1)!='%' || substr($this_val,-1)!='%') {
                            $this_val='%'.$this_val.'%';
                        }
                        if($key) {
                            $this_sql.=' and '.$this->escape($name).' like \''.$this->escape($this_val).'\'';
                        }else {
                            $this_sql.=$this->escape($name).' like \''.$this->escape($this_val).'\'';
                        }
                    }
                }else {
                    if(substr($val,0,1)!='%' || substr($val,-1)!='%') {
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
        $this->query('SELECT count(*) FROM '.$this->prefix($this->escape($table)).' '.$where.'  limit 1');
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
        if(isset($strarray['order']) && !empty($strarray['order'])) {$order='order by '.$this->escape($strarray['order']);}else {$order='';}
        if(isset($strarray['offset']) && !empty($strarray['offset'])) {$offset=intval($strarray['offset']);}else {$offset='';}
        if(isset($strarray['column']) && !empty($strarray['column'])) {$column=$this->escape($strarray['column']);}else {$column='*';}
        $limitsql='';
        if(!empty($offset)) {$limitsql='limit '.$offset.',1';}
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
        if(isset($strarray['order']) && !empty($strarray['order'])) {$order='order by '.$this->escape($strarray['order']);}else {$order='';}
        if(isset($strarray['offset']) && !empty($strarray['offset'])) {$offset=intval($strarray['offset']);}else {$offset='';}
        if(isset($strarray['limit']) && !empty($strarray['limit'])) {$limit=intval($strarray['limit']);}else {$limit='';}
        if(isset($strarray['page'])) {$page=$strarray['page'];}else {$page='';}
        if(isset($strarray['optimize']) && $strarray['optimize']) {$optimize=$strarray['optimize'];}else {$optimize=false;}
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
            $aticlecount=$this->fetchone();
            $GLOBALS['C']['page']['article']=$aticlecount['count(*)'];
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
            foreach($fields as $fieldname=>$field) {
                if(is_array($field)) {
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
        $config=$this->escape($config);
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
        $config=$this->escape($config);
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
    function addIndex($table,$name){
        $table=$this->prefix($table);
        $name=$this->escape($name);
        if($this->kind=='sqlitepdo') {
            $this->query("CREATE INDEX {$table}__$name ON $table ($name);");
        }elseif($this->kind=='mysqlpdo' || $this->kind=='mysql') {
            $this->query("alter table $table add INDEX $name ($name);");
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
        if($this->kind=='mysql')
        {
            Return @mysql_fetch_assoc($this->Stmt);
        }else {
            return @$this->Stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    function lastId(){
        if($this->kind=='mysql')
        {
            return intval(mysql_insert_id());
        }else {
            return intval($this->databaselink->lastInsertId());
        }
    }
    function begin(){
        if(!method_exists($this->databaselink,'inTransaction')) {
            return false;
        }
        return $this->databaselink->beginTransaction();
    }
    function commit(){
        if(!method_exists($this->databaselink,'inTransaction')) {
            return false;
        }
        if($this->databaselink->inTransaction()) {
            return $this->databaselink->commit();
        }
        return false;
    }
    function rollback(){
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
        Return !in_array($fieldname,array('id','cid','uid','rowstyle','stepstyle','rowurl','csrf','link','like','add','all','alter','as','and','asc','before','between','bigint','binary','blob','both','by','call','cascade','case','change','char','check','column','create','cross','cursor','databases','database','dec','delete','default','desc','div','double','drop','each','else','elseif','exists','exit','explain','false','float','for','force','from','foreign','goto','group','if','in','index','inner','inout','insert','int','integer','into','is','join','key','kill','keys','left','limit','lines','load','lock','loop','long','mod','not','null','on','option','or','order','out','outer','outfile','primary','range','read','reads','real','set','show','sql','ssl','starting','then','table','to','undo','true','union','unlock','update','using','values','varchar','when','where','while','with','write'));
    }
}