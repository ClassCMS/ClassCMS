<?php
if(!defined('ClassCms')) {exit();}
class cms_config {
    function get($hash,$classhash='') {
        $config_query=array();
        $config_query['table']='config';
        if(empty($classhash)) {$classhash=I(-1);}
        if(!is_hash($classhash)) {Return false;}
        $config_query['where']=array('hash'=>$hash,'classhash'=>$classhash);
        $config_query['order']='id desc';
        $config=one($config_query);
        if(!$config) {
            Return false;
        }
        if($config['overtime']!=0 && $config['overtime']<time()) {
            Return false;
        }
        Return $config['value'];
    }
    function gets($hashs,$classhash='') {
        if(!is_array($hashs) || !count($hashs)) {
            Return array();
        }
        $config_query=array();
        $config_query['table']='config';
        if(empty($classhash)) {$classhash=I(-1);}
        if(!is_hash($classhash)) {Return false;}
        $config_query['where']=array('hash'=>$hashs,'classhash'=>$classhash);
        $config_query['order']='id desc';
        $configs=all($config_query);
        $values=array();
        $values_key=array();
        foreach($hashs as $key=>$hash) {
            $in=false;
            foreach($configs as $config) {
                if(!isset($values_key[$hash]) && $hash==$config['hash']) {
                    $in=true;
                    if($config['overtime']!=0 && $config['overtime']<time()) {
                        $config['value']=false;
                    }
                    $values[]=$config['value'];
                    $values_key[$hash]=true;
                }
            }
            if(!$in) {
                $values[]=false;
            }
        }
        Return $values;
    }
    function set($hash,$value='',$overtime=0,$classhash='') {
        if(empty($classhash)) {$classhash=I(-1);}
        if(!is_hash($classhash)) {Return false;}
        $config_query=array();
        $config_query['table']='config';
        $config_query['where']=array('hash'=>$hash,'classhash'=>$classhash);
        $config_query['order']='id desc';
        $config=one($config_query);
        if($config) {
            $config_edit_query=array();
            $config_edit_query['table']='config';
            $config_edit_query['value']=$value;
            $config_edit_query['overtime']=$overtime;
            $config_edit_query['where']=array('id'=>$config['id']);
            Return update($config_edit_query);
        }else {
            $config_add_query=array();
            $config_add_query['table']='config';
            $config_add_query['value']=$value;
            $config_add_query['hash']=$hash;
            $config_add_query['classhash']=$classhash;
            $config_add_query['overtime']=$overtime;
            if(insert($config_add_query)) {
                Return true;
            }
            Return false;
        }
    }
    function del($hash,$classhash='') {
        if(empty($classhash)) {$classhash=I(-1);}
        if(!is_hash($classhash)) {Return false;}
        $del_config_query['table']='config';
        $del_config_query['where']=array('hash'=>$hash,'classhash'=>$classhash);
        Return del($del_config_query);
    }
}