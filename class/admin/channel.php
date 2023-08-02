<?php
if(!defined('ClassCms')) {exit();}
class admin_channel {
    function auth() {
        Return array(
            'channel:index;channel:jump;channel:jumpModule'=>'查看栏目列表',
            'channel:add;channel:addPost'=>'增加栏目',
            'channel:edit;channel:editPost'=>'修改栏目',
            'channel:del'=>'删除栏目',
        );
    }
    function maxshow($classhash) {
        $pagesize=300;
        $channel_count=total('channel',where(array('classhash'=>$classhash)));
        if($channel_count>=$pagesize) {
            Return $pagesize;
        }else {
            Return false;
        }
    }
    function index() {
        if(isset($_GET['id'])) {
            if(!$channel=C('cms:channel:get',intval($_GET['id']))){
                Return E('栏目不存在');
            }
            $_GET['classhash']=$channel['classhash'];
            $array['fid']=$channel['id'];
            $array['breadcrumb']=C('this:channel:breadcrumb',$channel);
        }else{
            if(!isset($_GET['classhash']) && !$_GET['classhash']=C('cms:class:defaultClass')){
                Return E('未安装模板应用');
            }
            $array['fid']=0;
            $array['breadcrumb']=C('this:channel:breadcrumb',false,$_GET['classhash']);
        }
        if(!$array['classinfo']=C('cms:class:get',$_GET['classhash'])) {
            Return E('应用未安装');
        }
        if(!$array['classinfo']['installed']) {Return E($_GET['classhash'].' 应用未安装');}
        if(!$array['classinfo']['module']) {Return E('此应用['.$_GET['classhash'].'] 未开启模型配置选项');}
        if($maxshow=C('this:channel:maxshow',$array['classinfo']['hash'])) {
            $array['showpage']=1;
            $channel_list_query=array();
            $channel_list_query['page']=page('pagesize',$maxshow);
            $channel_list_query['table']='channel';
            $channel_list_query['where']=array('fid'=>$array['fid'],'classhash'=>$array['classinfo']['hash']);
            $channel_list_query['order']='channelorder asc,id asc';
            $array['channels']=all($channel_list_query);
        }else {
            $array['showpage']=0;
            $array['channels']=C('cms:channel:tree',$array['fid'],$array['classinfo']['hash']);
        }
        foreach($array['channels'] as $key=>$this_channel) {
            if(isset($GLOBALS['C']['adminmodulecache'][$this_channel['modulehash']])) {
                $this_module=$GLOBALS['C']['adminmodulecache'][$this_channel['modulehash']];
            }else {
                $this_module=C('cms:module:get',$this_channel['modulehash'],$this_channel['classhash']);
                if(!C('this:moduleAuth',$this_module,'list') && !C('this:moduleAuth',$this_module,'add') && !C('this:moduleAuth',$this_module,'edit') && !C('this:moduleAuth',$this_module,'var')) {
                    $this_module['_hideChannel']=true;
                }else{
                    $this_module['_hideChannel']=false;
                }
                $GLOBALS['C']['adminmodulecache'][$this_channel['modulehash']]=$this_module;
            }
            if($this_module['_hideChannel']) {
                $array['channels']=C('this:channel:hideDisabledChannel',$array['channels'],$this_channel['id']);
            }
        }
        $array['auth']['channel_add']=P('channel:add');
        $array['auth']['channel_edit']=P('channel:edit');
        $array['auth']['module_config']=P('module:config');
        $array['auth']['channel_del']=P('channel:del');
        Return V('channel_index',$array);
    }
    function add() {
        if(!$array['classinfo']=C('cms:class:get',@$_GET['classhash'])) {
            Return E('应用不存在');
        }
        if(!$array['classinfo']['module']) {Return E($array['classinfo']['classname'].' 应用无法配置模型');}
        $modulelist=C('cms:module:all',$array['classinfo']['hash']);
        if(!count($modulelist)) {
            Return E('当前应用下没有模型,请先增加模型');
        }
        if(isset($_GET['fid'])) {
            $array['channel']['fid']=intval($_GET['fid']);
            $fid_channel=C('cms:channel:get',$array['channel']['fid']);
            $array['breadcrumb']=C('this:channel:breadcrumb',$fid_channel,$array['classinfo']['hash'],'增加',1);
        }else {
            $array['breadcrumb']=C('this:channel:breadcrumb',false,$array['classinfo']['hash'],'增加',1);
        }
        if(isset($_GET['modulehash']) && is_hash($_GET['modulehash'])) {
            $array['channel']['modulehash']=$_GET['modulehash'];
        }
        $array['channel']['channelorder']=1;
        $max_order_query=array();
        $max_order_query['table']='channel';
        $max_order_query['where']=array('classhash'=>$array['classinfo']['hash']);
        $max_order_query['order']='channelorder desc';
        $max_order=one($max_order_query);
        if(isset($max_order['channelorder'])) {
            $array['channel']['channelorder']=ceil(($max_order['channelorder']+10)/10)*10;
        }
        $array['title']='增加栏目';
        Return V('channel_edit',$array);
    }
    function addPost() {
        if(!$array['classinfo']=C('cms:class:get',@$_POST['classhash'])) {Return E('应用不存在');}
        if(!$array['classinfo']['installed']) {Return E('应用未安装');}
        if(!$array['classinfo']['module']) {Return E($array['classinfo']['classname'].' 应用无法配置模型');}
        if(!@$_POST['modulehash']) {
            Return E('请选择模型');
        }
        $module=C('cms:module:get',@$_POST['modulehash'],$array['classinfo']['hash']);
        if(!$module) {
            Return E('模型不存在');
        }
        $channel_add_array=array();
        $channel_add_array['classhash']=$module['classhash'];
        $channel_add_array['channelname']=trim($_POST['channelname']);
        $channel_add_array['fid']=@$_POST['fid'];
        $channel_add_array['modulehash']=$module['hash'];
        $channel_add_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
        $channel_add_array['channelorder']=intval($_POST['channelorder']);
        if($channel_add_array['channelorder']<0) {$channel_add_array['channelorder']=0;}
        $addreturn=C('cms:channel:add',$channel_add_array);
        if(is_numeric($addreturn)) {
            return array('msg'=>'增加成功','id'=>$addreturn,'url'=>'?do=admin:article:home&cid='.$addreturn,'popup'=>array('btns'=>array('管理'=>array('go'=>'?do=admin:article:home&cid='.$addreturn),'返回'=>'back')));
        }elseif(is_string($addreturn)) {
            Return E($addreturn);
        }elseif(E()){
            Return E(E());
        }
        Return E('增加失败');
    }
    function edit() {
        $array['channel']=C('cms:channel:get',@$_GET['id']);
        if(!$array['channel']) {
            Return E('栏目不存在');
        }
        if(!$array['classinfo']=C('cms:class:get',$array['channel']['classhash'])) {
            Return E('应用不存在');
        }
        if(!$array['classinfo']['module']) {Return E($array['classinfo']['classname'].' 应用无法配置模型');}
        $array['breadcrumb']=C('this:channel:breadcrumb',$array['channel'],$array['classinfo']['hash'],'修改');
        $array['classinfo']=C('cms:class:get',$array['channel']['classhash']);
        $array['title']=$array['channel']['channelname'].' 修改';
        Return V('channel_edit',$array);
    }
    function editPost() {
        if($channel=C('cms:channel:get',@$_POST['id'])){
            $module=C('cms:module:get',@$_POST['modulehash'],$channel['classhash']);
            if(!$module) {
                Return E('模型不存在');
            }
            $channel_edit_array=array();
            $channel_edit_array['id']=$_POST['id'];
            $channel_edit_array['channelname']=trim($_POST['channelname']);
            $channel_edit_array['fid']=$_POST['fid'];
            $channel_edit_array['modulehash']=$module['hash'];
            $channel_edit_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
            $channel_edit_array['channelorder']=intval($_POST['channelorder']);
            if($channel_edit_array['channelorder']<0) {$channel_edit_array['channelorder']=0;}
            $editreturn=C('cms:channel:edit',$channel_edit_array);
            if($editreturn===true) {
                return array('msg'=>'修改成功','id'=>$_POST['id'],'url'=>'?do=admin:article:home&cid='.$_POST['id'],'popup'=>array('btns'=>array('管理'=>array('go'=>'?do=admin:article:home&cid='.$_POST['id']),'返回'=>'back')));
            }elseif(is_string($editreturn)) {
                Return E($editreturn);
            }elseif(E()){
                Return E(E());
            }
            Return E('修改失败');
        }else {
            Return E('栏目不存在');
        }
    }
    function jump() {
        $array['channel']=C('cms:channel:get',@$_GET['id']);
        if(!$array['channel']) {
            Return E('栏目不存在');
        }
        if(!$array['channel']['enabled']) {
            Return E('栏目已禁用');
        }
        $class=C('cms:class:get',$array['channel']['classhash']);
        if(!$class['enabled']){
            Return E('应用未启用');
        }
        if(!isset($array['channel']['link']) || empty($array['channel']['link']) || $array['channel']['link']=='#') {
            Return E('栏目页面未配置');
        }
        echo('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
        echo("<meta name='referrer' content='never'><meta http-equiv=refresh content='0;url=".$array['channel']['link']."'>");
        Return true;
    }
    function jumpModule() {
        $array['channel']=C('cms:channel:get',@$_GET['id']);
        if(!$array['channel']) {
            Return E('栏目不存在');
        }
        if($module=C('cms:module:get',$array['channel']['modulehash'],$array['channel']['classhash'])) {
            echo('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
            echo("<meta name='referrer' content='never'><meta http-equiv=refresh content='0;url=?do=admin:module:config&id=".$module['id']."'>");
            Return true;
        }
        Return E('模型不存在');
    }
    function del() {
        if($channel=C('cms:channel:get',@$_POST['id'])){
            $son_channels=C('cms:channel:tree',$channel['id'],$channel['classhash']);
            if(count($son_channels)) {
                Return E('请先删除下属栏目');
            }
            $delreturn=C('cms:channel:del',$_POST['id']);
            if($delreturn===true) {
                Return '删除成功';
            }elseif(is_string($delreturn)) {
                Return E($delreturn);
            }elseif(E()){
                Return E(E());
            }
            Return E('删除失败');
        }else {
            Return E('栏目不存在');
        }
    }
    function breadcrumb($channel=0,$classhash='',$actionname='',$showself=0) {
        if(!$channel) {
            if(!$class=C('cms:class:get',$classhash)) {
                Return false;
            }
        }else {
            if(!$class=C('cms:class:get',$channel['classhash'])) {
                Return false;
            }
        }
        $breadcrumb=array();
        if(P('class:index')) {
            $breadcrumb[]=array('url'=>'?do=admin:class:index','title'=>'应用管理');
            $classes=C('cms:class:all');
            $classlist=array();
            foreach ($classes as $thisclass) {
                if($thisclass['installed'] && $thisclass['module']){
                    if($thisclass['hash']==$classhash){
                        $classlist[]=array('title'=>$thisclass['classname'],'url'=>'?do=admin:class:config&hash='.$thisclass['hash']);
                    }else{
                        $classlist[]=array('title'=>$thisclass['classname'],'url'=>'?do=admin:channel:index&classhash='.$thisclass['hash']);
                    }
                }
            }
            $breadcrumb[]=array('url'=>'?do=admin:class:config&hash='.$class['hash'],'title'=>$class['classname'],'list'=>$classlist);
        }
        if(P('channel:index')) {
            $breadcrumb[]=array('url'=>'?do=admin:channel:index&classhash='.$class['hash'],'title'=>'栏目');
        }
        if(!$channel) {
            if(!empty($actionname)) {
                $breadcrumb[]=array('title'=>$actionname);
            }
            Return $breadcrumb;
        }
        if(C('this:channel:maxshow',$class['hash'])) {
            $navs=C('cms:channel:parents',$channel['id'],$channel['classhash']);
            foreach($navs as $this_nav) {
                $breadcrumb[]=array('url'=>'?do=admin:channel:index&classhash='.$class['hash'].'&id='.$this_nav['id'],'title'=>$this_nav['channelname']);
            }
            if($showself) {
                $breadcrumb[]=array('url'=>'?do=admin:channel:index&classhash='.$class['hash'].'&id='.$channel['id'],'title'=>$channel['channelname']);
            }
        }
        if(!empty($actionname)) {
            $breadcrumb[]=array('title'=>$channel['channelname'].' '.$actionname);
        }else {
            $breadcrumb[]=array('title'=>$channel['channelname'].' 栏目');
        }
        Return $breadcrumb;
    }
    function hideDisabledChannel($channels,$id){
        foreach($channels as $key=>$this_channel) {
            if($this_channel['fid']==$id){
                $channels=C('this:channel:hideDisabledChannel',$channels,$this_channel['id']);
            }
            if($this_channel['id']==$id){
                $delkey=$key;
            }
        }
        if(isset($delkey)){
            unset($channels[$delkey]);
        }
        return $channels;
    }
}