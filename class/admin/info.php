<?php
if(!defined('ClassCms')) {exit();}
class admin_info {
    function index() {
        $array['infos']=C('cms:form:all','info');
        $array['tabs']=C('cms:form:getTabs',$array['infos']);
        $table_fields=C($GLOBALS['C']['DbClass'].':getfields','user');
        foreach($array['infos'] as $key=>$info) {
            if(isset($table_fields[$info['hash']])) {
                $array['infos'][$key]['create']=1;
            }else {
                $array['infos'][$key]['create']=0;
            }
        }
        V('info_index',$array);
    }
    function editTab() {
        if($info=C('cms:form:get',@$_POST['infoid'])){
            $tabname=htmlspecialchars(trim($_POST['tabname']));
            $where=array();
            $where['classhash']=$info['classhash'];
            $where['modulehash']=$info['modulehash'];
            $where['tabname']=$info['tabname'];
            $where['kind']='info';
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
        $tabnames=explode('|||',$_POST['tabnamearray']);
        foreach($tabnames as $key=>$tabname) {
            if(!empty($tabname)) {
                $where=array();
                $where['tabname']=$tabname;
                $where['kind']='info';
                $edit_tab_query=array();
                $edit_tab_query['table']='form';
                $edit_tab_query['where']=$where;
                $edit_tab_query['taborder']=$key;
                update($edit_tab_query);
            }
        }
        Return C('this:ajax','修改成功');
    }
    function move() {
        if($info=C('cms:form:get',@$_POST['infoid'])){
            if($info['kind']!='info') {
                Return C('this:ajax','属性不存在',1);
            }
            $infoorder=explode('|',$_POST['infoorder']);
            foreach($infoorder as $key=>$infoorderid) {
                if(!empty($infoorderid)) {
                    $edit_info_order_query=array();
                    $edit_info_order_query['table']='form';
                    $edit_info_order_query['where']=array('id'=>intval($infoorderid));
                    $edit_info_order_query['formorder']=count($infoorder)-$key;
                    update($edit_info_order_query);
                }
            }

            $edit_info_query=array();
            $edit_info_query['table']='form';
            $edit_info_query['where']=array('id'=>intval($_POST['infoid']));
            $edit_info_query['tabname']=htmlspecialchars(trim($_POST['tabname']));
            $edit_info_query['formorder']=0;
            $edit_info_query['taborder']=intval($_POST['movetotabindex']);
            update($edit_info_query);

            Return C('this:ajax','已移动至分组 '.htmlspecialchars(trim($_POST['tabname'])));
        }else {
            Return C('this:ajax','属性不存在',1);
        }
    }
    function del() {
        if($info=C('cms:form:get',@$_POST['infoid'])){
            if($info['kind']!='info') {
                Return C('this:ajax','属性不存在',1);
            }
            if(C('cms:form:del',@$_POST['infoid'])){
                Return C('this:ajax','删除成功');
            }elseif(E()){
                Return C('this:ajax',E(),1);
            }
            Return C('this:ajax','删除失败',1);
        }else {
            Return C('this:ajax','属性不存在',1);
        }
    }
    function order() {
        $infoids=explode('|',$_POST['infoidarray']);
        foreach($infoids as $key=>$infoid) {
            $info=C('cms:form:get',intval($infoid));
            if($info && $info['kind']=='info') {
                $info_up_query=array();
                $info_up_query['id']=$infoid;
                $info_up_query['formorder']=count($infoids)-$key;
                C('cms:form:edit',$info_up_query);
            }
        }
        Return C('this:ajax','修改成功');
    }
    function addPost() {
        if(!isset($_POST['infos']) || !is_array($_POST['infos'])) {
            Return C('this:ajax','出错了',1);
        }
        $infos=array();
        foreach($_POST['infos'] as $key=>$val) {
            if(isset($val['name']) && isset($val['value'])) {
                $infos[$val['name']][]=$val['value'];
            }
        }
        if(!isset($infos['formname'])) {
           Return C('this:ajax','出错了',1);
        }
        if(isset($_POST['enabled']) && $_POST['enabled']) {
            $_POST['enabled']=1;
        }else {
            $_POST['enabled']=0;
        }
        $old_table_fields=C($GLOBALS['C']['DbClass'].':getfields','user');
        $msg='';
        $success_info=array();
        foreach($infos['formname'] as $key=>$val) {
            if(isset($infos['tabname'][$key]) && isset($infos['taborder'][$key]) && isset($infos['hash'][$key]) && isset($infos['inputhash'][$key]) && !empty($infos['formname'][$key])) {
                $thismsg='';
                if(!C('cms:form:allowFormName',$infos['formname'][$key])) {
                    $thismsg.=' 属性名不允许包含特殊符号';
                }
                if(isset($old_table_fields[$infos['hash'][$key]])) {
                    unset($old_table_fields[$infos['hash'][$key]]);
                }
                $where=array();
                $where['kind']='info';
                $where['formname']=$infos['formname'][$key];
                $same_form_query=array();
                $same_form_query['table']='form';
                $same_form_query['where']=$where;
                if(one($same_form_query)) {
                    $thismsg.=' 属性名已存在';
                }
                if(empty($infos['hash'][$key])) {
                    if(is_hash($infos['formname'][$key])) {
                        $infos['hash'][$key]=$infos['formname'][$key];
                    }else {
                        $infos['hash'][$key]=C('cms:common:pinyin',$infos['formname'][$key]);
                    }
                }
                if(!is_hash($infos['hash'][$key])) {
                    $thismsg.=' 标识格式错误';
                }
                if(!C('cms:form:allowFormHash',$infos['hash'][$key],'info')) {
                    $thismsg.=' 标识名冲突';
                }
                $where=array();
                $where['kind']='info';
                $where['hash']=$infos['hash'][$key];
                $same_form_query=array();
                $same_form_query['table']='form';
                $same_form_query['where']=$where;
                if(one($same_form_query)) {
                    $thismsg.=' 标识已存在';
                }

                if(!C('cms:input:get',$infos['inputhash'][$key])) {
                    $thismsg.=' 类型不存在';
                }

                if(empty($thismsg)) {
                    $info_add_array=array();
                    $info_add_array['classhash']='admin';
                    $info_add_array['modulehash']='';
                    $info_add_array['kind']='info';
                    $info_add_array['enabled']=$_POST['enabled'];
                    $info_add_array['formname']=htmlspecialchars($infos['formname'][$key]);
                    $info_add_array['hash']=$infos['hash'][$key];
                    $info_add_array['inputhash']=$infos['inputhash'][$key];
                    $info_add_array['tabname']=htmlspecialchars($infos['tabname'][$key]);
                    $info_add_array['taborder']=intval($infos['taborder'][$key]);
                    if(!C('cms:form:add',$info_add_array)) {
                        if(E()){
                            $thismsg.=' '.E();
                        }else{
                            $thismsg.=' 增加失败';
                        }
                    }
                }
                if(empty($thismsg)) {
                    $msg.=htmlspecialchars($infos['formname'][$key]).'['.htmlspecialchars($infos['hash'][$key]).']: 增加成功<br>';
                    $success_info[]=$infos['formname'][$key];
                }else {
                    $msg.=htmlspecialchars($infos['formname'][$key]).'['.htmlspecialchars($infos['hash'][$key]).']:'.$thismsg.'<br>';
                }
            }
        }
        if(empty($msg)) {
            Return C('this:ajax','提交数据有误',1);
        }else {
            if($_POST['enabled']) {
                $new_table_fields=C($GLOBALS['C']['DbClass'].':getfields','user');
                if(count($old_table_fields)==count($new_table_fields) && count($success_info)>0) {
                    $msg='创建用户属性失败,请检查数据库权限<br>'.$msg;
                }
            }
            Return C('this:ajax',$msg);
        }
    }
    function edit() {
        $array=C('cms:form:get',@$_GET['id']);
        if(!$array) {
            Return C('this:error','属性不存在');
        }
        if($array['kind']!='info') {
            Return C('this:error','属性不存在');
        }
        $array['info']=$array;
        $array['tips']=htmlspecialchars($array['tips']);
        $array['config']=C('cms:form:configGet',$array['id']);
        foreach($array['config'] as $key=>$config) {
            $array['config'][$key]['source']='admin_form_setting';
            $array['config'][$key]['auth']['all']=true;
            $array['config'][$key]['ajax_url']='?do=admin:info:ajax&id='.$array['id'].'&confighash='.$config['hash'].'&csrf='.C('admin:csrfForm');
        }

        $array['defaultvalue_form']=C('cms:form:build',$array['id']);
        $array['defaultvalue_form']['value']=$array['defaultvalue'];
        $array['defaultvalue_form']['hash']='defaultvalue';
        $array['defaultvalue_form']['source']='admin_defaultvalue_setting';
        $array['defaultvalue_form']['auth']['all']=true;
        $array['defaultvalue_form']['ajax_url']='?do=admin:info:ajax&id='.$array['id'].'&confighash=defaultvalue&csrf='.C('admin:csrfForm');

        $array['admin_role_name']=C('cms:user:$admin_role');
        $array['input_auths']=C('cms:input:auth',array('inputhash'=>$array['inputhash']));
        $array['roles']=C('cms:user:roleAll');
        foreach($array['roles'] as $key=>$thisrole) {
            $array['roles'][$key]['_editabled']=C('this:roleCheck','admin:info:index',$thisrole['hash'],false);
        }
        $array['title']=$array['formname'].'['.$array['hash'].'] 修改';
        V('info_edit',$array);
    }
    function editPost() {
        if($info=C('cms:form:get',@$_POST['id'])){
            if($info['kind']!='info') {
                Return C('this:ajax','属性不存在',1);
            }
            $formname=trim($_POST['formname']);
            if(!C('cms:form:allowFormName',$formname)) {
                Return C('this:ajax','属性名不允许包含特殊符号',1);
            }
            $where=array();
            $where['id<>']=intval($_POST['id']);
            $where['classhash']=$info['classhash'];
            $where['modulehash']=$info['modulehash'];
            $where['formname']=$formname;
            $where['kind']='info';
            $same_name_query=array();
            $same_name_query['table']='form';
            $same_name_query['where']=$where;
            if(one($same_name_query)) {
                Return C('this:ajax','存在同名属性',1);
            }
            $info_edit_array=array();
            $info_edit_array['id']=$_POST['id'];
            $info_edit_array['formname']=$formname;
            $input=C('cms:input:get',$_POST['inputhash']);
            if($input) {
                $info_edit_array['inputhash']=$input['hash'];
            }else {
                Return C('this:ajax','所选属性类型不存在',1);
            }
            $info_edit_array['formwidth']=intval($_POST['formwidth']);
            $info_edit_array['enabled']=C('cms:input:post',array('inputhash'=>'switch','name'=>'enabled'));
            $info_edit_array['nonull']=C('cms:input:post',array('inputhash'=>'switch','name'=>'nonull'));
            $info_edit_array['indexshow']=C('cms:input:post',array('inputhash'=>'switch','name'=>'indexshow'));
            $info_edit_array['tips']=trim($_POST['tips']);

            if($info_edit_array['inputhash']==$info['inputhash']) {
                $info_config=C('cms:form:configGet',$info['id']);
                foreach($info_config as $val) {
                    $val['source']='admin_form_setting';
                    $val['auth']['all']=true;
                    $info_edit_array['config'][$val['name']]=C('cms:input:post',$val);
                    if($info_edit_array['config'][$val['name']]===false) {
                        Return C('this:ajax','配置项:'.$val['configname'].' 不正确',1);
                    }elseif(is_array($info_edit_array['config'][$val['name']])) {
                        if(isset($info_edit_array['config'][$val['name']]['error'])) {
                            Return C('this:ajax','配置项:'.$val['configname'].' '.$info_edit_array['config'][$val['name']]['error'],1);
                        }else {
                            Return C('this:ajax','配置项:'.$val['configname'].' 不正确',1);
                        }
                    }
                }
                $info_defaultvalue_form=array('inputhash'=>$info['inputhash'],'name'=>'defaultvalue','source'=>'admin_defaultvalue_setting');
                foreach($info_config as $val) {
                    $info_defaultvalue_form[$val['hash']]=$val['value'];
                }
                $info_defaultvalue_form['value']=$info['defaultvalue'];
                $info_defaultvalue_form['auth']['all']=true;
                $info_edit_array['defaultvalue']=C('cms:input:post',$info_defaultvalue_form);
                if(is_array($info_edit_array['defaultvalue']) && isset($info_edit_array['defaultvalue']['error'])) {
                    $info_edit_array['defaultvalue']=false;
                }
            }
            if(C('cms:form:edit',$info_edit_array)) {
                $roles=C('cms:user:roleAll');
                foreach($roles as $role) {
                    $authkind=C('cms:form:authStr',$info);
                    C('cms:user:authDelAll',array('rolehash'=>$role['hash'],'authkind'=>$authkind));
                    if(isset($_POST[$role['hash'].'_role']) && is_array($_POST[$role['hash'].'_role'])) {
                        foreach($_POST[$role['hash'].'_role'] as $thiskey=>$thisval) {
                            $action=C('cms:form:authStr',$info,$thiskey);
                            C('cms:user:authEdit',array('hash'=>$action,'rolehash'=>$role['hash'],'authkind'=>$authkind));
                        }
                    }
                }
                $msg='修改成功';
                if(isset($info_edit_array['defaultvalue']) && $info_edit_array['defaultvalue']===false) {
                    $msg.=',默认值出错';
                }
                if($info_edit_array['inputhash']!=$info['inputhash']) {
                    $msg.=',请重新更改属性配置和默认值';
                }
                $resetdefault=C('cms:input:post',array('inputhash'=>'switch','name'=>'resetdefault'));
                if($info_edit_array['inputhash']==$info['inputhash'] && $resetdefault && $info_edit_array['defaultvalue']!==false) {
                    $table_fields=C($GLOBALS['C']['DbClass'].':getfields','user');
                    if(isset($table_fields[$info['hash']])) {
                        $reset_query=array();
                        $reset_query['table']='user';
                        $reset_query[$info['hash']]=$info_edit_array['defaultvalue'];
                        update($reset_query);
                    }
                }
                Return C('this:ajax',array('msg'=>$msg,'refresh'=>1));
            }else {
                if(E()){Return C('this:ajax',E(),1);}
                Return C('this:ajax','修改失败',1);
            }
        }else {
            Return C('this:ajax','属性不存在',1);
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