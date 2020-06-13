<?php
if(!defined('ClassCms')) {exit();}
class cms_hook {
    function all($classhash='') {
        $list_query=array();
        $list_query['table']='hook';
        if(!empty($classhash)) {
            $list_query['where']=array('classhash'=>$classhash);
        }
        $list_query['order']='classorder desc,hookorder desc,id asc';
        $hooks=all($list_query);
        Return $hooks;
    }
    function get($hookname,$hookedfunction,$classhash='') {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
        $hook_query=array();
        $hook_query['table']='hook';
        $hook_query['where']=array('hookname'=>$hookname,'hookedfunction'=>$hookedfunction,'classhash'=>$classhash);
        Return one($hook_query);
    }
    function add($hook_add_query) {
        if(!isset($hook_add_query['classhash'])) {
            $hook_add_query['classhash']=last_class();
        }
        if(!isset($hook_add_query['hookname']) || empty($hook_add_query['hookname'])) {
            Return false;
        }
        $hook_add_query['hookname']=$hook_add_query['hookname'];
        if(!isset($hook_add_query['hookedfunction'])) {
            Return false;
        }
        $hook_add_query['hookedfunction']=$hook_add_query['hookedfunction'];
        if(!isset($hook_add_query['enabled'])) {
            $hook_add_query['enabled']=1;
        }
        if(!isset($hook_add_query['hookorder'])) {
            $hook_add_query['hookorder']=1;
        }
        if(!isset($hook_add_query['classenabled'])) {
            $hook_add_query['classenabled']=1;
        }
        if(!isset($hook_add_query['classorder'])) {
            if($class=C('this:class:get',$hook_add_query['classhash'])) {
                $hook_add_query['classorder']=$class['classorder'];
            }else {
                $hook_add_query['classorder']=1;
            }
        }
        $hook_add_query['table']='hook';
        if(C('this:hook:get',$hook_add_query['hookname'],$hook_add_query['hookedfunction'],$hook_add_query['classhash'])) {
            Return false;
        }
        Return insert($hook_add_query);
    }
    function edit($hook_edit_query) {
        if(!isset($hook_edit_query['id'])) {
            Return false;
        }
        unset($hook_edit_query['classhash']);
        $hook_edit_query['table']='hook';
        if(isset($hook_edit_query['hookname'])) {
            $hook_edit_query['hookname']=$hook_edit_query['hookname'];
        }
        if(isset($hook_edit_query['hookedfunction'])) {
            $hook_edit_query['hookedfunction']=$hook_edit_query['hookedfunction'];
        }
        $hook_edit_query['where']=array('id'=>$hook_edit_query['id']);
        Return update($hook_edit_query);
    }
    function del($hookname,$hookedfunction,$classhash='') {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
        $del_hook_query=array();
        $del_hook_query['table']='hook';
        $del_hook_query['where']=array('hookname'=>$hookname,'hookedfunction'=>$hookedfunction,'classhash'=>$classhash);
        Return del($del_hook_query);
    }
}