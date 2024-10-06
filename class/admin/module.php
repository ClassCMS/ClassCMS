<?php
if(!defined('ClassCms')) {exit();}
class admin_module {
    function auth() {
        Return array(
            'module:index;module:order'=>'查看模型列表',
            'module:add;module:addPost'=>'增加模型',
            'module:config;module:edit;module:editPost'=>'修改模型',
            'module:del'=>'删除模型',
            'route:index;route:add;route:addPost;route:edit;route:editPost;route:del;route:order'=>'页面管理',
            'var:index;var:editTab;var:tabOrder;var:addPost;var:move;var:del;var:edit;var:editPost;var:order;var:ajax'=>'变量管理',
            'column:index;column:editTab;column:tabOrder;column:addPost;column:move;column:del;column:edit;column:editPost;column:order;column:ajax'=>'字段管理',
            'module:permission;module:permissionPost'=>'权限管理',
        );
    }
    function index() {
        if(!isset($_GET['classhash']) && !$_GET['classhash']=C('cms:class:defaultClass')) {
            Return E('未安装模板应用');
        }
        if(!$class=C('cms:class:get',$_GET['classhash'])) {
            Return E($_GET['classhash'].' 应用未安装');
        }
        if(!$class['installed']) {Return E($_GET['classhash'].' 应用未安装');}
        if(!$class['module']) {Return E('此应用['.$_GET['classhash'].'] 未开启模型配置选项');}
        $array['modulelist']=C('cms:module:all',$_GET['classhash']);
        $array['classhash']=$class['hash'];
        $array['classname']=$class['classname'];
        $array['breadcrumb']=C('this:module:breadcrumb',$class);
        $array['title']=$array['classname'].'['.$array['classhash'].'] 模型';
        $array['auth']['module_add']=P('module:add');
        $array['auth']['module_edit']=P('module:edit');
        $array['auth']['route_index']=P('route:index');
        $array['auth']['var_index']=P('var:index');
        $array['auth']['column_index']=P('column:index');
        Return V('module_index',$array);
    }
    function order() {
        if(!is_hash(@$_POST['classhash'])) {Return E('error');}
        $modulesarray=explode('|',$_POST['modulesarray']);
        foreach($modulesarray as $key=>$moduleid) {
            if(!empty($moduleid)) {
                $module_up_query=array();
                $module_up_query['id']=$moduleid;
                $module_up_query['moduleorder']=count($modulesarray)-$key;
                C('cms:module:edit',$module_up_query);
            }
        }
        Return '修改成功';
    }
    function add() {
        $array['classinfo']=C('cms:class:get',@$_GET['classhash']);
        if(!$array['classinfo']) {
            Return E('error');
        }
        $array['breadcrumb']=C('this:module:breadcrumb',$array['classinfo'],'','增加');
        $array['routes']=C('this:module:defaultRoute');
        $array['vars']=C('this:module:defaultVar');
        $array['columns']=C('this:module:defaultColumn');
        Return V('module_add',$array);
    }
    function addPost() {
        $classinfo=C('cms:class:get',@$_POST['classhash']);
        if(!$classinfo) {Return E('应用不存在');}
        if(!$classinfo['installed']) {Return E('应用未安装');}
        if(!$classinfo['module']) {Return E($classinfo['classname'].' 应用无法配置模型');}
        $where=array();
        $where['classhash']=$classinfo['hash'];
        $where['modulename']=htmlspecialchars($_POST['modulename']);
        $same_name_query=array();
        $same_name_query['table']='module';
        $same_name_query['where']=$where;
        if(one($same_name_query)) {
            Return E('存在相同的模型名称');
        }
        $module_add_array=array();
        $module_add_array['classhash']=$_POST['classhash'];
        if(!isset($_POST['hash']) || empty($_POST['hash'])){
            $_POST['hash']=C('cms:common:pinyin',htmlspecialchars($_POST['modulename']));
        }
        if(!is_hash($_POST['hash'])) {
            Return E('模型标识格式有误');
        }
        $module_add_array['hash']=$_POST['hash'];
        if(C('cms:module:get',$module_add_array['hash'],$module_add_array['classhash'])){
            Return E('已存在该模型标识['.$module_add_array['hash'].']');
        }
        $module_add_array['modulename']=htmlspecialchars($_POST['modulename']);
        if($id=C('cms:module:add',$module_add_array)) {
            $module=C('cms:module:get',$id);
            if(isset($_POST['rotues'])) {
                $defaultRoutes=C('this:module:defaultRoute');
                $route_add_array=array();
                $route_add_array['classhash']=$classinfo['hash'];
                $route_add_array['classorder']=$classinfo['classorder'];
                $route_add_array['modulehash']=$module['hash'];
                $route_add_array['moduleorder']=$module['moduleorder'];
                foreach($_POST['rotues'] as $routes_key) {
                    if(isset($defaultRoutes[$routes_key])) {
                        $route_add_array['hash']=$defaultRoutes[$routes_key]['hash'];
                        $route_add_array['uri']=$defaultRoutes[$routes_key]['uri'];
                        $route_add_array['classview']=str_replace('{modulehash}',$module['hash'],$defaultRoutes[$routes_key]['classview']);
                        C('cms:route:add',$route_add_array);
                    }
                }
            }
            if(isset($_POST['vars'])) {
                $defaultVars=C('this:module:defaultVar');
                $var_add_array=array();
                $var_add_array['classhash']=$classinfo['hash'];
                $var_add_array['modulehash']=$module['hash'];
                $var_add_array['kind']='var';
                $var_add_array['enabled']=1;
                $var_add_array['tabname']='默认分组';
                $var_add_array['taborder']=0;
                foreach($_POST['vars'] as $vars_key) {
                    if(isset($defaultVars[$vars_key])) {
                        $var_add_array['formname']=$defaultVars[$vars_key]['title'];
                        $var_add_array['hash']=$defaultVars[$vars_key]['hash'];
                        $var_add_array['inputhash']=$defaultVars[$vars_key]['inputhash'];
                        C('cms:form:add',$var_add_array);
                    }
                }
            }
            if(isset($_POST['columns'])) {
                $defaultColumns=C('this:module:defaultColumn');
                $column_add_array=array();
                $column_add_array['classhash']=$classinfo['hash'];
                $column_add_array['modulehash']=$module['hash'];
                $column_add_array['kind']='column';
                $column_add_array['enabled']=1;
                $column_add_array['tabname']='默认分组';
                $column_add_array['taborder']=0;
                foreach($_POST['columns'] as $columns_key) {
                    if(isset($defaultColumns[$columns_key])) {
                        unset($column_add_array['indexshow']);
                        if(isset($defaultColumns[$columns_key]['indexshow']) && $defaultColumns[$columns_key]['indexshow']) {
                            $column_add_array['indexshow']=1;
                        }
                        unset($column_add_array['nonull']);
                        if(isset($defaultColumns[$columns_key]['nonull']) && $defaultColumns[$columns_key]['nonull']) {
                            $column_add_array['nonull']=1;
                        }
                        $column_add_array['formname']=$defaultColumns[$columns_key]['title'];
                        $column_add_array['hash']=$defaultColumns[$columns_key]['hash'];
                        $column_add_array['inputhash']=$defaultColumns[$columns_key]['inputhash'];
                        C('cms:form:add',$column_add_array);
                    }
                }
            }
            return array('msg'=>'增加成功','id'=>$id,'url'=>'?do=admin:module:config&id='.$id,'popup'=>array('btns'=>array('管理'=>array('go'=>'?do=admin:module:config&id='.$id),'返回'=>'back')));
        }else {
            Return E('增加失败');
        }
    }
    function config() {
        $array['module']=C('cms:module:get',@$_GET['id']);
        if(!$array['module']) {Return E('模型不存在');}
        $array['classinfo']=C('cms:class:get',$array['module']['classhash']);
        if(!$array['classinfo']['module']) {Return E($array['classinfo']['classname'].' 应用无法配置模型');}
        $array['breadcrumb']=C('this:module:breadcrumb',$array['classinfo'],$array['module']);
        $array['routes']=C('cms:route:all',$array['module']['hash'],$array['module']['classhash']);
        $array['vars']=C('cms:form:all','var',$array['module']['hash'],$array['module']['classhash']);
        $array['varstabs']=C('cms:form:getTabs',$array['vars']);
        $array['columns']=C('cms:form:all','column',$array['module']['hash'],$array['module']['classhash']);
        $array['columnstabs']=C('cms:form:getTabs',$array['columns']);
        $array['admin_role_name']=C('cms:user:$admin_role');
        $array['edit_modulename']=str_replace('"','\"',htmlspecialchars_decode($array['module']['modulename']));
        $array['title']=$array['module']['modulename'].' 模型';
        $array['roles']=C('cms:user:roleAll');
        foreach($array['roles'] as $key=>$thisrole) {
            $array['roles'][$key]['_editabled']=C('this:roleCheck','admin:module:permission',$thisrole['hash'],false);
        }
        $actions=C('this:article:articleAction');
        $array['actions']=array();
        if(count($array['vars'])) {
            $array['actions']['var']=$actions['var'];
        }
        if(count($array['columns'])) {
            foreach($actions as $key=>$action) {
                if($key!='var') {
                    $array['actions'][$key]=$action;
                }
            }
        }
        Return V('module_config',$array);
    }
    function editPost() {
        if($module=C('cms:module:get',@$_POST['id'])){
            $module_edit_array=array();
            if(isset($_POST['modulename'])) {
                $where=array();
                $where['classhash']=$module['classhash'];
                $where['modulename']=htmlspecialchars($_POST['modulename']);
                $where['id<>']=$module['id'];
                $same_name_query=array();
                $same_name_query['table']='module';
                $same_name_query['where']=$where;
                if(one($same_name_query)) {
                    Return E('存在相同的模型名称');
                }
                $module_edit_array['modulename']=htmlspecialchars($_POST['modulename']);
            }
            $module_edit_array['id']=$_POST['id'];
            $module_edit_array['hash']=$module['hash'];
            $module_edit_array['classhash']=$module['classhash'];
            if(isset($_POST['enabled'])) {
                if($_POST['enabled']=='false') {
                    $module_edit_array['enabled']=0;
                }else {
                    $module_edit_array['enabled']=1;
                }
            }
            $editreturn=C('cms:module:edit',$module_edit_array);
            if($editreturn===true) {
                return array('msg'=>'修改成功','popup'=>array('btns'=>array('好的'=>'reload','返回'=>'back')));
            }elseif(is_string($editreturn)) {
                Return E($editreturn);
            }
            Return E('修改失败');
        }else {
            Return E('模型不存在');
        }
    }
    function del() {
        if($module=C('cms:module:get',@$_POST['id'])){
            if($delreturn=C('cms:module:del',$module['hash'],$module['classhash'])) {
                if($delreturn===true) {
                    return array('msg'=>'删除成功','popup'=>array('btns'=>array('返回'=>'back')));
                }elseif(is_numeric($delreturn)) {
                    $channel=C('cms:channel:get',$delreturn);
                    Return E('删除失败,栏目 '.$channel['channelname'].' 下属栏目未删除<br>请先删除下属栏目');
                }elseif(is_string($delreturn)) {
                    Return E($delreturn);
                }else {
                    Return E('删除失败');
                }
            }else {
                Return E('删除失败');
            }
        }else {
            Return E('模型不存在');
        }
    }
    function permission() {
        $array['module']=C('cms:module:get',@$_GET['id']);
        if(!$array['module']) {
            Return E('模型不存在');
        }
        $array['classinfo']=C('cms:class:get',$array['module']['classhash']);
        $array['breadcrumb']=C('this:module:breadcrumb',$array['classinfo'],$array['module'],'权限');
        $array['admin_role_name']=C('cms:user:$admin_role');
        $array['actions']=array();
        $actions=C('this:article:articleAction');
        $array['vars']=C('cms:form:all','var',$array['module']['hash'],$array['module']['classhash']);
        if(count($array['vars'])) {
            $array['actions']['var']=$actions['var'];
        }
        $array['columns']=C('cms:form:all','column',$array['module']['hash'],$array['module']['classhash']);
        if(count($array['columns'])) {
            foreach($actions as $key=>$action) {
                if($key!='var') {
                    $array['actions'][$key]=$action;
                }
            }
        }
        $array['roles']=C('cms:user:roleAll');
        foreach($array['roles'] as $key=>$thisrole) {
            $array['roles'][$key]['_editabled']=C('this:roleCheck','admin:module:permission',$thisrole['hash'],false);
        }
        $array['title']=$array['module']['modulename'].' 权限';
        Return V('module_permission',$array);
    }
    function permissionPost() {
        $module=C('cms:module:get',@$_POST['id']);
        if(!$module) {
            Return E('模型不存在');
        }
        $roles=C('cms:user:roleAll');
        foreach($roles as $role) {
            $authkind=$module['classhash'].':_module:'.$module['hash'];
            C('cms:user:authDelAll',array('rolehash'=>$role['hash'],'authkind'=>$authkind));
            if(isset($_POST[$role['hash'].'_role']) && is_array($_POST[$role['hash'].'_role'])) {
                foreach($_POST[$role['hash'].'_role'] as $thiskey=>$thisval) {
                    $action=C('cms:module:authStr',$module,$thiskey);
                    C('cms:user:authEdit',array('hash'=>$action,'rolehash'=>$role['hash'],'authkind'=>$authkind));
                }
            }
        }
        Return '修改成功';
    }
    function breadcrumb($class,$module='',$action='') {
        if(!is_array($class)) {
            Return array();
        }
        $breadcrumb=array();
        if(P('class:index')) {
            $breadcrumb[]=array('url'=>'?do=admin:class:index','title'=>'应用管理');
            $classes=C('cms:class:all');
            $classlist=array();
            foreach ($classes as $thisclass) {
                if($thisclass['installed'] && $thisclass['module']){
                    if($thisclass['hash']==$class['hash']){
                        $classlist[]=array('title'=>$thisclass['classname'],'url'=>'?do=admin:class:config&hash='.$thisclass['hash']);
                    }else{
                        $classlist[]=array('title'=>$thisclass['classname'],'url'=>'?do=admin:module:index&classhash='.$thisclass['hash']);
                    }
                }
            }
            $breadcrumb[]=array('url'=>'?do=admin:class:config&hash='.$class['hash'],'title'=>$class['classname'],'list'=>$classlist);
        }
        $breadcrumb[]=array('url'=>'?do=admin:module:index&classhash='.$class['hash'],'title'=>'模型');
        if(!empty($module)) {
            $breadcrumb[]=array('url'=>'?do=admin:module:config&id='.$module['id'],'title'=>$module['modulename']);
        }
        if(!empty($action)) {
            $breadcrumb[]=array('url'=>'','title'=>$action);
        }
        Return $breadcrumb;
    }
    function defaultRoute() {
        Return array(
                array('title'=>'栏目页','hash'=>'channel','uri'=>'/($.id)/','classview'=>'{modulehash}'),
                array('title'=>'列表页','hash'=>'list','uri'=>'/($.id)/page_(page).html','classview'=>'{modulehash}','checked'=>0),
                array('title'=>'文章页','hash'=>'article','uri'=>'/($.id)/($id).html','classview'=>'{modulehash}_content','checked'=>0),
            );
    }
    function defaultVar() {
        if($editor=one(array('table'=>'input','where'=>array('enabled'=>1,'classenabled'=>1,'groupname'=>'编辑器'),'order'=>'inputorder asc,id asc'))) {
            $editorhash=$editor['hash'];
        }else {
            $editorhash='textarea';
        }
        Return array(
                array('title'=>'标题','hash'=>'title','inputhash'=>'text'),
                array('title'=>'关键词','hash'=>'keywords','inputhash'=>'text'),
                array('title'=>'描述','hash'=>'description','inputhash'=>'textarea'),
                array('title'=>'内容','hash'=>'content','inputhash'=>$editorhash)
            );
    }
    function defaultColumn() {
        if($editor=one(array('table'=>'input','where'=>array('enabled'=>1,'classenabled'=>1,'groupname'=>'编辑器'),'order'=>'inputorder asc,id asc'))) {
            $editorhash=$editor['hash'];
        }else {
            $editorhash='textarea';
        }
        Return array(
                array('title'=>'标题','hash'=>'title','inputhash'=>'text','indexshow'=>1,'nonull'=>1),
                array('title'=>'关键词','hash'=>'keywords','inputhash'=>'text'),
                array('title'=>'描述','hash'=>'description','inputhash'=>'textarea'),
                array('title'=>'内容','hash'=>'content','inputhash'=>$editorhash),
                array('title'=>'图片','hash'=>'pic','inputhash'=>'imgupload','checked'=>0),
                array('title'=>'时间','hash'=>'datetime','inputhash'=>'datetime','checked'=>0)
            );
    }
}