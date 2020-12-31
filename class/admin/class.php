<?php
if(!defined('ClassCms')) {exit();}
class admin_class {
    function auth() {
        Return array(
            'class:index'=>'查看应用',
            'class:changeState'=>'启停应用',
            'class:config;class:upload;class:install;class:uninstall;class:fileUpdate;class:order;class:menu;class:permission;class:permissionPost;class:setting;class:settingPost'=>'管理应用'
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
                $array['newclass'].=' <a class="layui-btn layui-btn-xs layui-btn-normal" href="?do=admin:class:config&hash='.$this_new.'">'.$this_new.'</a>';
            }
        }
        V('class_index',$array);
    }
    function config() {
        $classhash=@$_GET['hash'];
        if(!is_hash($classhash)) {Return C('this:error','error');}
        C('cms:class:refresh',$classhash);
        if($array['classinfo']=C('cms:class:get',$classhash)) {
            if(!is_file(classDir($classhash).$classhash.'.php')) {
                if(!$array['classinfo']['installed']) {
                    $del_class=array();
                    $del_class['table']='class';
                    $del_class['where']=array('hash'=>$array['classinfo']['hash']);
                    del($del_class);
                    Return C('this:error','应用不存在,或此应用已卸载');
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
                    Return C('this:error',$classhash.'.config 文件解析错误');
                }
                $array['new_version']=C('cms:class:config',$array['classinfo']['hash'],'version');
                $array['description']=C('cms:class:config',$array['classinfo']['hash'],'description');
                $array['setting']=false;
                $array['phpcheck']='';
                if(C('cms:class:phpCheck',$classhash)) {
                    if($class_configs=C($array['classinfo']['hash'].':config')) {
                        if(is_array($class_configs)) {
                            $array['setting']=true;
                        }
                    }
                }else {
                    $array['phpcheck']='无法使用,当前服务器PHP版本为:'.PHP_VERSION.',当前应用需要PHP版本为:'.C('cms:class:config',$classhash,'php');
                }
            }
            $array['roles']=C('cms:user:roleAll');
            $array['modulecount']=0;
            if($array['classinfo']['module']) {
                $modules=C('cms:module:all',$array['classinfo']['hash']);
                if(count($modules)) {$array['modulecount']=1;}
            }
            V('class_config',$array);
        }else {
            Return C('this:error','应用不存在,或此应用已卸载');
        }
    }
    function permission() {
        if($array['classinfo']=C('cms:class:get',@$_GET['hash'])) {
            $array['roles']=C('cms:user:roleAll');
            $array['class_auth']=C($array['classinfo']['hash'].':auth');
            if(!$array['class_auth']) {
                Return C('this:error','此应用无权限配置项');
            }
            $array['title']=$array['classinfo']['classname'].' 权限';
            V('class_permission',$array);
        }else {
            Return C('this:error','应用不存在,或此应用已卸载');
        }
    }
    function permissionPost() {
        if(!$class=C('cms:class:get',@$_POST['classhash'])) {
            Return C('this:ajax','修改失败',1);
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
        Return C('this:ajax','修改成功');
    }
    function setting() {
        if($array['classinfo']=C('cms:class:get',@$_GET['hash'])) {
            C('cms:class:installConfig',$array['classinfo']['hash']);
            $configs=C($array['classinfo']['hash'].':config');
            if(!is_array($configs)) {
                Return C('this:error','应用不存在设置选项');
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
                    $array['configs'][$key]['auth']=C('this:formAuth',$config['id']);
                    foreach($array['configs'][$key]['auth'] as $authkey=>$authval) {
                        $array['configs'][$key]['auth'][$authkey]=true;
                    }
                    $array['configs'][$key]['source']='admin_class_setting';
                    $array['configs'][$key]['value']=config($config['hash'],false,$array['classinfo']['hash']);
                }else {
                    unset($array['configs'][$key]);
                }
            }
            if(!count($array['configs'])) {
                Return C('this:error','应用不存在设置选项');
            }
            $array['tabs']=C('cms:form:getTabs',$array['configs']);
            if(count($array['tabs'])==0) {
                $array['tabs']=array('默认分组');
            }
            $array['title']=$array['classinfo']['classname'].' 设置';
            V('class_setting',$array);
        }else {
            Return C('this:error','应用不存在,或此应用已卸载');
        }
    }
    function settingPost() {
        if($array['classinfo']=C('cms:class:get',@$_POST['classcms_classhash_'])) {
             $configs=C($array['classinfo']['hash'].':config');
            if(!is_array($configs)) {
                Return C('this:error','应用不存在设置选项');
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
                    $config['auth']=C('this:formAuth',$config['id']);
                    foreach($config['auth'] as $authkey=>$authval) {
                        $config['auth'][$authkey]=true;
                    }
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
                    Return C('this:ajax',$install_route,1);
                }
                $install_hook=C('cms:class:installHook',$array['classinfo']['hash']);
                if(is_string($install_hook)) {
                    Return C('this:ajax',$install_hook,1);
                }
                Return C('this:ajax','保存成功');
            }else {
                Return C('this:ajax',$msg,1);
            }
        }else {
            C('this:ajax','用户不存在',1);
        }
    }
    function install() {
        $classhash=@$_POST['hash'];
        if(!C('cms:class:phpCheck',$classhash)) {
            Return C('this:ajax','当前服务器PHP版本为:'.PHP_VERSION.'<br>此应用需要PHP版本为:'.C('cms:class:config',$classhash,'php'),1);
        }
        if(!C('cms:class:requires',$classhash)) {
            Return C('this:ajax','安装失败.请先安装依赖应用',1);
        }
        $info=C('cms:class:install',$classhash);
        if($info===true) {
            Return C('this:ajax','安装成功.');
        }else {
            Return C('this:ajax','安装失败.'.$info,1);
        }
    }
    function uninstall() {
        $classhash=@$_POST['hash'];
        C('cms:hook:unhook',$classhash);
        $info=C('cms:class:uninstall',$classhash);
        if($info===true) {
            Return C('this:ajax','卸载成功.');
        }else {
            Return C('this:ajax','卸载失败.'.$info,1);
        }
    }
    function fileUpdate() {
        $classhash=@$_POST['hash'];
        $old_version=@$_POST['old_version'];
        $new_version=@$_POST['new_version'];
        $info=C('cms:class:upgrade',$classhash);
        if($info===true) {
            Return C('this:ajax','更新成功');
        }else {
            Return C('this:ajax','更新失败.'.$info,1);
        }
    }
    function changeState() {
        $classhash=@$_POST['hash'];
        $state=@$_POST['state'];
        if($state=='false') {
            C('cms:hook:unhook',$classhash);
            $info=C('cms:class:stop',$classhash);
            if($info===true) {
                Return C('this:ajax','停用成功');
            }else {
                Return C('this:ajax','停用失败.'.$info);
            }
        }else {
            if(!C('cms:class:requires',$classhash)) {
                Return C('this:ajax','启动失败.请先安装依赖应用');
            }
            $info=C('cms:class:start',$classhash);
            if($info===true) {
                Return C('this:ajax','启动成功');
            }else {
                Return C('this:ajax','启动失败.'.$info);
            }
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
            Return C('this:ajax','后台菜单设置成功,请刷新后台页面');
        }
        Return C('this:ajax','后台菜单设置失败');
    }
    function order() {
        $classhash=@$_POST['hash'];
        $state=@$_POST['state'];
        if(C('cms:class:refresh',$classhash)) {
            if($state=='false') {
                $new_order=1;
            }else {
                $lastorder=array();
                $lastorder['table']='class';
                $lastorder['column']='classorder';
                $lastorder['order']='classorder asc';
                $lastorder['where']=array('classorder>'=>1);
                $lastorder_query=one($lastorder);
                if(isset($lastorder_query['classorder'])) {
                    $new_order=$lastorder_query['classorder']-1;
                }else {
                    $new_order=99999999;
                }
            }
            C('cms:class:changeClassOrder',$classhash,$new_order);
            Return C('this:ajax','置顶应用成功');
        }
        Return C('this:ajax','置顶应用失败');
    }
    function upload() {
        if(!$GLOBALS['C']['Debug']) {
            Return C('this:ajax','未开启Debug模式,无法上传安装包',1);
        }
        if(!isset($_FILES['zipfile'])) {
            Return C('this:ajax','未上传',1);
        }
        $classhashs=explode('.',$_FILES['zipfile']['name']);
        $classhash=$classhashs[0];
        if(!is_hash($classhash)) {
            Return C('this:ajax',$classhash." 安装包文件名不合法,格式为:xxx.zip或xxx.1.0.zip",1);
        }
        if(!function_exists('zip_open') || !class_exists('ZipArchive')) {
            Return C('this:ajax',"未安装ZIP组件,请解压安装包,将文件夹上传至应用目录",1);
        }
        $classdir=classDir($classhash);
        if(is_dir($classdir)) {
            Return C('this:ajax',$classhash." 已存在,请先删除该目录",1);
        }
        if(C('cms:class:unzip',$_FILES['zipfile']['tmp_name'],$classdir)) {
            if(C('cms:class:refresh',$classhash)) {
                Return C('this:ajax',$classhash." 上传成功,请刷新页面");
            }else {
                Return C('this:ajax',$classhash." 上传失败,请检查安装包格式是否正确",1);
            }
        }else{
            Return C('this:ajax',$classhash."安装包解压失败,请重试",1);
        }
    }
}