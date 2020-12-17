<?php
if(!defined('ClassCms')) {exit();}
class cms_route {
    function all($modulehash='',$classhash='') {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
        if(!is_hash($modulehash)) {Return array();}
        $list_query=array();
        $list_query['table']='route';
        $where=array();
        $where['classhash']=$classhash;
        $where['modulehash']=$modulehash;
        $list_query['where']=$where;
        $list_query['order']='routeorder desc,id asc';
        $routelist=all($list_query);
        Return $routelist;
    }
    function get($hash,$modulehash='',$classhash='') {
        $route_query=array();
        $route_query['table']='route';
        $where=array();
        if(C('this:common:verify',$hash,'id')) {
            $where['id']=$hash;
        }else {
            if(!is_hash($hash)) {Return false;}
            $where['hash']=$hash;
            if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
            $where['classhash']=$classhash;
            if(!empty($modulehash)) {
                if(!is_hash($modulehash)) {Return false;}
                $where['modulehash']=$modulehash;
            }
        }
        $route_query['where']=$where;
        $route_query['order']='routeorder desc,id asc';
        Return one($route_query);
    }
    function add($route_add_query) {
        if(!isset($route_add_query['hash']) || !is_hash($route_add_query['hash'])) {
            Return false;
        }
        if(!isset($route_add_query['classhash'])) {
            $route_add_query['classhash']=last_class();
        }
        $route_add_query['moduleorder']=1;
        if(isset($route_add_query['modulehash'])) {
            if(!is_hash($route_add_query['modulehash'])) {Return false;}
            if(!$module=C('this:module:get',$route_add_query['modulehash'],$route_add_query['classhash'])) {
                Return false;
            }
            $route_add_query['moduleorder']=$module['moduleorder'];
        }else {
            $route_add_query['modulehash']='';
        }
        if(!is_hash($route_add_query['classhash'])) {Return false;}
        if($route=C('this:route:get',$route_add_query['hash'],$route_add_query['modulehash'],$route_add_query['classhash'])) {
            Return false;
        }
        $route_add_query['table']='route';
        if(isset($route_add_query['uri'])) {
            $route_add_query['uri']=C('this:route:optimize',$route_add_query['uri']);
            if(!C('this:route:allow',$route_add_query['uri'])) {
                Return false;
            }
        }else {
            Return false;
        }
        if(isset($route_add_query['classfunction'])) {
            $route_add_query['classfunction']=trim($route_add_query['classfunction']);
        }else {
            $route_add_query['classfunction']='';
        }
        if(!isset($route_add_query['classview'])) {
            $route_add_query['classview']='';
        }
        if(empty($route_add_query['classfunction']) && empty($route_add_query['classview'])) {
            Return false;
        }
        if(!isset($route_add_query['routeorder'])) {
            $route_add_query['routeorder']=1;
        }
        if(!isset($route_add_query['enabled'])) {
            $route_add_query['enabled']=1;
        }
        if(!isset($route_add_query['moduleenabled'])) {
            $route_add_query['moduleenabled']=1;
        }
        if(!isset($route_add_query['classenabled'])) {
            $route_add_query['classenabled']=1;
        }
        if($class=C('this:class:get',$route_add_query['classhash'])) {
            $route_add_query['classorder']=$class['classorder'];
        }else {
            $route_add_query['classorder']=1;
        }
        Return insert($route_add_query);
    }
    function edit($route_edit_query) {
        if(!isset($route_edit_query['id'])) {
            Return false;
        }
        $route=C('this:route:get',$route_edit_query['id']);
        if(!$route) {
            Return false;
        }
        $route_edit_query['table']='route';
        $route_edit_query['where']=array('id'=>$route_edit_query['id']);
        if(isset($route_edit_query['uri'])) {
            $route_edit_query['uri']=C('this:route:optimize',$route_edit_query['uri']);
        }
        if(isset($route_edit_query['classfunction'])) {
            $route_edit_query['classfunction']=trim($route_edit_query['classfunction']);
        }
        Return update($route_edit_query);
    }
    function del($hash) {
        $route=C('this:route:get',$hash);
        if(!$route) {
            Return false;
        }
        $where=array();
        $where['id']=$route['id'];
        $del_route_query=array();
        $del_route_query['table']='route';
        $del_route_query['where']=$where;
        Return del($del_route_query);
    }
    function allow($uri) {
        if(strpos($uri,';')!==false || strpos($uri,'#')!==false) {
            Return false;
        }
        if(substr_count($uri,'(')!=substr_count($uri,')')) {
            Return false;
        }
        Return true;
    }
    function optimize($uri) {
        if(empty($uri)) {
            Return $uri;
        }
        $uri=trim($uri);
        if(substr($uri,0,1)!='/') {
            $uri='/'.$uri;
        }
        $uri=str_replace("\\","/",$uri);
        $uri=str_replace("//","/",$uri);
        $uris=explode('/',$uri);
        $lasturi=$uris[count($uris)-1];
        if(!empty($lasturi) && substr_count($lasturi,'$.')>=substr_count($lasturi,'.')) {
            $uri=$uri.'/';
        }
        Return $uri;
    }
}