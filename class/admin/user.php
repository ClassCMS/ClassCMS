<?php
if(!defined('ClassCms')) {exit();}
class admin_user {
    function auth() {
        Return array(
            'user:index'=>'查看用户列表',
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
        $array['breadcrumb']=array(array('title'=>'用户管理','url'=>'?do=admin:user:index'));
        $user_query=array();
        $user_query['table']='user';
        $user_query['optimize']=true;
        $user_query['page']=page('pagesize',30);
        $user_query_where='';
        if(isset($_GET['rolehash']) && P('user:roleIndex') && $role=C('cms:user:roleGet',$_GET['rolehash'])) {
            $user_query_where.="(rolehash like '".$role['hash']."' or rolehash like '".$role['hash'].";%' or rolehash like '%;".$role['hash']."' or rolehash like '%;".$role['hash'].";%')";
            $array['breadcrumb'][]=array('title'=>$role['rolename'].'['.$role['hash'].']');
        }
        if(!C('this:user:superAdmin',$array['nowuser']['rolehash'])) {
            $roles=C('cms:user:roleAll');
            $myrolehashs=explode(';',$array['nowuser']['rolehash']);
            foreach($myrolehashs as $key=>$thismyrolehash) {
                if(empty($thismyrolehash)) {
                    unset($myrolehashs[$key]);
                }
            }
            $role_where='';
            foreach($roles as $key=>$thisrole) {
                if(!in_array($thisrole['hash'],$myrolehashs)) {
                    if(!empty($role_where)) {
                        $role_where.=' and ';
                    }
                    $role_where.="rolehash not like '".$thisrole['hash']."' and rolehash not like '".$thisrole['hash'].";%' and rolehash not like '%;".$thisrole['hash']."' and rolehash not like '%;".$thisrole['hash'].";%'";
                }
            }
            $user_query_where.=$role_where;
        }
        $user_query['where']=$user_query_where;
        $array['users']=all($user_query);
        $array['infos']=C('cms:form:all','info');
        $array['infos']=C('cms:form:getColumnCreated',$array['infos'],'user');
        foreach($array['infos'] as $key=>$column) {
            $array['infos'][$key]=C('cms:form:build',$column['id']);
            $array['infos'][$key]['source']='adminuserlist';
            $thisauth=C('this:formAuth',$column['id']);
            if($array['infos'][$key]['indexshow']) {
                if(!$thisauth['read']) {
                    unset($array['infos'][$key]);
                }
            }else {
                unset($array['infos'][$key]);
            }
        }
        Return V('user_index',$array);
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
        Return V('user_edit',$array);
    }
    function addPost() {
        $user_add_array=array();
        $user_add_array['username']=trim($_POST['username']);
        $same_name_query['table']='user';
        $same_name_query['where']=array('username'=>$user_add_array['username']);
        if(one($same_name_query)) {
            Return E('该昵称已被使用');
        }
        $user_add_array['hash']=$_POST['hash'];
        if(C('cms:user:get',$user_add_array['hash'])){
            Return E('已存在该账号');
        }
        $user_add_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
        $user_add_array['rolehash']=C('cms:input:post',array('inputhash'=>'rolecheckbox','name'=>'rolehash'));
        $nowuser=C('cms:user:get',C('this:nowUser'));
        if(!C('this:user:superAdmin',$nowuser['rolehash']) && !empty($user_add_array['rolehash'])) {
            $my_role_array=explode(';',$nowuser['rolehash']);
            $user_rolehash_array=explode(';',$user_add_array['rolehash']);
            foreach($user_rolehash_array as $this_role) {
                if(!in_array($this_role,$my_role_array)) {
                    Return E('没有权限增加此角色['.htmlspecialchars($this_role).']');
                }
            }
        }
        $user_add_array['passwd']=C('cms:input:post',array('inputhash'=>'password','name'=>'passwd'));
        if(strlen(trim($_POST['passwd']))) {
            if($_POST['passwd']!==$_POST['passwd_2']) {
                Return E('新密码输入不一致');
            }
        }else {
            Return E('密码不能为空');
        }
        $addreturn=C('cms:user:add',$user_add_array);
        if(is_numeric($addreturn)) {
            Return array('msg'=>'增加成功','id'=>$addreturn,'hash'=>$user_add_array['hash']);
        }elseif(is_string($addreturn)){
            Return E($addreturn);
        }elseif(E()){
            Return E(E());
        }
        Return E('增加失败');
    }
    function edit() {
        if($array=C('cms:user:get',@$_GET['id'])){
            $array['nowuser']=C('cms:user:get',C('this:nowUser'));
            if(!C('this:user:superAdmin',$array['nowuser']['rolehash']) && !empty($array['rolehash'])) {
                $my_role_array=explode(';',$array['nowuser']['rolehash']);
                $user_rolehash_array=explode(';',$array['rolehash']);
                foreach($user_rolehash_array as $this_role) {
                    if(!in_array($this_role,$my_role_array) && !empty($this_role)) {
                        Return E('没有权限修改此账号');
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
            $array['title']='['.$array['username'].'] 管理';
            $array['infos']=C('cms:form:all','info');
            $array['infos']=C('cms:form:getColumnCreated',$array['infos'],'user');
            foreach($array['infos'] as $key=>$column) {
                $array['infos'][$key]=C('cms:form:build',$column['id']);
                $array['infos'][$key]['source']='adminuseredit';
                $thisauth=C('this:formAuth',$column['id']);
                if(!$thisauth['read']) {
                    unset($array['infos'][$key]);
                }
            }
            Return V('user_edit',$array);
        }else {
            Return E('用户不存在');
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
                Return E('该昵称已被使用');
            }
            $user_edit_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
            
            $nowuser=C('cms:user:get',C('this:nowUser'));
            if(!C('this:user:superAdmin',$nowuser['rolehash'])) {
                $user_edit_array['rolehash']=C('cms:input:post',array('inputhash'=>'rolecheckbox','name'=>'rolehash','showdisabled'=>1,'rolehash'=>$nowuser['rolehash']));
                if(!empty($user_edit_array['rolehash'])) {
                    $my_role_array=explode(';',$nowuser['rolehash']);
                    $user_rolehash_array=explode(';',$user_edit_array['rolehash']);
                    foreach($user_rolehash_array as $this_role) {
                        if(!in_array($this_role,$my_role_array)) {
                            Return E('没有权限为此账号增加角色 ['.htmlspecialchars($this_role).']');
                        }
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
                    Return E('新密码输入不一致');
                }
            }else {
                unset($user_edit_array['passwd']);
            }
            $editreturn=C('cms:user:edit',$user_edit_array);
            if($editreturn===true) {
                Return '修改成功';
            }elseif(is_string($editreturn)){
                Return $editreturn;
            }elseif(E()){
                Return E(E());
            }
            Return E('修改失败');
        }else {
            Return E('用户不存在');
        }
    }
    function del() {
        if(!$del_user=C('cms:user:get',@$_POST['id'])) {
            Return E('用户不存在');
        }
        $nowuser=C('cms:user:get',C('this:nowUser'));
        if($nowuser['hash']==$del_user['hash']) {
            Return E('无法删除自身账号');
        }
        if(!C('this:user:superAdmin',$nowuser['rolehash']) && !empty($del_user['rolehash'])) {
            $my_role_array=explode(';',$nowuser['rolehash']);
            $user_rolehash_array=explode(';',$del_user['rolehash']);
            foreach($user_rolehash_array as $this_role) {
                if(!in_array($this_role,$my_role_array) && !empty($this_role)) {
                    Return E('没有权限删除此账号');
                }
            }
        }
        $delreturn=C('cms:user:del',@$_POST['id']);
        if($delreturn===true){
            Return '删除成功';
        }elseif(is_string($delreturn)) {
            Return $delreturn;
        }elseif(E()){
            Return E(E());
        }
        Return E('删除失败');
    }
    function superAdmin($rolehashs) {
        $rolehash_array=explode(';',$rolehashs);
        if(in_array(C('cms:user:$admin_role'),$rolehash_array)) {
            Return true;
        }
        Return false;
    }
    function roleIndex() {
        Return V('user_role_index');
    }
    function roleEdit() {
        if($array=C('cms:user:roleGet',@$_GET['hash'])){
            Return V('user_role_edit',$array);
        }else {
            Return V('user_role_edit');
        }
    }
    function roleAddPost() {
        $role_add_array=array();
        if(isset($_POST['enabled'])) {$role_add_array['enabled']=1;}else {$role_add_array['enabled']=0;}
        $role_add_array['rolename']=htmlspecialchars($_POST['rolename']);
        if(!is_hash(@$_POST['hash'])) {
            Return E('角色标识格式有误');
        }
        $role_add_array['hash']=$_POST['hash'];
        if($id=C('cms:user:roleAdd',$role_add_array)) {
            Return array('msg'=>'增加成功','hash'=>$role_add_array['hash'],'id'=>$id);
        }elseif(E()) {
            Return E(E());
        }
        Return E('增加失败');
    }
    function roleEditPost() {
        if($role=C('cms:user:roleGet',@$_POST['hash'])){
            $role_edit_array=array();
            if(isset($_POST['enabled'])) {$role_edit_array['enabled']=1;}else {$role_edit_array['enabled']=0;}
            $role_edit_array['rolename']=htmlspecialchars($_POST['rolename']);
            $role_edit_array['hash']=$_POST['hash'];
            if(C('cms:user:roleEdit',$role_edit_array)) {
                Return '修改成功';
            }elseif(E()) {
                Return E(E());
            }
            Return E('修改失败');
        }else {
            Return E('此角色不存在');
        }
    }
    function rolePermission() {
        if($array=C('cms:user:roleGet',@$_GET['hash'])){
            $array['superadmin']=C('this:user:superAdmin',$array['hash']);
            $array['title']=$array['rolename'].'['.$array['hash'].'] 权限';
            Return V('user_role_permission',$array);
        }else {
            Return E('此角色不存在');
        }
    }
    function rolePermissionPost() {
        $role=C('cms:user:roleGet',@$_POST['rolehash']);
        if(!$role) {
            Return E('角色不存在');
        }
        if(C('this:user:superAdmin',$role['hash'])) {
            Return E('无法编辑管理员权限');
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
        Return '修改成功';
    }
    function roleDel() {
        if(C('cms:user:roleDel',$_POST['hash'])) {
            Return '删除成功';
        }elseif(E()) {
            Return E(E());
        }
        Return E('删除失败');
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
        Return '修改成功';
    }
    function info() {
        if($array['user']=C('cms:user:get',@$_GET['id'])){
            $array['nowuser']=C('cms:user:get',C('this:nowUser'));
            if(!C('this:user:superAdmin',$array['nowuser']['rolehash'])) {
                $my_role_array=explode(';',$array['nowuser']['rolehash']);
                $user_rolehash_array=explode(';',$array['user']['rolehash']);
                foreach($user_rolehash_array as $this_role) {
                    if(!in_array($this_role,$my_role_array)) {
                        Return E('没有权限修改此账号');
                    }
                }
            }
            $array['infos']=C('cms:form:all','info');
            $array['infos']=C('cms:form:getColumnCreated',$array['infos'],'user');
            if(!count($array['infos'])) {
                Return E('未增加用户属性');
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
                Return E('无任何属性权限');
            }
            $array['tabs']=C('cms:form:getTabs',$array['infos']);
            $array['title']=$array['user']['username'].'['.$array['user']['hash'].'] 属性';
            Return V('user_info',$array);
        }else {
            Return E('用户不存在');
        }
    }
    function infoSave() {
        if(!is_hash(@$_POST['hash'])) {Return E('用户不存在');}
        if($array['user']=C('cms:user:get',$_POST['hash'])){
            $array['nowuser']=C('cms:user:get',C('this:nowUser'));
            if(!C('this:user:superAdmin',$array['nowuser']['rolehash'])) {
                $my_role_array=explode(';',$array['nowuser']['rolehash']);
                $user_rolehash_array=explode(';',$array['user']['rolehash']);
                foreach($user_rolehash_array as $this_role) {
                    if(!in_array($this_role,$my_role_array)) {
                        Return E('没有权限修改此账号');
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
                    Return '修改成功';
                }elseif(E()) {
                    Return E(E());
                }
                Return E('修改失败');
            }else {
                Return E($msg);
            }
        }else {
            Return E('用户不存在');
        }
    }
}