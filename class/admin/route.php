<?php
if(!defined('ClassCms')) {exit();}
class admin_route {
    function index() {
        $array['module']=C('cms:module:get',@$_GET['id']);
        if(!$array['module']) {
            Return C('this:error','模型不存在');
        }
        $array['classinfo']=C('cms:class:get',$array['module']['classhash']);
        $array['routes']=C('cms:route:all',$array['module']['hash'],$array['module']['classhash']);
        $array['columns']=C('cms:form:all','column',$array['module']['hash'],$array['module']['classhash']);
        $array['breadcrumb']=C('this:module:breadcrumb',$array['classinfo'],$array['module'],'页面');
        $array['title']=$array['module']['modulename'].' 页面';
        V('route_index',$array);
    }
    function add() {
        $array['module']=C('cms:module:get',@$_GET['moduleid']);
        if(!$array['module']) {
            Return C('this:error','模型不存在');
        }
        $array['classinfo']=C('cms:class:get',$array['module']['classhash']);
        if(!$array['classinfo']['module']) {Return C('this:error',$array['classinfo']['classname'].' 应用无法配置模型');}
        $array['breadcrumb']=C('this:module:breadcrumb',$array['classinfo'],$array['module']);
        $array['breadcrumb'][]=array('url'=>'?do=admin:route:index&id='.$array['module']['id'],'title'=>'页面');
        $array['breadcrumb'][]=array('url'=>'','title'=>' 增加');
        $array['title']='增加页面';
        V('route_edit',$array);
    }
    function edit() {
        $array['route']=C('cms:route:get',@$_GET['id']);
        if(!$array['route']){
            Return C('this:error','页面不存在');
        }
        $array['module']=C('cms:module:get',$array['route']['modulehash'],$array['route']['classhash']);
        $array['classinfo']=C('cms:class:get',$array['module']['classhash']);
        $array['breadcrumb']=C('this:module:breadcrumb',$array['classinfo'],$array['module']);
        $array['breadcrumb'][]=array('url'=>'?do=admin:route:index&id='.$array['module']['id'],'title'=>'页面');
        $array['breadcrumb'][]=array('url'=>'','title'=>$array['route']['hash'].' 修改');
        $array['title']=$array['route']['hash'].' 修改';
        V('route_edit',$array);
    }
    function addPost() {
        if(!$module=C('cms:module:get',intval($_POST['moduleid']))){
            Return C('this:ajax','模型不存在',1);
        }
        if(!$class=C('cms:class:get',$module['classhash'])) {
            Return C('this:ajax','应用不存在',1);
        }
        if(!C('cms:route:allow',@$_POST['uri'])) {
            Return C('this:ajax','网址中不允许包含特殊字符',1);
        }
        if(substr(@$_POST['uri'],0,1)!='/'){
            Return C('this:ajax','网址必须以 / 开头',1);
        }
        if(!is_hash(@$_POST['hash'])) {
            Return C('this:ajax','标识错误',1);
        }
        $route=C('cms:route:get',$_POST['hash'],$module['hash'],$module['classhash']);
        if($route) {
            Return C('this:ajax','增加失败,已存在此页面标识',1);
        }
        if(empty($_POST['classfunction']) && empty($_POST['classview'])) {
            Return C('this:ajax','方法名或模板文件,请填写其中一项',1);
        }
        $route_add_array=array();
        $route_add_array['hash']=$_POST['hash'];
        $route_add_array['classhash']=$class['hash'];
        $route_add_array['classorder']=$class['classorder'];
        $route_add_array['modulehash']=$module['hash'];
        $route_add_array['moduleorder']=$module['moduleorder'];
        $route_add_array['uri']=$_POST['uri'];
        $route_add_array['routeorder']=1;
        $route_add_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
        $route_add_array['classfunction']=$_POST['classfunction'];
        $route_add_array['classview']=$_POST['classview'];
        if(C('cms:route:add',$route_add_array)) {
            Return C('this:ajax','增加成功');
        }else {
            Return C('this:ajax','增加失败',1);
        }
    }
    function editPost() {
        if(!$route=C('cms:route:get',intval($_POST['id']))) {
            Return C('this:ajax','页面不存在',1);
        }
        if(!C('cms:route:allow',@$_POST['uri'])) {
            Return C('this:ajax','网址中不允许包含特殊字符',1);
        }
        if(substr(@$_POST['uri'],0,1)!='/'){
            Return C('this:ajax','网址必须以 / 开头',1);
        }
        if(empty($_POST['classfunction']) && empty($_POST['classview'])) {
            Return C('this:ajax','函数名或模板文件,请填写其中一项',1);
        }
        $route_edit_array=array();
        $route_edit_array['id']=$_POST['id'];
        $route_edit_array['uri']=$_POST['uri'];
        $route_edit_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
        $route_edit_array['classfunction']=$_POST['classfunction'];
        $route_edit_array['classview']=$_POST['classview'];
        if(C('cms:route:edit',$route_edit_array)) {
            Return C('this:ajax','修改成功');
        }else {
            Return C('this:ajax','修改失败',1);
        }
    }
    function del() {
        if(C('cms:route:del',@$_POST['id'])){
            Return C('this:ajax','删除成功');
        }else {
            Return C('this:ajax','删除失败',1);
        }
    }
    function order() {
        $routesarray=explode('|',$_POST['routesarray']);
        $route_up_query=array();
        $route_up_query['table']='route';
        foreach($routesarray as $key=>$val) {
            if(!empty($val)) {
                $route_up_query['where']=array('id'=>$val);
                $route_up_query['routeorder']=count($routesarray)-$key;
                update($route_up_query);
            }
        }
        Return C('this:ajax','修改成功');
    }
}