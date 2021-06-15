<?php
if(!defined('ClassCms')) {exit();}
class admin {
    function init(){
        Return array(
            'template_dir' => 'template'
        );
    }
    function stop() {
        Return '无法停止';
    }
    function uninstall() {
        Return '无法卸载';
    }
    function install() {
        if(!isset($GLOBALS['C']['install']) || $GLOBALS['C']['install']!==false) {
            Return false;
        }
        begin();
        update(array('table'=>'class','where'=>array('hash'=>__Class__),'menu'=>1));
        if(!C('cms:user:roleAdd',array('hash'=>'admin','rolename'=>'管理员','enabled'=>1))) {
            rollback();
            Return '管理员角色增加失败';
        }
        if(!C('cms:user:add',array('hash'=>$_POST['userhash'],'passwd'=>$_POST['passwd'],'username'=>$_POST['userhash'],'enabled'=>1,'rolehash'=>'admin'))) {
            rollback();
            Return '管理员增加失败';
        }
        C('this:input:text','test');
        $inputs=get_class_methods('admin_input');
        if(is_array($inputs)) {
            foreach($inputs as $key=>$input) {
                C('cms:input:add','this:input:'.$input);
            }
        }
        update(array('table'=>'input','where'=>array('groupname'=>'数据库'),'inputorder'=>5));
        update(array('table'=>'input','where'=>array('groupname'=>'系统'),'inputorder'=>10));
        commit();
    }
    function upgrade($old_version) {
        if(version_compare($old_version,'1.4','<')) {
            C('cms:input:add','this:input:databasetree');
            update(array('table'=>'input','where'=>array('hash'=>'databasetree'),'inputorder'=>5));
        }
    }
    function auth() {
        $auth=array();
        $auth['基础权限']=C('this:my:auth');
        $auth['应用管理']=C('this:class:auth');
        $auth['栏目管理']=C('this:channel:auth');
        $auth['模型管理']=C('this:module:auth');
        $auth['用户管理']=C('this:user:auth');
        Return $auth;
    }
    function index() {
        V('index',array('title'=>C('this:title')));
        Return true;
    }
    function defaultPage() {
        if(isset($GLOBALS['C']['admin']['defaultpage'])) {Return $GLOBALS['C']['admin']['defaultpage'];}
        if(P('class:index')) {Return '?do=admin:class:index';}
        C('this:leftMenu');
        if(isset($GLOBALS['C']['admin']['defaultpage'])) {Return $GLOBALS['C']['admin']['defaultpage'];}
        if(P('my:info')) {
            $infos=C('cms:form:all','info');
            $infos=C('cms:form:getColumnCreated',$infos,'user');
            if(count($infos)) {
                foreach($infos as $key=>$info) {
                    if($infos[$key]['enabled']) {
                        $infos[$key]=C('cms:form:build',$info['id']);
                        $infos[$key]['auth']=C('this:formAuth',$info['id']);
                        if($infos[$key]['auth']['read']) {
                            Return '?do=admin:my:info';
                        }
                    }
                }
            }
        }
        if(P('my:edit')) {Return '?do=admin:my:edit';}
        Return '';
    }
    function load() {
        if(isset($_GET['do'])) {
            $do=$_GET['do'];
        }else {
            $do='admin:index';
        }
        $doclass=explode(':',$do);
        if(count($doclass)!=2 && count($doclass)!=3) {
            Return C('this:error','error');
        }
        $classinfo=C('cms:class:get',$doclass[0]);
        if(!$classinfo || !$classinfo['enabled']) {
            if(C('cms:common:isAjax')) {
                Return C('this:ajax','应用已停用',1,1001);
            }else {
                Return C('this:error','应用已停用');
            }
        }
        if(!$userid=C('this:nowUser')) {
            if(C('cms:common:isAjax') && $do!=='admin:login') {
                Return C('this:ajax','已退出,请重新登入',1,1001);
            }
            $do='admin:login';
        }
        $GLOBALS['C']['admin']['load']=$do;
        if(!C('this:check',$do,$userid,true)) {
            if(C('cms:common:isAjax')) {
                Return C('this:ajax','无权限',1,1001);
            }
            Return C('this:error','无权限');
        }
        if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST' && !C('this:publicActionCheck',$do)) {
            if(!C('this:csrfCheck',1)) {
                if(C('cms:common:isAjax')) {
                    Return C('this:ajax','非法提交,请刷新当前页面或重新登入系统',1,1001);
                }else {
                    Return C('this:error','非法提交,请刷新当前页面或重新登入系统');
                }
            }
        }
        C($do);
        Return true;
    }
    function publicActionCheck($do) {
        $defaultaction=array('admin:login','admin:index','admin:logout','admin:article:*','admin:formAjax','admin:jumpHome');
        if(isset($GLOBALS['C']['admin']['publicAction'])) {
            foreach($defaultaction as $action) {
                if(!in_array($action,$GLOBALS['C']['admin']['publicAction'])) {
                    $GLOBALS['C']['admin']['publicAction'][]=$action;
                }
            }
        }else {
            $GLOBALS['C']['admin']['publicAction']=$defaultaction;
        }
        $dos=explode(':',$do);
        foreach($GLOBALS['C']['admin']['publicAction'] as $action) {
            $action=str_replace("*",end($dos),$action);
            if($do==$action) {
                Return true;
            }
        }
        Return false;
    }
    function check($do='',$userid=false,$admin_load=false) {
        if(C('this:publicActionCheck',$do)) {
            Return true;
        }
        if(!$userid) {
            if(!$userid=C('this:nowUser')) {
                Return false;
            }
        }
        if(!isset($GLOBALS['C']['admin']['user'][$userid])) {
            $GLOBALS['C']['admin']['user'][$userid]=C('cms:user:get',$userid);
        }
        Return C('this:rolesCheck',$do,$GLOBALS['C']['admin']['user'][$userid]['rolehash'],true,$admin_load);
    }
    function rolesCheck($do,$rolehashs,$check_role_enabled=true,$admin_load=false) {
        $rolehash_array=explode(';',$rolehashs);
        foreach($rolehash_array as $rolehash) {
            if(C('this:roleCheck',$do,$rolehash,$check_role_enabled,$admin_load)) {
                Return true;
            }
        }
        Return false;
    }
    function roleCheck($do='',$rolehash='',$check_role_enabled=true,$admin_load=false) {
        if(!isset($GLOBALS['C']['admin']['role'][$rolehash])) {
            $GLOBALS['C']['admin']['role'][$rolehash]=C('cms:user:roleGet',$rolehash);
        }
        if(!$GLOBALS['C']['admin']['role'][$rolehash]) {
            Return false;
        }
        if($GLOBALS['C']['admin']['role'][$rolehash]['hash']==C('cms:user:$admin_role')) {
            if($admin_load) {
                Return C('this:adminLoadAuth',$do);
            }
            if(stripos($do,'|false')===false) {
                Return true;
            }else {
                Return false;
            }
        }
        if($check_role_enabled && isset($GLOBALS['C']['admin']['role'][$rolehash]['enabled']) && !$GLOBALS['C']['admin']['role'][$rolehash]['enabled']) {
            Return false;
        }
        Return C('cms:user:authGet',$do,$GLOBALS['C']['admin']['role'][$rolehash]['hash']);
    }
    function actionsCheck($dos='',$rolehash='') {
        $dos_array=explode(';',$dos);
        foreach($dos_array as $do) {
            if(C('this:roleCheck',$do,$rolehash,false)) {
                Return true;
            }
        }
        Return false;
    }
    function adminLoadAuth($hash) {
        $admin_class=explode(':',$hash);
        if(!is_hash($admin_class[0])) {
            Return false;
        }
        $class_auth=C($admin_class[0].':auth');
        if(is_array($class_auth)) {
            $all_auth='';
            foreach($class_auth as $auth_key=>$auth_name) {
                if(is_array($auth_name)) {
                    foreach($auth_name as $auth_key_2=>$auth_name) {
                        $all_auth.=$auth_key_2.';';
                    }
                }else {
                    $all_auth.=$auth_key.';';
                }
            }
            $auths=explode(';',$all_auth);
            foreach($auths as $auth) {
                if(stripos($auth,'|false')===false) {
                    if($admin_class[0].':'.$auth===$hash) {
                        Return true;
                    }
                }else {
                    if($admin_class[0].':'.$auth===$hash) {
                        Return false;
                    }
                }
            }
        }
        Return false;
    }
    function moduleAuth($module,$action='',$userid=false) {
        if(stripos($action,'|false')===false) {
            if(C('this:check','admin:module:permission',$userid)) {Return true;}
        }else {
            if(C('this:check','admin:module:permission',$userid)) {Return false;}
        }
        Return C('this:check',C('cms:module:authStr',$module,$action),$userid);
    }
    function formAuth($formid) {
        if(!$form=C('cms:form:get',$formid)) {
            Return array();
        }
        $user_auth=array();
        $default_auth=array('read'=>'','write'=>'');
        $form_auth=C('cms:input:auth',array('inputhash'=>$form['inputhash']));
        $all_auth=array_merge($default_auth,$form_auth);
        $formkinds=array('column','var','info');
        if(isset($form['kind']) && in_array($form['kind'],$formkinds)) {
            if(C('this:check','admin:'.$form['kind'].':index')) {
                foreach($all_auth as $key=>$this_auth) {
                    if(stripos($key,'|false')===false) {
                        $user_auth[$key]=true;
                    }else {
                        $user_auth[$key]=false;
                    }
                }
                Return $user_auth;
            }
        }
        foreach($all_auth as $key=>$this_auth) {
            $user_auth[$key]=C('this:check',C('cms:form:authStr',$form,$key));
        }
        Return $user_auth;
    }
    function ajax() {
        $return=array();
        $args=func_get_args();
        if(count($args)>1) {
            if(isset($args[0])) {$return['msg']=$args[0];}
            if(isset($args[1])) {$return['error']=$args[1];}
            if(isset($args[2])) {$return['code']=$args[2];}
        }else {
            if(!is_array($args[0])) {
                $return['msg']=$args[0];
            }else {
                $return=$args[0];
            }
        }
        if(C('cms:common:echoJson',$return)) {
            Return true;
        }
        echo(json_encode($return));
        Return true;
    }
    function formAjax() {
        if(isset($_POST['classcms_form_id'])) {
            $formid=intval(@$_POST['classcms_form_id']);
        }elseif(isset($_GET['classcms_form_id'])) {
            $formid=intval(@$_GET['classcms_form_id']);
        }else {
            Return C('this:ajax','参数错误',1);
        }
        if(!C('this:csrfCheck',1)) {
            if(C('cms:common:isAjax')) {
                Return C('this:ajax','非法提交,请刷新当前页面或重新登入系统',1,1001);
            }else {
                Return C('this:error','非法提交,请刷新当前页面或重新登入系统');
            }
        }
        if(!$form=C('cms:form:build',$formid)) {
            Return C('this:ajax','输入框不存在',1);
        }
        $form['auth']=C('this:formAuth',$form['id']);
        if(!isset($form['auth']['read']) || !$form['auth']['read']) {
            Return C('this:ajax','无权限',1);
        }
        $ajax=C('cms:input:ajax',$form);
        Return C('this:ajax',$ajax);
    }
    function leftMenu() {
        $classes=C('cms:class:all');
        $html='';
        foreach($classes as $key=>$class) {
            if($class['enabled'] && $class['menu']) {
                $this_menu=C($class['hash'].':menu');
                if(!$this_menu && $this_menu!==false) {
                    if($class['adminpage'] && P($class['adminpage'],$class['hash'])) {
                        $this_menu=array('title'=>$class['classname'],'url'=>'?do='.$class['hash'].':'.$class['adminpage'],'ico'=>$class['ico']);
                    }elseif(P('class:config')) {
                        $this_menu=array('title'=>$class['classname'],'url'=>'?do=admin:class:config&hash='.$class['hash'],'ico'=>$class['ico']);
                    }
                }
                if($this_menu) {
                    if(isset($this_menu['child']) && is_array($this_menu['child']) && count($this_menu)===1) {
                        foreach($this_menu['child'] as $this_child) {
                            $html.=C('this:childMenu',$class['hash'],$this_child);
                        }
                    }else {
                        if(!isset($this_menu['title'])) {$this_menu['title']=$class['classname'];}
                        if(!isset($this_menu['ico'])) {$this_menu['ico']=$class['ico'];}
                        $html.=C('this:childMenu',$class['hash'],$this_menu);
                    }
                }
            }
        }
        Return $html;
    }
    function childMenu($classhash,$child,$times=0) {
        $html='';
        if(!isset($child['url'])) {$child['url']='';}
        if(!isset($child['title'])) {$child['title']='unknown';}
        if(!isset($child['ico'])) {$child['ico']='';}
        if(!isset($child['function'])) {$child['function']='';}
        if(!isset($child['classhash'])) {$child['classhash']=$classhash;}
        if(!isset($child['target'])) {$child['target']=false;}
        if(!isset($child['open'])) {
            if($times) {
                $child['open']=false;
            }else {
                $child['open']=true;
            }
        }
        if(!empty($child['function'])) {
            if(!P($child['function'],$child['classhash'])) {Return '';}
            if(empty($child['url'])) {
                $child['url']='?do='.$child['classhash'].':'.$child['function'];
            }
        }
        if(!isset($GLOBALS['C']['admin']['defaultpage']) && !empty($child['url']) && $child['url']!='javascript:;') {
            $GLOBALS['C']['admin']['defaultpage']=$child['url'];
        }
        if($child['target']) {
            $targethtml=' target="_blank" href="'.$child['url'].'"';
        }else {
            $targethtml=' lay-href="'.$child['url'].'"';
        }
        if($child['open']) {
            $openhtml='layui-nav-itemed';
        }else {
            $openhtml='';
        }
        if($times) {
            $html.='<dd data-name="'.($child['title']).'" class="'.$openhtml.'">';
            $html.='<a'.$targethtml.'><i class="layui-icon '.$child['ico'].'"></i>'.$child['title'].'</a>';
        }else {
            $html.='<li data-name="'.($child['title']).'" class="layui-nav-item '.$openhtml.'">'.PHP_EOL;
            $html.='<a'.$targethtml.'><i class="layui-icon '.$child['ico'].'"></i><cite>'.$child['title'].'</cite></a>';
        }
        if(isset($child['child']) && is_array($child['child']) && count($child['child'])) {
            $html.=PHP_EOL.'<dl class="layui-nav-child">'.PHP_EOL;
            foreach($child['child'] as $this_child) {
                $html.=C('this:childMenu',$classhash,$this_child,$times+1);
            }
            $html.='</dl>'.PHP_EOL;
        }
        if($times) {$html.='</dd>'.PHP_EOL;}else {$html.='</li>'.PHP_EOL;}
        Return $html;
    }
    function menu() {
        if(!P('showleftmenu')) {
            Return false;
        }
        if(P('class:index') || P('module:index') || P('channel:index') || P('user:index') ) {
            $menu=array('title'=>'管理','ico'=>'layui-icon-set',);
        }else {
            Return false;
        }
        $menu['child']=array();
        $menu['child'][]=array('title'=>'应用管理','function'=>'class:index','ico'=>'layui-icon-app',);
        $menu['child'][]=array('title'=>'模型管理','function'=>'module:index','ico'=>'layui-icon-template',);
        $menu['child'][]=array('title'=>'栏目管理','function'=>'channel:index','ico'=>'layui-icon-tabs',);
        $menu['child'][]=array('title'=>'用户管理','function'=>'user:index','ico'=>'layui-icon-username',);
        Return $menu;
    }
    function jumpHome() {
        $homepage=C('cms:homepage');
        if(empty($homepage) || $homepage=='#') {
            $homepage=$GLOBALS['C']['SystemDir'];
        }
        echo('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
        echo("<meta name='referrer' content='never'><meta http-equiv=refresh content='0;url=".$homepage."'>");
        Return true;
    }
    function error($msg='error',$ico='&#xe664;') {
        V('error.php',array('msg'=>$msg,'ico'=>$ico));
        Return true;
    }
    function head($title='') {
        $headhtml=PHP_EOL.'<meta charset="utf-8"><title>'.$title.'</title><meta name="renderer" content="webkit">'.PHP_EOL;
        $headhtml.='<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="referrer" content="origin-when-cross-origin">'.PHP_EOL;
        $headhtml.='<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">'.PHP_EOL;
        $headhtml.=C('layui:css').PHP_EOL;
        $headhtml.=C('this:css');
        $headhtml.=C('layui:js').PHP_EOL;
        $headhtml.=C('this:csrfJs').PHP_EOL;
        $headhtml.='<script>layui.config({base: \''.template_url().'static/\'}).extend({index: \'lib/index\'}).use([\'index\',\'form\'],function(){});</script>'.PHP_EOL;
        Return $headhtml;
    }
    function css() {
        Return '<link rel="stylesheet" href="'.template_url().'static/admin.css" media="all">'.PHP_EOL;
    }
    function icoNav() {
        Return '<li class="layui-nav-item layadmin-flexible" lay-unselect><a href="javascript:;" layadmin-event="flexible" title="侧边伸缩"><i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i></a></li><li class="layui-nav-item" lay-unselect><a href="?do=admin:jumpHome" target="_blank" title="主页"><i class="layui-icon layui-icon-website"></i></a></li><li class="layui-nav-item" lay-unselect><a href="javascript:;" layadmin-event="refresh" title="刷新"><i class="layui-icon layui-icon-refresh-3"></i></a></li>';
    }
    function userNav() {
        $userid=C('this:nowUser');
        $userinfo=C('cms:user:get',$userid);
        if(!isset($userinfo['username'])) {
            $userinfo['username']='';
        }
        $html='<li class="layui-nav-item" lay-unselect><a href="javascript:;"><cite>'.$userinfo['username'].'</cite></a><dl class="layui-nav-child">';
        $items=C('this:userNavItems');
        foreach($items as $item) {
            $html.='<dd><a lay-href="'.$item[0].'">'.$item[1].'</a></dd>';
        }
        $html.='<dd><a href="javascript:;" layadmin-event="logout">退出</a></dd></dl></li>';
        Return $html;
    }
    function userNavItems() {
        $items=array();
        if(P('my:info')) {
            $infos=C('cms:form:all','info');
            $infos=C('cms:form:getColumnCreated',$infos,'user');
            foreach($infos as $key=>$info) {
                $thisauth=C('this:formAuth',$info['id']);
                if(isset($thisauth['read']) && $thisauth['read']) {
                    $items[]=array('?do=admin:my:info','个人资料');
                    break;
                }
            }
        }
        if(P('my:edit')) {
            $items[]=array('?do=admin:my:edit','账号管理');
        }
        Return $items;
    }
    function breadcrumb($links='',$home='') {
        $html='<span class="layui-breadcrumb" lay-separator="&gt;">';
        if(empty($home)) {
            $html.='<a href="'.C('this:defaultPage').'"><i class="layui-icon layui-icon-home"></i></a>';
        }else {
            $html.=$home;
        }
        if(is_array($links)) {
            foreach($links as $key=>$val) {
                if(is_string($val)){$val=array('title'=>$val);}
                if(!isset($val['title'])) {
                    $val['title']='<i class="layui-icon layui-icon-align-left"></i>';
                }
                if(($key+1)==count($links)) {
                    $val['url']='';
                }
                if(isset($val['url']) && !empty($val['url'])) {
                    $html.='<a href="'.$val['url'].'">'.$val['title'].'</a>';
                }else {
                    $html.='<a><cite>'.$val['title'].'</cite></a>';
                }
            }
        }
        $html.='</span>';
        Return $html;
    }
    function pagelist() {
        $pagelist=pagelist();
        $pageinfo=pageinfo();
        if(!isset($pageinfo['pagecount'])) {Return ;}
        $pagehtml='<div id="cms-pagelist"><div class="pagelist">';
        if($pageinfo['pagecount']>1) {
            $firstshow=true;
            $lastshow=true;
            foreach($pagelist as $thispage) {
                if($thispage['page']==1) {$firstshow=false;}
                if($thispage['page']==$pageinfo['pagecount']) {$lastshow=false;}
            }
            if($firstshow) {
                $pagehtml.='<a href="'.$pageinfo['first']['link'].'" class="'.$pageinfo['first']['class'].'">1</a>';
                if($pagelist[0]['page']==3) {
                    $pagehtml.='<a href="'.str_replace($pageinfo['replace'],2,$pageinfo['url']).'" class="'.$pagelist[0]['class'].'">2</a>';
                }elseif($pagelist[0]['page']-1>1){
                    $pagehtml.='<a href="javascript:;" class="">...</a>';
                }
            }
            foreach($pagelist as $key=>$page) {
                $pagehtml.='<a href="'.$page['link'].'" class="'.$page['class'].'">'.$page['title'].'</a>';
            }
            if($lastshow) {
                if($pageinfo['pagecount']-$pagelist[count($pagelist)-1]['page']==2) {
                    $pagehtml.='<a href="'.str_replace($pageinfo['replace'],$pagelist[count($pagelist)-1]['page']+1,$pageinfo['url']).'" class="'.$page['class'].'">'.($pagelist[count($pagelist)-1]['page']+1).'</a>';
                }elseif($pageinfo['pagecount']-$pagelist[count($pagelist)-1]['page']>1){
                    $pagehtml.='<a href="javascript:;" class="">...</a>';
                }
                $pagehtml.='<a href="'.$pageinfo['last']['link'].'" class="'.$pageinfo['last']['class'].'">'.$pageinfo['last']['page'].'</a>';
            }
        }
        $pagehtml.='</div></div>';
        Return $pagehtml;
    }
    function login() {
        $array['msg']='';
        if(isset($_POST['userhash']) && isset($_POST['passwd'])) {
            if(empty($_POST['userhash'])) {
                Return C('this:ajax','请填写用户名',1);
            }
            if(empty($_POST['passwd'])) {
                Return C('this:ajax','请填写密码',1);
            }
            $check=C('cms:user:checkUser',$_POST['userhash'],$_POST['passwd']);
            if($check['error']===0 && isset($check['userid'])) {
                $token=C('cms:user:makeToken',$check['userid']);
                if(C('this:adminCookie',$token)) {
                    C('this:csrfSet',1);
                    Return C('this:ajax','登入成功');
                }else {
                    Return C('this:ajax','登入失败',1);
                }
            }else {
                Return C('this:ajax',$check['msg'],1);
            }
        }
        V('login',$array);
    }
    function title() {
        Return 'ClassCMS-后台管理';
    }
    function navTitle() {
        Return 'ClassCMS';
    }
    function loginTitle() {
        Return 'ClassCMS';
    }
    function loginCopyright() {
        //如需去除版权信息,请在应用商店内购买<<自定义版权信息>>应用
        Return ('<div class="layui-trans layadmin-user-login-footer"><p>© '.date('Y').' <a href="http://classcms.com" target="_blank">ClassCMS.com</a></p></div>');
    }
    function logout() {
        if(!C('this:csrfCheck',1)) {
            Return C('this:ajax','非法提交,请刷新当前页面或重新登入系统',1,1002);
        }
        C('this:adminCookie','');
        C('this:csrfSet','');
        Return C('this:ajax','');
    }
    function nowUser() {
        if(isset($GLOBALS['C']['admin']['nowuser'])) {
            Return $GLOBALS['C']['admin']['nowuser'];
        }
        $cookieHash=C('this:cookieHash');
        if (!isset($_COOKIE['token'.$cookieHash])  || empty($_COOKIE['token'.$cookieHash])){
            Return false;
        }
        if(!$userid=C('cms:user:checkToken',$_COOKIE['token'.$cookieHash])) {
            Return false;
        }
        $GLOBALS['C']['admin']['nowuser']=$userid;
        Return $userid;
    }
    function cookieHash() {
        Return '_'.substr(md5($GLOBALS['C']['SiteHash']),0,6);
    }
    function adminCookie($token) {
        setcookie('token'.C('this:cookieHash'), $token, 0,$GLOBALS['C']['SystemDir'],null,null,true);
        Return true;
    }
    function csrfSet($value='') {
        if(empty($value)) {
            setcookie('csrf'.C('this:cookieHash'),'',0,$GLOBALS['C']['SystemDir'],null,null,true);
        }else {
            $value=substr(md5(@$GLOBALS['C']['SiteHash'].rand(10041989,19891004)),0,8);
            setcookie('csrf'.C('this:cookieHash'),$value,strtotime("+1 year"),$GLOBALS['C']['SystemDir'],null,null,true);
        }
    }
    function csrfValue() {
        if(isset($_COOKIE['csrf'.C('this:cookieHash')])) {
            Return $_COOKIE['csrf'.C('this:cookieHash')];
        }
        Return '';
    }
    function csrfJs() {
        Return '<script>window.csrf="'.C('this:csrfValue').'";</script>';
    }
    function csrfForm($kind=0) {
        $csrf_value=C('this:csrfValue');
        if($kind==1) {
            Return '<input type="hidden" name="csrf" value="'.$csrf_value.'">';
        }elseif($kind==2) {
            Return 'csrf='.$csrf_value;
        }else {
            Return $csrf_value;
        }
    }
    function csrfCheck($return=0) {
        $errormsg='非法提交,请刷新当前页面或重新登入系统';
        $cookiecsrf=C('this:csrfValue');
        if(empty($cookiecsrf)) {Return true;}
        if(isset($_POST['csrf'])) {
            $csrf=$_POST['csrf'];
        }elseif(isset($_GET['csrf'])) {
            $csrf=$_GET['csrf'];
        }else {
            if(C('cms:common:isAjax')) {
                if(!$return) {
                    Return C('this:ajax',$errormsg,1,1002);
                }
                Return false;
            }else {
                if(!$return) {
                    C('this:error',$errormsg);
                }
                Return false;
            }
        }
        if($cookiecsrf==$csrf) {
            Return true;
        }else {
            if(C('cms:common:isAjax')) {
                if(!$return) {
                    Return C('this:ajax',$errormsg,1,1002);
                }
            }else {
                if(!$return) {
                    C('this:error',$errormsg);
                }
                Return false;
            }
        }
    }
}