<?php
if(!defined('ClassCms')) {exit();}
class cms_channel {
    function all($fid=0,$classhash='',$size=99999999,$var=false,$hideDisabled=0) {
        $channel_list_query=array();
        $channel_list_query['table']='channel';
        if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
        $channel_list_query['where']=array('fid'=>$fid,'classhash'=>$classhash);
        if($hideDisabled) {$channel_list_query['where']['enabled']=1;}
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
        if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
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
        if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
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
        if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
        if(!$channel=C('this:channel:get',$cid,$classhash)) {
            unset($parents[0]);
            Return array_reverse($parents);
        }
        $parents[]=$channel;
        if($channel['fid']) {
            Return C('this:channel:parents',$channel['fid'],$channel['classhash'],$parents,$times+1);
        }else {
            unset($parents[0]);
            Return array_reverse($parents);
        }
    }
    function url($channel,$routehash='',$article=array(),$args=array(),$fullurl=false) {
        if(!is_array($channel)) {
            if(is_numeric($channel)) {
                if($channel==0) {
                    $channel=C('cms:channel:home');
                }else {
                    $channel=C('cms:channel:get',$channel);
                }
            }else {
                $channel=C('cms:channel:get',$channel,I(-1));
            }
        }
        if(!$channel) {Return '';}
        if(empty($routehash)) {$routehash='channel';}
        if(isset($channel['link']) && !empty($channel['link']) && $routehash=='channel' && !isset($channel['link_channel'])) {
            $channel['link_channel']=$channel['link'];
        }
        if(isset($channel['link_'.$routehash]) && !empty($channel['link_'.$routehash])){
            if(substr($channel['link_'.$routehash],0,1)!='@'){
                return $channel['link_'.$routehash];
            }
            $targetLink=explode(':',substr($channel['link_'.$routehash],1));
            if(strlen($targetLink[0])==0){
                $sonChannel=C('this:channel:all',$channel['id'],$channel['classhash'],1,true,1);
                if(!isset($sonChannel[0])){
                    return '';
                }
                $targetLink[0]=$sonChannel[0];
            }elseif($targetLink[0]==0){
                if(!$homeChannel=C('this:channel:home',$channel['classhash'])){
                    return '';
                }
                $targetLink[0]=$homeChannel;
            }
            if(!isset($targetLink[1]) || empty($targetLink[1])){
                $targetLink[1]='channel';
            }
            return C('this:channel:url',$targetLink[0],$targetLink[1],array(),array(),$fullurl);
        }
        if(isset($article['link']) && !empty($article['link']) && $routehash=='article') {Return $article['link'];}
        if(isset($GLOBALS['route'])) {
            foreach($GLOBALS['route'] as $thisroute) {
                if(isset($thisroute['classhash']) && isset($thisroute['modulehash']) && isset($thisroute['hash']) && $thisroute['classhash']==$channel['classhash'] && $thisroute['modulehash']==$channel['modulehash'] && $thisroute['hash']==$routehash) {
                    $route=$thisroute;
                    break;
                }
            }
        }
        if(!isset($route) || !$route) {Return '';}
        preg_match_all('/[{|\[|(](.*)[}|\]|)]/U',$route['uri'],$getarray);
        foreach($getarray[1] as $key=>$val) {
            if(substr($val,0,2)=='$.') {
                $val=substr($val,2);
                if(isset($channel[$val])) {
                    $route['uri']=str_replace($getarray[0][$key],$channel[$val],$route['uri']);
                }
            }elseif(substr($val,0,1)=='$') {
                $val=substr($val,1);
                if(isset($article[$val])) {
                    $route['uri']=str_replace($getarray[0][$key],$article[$val],$route['uri']);
                }
            }elseif(isset($args[$val])){
                $route['uri']=str_replace($getarray[0][$key],$args[$val],$route['uri']);
            }
        }
        $route['uri']=rewriteUri($route['uri']);
        if(isset($channel['domain']) && !empty($channel['domain'])) {
            $route['domain']=$channel['domain'];
        }elseif(!isset($route['domain'])){
            $route['domain']='';
        }
        if(!$fullurl && macthDomain($route['domain'])) {
            Return $route['uri'];
        }
        $domains=explode(';',strtolower($route['domain']));
        foreach($domains as $domain) {
            if(stripos($domain,'*')===false) {
                break;
            }
        }
        if(empty($domain)){$domain=server_name();}
        if(C('cms:common:serverHttps')){
            Return 'https://'.$domain.server_port().$route['uri'];
        }else{
            Return 'http://'.$domain.server_port().$route['uri'];
        }
    }
    function nav($cid=0,$size=999999,$classhash=''){
        if(empty($classhash)) {$classhash=I(-1);}
        if($cid) {
            if(!$channel=C('cms:channel:get',$cid,$classhash)) {
                Return array();
            }
            $cid=$channel['id'];
            $channels=C('cms:channel:all',$cid,$channel['classhash'],$size,1,1);
        }else{
            $channels=C('cms:channel:all',$cid,$classhash,$size,1,1);
        }
        $parents=array();
        if(isset($GLOBALS['C']['channel']['id'])) {
            $parents[]=$GLOBALS['C']['channel']['id'];
            if(isset($GLOBALS['C']['channel']['fid']) && $GLOBALS['C']['channel']['fid']>0) {
                $parents_channels=C('cms:channel:parents',$GLOBALS['C']['channel']['id']);
                foreach($parents_channels as $parents_channel) {
                    $parents[]=$parents_channel['id'];
                }
            }
        }
        foreach($channels as $key=>$channel) {
            if(in_array($channel['id'],$parents)) {
                $channels[$key]['active']=1;
            }else {
                $channels[$key]['active']=0;
            }
            if(!isset($channel['link']) || empty($channel['link'])){
                unset($channels[$key]);
            }
        }
        Return array_merge($channels);
    }
    function bread($cid=0,$classhash=''){
        if(empty($classhash)) { $classhash=I(-1); }
        if(!$cid && isset($GLOBALS['C']['channel'])) {
            $channel=$GLOBALS['C']['channel'];
        }elseif($cid) {
            if(!$channel=C('cms:channel:get',$cid,$classhash)) {
                Return array();
            }
        }else {
            Return array();
        }
        $channels=C('cms:channel:parents',$channel['id'],$channel['classhash']);
        if($home=C('cms:channel:home',$channel['classhash'])) {
            $in=0;
            foreach ($channels as $thischannels) {
                if($thischannels['id']==$home['id']){
                    $in=1;
                }
            }
            if(!$in){
                array_unshift($channels,$home);
            }
        }
        foreach($channels as $key=>$this_channel) {
            $channels[$key]['active']=0;
        }
        $channel['active']=1;
        $channels[]=$channel;
        Return $channels;
    }
    function moduleChannel($modulehash,$hideDisabled=0,$classhash=''){
        if(empty($classhash)) { $classhash=I(-1); }
        if(!is_hash($modulehash)){
            return array();
        }
        if($hideDisabled) {
            $channels=all(array('table'=>'channel','column'=>'id','where'=>array('enabled'=>1,'classhash'=>$classhash,'modulehash'=>$modulehash)));
        }else {
            $channels=all(array('table'=>'channel','column'=>'id','where'=>array('classhash'=>$classhash,'modulehash'=>$modulehash)));
        }
        $ids=array();
        foreach ($channels as $channel) {
            $ids[]=$channel['id'];
        }
        return $ids;
    }
    function top($cid=0,$classhash='',$times=0) {
        if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
        if(!$now_channel=C('this:channel:get',$cid,$classhash)) {
            Return false;
        }
        if($times==0 && $now_channel['fid']==0) {
            Return false;
        }
        if($now_channel['fid']==0) {
            Return $now_channel;
        }else {
            Return C('this:channel:top',$now_channel['fid'],$now_channel['classhash'],1);
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
                if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
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
        $channel['link']=U($channel);
        if($channel) {
            $GLOBALS['channel'][$channel['id']]=$channel;
        }
        Return $channel;
    }
    function add($channel_add_query) {
        if(!isset($channel_add_query['modulehash']) || !is_hash($channel_add_query['modulehash'])) {
            Return false;
        }
        if(isset($channel_add_query['fid']) && $channel_add_query['fid']) {
            if(!$fidchannel=C('this:channel:get',$channel_add_query['fid'],@$channel_add_query['classhash'])){
                Return false;
            }
            $channel_add_query['classhash']=$fidchannel['classhash'];
        }else {
            $channel_add_query['fid']=0;
        }
        if(!isset($channel_add_query['classhash'])) {
            $channel_add_query['classhash']=I(-1);
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
        if(!isset($channel_add_query['id'])){
            $channel_add_query['id']=C('this:channel:randId',$channel_add_query['classhash']);
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
        if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
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
    function randId($classhash){
        if($lastChannel=one('table','channel','order','id desc','where',where('classhash',$classhash))){
            $id=$lastChannel['id']+1;
        }else{
            $md5str=md5($classhash);
            $hash='';
            for ($i=0; $i<31; $i++) {
                if(is_numeric(substr($md5str,$i,1))){
                    $hash.=substr($md5str,$i,1);
                    if(strlen($hash)>5){
                        break;
                    }
                }
            }
            $id=intval($hash)*100+1;
        }
        return $id;
    }
}