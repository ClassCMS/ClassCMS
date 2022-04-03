<?php
if(!defined('ClassCms')) {exit();}
class admin_my {
    function auth() {
        Return array(
            'showleftmenu'=>'显示管理菜单',
            'my:info;my:infoPost'=>'个人资料管理',
            'my:edit;my:editPost'=>'个人账号管理',
        );
    }
    function edit() {
        $array['userid']=C('admin:nowUser');
        $array['userinfo']=C('cms:user:get',$array['userid']);
        $array['breadcrumb']=array(array('title'=>'账号管理'));
        $array['password_input']=array('name'=>'passwd','inputhash'=>'password','checkold'=>1,'value'=>$array['userinfo']['passwd'],'placeholder_old'=>'请输入当前的密码','placeholder_new'=>'请输入新密码','placeholder_check'=>'请确认新密码');
        V('my_edit',$array);
    }
    function editPost() {
        $array['userid']=C('admin:nowUser');
        $array['userinfo']=C('cms:user:get',$array['userid']);
        $my_edit_query=array();
        $my_edit_query['id']=$array['userid'];
        $my_edit_query['username']=trim($_POST['username']);
        $same_name_query['table']='user';
        $same_name_query['where']=array('id<>'=>$my_edit_query['id'],'username'=>$my_edit_query['username']);
        if(one($same_name_query)) {
            Return C('this:ajax','该昵称已被使用',1);
        }
        if(strlen(trim($_POST['passwd']))) {
            if(C('cms:user:passwd2md5',$_POST['passwd_old'])!=$array['userinfo']['passwd']) {
                Return C('this:ajax','当前密码错误',1);
            }
            if($_POST['passwd']!==$_POST['passwd_2']) {
                Return C('this:ajax','新密码输入不一致',1);
            }
        }
        $array['password_input']=array('name'=>'passwd','inputhash'=>'password','checkold'=>1,'value'=>$array['userinfo']['passwd']);
        $my_edit_query['passwd']=C('cms:input:post',$array['password_input']);
        if(!$my_edit_query['passwd']) {
            unset($my_edit_query['passwd']);
        }
        if(C('cms:user:edit',$my_edit_query)){
            if(isset($my_edit_query['passwd'])) {
                Return C('this:ajax',array('msg'=>'修改成功,密码已经重置,请重新登入','refresh'=>1));
            }else {
                Return C('this:ajax','修改成功,请刷新页面');
            }
        }elseif(E()) {
            Return C('this:ajax',E(),1);
        }
        Return C('this:ajax','修改失败',1);
    }
    function info() {
        $array['userid']=C('admin:nowUser');
        $array['userinfo']=C('cms:user:get',$array['userid']);
        $array['breadcrumb']=array(array('title'=>'个人资料'));
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
                    if(isset($array['userinfo'][$info['hash']])) {
                        $array['infos'][$key]['value']=$array['userinfo'][$info['hash']];
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
        V('my_info',$array);
    }
    function infoPost() {
        $array['userid']=C('admin:nowUser');
        $array['userinfo']=C('cms:user:get',$array['userid']);
        $array['infos']=C('cms:form:all','info');
        $array['infos']=C('cms:form:getColumnCreated',$array['infos'],'user');
        $msg='';
        $my_edit_query=array();
        foreach($array['infos'] as $info) {
            if($info['enabled']) {
                $info=C('cms:form:build',$info['id']);
                $info['name']=$info['hash'];
                $info['auth']=C('this:formAuth',$info['id']);
                $info['source']='my_info_save';
                if($info['auth']['read'] && $info['auth']['write']) {
                    if(isset($array['userinfo'][$info['hash']])) {
                        $info['value']=$array['userinfo'][$info['hash']];
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
                        $my_edit_query[$info['hash']]=$info_value;
                    }
                }
            }
        }
        if(empty($msg) && count($my_edit_query)) {
            $my_edit_query['id']=$array['userid'];
            if(C('cms:user:edit',$my_edit_query)) {
                Return C('this:ajax','保存成功');
            }elseif(E()) {
                Return C('this:ajax',E(),1);
            }
            Return C('this:ajax','保存失败',1);
        }else {
            Return C('this:ajax',$msg,1);
        }
    }
}