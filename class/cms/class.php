<?php
if(!defined('ClassCms')) {exit();}
class cms_class {
    function path($classhash) {
        Return classDir($classhash);
    }
    function uri($classhash) {
        Return $GLOBALS['C']['SystemDir'].$GLOBALS['C']['ClassDir'].'/'.$classhash.'/';
    }
    function defaultClass(){
        $classlist=C('this:class:all',1);
        foreach ($classlist as $class) {
            if($class['module']){
                Return $class['hash'];
            }
        }
        Return false;
    }
    function all($enabled=0) {
        $list_query=array();
        $list_query['table']='class';
        if($enabled) {
            $list_query['where']=array('enabled'=>$enabled);
        }
        $list_query['order']='enabled desc,classorder desc,id asc';
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
            if(!$class=C('this:class:get',$classhash)) {
                Return false;
            }
            if($class['enabled']){
                Return false;
            }
            if(!C('this:class:phpCheck',$classhash)) {
                Return false;
            }
            if(!C('this:class:requires',$classhash)) {
                Return false;
            }
            $startinfo=C($classhash.':start');
            if($startinfo===null) { $startinfo=true; }
            if(!$startinfo){
                if(E()){
                    Return E(E());
                }
                Return false;
            }
            C('this:class:changeClassConfig',$classhash,1);
            update(array('table'=>'class','enabled'=>'1','where'=>array('hash'=>$classhash)));
            C('this:class:installConfig',$classhash);
            C('this:class:installRoute',$classhash);
            C('this:class:installHook',$classhash);
            Return $startinfo;
        }
        Return false;
    }
    function stop($classhash) {
        if(C('this:class:refresh',$classhash)) {
            if(!$class=C('this:class:get',$classhash)) {
                Return false;
            }
            if(!$class['enabled']){
                Return false;
            }
            if(!C('this:class:phpCheck',$classhash)) {
                Return false;
            }
            $stopinfo=C($classhash.':stop');
            if($stopinfo===null) { $stopinfo=true; }
            if(!$stopinfo){
                if(E()){
                    Return E(E());
                }
                Return false;
            }
            C('this:class:changeClassConfig',$classhash,0);
            update(array('table'=>'class','enabled'=>'0','where'=>array('hash'=>$classhash)));
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
            C('this:class:installTable',$classhash);
            C('this:class:installData',$classhash);
            $installinfo=C($classhash.':install');
            if($installinfo===null){ $installinfo=true; }
            if(!$installinfo){
                if(E()){
                    $installinfo=E();
                }
                C('this:class:removeClassConfig',$classhash);
                if($installinfo){
                    Return E($installinfo);
                }
                Return false;
            }
            update(array('table'=>'class','enabled'=>'0','installed'=>'1','where'=>array('hash'=>$classhash)));
            C('this:class:start',$classhash);
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
            foreach($requires as $require) {
                $enabled=false;
                @preg_match_all('/\[.*?\]/',$require,$requireversions);
                if(isset($requireversions[0][0])){
                    $requireclasshash=rtrim($require,$requireversions[0][0]);
                }else{
                    $requireclasshash=$require;
                }
                foreach($classes as $thisclass) {
                    if($thisclass['hash']==$requireclasshash && $thisclass['enabled']) {
                        if(isset($requireversions[0][0])){
                            $thisversions=explode(',',rtrim(ltrim($requireversions[0][0],'['),']'));
                            foreach ($thisversions as $thisversion) {
                                if(!empty($thisversion)){
                                    if(substr($thisversion,0,2)=='<='){
                                        if(!version_compare($thisclass['classversion'],substr($thisversion,2),'<=')){
                                            Return false;
                                        }
                                    }elseif(substr($thisversion,0,2)=='>='){
                                        if(!version_compare($thisclass['classversion'],substr($thisversion,2),'>=')){
                                            Return false;
                                        }
                                    }elseif(substr($thisversion,0,1)=='<'){
                                        if(!version_compare($thisclass['classversion'],substr($thisversion,1),'<')){
                                            Return false;
                                        }
                                    }elseif(substr($thisversion,0,1)=='>'){
                                        if(!version_compare($thisclass['classversion'],substr($thisversion,1),'>')){
                                            Return false;
                                        }
                                    }elseif(substr($thisversion,0,1)=='='){
                                        if(!version_compare($thisclass['classversion'],substr($thisversion,1),'=')){
                                            Return false;
                                        }
                                    }elseif(!version_compare($thisclass['classversion'],$thisversion,'=')){
                                        Return false;
                                    }
                                }
                            }
                        }
                        $enabled=true;
                    }
                }
                if(!$enabled) {
                    Return false;
                }
            }
        }
        Return true;
    }
    function installConfig($classhash) {
        if($configs=C($classhash.':config')) {
            if(is_array($configs)) {
                $autoOrder=true;
                foreach($configs as $config) {
                    if(isset($config['formorder'])){
                        $autoOrder=false;
                    }
                }
                foreach($configs as $key=>$config) {
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
                        if(isset($config['formorder'])) {
                            $newconfig['formorder']=intval($config['formorder']);
                        }elseif($autoOrder){
                            $newconfig['formorder']=count($configs)-$key;
                        }else{
                            $newconfig['formorder']=0;
                        }
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
        if(!del(array('table'=>'route','where'=>array('classhash'=>$classhash,'modulehash'=>'')))){
            Return false;
        }
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
                        C('this:route:add',$newroute);
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
        if(!del(array('table'=>'hook','where'=>array('classhash'=>$classhash)))){
            Return false;
        }
        if($hooks=C($classhash.':hook')) {
            if(is_array($hooks)) {
                foreach($hooks as $hook) {
                    if(is_array($hook) && isset($hook['hookname']) && isset($hook['hookedfunction'])) {
                        if(!isset($hook['enabled'])) {$hook['enabled']=1;}
                        if(!isset($hook['requires'])) {$hook['requires']='';}
                        if(!isset($hook['hookorder'])) {$hook['hookorder']=1;}
                        $newhook=array();
                        $newhook['hookname']=$hook['hookname'];
                        $newhook['classhash']=$classhash;
                        $newhook['hookedfunction']=$hook['hookedfunction'];
                        $newhook['requires']=$hook['requires'];
                        $newhook['hookorder']=$hook['hookorder'];
                        $newhook['enabled']=$hook['enabled'];
                        C('this:hook:add',$newhook);
                    }
                }
            }
            if(is_string($hooks)) {
                Return $hooks;
            }
        }
        Return true;
    }
    function installTable($classhash) {
        if($tables=C($classhash.':table')) {
            if(is_array($tables)) {
                foreach($tables as $tablename=>$table) {
                    if(is_array($table)) {
                        C($GLOBALS['C']['DbClass'].':createTable',$tablename,$table);
                    }
                }
            }
        }
        Return true;
    }
    function installData($classhash,$datafile=''){
        if(!$class=C('this:class:get',$classhash)) {
            Return false;
        }
        if(empty($datafile)){
            $datafile=classDir($classhash).$classhash.'.data.php';
        }
        if(is_file($datafile)) {
            $content=file_get_contents($datafile);
            $content=str_replace("<?php if(!defined('ClassCms')) {exit();}?>","",$content);
            $tables=json_decode($content,1);
            if(is_array($tables)){
                C('this:class:installTable',$classhash);
                foreach ($tables as $key => $table) {
                    foreach ($table as $data) {
                        if($key!='channel'){
                            unset($data['id']);
                        }
                        if($key=='form'){
                            C('cms:form:add',$data);
                        }else{
                            $data['table']=$key;
                            if($key=='route' || $key=='hook'){
                                $data['classorder']=$class['classorder'];
                            }
                            if($key=='auth' || $key=='hook' || $key=='input' || $key=='module' || $key=='route'){
                                $data['classenabled']=$class['enabled'];
                            }
                            insert($data);
                        }
                    }
                }
            }
        }
        return true;
    }
    function uninstall($classhash) {
        if(!is_hash($classhash)) {Return false;}
        if(!$class=C('this:class:get',$classhash)) {
            Return false;
        }
        if(!$class['installed']) {
            Return false;
        }
        if(is_file(classDir($classhash).$classhash.'.php')) {
            if(!C('this:class:refresh',$classhash)) {
                Return false;
            }
            if(!C('this:class:phpCheck',$classhash)) {
                Return 'phpCheck false';
            }
            $uninstallinfo=C($classhash.':uninstall');
            if($uninstallinfo===null) { $uninstallinfo=true; }
            if(!$uninstallinfo){
                if(E()){
                    return E(E());
                }
                return false;
            }
        }else {
            $uninstallinfo=true;
        }
        C('this:class:removeClassConfig',$classhash);
        update(array('table'=>'class','enabled'=>'0','installed'=>'0','where'=>array('hash'=>$classhash)));
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
        if($class['installed']) {
            if(!C('this:class:requires',$classhash)) {
                Return false;
            }
            $updateinfo=C($classhash.':upgrade',$old_version);
            if($updateinfo===null) {$updateinfo=true;}
            if(!$updateinfo){
                if(E()){
                    Return E(E());
                }
                Return false;
            }
        }else {
            $updateinfo=true;
        }
        update(array('table'=>'class','classversion'=>$new_version,'where'=>array('hash'=>$classhash)));
        C('this:class:refresh',$classhash);
        if($class['enabled']) {
            C('this:class:installConfig',$classhash);
            C('this:class:installRoute',$classhash);
            C('this:class:installHook',$classhash);
        }
        Return $updateinfo;
    }
    function removeClassConfig($classhash) {
        $systemTable=C('this:install:defaultTable');
        $modules=all('table','module','where',where('classhash',$classhash));
        foreach ($modules as $key => $module) {
            $module=C('cms:module:get',$module['hash'],$classhash);
            $fields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
            if(is_array($fields) && count($fields) && !isset($systemTable[$module['table']])) {
                C($GLOBALS['C']['DbClass'].':delTable',$module['table']);
            }
        }
        del(array('table'=>'hook','where'=>array('classhash'=>$classhash)));
        del(array('table'=>'auth','where'=>array('classhash'=>$classhash)));
        del(array('table'=>'route','where'=>array('classhash'=>$classhash)));
        del(array('table'=>'input','where'=>array('classhash'=>$classhash)));
        del(array('table'=>'channel','where'=>array('classhash'=>$classhash)));
        del(array('table'=>'form','where'=>array('classhash'=>$classhash)));
        del(array('table'=>'config','where'=>array('classhash'=>$classhash)));
        del(array('table'=>'module','where'=>array('classhash'=>$classhash)));
        if(is_file(classDir($classhash).$classhash.'.php')) {
            if($tables=C($classhash.':table')) {
                if(is_array($tables)) {
                    foreach($tables as $tablename=>$table) {
                        if(is_array($table) && !isset($systemTable[$tablename])) {
                            C($GLOBALS['C']['DbClass'].':delTable',$tablename);
                        }
                    }
                }
            }
        }
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
        if($order<1){$order=1;}
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
        if(!is_file(classDir($classhash).$classhash.'.php')) {Return false;}
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
            $new_class['enabled']='0';
            $new_class['installed']='0';
            $new_class['menu']='0';
            if($lastClass=one('table','class','order','classorder asc','where',where('classorder<=',999999))){
                $new_class['classorder']=$lastClass['classorder']-1;
                if($new_class['classorder']<0){$new_class['classorder']=1;}
            }else{
                $new_class['classorder']=999999;
            }
            if(isset($config['version']) && !empty($config['version'])) {$new_class['classversion']=$config['version'];}else {$new_class['classversion']='1.0';}
        }elseif(!$class['installed'] && isset($config['version']) && !empty($config['version'])) {
            $new_class['classversion']=$config['version'];
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
    function data($classhash) {
        $data['config']=all('table','config','where',where('classhash',$classhash));
        $data['module']=all('table','module','where',where('classhash',$classhash));
        $data['form']=all('table','form','where',where('classhash',$classhash));
        $data['route']=all('table','route','where',where('classhash',$classhash));
        $data['channel']=all('table','channel','where',where('classhash',$classhash));
        $data['hook']=all('table','hook','where',where('classhash',$classhash));
        $data['input']=all('table','input','where',where('classhash',$classhash));
        $data['auth']=all('table','auth','where',where('classhash',$classhash));
        $articleModules=array();
        foreach ($data['form'] as $classForm) {
            if($classForm['kind']=='column' && $classForm['enabled']){
                $articleModules[$classForm['modulehash']]=1;
            }
        }
        foreach ($articleModules as $key => $module) {
            $module=C('cms:module:get',$key,$classhash);
            $fields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
            if(is_array($fields) && count($fields)) {
                $data[$module['table']]=all('table',$module['table']);
            }
        }
        if(is_file(classDir($classhash).$classhash.'.php') && $classTables=C($classhash.':table')){
            if(count($classTables)){
                foreach ($classTables as $key => $classTable){
                    $data[$key]=all('table',$key);
                }
            }
        }
        return $data;
    }
    function unzip($src_file, $dest_dir=false, $create_zip_name_dir=true, $overwrite=true) 
    {
        if(class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            if ($zip->open($src_file) === TRUE)
            {
                if(@$zip->extractTo($dest_dir)) {
                    $zip->close();
                    Return true;
                }
                $zip->close();
            }
        }elseif(function_exists('zip_open')) {
            if(!cms_createdir($dest_dir)) {Return false;}
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