<?php
if(!defined('ClassCms')) {exit();}
class cms_channel {
    function all($fid=0,$classhash='',$size=99999999,$var=false,$enabled=0) {
        $channel_list_query=array();
        $channel_list_query['table']='channel';
        if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
        $channel_list_query['where']=array('fid'=>$fid,'classhash'=>$classhash);
        if($enabled) {$channel_list_query['where']['enabled']=1;}
        if($size) {$channel_list_query['limit']=$size;}
        $channel_list_query['order']='channelorder asc,id asc';
        $channel_list=all($channel_list_query);
        if($var) {
            foreach($channel_list as $key=>$channel) {
                $channel_list[$key]=C('this:channel:get',$channel,$channel['classhash']);
            }
        }
        Return $channel_list;
    }
    function home($classhash='') {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
        if(isset($GLOBALS['C']['homechannel'][$classhash])) {
            Return $GLOBALS['C']['homechannel'][$classhash];
        }
        $home_channel_query=array();
        $home_channel_query['table']='channel';
        $home_channel_query['where']=array('fid'=>0,'classhash'=>$classhash);
        $home_channel_query['order']='channelorder asc,id asc';
        if(!$home_channel=one($home_channel_query)) {
            $GLOBALS['C']['homechannel'][$classhash]=false;
            Return false;
        }
        $GLOBALS['C']['homechannel'][$classhash]=C('this:channel:get',$home_channel);
        Return $GLOBALS['C']['homechannel'][$classhash];
    }
    function tree($cid=0,$classhash='',$return=array(),$channels='',$times=0) {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
        if(!is_array($channels)) {
            $list_query=array();
            $list_query['table']='channel';
            $list_query['where']=array('classhash'=>$classhash);
            $list_query['order']='channelorder asc,id asc';
            $channels=all($list_query);
        }
        foreach($channels as $channel) {
            if($channel['fid']==$cid) {
                $channel['ex']='|--'.str_repeat('----',$times*2);
                $return[]=$channel;
                $return=C('this:channel:tree',$channel['id'],$classhash,$return,$channels,$times+1);
            }
        }
        Return $return;
    }
    function parents($cid=0,$classhash='',$parents=array(),$times=0) {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
        if(!$channel=C('this:channel:get',$cid,$classhash)) {
            unset($parents[0]);
            Return array_reverse($parents);
        }
        $parents[]=$channel;
        if($channel['fid']) {
            Return C('this:channel:parents',$channel['fid'],$classhash,$parents,$times+1);
        }else {
            unset($parents[0]);
            Return array_reverse($parents);
        }
    }
    function top($cid=0,$classhash='',$times=0) {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
        if(!$now_channel=C('this:channel:get',$cid,$classhash)) {
            Return false;
        }
        if($times==0 && $now_channel['fid']==0) {
            Return false;
        }
        if($now_channel['fid']==0) {
            Return $now_channel;
        }else {
            Return C('this:channel:top',$now_channel['fid'],$classhash,1);
        }
    }
    function get($cid=0,$classhash='') {
        if(is_array($cid)) {
            $channel=$cid;
        }else {
            $channel_query=array();
            $channel_query['table']='channel';
            $where=array();
            if(C('this:common:verify',$cid,'id')) {
                $where['id']=$cid;
                if(isset($GLOBALS['channel'][$cid])) {
                    Return $GLOBALS['channel'][$cid];
                }
            }else {
                if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
                $where['channelname']=$cid;
                $where['classhash']=$classhash;
            }
            $channel_query['where']=$where;
            if(!$channel=one($channel_query)) {
                Return false;
            }
        }
        $vars=C('this:form:all','var',$channel['modulehash'],$channel['classhash']);
        if(count($vars)) {
            $vars=C('this:article:getVars',$channel,$vars);
            foreach($vars as $var) {
                $channel[$var['hash']]=$var['value'];
                $channel[$var['formname']]=$channel[$var['hash']];
            }
        }
        if(!isset($channel['link']) || empty($channel['link'])) {
            $channel['link']=U($channel);
        }
        if($channel) {
            $GLOBALS['channel'][$channel['id']]=$channel;
        }
        Return $channel;
    }
    function add($channel_add_query) {
        if(!isset($channel_add_query['modulehash']) || !is_hash($channel_add_query['modulehash'])) {
            Return false;
        }
        if(!isset($channel_add_query['classhash'])) {
            $channel_add_query['classhash']=last_class();
        }
        if(!is_hash($channel_add_query['classhash'])) {Return false;}
        if(!isset($channel_add_query['channelorder'])) {
            $channel_add_query['channelorder']=1;
        }
        if(isset($channel_add_query['channelname'])) {
            $channel_add_query['channelname']=str_replace(array('<','>'),array('&lt;','&gt;'),$channel_add_query['channelname']);
        }
        if(!isset($channel_add_query['enabled'])) {
            $channel_add_query['enabled']=1;
        }
        if(isset($channel_add_query['fid']) && $channel_add_query['fid']) {
            if(!C('this:channel:get',$channel_add_query['fid'],$channel_add_query['classhash'])){
                Return false;
            }
        }else {
            $channel_add_query['fid']=0;
        }
        $channel_add_query['table']='channel';
        if(isset($channel_add_query['var'])) {
            $vars=$channel_add_query['var'];
            unset($channel_add_query['var']);
        }
        if(!$id=insert($channel_add_query)) {
            Return false;
        }
        if(isset($vars) && is_array($vars)) {
            foreach($vars as $varkey=>$varval) {
                C('this:article:setVar',$id,$varkey,$varval);
            }
        }
        Return $id;
    }
    function edit($channel_edit_query) {
        $where=array();
        if(!isset($channel_edit_query['id'])) {
            Return false;
        }
        $where['id']=intval($channel_edit_query['id']);
        if(!$channel=C('this:channel:get',$where['id'])) {
            Return false;
        }
        if(isset($channel_edit_query['modulehash'])) {
            if(!C('this:module:get',$channel_edit_query['modulehash'],$channel['classhash'])){
                Return false;
            }
        }
        if(isset($channel_edit_query['fid']) && $channel_edit_query['fid']) {
            if(!C('this:channel:get',$channel_edit_query['fid'],$channel['classhash'])){
                Return false;
            }
            if($channel_edit_query['fid']==$channel_edit_query['id']) {
                Return false;
            }
            $son_channels=C('this:channel:tree',$channel_edit_query['id'],$channel['classhash']);
            foreach($son_channels as $son_channel) {
                if($son_channel['id']==$channel_edit_query['fid']) {
                    Return false;
                }
            }
        }else {
            $channel_edit_query['fid']=0;
        }
        if(isset($channel_edit_query['channelname'])) {
            $channel_edit_query['channelname']=str_replace(array('<','>'),array('&lt;','&gt;'),$channel_edit_query['channelname']);
        }
        unset($channel_edit_query['classhash']);
        $channel_edit_query['table']='channel';
        $channel_edit_query['where']=$where;
        unset($GLOBALS['channel'][$where['id']]);
        Return update($channel_edit_query);
    }
    function del($id,$classhash='') {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=last_class();}
        if(!$channel=C('this:channel:get',$id,$classhash)) {
            Return false;
        }
        $son_channels=C('this:channel:tree',$channel['id'],$channel['classhash']);
        if(count($son_channels)) {
            Return false;
        }
        if($module=C('this:module:get',$channel['modulehash'],$channel['classhash'])) {
            $columns=C('this:form:all','column',$module['hash'],$module['classhash']);
            $columns=C('this:form:getColumnCreated',$columns,$module['table']);
            if(count($columns)) {
                C('this:article:del',array('cid'=>$channel['id'],'where'=>array('cid'=>$channel['id'])));
            }
        }
        $vars=C('this:form:all','var',$channel['modulehash'],$channel['classhash']);
        foreach($vars as $var) {
            C('this:article:delVar',$channel,$var['hash']);
        }
        $channel_del_query=array();
        $channel_del_query['table']='channel';
        $channel_del_query['where']=array('id'=>$channel['id']);
        unset($GLOBALS['channel'][$channel['id']]);
        Return del($channel_del_query);
    }
}