<?php
if(!defined('ClassCms')) {exit();}
class cms_user {
    Public $admin_role = 'admin';
    function get($userid=0) {
        $user_query=array();
        $user_query['table']='user';
        if(C('this:common:verify',$userid,'id')) {
            $user_query['where']=array('id'=>$userid);
        }else {
            $user_query['where']=array('hash'=>$userid);
        }
        $userinfo=one($user_query);
        if(!$userinfo) {
            Return false;
        }
        Return $userinfo;
    }
    function add($user_add_query) {
        if(!is_hash($user_add_query['hash'])) { Return false; }
        if(C('this:user:get',$user_add_query['hash'])){Return false;}
        if(!isset($user_add_query['username']) || !$user_add_query['username']){
            $user_add_query['username']=$user_add_query['hash'];
        }
        $user_add_query['username']=htmlspecialchars(trim($user_add_query['username']));
        $same_name_query=array();
        $same_name_query['table']='user';
        $same_name_query['where']=array('username'=>$user_add_query['username']);
        if(one($same_name_query)) {
            Return false;
        }
        $user_add_query['table']='user';
        if(!isset($user_add_query['enabled'])) {$user_add_query['enabled']=1;}
        if(!isset($user_add_query['passwd'])) {Return false;}
        $user_add_query['passwd']=C('this:user:passwd2md5',$user_add_query['passwd']);
        $infos=C('this:form:all','info');
        $infos=C('this:form:getColumnCreated',$infos,'user');
        foreach($infos as $info) {
            if(!isset($user_add_query[$info['hash']])) {
                $info=C('this:form:build',$info['id']);
                $user_add_query[$info['hash']]=C('this:input:defaultvalue',$info);
            }
        }
        Return insert($user_add_query);
    }
    function edit($user_edit_query) {
        if(isset($user_edit_query['hash']) && is_hash($user_edit_query['hash'])) {
            if(!$userinfo=C('this:user:get',$user_edit_query['hash'])) {
                Return false;
            }
        }elseif(isset($user_edit_query['id'])) {
            if(!$userinfo=C('this:user:get',$user_edit_query['id'])) {
                Return false;
            }
        }else {
            Return false;
        }
        unset($user_edit_query['id']);
        unset($user_edit_query['hash']);
        if(isset($user_edit_query['username'])) {
            $user_edit_query['username']=htmlspecialchars(trim($user_edit_query['username']));
            $same_name_query=array();
            $same_name_query['table']='user';
            $same_name_query['where']=array('id<>'=>$userinfo['id'],'username'=>$user_edit_query['username']);
            if(one($same_name_query)) {
                Return false;
            }
        }
        $user_edit_query['table']='user';
        $user_edit_query['where']=array('id'=>$userinfo['id']);
        if(!empty($user_edit_query['passwd'])) {
            $user_edit_query['passwd']=C('this:user:passwd2md5',$user_edit_query['passwd']);
            C('cms:user:cleanToken',$userinfo['id'],true);
        }else {
            unset($user_edit_query['passwd']);
        }
        Return update($user_edit_query);
    }
    function del($hash) {
        if(!$userinfo=C('this:user:get',$hash)) {
            Return false;
        }
        C('cms:user:cleanToken',$userinfo['id'],true);
        $del_user_query=array();
        $del_user_query['table']='user';
        $del_user_query['where']=array('id'=>$userinfo['id']);
        Return del($del_user_query);
    }
    function roleAll($all=1) {
        $role_list_query=array();
        $role_list_query['table']='role';
        $role_list_query['order']='roleorder desc,id asc';
        if(!$all) {
            $role_list_query['where']=array('enabled'=>1);
        }
        Return all($role_list_query);
    }
    function roleGet($hash) {
        if(!is_hash($hash)) {
            Return false;
        }
        $role_query=array();
        $role_query['table']='role';
        $role_query['where']=array('hash'=>$hash);
        Return one($role_query);
    }
    function roleAdd($role_add_query) {
        if(!is_hash($role_add_query['hash'])) {
            Return false;
        }
        if(C('this:user:roleGet',$role_add_query['hash'])){Return false;}
        $role_add_query['table']='role';
        if(empty($role_add_query['rolename'])) {
            Return false;
        }
        if(!isset($role_add_query['enabled'])) {
            $role_add_query['enabled']=1;
        }
        if(!isset($role_add_query['roleorder'])) {
            $role_add_query['roleorder']=0;
        }
        Return insert($role_add_query);
    }
    function roleEdit($role_edit_query) {
        if(!is_hash($role_edit_query['hash'])) {
            Return false;
        }
        $role_edit_query['table']='role';
        if(isset($role_edit_query['enabled']) && $role_edit_query['hash']==$this->admin_role  && $role_edit_query['enabled']==0) {
            Return false;
        }
        $role_edit_query['where']=array('hash'=>$role_edit_query['hash']);
        unset($role_edit_query['hash']);
        Return update($role_edit_query);
    }
    function roleDel($hash) {
        if($this->admin_role==$hash) {
            Return false;
        }
        $del_role_query=array();
        $del_role_query['table']='role';
        $del_role_query['where']=array('hash'=>$hash);
        del($del_role_query);

        $del_auth_query=array();
        $del_auth_query['table']='auth';
        $del_auth_query['where']=array('rolehash'=>$hash);
        del($del_auth_query);

        Return true;
    }
    function authGet($hash,$rolehash) {
        if(isset($GLOBALS['C']['auth'][$rolehash])) {
            if(isset($GLOBALS['C']['auth'][$rolehash][$hash])) {
                Return $GLOBALS['C']['auth'][$rolehash][$hash];
            }else {
                Return false;
            }
        }
        $auth_query=array();
        $auth_query['table']='auth';
        $auth_query['where']=array('rolehash'=>$rolehash,'classenabled'=>1);
        $all_role_auth_hash=all($auth_query);
        foreach($all_role_auth_hash as $this_auth) {
            $GLOBALS['C']['auth'][$rolehash][$this_auth['hash']]=$this_auth['enabled'];
        }
        if(count($all_role_auth_hash)==0) {
            $GLOBALS['C']['auth'][$rolehash]=false;
        }
        if(isset($GLOBALS['C']['auth'][$rolehash][$hash])) {
            Return $GLOBALS['C']['auth'][$rolehash][$hash];
        }
        Return false;
    }
    function authEdit($auth_edit_query) {
        if(isset($auth_edit_query['rolehash']) && empty($auth_edit_query['rolehash'])) {
            Return false;
        }
        if(isset($auth_edit_query['hash']) && empty($auth_edit_query['hash'])) {
            Return false;
        }
        if(!isset($auth_edit_query['enabled'])) {
            $auth_edit_query['enabled']=1;
        }
        if(!isset($auth_edit_query['classenabled'])) {
            $auth_edit_query['classenabled']=1;
        }
        if(isset($auth_edit_query['classhash']) && !empty($auth_edit_query['classhash'])) {
            $auth_edit_query['hash']=$auth_edit_query['classhash'].':'.$auth_edit_query['hash'];
        }else {
            $class=explode(':',$auth_edit_query['hash']);
            $auth_edit_query['classhash']=$class[0];
        }
        $auth_query=array();
        $auth_query['table']='auth';
        $auth_query['where']=array('hash'=>$auth_edit_query['hash'],'rolehash'=>$auth_edit_query['rolehash']);
        $auth_edit_query['table']='auth';
        $GLOBALS['C']['auth'][$auth_edit_query['rolehash']][$auth_edit_query['hash']]=$auth_edit_query['enabled'];
        if(one($auth_query)) {
            $auth_edit_query['where']=array('hash'=>$auth_edit_query['hash'],'rolehash'=>$auth_edit_query['rolehash']);
            Return update($auth_edit_query);
        }else {
            Return insert($auth_edit_query);
        }
    }
    function authDel($hash,$rolehash) {
        $auth_del_query=array();
        $auth_del_query['table']='auth';
        $auth_del_query['where']=array('hash'=>$hash,'rolehash'=>$rolehash);
        del($auth_del_query);
        unset($GLOBALS['C']['auth'][$rolehash][$hash]);
        Return true;
    }
    function authDelAll($del_query) {
        $auth_del_query=array();
        $auth_del_query['table']='auth';
        $auth_del_query['where']=$del_query;
        del($auth_del_query);
        unset($GLOBALS['C']['auth'][$del_query['rolehash']]);
        Return true;
    }
    function checkUser($userid,$passwd='') {
        $userinfo=C('this:user:get',$userid);
        if(!$userinfo) {
            E('账号不存在');
            Return array('error'=>2,'msg'=>'账号不存在');
        }
        if(!$userinfo['enabled']) {
            E('账号未启用');
            Return array('error'=>3,'msg'=>'账号未启用');
        }
        if(!empty($passwd)) {
            $passwd=C('this:user:passwd2md5',trim($passwd));
            if($userinfo['passwd']!==$passwd) {
                E('密码不正确');
                Return array('error'=>4,'msg'=>'密码不正确');
            }
        }
        $rolehashs=explode(';',$userinfo['rolehash']);
        foreach($rolehashs as $role) {
            $role=C('this:user:roleGet',$role);
            if($role && $role['enabled']) {
                Return array('error'=>0,'userid'=>$userinfo['id']);
            }
        }
        E('账号无法登入');
        Return array('error'=>5,'msg'=>'账号无法登入');
    }
    function checkToken($token,$checkuser=true) {
        $token_query=array();
        $token_query['table']='token';
        $token_query['where']=array('hash'=>$token,'overtime>='=>time());
        $token=one($token_query);
        if($token) {
            if($user=C('this:user:get',$token['userid'])) {
                if(!$checkuser) {
                    Return $user['id'];
                }
                $check=C('this:user:checkUser',$user['id']);
                if($check['error']===0 && isset($check['userid'])) {
                    Return $user['id'];
                }
            }
        }
        Return false;
    }
    function makeToken($userid,$overtime=0) {
        if(!$overtime) {
            $token_add_query['overtime']=time()+3600*24*7;
        }elseif($overtime<time()) {
            $token_add_query['overtime']=time()+$overtime;
        }else {
            $token_add_query['overtime']=$overtime;
        }
        $token=md5(md5(time().@$GLOBALS['C']['SiteHash']).C('this:common:randStr',12));
        $token_add_query['table']='token';
        $token_add_query['userid']=$userid;
        $token_add_query['hash']=$token;
        if(insert($token_add_query)) {
            C('this:user:cleanToken',$userid);
            Return $token;
        }
        Return false;
    }
    function delToken($token) {
        $del_query=array();
        $del_query['table']='token';
        $del_query['where']=array('hash'=>$token);
        Return del($del_query);
    }
    function cleanToken($userid,$all=false) {
        $del_query=array();
        $del_query['table']='token';
        if($all) {
            $del_query['where']=array('userid'=>$userid);
        }else {
            $del_query['where']=array('userid'=>$userid,'overtime<'=>time());
        }
        Return del($del_query);
    }
    function passwd2md5($passwd) {
        Return md5(strrev(substr(md5($passwd),0,22)));
    }
}