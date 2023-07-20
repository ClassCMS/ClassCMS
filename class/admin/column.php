<?php
if(!defined('ClassCms')) {exit();}
class admin_column {
    function index() {
        $array=C('cms:module:get',@$_GET['id']);
        if(!$array) {
            Return E('模型不存在');
        }
        $array['classinfo']=C('cms:class:get',$array['classhash']);
        if(!$array['classinfo']['module']) {Return E($array['classinfo']['classname'].' 应用无法配置模型');}
        $array['columns']=C('cms:form:all','column',$array['hash'],$array['classhash']);
        $array['tabs']=C('cms:form:getTabs',$array['columns']);
        $table_fields=C($GLOBALS['C']['DbClass'].':getfields',$array['table']);
        foreach($array['columns'] as $key=>$column) {
            if(isset($table_fields[$column['hash']])) {
                $array['columns'][$key]['create']=1;
            }else {
                $array['columns'][$key]['create']=0;
            }
        }
        $array['breadcrumb']=C('this:module:breadcrumb',$array['classinfo'],$array,'字段 [表:'.$array['table'].']');
        $array['title']=$array['modulename'].' 字段';
        Return V('column_index',$array);
    }
    function editTab() {
        if($column=C('cms:form:get',@$_POST['columnid'])){
            $tabname=htmlspecialchars(trim($_POST['tabname']));
            $where=array();
            $where['classhash']=$column['classhash'];
            $where['modulehash']=$column['modulehash'];
            $where['tabname']=$column['tabname'];
            $where['kind']='column';
            $edit_tab_query=array();
            $edit_tab_query['table']='form';
            $edit_tab_query['where']=$where;
            $edit_tab_query['tabname']=$tabname;
            if(update($edit_tab_query)) {
                Return '修改成功';
            }else {
                Return E('修改失败');
            }
        }else {
            Return '修改成功';
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
                    $where['kind']='column';
                    $edit_tab_query=array();
                    $edit_tab_query['table']='form';
                    $edit_tab_query['where']=$where;
                    $edit_tab_query['taborder']=$key;
                    update($edit_tab_query);
                }
            }
            Return '修改成功';
        }else {
            Return E('模型不存在');
        }
    }
    function move() {
        if($column=C('cms:form:get',@$_POST['columnid'])){
            if($column['kind']!='column') {
                Return E('字段不存在');
            }
            $columnorder=explode('|',$_POST['columnorder']);
            foreach($columnorder as $key=>$columnorderid) {
                if(!empty($columnorderid)) {
                    $edit_column_order_query=array();
                    $edit_column_order_query['table']='form';
                    $edit_column_order_query['where']=array('id'=>intval($columnorderid));
                    $edit_column_order_query['formorder']=count($columnorder)-$key;
                    update($edit_column_order_query);
                }
            }

            $edit_column_query=array();
            $edit_column_query['table']='form';
            $edit_column_query['where']=array('id'=>intval($_POST['columnid']));
            $edit_column_query['tabname']=htmlspecialchars(trim($_POST['tabname']));
            $edit_column_query['formorder']=0;
            $edit_column_query['taborder']=intval($_POST['movetotabindex']);
            update($edit_column_query);

            Return '已移动至分组 '.htmlspecialchars(trim($_POST['tabname']));
        }else {
            Return E('字段不存在');
        }
    }
    function del() {
        if($column=C('cms:form:get',@$_POST['columnid'])){
            if($column['kind']!='column') {
                Return E('字段不存在');
            }
            if(C('cms:form:del',@$_POST['columnid'])){
                Return '删除成功';
            }elseif(E()){
                Return E(E());
            }
            Return E('删除失败');
        }else {
            Return E('字段不存在');
        }
    }
    function order() {
        $module=C('cms:module:get',@$_POST['moduleid']);
        if(!$module) {
            Return E('模型不存在');
        }
        $columnids=explode('|',$_POST['columnidarray']);
        foreach($columnids as $key=>$columnid) {
            $column=C('cms:form:get',intval($columnid));
            if($column && $column['kind']=='column' && $column['classhash']==$module['classhash'] && $column['modulehash']==$module['hash']) {
                $column_up_query=array();
                $column_up_query['id']=$columnid;
                $column_up_query['formorder']=count($columnids)-$key;
                C('cms:form:edit',$column_up_query);
            }
        }
        Return '修改成功';
    }
    function addPost() {
        $module=C('cms:module:get',@$_POST['moduleid']);
        if(!$module) {
            Return E('模型不存在');
        }
        if(!isset($_POST['columns']) || !is_array($_POST['columns'])) {
            Return E('出错了');
        }
        $columns=array();
        foreach($_POST['columns'] as $key=>$val) {
            if(isset($val['name']) && isset($val['value'])) {
                $columns[$val['name']][]=$val['value'];
            }
        }
        if(!isset($columns['formname'])) {
            Return E('出错了');
        }
        if(isset($_POST['enabled']) && $_POST['enabled']) {
            $_POST['enabled']=1;
        }else {
            $_POST['enabled']=0;
        }
        $old_table_fields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
        $msg='';
        $success_column=array();
        foreach($columns['formname'] as $key=>$val) {
            if(isset($columns['tabname'][$key]) && isset($columns['taborder'][$key]) && isset($columns['hash'][$key]) && isset($columns['inputhash'][$key]) && !empty($columns['formname'][$key])) {
                $thismsg='';
                if(!C('cms:form:allowFormName',$columns['formname'][$key])) {
                    $thismsg.=' 字段名不允许包含特殊符号';
                }
                if(isset($old_table_fields[$columns['hash'][$key]])) {
                    unset($old_table_fields[$columns['hash'][$key]]);
                }
                if(empty($columns['hash'][$key])) {
                    if(is_hash($columns['formname'][$key])) {
                        $columns['hash'][$key]=$columns['formname'][$key];
                    }else {
                        $columns['hash'][$key]=C('cms:common:pinyin',$columns['formname'][$key]);
                    }
                }
                if(!is_hash($columns['hash'][$key])) {
                    $thismsg.=' 标识格式错误';
                }
                if(!C('cms:form:allowFormHash',$columns['hash'][$key],'column')) {
                    $thismsg.=' 标识名冲突';
                }
                $where=array();
                $where['classhash']=$module['classhash'];
                $where['modulehash']=$module['hash'];
                $where['kind']='column';
                $where['hash']=$columns['hash'][$key];
                $same_form_query=array();
                $same_form_query['table']='form';
                $same_form_query['where']=$where;
                if(one($same_form_query)) {
                    $thismsg.=' 标识已存在';
                }

                if(!C('cms:input:get',$columns['inputhash'][$key])) {
                    $thismsg.=' 类型不存在';
                }

                if(empty($thismsg)) {
                    if(!isset($create_table) && $_POST['enabled']) {
                        if(!C('cms:module:tableCreate',$module['hash'],$module['classhash'])) {
                            Return E('创建模型表失败,请检查数据库权限');
                        }
                        $create_table=1;
                    }

                    $column_add_array=array();
                    $column_add_array['classhash']=$module['classhash'];
                    $column_add_array['modulehash']=$module['hash'];
                    $column_add_array['kind']='column';
                    $column_add_array['enabled']=$_POST['enabled'];
                    $column_add_array['formname']=htmlspecialchars($columns['formname'][$key]);
                    $column_add_array['hash']=$columns['hash'][$key];
                    $column_add_array['inputhash']=$columns['inputhash'][$key];
                    $column_add_array['tabname']=htmlspecialchars($columns['tabname'][$key]);
                    $column_add_array['taborder']=intval($columns['taborder'][$key]);
                    if($column_add_array['hash']=='title') {
                        $column_add_array['indexshow']=1;
                    }
                    if(!C('cms:form:add',$column_add_array)) {
                        if(E()){
                            $thismsg.=' '.E();
                        }else{
                            $thismsg.=' 增加失败';
                        }
                    }
                }
                if(empty($thismsg)) {
                    $msg.=htmlspecialchars($columns['formname'][$key]).'['.htmlspecialchars($columns['hash'][$key]).']: 增加成功<br>';
                    $success_column[]=$columns['formname'][$key];
                }else {
                    $msg.=htmlspecialchars($columns['formname'][$key]).'['.htmlspecialchars($columns['hash'][$key]).']:'.$thismsg.'<br>';
                }
            }
        }
        if(empty($msg)) {
            Return E('提交数据有误');
        }else {
            if($_POST['enabled']) {
                $new_table_fields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
                if(count($old_table_fields)==count($new_table_fields) && count($success_column)>0) {
                    $msg='创建数据库模型字段失败,请检查数据库权限<br>'.$msg;
                }
            }
            Return $msg;
        }
    }
    function edit() {
        $array=C('cms:form:get',@$_GET['id']);
        if(!$array) {
            Return E('字段不存在');
        }
        if($array['kind']!='column') {
            Return E('字段不存在');
        }
        $array['column']=$array;
        $array['tips']=htmlspecialchars($array['tips']);
        $array['module']=C('cms:module:get',$array['modulehash'],$array['classhash']);
        $array['classinfo']=C('cms:class:get',$array['classhash']);
        $array['config']=C('cms:form:configGet',$array['id']);
        foreach($array['config'] as $key=>$config) {
            $array['config'][$key]['source']='admin_form_setting';
            $array['config'][$key]['auth']['all']=true;
            $array['config'][$key]['ajax_url']='?do=admin:column:ajax&formid='.$array['id'].'&confighash='.$config['hash'].'&csrf='.C('admin:csrfForm');
        }
        $array['breadcrumb']=C('this:module:breadcrumb',$array['classinfo'],$array['module']);
        $array['breadcrumb'][]=array('url'=>'?do=admin:column:index&id='.$array['module']['id'],'title'=>'字段');
        $array['breadcrumb'][]=array('url'=>'','title'=>$array['formname'].'['.$array['hash'].'] 修改');

        $array['defaultvalue_form']=C('cms:form:build',$array['id']);
        $array['defaultvalue_form']['value']=$array['defaultvalue'];
        $array['defaultvalue_form']['hash']='defaultvalue';
        $array['defaultvalue_form']['source']='admin_defaultvalue_setting';
        $array['defaultvalue_form']['auth']['all']=true;
        $array['defaultvalue_form']['ajax_url']='?do=admin:column:ajax&formid='.$array['id'].'&confighash=defaultvalue&csrf='.C('admin:csrfForm');
        $array['defaultvalue_form']['nonull']=0;

        $array['admin_role_name']=C('cms:user:$admin_role');
        $array['input_auths']=C('cms:input:auth',array('inputhash'=>$array['inputhash']));
        $array['roles']=C('cms:user:roleAll');
        foreach($array['roles'] as $key=>$thisrole) {
            $array['roles'][$key]['_editabled']=C('this:roleCheck','admin:column:index',$thisrole['hash'],false);
        }
        $array['title']=$array['formname'].'['.$array['hash'].'] 修改';
        Return V('column_edit',$array);
    }
    function editPost() {
        if($column=C('cms:form:get',@$_POST['id'])){
            if($column['kind']!='column') {
                Return E('字段不存在');
            }
            if(!C('cms:form:allowFormName',$_POST['formname'])) {
                Return E('字段名不允许包含特殊符号');
            }
            $column_edit_array=array();
            $column_edit_array['id']=$_POST['id'];
            $column_edit_array['formname']=trim($_POST['formname']);
            $input=C('cms:input:get',$_POST['inputhash']);
            if($input) {
                $column_edit_array['inputhash']=$input['hash'];
            }else {
                Return E('所选字段类型不存在');
            }
            $column_edit_array['formwidth']=intval($_POST['formwidth']);
            $column_edit_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
            $column_edit_array['nonull']=C('cms:input:post',array('inputhash'=>'switch','name'=>'nonull'));
            $column_edit_array['indexshow']=C('cms:input:post',array('inputhash'=>'switch','name'=>'indexshow'));
            $column_edit_array['tips']=trim($_POST['tips']);

            if($column_edit_array['inputhash']==$column['inputhash']) {
                $column_config=C('cms:form:configGet',$column['id']);
                foreach($column_config as $val) {
                    $val['source']='admin_form_setting';
                    $val['auth']['all']=true;
                    $column_edit_array['config'][$val['name']]=C('cms:input:post',$val);
                    if($column_edit_array['config'][$val['name']]===false) {
                        Return E('配置项:'.$val['configname'].' 不正确');
                    }elseif(is_array($column_edit_array['config'][$val['name']])) {
                        if(isset($column_edit_array['config'][$val['name']]['error'])) {
                            Return E('配置项:'.$val['configname'].' '.$column_edit_array['config'][$val['name']]['error']);
                        }else {
                            Return E('配置项:'.$val['configname'].' 不正确');
                        }
                    }
                }
                $column_defaultvalue_form=C('cms:form:build',$column['id']);
                $column_defaultvalue_form['hash']='defaultvalue';
                $column_defaultvalue_form['source']='admin_defaultvalue_setting';
                $column_defaultvalue_form['value']=$column['defaultvalue'];
                $column_defaultvalue_form['auth']['all']=true;
                $column_defaultvalue_form['nonull']=0;
                $column_edit_array['defaultvalue']=C('cms:input:post',$column_defaultvalue_form);
                if(is_array($column_edit_array['defaultvalue']) && isset($column_edit_array['defaultvalue']['error'])) {
                    $column_edit_array['defaultvalue']=false;
                }
            }
            if(C('cms:form:edit',$column_edit_array)) {
                $roles=C('cms:user:roleAll');
                foreach($roles as $role) {
                    $authkind=C('cms:form:authStr',$column);
                    C('cms:user:authDelAll',array('rolehash'=>$role['hash'],'authkind'=>$authkind));
                    if(isset($_POST[$role['hash'].'_role']) && is_array($_POST[$role['hash'].'_role'])) {
                        foreach($_POST[$role['hash'].'_role'] as $thiskey=>$thisval) {
                            $action=C('cms:form:authStr',$column,$thiskey);
                            C('cms:user:authEdit',array('hash'=>$action,'rolehash'=>$role['hash'],'authkind'=>$authkind));
                        }
                    }
                }
                $msg='修改成功';
                if(isset($column_edit_array['defaultvalue']) && $column_edit_array['defaultvalue']===false) {
                    $msg.=',默认值出错';
                }
                if($column_edit_array['inputhash']!=$column['inputhash']) {
                    $msg.=',请重新更改字段配置和默认值';
                }
                $resetdefault=C('cms:input:post',array('inputhash'=>'switch','name'=>'resetdefault'));
                if($column_edit_array['inputhash']==$column['inputhash'] && $resetdefault && $column_edit_array['defaultvalue']!==false) {
                    $module=C('cms:module:get',$column['modulehash'],$column['classhash']);
                    $all_channel=all(array('table'=>'channel','where'=>array('modulehash'=>$column['modulehash'],'classhash'=>$column['classhash'])));
                    if(count($all_channel)) {
                        $cids=array();
                        foreach($all_channel as $this_channel) {
                            $cids[]=$this_channel['id'];
                        }
                        $table_fields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
                        if(isset($table_fields[$column['hash']])) {
                            $reset_query=array();
                            $reset_query['table']=$module['table'];
                            $reset_query['where']=array('cid'=>$cids);
                            $reset_query[$column['hash']]=$column_edit_array['defaultvalue'];
                            update($reset_query);
                        }
                    }
                }
                Return array('msg'=>$msg,'refresh'=>1);
            }else {
                if(E()){Return E(E());}
                Return E('修改失败');
            }
        }else {
            Return E('字段不存在');
        }
    }
    function ajax() {
        if(!$form=C('cms:form:build',@$_GET['formid'])) {
            Return E('输入框不存在');
        }
        if(@$_GET['confighash']=='defaultvalue') {
            $form['hash']='defaultvalue';
            $form['source']='admin_defaultvalue_setting';
            $form['auth']['all']=true;
            Return C('cms:input:ajax',$form);
        }
        $configs=C('cms:form:configGet',$form['id']);
        foreach($configs as $config) {
            if($config['hash']==@$_GET['confighash']) {
                $config['source']='admin_form_setting';
                $config['auth']['all']=true;
                Return C('cms:input:ajax',$config);
            }
        }
        Return E('参数不存在');
    }
}