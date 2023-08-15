<?php
if(!defined('ClassCms')) {exit();}
class admin {
    function init(){
        Return array(
            'template_dir' => 'template'
        );
    }
    function stop() {
        Return E('管理后台无法停用');
    }
    function uninstall() {
        Return E('管理后台无法卸载');
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
        }
        if(version_compare($old_version,'2.0','<')) {
            $auths=all('table','auth','where',where('hash%',':_module:'));
            foreach($auths as $auth) {
                update('table','auth','where',where('id',$auth['id']),'authkind',$auth['classhash'].':_'.str_replace('_',':',$auth['authkind']));
            }
        }
        if(version_compare($old_version,'2.3','<')) {
            C('cms:input:add','this:input:articleunlimit');
            C('cms:input:add','this:input:articletree');
        }
        if(version_compare($old_version,'3.8','<')) {
            C('cms:input:add','this:input:user');
            update('table','input','where',where('hash',array('userselect','usercheckbox','roleselect','rolecheckbox')),'groupname','用户');
        }
    }
    function auth() {
        $auth=array();
        $auth['基础权限']=C('this:my:auth');
        $auth['应用管理']=C('this:class:auth');
        $auth['模型管理']=C('this:module:auth');
        $auth['栏目管理']=C('this:channel:auth');
        $auth['用户管理']=C('this:user:auth');
        Return $auth;
    }
    function index() {
        Return V('index',array('title'=>C('this:title')));
    }
    function defaultPage() {
        if(isset($_GET['home']) && isset($GLOBALS['C']['admin']['load']) && $GLOBALS['C']['admin']['load']=='admin:index' && isset($_SERVER['QUERY_STRING'])){
            Return str_ireplace('?home=','?do=','?'.$_SERVER['QUERY_STRING']);
        }
        if(isset($GLOBALS['C']['admin']['defaultpage'])) {Return $GLOBALS['C']['admin']['defaultpage'];}
        if(P('class:index')) {Return '?do=admin:class:index';}
        C('this:leftMenu');
        if(isset($GLOBALS['C']['admin']['defaultpage'])) {Return $GLOBALS['C']['admin']['defaultpage'];}
        $userNavItems=C('this:userNavItems');
        if(isset($userNavItems[0][0])){
            Return $userNavItems[0][0];
        }
        Return '';
    }
    function load() {
        if(isset($_GET['do'])) {
            $do=$_GET['do'];
            if(is_array($do)){Return C('this:error','错误');}
        }else {
            $do='admin:index';
        }
        if(!$userid=C('this:nowUser')) {
            if(!C('this:nologinActionCheck',$do)){
                if(C('cms:common:isAjax')) {
                    Return C('this:ajax','已退出,请重新登入',1,1001);
                }
                $do='admin:login';
            }
        }
        $doclass=explode(':',$do);
        if(count($doclass)!=2 && count($doclass)!=3) {
            Return C('this:error','error');
        }
        $classinfo=C('cms:class:get',$doclass[0]);
        if(!$classinfo || !$classinfo['enabled']) {
            if($doclass[0]=='admin' && $doclass[1]=='login'){
                Return C('this:error','数据库连接失败');
            }
            if(C('cms:common:isAjax')) {
                Return C('this:ajax','无权限',1,1001);
            }else {
                Return C('this:error','无权限');
            }
        }
        if(!C('this:check',$do,$userid,true)) {
            if(C('cms:common:isAjax')) {
                Return C('this:ajax','无权限',1,1001);
            }
            Return C('this:error','无权限');
        }
        if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST' && !C('this:nologinActionCheck',$do) && !C('this:publicActionCheck',$do)) {
            if(!C('this:csrfCheck',1)) {
                if(C('cms:common:isAjax')) {
                    Return C('this:ajax','非法提交,请刷新当前页面或重新登入系统',1,1001);
                }else {
                    Return C('this:error','非法提交,请刷新当前页面或重新登入系统');
                }
            }
        }
        $GLOBALS['C']['admin']['load']=$do;
        $return=C($do);
        if($return===null || $return===true){
            Return true;
        }elseif(is_array($return)){
            Return C('this:ajax',$return);
        }elseif(is_string($return)){
            if(C('cms:common:isAjax')) {
                Return C('this:ajax',$return);
            }else {
                echo($return);
            }
        }elseif(!$return){
            $error='error';
            if(E()){
                $error=E();
            }
            if(C('cms:common:isAjax')) {
                Return C('this:ajax',$error,1);
            }else {
                Return C('this:error',$error);
            }
        }
        Return true;
    }
    function nologinActionCheck($do){
        $defaultActions=array('admin:login');
        $dos=explode(':',$do);
        foreach($defaultActions as $action) {
            $action=str_replace("*",end($dos),$action);
            if($do==$action) {
                Return true;
            }
        }
        Return false;
    }
    function publicActionCheck($do) {
        $defaultActions=array('admin:index','admin:logout','admin:article:*','admin:formAjax','admin:jumpHome','admin:loadMenu');
        $dos=explode(':',$do);
        foreach($defaultActions as $action) {
            $action=str_replace("*",end($dos),$action);
            if($do==$action) {
                Return true;
            }
        }
        Return false;
    }
    function loadMenu(){
        Return array('left'=>C('this:leftMenu'),'user'=>C('this:userNav'),'ico'=>C('this:icoNav'));
    }
    function check($do='',$userid=false,$admin_load=false) {
        if(C('this:nologinActionCheck',$do)) {
            Return true;
        }
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
        if(C('this:publicActionCheck',$do)) {
            Return true;
        }
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
        $userauth=array();
        $inputauth=C('cms:input:auth',array('inputhash'=>$form['inputhash']));
        $formkinds=array('column'=>'admin:column:index','var'=>'admin:var:index','info'=>'admin:info:index','config'=>'admin:class:setting');
        if(isset($form['kind']) && isset($formkinds[$form['kind']])) {
            if(C('this:check',$formkinds[$form['kind']])) {
                foreach($inputauth as $key=>$this_auth) {
                    if(stripos($key,'|false')===false) {
                        $userauth[$key]=true;
                    }else {
                        $userauth[$key]=false;
                    }
                }
                Return $userauth;
            }
        }
        foreach($inputauth as $key=>$this_auth) {
            $userauth[$key]=C('this:check',C('cms:form:authStr',$form,$key));
        }
        Return $userauth;
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
        if(!isset($return['error']) && !isset($return['_no_return_error'])){
            $return['error']=0;
        }
        if(isset($return['_no_return_error'])){
            unset($return['_no_return_error']);
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
            Return E('参数错误');
        }
        if(!C('this:csrfCheck',1)) {
            if(C('cms:common:isAjax')) {
                Return array('msg'=>'非法提交,请刷新当前页面或重新登入系统','error'=>1,'code'=>1001);
            }else {
                Return E('非法提交,请刷新当前页面或重新登入系统');
            }
        }
        if(!$form=C('cms:form:build',$formid)) {
            Return E('输入框不存在');
        }
        $form['auth']=C('this:formAuth',$form['id']);
        if(!isset($form['auth']['read']) || !$form['auth']['read']) {
            Return E('无权限');
        }
        Return C('cms:input:ajax',$form);
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
        if(!isset($child['tips'])) {$child['tips']='';}
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
            $html.='<a'.$targethtml.'><i class="layui-icon '.$child['ico'].'"></i>'.$child['title'];
            if($child['tips']){
                $html.='<span class="layui-badge">'.$child['tips'].'</span>';
            }
            $html.='</a>';
        }else {
            $html.='<li data-name="'.($child['title']).'" class="layui-nav-item '.$openhtml."\">\n";
            $html.='<a'.$targethtml.'><i class="layui-icon '.$child['ico'].'"></i><cite>'.$child['title'].'</cite>';
            if($child['tips']){
                $html.='<span class="layui-badge">'.$child['tips'].'</span>';
            }
            $html.='</a>';
        }
        $ifson=false;
        if(isset($child['child']) && is_array($child['child']) && count($child['child'])) {
            $sonhtml='';
            foreach($child['child'] as $this_child) {
                if($thishtml=C('this:childMenu',$classhash,$this_child,$times+1)){
                    $sonhtml.=$thishtml;
                    $ifson=true;
                }
            }
            if($ifson){
                $html.="\n<dl class=\"layui-nav-child\">\n".$sonhtml."</dl>\n";
            }
        }
        if($times) {$html.="</dd>\n";}else {$html.="</li>\n";}
        if(empty($child['url']) && !$ifson){
            return '';
        }
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
        if(C('cms:class:defaultClass')){
            $menu['child'][]=array('title'=>'模型管理','function'=>'module:index','ico'=>'layui-icon-template',);
            $menu['child'][]=array('title'=>'栏目管理','function'=>'channel:index','ico'=>'layui-icon-tabs',);
        }
        $menu['child'][]=array('title'=>'用户管理','function'=>'user:index','ico'=>'layui-icon-username',);
        Return $menu;
    }
    function jumpHome() {
        $classes=C('cms:class:all',1);
        foreach ($classes as $thisclass) {
            if($thisclass['module']){
                if($homepage=C('cms:homepage',$thisclass['hash'])){
                    break;
                }
            }
        }
        if(!isset($homepage) || !$homepage){
            $homepage=$GLOBALS['C']['SystemDir'];
        }
        echo('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
        echo("<meta name='referrer' content='never'><meta http-equiv=refresh content='0;url=".$homepage."'>");
        Return true;
    }
    function error($msg='error',$ico='&#xe664;') {
        if(is_array($msg)){
            if(!isset($msg['msg'])){ $msg['msg']='error'; } 
            if(!isset($msg['ico'])){ $msg['ico']='&#xe664;'; }
            Return V('error.php',$msg);
        }else{
            Return V('error.php',array('msg'=>$msg,'ico'=>$ico));
        }
    }
    function head($title='') {
        $headhtml="\n".'<meta charset="utf-8"><title>'.$title.'</title><meta name="renderer" content="webkit">'."\n";
        $headhtml.='<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="referrer" content="origin-when-cross-origin">'."\n";
        $headhtml.='<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=5">'."\n";
        $headhtml.=C('layui:css')."\n";
        $headhtml.=C('this:css');
        $headhtml.=C('layui:js')."\n";
        $headhtml.=C('this:csrfJs')."\n";
        $headhtml.='<script>layui.config({base: \''.template_url().'static/\',version:\'4.4\'}).extend({index: \'lib/index\'}).use([\'index\',\'form\'],function(){});</script>'."\n";
        Return $headhtml;
    }
    function css($check=1) {
        if($check && G('css')){return '';}
        G('css',1);
        Return '<link rel="stylesheet" href="'.template_url().'static/admin.css" media="all">'."\n";
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
    function breadcrumb($links='') {
        if(!$links){
            return '';
        }
        if(is_string($links)){
            return '<a><cite>'.$links.'</cite></a>';
        }
        $html='<a href="'.C('this:defaultPage').'"><i class="layui-icon layui-icon-home"></i></a>';
        $isLayuiForm=false;
        if(is_array($links)) {
            foreach($links as $key=>$val) {
                if(is_string($val)){$val=array('title'=>$val);}
                if(!isset($val['title'])) {
                    $val['title']='';
                }
                if(isset($val['function']) && !empty($val['function'])){
                    $functions=explode(':',$val['function']);
                    if(count($functions)==2){
                        if(!P($functions[1],$functions[0])){
                            $val['title']='';
                        }
                    }elseif(count($functions)==3){
                        if(!P($functions[1].':'.$functions[2],$functions[0])){
                            $val['title']='';
                        }
                    }
                    if(!isset($val['url']) || empty($val['url'])){
                        $val['url']='?do='.$val['function'];
                    }elseif(substr($val['url'],0,1)=='&'){
                        $val['url']='?do='.$val['function'].$val['url'];
                    }
                }
                if(($key+1)==count($links)) {
                    $val['url']='';
                }
                if(empty($val['title'])){
                    
                }elseif(isset($val['list']) && count($val['list'])>1){
                    $isLayuiForm=true;
                    $html.='<a><div class="layui-inline _classlist"><div class="layui-input-inline"><select lay-filter="breadcrumb_'.$key.'">';
                    foreach ($val['list'] as $thislist) {
                        if(is_string($thislist)){$thislist=array('title'=>$thislist);}
                        if(!isset($thislist['url'])){$thislist['url']='';}
                        if(!isset($thislist['title'])){$thislist['title']='';}
                        if(isset($thislist['function']) && !empty($thislist['function'])){
                            $functions=explode(':',$thislist['function']);
                            if(count($functions)==2){
                                if(!P($functions[1],$functions[0])){
                                    $thislist['title']='';
                                }
                            }elseif(count($functions)==3){
                                if(!P($functions[1].':'.$functions[2],$functions[0])){
                                    $thislist['title']='';
                                }
                            }
                            if(!isset($thislist['url']) || empty($thislist['url'])){
                                $thislist['url']='?do='.$thislist['function'];
                            }elseif(substr($thislist['url'],0,1)=='&'){
                                $thislist['url']='?do='.$thislist['function'].$thislist['url'];
                            }
                        }
                        if($thislist['url']==$val['url'] && $thislist['title']){
                            $html.='<option value="'.$thislist['url'].'" selected>'.$thislist['title'].'</option>';
                        }elseif($thislist['title']){
                            $html.='<option value="'.$thislist['url'].'">'.$thislist['title'].'</option>';
                        }
                    }
                    $html.='</select></div></div></a><script>layui.use([\'form\'],function(){layui.form.on(\'select(breadcrumb_'.$key.')\', function(data){window.location.href=data.value;});});</script>';
                }elseif(isset($val['url']) && !empty($val['url'])) {
                    $html.='<a href="'.$val['url'].'">'.$val['title'].'</a>';
                }else {
                    $html.='<a><cite>'.$val['title'].'</cite></a>';
                }
            }
        }
        if($isLayuiForm){
            $html='<span class="layui-breadcrumb layui-form" lay-separator="&gt;">'.$html.'</span>';
        }else{
            $html='<span class="layui-breadcrumb" lay-separator="&gt;">'.$html.'</span>';
        }
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
                Return E('请填写用户名');
            }
            if(!is_hash($_POST['userhash'])){
                Return E('用户名格式不正确');
            }
            if(empty($_POST['passwd'])) {
                Return E('请填写密码');
            }
            $check=C('cms:user:checkUser',$_POST['userhash'],$_POST['passwd']);
            if(is_array($check)){
                if($check['error']===0 && isset($check['userid'])) {
                    $userid=$check['userid'];
                }else{
                    Return E($check['msg']);
                }
            }elseif($check){
                $userid=$check;
            }else{
                Return E(E());
            }
            $token=C('cms:user:makeToken',$userid);
            if(C('this:adminCookie',$token)) {
                Return array('msg'=>'登入成功','token'=>$token,'csrf'=>C('this:csrfSet',1),'cookiehash'=>C('this:cookieHash'));
            }else {
                Return E('登入失败');
            }
        }
        if(C('this:nowUser')){return C('cms:common:jump','?do=admin:index');}
        Return V('login',$array);
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
            if(C('cms:common:isAjax')) {
                Return C('this:ajax','非法提交,请刷新当前页面或重新登入系统',1,1002);
            }
            Return C('this:error','非法提交,请刷新当前页面或重新登入系统');
        }
        C('this:adminCookie','');
        C('this:csrfSet','');
        if(C('cms:common:isAjax')) {
            Return C('this:ajax','');
        }
        Return C('cms:common:jump','?do=admin:login');
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
        if(version_compare(PHP_VERSION,'7.3.0','<')){
            setcookie('token'.C('this:cookieHash'), $token, 0,$GLOBALS['C']['SystemDir'],null,null,true);
        }else{
            setcookie('token'.C('this:cookieHash'), $token,array('expires'=>0,'path'=>$GLOBALS['C']['SystemDir'],'domain'=>null,'secure'=>null,'httponly'=>true));
        }
        Return true;
    }
    function csrfSet($value='') {
        if(version_compare(PHP_VERSION,'7.3.0','<')){
            if(empty($value)) {
                setcookie('csrf'.C('this:cookieHash'),'',0,$GLOBALS['C']['SystemDir'],null,null,true);
            }else {
                $value=substr(md5(@$GLOBALS['C']['SiteHash'].rand(10041989,19891004)),0,8);
                setcookie('csrf'.C('this:cookieHash'),$value,strtotime("+1 year"),$GLOBALS['C']['SystemDir'],null,null,true);
                Return $value;
            }
        }else{
            if(empty($value)) {
                setcookie('csrf'.C('this:cookieHash'),'',array('expires'=>0,'path'=>$GLOBALS['C']['SystemDir'],'domain'=>null,'secure'=>null,'httponly'=>true));
            }else {
                $value=substr(md5(@$GLOBALS['C']['SiteHash'].rand(10041989,19891004)),0,8);
                setcookie('csrf'.C('this:cookieHash'),$value,array('expires'=>strtotime("+1 year"),'path'=>$GLOBALS['C']['SystemDir'],'domain'=>null,'secure'=>null,'httponly'=>true));
                Return $value;
            }
        }
        Return true;
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