<?php
if(!defined('ClassCms')) {exit();}
class cms_class {
    function path($classhash) {
        Return classDir($classhash);
    }
    function uri($classhash) {
        Return $GLOBALS['C']['SystemDir'].$GLOBALS['C']['ClassDir'].'/'.$classhash.'/';
    }
    function all($enabled=0) {
        $list_query=array();
        $list_query['table']='class';
        if($enabled) {
            $list_query['where']=array('enabled'=>$enabled);
        }
        $list_query['order']='classorder desc,enabled desc,id asc';
        $classlist=all($list_query);
        Return $classlist;
    }
    function get($classhash) {
        if(!is_hash($classhash)) {
            Return false;
        }
        $array=array();
        $array['table']='class';
        $array['where']=array('hash'=>$classhash);
        Return one($array);
    }
    function start($classhash) {
        if(C('this:class:refresh',$classhash)) {
            if(!C('this:class:phpCheck',$classhash)) {
                Return false;
            }
            if(!C('this:class:requires',$classhash)) {
                Return false;
            }
            $startinfo=C($classhash.':start');
            if($startinfo===true || $startinfo===false || $startinfo===null) {
                C('this:class:changeClassConfig',$classhash,1);
                $start_class=array();
                $start_class['table']='class';
                $start_class['where']=array('hash'=>$classhash);
                $start_class['enabled']='1';
                update($start_class);
                C('this:class:installConfig',$classhash);
                C('this:class:installRoute',$classhash);
                C('this:class:installHook',$classhash);
                Return true;
            }
            Return $startinfo;
        }
        Return false;
    }
    function stop($classhash) {
        if(C('this:class:refresh',$classhash)) {
            if(!C('this:class:phpCheck',$classhash)) {
                Return false;
            }
            $stopinfo=C($classhash.':stop');
            if($stopinfo===true || $stopinfo===false || $stopinfo===null) {
                C('this:class:changeClassConfig',$classhash,0);
                $stop_class=array();
                $stop_class['table']='class';
                $stop_class['where']=array('hash'=>$classhash);
                $stop_class['enabled']='0';
                update($stop_class);
                Return true;
            }
            Return $stopinfo;
        }
        Return false;
    }
    function install($classhash,$requirecheck=true) {
        if(C('this:class:refresh',$classhash)) {
            if(!$class=C('this:class:get',$classhash)) {
                Return false;
            }
            if($class['installed']) {
                Return false;
            }
            if(!C('this:class:phpCheck',$classhash)) {
                Return false;
            }
            if($requirecheck && !C('this:class:requires',$classhash)) {
                Return false;
            }
            $installinfo=C($classhash.':install');
            if($installinfo===true || $installinfo===false || $installinfo===null) {
                $new_class=array();
                $new_class['table']='class';
                $new_class['where']=array('hash'=>$classhash);
                $new_class['enabled']='0';
                $new_class['installed']='1';
                update($new_class);
                C('this:class:start',$classhash);
                Return true;
            }
            C('this:class:removeClassConfig',$classhash);
            Return $installinfo;
        }
        Return false;
    }
    function requires($classhash) {
        if(!$class=C('this:class:get',$classhash)) {
            Return false;
        }
        if(empty($class['requires'])) {
            Return true;
        }
        $requires=explode(';',$class['requires']);
        if(!$classes=C('this:class:all')) {
            Return false;
        }
        if(count($requires)) {
            foreach($requires as $key=>$require) {
                $installed=false;
                foreach($classes as $thisclass) {
                    if($thisclass['hash']==$require && $thisclass['installed']) {
                        $installed=true;
                    }
                }
                if(!$installed) {
                    Return false;
                }
            }
        }
        Return true;
    }
    function installConfig($classhash) {
        if($configs=C($classhash.':config')) {
            if(is_array($configs)) {
                foreach($configs as $config) {
                    if(is_array($config) && isset($config['configname']) && isset($config['hash']) && isset($config['inputhash'])) {
                        $newconfig=array();
                        $newconfig['hash']=$config['hash'];
                        $newconfig['formname']=$config['configname'];
                        $newconfig['enabled']=1;
                        $newconfig['kind']='config';
                        $newconfig['classhash']=$classhash;
                        $newconfig['inputhash']=$config['inputhash'];
                        if(isset($config['tabname'])) {$newconfig['tabname']=$config['tabname'];}
                        if(isset($config['tips'])) {$newconfig['tips']=$config['tips'];}
                        if(isset($config['defaultvalue'])) {$newconfig['defaultvalue']=$config['defaultvalue'];}
                        if(isset($config['formorder'])) {$newconfig['formorder']=intval($config['formorder']);}
                        if(isset($config['taborder'])) {$newconfig['taborder']=intval($config['taborder']);}
                        if(isset($config['nonull'])) {$newconfig['nonull']=intval($config['nonull']);}
                        if($form=C('this:form:get',$config['hash'],'config','',$classhash)) {
                            $newconfig['id']=$form['id'];
                            C('this:form:edit',$newconfig);
                        }else {
                            C('this:form:add',$newconfig);
                            if(isset($newconfig['defaultvalue'])) {
                                config($newconfig['hash'],$newconfig['defaultvalue'],$classhash);
                            }
                        }
                    }
                }
            }
        }
        Return true;
    }
    function installRoute($classhash) {
        if($routes=C($classhash.':route')) {
            if(is_array($routes)) {
                foreach($routes as $route) {
                    if(is_array($route) && isset($route['hash']) && isset($route['uri'])) {
                        if(!isset($route['enabled'])) {$route['enabled']=1;}
                        if(!isset($route['function'])) {$route['function']='';}
                        if(!isset($route['view'])) {$route['view']='';}
                        $newroute=array();
                        $newroute['hash']=$route['hash'];
                        $newroute['classhash']=$classhash;
                        $newroute['uri']=$route['uri'];
                        $newroute['enabled']=$route['enabled'];
                        $newroute['classfunction']=$route['function'];
                        $newroute['classview']=$route['view'];
                        if($thisroute=C('this:route:get',$newroute['hash'],'',$classhash)) {
                            $newroute['id']=$thisroute['id'];
                            C('this:route:edit',$newroute);
                        }else {
                            C('this:route:add',$newroute);
                        }
                    }
                }
            }
            if(is_string($routes)) {
                Return $routes;
            }
        }
        Return true;
    }
    function installHook($classhash) {
        if($hooks=C($classhash.':hook')) {
            if(is_array($hooks)) {
                foreach($hooks as $hook) {
                    if(is_array($hook) && isset($hook['hookname']) && isset($hook['hookedfunction'])) {
                        if(!isset($hook['enabled'])) {$hook['enabled']=1;}
                        $newhook=array();
                        $newhook['hookname']=$hook['hookname'];
                        $newhook['classhash']=$classhash;
                        $newhook['hookedfunction']=$hook['hookedfunction'];
                        $newhook['enabled']=$hook['enabled'];
                        if($thishook=C('this:hook:get',$newhook['hookname'],$newhook['hookedfunction'],$classhash)) {
                            $newhook['id']=$thishook['id'];
                            C('this:hook:edit',$newhook);
                        }else {
                            C('this:hook:add',$newhook);
                        }
                    }
                }
            }
            if(is_string($hooks)) {
                Return $hooks;
            }
        }
        Return true;
    }
    function uninstall($classhash) {
        if(!is_hash($classhash)) {Return false;}
        if(is_file(classDir($classhash).$classhash.'.php')) {
            if(!C('this:class:refresh',$classhash)) {
                Return false;
            }
            if(!C('this:class:phpCheck',$classhash)) {
                Return 'phpCheck false';
            }
            $uninstallinfo=C($classhash.':uninstall');
        }else {
            $uninstallinfo=true;
        }
        if($uninstallinfo===true || $uninstallinfo===false || $uninstallinfo===null) {
            C('this:class:removeClassConfig',$classhash);
            $new_class=array();
            $new_class['table']='class';
            $new_class['where']=array('hash'=>$classhash);
            $new_class['enabled']='0';
            $new_class['installed']='0';
            Return update($new_class);
        }
        Return $uninstallinfo;
    }
    function upgrade($classhash) {
        if(!$class=C('this:class:get',$classhash)) {
            Return false;
        }
        $old_version=$class['classversion'];
        if(!$new_version=C('this:class:config',$classhash,'version')) {
            Return false;
        }
        if(version_compare($new_version,$old_version,'<=')) {
            Return false;
        }
        if(!C('this:class:phpCheck',$classhash)) {
            Return false;
        }
        $updateinfo=C($classhash.':upgrade',$old_version);
        if($updateinfo===true || $updateinfo===false || $updateinfo===null) {
            $new_class=array();
            $new_class['table']='class';
            $new_class['where']=array('hash'=>$classhash);
            $new_class['classversion']=$new_version;
            update($new_class);
            C('this:class:refresh',$classhash);
            if($class['enabled']) {
                C('this:class:installConfig',$classhash);
                C('this:class:installRoute',$classhash);
                C('this:class:installHook',$classhash);
            }
            Return true;
        }
        Return $updateinfo;
    }
    function removeClassConfig($classhash) {
        $del_hook=array();
        $del_hook['table']='hook';
        $del_hook['where']=array('classhash'=>$classhash);
        del($del_hook);
        $del_auth=array();
        $del_auth['table']='auth';
        $del_auth['where']=array('classhash'=>$classhash);
        del($del_auth);
        $del_route=array();
        $del_route['table']='route';
        $del_route['where']=array('classhash'=>$classhash);
        del($del_route);
        $del_input=array();
        $del_input['table']='input';
        $del_input['where']=array('classhash'=>$classhash);
        del($del_input);
        $del_channel=array();
        $del_channel['table']='channel';
        $del_channel['where']=array('classhash'=>$classhash);
        del($del_channel);
        $del_form=array();
        $del_form['table']='form';
        $del_form['where']=array('classhash'=>$classhash);
        del($del_form);
        $del_config=array();
        $del_config['table']='config';
        $del_config['where']=array('classhash'=>$classhash);
        del($del_config);
        $modules=all(array('table'=>'module','where'=>where('classhash',$classhash)));
        foreach($modules as $module) {
            $module['table']='article_'.$module['classhash'].'_'.$module['hash'];
            if(strlen($module['table'])>54) {
                $module['table']=substr($module['table'],0,54);
            }
            C($GLOBALS['C']['DbClass'].':delTable',$module['table']);
        }
        $del_module=array();
        $del_module['table']='module';
        $del_module['where']=array('classhash'=>$classhash);
        del($del_module);
        Return true;
    }
    function changeClassConfig($classhash,$enabled) {
        $hook=array();
        $hook['table']='hook';
        $hook['where']=array('classhash'=>$classhash);
        $hook['classenabled']=$enabled;
        update($hook);
        $auth=array();
        $auth['table']='auth';
        $auth['where']=array('classhash'=>$classhash);
        $auth['classenabled']=$enabled;
        update($auth);
        $route=array();
        $route['table']='route';
        $route['where']=array('classhash'=>$classhash);
        $route['classenabled']=$enabled;
        update($route);
        $module=array();
        $module['table']='module';
        $module['where']=array('classhash'=>$classhash);
        $module['classenabled']=$enabled;
        update($module);
        $input=array();
        $input['table']='input';
        $input['where']=array('classhash'=>$classhash);
        $input['classenabled']=$enabled;
        update($input);
        Return true;
    }
    function changeClassOrder($classhash,$order=1) {
        $new_class=array();
        $new_class['table']='class';
        $new_class['where']=array('hash'=>$classhash);
        $new_class['classorder']=$order;
        update($new_class);
        $hook_order=array();
        $hook_order['table']='hook';
        $hook_order['where']=array('classhash'=>$classhash);
        $hook_order['classorder']=$order;
        update($hook_order);
        $route_order=array();
        $route_order['table']='route';
        $route_order['where']=array('classhash'=>$classhash);
        $route_order['classorder']=$order;
        update($route_order);
        Return true;
    }
    function refresh($classhash) {
        if(!is_hash($classhash)) {Return false;}
        if(!is_file(classDir($classhash).$classhash.'.php')) {
            Return false;
        }
        $array=array();
        $array['table']='class';
        $array['where']=array('hash'=>$classhash);
        $class=one($array);
        $new_class=array();
        $new_class['table']='class';
        $config=C('this:class:config',$classhash);
        if(!$class) {
            $new_class['classname']=$classhash;
            $new_class['hash']=$classhash;
            $new_class['classorder']=1;
            $new_class['enabled']='0';
            $new_class['installed']='0';
            if(isset($config['version']) && !empty($config['version'])) {$new_class['classversion']=$config['version'];}else {$new_class['classversion']='1.0';}
        }
        if(isset($config['name']) && !empty($config['name'])) {$new_class['classname']=$config['name'];}
        if(isset($config['ico']) && !empty($config['ico'])) {$new_class['ico']=$config['ico'];}else{$new_class['ico']='layui-icon-component';}
        if(isset($config['requires']) && !empty($config['requires'])) {$new_class['requires']=$config['requires'];}else{$new_class['requires']='';}
        if(isset($config['author']) && !empty($config['author'])) {$new_class['author']=$config['author'];}else{$new_class['author']='';}
        if(isset($config['url']) && !empty($config['url'])) {$new_class['url']=$config['url'];}else{$new_class['url']='';}
        if(isset($config['auth']) && $config['auth']) {$new_class['auth']=1;}else{$new_class['auth']=0;}
        if(isset($config['adminpage']) && !empty($config['adminpage'])) {$new_class['adminpage']=$config['adminpage'];$new_class['auth']=1;}else{$new_class['adminpage']='';}
        if(isset($config['module']) && $config['module']) {$new_class['module']=1;}else{$new_class['module']=0;}
        if($class) {
            $new_class['where']=array('hash'=>$classhash);
            Return update($new_class);
        }else {
            $new_class['classorder']=1;
            $new_class['enabled']='0';
            $new_class['installed']='0';
            $new_class['menu']='0';
            Return insert($new_class);
        }
    }
    function config($classhash='',$key='',$content='') {
        if(empty($content) && is_hash($classhash)) {
            $content=@file_get_contents(classDir($classhash).$classhash.'.config');
        }
        if($content) {
            $content=str_replace(array('\\:','\\;','\\'),array('---colon---','---semicolon---','---slash---'),$content);
            $contents=explode(';',$content);
            $config=array();
            foreach($contents as $line) {
                $linearray=explode(':',$line);
                if(count($linearray)===2 && is_hash(trim($linearray[0]))) {
                    $config[trim($linearray[0])]=trim(str_replace(array('---colon---','---semicolon---','---slash---'),array(':',';','\\'),$linearray[1]));
                }
            }
            if(!empty($key)) {
                if(isset($config[$key])) {
                    Return $config[$key];
                }else {
                    Return false;
                }
            }
            Return $config;
        }
        if(!empty($key)) {Return false;}
        Return array();
    }
    function phpCheck($classhash) {
        if(!$version=C('this:class:config',$classhash,'php')) {
            Return true;
        }
        $versions=explode(';',$version);
        foreach($versions as $thisversion) {
            $operator='=';
            if(stripos($thisversion,'>=')!==false) {
                $thisversion=str_replace('>=','',$thisversion);
                $operator='>=';
            }elseif(stripos($thisversion,'<=')!==false) {
                $thisversion=str_replace('<=','',$thisversion);
                $operator='<=';
            }
            $thisversion=str_replace('=','',$thisversion);
            if(!version_compare(PHP_VERSION,$thisversion,$operator)) {
                Return false;
            }
        }
        Return true;
    }
    function unzip($src_file, $dest_dir=false, $create_zip_name_dir=true, $overwrite=true) 
    {
        if(class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            if ($zip->open($src_file) === TRUE)
            {
                $zip->extractTo($dest_dir);
                $zip->close();
                Return true;
            }
        }elseif(function_exists('zip_open')) {
            cms_createdir($dest_dir);
            if ($zip = zip_open($src_file)){
                if ($zip){
                    if($create_zip_name_dir){
                        $splitter='.';
                    }else {
                        $splitter='/';
                    }
                    if ($dest_dir === false){
                        $dest_dir = substr($src_file, 0, strrpos($src_file, $splitter))."/";
                    }
                    while ($zip_entry = @zip_read($zip)){
                        $pos_last_slash = strrpos(zip_entry_name($zip_entry), "/");
                        if ($pos_last_slash !== false)
                        {
                            cms_createdir($dest_dir.substr(zip_entry_name($zip_entry), 0, $pos_last_slash+1));
                        }
                        if (zip_entry_open($zip,$zip_entry,"r")){
                            $file_name = $dest_dir.zip_entry_name($zip_entry);
                            if ($overwrite === true || $overwrite === false && !is_file($file_name)){
                                $fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                                @file_put_contents($file_name, $fstream);
                            }
                            zip_entry_close($zip_entry);
                        }
                    }
                    @zip_close($zip);
                }
                Return true;
            }
        }
        Return false;
    }
}