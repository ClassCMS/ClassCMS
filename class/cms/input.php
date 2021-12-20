<?php
if(!defined('ClassCms')) {exit();}
class cms_input {
    function all() {
        if(isset($GLOBALS['Inputs']) && $GLOBALS['Inputs']) {
            Return $GLOBALS['Inputs'];
        }
        $list_query=array();
        $list_query['table']='input';
        $list_query['where']=array('enabled'=>1,'classenabled'=>1);
        $list_query['order']='inputorder asc,id asc';
        $inputlist=all($list_query);
        $GLOBALS['Inputs']=$inputlist;
        Return $inputlist;
    }
    function tree() {
        $inputs=C('this:input:all');
        $groups=array();
        $groups[]='';
        foreach($inputs as $input) {
            if(!in_array($input['groupname'],$groups)) {
                $groups[]=$input['groupname'];
            }
        }
        $tree=array();
        foreach($groups as $group) {
            if(!empty($group)) {
                $tree[]=array('inputname'=>$group);
            }
            foreach($inputs as $input) {
                if($input['groupname']==$group) {
                    $tree[]=$input;
                }
            }
        }
        Return $tree;
    }
    function get($hash='') {
        $inputs=C('this:input:all');
        if($inputs && is_array($inputs)) {
            foreach($inputs as $input) {
                if($input['hash']==strtolower($hash)) {
                    Return $input;
                }
            }
        }
        Return false;
    }
    function add($inputfunction='') {
        $input_add_query=array();
        $functions=explode(':',$inputfunction);
        if($functions[0]=='this') {
            $functions[0]=I(-1);
        }
        $inputfunction=implode(':',$functions);
        $input_add_query['classhash']=$functions[0];
        if(!is_hash($input_add_query['classhash'])) {Return false;}
        if(count($functions)==2) {
            $input_add_query['classfunction']=$functions[1];
            $input_add_query['hash']=strtolower($functions[1]);
        }elseif(count($functions)==3) {
            $input_add_query['classfunction']=$functions[1].':'.$functions[2];
            $input_add_query['hash']=strtolower($functions[2]);
        }else {
            Return false;
        }
        if($hash=C($inputfunction,'hash')) {
            $input_add_query['hash']=strtolower($hash);
        }else {
            Return false;
        }
        if(!is_hash($input_add_query['hash'])) {
            Return false;
        }
        if(!$input_add_query['inputname']=C($inputfunction,'name')) {
            $input_add_query['inputname']=$input_add_query['hash'];
        }
        if(!$input_add_query['groupname']=C($inputfunction,'group')) {
            $input_add_query['groupname']='';
        }
        if($grouporder=one('table','input','where',where('groupname',$input_add_query['groupname']))){
            $input_add_query['inputorder']=$grouporder['inputorder'];
        }else{
            $input_add_query['inputorder']=1;
        }
        $input_add_query['enabled']=1;
        $input_add_query['classenabled']=1;
        unset($GLOBALS['Inputs']);
        if(C('this:input:get',$input_add_query['hash'])){Return false;}
        $input_add_query['table']='input';
        Return insert($input_add_query);
    }
    function del($hash=0) {
        if(!$input=C('this:input:get',$hash)) {
            Return false;
        }
        unset($GLOBALS['Inputs']);
        $where=array();
        $where['hash']=strtolower($hash);
        $del_input_query=array();
        $del_input_query['table']='input';
        $del_input_query['where']=$where;
        Return del($del_input_query);
    }
    function configReset($config,$loadconfig=1,$loadauth=1) {
        if(isset($config['inputhash'])) {
            if(!$input=C('this:input:get',$config['inputhash'])) {
                Return false;
            }
            $config['function']=$input['classhash'].':'.$input['classfunction'];
        }
        if(!isset($config['function'])) {
            Return false;
        }
        if(isset($config['hash']) && !isset($config['name'])) {
            $config['name']=$config['hash'];
        }
        if(!isset($config['ajax_url'])) {
            $config['ajax_url']='?do=admin:formAjax';
            if(isset($config['id']) && isset($GLOBALS['C']['admin']['load'])) {
                $config['ajax_url'].='&classcms_form_id='.$config['id'].'&csrf='.C('admin:csrfForm');
            }
        }
        if($loadconfig) {
            $input_config=C('this:input:config',$config);
            foreach($input_config as $thisconfig) {
                if(!isset($config[$thisconfig['hash']]) && isset($thisconfig['defaultvalue'])) {
                    $config[$thisconfig['hash']]=$thisconfig['defaultvalue'];
                }
                if(!isset($config[$thisconfig['hash']])) {
                    $config[$thisconfig['hash']]='';
                }
            }
        }
        if($loadauth){
            $input_auth=C($config['function'],'auth',$config);
            $default_auth=array('read'=>'查看','write'=>'修改');
            if(!is_array($input_auth)) {
                $input_auth=$default_auth;
            }else{
                $input_auth=array_merge($default_auth,$input_auth);
            }
            foreach ($input_auth as $key=>$auth) {
                if(!isset($config['auth'][$key])){
                    if($key=='read' || $key=='write'){
                        $config['auth'][$key]=1;
                    }else{
                        if(stripos($key,'|false')===false) {
                            if(isset($config['auth']['all']) && $config['auth']['all']){
                                $config['auth'][$key]=1;
                            }else{
                                $config['auth'][$key]=0;
                            }
                        }else {
                            if(isset($config['auth']['all']) && $config['auth']['all']){
                                $config['auth'][$key]=0;
                            }else{
                                $config['auth'][$key]=1;
                            }
                        }
                        
                    }
                }
            }
            if(isset($config['auth']['write']) && !$config['auth']['write']) {$config['disabled']=1;}
            if(!isset($config['disabled'])) {$config['disabled']=0;}
        }
        Return $config;
    }
    function form($config) {
        if(!$config=C('this:input:configReset',$config,1,1)) {
            Return false;
        }
        if(!isset($config['value'])) {$config['value']='';}
        if(!isset($config['name'])) {$config['name']='';}
        Return C($config['function'],'form',$config);
    }
    function post($config) {
        if(!$config=C('this:input:configReset',$config,1,1)) {
            Return false;
        }
        if(!isset($config['value'])) {$config['value']='';}
        if(!isset($config['name']) || empty($config['name'])) {Return false;}
        $postvalue=C($config['function'],'post',$config);
        if(isset($config['nonull']) && $config['nonull'] && is_string($postvalue) && !strlen($postvalue)) {
            $postvalue=false;
        }
        Return $postvalue;
    }
    function defaultvalue($config) {
        if(!$config=C('this:input:configReset',$config,1,1)) {
            Return false;
        }
        $defaultvalue=C($config['function'],'defaultvalue',$config);
        if($defaultvalue===false && isset($config['defaultvalue'])) {
            $defaultvalue=$config['defaultvalue'];
        }
        if($sql=C($config['function'],'sql',$config)) {
            if(strlen($defaultvalue)==0 && (stripos($sql,'int')!==false || stripos($sql,'decimal')!==false)){
                $defaultvalue=0;
            }
        }
        Return $defaultvalue;
    }
    function view($config) {
        if(!$config=C('this:input:configReset',$config,1,1)) {
            Return false;
        }
        if(!isset($config['value'])) {$config['value']='';}
        $view=C($config['function'],'view',$config);
        if($view===false) {
            $view=htmlspecialchars($config['value']);
        }
        Return $view;
    }
    function config($config) {
        if(!$config=C('this:input:configReset',$config,0,0)) {
            Return array();
        }
        $not_allowed_config=array('formname','hash','enabled','kind','formorder','formwidth','modulehash','classhash','inputhash','tabname','taborder','tips','resetdefault','defaultvalue','nonull','indexshow','value','source','article');
        $config_array=C($config['function'],'config',$config);
        if(is_array($config_array)) {
            foreach($config_array as $key=>$thisconfig) {
                if(in_array($thisconfig['hash'],$not_allowed_config)) {
                    unset($config_array[$key]);
                }
            }
        }else {
            $config_array=array();
        }
        Return $config_array;
    }
    function sql($config) {
        if(!$config=C('this:input:configReset',$config,1,0)) {
            Return false;
        }
        if(!$sql=C($config['function'],'sql',$config)) {
            $sql='varchar(255)';
        }
        Return $sql;
    }
    function ajax($config) {
        if(!$config=C('this:input:configReset',$config,1,1)) {
            Return false;
        }
        $ajax=C($config['function'],'ajax',$config);
        if(isset($ajax['message']) && !isset($ajax['msg'])){
          $ajax['msg']=$ajax['message'];
        }
        Return $ajax;
    }
    function auth($config) {
        if(!$config=C('this:input:configReset',$config,1,0)) {
            Return array();
        }
        $input_auth=C($config['function'],'auth',$config);
        $default_auth=array('read'=>'查看','write'=>'修改');
        if(!is_array($input_auth)) {
            $input_auth=$default_auth;
        }else{
            $input_auth=array_merge($default_auth,$input_auth);
        }
        if(isset($input_auth['all'])){unset($input_auth['all']);}
        Return $input_auth;
    }
}