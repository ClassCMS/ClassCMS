<?php
if(!defined('ClassCms')) {exit();}
class admin_var {
    function index() {
        $array=C('cms:module:get',@$_GET['id']);
        if(!$array) {
            Return C('this:error','模型不存在');
        }
        $array['classinfo']=C('cms:class:get',$array['classhash']);
        if(!$array['classinfo']['module']) {Return C('this:error',$array['classinfo']['classname'].' 应用无法配置模型');}
        $array['vars']=C('cms:form:all','var',$array['hash'],$array['classhash']);
        $array['tabs']=C('cms:form:getTabs',$array['vars']);
        $array['breadcrumb']=C('this:module:breadcrumb',$array['classinfo'],$array,'变量');
        $array['title']=$array['modulename'].' 变量';
        V('var_index',$array);
    }
    function editTab() {
        if($var=C('cms:form:get',@$_POST['varid'])){
            $tabname=htmlspecialchars(trim($_POST['tabname']));
            $where=array();
            $where['classhash']=$var['classhash'];
            $where['modulehash']=$var['modulehash'];
            $where['tabname']=$var['tabname'];
            $where['kind']='var';
            $edit_tab_query=array();
            $edit_tab_query['table']='form';
            $edit_tab_query['where']=$where;
            $edit_tab_query['tabname']=$tabname;
            if(update($edit_tab_query)) {
                Return C('this:ajax','修改成功');
            }else {
                Return C('this:ajax','修改失败',1);
            }
        }else {
            Return C('this:ajax','修改成功');
        }
    }
    function tabOrder() {
        if($module=C('cms:module:get',@$_POST['moduleid'])){
            $tabnames=explode('|||',$_POST['tabnamearray']);
            foreach($tabnames as $key=>$tabname) {
                if(!empty($tabname)) {
                    $where=array();
                    $where['classhash']=$module['classhash'];
                    $where['modulehash']=$module['hash'];
                    $where['tabname']=$tabname;
                    $where['kind']='var';
                    $edit_tab_query=array();
                    $edit_tab_query['table']='form';
                    $edit_tab_query['where']=$where;
                    $edit_tab_query['taborder']=$key;
                    update($edit_tab_query);
                }
            }
            Return C('this:ajax','修改成功');
        }else {
            Return C('this:ajax','模型不存在',1);
        }
    }
    function move() {
        if($var=C('cms:form:get',@$_POST['varid'])){
            if($var['kind']!='var') {
                Return C('this:ajax','变量不存在',1);
            }
            $varorder=explode('|',$_POST['varorder']);
            foreach($varorder as $key=>$varorderid) {
                if(!empty($varorderid)) {
                    $edit_var_order_query=array();
                    $edit_var_order_query['table']='form';
                    $edit_var_order_query['where']=array('id'=>intval($varorderid));
                    $edit_var_order_query['formorder']=count($varorder)-$key;
                    update($edit_var_order_query);
                }
            }

            $edit_var_query=array();
            $edit_var_query['table']='form';
            $edit_var_query['where']=array('id'=>intval($_POST['varid']));
            $edit_var_query['tabname']=htmlspecialchars(trim($_POST['tabname']));
            $edit_var_query['formorder']=0;
            $edit_var_query['taborder']=intval($_POST['movetotabindex']);
            update($edit_var_query);

            Return C('this:ajax','已移动至分组 '.htmlspecialchars(trim($_POST['tabname'])));
        }else {
            Return C('this:ajax','变量不存在',1);
        }
    }
    function del() {
        if($var=C('cms:form:get',@$_POST['varid'])){
            if($var['kind']!='var') {
                Return C('this:ajax','变量不存在',1);
            }
            if(C('cms:form:del',@$_POST['varid'])){
                Return C('this:ajax','删除成功');
            }elseif(E()){
                Return C('this:ajax',E(),1);
            }
            Return C('this:ajax','删除失败',1);
        }else {
            Return C('this:ajax','变量不存在',1);
        }
    }
    function order() {
        $module=C('cms:module:get',@$_POST['moduleid']);
        if(!$module) {
            Return C('this:error','模型不存在');
        }
        $varids=explode('|',$_POST['varidarray']);
        foreach($varids as $key=>$varid) {
            $var=C('cms:form:get',intval($varid));
            if($var && $var['kind']=='var' && $var['classhash']==$module['classhash'] && $var['modulehash']==$module['hash']) {
               $var_up_query=array();
                $var_up_query['id']=$varid;
                $var_up_query['formorder']=count($varids)-$key;
                C('cms:form:edit',$var_up_query);
            }
        }
        Return C('this:ajax','修改成功');
    }
    function addPost() {
        $module=C('cms:module:get',@$_POST['moduleid']);
        if(!$module) {
            Return C('this:ajax','模型不存在',1);
        }
        if(!isset($_POST['vars']) || !is_array($_POST['vars'])) {
            Return C('this:ajax','出错了',1);
        }
        $vars=array();
        foreach($_POST['vars'] as $key=>$val) {
            if(isset($val['name']) && isset($val['value'])) {
                $vars[$val['name']][]=$val['value'];
            }
        }
        if(!isset($vars['formname'])) {
           Return C('this:ajax','出错了',1);
        }
        $msg='';
        foreach($vars['formname'] as $key=>$val) {
            if(isset($vars['tabname'][$key]) && isset($vars['taborder'][$key]) && isset($vars['hash'][$key]) && isset($vars['inputhash'][$key]) && !empty($vars['formname'][$key])) {
                $thismsg='';
                if(!C('cms:form:allowFormName',$vars['formname'][$key])) {
                    $thismsg.=' 变量名不允许包含特殊符号';
                }
                $where=array();
                $where['classhash']=$module['classhash'];
                $where['modulehash']=$module['hash'];
                $where['kind']='var';
                $where['formname']=$vars['formname'][$key];
                $same_form_query=array();
                $same_form_query['table']='form';
                $same_form_query['where']=$where;
                if(one($same_form_query)) {
                    $thismsg.=' 变量名已存在';
                }
                if(empty($vars['hash'][$key])) {
                    if(is_hash($vars['formname'][$key])) {
                        $vars['hash'][$key]=$vars['formname'][$key];
                    }else {
                        $vars['hash'][$key]=C('cms:common:pinyin',$vars['formname'][$key]);
                    }
                }
                if(!is_hash($vars['hash'][$key])) {
                    $thismsg.=' 标识格式错误';
                }
                if(!C('cms:form:allowFormHash',$vars['hash'][$key],'var')) {
                    $thismsg.=' 此标识名为系统内置,无法增加';
                }
                $where=array();
                $where['classhash']=$module['classhash'];
                $where['modulehash']=$module['hash'];
                $where['kind']='var';
                $where['hash']=$vars['hash'][$key];
                $same_form_query=array();
                $same_form_query['table']='form';
                $same_form_query['where']=$where;
                if(one($same_form_query)) {
                    $thismsg.=' 标识已存在';
                }
                if(!C('cms:input:get',$vars['inputhash'][$key])) {
                    $thismsg.=' 类型不存在';
                }
                if(empty($thismsg)) {
                    $var_add_array=array();
                    $var_add_array['classhash']=$module['classhash'];
                    $var_add_array['modulehash']=$module['hash'];
                    $var_add_array['kind']='var';
                    $var_add_array['enabled']=1;
                    $var_add_array['formname']=htmlspecialchars($vars['formname'][$key]);
                    $var_add_array['hash']=$vars['hash'][$key];
                    $var_add_array['inputhash']=$vars['inputhash'][$key];
                    $var_add_array['tabname']=htmlspecialchars($vars['tabname'][$key]);
                    $var_add_array['taborder']=intval($vars['taborder'][$key]);
                    if(!C('cms:form:add',$var_add_array)) {
                        if(E()){
                            $thismsg.=' '.E();
                        }else{
                            $thismsg.=' 增加失败';
                        }
                    }
                }
                if(empty($thismsg)) {
                    $msg.=htmlspecialchars($vars['formname'][$key]).'['.htmlspecialchars($vars['hash'][$key]).']: 增加成功<br>';
                }else {
                    $msg.=htmlspecialchars($vars['formname'][$key]).'['.htmlspecialchars($vars['hash'][$key]).']:'.$thismsg.'<br>';
                }
            }
        }
        if(empty($msg)) {
            Return C('this:ajax','提交数据有误',1);
        }else {
            Return C('this:ajax',$msg);
        }
    }
    function edit() {
        $array=C('cms:form:get',@$_GET['id']);
        if(!$array) {
            Return C('this:error','变量不存在');
        }
        if($array['kind']!='var') {
            Return C('this:error','变量不存在');
        }
        $array['var']=$array;
        $array['tips']=htmlspecialchars($array['tips']);
        $array['module']=C('cms:module:get',$array['modulehash'],$array['classhash']);
        $array['classinfo']=C('cms:class:get',$array['classhash']);
        $array['config']=C('cms:form:configGet',$array['id']);
        foreach($array['config'] as $key=>$config) {
            $array['config'][$key]['source']='admin_form_setting';
            $array['config'][$key]['auth']['all']=true;
            $array['config'][$key]['ajax_url']='?do=admin:var:ajax&id='.$array['id'].'&confighash='.$config['hash'].'&csrf='.C('admin:csrfForm');
        }

        $array['breadcrumb']=C('this:module:breadcrumb',$array['classinfo'],$array['module']);
        $array['breadcrumb'][]=array('url'=>'?do=admin:var:index&id='.$array['module']['id'],'title'=>'变量');
        $array['breadcrumb'][]=array('url'=>'','title'=>$array['formname'].'['.$array['hash'].'] 管理');

        $array['defaultvalue_form']=C('cms:form:build',$array['id']);
        $array['defaultvalue_form']['value']=$array['defaultvalue'];
        $array['defaultvalue_form']['hash']='defaultvalue';
        $array['defaultvalue_form']['source']='admin_defaultvalue_setting';
        $array['defaultvalue_form']['auth']['all']=true;
        $array['defaultvalue_form']['ajax_url']='?do=admin:var:ajax&id='.$array['id'].'&confighash=defaultvalue&csrf='.C('admin:csrfForm');

        $array['admin_role_name']=C('cms:user:$admin_role');
        $array['input_auths']=C('cms:input:auth',array('inputhash'=>$array['inputhash']));
        $array['roles']=C('cms:user:roleAll');
        foreach($array['roles'] as $key=>$thisrole) {
            $array['roles'][$key]['_editabled']=C('this:roleCheck','admin:var:index',$thisrole['hash'],false);
        }
        $array['title']=$array['formname'].'['.$array['hash'].'] 修改';
        V('var_edit',$array);
    }
    function editpost() {
        if($var=C('cms:form:get',@$_POST['id'])){
            if($var['kind']!='var') {
                Return C('this:ajax','变量不存在',1);
            }

            $formname=trim($_POST['formname']);
            if(!C('cms:form:allowFormName',$formname)) {
                Return C('this:ajax','变量名不允许包含特殊符号',1);
            }
            $where=array();
            $where['id<>']=intval($_POST['id']);
            $where['classhash']=$var['classhash'];
            $where['modulehash']=$var['modulehash'];
            $where['formname']=$formname;
            $where['kind']='var';
            $same_name_query=array();
            $same_name_query['table']='form';
            $same_name_query['where']=$where;
            if(one($same_name_query)) {
                Return C('this:ajax','存在同名变量',1);
            }
            $var_edit_array=array();
            $var_edit_array['id']=$_POST['id'];
            $var_edit_array['formname']=$formname;
            $input=C('cms:input:get',$_POST['inputhash']);
            if($input) {
                $var_edit_array['inputhash']=$input['hash'];
            }else {
                Return C('this:ajax','所选变量类型不存在',1);
            }
            $var_edit_array['formwidth']=intval($_POST['formwidth']);
            $var_edit_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
            $var_edit_array['nonull']=C('cms:input:post',array('inputhash'=>'switch','name'=>'nonull'));
            $var_edit_array['indexshow']=0;
            $var_edit_array['tips']=trim($_POST['tips']);

            if($var_edit_array['inputhash']==$var['inputhash']) {
                $var_config=C('cms:form:configGet',$var['id']);
                foreach($var_config as $val) {
                    $val['source']='admin_form_setting';
                    $val['auth']['all']=true;
                    $var_edit_array['config'][$val['name']]=C('cms:input:post',$val);
                    if($var_edit_array['config'][$val['name']]===false) {
                        Return C('this:ajax','配置项:'.$val['configname'].' 不正确',1);
                    }elseif(is_array($var_edit_array['config'][$val['name']])) {
                        if(isset($var_edit_array['config'][$val['name']]['error'])) {
                            Return C('this:ajax','配置项:'.$val['configname'].' '.$var_edit_array['config'][$val['name']]['error'],1);
                        }else {
                            Return C('this:ajax','配置项:'.$val['configname'].' 不正确',1);
                        }
                    }
                }
                $var_defaultvalue_form=array('inputhash'=>$var['inputhash'],'name'=>'defaultvalue','source'=>'admin_defaultvalue_setting');
                foreach($var_config as $val) {
                    $var_defaultvalue_form[$val['hash']]=$val['value'];
                }
                $var_defaultvalue_form['value']=$var['defaultvalue'];
                $var_defaultvalue_form['auth']['all']=true;
                $var_edit_array['defaultvalue']=C('cms:input:post',$var_defaultvalue_form);
                if(is_array($var_edit_array['defaultvalue']) && isset($var_edit_array['defaultvalue']['error'])) {
                    $var_edit_array['defaultvalue']=false;
                }
            }
            if(C('cms:form:edit',$var_edit_array)) {
                $roles=C('cms:user:roleAll');
                foreach($roles as $role) {
                    $authkind=C('cms:form:authStr',$var);
                    C('cms:user:authDelAll',array('rolehash'=>$role['hash'],'authkind'=>$authkind));
                    if(isset($_POST[$role['hash'].'_role']) && is_array($_POST[$role['hash'].'_role'])) {
                        foreach($_POST[$role['hash'].'_role'] as $thiskey=>$thisval) {
                            $action=C('cms:form:authStr',$var,$thiskey);
                            C('cms:user:authEdit',array('hash'=>$action,'rolehash'=>$role['hash'],'authkind'=>$authkind));
                        }
                    }
                }
                $msg='修改成功';
                if(isset($var_edit_array['defaultvalue']) && $var_edit_array['defaultvalue']===false) {
                    $msg.=',默认值出错';
                }
                if($var_edit_array['inputhash']!=$var['inputhash']) {
                    $msg.=',请重新更改变量配置和默认值';
                }
                $resetdefault=C('cms:input:post',array('inputhash'=>'switch','name'=>'resetdefault'));
                if($var_edit_array['inputhash']==$var['inputhash'] && $resetdefault && $var_edit_array['defaultvalue']!==false) {
                    $all_channel=all(array('table'=>'channel','where'=>array('modulehash'=>$var['modulehash'],'classhash'=>$var['classhash'])));
                    foreach($all_channel as $this_channel) {
                        C('cms:article:setVar',$this_channel,$var['hash'],$var_edit_array['defaultvalue']);
                    }
                }
                Return C('this:ajax',array('msg'=>$msg,'refresh'=>1));
            }else {
                if(E()){Return C('this:ajax',E(),1);}
                Return C('this:ajax','修改失败',1);
            }
        }else {
            Return C('this:ajax','变量不存在',1);
        }
    }
    function ajax() {
        if(!$form=C('cms:form:build',@$_GET['id'])) {
            Return C('this:ajax','输入框不存在',1);
        }
        if(@$_GET['confighash']=='defaultvalue') {
            $form['hash']='defaultvalue';
            $form['source']='admin_defaultvalue_setting';
            $form['auth']['all']=true;
            $ajax=C('cms:input:ajax',$form);
            Return C('this:ajax',$ajax);
        }
        $configs=C('cms:form:configGet',$form['id']);
        foreach($configs as $config) {
            if($config['hash']==@$_GET['confighash']) {
                $config['source']='admin_form_setting';
                $config['auth']['all']=true;
                $ajax=C('cms:input:ajax',$config);
                Return C('this:ajax',$ajax);
            }
        }
        Return C('this:ajax','参数不存在',1);
    }
}