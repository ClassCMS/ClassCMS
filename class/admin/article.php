<?php
if(!defined('ClassCms')) {exit();}
class admin_article {
    function home() {
        if(!$array['channel']=C('this:article:channelGet',@$_GET['cid'])) {
            Return C('this:error','栏目不存在或无法访问');
        }
        $array['columns']=C('cms:form:all','column',$array['channel']['_module']['hash'],$array['channel']['_module']['classhash']);
        $array['columns']=C('cms:form:getColumnCreated',$array['columns'],$array['channel']['_module']['table']);
        foreach($array['columns'] as $key=>$column) {
            $array['columns'][$key]['auth']=C('this:formAuth',$column['id']);
            if(!$array['columns'][$key]['auth']['read']) {
                unset($array['columns'][$key]);
            }
        }
        if(count($array['columns'])) {
            if(C('this:moduleAuth',$array['channel']['_module'],'list')) {
                Return C('this:article:index');
            }
            if(C('this:moduleAuth',$array['channel']['_module'],'add')) {
                Return C('this:article:edit');
            }
        }
        if(C('this:article:varEnabled',$array['channel']['_module'])) {
            Return C('this:article:varEdit');
        }
        Return C('this:error','字段或变量权限未开放');
    }
    function index() {
        if(!$array['channel']=C('this:article:channelGet',@$_GET['cid'])) {
            Return C('this:error','栏目不存在或无法访问');
        }
        if(!C('this:moduleAuth',$array['channel']['_module'],'list')) {
            Return C('this:error','无权限');
        }
        if(C('this:moduleAuth',$array['channel']['_module'],'add')) {$array['auth']['add']=1;}else{$array['auth']['add']=0;}
        if(C('this:moduleAuth',$array['channel']['_module'],'var')) {$array['auth']['var']=1;}else {$array['auth']['var']=0;}
        if(C('this:moduleAuth',$array['channel']['_module'],'edit')) {$array['auth']['edit']=1;}else {$array['auth']['edit']=0;}
        if(C('this:moduleAuth',$array['channel']['_module'],'del')) {$array['auth']['del']=1;}else {$array['auth']['del']=0;}
        $array['varEnabled']=C('this:article:varEnabled',$array['channel']['_module']);
        $array['breadcrumb']=C('this:article:breadcrumb',$array['channel']);
        $array['columns']=C('cms:form:all','column',$array['channel']['_module']['hash'],$array['channel']['_module']['classhash']);
        $array['columns']=C('cms:form:getColumnCreated',$array['columns'],$array['channel']['_module']['table']);
        if(count($array['columns'])==0) {
            Return C('this:error','未配置模型字段');
        }
        $GLOBALS['admin']['articleAction']='index';
        $array['viewbutton']=1;
        foreach($array['columns'] as $key=>$column) {
            $array['columns'][$key]=C('cms:form:build',$column['id']);
            if($column['hash']=='title' && $column['inputhash']=='text' && $array['channel']['enabled'] && $array['channel']['_module']['enabled']) {
                $array['columns'][$key]['titlelink']=1;
                $array['viewbutton']=0;
            }
            $array['columns'][$key]['name']=$array['columns'][$key]['hash'];
            $array['columns'][$key]['source']='adminlist';
            $array['columns'][$key]['source_cid']=$array['channel']['id'];
            if($array['columns'][$key]['indexshow']) {
                $array['columns'][$key]['auth']=C('this:formAuth',$column['id']);
                if(!$array['columns'][$key]['auth']['read']) {
                    unset($array['columns'][$key]);
                }
            }else {
                unset($array['columns'][$key]);
            }
        }
        if($listColumns=C('this:article:listColumns:~',$array['columns'])) {
            $array['columns']=$listColumns;
        }
        if(!C('cms:route:get','article',$array['channel']['_module']['hash'],$array['channel']['_module']['classhash'])) {
            $array['viewbutton']=0;
        }
        $article_query=array();
        $article_query['cid']=$array['channel']['id'];
        $article_query['page']='page';
        $article_query['channelurl']='';
        $article_query['pageurl']='';
        if(C('this:moduleAuth',$array['channel']['_module'],'limit|false')) {
            $article_query['where']['uid']=C('this:nowUser');
        }
        $article_query['source']='admin';
        $array['articles']=C('cms:article:get',$article_query);
        V('article_index',$array);
    }
    function edit() {
        if(!$array['channel']=C('this:article:channelGet',@$_GET['cid'])) {
            Return C('this:error','栏目不存在或无法访问');
        }
        if(C('cms:common:verify',@$_GET['id'],'id')) {
            $GLOBALS['admin']['articleAction']='edit';
            $array['breadcrumb']=C('this:article:breadcrumb',$array['channel'],'修改');
            $array['id']=$_GET['id'];
            if(!$article=C('this:article:editEnabled',$array['channel']['id'],$array['id'])) {
                Return C('this:error','文章不存在');
            }
            $array['title']=$array['channel']['channelname'].' 修改';
        }else {
            $GLOBALS['admin']['articleAction']='add';
            $array['id']=false;
            if(!C('this:moduleAuth',$array['channel']['_module'],'add')) {Return C('this:error','无权限');}
            if(C('this:moduleAuth',$array['channel']['_module'],'list')) {
                $array['breadcrumb']=C('this:article:breadcrumb',$array['channel'],'增加');
            }else {
                $array['breadcrumb']=C('this:article:breadcrumb',$array['channel']);
            }
            $array['title']=$array['channel']['channelname'].' 增加';
        }
        if(C('this:moduleAuth',$array['channel']['_module'],'var')) {$array['auth']['var']=1;}else {$array['auth']['var']=0;}
        if(C('this:moduleAuth',$array['channel']['_module'],'del')) {$array['auth']['del']=1;}else {$array['auth']['del']=0;}
        if(C('this:moduleAuth',$array['channel']['_module'],'edit')) {$array['auth']['edit']=1;}else {$array['auth']['edit']=0;}
        if(C('this:moduleAuth',$array['channel']['_module'],'list')) {$array['auth']['list']=1;}else {$array['auth']['list']=0;}
        $array['varEnabled']=C('this:article:varEnabled',$array['channel']['_module']);
        $array['columns']=C('cms:form:all','column',$array['channel']['_module']['hash'],$array['channel']['_module']['classhash']);
        $array['columns']=C('cms:form:getColumnCreated',$array['columns'],$array['channel']['_module']['table']);
        $array['allowsubmit']=0;
        foreach($array['columns'] as $key=>$column) {
            $array['columns'][$key]=C('cms:form:build',$column['id']);
            $array['columns'][$key]['auth']=C('this:formAuth',$column['id']);
            $array['columns'][$key]['source_cid']=$array['channel']['id'];
            if($array['columns'][$key]['auth']['read']) {
                if($array['columns'][$key]['auth']['write']) {$array['allowsubmit']=1;}
                if(isset($article['id'])) {
                    $array['columns'][$key]['value']=$article[$column['hash']];
                    $array['columns'][$key]['source']='admin_article_edit';
                    $array['columns'][$key]['source_id']=$article['id'];
                }else {
                    $array['columns'][$key]['source']='admin_article_add';
                    $array['columns'][$key]['value']=C('cms:input:defaultvalue',$array['columns'][$key]);
                }
            }else {
                unset($array['columns'][$key]);
            }
        }
        if($array['id']) {
            if(isset($_SERVER['HTTP_REFERER'])){
                $referer_parse=parse_url($_SERVER['HTTP_REFERER']);
                if(isset($referer_parse['host']) && $referer_parse['host']==C('cms:common:serverName')){
                    $array['referer']=$_SERVER['HTTP_REFERER'];
                }
            }
            if($editColumns=C('this:article:editColumns:~',$array['columns'])) {
                $array['columns']=$editColumns;
            }
        }else {
            if($addColumns=C('this:article:addColumns:~',$array['columns'])) {
                $array['columns']=$addColumns;
            }
        }
        $array['tabs']=C('cms:form:getTabs',$array['columns']);
        V('article_edit',$array);
    }
    function editSave() {
        if(!C('this:csrfCheck',1)) {
            Return C('this:ajax','非法提交,请刷新当前页面或重新登入系统',1,1001);
        }
        if(!$array['channel']=C('this:article:channelGet',@$_POST['cid'])) {
            Return C('this:ajax','栏目不存在或无法访问',1);
        }
        if(C('cms:common:verify',@$_POST['id'],'id')) {
            $article_id=$_POST['id'];
            $array['breadcrumb']=C('this:article:breadcrumb',$array['channel'],'修改');
            if(!$article=C('this:article:editEnabled',$array['channel']['id'],$article_id)) {
                Return C('this:ajax','文章不存在',1);
            }
        }else {
            $article_id=false;
            if(!C('this:moduleAuth',$array['channel']['_module'],'add')) {
                Return C('this:ajax','无权限',1);
            }
        }
        $array['columns']=C('cms:form:all','column',$array['channel']['_module']['hash'],$array['channel']['_module']['classhash']);
        $array['columns']=C('cms:form:getColumnCreated',$array['columns'],$array['channel']['_module']['table']);
        $new_article=array();
        $new_article['cid']=$array['channel']['id'];
        $errormsg='';
        foreach($array['columns'] as $key=>$column) {
            $array['columns'][$key]=C('cms:form:build',$column['id']);
            $array['columns'][$key]['auth']=C('this:formAuth',$column['id']);
            $array['columns'][$key]['name']=$array['columns'][$key]['hash'];
            $array['columns'][$key]['source']='admin_article_save';
            $array['columns'][$key]['source_cid']=$array['channel']['id'];
            if($array['columns'][$key]['auth']['write']) {
                if($article_id) {
                    $array['columns'][$key]['value']=$article[$array['columns'][$key]['name']];
                    $array['columns'][$key]['source_id']=$article_id;
                }else {
                    $array['columns'][$key]['value']=C('cms:input:defaultvalue',$array['columns'][$key]);
                }
                $new_article[$column['hash']]=C('cms:input:post',$array['columns'][$key]);
                if($new_article[$column['hash']]===null) {
                    unset($new_article[$column['hash']]);
                }elseif(is_array($new_article[$column['hash']]) && isset($new_article[$column['hash']]['error'])) {
                    $errormsg.=$column['formname'].' '.$new_article[$column['hash']]['error'].'<br>';
                }elseif($new_article[$column['hash']]===false) {
                    $errormsg.=$column['formname'].' <i class="layui-icon layui-icon-close"></i><br>';
                }
            }elseif(!$article_id) {
                $new_article[$column['hash']]=C('cms:input:defaultvalue',$array['columns'][$key]);
            }
        }
        if(!empty($errormsg)) {
            Return C('this:ajax',$errormsg,1);
        }
        if(!$article_id) {
            $new_article['uid']=C('this:nowUser');
            $id=C('cms:article:add',$new_article);
            if(is_numeric($id)) {
                if(C('this:moduleAuth',$array['channel']['_module'],'edit')) {
                    Return C('this:ajax',array('msg'=>'增加成功','url'=>'?do=admin:article:edit&cid='.$array['channel']['id'].'&id='.$id));
                }else {
                    Return C('this:ajax',array('msg'=>'增加成功'));
                }
            }else {
                if(is_string($id)) {
                    Return C('this:ajax',$id,1);
                }
                if(E()){
                    Return C('this:ajax',E(),1);
                }
                Return C('this:ajax','增加失败',1);
            }
        }else {
            $new_article['id']=$article_id;
            $editreturn=C('cms:article:edit',$new_article);
            if($editreturn===true) {
                Return C('this:ajax',array('msg'=>'保存成功'));
            }else {
                if(is_string($editreturn)) {
                    Return C('this:ajax',$editreturn,1);
                }
                if(E()){
                    Return C('this:ajax',E(),1);
                }
                Return C('this:ajax','保存失败',1);
            }
        }
    }
    function del() {
        if(!C('this:csrfCheck',1)) {
            Return C('this:ajax','非法提交,请刷新当前页面或重新登入系统',1,1001);
        }
        if(!$array['channel']=C('this:article:channelGet',@$_POST['cid'])) {
            Return C('this:ajax','栏目不存在或无法访问',1);
        }
        if(!C('this:moduleAuth',$array['channel']['_module'],'del')) {
            Return C('this:ajax','无权限',1);
        }
        $ids=explode(';',@$_POST['ids']);
        $limit=C('this:moduleAuth',$array['channel']['_module'],'limit|false');
        $uid=C('this:nowUser');
        $article_del_query=array();
        $article_del_query['cid']=$array['channel']['id'];
        foreach($ids as $id) {
            if(!empty($id)) {
                $article_del_query['where']['id']=intval($id);
                if($limit) {
                    $article_del_query['where']['uid']=$uid;
                }
                $delreturn=C('cms:article:del',$article_del_query);
                if(is_string($delreturn)) {
                    Return C('this:ajax',$delreturn,1);
                }elseif(!$delreturn) {
                    if(E()){
                        Return C('this:ajax',E(),1);
                    }
                    Return C('this:ajax','删除失败',1);
                }
            }
        }
        Return C('this:ajax','删除成功');
    }
    function varEdit() {
        if(!$array['channel']=C('this:article:channelGet',@$_GET['cid'])) {
            Return C('this:error','栏目不存在或无法访问');
        }
        if(!C('this:moduleAuth',$array['channel']['_module'],'var')) {
            Return C('this:error','无权限');
        }
        $GLOBALS['admin']['articleAction']='var';
        $array['columns']=C('cms:form:all','column',$array['channel']['_module']['hash'],$array['channel']['_module']['classhash']);
        $array['columns']=C('cms:form:getColumnCreated',$array['columns'],$array['channel']['_module']['table']);
        foreach($array['columns'] as $key=>$column) {
            $array['columns'][$key]['auth']=C('this:formAuth',$column['id']);
            if(!$array['columns'][$key]['auth']['read']) {
                unset($array['columns'][$key]);
            }
        }
        if(count($array['columns']) && (C('this:moduleAuth',$array['channel']['_module'],'list') || C('this:moduleAuth',$array['channel']['_module'],'add'))) {
            $array['breadcrumb']=C('this:article:breadcrumb',$array['channel'],'设置');
            $array['title']=$array['channel']['channelname'].' 设置';
        }else {
            $array['breadcrumb']=C('this:article:breadcrumb',$array['channel']);
            $array['title']=$array['channel']['channelname'].'';
        }
        $array['vars']=C('cms:form:all','var',$array['channel']['_module']['hash'],$array['channel']['_module']['classhash']);
        $array['allowsubmit']=0;
        foreach($array['vars'] as $key=>$var) {
            if($var['enabled']) {
                $array['vars'][$key]=C('cms:form:build',$var['id']);
                $array['vars'][$key]['auth']=C('this:formAuth',$var['id']);
                $array['vars'][$key]['source']='admin_var_edit';
                $array['vars'][$key]['source_cid']=$array['channel']['id'];
                if($array['vars'][$key]['auth']['read']) {
                    if($array['vars'][$key]['auth']['write']) {$array['allowsubmit']=1;}
                    $array['vars'][$key]['value']=C('cms:article:getVar',$array['channel']['id'],$var['hash']);
                }else {
                    unset($array['vars'][$key]);
                }
            }else {
                unset($array['vars'][$key]);
            }
            
        }
        if($varsColumns=C('this:article:varsColumns:~',$array['vars'])) {
            $array['vars']=$varsColumns;
        }
        if(!count($array['vars'])) {
            Return C('this:error','无变量');
        }
        $array['tabs']=C('cms:form:getTabs',$array['vars']);
        V('article_var',$array);
    }
    function varSave() {
        if(!C('this:csrfCheck',1)) {
            Return C('this:ajax','非法提交,请刷新当前页面或重新登入系统',1,1001);
        }
        if(!$array['channel']=C('this:article:channelGet',@$_POST['cid'])) {
            Return C('this:ajax','栏目不存在或无法访问',1);
        }
        if(!C('this:moduleAuth',$array['channel']['_module'],'var')) {
            Return C('this:ajax','保存失败,无权限',1);
        }
        $array['vars']=C('cms:form:all','var',$array['channel']['_module']['hash'],$array['channel']['_module']['classhash']);
        $msg='';
        $channel_edit=array();
        foreach($array['vars'] as $var) {
            if($var['enabled']) {
                $var=C('cms:form:build',$var['id']);
                $var['name']=$var['hash'];
                $var['auth']=C('this:formAuth',$var['id']);
                $var['source']='admin_var_save';
                $var['source_cid']=$array['channel']['id'];
                if($var['auth']['read'] && $var['auth']['write']) {
                    if(isset($array['channel'][$var['name']])) {
                        $var['value']=$array['channel'][$var['name']];
                    }
                    $var_value=C('cms:input:post',$var);
                    if($var_value===null) {
                    }elseif(is_array($var_value) && isset($var_value['error'])){
                        $msg.=$var['formname'].' '.$var_value['error'].'<br>';
                    }elseif($var_value===false) {
                        $msg.=$var['formname'].'<i class="layui-icon layui-icon-close"></i><br>';
                    }else {
                        $channel_edit[$var['hash']]=$var_value;
                    }
                }
            }
        }
        if(empty($msg)) {
            foreach($channel_edit as $var_name=>$var_value) {
                $savetrturn=C('cms:article:setVar',$array['channel']['id'],$var_name,$var_value);
                if(is_string($savetrturn)) {
                    Return C('this:ajax',$savetrturn,1);
                }elseif(!$savetrturn) {
                    if(E()){
                        Return C('this:ajax',E(),1);
                    }
                    Return C('this:ajax','保存失败',1);
                }
                
            }
            Return C('this:ajax','保存成功');
        }else {
            Return C('this:ajax',$msg,1);
        }
        
    }
    function channelGet($cid=0) {
        if(!$channel=C('cms:channel:get',$cid)) {
            Return false;
        }
        if(!$channel['_module']=C('cms:module:get',$channel['modulehash'],$channel['classhash'])) {
            Return false;
        }
        if(!$class=C('cms:class:get',$channel['classhash'])) {
            Return false;
        }
        if(!$class['module']) {
            Return false;
        }
        if(!$class['enabled'] && !P('class:changestate')) {
            Return false;
        }
        Return $channel;
    }
    function editEnabled($cid,$id,$userid=0) {
        if(!$channel=C('this:article:channelGet',$cid)) {
            Return false;
        }
        if(!$userid) {
            $userid=C('this:nowUser');
        }
        if(!C('this:moduleAuth',$channel['_module'],'edit',$userid)) {
            Return false;
        }
        $article_query=array();
        $article_query['cid']=$channel['id'];
        $article_query['where']['id']=intval($id);
        if(C('this:moduleAuth',$channel['_module'],'limit|false',$userid)) {
            $article_query['where']['uid']=$userid;
        }
        if(!$article=C('cms:article:getOne',$article_query)) {
            Return false;
        }
        Return $article;
    }
    function varEnabled($module) {
        if(!C('this:moduleAuth',$module,'var')) {
            Return false;
        }
        $vars=C('cms:form:all','var',$module['hash'],$module['classhash']);
        foreach($vars as $key=>$var) {
            if($var['enabled']) {
                $vars[$key]['auth']=C('this:formAuth',$var['id']);
                if(!$vars[$key]['auth']['read']) {
                    unset($vars[$key]);
                }else {
                    break;
                }
            }else {
                unset($vars[$key]);
            }
        }
        if(count($vars)) {
            Return true;
        }
        Return false;
    }
    function breadcrumb($channel=0,$actionname='') {
        if(!$channel) {Return false;}
        $breadcrumb=array();
        if(P('class:index')) {
            $class=C('cms:class:get',$channel['classhash']);
            $breadcrumb[]=array('url'=>'?do=admin:class:index','title'=>'应用管理');
            $classes=C('cms:class:all');
            $classlist=array();
            foreach ($classes as $thisclass) {
                if(!$class['enabled'] && !P('class:changestate')) {
                    $thisclass['installed']=0;
                }
                if($thisclass['installed'] && $thisclass['module']){
                    if($thisclass['hash']==$class['hash']){
                        $classlist[]=array('title'=>$thisclass['classname'],'url'=>'?do=admin:class:config&hash='.$thisclass['hash']);
                    }else{
                        $classlist[]=array('title'=>$thisclass['classname'],'url'=>'?do=admin:channel:index&classhash='.$thisclass['hash']);
                    }
                }
            }
            $breadcrumb[]=array('url'=>'?do=admin:class:config&hash='.$class['hash'],'title'=>$class['classname'],'list'=>$classlist);
        }
        if(P('channel:index')) {
            $breadcrumb[]=array('url'=>'?do=admin:channel:index&classhash='.$channel['classhash'],'title'=>'栏目');
        }
        $navs=C('cms:channel:parents',$channel['id'],$channel['classhash']);
        foreach($navs as $this_nav) {
            $breadcrumb[]=array('url'=>'?do=admin:article:home&cid='.$this_nav['id'],'title'=>$this_nav['channelname']);
        }
        if(!empty($actionname)) {
            $breadcrumb[]=array('url'=>'?do=admin:article:home&cid='.$channel['id'],'title'=>$channel['channelname']);
            $breadcrumb[]=array('title'=>$actionname);
        }else {
            $breadcrumb[]=array('title'=>$channel['channelname']);
        }
        Return $breadcrumb;
    }
    function articleAction() {
        Return array(
            'var'=>array('变量管理','修改栏目变量,如栏目标题'),
            'list'=>array('文章管理'),
            'add'=>array('文章增加'),
            'edit'=>array('文章修改'),
            'del'=>array('文章删除'),
            'limit|false'=>array('文章隔离','只允许操作当前账号创建的文章'),
            );
    }
}