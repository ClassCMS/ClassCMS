<?php
if(!defined('ClassCms')) {exit();}
class cms_form {
    function all($kind='',$modulehash='',$classhash='') {
        if(isset($GLOBALS['C']['formlist'][$kind.'|'.$modulehash.'|'.$classhash])) {
            Return $GLOBALS['C']['formlist'][$kind.'|'.$modulehash.'|'.$classhash];
        }
        $form_list_query=array();
        $form_list_query['table']='form';
        if(!is_hash($kind)) {Return false;}
        $form_list_query['where']['kind']=$kind;
        if(!empty($modulehash)) {
            if(!is_hash($modulehash)) {Return false;}
            $form_list_query['where']['modulehash']=$modulehash;
        }
        if(!empty($classhash)) {
            if(!is_hash($classhash)) {Return false;}
            $form_list_query['where']['classhash']=$classhash;
        }
        $form_list_query['order']='taborder asc,formorder desc,id asc';
        $form_list=all($form_list_query);
        $GLOBALS['C']['formlist'][$kind.'|'.$modulehash.'|'.$classhash]=$form_list;
        foreach($form_list as $this_form) {
            $GLOBALS['C']['form'][$this_form['id']]=$this_form;
        }
        Return $form_list;
    }
    function getTabs($form_list) {
        $tabs=array();
        if(!is_array($form_list)) {
            Return $tabs;
        }
        foreach($form_list as $form) {
            if(!in_array($form['tabname'],$tabs)) {
                $tabs[]=$form['tabname'];
            }
        }
        if(!count($tabs)){
            return array('默认分组');
        }
        Return $tabs;
    }
    function get($hash='',$kind='',$modulehash='',$classhash='') {
        $form_query=array();
        $form_query['table']='form';
        $where=array();
        if(C('this:common:verify',$hash,'id')) {
            $where['id']=$hash;
            if(isset($GLOBALS['C']['form'][$hash])) {
                Return $GLOBALS['C']['form'][$hash];
            }
        }else {
            if(empty($classhash) || !is_hash($classhash)) {$classhash=I(-1);}
            $where['hash']=$hash;
            if(!empty($classhash)) {$where['classhash']=$classhash;}
            if(!empty($modulehash)) {$where['modulehash']=$modulehash;}
            if(!empty($kind)) {$where['kind']=$kind;}
        }
        $form_query['where']=$where;
        if($form=one($form_query)) {
            $GLOBALS['C']['form'][$form['id']]=$form;
        }
        Return $form;
    }
    function allowFormName($formname) {
        if(stripos($formname,'.')!==false) {Return false;}
        if(stripos($formname,'(')!==false) {Return false;}
        if(stripos($formname,')')!==false) {Return false;}
        if(stripos($formname,'[')!==false) {Return false;}
        if(stripos($formname,']')!==false) {Return false;}
        if(stripos($formname,'{')!==false) {Return false;}
        if(stripos($formname,'}')!==false) {Return false;}
        if(stripos($formname,'<')!==false) {Return false;}
        if(stripos($formname,'>')!==false) {Return false;}
        if(stripos($formname,'$')!==false) {Return false;}
        if(stripos($formname,';')!==false) {Return false;}
        if(stripos($formname,',')!==false) {Return false;}
        if(stripos($formname,'\'')!==false) {Return false;}
        if(stripos($formname,'/')!==false) {Return false;}
        Return true;
    }
    function allowFormHash($hash,$kind='') {
        if($kind=='var') {
            Return !in_array($hash,array('id','cid','uid','hash','enabled','channelorder','classhash','modulehash','moduleorder','modulename','csrf','active'));
        }
        if($kind=='column') {
            Return C($GLOBALS['C']['DbClass'].':if_field_allow',$hash);
        }
        if($kind=='info') {
            if(!C($GLOBALS['C']['DbClass'].':if_field_allow',$hash)) {
                Return false;
            }
            Return !in_array($hash,array('id','username','hash','passwd','enabled','rolehash'));
        }
        Return true;
    }
    function add($form_add_query) {
        if(!isset($form_add_query['hash']) || !is_hash($form_add_query['hash'])) {
            Return false;
        }
        if(!isset($form_add_query['modulehash'])) {
            $form_add_query['modulehash']='';
        }
        if(!isset($form_add_query['classhash'])) {
            $form_add_query['classhash']=I(-1);
        }
        if(!isset($form_add_query['kind']) || !is_hash($form_add_query['kind'])) {
            Return false;
        }
        if(!isset($form_add_query['tips'])) {$form_add_query['tips']='';}
        if(!isset($form_add_query['formorder'])) {
            $form_add_query['formorder']=0;
        }
        if(!isset($form_add_query['taborder'])) {
            $form_add_query['taborder']=0;
        }
        if(!isset($form_add_query['tabname']) || empty($form_add_query['tabname'])) {
            $form_add_query['tabname']='默认分组';
        }
        if(!isset($form_add_query['formwidth']) || empty($form_add_query['formwidth'])) {
            $form_add_query['formwidth']=100;
        }
        if(!isset($form_add_query['enabled'])) {
            $form_add_query['enabled']=1;
        }
        if(!isset($form_add_query['nonull'])) {
            $form_add_query['nonull']=0;
        }
        if(!isset($form_add_query['indexshow'])) {
            $form_add_query['indexshow']=0;
        }
        if(!isset($form_add_query['defaultvalue'])) {
            $form_add_query['defaultvalue']='';
        }
        if(C('this:form:get',$form_add_query['hash'],$form_add_query['kind'],$form_add_query['modulehash'],$form_add_query['classhash'])){Return false;}
        $form_add_query['table']='form';
        if(isset($form_add_query['config'])) {
            $form_config=array();
            $form_config=$form_add_query['config'];
            unset($form_add_query['config']);
        }
        $formidid=insert($form_add_query);
        if($form_add_query['kind']=='column' && $form_add_query['enabled'] && $formidid) {
            C('this:module:tableCreate',$form_add_query['modulehash'],$form_add_query['classhash']);
            C('this:form:columnReset',$formidid);
        }
        if($form_add_query['kind']=='info' && $form_add_query['enabled'] && $formidid) {
            C('this:form:infoReset',$formidid);
        }
        if(isset($form_config) && is_array($form_config)) {
            foreach($form_config as $configkey=>$configval) {
                C('this:config:set',C('this:form:configStr',$form_add_query,$configkey),$configval,0,$form_add_query['classhash']);
            }
        }
        Return $formidid;
    }
    function edit($form_edit_query) {
        $where=array();
        if(!isset($form_edit_query['id'])) {
            Return false;
        }
        unset($GLOBALS['C']['form'][$form_edit_query['id']]);
        $where['id']=intval($form_edit_query['id']);
        if(!$form=C('this:form:get',$where['id'])) {
            Return false;
        }
        unset($form_edit_query['hash']);
        unset($form_edit_query['classhash']);
        unset($form_edit_query['modulehash']);
        if(isset($form_edit_query['formname'])) {
            $same_name_where=array();
            $same_name_where['id<>']=$form_edit_query['id'];
            $same_name_where['classhash']=$form['classhash'];
            $same_name_where['modulehash']=$form['modulehash'];
            $same_name_where['kind']=$form['kind'];
            $same_name_where['formname']=$form_edit_query['formname'];
            $same_name_channel_query=array();
            $same_name_channel_query['table']='form';
            $same_name_channel_query['where']=$same_name_where;
            if(one($same_name_channel_query)) {
                Return false;
            }
        }
        if(isset($form_edit_query['hash'])) {
            $same_hash_where=array();
            $same_hash_where['id<>']=$form_edit_query['id'];
            $same_hash_where['classhash']=$form['classhash'];
            $same_hash_where['modulehash']=$form['modulehash'];
            $same_hash_where['kind']=$form['kind'];
            $same_hash_where['hash']=$form_edit_query['hash'];
            $same_hash_channel_query=array();
            $same_hash_channel_query['table']='form';
            $same_hash_channel_query['where']=$same_hash_where;
            if(one($same_hash_channel_query)) {
                Return false;
            }
        }
        $form_edit_query['table']='form';
        $form_edit_query['where']=$where;
        if(isset($form_edit_query['inputhash']) && $form_edit_query['inputhash']!=$form['inputhash']) {
            unset($form_edit_query['config']);
            $form_edit_query['defaultvalue']='';
            C('this:form:configDel',$form_edit_query['id']);
        }
        if(isset($form_edit_query['tabname']) && empty($form_edit_query['tabname'])) {
            $form_edit_query['tabname']='默认分组';
        }
        $form_config=array();
        if(isset($form_edit_query['config']) && is_array($form_edit_query['config'])) {
            $form_config=$form_edit_query['config'];
            $input_config=C('this:input:config',array('inputhash'=>$form['inputhash']));
        }
        unset($form_edit_query['config']);
        if(update($form_edit_query)) {
            if(isset($input_config)) {
                foreach($input_config as $this_config) {
                    if(isset($form_config[$this_config['hash']]) && !is_array($form_config[$this_config['hash']])) {
                        C('this:config:set',C('this:form:configStr',$form,$this_config['hash']),$form_config[$this_config['hash']],0,$form['classhash']);
                    }
                }
            }
            if($form['kind']=='column' && isset($form_edit_query['enabled']) && $form_edit_query['enabled']) {
                C('this:module:tableCreate',$form['modulehash'],$form['classhash']);
                C('this:form:columnReset',$form_edit_query['id']);
            }
            if($form['kind']=='info' && isset($form_edit_query['enabled']) && $form_edit_query['enabled']) {
                C('this:form:infoReset',$form_edit_query['id']);
            }
            Return true;
        }
        Return false;
    }
    function del($id) {
        if(!$form=C('this:form:get',$id)) {
            Return false;
        }
        unset($GLOBALS['C']['form'][$form['id']]);
        if($form['kind']=='column') {
            C('this:form:columnDel',$form['id']);
        }
        if($form['kind']=='info') {
            C('this:form:infoDel',$form['id']);
        }
        if($form['kind']=='var') {
            $channels_query=array();
            $channels_query['table']='channel';
            $channels_query['where']=array('modulehash'=>$form['modulehash'],'classhash'=>$form['classhash']);
            $channels=all($channels_query);
            foreach($channels as $channel) {
                C('this:article:delVar',$channel,$form['hash']);
            }
        }
        C('this:form:configDel',$form['id']);
        $roles=C('this:user:roleAll');
        foreach($roles as $role) {
            C('this:user:authDelAll',array('rolehash'=>$role['hash'],'authkind'=>C('this:form:authStr',$form)));
        }
        $form_del_query=array();
        $form_del_query['table']='form';
        $form_del_query['where']=array('id'=>$id);
        Return del($form_del_query);
    }
    function getColumnCreated($columns,$table) {
        $table_fields=C($GLOBALS['C']['DbClass'].':getfields',$table);
        foreach($columns as $key=>$column) {
            if(!isset($table_fields[$column['hash']]) || !$column['enabled']) {
                unset($columns[$key]);
            }
        }
        Return array_merge($columns);
    }
    function columnReset($id) {
        if(!$form=C('this:form:build',$id)) {
            Return false;
        }
        $module=C('this:module:get',$form['modulehash'],$form['classhash']);
        $showerror=$GLOBALS['C']['DbInfo']['showerror'];
        $GLOBALS['C']['DbInfo']['showerror']=0;
        $fields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
        $column_sql=C('this:input:sql',$form);
        if(isset($fields[$form['hash']])) {
            if($column_sql<>$fields[$form['hash']]['Type']) {
                C($GLOBALS['C']['DbClass'].':editField',$module['table'],$form['hash'],$column_sql);
            }
        }else {
            C($GLOBALS['C']['DbClass'].':addField',$module['table'],$form['hash'],$column_sql);
        }
        $GLOBALS['C']['DbInfo']['showerror']=$showerror;
        Return true;
    }
    function columnDel($id) {
        if(!$form=C('this:form:build',$id)) {
            Return false;
        }
        $module=C('this:module:get',$form['modulehash'],$form['classhash']);
        $showerror=$GLOBALS['C']['DbInfo']['showerror'];
        $GLOBALS['C']['DbInfo']['showerror']=0;
        $fields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
        if(isset($fields[$form['hash']])) {
                C($GLOBALS['C']['DbClass'].':delField',$module['table'],$form['hash']);
        }
        $GLOBALS['C']['DbInfo']['showerror']=$showerror;
        Return true;
    }
    function infoReset($id) {
        if(!$form=C('this:form:build',$id)) {
            Return false;
        }
        $showerror=$GLOBALS['C']['DbInfo']['showerror'];
        $GLOBALS['C']['DbInfo']['showerror']=0;
        $fields=C($GLOBALS['C']['DbClass'].':getfields','user');
        $column_sql=C('this:input:sql',$form);
        if(isset($fields[$form['hash']])) {
            if($column_sql<>$fields[$form['hash']]['Type']) {
                C($GLOBALS['C']['DbClass'].':editField','user',$form['hash'],$column_sql);
            }
        }else {
            C($GLOBALS['C']['DbClass'].':addField','user',$form['hash'],$column_sql);
        }
        $GLOBALS['C']['DbInfo']['showerror']=$showerror;
        Return true;
    }
    function infoDel($id) {
        if(!$form=C('this:form:build',$id)) {
            Return false;
        }
        $showerror=$GLOBALS['C']['DbInfo']['showerror'];
        $GLOBALS['C']['DbInfo']['showerror']=0;
        $fields=C($GLOBALS['C']['DbClass'].':getfields','user');
        if(isset($fields[$form['hash']])) {
                C($GLOBALS['C']['DbClass'].':delField','user',$form['hash']);
        }
        $GLOBALS['C']['DbInfo']['showerror']=$showerror;
        Return true;
    }
    function configGet($id) {
        if(!$form=C('this:form:get',$id)) {
            Return array();
        }
        $form_config=C('this:input:config',array('inputhash'=>$form['inputhash']));
        $hashs=array();
        foreach($form_config as $key=>$val) {
            $hashs[]=C('this:form:configStr',$form,$val['hash']);
        }
        $values=C('this:config:gets',$hashs,$form['classhash']);
        foreach($form_config as $key=>$val) {
            $form_config[$key]['name']=$val['hash'];
            $form_config[$key]['classhash']=$form['classhash'];
            $form_config[$key]['modulehash']=$form['modulehash'];
            $form_config[$key]['value']=$values[$key];
            if($form_config[$key]['value']===false && isset($form_config[$key]['defaultvalue'])) {
                $form_config[$key]['value']=$form_config[$key]['defaultvalue'];
            }
        }
        Return $form_config;
    }
    function configDel($id) {
        if(!$form=C('this:form:get',$id)) {
            Return false;
        }
        $form_config=C('this:input:config',array('inputhash'=>$form['inputhash']));
        foreach($form_config as $key=>$val) {
            C('this:config:del',C('this:form:configStr',$form,$val['hash']),$form['classhash']);
        }
        Return true;
    }
    function build($id) {
        if(!$form=C('this:form:get',$id)) {
            Return false;
        }
        $form_config=C('this:form:configGet',$id);
        if(!is_array($form_config)) {
            $form_config=array();
        }
        foreach($form_config as $key=>$val) {
            $form[$val['hash']]=$val['value'];
        }
        Return $form;
    }
    function authStr($form,$action='') {
        if(!empty($action)){ $action=':'.$action; }
        Return $form['classhash'].':_form:'.$form['modulehash'].':'.$form['kind'].':'.$form['hash'].$action;
    }
    function configStr($form,$configname) {
        Return $form['classhash'].':_form:'.$form['modulehash'].':'.$form['kind'].':'.$form['hash'].':'.$configname;
    }
}