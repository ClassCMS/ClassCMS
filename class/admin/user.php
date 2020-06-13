<?php
if(!defined('ClassCms')) {exit();}
class admin_user {
    function auth() {
        Return array(
            'user:index'=>'查看用户',
            'user:add;user:addPost'=>'增加用户',
            'user:edit;user:editPost'=>'修改用户账号',
            'user:info;user:infoSave'=>'修改用户属性',
            'user:del'=>'删除用户',
            'user:roleIndex;user:roleEdit;user:roleAddPost;user:roleEditPost;user:roleOrder;user:roleDel;user:rolePermission;user:rolePermissionPost'=>'角色管理',
            'info:index;info:editTab;info:tabOrder;info:addPost;info:move;info:del;info:edit;info:editPost;info:order;info:ajax'=>'属性管理'
        );
    }
    function index() {
        $array['nowuser']=C('cms:user:get',C('this:nowUser'));
        $user_query=array();
        $user_query['table']='user';
        $user_query['optimize']=true;
        $user_query['page']=page('pagesize',30);
        $user_query_where=array();
        if(isset($_GET['rolehash']) && !empty($_GET['rolehash'])) {
            $user_query_where['rolehash%']=$_GET['rolehash'];
        }
        $user_query['where']=$user_query_where;
        $array['users']=all($user_query);
        $array['infobutton']=0;
        $array['infos']=C('cms:form:all','info');
        $array['infos']=C('cms:form:getColumnCreated',$array['infos'],'user');
        foreach($array['infos'] as $key=>$column) {
            $array['infos'][$key]=C('cms:form:build',$column['id']);
            $array['infos'][$key]['source']='adminuserlist';
            $thisauth=C('this:formAuth',$column['id']);
            if($thisauth['read']) {
                $array['infobutton']=1;
            }
            if($array['infos'][$key]['indexshow']) {
                if(!$thisauth['read']) {
                    unset($array['infos'][$key]);
                }
            }else {
                unset($array['infos'][$key]);
            }
        }
        V('user_index',$array);
    }
    function add() {
        $array['nowuser']=C('cms:user:get',C('this:nowUser'));
        $array['roleinput']=array('name'=>'rolehash','value'=>'','inputhash'=>'rolecheckbox','showdisabled'=>1);
        if(!C('this:user:superAdmin',$array['nowuser']['rolehash'])) {
            $array['roleinput']['rolehash']=$array['nowuser']['rolehash'];
        }
        $array['passwdinput']=array('name'=>'passwd','inputhash'=>'password','checkold'=>0,'placeholder_new'=>'请输入密码','placeholder_check'=>'请确认密码');
        if(!C('this:user:superAdmin',$array['nowuser']['rolehash'])) {
            $array['roleinput']['rolehash']=$array['nowuser']['rolehash'];
        }
        $array['title']='增加用户';
        V('user_edit',$array);
    }
    function addPost() {
        $user_add_array=array();
        $user_add_array['username']=trim($_POST['username']);
        $same_name_query['table']='user';
        $same_name_query['where']=array('username'=>$user_add_array['username']);
        if(one($same_name_query)) {
            Return C('this:ajax','该昵称已被使用',1);
        }
        $user_add_array['hash']=$_POST['hash'];
        if(C('cms:user:get',$user_add_array['hash'])){
            Return C('this:ajax','已存在该账号',1);
        }
        $user_add_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
        $user_add_array['rolehash']=C('cms:input:post',array('inputhash'=>'rolecheckbox','name'=>'rolehash'));
        $nowuser=C('cms:user:get',C('this:nowUser'));
        if(!C('this:user:superAdmin',$nowuser['rolehash']) && !empty($user_add_array['rolehash'])) {
            $my_role_array=explode(';',$nowuser['rolehash']);
            $user_rolehash_array=explode(';',$user_add_array['rolehash']);
            foreach($user_rolehash_array as $this_role) {
                if(!in_array($this_role,$my_role_array)) {
                    Return C('this:ajax','没有权限增加此角色['.htmlspecialchars($this_role).']',1);
                }
            }
        }
        $user_add_array['passwd']=C('cms:input:post',array('inputhash'=>'password','name'=>'passwd'));
        if(strlen(trim($_POST['passwd']))) {
            if($_POST['passwd']!==$_POST['passwd_2']) {
                Return C('this:ajax','新密码输入不一致',1);
            }
        }else {
            Return C('this:ajax','密码不能为空',1);
        }
        $addreturn=C('cms:user:add',$user_add_array);
        if(is_numeric($addreturn)) {
            Return C('this:ajax','增加成功');
        }elseif(is_string($addreturn)){
            Return C('this:ajax',$addreturn,1);
        }
        Return C('this:ajax','增加失败',1);
    }
    function edit() {
        if($array=C('cms:user:get',@$_GET['id'])){
            $array['nowuser']=C('cms:user:get',C('this:nowUser'));
            if(!C('this:user:superAdmin',$array['nowuser']['rolehash'])) {
                $my_role_array=explode(';',$array['nowuser']['rolehash']);
                $user_rolehash_array=explode(';',$array['rolehash']);
                foreach($user_rolehash_array as $this_role) {
                    if(!in_array($this_role,$my_role_array)) {
                        Return C('this:error','没有权限修改此账号');
                    }
                }
            }
            $array['roleinput']=array('name'=>'rolehash','value'=>$array['rolehash'],'inputhash'=>'rolecheckbox','showdisabled'=>1);
            if($array['hash']==$array['nowuser']['hash']) {
                $array['roleinput']['disabled']=1;
            }
            if(!C('this:user:superAdmin',$array['nowuser']['rolehash'])) {
                $array['roleinput']['rolehash']=$array['nowuser']['rolehash'];
            }
            $array['passwdinput']=array('name'=>'passwd','inputhash'=>'password','checkold'=>0,'placeholder_new'=>'请输入新密码','placeholder_check'=>'请确认新密码');
            $array['title']='['.$array['username'].'] 修改';
            V('user_edit',$array);
        }else {
            C('this:error','用户不存在');
        }
    }
    function editPost() {
        if($user=C('cms:user:get',@$_POST['hash'])){
            $user_edit_array=array();
            $user_edit_array['hash']=$_POST['hash'];
            $user_edit_array['username']=trim($_POST['username']);
            $same_name_query=array();
            $same_name_query['table']='user';
            $same_name_query['where']=array('hash<>'=>$user_edit_array['hash'],'username'=>$user_edit_array['username']);
            if(one($same_name_query)) {
                Return C('this:ajax','该昵称已被使用',1);
            }
            $user_edit_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
            
            $nowuser=C('cms:user:get',C('this:nowUser'));
            if(!C('this:user:superAdmin',$nowuser['rolehash'])) {
                $user_edit_array['rolehash']=C('cms:input:post',array('inputhash'=>'rolecheckbox','name'=>'rolehash','showdisabled'=>1,'rolehash'=>$nowuser['rolehash']));
                $my_role_array=explode(';',$nowuser['rolehash']);
                $user_rolehash_array=explode(';',$user_edit_array['rolehash']);
                foreach($user_rolehash_array as $this_role) {
                    if(!in_array($this_role,$my_role_array)) {
                        Return C('this:ajax','没有权限为此账号增加角色 ['.htmlspecialchars($this_role).']',1);
                    }
                }
            }else {
                $user_edit_array['rolehash']=C('cms:input:post',array('inputhash'=>'rolecheckbox','name'=>'rolehash','showdisabled'=>1));
            }
            if($nowuser['hash']==$user['hash']) {
                unset($user_edit_array['enabled']);
                unset($user_edit_array['rolehash']);
            }
            $user_edit_array['passwd']=C('cms:input:post',array('inputhash'=>'password','name'=>'passwd'));
            if(strlen(trim($_POST['passwd']))) {
                if($_POST['passwd']!==$_POST['passwd_2']) {
                    Return C('this:ajax','新密码输入不一致',1);
                }
            }else {
                unset($user_edit_array['passwd']);
            }
            $editreturn=C('cms:user:edit',$user_edit_array);
            if($editreturn===true) {
                Return C('this:ajax','修改成功');
            }elseif(is_string($editreturn)){
                Return C('this:ajax',$editreturn,1);
            }
            Return C('this:ajax','修改失败',1);
        }else {
            Return C('this:ajax','用户不存在',1);
        }
    }
    function del() {
        if(!$del_user=C('cms:user:get',@$_POST['id'])) {
            Return C('this:ajax','用户不存在',1);
        }
        $nowuser=C('cms:user:get',C('this:nowUser'));
        if($nowuser['hash']==$del_user['hash']) {
            Return C('this:ajax','无法删除自身账号',1);
        }
        if(!C('this:user:superAdmin',$nowuser['rolehash'])) {
            $my_role_array=explode(';',$nowuser['rolehash']);
            $user_rolehash_array=explode(';',$del_user['rolehash']);
            foreach($user_rolehash_array as $this_role) {
                if(!in_array($this_role,$my_role_array)) {
                    Return C('this:ajax','没有权限删除此账号',1);
                }
            }
        }
        $delreturn=C('cms:user:del',@$_POST['id']);
        if($delreturn===true){
            Return C('this:ajax','删除成功');
        }elseif(is_string($delreturn)) {
            Return C('this:ajax',$delreturn,1);
        }
        Return C('this:ajax','删除失败',1);
    }
    function superAdmin($rolehashs) {
        $rolehash_array=explode(';',$rolehashs);
        if(in_array(C('cms:user:$admin_role'),$rolehash_array)) {
            Return true;
        }
        Return false;
    }
    function roleIndex() {
        V('user_role_index');
    }
    function roleEdit() {
        if($array=C('cms:user:roleGet',@$_GET['hash'])){
            V('user_role_edit',$array);
        }else {
            V('user_role_edit');
        }
    }
    function roleAddPost() {
        $role_add_array=array();
        if(isset($_POST['enabled'])) {$role_add_array['enabled']=1;}else {$role_add_array['enabled']=0;}
        $role_add_array['rolename']=htmlspecialchars($_POST['rolename']);
        if(!is_hash(@$_POST['hash'])) {
            Return C('this:ajax','角色标识格式有误',1);
        }
        $role_add_array['hash']=$_POST['hash'];
        if(C('cms:user:roleAdd',$role_add_array)) {
            Return C('this:ajax','增加成功');
        }else {
            Return C('this:ajax','增加失败',1);
        }
    }
    function roleEditPost() {
        if($role=C('cms:user:roleGet',@$_POST['hash'])){
            $role_edit_array=array();
            if(isset($_POST['enabled'])) {$role_edit_array['enabled']=1;}else {$role_edit_array['enabled']=0;}
            $role_edit_array['rolename']=htmlspecialchars($_POST['rolename']);
            $role_edit_array['hash']=$_POST['hash'];
            if(C('cms:user:roleEdit',$role_edit_array)) {
                Return C('this:ajax','修改成功');
            }else {
                Return C('this:ajax','修改失败',1);
            }
        }else {
            Return C('this:ajax','此角色不存在',1);
        }
    }
    function rolePermission() {
        if($array=C('cms:user:roleGet',@$_GET['hash'])){
            $array['superadmin']=C('this:user:superAdmin',$array['hash']);
            $array['title']=$array['rolename'].'['.$array['hash'].'] 权限';
            V('user_role_permission',$array);
        }else {
            Return C('this:error','此角色不存在');
        }
    }
    function rolePermissionPost() {
        $role=C('cms:user:roleGet',@$_POST['rolehash']);
        if(!$role) {
            Return C('this:ajax','此角色不存在',1);
        }
        if(C('this:user:superAdmin',$role['hash'])) {
            Return C('this:ajax','无法编辑管理员权限',1);
        }
        C('cms:user:authDelAll',array('rolehash'=>$role['hash'],'authkind'=>'class'));
        begin();
        foreach($_POST as $auth_key=>$val) {
            if(stripos($auth_key,':')) {
                $actions=explode(';',$auth_key);
                foreach($actions as $action) {
                    C('cms:user:authEdit',array('hash'=>$action,'rolehash'=>$role['hash'],'authkind'=>'class'));
                }
            }
        }
        commit();
        Return C('this:ajax','修改成功');
    }
    function roleDel() {
        if(C('cms:user:roleDel',$_POST['hash'])) {
            Return C('this:ajax','删除成功');
        }else {
            Return C('this:ajax','删除失败',1);
        }
    }
    function roleOrder() {
        $rolesarray=explode('|',$_POST['rolesarray']);
        foreach($rolesarray as $key=>$val) {
            if(!empty($val)) {
                $role_up_query=array();
                $role_up_query['hash']=$val;
                $role_up_query['roleorder']=count($rolesarray)-$key;
                C('cms:user:roleEdit',$role_up_query);
            }
        }
        Return C('this:ajax','修改成功');
    }
    function info() {
        if($array['user']=C('cms:user:get',@$_GET['id'])){
            $array['nowuser']=C('cms:user:get',C('this:nowUser'));
            if(!C('this:user:superAdmin',$array['nowuser']['rolehash'])) {
                $my_role_array=explode(';',$array['nowuser']['rolehash']);
                $user_rolehash_array=explode(';',$array['user']['rolehash']);
                foreach($user_rolehash_array as $this_role) {
                    if(!in_array($this_role,$my_role_array)) {
                        Return C('this:error','没有权限修改此账号');
                    }
                }
            }
            $array['infos']=C('cms:form:all','info');
            $array['infos']=C('cms:form:getColumnCreated',$array['infos'],'user');
            if(!count($array['infos'])) {
                Return C('this:error','未增加用户属性');
            }
            $array['allowsubmit']=0;
            foreach($array['infos'] as $key=>$info) {
                if($array['infos'][$key]['enabled']) {
                    $array['infos'][$key]=C('cms:form:build',$info['id']);
                    $array['infos'][$key]['auth']=C('this:formAuth',$info['id']);
                    $array['infos'][$key]['source']='admin_info_edit';
                    if($array['infos'][$key]['auth']['read']) {
                        if($array['infos'][$key]['auth']['write']) {$array['allowsubmit']=1;}
                        if(isset($array['user'][$info['hash']])) {
                            $array['infos'][$key]['value']=$array['user'][$info['hash']];
                        }else {
                            $array['infos'][$key]['value']='';
                        }
                    }else {
                        unset($array['infos'][$key]);
                    }
                }else {
                    unset($array['infos'][$key]);
                }
            }
            if(!count($array['infos'])) {
                Return C('this:error','无任何属性权限');
            }
            $array['tabs']=C('cms:form:getTabs',$array['infos']);
            if(count($array['tabs'])==0) {
                $array['tabs']=array('默认分组');
            }
            $array['title']=$array['user']['username'].'['.$array['user']['hash'].'] 属性';
            V('user_info',$array);
        }else {
            C('this:error','用户不存在');
        }
    }
    function infoSave() {
        if(!is_hash(@$_POST['hash'])) {Return C('this:ajax','用户不存在',1);}
        if($array['user']=C('cms:user:get',$_POST['hash'])){
            $array['nowuser']=C('cms:user:get',C('this:nowUser'));
            if(!C('this:user:superAdmin',$array['nowuser']['rolehash'])) {
                $my_role_array=explode(';',$array['nowuser']['rolehash']);
                $user_rolehash_array=explode(';',$array['user']['rolehash']);
                foreach($user_rolehash_array as $this_role) {
                    if(!in_array($this_role,$my_role_array)) {
                        Return C('this:ajax','没有权限修改此账号',1);
                    }
                }
            }
            $array['infos']=C('cms:form:all','info');
            $array['infos']=C('cms:form:getColumnCreated',$array['infos'],'user');
            $msg='';
            $user_edit_query=array();
            foreach($array['infos'] as $info) {
                if($info['enabled']) {
                    $info=C('cms:form:build',$info['id']);
                    $info['name']=$info['hash'];
                    $info['auth']=C('this:formAuth',$info['id']);
                    $info['source']='admin_info_save';
                    if($info['auth']['read'] && $info['auth']['write']) {
                        if(isset($array['user'][$info['hash']])) {
                            $info['value']=$array['user'][$info['hash']];
                        }else {
                            $info['value']='';
                        }
                        $info_value=C('cms:input:post',$info);
                        if($info_value===null) {
                        }elseif(is_array($info_value) && isset($info_value['error'])) {
                            $msg.=$info['formname'].' '.$info_value['error'].'<br>';
                        }elseif($info_value===false) {
                            $msg.=$info['formname'].'<i class="layui-icon layui-icon-close"></i><br>';
                        }else {
                            $user_edit_query[$info['hash']]=$info_value;
                        }
                    }
                }
            }
            if(empty($msg) && count($user_edit_query)) {
                $user_edit_query['id']=$array['user']['id'];
                if(C('cms:user:edit',$user_edit_query)) {
                    $msg='修改成功';
                }else {
                    $msg='修改失败';
                }
                Return C('this:ajax',$msg);
            }else {
                Return C('this:ajax',$msg,1);
            }
        }else {
            C('this:ajax','用户不存在',1);
        }
    }
}