<?php
if(!defined('ClassCms')) {exit();}
class admin_class {
    function auth() {
        Return array(
            'class:index'=>'查看应用列表',
            'class:config'=>'管理应用',
            'class:changeState'=>'启停应用',
            'class:install;class:uninstall;class:fileUpdate'=>'安装卸载',
            'class:permission;class:permissionPost'=>'权限设置',
            'class:setting;class:settingPost;class:order;class:menu'=>'应用设置',
        );
    }
    function index() {
        $classlist=C('cms:class:all');
        $newclass=array();
        if($classdirs=@scandir(classDir())) {
            if(is_array($classdirs)) {
                foreach($classdirs as $classdir) {
                    if(is_hash($classdir)) {
                        $ifin=false;
                        foreach($classlist as $class) {
                            if($class['hash']==$classdir) {
                                $ifin=true;
                            }
                        }
                        if(!$ifin && C('cms:class:refresh',$classdir)) {
                            $newclass[]=$classdir;
                        }
                    }
                    
                }
            }
            foreach($classlist as $class) {
                if(!$class['installed'] && !in_array($class['hash'],$classdirs)) {
                    $del_class=array();
                    $del_class['table']='class';
                    $del_class['where']=array('hash'=>$class['hash']);
                    del($del_class);
                }
            }
        }
        $array['classlist']=C('cms:class:all');
        $array['newclass']='';
        if(count($newclass)) {
            foreach($newclass as $this_new) {
                if($this_name=C('cms:class:config',$this_new,'name')){
                    $this_name=$this_name.'['.$this_new.']';
                }else{
                    $this_name=$this_new;
                }
                $array['newclass'].=' <a class="layui-btn layui-btn-xs layui-btn-normal" href="?do=admin:class:config&hash='.$this_new.'">'.$this_name.'</a>';
            }
        }
        Return V('class_index',$array);
    }
    function config() {
        $classhash=@$_GET['hash'];
        if(!is_hash($classhash)) {Return E('error');}
        C('cms:class:refresh',$classhash);
        if($array['classinfo']=C('cms:class:get',$classhash)) {
            if(!is_file(classDir($classhash).$classhash.'.php')) {
                if(!$array['classinfo']['installed']) {
                    $del_class=array();
                    $del_class['table']='class';
                    $del_class['where']=array('hash'=>$array['classinfo']['hash']);
                    del($del_class);
                    Return E('应用不存在,或此应用已卸载');
                }
                $array['filenotfound']=1;
                $array['new_version']=$array['classinfo']['classversion'];
                $array['description']='';
                $array['setting']=false;
                $array['phpcheck']='';
                $array['classinfo']['auth']=0;
                $array['classinfo']['installed']=0;
            }else {
                $array['filenotfound']=0;
                if(is_file(classDir($classhash).$classhash.'.config') && count(C('cms:class:config',$classhash))==0) {
                    Return E($classhash.'.config 文件解析错误');
                }
                $array['new_version']=C('cms:class:config',$array['classinfo']['hash'],'version');
                if($array['classinfo']['classversion']!=$array['new_version']) {
                    C('cms:common:opcacheReset');
                }
                $array['description']=C('cms:class:config',$array['classinfo']['hash'],'description');
                $array['setting']=total('form',where(array('classhash'=>$array['classinfo']['hash'],'kind'=>'config','enabled'=>1)));
                $array['phpcheck']='';
                if(!C('cms:class:phpCheck',$classhash)) {
                    $array['phpcheck']='无法使用,当前服务器PHP版本为:'.PHP_VERSION.',当前应用需要PHP版本为:'.C('cms:class:config',$classhash,'php');
                }
            }
            $requiredClasses=array();
            if($classes=C('cms:class:all')) {
                foreach($classes as $thisclass) {
                    if(!empty($thisclass['requires']) && $thisclass['installed']){
                        $requires=explode(';',$thisclass['requires']);
                        foreach ($requires as $require) {
                            @preg_match_all('/\[.*?\]/',$require,$requireversions);
                            if(isset($requireversions[0][0]) && rtrim($require,$requireversions[0][0])==$classhash){
                                $requiredClasses[$thisclass['id']]=$thisclass;
                            }elseif($require==$classhash){
                                $requiredClasses[$thisclass['id']]=$thisclass;
                            }
                        }
                    }
                }
            }
            if(count($requiredClasses)){
                $array['required_tips']='<br><br>此应用被其它应用所依赖<br>卸载后会造成这些应用无法正常运行!!!<br>';
                foreach ($requiredClasses as $requiredClass) {
                    $array['required_tips'].=' '.$requiredClass['classname'].'['.$requiredClass['hash'].']<br>';
                }
            }else{
                $array['required_tips']='';
            }
            $array['roles']=C('cms:user:roleAll');
            Return V('class_config',$array);
        }else {
            Return E('应用不存在,或此应用已卸载');
        }
    }
    function permission() {
        if($array['classinfo']=C('cms:class:get',@$_GET['hash'])) {
            $array['roles']=C('cms:user:roleAll');
            $array['class_auth']=C($array['classinfo']['hash'].':auth');
            if(!$array['class_auth']) {
                Return E('此应用无权限配置项');
            }
            $array['title']=$array['classinfo']['classname'].' 权限';
            Return V('class_permission',$array);
        }else {
            Return E('应用不存在,或此应用已卸载');
        }
    }
    function permissionPost() {
        if(!$class=C('cms:class:get',@$_POST['classhash'])) {
            Return E('修改失败');
        }
        $roles=C('cms:user:roleAll');
        begin();
        foreach($roles as $role) {
            C('cms:user:authDelAll',array('rolehash'=>$role['hash'],'authkind'=>'class','classhash'=>$class['hash']));
            foreach($_POST as $auth_key=>$val) {
                if(stripos($auth_key,'|')) {
                    $auth_keys=explode('|',$auth_key);
                    if($auth_keys[0]==$role['hash']) {
                        $actions=explode(';',$auth_keys[1]);
                        foreach($actions as $action) {
                            C('cms:user:authEdit',array('hash'=>$action,'rolehash'=>$role['hash'],'authkind'=>'class'));
                        }
                    }
                    
                }
            }
        }
        commit();
        Return '修改成功';
    }
    function setting() {
        if($array['classinfo']=C('cms:class:get',@$_GET['hash'])) {
            if(!$array['classinfo']['enabled']){
                Return E('应用未启用');
            }
            C('cms:class:installConfig',$array['classinfo']['hash']);
            $configs=C($array['classinfo']['hash'].':config');
            if(!is_array($configs)) {
                Return E('应用不存在设置选项');
            }
            $new_configs=array();
            $configs_configs=array();
            foreach($configs as $config) {
                if(isset($config['hash'])) {
                    $new_configs[]=$config['hash'];
                    $configs_configs[$config['hash']]=$config;
                }
            }
            $array['configs']=C('cms:form:all','config','',$array['classinfo']['hash']);
            foreach($array['configs'] as $key=>$config) {
                if($array['configs'][$key]['enabled'] && in_array($config['hash'],$new_configs)) {
                    $array['configs'][$key]=C('cms:form:build',$config['id']);
                    if(isset($configs_configs[$config['hash']])) {
                        foreach($configs_configs[$config['hash']] as $configkey=>$configval) {
                            $array['configs'][$key][$configkey]=$configval;
                        }
                    }
                    $array['configs'][$key]['auth']['all']=true;
                    $array['configs'][$key]['source']='admin_class_setting';
                    $array['configs'][$key]['value']=config($config['hash'],false,$array['classinfo']['hash']);
                    if(empty($array['configs'][$key]['tabname'])){
                        $array['configs'][$key]['tabname']='默认分组';
                    }
                }else {
                    unset($array['configs'][$key]);
                }
            }
            if(!count($array['configs'])) {
                Return E('应用不存在设置选项');
            }
            $array['tabs']=C('cms:form:getTabs',$array['configs']);
            $array['title']=$array['classinfo']['classname'].' 设置';
            Return V('class_setting',$array);
        }else {
            Return E('应用不存在,或此应用已卸载');
        }
    }
    function settingPost() {
        if($array['classinfo']=C('cms:class:get',@$_POST['classcms_classhash_'])) {
            if(!$array['classinfo']['enabled']){
                Return E('应用未启用');
            }
            $configs=C($array['classinfo']['hash'].':config');
            if(!is_array($configs)) {
                Return E('应用不存在设置选项');
            }
            $new_configs=array();
            $configs_configs=array();
            foreach($configs as $config) {
                if(isset($config['hash'])) {
                    $new_configs[]=$config['hash'];
                    $configs_configs[$config['hash']]=$config;
                }
            }
            $array['configs']=C('cms:form:all','config','',$array['classinfo']['hash']);
            $msg='';
            $class_edit=array();
            foreach($array['configs'] as $config) {
                if($config['enabled'] && in_array($config['hash'],$new_configs)) {
                    $config=C('cms:form:build',$config['id']);
                    if(isset($configs_configs[$config['hash']])) {
                        foreach($configs_configs[$config['hash']] as $configkey=>$configval) {
                            $config[$configkey]=$configval;
                        }
                    }
                    $config['name']=$config['hash'];
                    $config['auth']['all']=true;
                    $config['source']='admin_class_settingsave';
                    $config['value']=config($config['hash'],false,$array['classinfo']['hash']);
                    $config_value=C('cms:input:post',$config);
                    if($config_value===null) {
                    }elseif(is_array($config_value) && isset($config_value['error'])) {
                        $msg.=$config['formname'].' '.$config_value['error'].'<br>';
                    }elseif($config_value===false) {
                        $msg.=$config['formname'].'<i class="layui-icon layui-icon-close"></i><br>';
                    }else {
                        $class_edit[$config['hash']]=$config_value;
                    }
                }
            }
            if(empty($msg)) {
                foreach($class_edit as $config_hash=>$config_value) {
                    config($config_hash,$config_value,$array['classinfo']['hash']);
                }
                $install_route=C('cms:class:installRoute',$array['classinfo']['hash']);
                if(is_string($install_route)) {
                    Return E($install_route);
                }
                $install_hook=C('cms:class:installHook',$array['classinfo']['hash']);
                if(is_string($install_hook)) {
                    Return E($install_hook);
                }
                Return '保存成功';
            }else {
                Return E($msg);
            }
        }else {
            Return E('应用不存在');
        }
    }
    function install() {
        $classhash=@$_POST['hash'];
        if(!C('cms:class:phpCheck',$classhash)) {
            Return E('当前服务器PHP版本为:'.PHP_VERSION.'<br>此应用需要PHP版本为:'.C('cms:class:config',$classhash,'php'));
        }
        if(!C('cms:class:requires',$classhash)) {
            Return E('安装失败.请先安装依赖应用');
        }
        $info=C('cms:class:install',$classhash);
        if($info===true) {
            Return '安装成功.';
        }elseif($info) {
            Return E($info);
        }
        Return E('安装失败.');
    }
    function uninstall() {
        $classhash=@$_POST['hash'];
        C('cms:hook:unhook',$classhash);
        $info=C('cms:class:uninstall',$classhash);
        if($info===true) {
            Return '卸载成功';
        }elseif($info) {
            Return E($info);
        }
        Return E('卸载失败');
    }
    function fileUpdate() {
        $classhash=@$_POST['hash'];
        $old_version=@$_POST['old_version'];
        $new_version=@$_POST['new_version'];
        if(!C('cms:class:requires',$classhash)) {
            Return E('更新失败,请先安装依赖应用');
        }
        $info=C('cms:class:upgrade',$classhash);
        if($info===true) {
            Return '更新成功';
        }elseif($info) {
            Return E($info);
        }
        Return E('更新失败');
    }
    function changeState() {
        $classhash=@$_POST['hash'];
        $state=@$_POST['state'];
        if($state=='false') {
            C('cms:hook:unhook',$classhash);
            $info=C('cms:class:stop',$classhash);
            if($info===true) {
                Return '停用成功';
            }elseif($info) {
                Return $info;
            }
            Return '停用失败';
        }else {
            if(!C('cms:class:requires',$classhash)) {
                Return '启用失败,请先安装依赖应用';
            }
            $info=C('cms:class:start',$classhash);
            if($info===true) {
                Return '启用成功';
            }elseif($info) {
                Return $info;
            }
            Return '启用失败';
        }
    }
    function menu() {
        $classhash=@$_POST['hash'];
        $state=@$_POST['state'];
        if(C('cms:class:refresh',$classhash)) {
            $new_class=array();
            $new_class['table']='class';
            $new_class['where']=array('hash'=>$classhash);
            if($state=='false') {
                $new_class['menu']=0;
            }else {
                $new_class['menu']=1;
            }
            update($new_class);
            Return '后台菜单设置成功';
        }
        Return E('后台菜单设置失败');
    }
    function order() {
        $classhash=@$_POST['hash'];
        $state=@$_POST['state'];
        if(C('cms:class:refresh',$classhash)) {
            if($state=='false') {
                if($lastClass=one('table','class','order','classorder asc','where',where('classorder<=',999999))){
                    $new_order=$lastClass['classorder']-1;
                }else{
                    $new_order=999999;
                }
            }else {
                if($lastClass=one('table','class','order','classorder asc','where',where('classorder>',999999))){
                    $new_order=$lastClass['classorder']-1;
                }else{
                    $new_order=99999999;
                }
            }
            C('cms:class:changeClassOrder',$classhash,$new_order);
            Return '置顶应用成功';
        }
        Return E('置顶应用失败');
    }
}