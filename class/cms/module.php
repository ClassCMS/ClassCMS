<?php
if(!defined('ClassCms')) {exit();}
class cms_module {
    function all($classhash='') {
        $list_query=array();
        $list_query['table']='module';
        $where=array();
        if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
        $where['classhash']=$classhash;
        $list_query['where']=$where;
        $list_query['order']='moduleorder desc,id asc';
        $modulelist=all($list_query);
        Return $modulelist;
    }
    function get($hash=0,$classhash='') {
        $module_query=array();
        $module_query['table']='module';
        $where=array();
        if(C('this:common:verify',$hash,'id')) {
            $where['id']=$hash;
        }else {
            if(!is_hash($hash)) {Return false;}
            $where['hash']=$hash;
            if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
            $where['classhash']=$classhash;
        }
        $module_query['where']=$where;
        if(!$module=one($module_query)) {
            Return false;
        }
        $vars=C('this:form:all','var',$module['hash'],$module['classhash']);
        foreach($vars as $var) {
            $module[$var['hash']]=$var['defaultvalue'];
        }
        if(!isset($module['table'])) {
            $module['table']='article_'.$module['classhash'].'_'.$module['hash'];
            if(strlen($module['table'])>54) {
                $module['table']=substr($module['table'],0,54);
            }
        }
        Return $module;
    }
    function add($module_add_query) {
        if(!isset($module_add_query['hash']) || !is_hash($module_add_query['hash'])) {
            Return false;
        }
        if(!isset($module_add_query['classhash'])) {
            $module_add_query['classhash']=I(-1);
        }
        if(!is_hash($module_add_query['classhash'])) {Return false;}
        $where=array();
        $where['classhash']=$module_add_query['classhash'];
        $where['modulename']=$module_add_query['modulename'];
        $same_name_query=array();
        $same_name_query['table']='module';
        $same_name_query['where']=$where;
        if(one($same_name_query)) {Return false;}
        if(C('this:module:get',$module_add_query['hash'],$module_add_query['classhash'])){Return false;}
        $module_add_query['table']='module';
        if(!isset($module_add_query['moduleorder'])) {
            $module_add_query['moduleorder']=1;
        }
        if(!isset($module_add_query['enabled'])) {
            $module_add_query['enabled']=1;
        }
        if(!isset($module_add_query['classenabled'])) {
            $module_add_query['classenabled']=1;
        }
        if(!$moduleid=insert($module_add_query)) {
            Return false;
        }
        C('this:module:reload',$moduleid);
        Return $moduleid;
    }
    function edit($module_edit_query) {
        $where=array();
        if(!isset($module_edit_query['id'])) {
            Return false;
        }
        $where['id']=intval($module_edit_query['id']);
        if(!$module=C('this:module:get',$where['id'])) {
                Return false;
        }
        unset($module_edit_query['id']);
        unset($module_edit_query['hash']);
        unset($module_edit_query['classhash']);
        if(isset($module_edit_query['modulename'])) {
            $same_name_where=array();
            $same_name_where['classhash']=$module['classhash'];
            $same_name_where['modulename']=$module_edit_query['modulename'];
            $same_name_where['id<>']=$module['id'];
            $same_name_query=array();
            $same_name_query['table']='module';
            $same_name_query['where']=$same_name_where;
            if(one($same_name_query)) {Return false;}
        }
        $module_edit_query['table']='module';
        $module_edit_query['where']=$where;
        $update=update($module_edit_query);
        if($update) {
            C('this:module:reload',$module['id']);
            Return true;
        }
        Return false;
    }
    function del($hash=0,$classhash='') {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
        if(!$module=C('this:module:get',$hash,$classhash)) {
            Return false;
        }
        $channels=all(array('table'=>'channel','where'=>array('classhash'=>$module['classhash'],'modulehash'=>$module['hash'])));
        begin();
        foreach($channels as $channel) {
            if(!C('this:channel:del',$channel['id'])) {
                rollback();
                Return $channel['id'];
            }
        }
        $columns=C('this:form:all','column',$module['hash'],$module['classhash']);
        foreach($columns as $column) {
            C('this:form:del',$column['id']);
        }
        $vars=C('this:form:all','var',$module['hash'],$module['classhash']);
        foreach($vars as $var) {
            C('this:form:del',$var['id']);
        }
        $defaultTable='article_'.$module['classhash'].'_'.$module['hash'];
        if(strlen($defaultTable)>54) {
            $defaultTable=substr($defaultTable,0,54);
        }
        if($defaultTable==$module['table']) {
            C($GLOBALS['C']['DbClass'].':delTable',$module['table']);
        }
        $del_route_query=array();
        $del_route_query['table']='route';
        $del_route_query['where']=array('classhash'=>$module['classhash'],'modulehash'=>$module['hash']);
        del($del_route_query);
        $del_module_query=array();
        $del_module_query['table']='module';
        $del_module_query['where']=array('classhash'=>$module['classhash'],'hash'=>$module['hash']);
        del($del_module_query);
        commit();
        Return true;
    }
    function reload($hash=0,$classhash='') {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
        if(!$module=C('this:module:get',$hash,$classhash)) {
            Return false;
        }
        $route_edit_query['table']='route';
        $route_edit_query['moduleorder']=$module['moduleorder'];
        $route_edit_query['moduleenabled']=$module['enabled'];
        $route_edit_query['where']=array('classhash'=>$module['classhash'],'modulehash'=>$module['hash']);
        Return update($route_edit_query);
    }
    function tableCreate($hash=0,$classhash='') {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
        if(!$module=C('this:module:get',$hash,$classhash)) {
            Return false;
        }
        $fields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
        if(is_array($fields) && !count($fields)) {
            if(!C($GLOBALS['C']['DbClass'].':createTable',$module['table'])) {
                Return false;
            }
        }
        if(is_array($fields) && !isset($fields['cid'])) {
            C($GLOBALS['C']['DbClass'].':addField',$module['table'],'cid','int(11)','NOT NULL DEFAULT 0');
            C($GLOBALS['C']['DbClass'].':addIndex',$module['table'],'cid');
        }
        if(is_array($fields) && !isset($fields['uid'])) {
            C($GLOBALS['C']['DbClass'].':addField',$module['table'],'uid','int(11)','NOT NULL DEFAULT 0');
            C($GLOBALS['C']['DbClass'].':addIndex',$module['table'],'uid');
        }
        Return true;
    }
    function authStr($module,$action) {
        Return $module['classhash'].':_module:'.$module['hash'].':'.$action;
    }
}