<?php
if(!defined('ClassCms')) {exit();}
class cms_install {
    function startup() {
        $GLOBALS['C']['install']=false;
        if(isset($GLOBALS['C']['DbInfo']) && is_array($GLOBALS['C']['DbInfo'])) {Return ;}
        if(isset($_POST['rewrite'])) {
            $config=array();
            if(isset($_POST['rewrite']) && $_POST['rewrite']=='1') {
                $config['UrlRewrite']=1;
                $GLOBALS['C']['UrlRewrite']=1;
            }else {
                $config['UrlRewrite']=0;
                $GLOBALS['C']['UrlRewrite']=0;
            }
            if($_POST['step']=='_database') {
                $createDatabase=C('this:install:createDatabase',1);
                if(!is_array($createDatabase)) {
                    Return C('admin:ajax',$createDatabase,1);
                }
                $writeDatabase=C('this:install:writeDatabase',1);
                if($writeDatabase!==true) {
                    Return C('admin:ajax',$writeDatabase,1);
                }
                C('this:class:install','cms');
                Return C('admin:ajax','ok');
            }else {
                $createDatabase=C('this:install:createDatabase');
                if(!is_array($createDatabase)) {
                    Return C('admin:ajax',$createDatabase,1);
                }
            }
            if($_POST['step']=='_config') {
                $config['SiteHash']=substr(md5(rand(10000000,99999999).time().rand(10000000,99999999)),0,16);
                if(!isset($GLOBALS['C']['TemplateClass']) || $GLOBALS['C']['TemplateClass']=='template') {
                    $config['TemplateClass']='template';
                }
                $config['DbInfo']=$createDatabase;
                $writeConfig=C('this:install:writeConfig',$config);
                if($writeConfig!==true) {
                    Return C('admin:ajax',$writeConfig,1);
                }
                C('this:common:opcacheReset');
                Return C('admin:ajax',rewriteUri($GLOBALS['C']['AdminDir'].'/'));
            }
            if($classdirslist=@scandir(classDir())) {
                if(in_array($_POST['step'],$classdirslist)) {
                    if(!C('this:class:phpCheck',$_POST['step'])) {
                        Return C('admin:ajax',$_POST['step'].':PHP版本必须:'.C('this:class:config',$_POST['step'],'php'),1);
                    }
                    $thisinstall=C('this:class:install',$_POST['step'],0);
                    if($thisinstall!==true) {
                        Return C('admin:ajax',$_POST['step'].':'.$thisinstall,1);
                    }
                    Return C('admin:ajax','ok');
                }
            }
            Return C('admin:ajax','error',1);
        }
        $array['allow']=true;
        if(!$cmsversion=C('this:class:config','cms','version')) {
            $cmsversion='';
        }
        $array['version']=$cmsversion;
        $array['infos'][]=array('name'=>'ClassCMS版本','value'=>$cmsversion);
        if(!isset($_SERVER['SERVER_SOFTWARE'])) {$_SERVER['SERVER_SOFTWARE']='未知';}
        $array['infos'][]=array('name'=>'Web服务器','value'=>htmlspecialchars($_SERVER['SERVER_SOFTWARE']));
        $array['infos'][]=array('name'=>'PHP版本','value'=>PHP_VERSION);
        if(!function_exists('get_loaded_extensions')) {
            $array['infos'][]=array('name'=>'get_loaded_extensions','value'=>'函数被禁用,无法检测安装环境','error'=>1);
            $array['allow']=false;
        }else {
            if(!C('this:install:extTest','json')) {
                $array['infos'][]=array('name'=>'json组件','value'=>'不支持,无法安装','error'=>1);
                $array['allow']=false;
            }
        }
        if(!function_exists('scandir')) {
            $array['infos'][]=array('name'=>'scandir','value'=>'函数被禁用,无法获取应用列表','error'=>1);
        }
        if(C('this:install:configfileTest')) {
            $array['infos'][]=array('name'=>'配置文件权限('.$GLOBALS['C']['Indexfile'].')','value'=>'正常');
        }else {
            $array['infos'][]=array('name'=>'配置文件权限('.$GLOBALS['C']['Indexfile'].')','value'=>'无权限,无法安装','error'=>1);
            $array['allow']=false;
        }
        if(C('this:install:dirTest',$GLOBALS['C']['ClassDir'])) {
            $array['infos'][]=array('name'=>'应用目录('.$GLOBALS['C']['ClassDir'].')','value'=>'正常');
        }else {
            $array['infos'][]=array('name'=>'应用目录('.$GLOBALS['C']['ClassDir'].')','value'=>'无权限,无法安装新应用','error'=>1);
        }
        if(C('this:install:makeDir',$GLOBALS['C']['CacheDir'])) {
            if(C('this:install:dirTest',$GLOBALS['C']['CacheDir'])) {
                $array['infos'][]=array('name'=>'缓存目录('.$GLOBALS['C']['CacheDir'].')','value'=>'正常');
            }else {
                $array['infos'][]=array('name'=>'缓存目录('.$GLOBALS['C']['CacheDir'].')','value'=>'无权限,无法安装','error'=>1);
                $array['allow']=false;
                echo($GLOBALS['C']['SystemRoot'].$GLOBALS['C']['CacheDir'].DIRECTORY_SEPARATOR.' permission denied!');
                Return ;
            }
        }else {
            echo('Unable to create directory '.$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['CacheDir'].DIRECTORY_SEPARATOR);
            Return ;
        }
        if(C('this:install:makeDir',$GLOBALS['C']['UploadDir'])) {
            if(C('this:install:dirTest',$GLOBALS['C']['UploadDir'])) {
                $array['infos'][]=array('name'=>'上传目录('.$GLOBALS['C']['UploadDir'].')','value'=>'正常');
            }else {
                $array['infos'][]=array('name'=>'上传目录('.$GLOBALS['C']['UploadDir'].')','value'=>'无权限,无法上传文件','error'=>1);
            }
        }else {
            $array['infos'][]=array('name'=>'上传目录('.$GLOBALS['C']['UploadDir'].')','value'=>'创建失败,无法上传文件','error'=>1);
        }
        if(stripos($_SERVER['SERVER_SOFTWARE'],'nginx')!==false) {
            $array['nginx']=1;
        }else {
            $array['nginx']=0;
        }
        if(C('this:install:extTest','pdo_sqlite')) {
            $array['sqlite']=1;
            $array['sqlitefilename']='db_'.substr(md5(rand(10000000,99999999).time()),0,16);
            $array['sqlitefile']='/'.$array['sqlitefilename'].'.db';
            $array['sqliteinfo']='';
        }else {
            $array['sqlite']=0;
            $array['sqlitefilename']='';
            $array['sqlitefile']='';
            $array['sqliteinfo']='服务器未开启pdo_sqlite组件,无法使用Sqlite数据库';
        }
        if(C('this:install:extTest','pdo_mysql')) {
            $array['pdo_mysql']=1;
        }else {
            $array['pdo_mysql']=0;
            if(C('this:install:extTest','mysql')) {
                $array['mysql']=1;
            }else {
                $array['mysql']=0;
            }
        }
        if(!$array['sqlite'] && !$array['pdo_mysql'] && !$array['mysql']) {
            $array['allow']=false;
        }
        $array['classlist']=array();
        if(function_exists('scandir')) {
            $array['classlist']['cms']='';
            $array['classlist']['admin']='';
            $array['classlist']['layui']='';
            if($classdirslist=@scandir(classDir())) {
                foreach($classdirslist as $dir) {
                    if(stripos($dir,'.')===false && !isset($array['classlist'][$dir])) {
                        if(is_file(classDir($dir).$dir.'.php')) {
                            $array['classlist'][$dir]='';
                        }
                    }
                }
                unset($array['classlist']['cms']);
                foreach($array['classlist'] as $key=>$title) {
                    if($array['classlist'][$key]=C('this:class:config',$key,'name')) {
                        $array['classlist'][$key]=$array['classlist'][$key].'['.$key.']';
                    }else {
                        $array['classlist'][$key]=$key;
                    }
                }
            }else {
                $array['allow']=false;
            }
        }
        V('install',$array);
    }
    function rewrite() {
        echo(json_encode(array('test'=>'ok')));
        Return ;
    }
    function goInstall() {
        echo('<meta http-equiv=refresh content="0; url='.$GLOBALS['C']['SystemDir'].'">');
        Return true;
    }
    function extTest($ext='') {
        if(empty($ext)) {Return false;}
        $extension=@get_loaded_extensions();
        if($extension==false) {
            $extension=array();
        }
        if(in_array($ext,$extension)) {
            Return true;
        }
    }
    function makeDir($dir='') {
        if(empty($dir)) {Return ;}
        $dir=$GLOBALS['C']['SystemRoot'].$dir.DIRECTORY_SEPARATOR;
        if(!is_dir($dir)) {
            if(!@mkdir($dir)) {
                Return false;
            }
        }
        if(!is_dir($dir)) {
            Return false;
        }
        Return true;
    }
    function sqliteTest($file='') {
        if(empty($file)) {Return ;}
        $file=$GLOBALS['C']['SystemRoot'].$file;
        if(!$fp = @fopen($file,"w")) {
            Return false;
        }
        if(!@fwrite($fp,"write test")){
            @fclose($fp);
            Return false;
        }
        @fclose($fp);
        @unlink($file);
        Return true;
    }
    function dirTest($dir='') {
        if(empty($dir)) {Return ;}
        $dir=$GLOBALS['C']['SystemRoot'].$dir.DIRECTORY_SEPARATOR.rand(1000000,9999999).'_';
        $file=$dir.'write.test';
        if(!$fp = @fopen($file,"w")) {
            Return false;
        }
        if(!@fwrite($fp,"write test")){
            @fclose($fp);
            Return false;
        }
        @fclose($fp);
        if(!@unlink($file)) {
            Return false;
        }
        $dir=$dir.'dirtest';
        if(!@mkdir($dir)) {
            Return false;
        }
        if(!is_dir($dir)) {
            Return false;
        }
        if(!@rmdir($dir)) {
            Return false;
        }
        Return true;
    }
    function createDatabase($create=0) {
        if(!isset($_POST['database'])) {
            Return false;
        }
        if($_POST['database']=="0") {
            if(!isset($_POST['sqlitefile'])) {
                Return false;
            }
            if(!is_hash($_POST['sqlitefile'])) {
                Return '数据库文件名有误';
            }
            $sqlitefile=$_POST['sqlitefile'].'.db';
            if($create) {
                if(!C('this:install:sqliteTest',$sqlitefile)) {
                    Return '权限不足,Sqlite数据库文件写入失败 '.$sqlitefile;
                }
            }
            $GLOBALS['C']['DbInfo']=array('kind'=>'sqlitepdo','file'=>$sqlitefile,'prefix'=>$_POST['prefix'],'showerror'=>0);
        }
        if($_POST['database']=="1") {
            $GLOBALS['C']['DbInfo']=array('showerror'=>0);
            if(C('this:install:extTest','pdo_mysql')) {
                $GLOBALS['C']['DbInfo']['kind']='mysqlpdo';
            }elseif(C('this:install:extTest','mysql')) {
                $GLOBALS['C']['DbInfo']['kind']='mysql';
            }else {
                Return 'pdo_mysql或mysql组件未安装';
            }
            $GLOBALS['C']['DbInfo']['host']=$_POST['mysql_host'];
            $GLOBALS['C']['DbInfo']['user']=$_POST['mysql_user'];
            $GLOBALS['C']['DbInfo']['password']=$_POST['mysql_password'];
            $GLOBALS['C']['DbInfo']['prefix']=$_POST['prefix'];
            $GLOBALS['C']['DbInfo']['engine']='MyISAM';
            $GLOBALS['C']['DbInfo']['charset']='utf8';
            $engines_query=query('show engines');
            if($engines_query) {
                $engines=fetchall($engines_query);
                if(is_array($engines)) {
                    foreach($engines as $engine) {
                        if(isset($engine['Engine']) && strtolower($engine['Engine'])=='innodb') {
                            $GLOBALS['C']['DbInfo']['engine']='InnoDB';
                        }
                    }
                }
            }
            if($create) {
                query('CREATE DATABASE IF NOT EXISTS `'.escape($_POST['mysql_dbname']).'` DEFAULT CHARSET '.$GLOBALS['C']['DbInfo']['charset']);
            }
            $GLOBALS['C']['DbInfo']['dbname']=$_POST['mysql_dbname'];
            $databases_query=query('show databases');
            if(!$databases_query) {
                Return '数据库无法连接';
            }
            $databases=fetchall($databases_query);
            if(!$databases) {
                Return '数据库无法连接';
            }
            if(is_array($databases)) {
                $created=false;
                foreach($databases as $database) {
                    if(isset($database['Database']) && strtolower($database['Database'])==strtolower($_POST['mysql_dbname'])) {
                        $created=true;
                    }
                }
                if(!$created) {
                    Return '权限不足,无法创建数据库,请手动建立数据库'.htmlspecialchars($_POST['mysql_dbname']);
                }
            }
            if(!$use_query=query('use '.escape($_POST['mysql_dbname']))) {
                Return '权限不足,无法使用数据库 '.htmlspecialchars($_POST['mysql_dbname']);
            }
        }
        $dbinfo=$GLOBALS['C']['DbInfo'];
        unset($dbinfo['sql']);
        unset($dbinfo['querycount']);
        unset($dbinfo['showerror']);
        Return $dbinfo;
    }
    function configfileTest() {
        $file=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['Indexfile'];
        $configfileContent=@file_get_contents($file);
        if(!$configfileContent) {
            Return false;
        }
        if(!$fp = @fopen($file,"w")) {
            Return false;
        }
        if(!@fwrite($fp,$configfileContent)){
            Return false;
        }
        @fclose($fp);
        Return true;
    }
    function writeConfig($config=array()) {
        $configstr='';
        $linestr="\n";
        if(DIRECTORY_SEPARATOR=='\\') {
            $linestr="\r\n";
        }
        foreach($config as $key=>$val) {
            if(is_array($val)) {
                $configstr.="\$GLOBALS['C']['".$key."']=array(";
                foreach($val as $key2=>$val2) {
                    if(!is_array($val2)) {
                        $configstr.="'".$key2."'=>'".$val2."',";
                    }
                }
                $configstr.=");".$linestr;
            }else {
                if(is_int($val)) {
                    $configstr.="\$GLOBALS['C']['".$key."']=".$val.";".$linestr;
                }else {
                    $configstr.="\$GLOBALS['C']['".$key."']='".$val."';".$linestr;
                }
            }
        }
        $configstr.=$linestr.$linestr."require('class/cms/cms.php');";
        $file=$GLOBALS['C']['SystemRoot'].$GLOBALS['C']['Indexfile'];
        $indexfileContent=@file_get_contents($file);
        if(!$indexfileContent) {
            Return '获取配置文件['.$GLOBALS['C']['Indexfile'].']内容失败';
        }
        $bomCheck = array();
        $bomCheck[1] = substr($indexfileContent, 0, 1);
        $bomCheck[2] = substr($indexfileContent, 1, 1);
        $bomCheck[3] = substr($indexfileContent, 2, 1);
        if (ord($bomCheck[1]) == 239 && ord($bomCheck[2]) == 187 && ord($bomCheck[3]) == 191) {
            Return '配置文件['.$GLOBALS['C']['Indexfile'].']编码为UTF-8 BOM,请改成UTF-8格式';
        }
        $indexfileContent=str_replace("require('class/cms/cms.php');",$configstr,$indexfileContent);
        if(!$fp = @fopen($file,"w")) {
            Return '无法打开配置文件['.$GLOBALS['C']['Indexfile'].']';
        }
        if(!@fwrite($fp,$indexfileContent)){
            Return '写入配置信息失败,请确认是否拥有写入权限 配置文件:['.$GLOBALS['C']['Indexfile'].']';
        }
        @fclose($fp);
        Return true;
    }
    function writeDatabase($DbInfo='') {
        if(!$DbInfo) {Return false;}
        $tables=C('this:install:defaultTable');
        if(!is_array($tables)) {
            Return '获取数据库字段失败';
        }
        begin();
        foreach($tables as $key=>$table) {
            $getfieleds=C($GLOBALS['C']['DbClass'].':getFields',$key);
            if(count($getfieleds)) {
                Return '数据库表已存在,请重命名数据库表名前缀';
            }
            C($GLOBALS['C']['DbClass'].':createTable',$key,$table);
            $getfieleds=C($GLOBALS['C']['DbClass'].':getFields',$key);
            if(count($getfieleds)<count($table)) {
                Return '数据库表字段建立失败,请确认是否拥有权限';
            }
        }
        commit();
        Return true;
    }
    function defaultTable() {
        $tables=array();
        $tables['auth']=array(
            'hash'=>'varchar(255)',
            'rolehash'=>'varchar(255)',
            'enabled'=>'int(1)',
            'classhash'=>'varchar(32)',
            'authkind'=>'varchar(255)',
            'classenabled'=>'int(1)'
        );
        $tables['channel']=array(
            'channelname'=>'varchar(255)',
            'fid'=>'int(11)',
            'enabled'=>'int(1)',
            'channelorder'=>'int(11)',
            'classhash'=>'varchar(32)',
            'modulehash'=>'varchar(32)'
        );
        $tables['class']=array(
            'classname'=>'varchar(255)',
            'hash'=>'varchar(32)',
            'enabled'=>'int(1)',
            'classorder'=>'int(11)',
            'installed'=>'int(1)',
            'menu'=>'int(1)',
            'auth'=>'int(1)',
            'module'=>'int(1)',
            'classversion'=>'varchar(32)',
            'adminpage'=>'varchar(32)',
            'author'=>'varchar(255)',
            'url'=>'varchar(255)',
            'ico'=>'varchar(32)',
            'requires'=>'varchar(255)',
            
        );
        $tables['config']=array(
            'hash'=>'varchar(255)',
            'classhash'=>'varchar(32)',
            'overtime'=>'bigint(10)',
            'value'=>'longtext'
        );
        $tables['form']=array(
            'formname'=>'varchar(32)',
            'hash'=>'varchar(32)',
            'enabled'=>'int(1)',
            'kind'=>'varchar(32)',
            'formorder'=>'int(11)',
            'formwidth'=>'int(3)',
            'modulehash'=>'varchar(32)',
            'classhash'=>'varchar(32)',
            'inputhash'=>'varchar(32)',
            'tabname'=>'varchar(32)',
            'taborder'=>'int(11)',
            'tips'=>'varchar(255)',
            'defaultvalue'=>'longtext',
            'nonull'=>'int(1)',
            'indexshow'=>'int(1)'
        );
        $tables['hook']=array(
            'hookname'=>'varchar(255)',
            'enabled'=>'int(1)',
            'hookorder'=>'int(11)',
            'hookedfunction'=>'varchar(255)',
            'classhash'=>'varchar(32)',
            'classenabled'=>'int(1)',
            'classorder'=>'int(11)'
        );
        $tables['input']=array(
            'inputname'=>'varchar(32)',
            'hash'=>'varchar(32)',
            'enabled'=>'int(1)',
            'classenabled'=>'int(1)',
            'classhash'=>'varchar(32)',
            'classfunction'=>'varchar(255)',
            'inputorder'=>'int(11)',
            'groupname'=>'varchar(255)'
        );
        $tables['module']=array(
            'modulename'=>'varchar(32)',
            'hash'=>'varchar(32)',
            'enabled'=>'int(1)',
            'moduleorder'=>'int(11)',
            'classenabled'=>'int(1)',
            'classhash'=>'varchar(32)'
        );
        $tables['role']=array(
            'rolename'=>'varchar(255)',
            'hash'=>'varchar(32)',
            'enabled'=>'int(1)',
            'roleorder'=>'int(11)'
        );
        $tables['route']=array(
            'hash'=>'varchar(32)',
            'enabled'=>'int(1)',
            'routeorder'=>'int(11)',
            'classorder'=>'int(11)',
            'classhash'=>'varchar(32)',
            'classenabled'=>'int(1)',
            'moduleorder'=>'int(11)',
            'modulehash'=>'varchar(32)',
            'moduleenabled'=>'int(1)',
            'uri'=>'varchar(255)',
            'classfunction'=>'varchar(255)',
            'classview'=>'varchar(255)'
        );
        $tables['user']=array(
            'username'=>'varchar(32)',
            'hash'=>'varchar(32)',
            'passwd'=>'varchar(32)',
            'enabled'=>'int(1)',
            'rolehash'=>'text'
        );
        $tables['token']=array(
            'userid'=>'int(11)',
            'hash'=>'varchar(32)',
            'overtime'=>'bigint(11)'
        );
        Return $tables;
    }
}