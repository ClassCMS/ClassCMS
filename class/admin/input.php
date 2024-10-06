<?php
if(!defined('ClassCms')) {exit();}
class admin_input {
    function text($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '文本框';
            case 'hash':
                Return 'text';
            case 'group':
                Return '';
            case 'sql':
                Return 'varchar(255)';
            case 'form':
                $config['value']=str_replace(array('"','<','>'),array('&quot;','&lt;','&gt;'),$config['value']);
                echo('<input type="text" name="'.$config['name'].'"  lay-filter="'.$config['name'].'" value="'.($config['value']).'" placeholder="'.$config['placeholder'].'"');
                if($config['disabled']) {
                    echo(' disabled');
                }
                if(($config['width'])) {
                    echo(' style="width:'.$config['width'].'"');
                }
                echo(' class="layui-input">');
                Return '';
            case 'view':
                if(isset($config['titlelink']) && isset($config['article']['link']) && !empty($config['article']['link']) && $config['article']['link']!='#') {
                    Return '<a class="cmscolor" target="_blank" href="'.$config['article']['link'].'">'.htmlspecialchars($config['value']).'</a>';
                }else {
                    Return $config['value'];
                }
            case 'post':
                if(isset($config['regular']) && !empty($config['regular']) && isset($_POST[$config['name']]) && !empty($_POST[$config['name']])) {
                    if(is_hash($config['regular'])) {
                        if(!C('cms:common:verify',@$_POST[$config['name']],$config['regular'])) {
                            if(!empty($config['regulartips'])) {Return array('error'=>htmlspecialchars($config['regulartips']));}
                            Return false;
                        }
                    }elseif(!preg_match($config['regular'],@$_POST[$config['name']])) {
                        if(!empty($config['regulartips'])) {Return array('error'=>htmlspecialchars($config['regulartips']));}
                        Return false;
                    }
                }
                if(isset($config['nonull']) && $config['nonull']) {
                    if(!isset($_POST[$config['name']]) || strlen($_POST[$config['name']])==0) {
                        Return array('error'=>'不能为空');
                    }
                }
                if(isset($config['max']) && $config['max'] && isset($_POST[$config['name']])) {
                    if(C('cms:common:text',$_POST[$config['name']],$config['max'])<>C('cms:common:text',$_POST[$config['name']])) {
                        Return array('error'=>'不能超过'.$config['max'].'个字符');
                    }
                }
                if(!isset($config['auth']['html']) || !$config['auth']['html']) {
                    Return htmlspecialchars(@$_POST[$config['name']]);
                }
                Return @$_POST[$config['name']];
            case 'auth':
                Return array('html'=>'允许HTML代码');
            case 'config':
                Return array(
                            array('configname'=>'数据效验','hash'=>'regular','inputhash'=>'text','tips'=>'常见类型:id,email,phone,hash,username,ip.也可输入正则表达式:纯字母:/^[a-z]+$/i 字母+数字:/^[0-9a-z]+$/i'),
                            array('configname'=>'效验提示','hash'=>'regulartips','inputhash'=>'text','tips'=>'如提交数据不能通过数据校验,则提示此信息'),
                            array('configname'=>'字数限制','hash'=>'max','inputhash'=>'number','tips'=>'允许的最大字符数,0则不限制','defaultvalue'=>'0'),
                            array('configname'=>'输入框提示','hash'=>'placeholder','inputhash'=>'text','tips'=>'输入框的placeholder'),
                            array('configname'=>'输入框宽度','hash'=>'width','inputhash'=>'text','tips'=>'使用百分比,如:40% 或者 固定宽度如:200px')
                        );
        }
        Return false;
    }
    function textarea($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '文本区域';
            case 'hash':
                Return 'textarea';
            case 'group':
                Return '';
            case 'sql':
                Return 'longtext';
            case 'form':
                echo('<textarea class="layui-textarea" name="'.$config['name'].'" lay-filter="'.$config['name'].'" placeholder="'.$config['placeholder'].'" ');
                if($config['disabled']) {
                    echo(' disabled');
                }
                if($config['style']) {
                    echo(' style="'.$config['style'].'"');
                }
                echo('>');
                echo(htmlspecialchars($config['value']));
                echo('</textarea>');
                Return '';
            case 'post':
                if(isset($config['nonull']) && $config['nonull']) {
                    if(!isset($_POST[$config['name']]) || strlen($_POST[$config['name']])==0) {
                        Return array('error'=>'不能为空');
                    }
                }
                if(!isset($_POST[$config['name']])){ return ''; }
                if(!isset($config['auth']['html']) || !$config['auth']['html']) {
                    Return htmlspecialchars($_POST[$config['name']]);
                }
                Return $_POST[$config['name']];
            case 'auth':
                Return array('html'=>'允许HTML代码');
            case 'config':
                Return array(
                            array('configname'=>'输入框提示','hash'=>'placeholder','inputhash'=>'text','tips'=>'输入框的placeholder'),
                            array('configname'=>'输入框样式','hash'=>'style','inputhash'=>'text','tips'=>'如:width:50%;min-height:200px')
                        );
        }
        Return false;
    }
    function dateTime($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '日期时间';
            case 'hash':
                Return 'datetime';
            case 'group':
                Return '';
            case 'sql':
                Return 'bigint(10)';
            case 'form':
                if(isset($config['source']) && $config['source']=='admin_defaultvalue_setting') {
                    $config['nowtime']=0;
                }
                if(isset($config['source']) && $config['source']=='admin_article_edit') {
                    $config['nowtime']=0;
                }
                if(empty($config['value']) && $config['nowtime']) {
                    $config['value']=time();
                }
                if(!empty($config['value'])) {
                    if (defined('PHP_INT_MAX') && $config['value']>PHP_INT_MAX) {
                        echo('系统不支持此时间戳:'.$config['value']);
                        Return '';
                    }
                    if($config['time'] && is_numeric($config['value'])) {
                        $config['value']=date('Y-m-d H:i:s',$config['value']);
                    }elseif(is_numeric($config['value'])) {
                        $config['value']=date('Y-m-d',$config['value']);
                    }
                }elseif($config['value']=='0') {
                    $config['value']='';
                }
                V('input/datetime',$config);
                Return '';
            case 'defaultvalue':
                if(empty($config['defaultvalue']) && $config['nowtime']) {
                    $config['defaultvalue']=time();
                }
                Return $config['defaultvalue'];
            case 'view':
                if(empty($config['value'])) {
                    Return '';
                }
                if (defined('PHP_INT_MAX') && $config['value']>PHP_INT_MAX) {
                    Return '';
                }
                if($config['time']) {
                    Return date('Y-m-d H:i:s',$config['value']);
                }else {
                    Return date('Y-m-d',$config['value']);
                }
            case 'post':
                if(!isset($_POST[$config['name']])) {
                    Return false;
                }
                if(empty($_POST[$config['name']])) {
                    Return 0;
                }else {
                    if($config['time']) {
                        if(date('Y-m-d H:i:s',strtotime($_POST[$config['name']]))!==trim($_POST[$config['name']])) {
                            Return array('error'=>'日期时间格式不正确');
                        }
                    }else {
                        if(date('Y-m-d',strtotime($_POST[$config['name']]))!==trim($_POST[$config['name']])) {
                            Return array('error'=>'日期格式不正确');
                        }
                    }
                }
                Return strtotime($_POST[$config['name']]);
            case 'config':
                Return array(
                            array('configname'=>'时间','hash'=>'time','inputhash'=>'switch','tips'=>'默认只能选择日期,开启后,允许选择时间'),
                            array('configname'=>'默认时间','hash'=>'nowtime','inputhash'=>'switch','tips'=>'当默认值为空时,则默认为当前时间'),
                        );
        }
        Return false;
    }
    function imgupload($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '图片上传';
            case 'hash':
                Return 'imgupload';
            case 'group':
                Return '';
            case 'sql':
                if($config['multiple']) {
                    Return 'text';
                }
                Return 'varchar(255)';
            case 'form':
                if($config['multiple']) {
                    $config['pics']=explode(';',$config['value']);
                    $config['height']='150';
                    if(empty($config['pics'][0])) {
                        $config['pics']=array();
                    }
                }else {
                    $config['pics']=explode(';',$config['value']);
                    if(empty($config['pics'][0])) {
                        $config['value']='';
                        $config['pics']=array();
                    }else {
                        $config['value']=$config['pics'][0];
                        $config['pics']=array($config['pics'][0]);
                    }
                    $config['height']='200';
                }
                V('input/imgupload',$config);
                Return '';
            case 'ajax':
                if($config['disabled']) {Return array('error'=>1,'message'=>'无权限');}
                if($file_upload=C('cms:common:upload',$config['name'].'_layupload',$config['filepath'],$config['filename'])) {
                    if(!$file_upload['error']) {
                        Return array('url'=>$file_upload['url'][0],'error'=>0);
                    }else {
                        if(isset($file_upload['message'])) {
                            Return array('message'=>$file_upload['message'],'error'=>1);
                        }else {
                            Return array('message'=>'error','error'=>1);
                        }
                    }
                }
                Return array('error'=>1,'message'=>'error');
            case 'view':
                if($config['multiple']) {
                    $config['pics']=explode(';',$config['value']);
                    if(empty($config['pics'][0])) {
                        $config['pics']=array();
                    }
                }else {
                    $config['pics']=explode(';',$config['value']);
                    if(empty($config['pics'][0])) {
                        $config['pics']=array();
                    }else {
                        $config['pics']=array($config['pics'][0]);
                    }
                }
                foreach($config['pics'] as $pic) {
                    echo('<img style="padding:3px" src="'.$pic.'">');
                }
                Return '';
            case 'post':
                Return trim(str_replace(array('"','<','>'),array('&quot;','&lt;','&gt;'),@$_POST[$config['name']]),';');
            case 'config':
                Return array(
                            array('configname'=>'多图','hash'=>'multiple','inputhash'=>'switch','tips'=>'允许同时上传多张图片.开启后再关闭此选项,已上传的图片数据将丢失!'),
                            array('configname'=>'目录','hash'=>'filepath','inputhash'=>'text','tips'=>'上传文件保存目录,如: /upload/(Y)(m)(d)/ 请确保此目录有写入权限,不填则为默认目录'),
                            array('configname'=>'文件名','hash'=>'filename','inputhash'=>'text','tips'=>'保存的文件名,随机文件名:(rand).(ext) 固定文件名:logo.png 原始文件名:(filename).(ext),中文文件名可能无法访问'),
                        );
        }
        Return false;
    }
    function fileupload($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '文件上传';
            case 'hash':
                Return 'fileupload';
            case 'group':
                Return '';
            case 'sql':
                if($config['multiple']) {Return 'text';}
                Return 'varchar(255)';
            case 'form':
                $config['files']=array();
                if($config['multiple']) {
                    $files=explode(';',$config['value']);
                    foreach($files as $file) {
                        if(!empty($file)) {
                            $thisfile=explode('/',$file);
                            $config['files'][]=array($file,$thisfile[count($thisfile)-1]);
                        }
                    }
                }else {
                    $files=explode(';',$config['value']);
                    foreach($files as $file) {
                        if(!empty($file)) {
                            $thisfile=explode('/',$file);
                            $config['files'][]=array($file,$thisfile[count($thisfile)-1]);
                            $config['value']=$file;
                            break;
                        }
                    }
                }
                V('input/fileupload',$config);
                Return '';
            case 'ajax':
                if($config['disabled']) {Return array('error'=>1,'message'=>'无权限');}
                if($file_upload=C('cms:common:upload',$config['name'].'_layupload',$config['filepath'],$config['filename'])) {
                    if(!$file_upload['error']) {
                        $thisfile=explode('/',$file_upload['url'][0]);
                        Return array('url'=>$file_upload['url'][0],'filename'=>$thisfile[count($thisfile)-1],'error'=>0);
                    }else {
                        if(isset($file_upload['message'])) {
                            Return array('message'=>$file_upload['message'],'error'=>1);
                        }else {
                            Return array('message'=>'error','error'=>1);
                        }
                    }
                }
                Return array('error'=>1,'message'=>'error');
            case 'view':
                $config['files']=array();
                if($config['multiple']) {
                    $files=explode(';',$config['value']);
                    foreach($files as $file) {
                        if(!empty($file)) {
                            $thisfile=explode('/',$file);
                            $config['files'][]=array($file,$thisfile[count($thisfile)-1]);
                        }
                    }
                }else {
                    $files=explode(';',$config['value']);
                    foreach($files as $file) {
                        if(!empty($file)) {
                            $thisfile=explode('/',$file);
                            $config['files'][]=array($file,$thisfile[count($thisfile)-1]);
                            $config['value']=$file;
                            break;
                        }
                    }
                }
                foreach($config['files'] as $file) {
                    echo('<a href="'.$file[0].'" target="_blank">'.$file[1].'</a> ');
                }
                Return '';
            case 'post':
                Return trim(str_replace(array('"','<','>'),array('&quot;','&lt;','&gt;'),@$_POST[$config['name']]),';');
            case 'config':
                Return array(
                            array('configname'=>'多文件','hash'=>'multiple','inputhash'=>'switch','tips'=>'允许同时上传多个文件.开启后再关闭此选项,已上传的文件数据将丢失!'),
                            array('configname'=>'目录','hash'=>'filepath','inputhash'=>'text','tips'=>'上传文件保存目录,如: /upload/(Y)(m)(d)/ 请确保此目录有写入权限,不填则为默认目录'),
                            array('configname'=>'文件名','hash'=>'filename','inputhash'=>'text','tips'=>'保存的文件名,随机文件名:(rand).(ext) 固定文件名:logo.png 原始文件名:(filename).(ext),中文文件名可能无法访问'),
                        );
        }
        Return false;
    }
    function onOff($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '开关';
            case 'hash':
                Return 'switch';
            case 'group':
                Return '';
            case 'sql':
                Return 'int(1)';
            case 'form':
                if($config['value']) {$config['value']=' checked';}else {$config['value']='';}
                if($config['disabled']) {$config['disabled']=' disabled';}else {$config['disabled']='';}
                echo('<input type="checkbox" name="'. $config['name'].'" '. $config['value'].' '. $config['disabled'].' lay-skin="switch"  lay-text="'. $config['opentips'].'|'. $config['closetips'].'">');
                Return '';
            case 'view':
                if(isset($config['source']) && $config['source']=='adminlist'){
                    $config['disabled']=false;
                }else{
                    $config['disabled']=true;
                }
                V('input/switch',$config);
                Return ;
            case 'ajax':
                if($config['disabled']) {Return array('msg'=>'无权限');}
                if(!$article=C('this:article:editEnabled',intval($_POST['cid']),intval($_POST['articleid']))) {
                    Return array('msg'=>'修改失败');
                }
                $new_article=array();
                $new_article['cid']=$article['cid'];
                $new_article['id']=$article['id'];
                if($_POST['state']=="false") {
                    $new_article[$config['hash']]=0;
                }else {
                    $new_article[$config['hash']]=1;
                }
                if(!C('cms:article:edit',$new_article)) {
                    Return array('msg'=>'修改失败');
                }
                Return array('msg'=>'');
            case 'post':
                if(!isset($_POST[$config['name']])) {
                    Return 0;
                }else {
                    Return 1;
                }
            case 'config':
                Return array(
                            array('configname'=>'开启文字','hash'=>'opentips','inputhash'=>'text','tips'=>'开启时显示的文字'),
                            array('configname'=>'关闭文字','hash'=>'closetips','inputhash'=>'text','tips'=>'关闭时显示的文字'),
                        );
        }
        Return false;
    }
    function number($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '数字';
            case 'hash':
                Return 'number';
            case 'group':
                Return '';
            case 'sql':
                if($config['savetype']==2) {
                    Return 'decimal(20,6)';
                }
                Return 'bigint(11)';
            case 'view':
                if($config['savetype']==2 && stripos($config['value'],'.')) {
                    return rtrim(rtrim($config['value'],'0'),'.');
                }
                return $config['value'];
            case 'form':
                echo('<input type="number" name="'.$config['name'].'"  lay-filter="'.$config['name'].'"');
                if(!empty($config['min'])) {
                    echo(' min="'.$config['min'].'"');
                }
                if(!empty($config['max'])) {
                    echo(' max="'.$config['max'].'"');
                }
                if($config['savetype']==2 && stripos($config['value'],'.')) {
                    $config['value']=rtrim($config['value'],'0');
                    $config['value']=rtrim($config['value'],'.');
                }
                echo(' value="'.$config['value'].'" ');
                echo(' placeholder="'.$config['placeholder'].'" ');
                if($config['disabled']) {
                    echo(' disabled');
                }
                if(!empty($config['width'])) {
                    echo(' style="width:'.$config['width'].'"');
                }
                echo(' class="layui-input">');
                Return '';
            case 'post':
                if(!isset($_POST[$config['name']])) {Return false;}
                if(isset($config['kind']) && $config['kind']=='column' && !strlen($_POST[$config['name']])){
                    Return array('error'=>'格式不正确');
                }
                if(isset($config['kind']) && $config['kind']=='info' && !strlen($_POST[$config['name']])){
                    Return array('error'=>'格式不正确');
                }
                if(strlen($config['min']) && $_POST[$config['name']]<$config['min']) {
                    Return array('error'=>'最小为'.$config['min']);
                }
                if(strlen($config['max']) && $_POST[$config['name']]>$config['max']) {
                    Return array('error'=>'最大为'.$config['max']);
                }
                if(strlen(intval($_POST[$config['name']]))>11){
                    Return array('error'=>'格式不正确');
                }
                if($config['savetype']==1) {
                    if(strlen($_POST[$config['name']])==0) {Return '';}
                    Return floor($_POST[$config['name']]);
                }
                Return $_POST[$config['name']];
            case 'config':
                Return array(
                            array('configname'=>'类型','hash'=>'savetype','inputhash'=>'radio','tips'=>'数字类型保存格式为int(9),金额为decimal(20,6).切换类型会使金额小数位丢失,请提前确认类型','defaultvalue'=>'1','values'=>"1:数字\n2:金额",'savetype'=>1),
                            array('configname'=>'最小值','hash'=>'min','inputhash'=>'number','tips'=>'数字的最小值'),
                            array('configname'=>'最大值','hash'=>'max','inputhash'=>'number','tips'=>'数字的最大值'),
                            array('configname'=>'输入框提示','hash'=>'placeholder','inputhash'=>'text','tips'=>'输入框的placeholder'),
                            array('configname'=>'输入框宽度','hash'=>'width','inputhash'=>'text','tips'=>'使用百分比,如:40% 或者 固定宽度如:200px')
                        );
        }
        Return false;
    }
    function colorPicker($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '颜色';
            case 'hash':
                Return 'colorpicker';
            case 'group':
                Return '';
            case 'sql':
                Return 'varchar(30)';
            case 'form':
                V('input/colorpicker',$config);
                Return '';
            case 'post':
                if(strlen(@$_POST[$config['name']])>30) {
                    Return '';
                }
                Return htmlspecialchars(@$_POST[$config['name']]);
            case 'view':
                if(isset($config['source']) && $config['source']=='adminlist') {
                    Return '<button type="button" class="layui-btn layui-btn-xs" style="background-color:'.$config['value'].'">&nbsp;&nbsp;&nbsp;&nbsp;</button>';
                }
                Return $config['value'];
            case 'config':
                Return array(
                            array('configname'=>'rgb格式','hash'=>'rgb','inputhash'=>'switch','tips'=>'rgb格式为:rgb(255, 0, 0),默认为hex格式,如:#ffffff'),
                            array('configname'=>'透明度','hash'=>'alpha','inputhash'=>'switch','tips'=>'只有在rgb格式下,才可使用透明度.格式为:rgba(255, 0, 0, 1)'),
                        );
        }
        Return false;
    }
    function tags($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '多项文本框';
            case 'hash':
                Return 'tags';
            case 'group':
                Return '';
            case 'sql':
                Return 'longtext';
            case 'form':
                if($config['column']>1) {$config['showstyle']=1;}
                if($config['column']<1) {$config['column']=1;}
                if(!is_numeric($config['max'])) {$config['max']=0;}
                if(!$config['max']) {$config['max']='999999';}
                if($config['showstyle']) {
                    if($config['column']==1) {
                        $defaultwidth='80%';
                    }elseif($config['column']==2) {
                        $defaultwidth='45%';
                    }elseif($config['column']==3) {
                        $defaultwidth='30%';
                    }else {
                        $defaultwidth='20%';
                    }
                }else {
                    $defaultwidth='200px';
                }
                $config['width']=explode(';',$config['width']);
                for($i=0; $i<$config['column']; $i++) {
                    if(!isset($config['width'][$i]) || empty($config['width'][$i])) {
                        $config['width'][$i]=$defaultwidth;
                    }
                    $config['width'][$i]='style="width:'.$config['width'][$i].'"';
                }
                $config['columntips']=explode(';',$config['columntips']);
                for($i=0; $i<$config['column']; $i++) {
                    if(!isset($config['columntips'][$i])) {
                        $config['columntips'][$i]='';
                    }
                }
                $config['values']=array();
                if(!empty($config['value'])) {
                    $values=explode(';',$config['value']);
                    foreach($values as $thisval) {
                        if($config['column']>1) {
                            $thisvals=explode('|',$thisval);
                            $thisvals_new=array();
                            foreach($thisvals as $key=>$val) {
                                if($key<$config['column']) {
                                    $thisvals_new[]=htmlspecialchars($val);
                                }
                            }
                            $config['values'][]=$thisvals_new;
                        }else {
                            $config['values'][][]=htmlspecialchars($thisval);
                        }
                    }
                }
                if(!isset($config['sortable'])) {$config['sortable']=1;}
                if($config['disabled']) {$config['sortable']=0;}
                V('input/tags',$config);
                Return '';
            case 'post':
                if(!$config['max']) {$config['max']='999999';}
                if($config['column']<1) {$config['column']=1;}
                if(!isset($config['norepet'])) {$config['norepet']=0;}
                if(isset($config['nonull']) && $config['nonull'] && !($config['min'])) {$config['min']=1;}
                if(isset($config['regulars']) && $config['regulars']){$regulars=explode(';',$config['regulars']); }else{ $regulars=array(); }
                if($config['column']==1) {
                    if(isset($_POST[$config['name']]) && is_array($_POST[$config['name']])) {
                        foreach($_POST[$config['name']] as $key=>$val) {
                            if(isset($regulars[0]) && $regulars[0] && $val){
                                if(is_hash($regulars[0])) {
                                    if(!C('cms:common:verify',$val,$regulars[0])) {
                                        Return false;
                                    }
                                }elseif(!preg_match($regulars[0],$val)) {
                                    Return false;
                                }
                            }
                            $_POST[$config['name']][$key]=str_replace(';',' ',$val);
                            if(empty($_POST[$config['name']][$key])) {
                                unset($_POST[$config['name']][$key]);
                            }
                        }
                        if($config['norepet']) {
                            $norepet=array_unique($_POST[$config['name']]);
                            if(count($_POST[$config['name']])!=count($norepet)) {
                                Return array('error'=>'不允许重复项');
                            }
                        }
                        if(count($_POST[$config['name']])<$config['min']) {Return array('error'=>'至少填写'.$config['min'].'项');}
                        if(count($_POST[$config['name']])>$config['max']) {Return array('error'=>'最多填写'.$config['max'].'项');}
                        Return implode(';',$_POST[$config['name']]);
                    }
                    if($config['min']) {Return false;}
                }else {
                    $rows=array();
                    $columns=array();
                    if(isset($_POST[$config['name']]) && is_array($_POST[$config['name']])) {
                        foreach($_POST[$config['name']] as $key=>$val) {
                            $val=str_replace(array(';','|'),' ',$val);
                            $regularkey=$key%$config['column'];
                            if(isset($regulars[$regularkey]) && $regulars[$regularkey]){
                                if(is_hash($regulars[$regularkey])) {
                                    if(!C('cms:common:verify',$val,$regulars[$regularkey])) {
                                        Return false;
                                    }
                                }elseif(!preg_match($regulars[$regularkey],$val)) {
                                    Return false;
                                }
                            }
                            $columns[]=$val;
                            if(($key+1)%$config['column']==0) {
                                if(implode('|',$columns)!=str_repeat('|',$config['column']-1)) {
                                    $rows[]=implode('|',$columns);
                                }
                                $columns=array();
                            }
                        }
                    }
                    if($config['norepet']) {
                        $norepet=array_unique($rows);
                        if(count($rows)!=count($norepet)) {
                            Return array('error'=>'不允许重复项');
                        }
                    }
                    if(count($rows)<$config['min']) {Return array('error'=>'至少填写'.$config['min'].'项');}
                    if(count($rows)>$config['max']) {Return array('error'=>'最多填写'.$config['max'].'项');}
                    Return implode(';',$rows);
                }
                Return '';
            case 'view':
                if($config['column']>1) {$config['showstyle']=1;}
                if($config['showstyle']==0 && isset($config['source']) && $config['source']=='adminlist') {
                    $html='';
                    $values=array_filter(explode(';',$config['value']));
                    foreach($values as $val) {
                        $html.='<button type="button" class="layui-btn layui-btn-xs layui-btn-normal">'.$val.'</button> ';
                    }
                    Return $html;
                }
                if($config['showstyle']==1) {
                    $config['value']=str_replace(';','<br>',$config['value']);
                }
                Return $config['value'];
            case 'config':
                Return array(
                            array('configname'=>'多行显示','hash'=>'showstyle','inputhash'=>'switch','tips'=>'每项单独一行显示,如列数大于1则自动开启多行显示'),
                            array('configname'=>'列数','hash'=>'column','inputhash'=>'number','tips'=>'单项内文本框数量,更改列数会丢失数据,请提前确认列数','defaultvalue'=>'1'),
                            array('configname'=>'效验','hash'=>'regulars','inputhash'=>'text','tips'=>'每列文本框的数据效验,使用;号分隔,如:id;hash,常见类型:id,email,phone,hash,也可输入正则表达式','defaultvalue'=>''),
                            array('configname'=>'提示','hash'=>'columntips','inputhash'=>'text','tips'=>'每列文本框的placeholder,使用;号分隔,如: 姓名;年龄;性别','defaultvalue'=>''),
                            array('configname'=>'宽度','hash'=>'width','inputhash'=>'text','tips'=>'每列文本框的输入框的宽度,使用;号分隔,如:300px;200px;100px,也可使用百分比'),
                            array('configname'=>'不允许重复','hash'=>'norepet','inputhash'=>'switch','tips'=>'开启后,不允许出现重复的内容'),
                            array('configname'=>'允许排序','hash'=>'sortable','inputhash'=>'switch','tips'=>'允许拖曳图标排序','defaultvalue'=>'1'),
                            array('configname'=>'最少项数','hash'=>'min','inputhash'=>'number','tips'=>'必须填写的项目数量','defaultvalue'=>'0'),
                            array('configname'=>'最多项数','hash'=>'max','inputhash'=>'number','tips'=>'最多填写的项目数量,0则不限制','defaultvalue'=>'0')
                        );
        }
        Return false;
    }
    function slider($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '滑块';
            case 'hash':
                Return 'slider';
            case 'group':
                Return '';
            case 'sql':
                Return 'int(9)';
            case 'form':
                V('input/slider',$config);
                Return '';
            case 'post':
                if(!isset($_POST[$config['name']])) {Return false;}
                if(isset($config['min']) && strlen($config['min']) && $_POST[$config['name']]<$config['min']) {
                    Return array('error'=>'最小为 '.$config['min']);
                }
                if(isset($config['max']) && strlen($config['max']) && $_POST[$config['name']]>$config['max']) {
                    Return array('error'=>'最大为 '.$config['max']);
                }
                Return $_POST[$config['name']];
            case 'config':
                Return array(
                            array('configname'=>'类型','hash'=>'type','inputhash'=>'radio','tips'=>'','defaultvalue'=>'1','values'=>"1:水平\n2:垂直",'savetype'=>1),
                            array('configname'=>'最小值','hash'=>'min','inputhash'=>'number','tips'=>'数字的最小值','defaultvalue'=>'0'),
                            array('configname'=>'最大值','hash'=>'max','inputhash'=>'number','tips'=>'数字的最大值','defaultvalue'=>'100'),
                            array('configname'=>'步长','hash'=>'step','inputhash'=>'number','tips'=>'拖动的步长','placeholder'=>'如:10','defaultvalue'=>'0'),
                            array('configname'=>'显示间隔','hash'=>'showstep','inputhash'=>'switch','tips'=>'显示步长的间隔')
                        );
        }
        Return false;
    }
    function rate($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '评分';
            case 'hash':
                Return 'rate';
            case 'group':
                Return '';
            case 'sql':
                if($config['half']) {Return 'float(3,1)';}
                Return 'int(3)';
            case 'form':
                if(substr($config['value'], -2)!=='.5') {$config['value']=intval($config['value']);}
                V('input/rate',$config);
                Return '';
            case 'view':
                if(substr($config['value'], -2)!=='.5') {$config['value']=intval($config['value']);}
                Return $config['value'];
            case 'post':
                if(!isset($_POST[$config['name']])) {Return false;}
                if($_POST[$config['name']]>$config['stars'] || $_POST[$config['name']]<0) {Return false;}
                if(!is_numeric($_POST[$config['name']])) {Return false;}
                if($config['half']) {
                    if(substr($_POST[$config['name']], -2)!=='.5') {
                        $_POST[$config['name']]=intval($_POST[$config['name']]);
                    }
                }else {
                    $_POST[$config['name']]=intval($_POST[$config['name']]);
                }
                if(isset($config['nonull']) && $config['nonull'] && $_POST[$config['name']]==0) {
                    Return false;
                }
                Return $_POST[$config['name']];
            case 'config':
                Return array(
                            array('configname'=>'允许半星','hash'=>'half','inputhash'=>'switch','tips'=>'请提前确认好是否允许半星,切换此选项会导致数据小数位丢失','defaultvalue'=>'0'),
                            array('configname'=>'星星数量','hash'=>'stars','inputhash'=>'number','tips'=>'默认为5颗星','placeholder'=>'最大为100','max'=>100,'defaultvalue'=>'5'),
                            array('configname'=>'颜色','hash'=>'color','inputhash'=>'colorpicker','tips'=>'','defaultvalue'=>'#1E9FFF'),
                            array('configname'=>'显示数量','hash'=>'showtext','inputhash'=>'switch','tips'=>'显示勾选星星的数量'),
                        );
        }
        Return false;
    }
    function icon($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '图标';
            case 'hash':
                Return 'icon';
            case 'group':
                Return '';
            case 'sql':
                Return 'varchar(64)';
            case 'form':
                $config['icons']=C('layui:icon_list');
                V('input/icon',$config);
                Return '';
            case 'view':
                if(!empty($config['value'])) {
                    Return '<i class="layui-icon '.$config['value'].'"></i>';
                }
                Return '';
            case 'post':
                Return htmlspecialchars(@$_POST[$config['name']]);
        }
        Return false;
    }
    function password($action,$config=array()) {
        if(isset($config['auth']['nocheckold']) && $config['auth']['nocheckold']) {
            $config['checkold']=0;
        }
        if(!isset($config['auth']['showpsw'])) {
            $config['auth']['showpsw']=0;
        }
        if(isset($config['source']) && $config['source']=='admin_defaultvalue_setting') {
            $config['checkold']=0;
            $config['auth']['showpsw']=1;
        }
        if(isset($config['md5']) && $config['md5']) {
            $config['auth']['showpsw']=0;
        }
        switch($action) {
            case 'name':
                Return '密码';
            case 'hash':
                Return 'password';
            case 'group':
                Return '';
            case 'sql':
                Return 'varchar(32)';
            case 'form':
                if(empty($config['value'])) {
                    $config['checkold']=0;
                }
                V('input/password',$config);
                Return '';
            case 'view':
                if($config['auth']['showpsw']) {
                    Return $config['value'];
                }elseif(!empty($config['value'])) {
                    Return '******';
                }
                Return '';
            case 'post':
                $post_password='';
                if(isset($_POST[$config['name']])) {
                    $post_password=trim($_POST[$config['name']]);
                }
                if(!$config['auth']['showpsw']) {
                    if(!isset($_POST[$config['name'].'_2'])) {
                        $_POST[$config['name'].'_2']='';
                    }
                    if($post_password!=trim($_POST[$config['name'].'_2'])) {
                        Return false;
                    }
                }
                if(empty($post_password)) {
                    Return null;
                }
                if(isset($config['min']) && $config['min'] && strlen($post_password)<$config['min']) {
                    Return false;
                }
                if(strlen($post_password)>32) {
                    Return false;
                }
                if(isset($config['checkold']) && $config['checkold'] && !empty($config['value'])) {
                    if(!isset($_POST[$config['name'].'_old'])) {Return false;}
                    $_POST[$config['name'].'_old']=trim($_POST[$config['name'].'_old']);
                    if($_POST[$config['name'].'_old']!=$config['value'] && C('cms:user:passwd2md5',$_POST[$config['name'].'_old'])!=$config['value']) {
                        Return false;
                    }
                }
                if(isset($config['md5']) && $config['md5']) {
                    $post_password=C('cms:user:passwd2md5',$post_password);
                }
                Return $post_password;
            case 'config':
                Return array(
                            array('configname'=>'加密','hash'=>'md5','inputhash'=>'switch','tips'=>'加密存储密码.加密后会使当前密码失效,请提前确认好是否加密.加密后无法显示密码'),
                            array('configname'=>'验证原密码','hash'=>'checkold','inputhash'=>'switch','tips'=>'修改密码时需要验证原密码'),
                            array('configname'=>'原密码提示','hash'=>'placeholder_old','inputhash'=>'text','tips'=>'原密码输入框的placeholder','defaultvalue'=>'请输入原密码'),
                            array('configname'=>'新密码提示','hash'=>'placeholder_new','inputhash'=>'text','tips'=>'新密码输入框的placeholder','defaultvalue'=>'请输入密码,不改则无需填写'),
                            array('configname'=>'密码最短长度','hash'=>'min','inputhash'=>'text','tips'=>'密码的最短长度','placeholder'=>'请填写密码的长度,如:6'),
                            array('configname'=>'密码确认提示','hash'=>'placeholder_check','inputhash'=>'text','tips'=>'新密码二次确认输入框的placeholder','defaultvalue'=>'请确认新密码'),
                        );
            case 'auth':
                Return array('nocheckold'=>'无需验证原密码','showpsw'=>'显示密码');
        }
        Return false;
    }
    function radio($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(!isset($config['values'])) {$config['values']=array();}
            if(!isset($config['savetype'])) {$config['savetype']=1;}
            if(!isset($config['value'])) {$config['value']='';}
            if(!is_array($config['values'])) {
                $config['values']=explode("\n",$config['values']);
            }
            $values=array();
            foreach($config['values'] as $key=>$val) {
                $val=htmlspecialchars($val);
                if(!empty($val)) {
                    if($config['savetype']==1) {
                        $val=str_replace('\\:','---colon---',$val);
                        $thisvalue=explode(':',$val);
                        $thisvalue[0]=str_replace('---colon---',':',$thisvalue[0]);
                        if(!isset($thisvalue[1])) {$thisvalue[1]=$thisvalue[0];}else {$thisvalue[1]=str_replace('---colon---',':',$thisvalue[1]);}
                        if(!isset($thisvalue[2])) {$thisvalue[2]='';}
                        if($config['disabled']) {$thisvalue[2]='disabled';}
                        if($config['value']==$thisvalue[0]) {$thisvalue[3]='checked';}
                        if(!isset($thisvalue[3])) {$thisvalue[3]='';}
                        $values[]=$thisvalue;
                    }else {
                        $thisvalue=array();
                        $thisvalue[0]=$val;
                        $thisvalue[1]=$val;
                        if(!isset($thisvalue[2])) {$thisvalue[2]='';}
                        if($config['disabled']) {$thisvalue[2]='disabled';}
                        if($config['value']==$val) {$thisvalue[3]='checked';}
                        if(!isset($thisvalue[3])) {$thisvalue[3]='';}
                        $values[]=$thisvalue;
                    }
                }
            }
        }
        switch($action) {
            case 'name':
                Return '单选框';
            case 'hash':
                Return 'radio';
            case 'group':
                Return '';
            case 'sql':
                if($config['savetype']==1) {Return 'int(9)';}
                Return 'varchar(255)';
            case 'form':
                if(!count($values)) {
                    Return '无选项';
                }
                foreach($values as $thisvalue) {
                    echo('<input type="radio" name="'.$config['name'].'" lay-filter="'.$config['name'].'" value="'.$thisvalue[0].'" title="'.$thisvalue[1].'" '.$thisvalue[2].' '.$thisvalue[3].'>');
                }
                Return '';
            case 'view':
                if($config['savetype']==1) {
                    foreach($values as $thisvalue) {
                        if($config['value']==$thisvalue[0]) {
                            Return $thisvalue[1];
                        }
                    }
                }
                Return $config['value'];
            case 'post':
                if(!isset($_POST[$config['name']])) {
                    if($config['savetype']==1) {Return false;}
                    Return '';
                }
                foreach($values as $thisvalue) {
                    if($_POST[$config['name']]==$thisvalue[0] && $thisvalue[2]!='disabled') {
                        Return $thisvalue[0];
                    }
                }
                Return false;
            case 'config':
                Return array(
                            array('configname'=>'保存类型','hash'=>'savetype','inputhash'=>'radio','tips'=>'切换保存类型会丢失信息,请提前确认好保存类型','defaultvalue'=>'1','values'=>"1:值\n2:标题",'savetype'=>1),
                            array('configname'=>'选项','hash'=>'values','inputhash'=>'textarea','tips'=>'一行一个选项.当保存类型为\'值\'时,每行选项的格式为 值:标题 如: 1:男'),
                        );
        }
        Return false;
    }
    function checkbox($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(!isset($config['values'])) {$config['values']=array();}
            if(!isset($config['savetype'])) {$config['savetype']=1;}
            if(!isset($config['value'])) {$config['value']='';}
            $config['value']=str_replace('\;','---semicolon---',$config['value']);
            $value=explode(';',$config['value']);
            foreach($value as $key=>$thisval) {
                $value[$key]=str_replace('---semicolon---',';',$thisval);
            }
            if(!is_array($config['values'])) {
                $config['values']=explode("\n",$config['values']);
            }
            $values=array();
            foreach($config['values'] as $key=>$val) {
                $val=htmlspecialchars($val);
                if(!empty($val)) {
                    if($config['savetype']==1) {
                        $val=str_replace('\\:','---colon---',$val);
                        $thisvalue=explode(':',$val);
                        $thisvalue[0]=str_replace('---colon---',':',$thisvalue[0]);
                        if(!isset($thisvalue[1])) {$thisvalue[1]=$thisvalue[0];}else {$thisvalue[1]=str_replace('---colon---',':',$thisvalue[1]);}
                        if(!isset($thisvalue[2])) {$thisvalue[2]='';}
                        if($config['disabled']) {$thisvalue[2]='disabled';}
                        if(in_array($thisvalue[0],$value)) {
                            $thisvalue[3]='checked';
                        }
                        if(!isset($thisvalue[3])) {$thisvalue[3]='';}
                        $values[]=$thisvalue;
                    }else {
                        $thisvalue=array();
                        $thisvalue[0]=$val;
                        $thisvalue[1]=$val;
                        if(!isset($thisvalue[2])) {$thisvalue[2]='';}
                        if($config['disabled']) {$thisvalue[2]='disabled';}
                        if(in_array($val,$value)) {
                            $thisvalue[3]='checked';
                        }
                        if(!isset($thisvalue[3])) {$thisvalue[3]='';}
                        $values[]=$thisvalue;
                    }
                }
            }
        }
        switch($action) {
            case 'name':
                Return '多选框';
            case 'hash':
                Return 'checkbox';
            case 'group':
                Return '';
            case 'sql':
                Return 'text';
            case 'form':
                if(!count($values)) {
                    Return '无选项';
                }
                if(!isset($config['style'])) {$config['style']=1;}
                if($config['style']==1) {$skin=' lay-skin="primary" ';}else {$skin='';}
                foreach($values as $thisvalue) {
                    echo('<input type="checkbox"'.$skin.'name="'.$config['name'].'[]"  lay-filter="'.$config['name'].'" value="'.$thisvalue[0].'" title="'.$thisvalue[1].'" '.$thisvalue[2].' '.$thisvalue[3].'>');
                }
                Return '';
            case 'view':
                $html='';
                if($config['savetype']==1) {
                    foreach($value as $thisvalue) {
                        foreach($values as $thisvalues) {
                            if(isset($thisvalues[3]) && $thisvalues[3]=='checked' && $thisvalue==$thisvalues[0]){
                                $html.='<button type="button" class="layui-btn cms-btn layui-btn-xs">'.$thisvalues[1].'</button> ';
                            }
                        }
                    }
                }else {
                    foreach($value as $thisvalue) {
                        foreach($values as $thisvalues) {
                            if(isset($thisvalues[3]) && $thisvalues[3]=='checked' && $thisvalue==$thisvalues[1]){
                                $html.='<button type="button" class="layui-btn cms-btn layui-btn-xs">'.$thisvalues[1].'</button> ';
                            }
                        }
                    }
                }
                Return $html;
            case 'post':
                if(!isset($config['mincheck']) || empty($config['mincheck'])) {$config['mincheck']=0;}
                if(!isset($config['maxcheck']) || empty($config['maxcheck'])) {$config['maxcheck']=999999;}
                if(!isset($_POST[$config['name']])) {
                    if($config['mincheck']) {
                        Return false;
                    }
                    Return '';
                }
                if(!is_array($_POST[$config['name']])) {
                    Return false;
                }
                $postvalue=array();
                foreach($values as $thisvalue) {
                    if($thisvalue[2]!='disabled' && in_array($thisvalue[0],$_POST[$config['name']])) {
                        if($config['savetype']==1) {
                            $postvalue[]=str_replace(';','\;',$thisvalue[0]);
                        }else {
                            $postvalue[]=str_replace(';','\;',$thisvalue[1]);
                        }
                    }
                }
                if(count($postvalue)<$config['mincheck']) {
                    Return array('error'=>'至少选择'.$config['mincheck'].'项');
                }
                if(count($postvalue)>$config['maxcheck']) {
                    Return array('error'=>'最多选择'.$config['maxcheck'].'项');
                }
                Return implode(';',$postvalue);
            case 'config':
                Return array(
                            array('configname'=>'样式','hash'=>'style','inputhash'=>'radio','tips'=>'','defaultvalue'=>'1','values'=>"1:原始风格\n2:按钮风格",'savetype'=>1),
                            array('configname'=>'保存类型','hash'=>'savetype','inputhash'=>'radio','tips'=>'切换保存类型会丢失信息,请提前确认好保存类型','defaultvalue'=>'1','values'=>"1:值\n2:标题",'savetype'=>1),
                            array('configname'=>'选项','hash'=>'values','inputhash'=>'textarea','tips'=>'一行一个选项.当保存类型为\'值\'时,每行选项的格式为 值:标题 如: 1:男'),
                            array('configname'=>'最少勾选','hash'=>'mincheck','inputhash'=>'number','tips'=>'最少必须勾选几项','placeholder'=>'如:1,则此表单必须勾选1项'),
                            array('configname'=>'最多勾选','hash'=>'maxcheck','inputhash'=>'number','tips'=>'最多勾选几项','placeholder'=>'如:5,则此表单最多勾选5项'),
                        );
        }
        Return false;
    }
    function select($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(!isset($config['values'])) {$config['values']=array();}
            if(!isset($config['savetype'])) {$config['savetype']=1;}
            if(!isset($config['value'])) {$config['value']='';}
            if(!is_array($config['values'])) {
                $config['values']=explode("\n",$config['values']);
            }
            $values=array();
            if(isset($config['selecttitle']) && isset($config['selectvalue']) && !empty($config['selecttitle'])) {
                $values[]=array($config['selectvalue'],$config['selecttitle'],'','');
            }
            foreach($config['values'] as $key=>$val) {
                $val=htmlspecialchars($val);
                if(!empty($val)) {
                    if($config['savetype']==1) {
                        $val=str_replace('\\:','---colon---',$val);
                        $thisvalue=explode(':',$val);
                        $thisvalue[0]=str_replace('---colon---',':',$thisvalue[0]);
                        if(!isset($thisvalue[1])) {$thisvalue[1]=$thisvalue[0];}else {$thisvalue[1]=str_replace('---colon---',':',$thisvalue[1]);}
                        if(!isset($thisvalue[2])) {$thisvalue[2]='';}
                        if($config['disabled']) {$thisvalue[2]='disabled';}
                        if($config['value']==$thisvalue[0]) {$thisvalue[3]='selected';}
                        if(!isset($thisvalue[3])) {$thisvalue[3]='';}
                        $values[]=$thisvalue;
                    }else {
                        $thisvalue=array();
                        $thisvalue[0]=$val;
                        $thisvalue[1]=$val;
                        if(!isset($thisvalue[2])) {$thisvalue[2]='';}
                        if($config['disabled']) {$thisvalue[2]='disabled';}
                        if($config['value']==$val) {$thisvalue[3]='selected';}
                        if(!isset($thisvalue[3])) {$thisvalue[3]='';}
                        $values[]=$thisvalue;
                    }
                }
            }
        }
        switch($action) {
            case 'name':
                Return '列表框';
            case 'hash':
                Return 'select';
            case 'group':
                Return '';
            case 'sql':
                if($config['savetype']==1) {Return 'int(9)';}
                Return 'varchar(255)';
            case 'form':
                if(!count($values)) {
                    Return '无选项';
                }
                if(!isset($config['search'])) {$config['search']=0;}
                if($config['search']) {$search=' lay-search';}else {$search='';}
                echo('<select'.$search.' name="'.$config['name'].'" lay-filter="'.$config['name'].'"');
                if($config['disabled']) {
                    echo(' disabled');
                }
                if(isset($config['lay-ignore']) && $config['lay-ignore']) {
                    echo(' lay-ignore');
                }
                echo('>');
                foreach($values as $thisvalue) {
                    echo('<option value="'.$thisvalue[0].'" '.$thisvalue[2].' '.$thisvalue[3].'>'.$thisvalue[1].'</option>');
                }
                echo('</select>');
                Return '';
            case 'view':
                if(isset($config['selectvalue']) && $config['value']==$config['selectvalue']) {
                    Return '';
                }
                if($config['savetype']==1) {
                    foreach($values as $thisvalue) {
                        if($config['value']==$thisvalue[0]) {
                            Return $thisvalue[1];
                        }
                    }
                }
                Return $config['value'];
            case 'post':
                if(!isset($_POST[$config['name']])) {
                    Return '';
                }
                if(isset($config['nonull']) && $config['nonull'] && isset($config['selectvalue']) && $_POST[$config['name']]==$config['selectvalue']) {
                    Return false;
                }
                foreach($values as $thisvalue) {
                    if($_POST[$config['name']]==$thisvalue[0] && $thisvalue[2]!='disabled') {
                        Return $thisvalue[0];
                    }
                }
                Return false;
            case 'config':
                Return array(
                            array('configname'=>'保存类型','hash'=>'savetype','inputhash'=>'radio','tips'=>'切换保存类型会丢失信息,请提前确认好保存类型','defaultvalue'=>'1','values'=>"1:值\n2:标题",'savetype'=>1),
                            array('configname'=>'选项','hash'=>'values','inputhash'=>'textarea','tips'=>'一行一个选项.当保存类型为\'值\'时,每行选项的格式为 值:标题 如: 1:男'),
                            array('configname'=>'默认文字','hash'=>'selecttitle','inputhash'=>'text','tips'=>'未选择时列表框的默认文字,不填则不显示','defaultvalue'=>'请选择'),
                            array('configname'=>'默认值','hash'=>'selectvalue','inputhash'=>'text','tips'=>'未选择时列表框的默认值','defaultvalue'=>'0'),
                            array('configname'=>'搜索','hash'=>'search','inputhash'=>'switch','tips'=>'当选项太多时,开启搜索功能可以快速找到对应的选项'),
                        );
        }
        Return false;
    }
    function transfer($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(!isset($config['values'])) {$config['values']=array();}
            if(!isset($config['savetype'])) {$config['savetype']=1;}
            if(!isset($config['value'])) {$config['value']='';}
            $value=explode(';',$config['value']);
            if(!is_array($config['values'])) {
                $config['values']=explode("\n",$config['values']);
            }
            $values=array();
            $checkedvalues=array();
            foreach($config['values'] as $key=>$val) {
                $val=htmlspecialchars($val);
                $val=str_replace(';',' ',$val);
                if(!empty($val)) {
                    if($config['savetype']==1) {
                        $val=str_replace('\\:','---colon---',$val);
                        $thisvalue=explode(':',$val);
                        $thisvalue[0]=str_replace('---colon---',':',$thisvalue[0]);
                        if(!isset($thisvalue[1])) {$thisvalue[1]=$thisvalue[0];}else {$thisvalue[1]=str_replace('---colon---',':',$thisvalue[1]);}
                        if(!isset($thisvalue[2])) {$thisvalue[2]='';}
                        if($config['disabled']) {$thisvalue[2]='disabled';}
                        if($thisvalue[2]=='disabled') {$thisvalue[2]=true;}else {$thisvalue[2]=false;}
                        if(in_array($thisvalue[0],$value)) {$thisvalue[3]=true;}else {$thisvalue[3]=false;}
                    }else {
                        $thisvalue=array();
                        $thisvalue[0]=$val;
                        $thisvalue[1]=$val;
                        if(!isset($thisvalue[2])) {$thisvalue[2]='';}
                        if($config['disabled']) {$thisvalue[2]='disabled';}
                        if($thisvalue[2]=='disabled') {$thisvalue[2]=true;}else {$thisvalue[2]=false;}
                        if(in_array($val,$value)) {$thisvalue[3]=true;}else {$thisvalue[3]=false;}
                    }
                    if($thisvalue[3]){
                        $checkedvalues[]=array('value'=>$thisvalue[0],'title'=>$thisvalue[1],'disabled'=>$thisvalue[2],'checked'=>false);
                    }else{
                        $values[]=array('value'=>$thisvalue[0],'title'=>$thisvalue[1],'disabled'=>$thisvalue[2],'checked'=>false);
                    }
                }
            }
            $newcheckedvalues=array();
            foreach ($value as $key => $thisvalue) {
                foreach ($checkedvalues as $checkedvalue) {
                    if($thisvalue==$checkedvalue['value']){
                        $newcheckedvalues[]=$checkedvalue;
                        break;
                    }
                }
            }
            $values=array_merge($values,$newcheckedvalues);
        }
        switch($action) {
            case 'name':
                Return '穿梭框';
            case 'hash':
                Return 'transfer';
            case 'group':
                Return '';
            case 'sql':
                Return 'text';
            case 'form':
                $config['values']=json_encode($values);
                $config['json_value']=json_encode(explode(';',$config['value']));
                V('input/transfer',$config);
                Return '';
            case 'view':
                if($config['savetype']==1) {
                    $view=array();
                    foreach($values as $thisvalue) {
                        if(in_array($thisvalue['value'],$value)) {
                            $view[]=$thisvalue['title'];
                        }
                    }
                    $view=implode(';',$view);
                }else {
                    $view=$config['value'];
                }
                Return $view;
            case 'post':
                if(!isset($config['mincheck']) || empty($config['mincheck'])) {$config['mincheck']=0;}
                if(!isset($config['maxcheck']) || empty($config['maxcheck'])) {$config['maxcheck']=999999;}
                if(!isset($_POST[$config['name']])) {
                    if($config['mincheck']) {
                        Return false;
                    }
                    Return '';
                }
                $postvalue=array();
                $postvalue_array=explode(';',$_POST[$config['name']]);
                foreach ($postvalue_array as $thispostvalue) {
                    foreach($values as $thisvalue) {
                        if($thisvalue['disabled']!='disabled' && $thispostvalue==$thisvalue['value']) {
                            if($config['savetype']==1) {
                                $postvalue[]=$thisvalue['value'];
                            }else {
                                $postvalue[]=$thisvalue['title'];
                            }
                        }
                    }
                }
                if(count($postvalue)<$config['mincheck']) {
                    Return array('error'=>'至少选择'.$config['mincheck'].'项');
                }
                if(count($postvalue)>$config['maxcheck']) {
                    Return array('error'=>'最多选择'.$config['maxcheck'].'项');
                }
                Return implode(';',$postvalue);
            case 'config':
                Return array(
                            array('configname'=>'保存类型','hash'=>'savetype','inputhash'=>'radio','tips'=>'切换保存类型会使文章字段或栏目变量信息丢失,请提前确认好保存类型','defaultvalue'=>'1','values'=>"1:值\n2:标题",'savetype'=>1),
                            array('configname'=>'选项','hash'=>'values','inputhash'=>'textarea','tips'=>'一行一个选项.当保存类型为\'值\'时,每行选项的格式为 值:标题 如: 1:男'),
                            array('configname'=>'最少勾选','hash'=>'mincheck','inputhash'=>'number','tips'=>'最少必须勾选几项','placeholder'=>'如:1,则此表单必须勾选1项'),
                            array('configname'=>'最多勾选','hash'=>'maxcheck','inputhash'=>'number','tips'=>'最多勾选几项','placeholder'=>'如:5,则此表单最多勾选5项'),
                            array('configname'=>'搜索','hash'=>'search','inputhash'=>'switch','tips'=>'当选项太多时,开启搜索功能可以快速找到对应的选项'),
                            array('configname'=>'穿梭框宽度','hash'=>'width','inputhash'=>'number','tips'=>'穿梭框的宽度,单位px,默认为200','placeholder'=>'200'),
                            array('configname'=>'穿梭框高度','hash'=>'height','inputhash'=>'number','tips'=>'穿梭框的高度,单位px,默认为340','placeholder'=>'340'),
                            array('configname'=>'待选区标题','hash'=>'title_left','inputhash'=>'text','tips'=>'','defaultvalue'=>'待选','placeholder'=>''),
                            array('configname'=>'已选区标题','hash'=>'title_right','inputhash'=>'text','tips'=>'','defaultvalue'=>'已选','placeholder'=>''),
                        );
        }
        Return false;
    }
    function article($action,$config=array()) {
        if(isset($config['auth']['nolimit']) && $config['auth']['nolimit']) {$config['limit']=0;}
        if(!isset($config['titlecolumn']) || empty($config['titlecolumn'])) {$config['titlecolumn']='title';}
        switch($action) {
            case 'name':
                Return '文章选择';
            case 'hash':
                Return 'article';
            case 'group':
                Return '文章';
            case 'sql':
                if(!$config['channel'] && !$config['module']) {$config['savetype']=2;}
                if($config['multiple']) {Return 'text';}
                if(!$config['module'] && !$config['channel']) {Return 'varchar(20)';}
                if($config['savetype']==2) {Return 'varchar(20)';}
                Return 'int(9)';
            case 'ajax':
                if($config['disabled']) {Return array('error'=>1,'message'=>'无权限');}
                if(isset($_POST['ajaxdo']) && $_POST['ajaxdo']=='choosechannel' && !$config['module'] && !$config['channel']) {
                    if($config['channel'] || $config['module'] || $config['class']) {Return array('error'=>1,'message'=>'error');}
                    $channels=C('cms:channel:tree',0,$_POST['classhash']);
                    $html='<select lay-filter="'.$_POST['name'].'_channelchose">';
                    if(count($channels)) {
                        $html.='<option value="">请选择栏目</option>';
                    }else {
                        $html.='<option value="">此应用下无栏目</option>';
                    }
                    foreach($channels as $channel) {
                        $html.='<option value="'.$channel['id'].'">'.$channel['ex'].''.$channel['channelname'].'</option>';
                    }
                    $html.='</select>';
                    Return array('error'=>0,'html'=>$html);
                }
                if(isset($_GET['ajaxdo']) && $_GET['ajaxdo']=='addarticle' && $config['channel']) {
                    $crud=array();
                    $crud['cid']=$_GET['cid'];
                    $crud['url']['add']='';
                    $crud['url']['var']='';
                    $crud['url']['save']=$config['ajax_url'].'&ajaxdo=savearticle';
                    $crud['referer']='';
                    $crud['breadcrumb']='';
                    $return=C('admin:article:edit',$crud);
                    if(!$return && E()){ return E(E()); }
                    return $return;
                }
                if(isset($_GET['ajaxdo']) && $_GET['ajaxdo']=='savearticle' && $config['channel']) {
                    $crud=array();
                    $crud['url']['edit']='';
                    $return=C('admin:article:editSave',$crud);
                    if(!$return && E()){ return array('msg'=>E(),'error'=>1); }
                    if(isset($return['msg'])){
                        $return['msg'].='<br>请刷新!';
                    }
                    return $return;
                }
                if(isset($_GET['ajaxdo']) && $_GET['ajaxdo']=='viewarticle') {
                    $crud=array();
                    $crud['cid']=$_GET['cid'];
                    $crud['id']=$_GET['id'];
                    $crud['url']['add']='';
                    $crud['url']['var']='';
                    $crud['url']['save']=$config['ajax_url'].'&ajaxdo=editarticle';
                    $crud['referer']='';
                    $crud['breadcrumb']='';
                    $return=C('admin:article:edit',$crud);
                    if(!$return && E()){ return E(E()); }
                    return $return;
                }
                if(isset($_GET['ajaxdo']) && $_GET['ajaxdo']=='editarticle') {
                    $crud=array();
                    $crud['cid']=$_POST['cid'];
                    $crud['id']=$_POST['id'];
                    $return=C('admin:article:editSave',$crud);
                    if(!$return && E()){ return array('msg'=>E(),'error'=>1); }
                    if(!isset($return['msg'])){
                        return '请刷新!';
                    }
                    return $return['msg'].'<br>请刷新!';
                }
                if(isset($_POST['ajaxdo']) && $_POST['ajaxdo']=='showvalue') {
                    $html='';
                    $values=explode(';',trim($_POST['value'],';'));
                    $articles=array();
                    foreach($values as $val) {
                        if(!empty($val)) {
                            $thisvalue=explode(':',$val);
                            $thiserror=0;
                            if($config['channel']) {
                                if($config['channel']!=$thisvalue[0]) {$thiserror=1;}
                            }elseif($config['class']) {
                                if($channel=C('cms:channel:get',$thisvalue[0])) {
                                    if($channel['classhash']!=$config['class']) {$thiserror=1;}
                                }else {
                                    $thiserror=1;
                                }
                            }elseif($config['module']) {
                                if($channel=C('cms:channel:get',$thisvalue[0])) {
                                    if($config['module']!=$channel['classhash'].':'.$channel['modulehash']) {$thiserror=1;}
                                }else {
                                    $thiserror=1;
                                }
                            }
                            if(isset($thisvalue[1]) && !$thiserror) {
                                $article_query=array();
                                $article_query['cid']=$thisvalue[0];
                                $article_query['where']['id']=$thisvalue[1];
                                if($config['limit']) {
                                    $article_query['where']['uid']=C('this:nowUser');
                                }
                                if($article=C('cms:article:getOne',$article_query)) {
                                    if(!isset($article[$config['titlecolumn']])) {
                                        $article[$config['titlecolumn']]='[无标题]';
                                    }
                                    $articles[]=array($article['cid'],$article['id'],$article[$config['titlecolumn']]);
                                }else {
                                    $articles[]=array(0,0,'[已删除]');
                                }
                            }else {
                                $articles[]=array(0,0,'[未知文章]');
                            }
                        }
                    }
                    foreach($articles as $article) {
                        if($config['multiple']) {
                            $html.='<tr data-id="'.$article[1].'" data-cid="'.$article[0].'"><td><i class="layui-icon layui-icon-find-fill sortable-color"></i> '.$article[2].' <i class="layui-icon close">&#x1006;</i></td><td style="width:60px;text-align:center">';
                            if($article[0]) {
                                $html.='<a href="javascript:;" class="'.$config['name'].'_article_view">查看</a>';
                            }
                            $html.='</td></tr>';
                        }else {
                            $html.='<tr data-id="'.$article[1].'" data-cid="'.$article[0].'"><td>'.$article[2].' <i class="layui-icon close">&#x1006;</i></td><td style="width:60px;text-align:center">';
                            if($article[0]) {
                                $html.='<a href="javascript:;" class="'.$config['name'].'_article_view">查看</a>';
                            }
                            $html.='</td></tr>';
                        }
                    }
                    if(!count($articles)) {
                        $html.='<tr><td colspan=2>未选</td></tr>';
                    }
                    Return array('error'=>0,'html'=>$html);
                    Return ;
                }
                if(isset($_POST['ajaxdo']) && $_POST['ajaxdo']=='articlelist') {
                    $html='';
                    $article_query=array();
                    if(is_numeric($_POST['cid'])) {
                        $article_query['cid']=$_POST['cid'];
                        if($config['channel']) {
                            if($config['channel']!=$_POST['cid']) {Return array('error'=>1,'message'=>'error');}
                        }elseif($config['class']) {
                            if($channel=C('cms:channel:get',$_POST['cid'])) {
                                if($channel['classhash']!=$config['class']) {Return array('error'=>1,'message'=>'error');}
                            }else {
                                Return array('error'=>1,'message'=>'error');
                            }
                        }elseif($config['module']) {
                            if($channel=C('cms:channel:get',$_POST['cid'])) {
                                if($config['module']!=$channel['classhash'].':'.$channel['modulehash']) {Return array('error'=>1,'message'=>'error');}
                            }else {
                                Return array('error'=>1,'message'=>'error');
                            }
                        }
                        $channel=C('cms:channel:get',$_POST['cid']);
                        if(!$module=C('cms:module:get',$channel['modulehash'],$channel['classhash'])) {
                            Return array('error'=>1,'message'=>'error');
                        }
                        if(!count(C($GLOBALS['C']['DbClass'].':getfields',$module['table']))) {
                            Return array('error'=>0,'pagecount'=>0,'html'=>'<tr><td colspan=2>没有文章</td></tr>');
                        }
                    }else {
                        $postmodule=explode(':',$_POST['cid']);
                        if($config['module']) {
                            if($_POST['cid']!=$config['module']) {Return array('error'=>1,'message'=>'error');}
                        }else {
                            Return array('error'=>1,'message'=>'error');
                        }
                        if(!is_hash($postmodule[0]) || !is_hash($postmodule[1])) {Return array('error'=>1,'message'=>'error');}
                        $article_query['classhash']=$postmodule[0];
                        $article_query['modulehash']=$postmodule[1];
                        if(!$module=C('cms:module:get',$article_query['modulehash'],$article_query['classhash'])) {
                            Return array('error'=>1,'message'=>'error');
                        }
                        if(!count(C($GLOBALS['C']['DbClass'].':getfields',$module['table']))) {
                            Return array('error'=>0,'pagecount'=>0,'html'=>'<tr><td colspan=2>没有文章</td></tr>');
                        }
                    }
                    $article_query['pagesize']=$config['pagesize'];
                    if($config['limit']) {
                        $article_query['where']['uid']=C('this:nowUser');
                    }
                    if(isset($_POST['keyword']) && !empty($_POST['keyword'])) {
                        $article_query['where'][$config['titlecolumn'].'%']=$_POST['keyword'];
                    }
                    $article_query['page']=intval($_POST['page']);
                    $articles=C('cms:article:get',$article_query);
                    $pagecount=0;
                    if(count($articles)==0) {
                        $html.='<tr><td colspan=2>没有文章</td></tr>';
                    }else {
                        pagelist();
                        $pageinfo=pageinfo();
                        if(isset($pageinfo['pagecount'])) {
                            $pagecount=$pageinfo['pagecount'];
                        }
                        if(!isset($articles[0][$config['titlecolumn']])) {
                            $html.='<tr><td colspan=2>此栏目下无标题</td></tr>';
                            $articles=array();
                        }
                        $values=explode(';',$_POST['value']);
                    }
                    $othercolumns=array_filter(explode(';',$config['columns']));
                    $modulecolumns=array();
                    if(count($othercolumns)){
                        $modulecolumns=C('cms:form:all','column',$module['hash'],$module['classhash']);
                        $modulecolumns=C('cms:form:getColumnCreated',$modulecolumns,$module['table']);
                    }
                    foreach ($modulecolumns as $modulekey => $thismodulecolumn) {
                        if(!in_array($thismodulecolumn['hash'],$othercolumns)){
                            unset($modulecolumns[$modulekey]);
                        }else{
                            $modulecolumns[$modulekey]=C('cms:form:build',$thismodulecolumn['id']);
                        }
                    }
                    foreach($articles as $article) {
                        if($config['multiple']) {
                            $html.='<tr data-id="'.$article['id'].'" data-cid="'.$article['cid'].'"><td><input type="checkbox"';
                            if(in_array($article['cid'].':'.$article['id'],$values)) {
                                $html.=' checked';
                            }
                            $html.=' lay-filter="'.$_POST['name'].'_article" data-id="'.$article['id'].'" data-cid="'.$article['cid'].'" value="" lay-skin="primary"  name="'.$_POST['name'].'-c1asscms" title="'.$article[$config['titlecolumn']].'"></td>';
                            foreach ($modulecolumns as $thismodulecolumn) {
                                if(isset($article[$thismodulecolumn['hash']])){
                                    $thismodulecolumn['value']=$article[$thismodulecolumn['hash']];
                                    $thismodulecolumn['article']=$article;
                                    ob_start();
                                    $inputview=C('cms:input:view',$thismodulecolumn);
                                    if(is_string($inputview) && $inputview){
                                        $html.='<td>'.$inputview.'</td>';
                                    }else{
                                        $html.='<td>'.ob_get_clean().'</td>';
                                    }
                                }
                            }
                            $html.='<td style="width:60px;text-align:center"><a href="javascript:;" class="'.$config['name'].'_article_view">查看</a></td></tr>';
                        }else {
                            $html.='<tr data-id="'.$article['id'].'" data-cid="'.$article['cid'].'"><td><input type="radio"';
                            if(in_array($article['cid'].':'.$article['id'],$values)) {
                                $html.=' checked';
                            }
                            $html.=' lay-filter="'.$_POST['name'].'_article" data-id="'.$article['id'].'" data-cid="'.$article['cid'].'" value="" name="'.$_POST['name'].'-c1asscms" title="'.$article[$config['titlecolumn']].'"></td>';
                            foreach ($modulecolumns as $thismodulecolumn) {
                                if(isset($article[$thismodulecolumn['hash']])){
                                    $thismodulecolumn['value']=$article[$thismodulecolumn['hash']];
                                    $thismodulecolumn['article']=$article;
                                    ob_start();
                                    $inputview=C('cms:input:view',$thismodulecolumn);
                                    if(is_string($inputview) && $inputview){
                                        $html.='<td>'.$inputview.'</td>';
                                    }else{
                                        $html.='<td>'.ob_get_clean().'</td>';
                                    }
                                }
                            }
                            $html.='<td style="width:60px;text-align:center"><a href="javascript:;" class="'.$config['name'].'_article_view">查看</a></td></tr>';
                        }
                    }
                    Return array('error'=>0,'pagecount'=>$pagecount,'html'=>$html);
                }
                Return '';
            case 'form':
                if(!$config['channel'] && !$config['module']) {$config['savetype']=2;}
                $config['chosehtml']='';
                if($config['savetype']==1) {
                    $newvalues='';
                    if(stripos($config['value'],':')===false) {
                        $values=explode(';',$config['value']);
                        foreach($values as $val) {
                            if(!empty($val)) {
                                if($config['channel']) {
                                    $newvalues.=$config['channel'].':'.$val.';';
                                }elseif($config['module']) {
                                    $module=explode(':',$config['module']);
                                    $article_query=array();
                                    $article_query['classhash']=$module[0];
                                    $article_query['modulehash']=$module[1];
                                    $article_query['where']['id']=$val;
                                    if($article=C('cms:article:getOne',$article_query)) {
                                        $newvalues.=$article['cid'].':'.$val.';';
                                    }
                                }
                            }
                        }
                    }
                    if($config['channel'] || $config['module']) {
                        $config['value']=$newvalues;
                    }
                }
                $config['defaultchannel']='';
                if(!$config['channel'] && !$config['module']) {
                    if($config['value']) {
                        $values=explode(';',trim($config['value'],';'));
                        $lastvalue=explode(':',$values[count($values)-1]);
                        if($lastvalue[0]) {
                            $lastchannel=C('cms:channel:get',$lastvalue[0]);
                        }
                    }
                    if($config['class']) {
                        if(isset($lastchannel) && $lastchannel) {
                            $lastchannel_cid=$lastchannel['id'];
                        }else {
                            $lastchannel_cid='';
                        }
                        $config['choseinput']=array('inputhash'=>'channelselect','name'=>$config['name'].'_channelselect','value'=>$lastchannel_cid,'otherclasshash'=>$config['class']);
                        $config['defaultchannel']=$lastchannel_cid;
                    }else {
                        $lastchannel_cid=0;
                        if(isset($lastchannel) && $lastchannel) {
                            $lastclass=$lastchannel['classhash'];
                            $lastchannel_cid=$lastchannel['id'];
                            $config['defaultchannel']=$lastchannel_cid;
                        }else {
                            $lastclass=$config['classhash'];
                        }
                        $config['choseinput']=array('inputhash'=>'classselect','module'=>1,'name'=>$config['name'].'_classselect','value'=>$lastclass);
                        $channels=C('cms:channel:tree',0,$lastclass);
                        $config['chosehtml'].='<select lay-filter="'.$config['name'].'_channelchose">';
                        if(count($channels)) {
                            $config['chosehtml'].='<option value="">请选择栏目</option>';
                        }else {
                            $config['chosehtml'].='<option value="">此应用无下属栏目</option>';
                        }
                        foreach($channels as $channel) {
                            if($lastchannel_cid==$channel['id']) {
                                $config['chosehtml'].='<option value="'.$channel['id'].'" selected>'.$channel['ex'].''.$channel['channelname'].'</option>';
                            }else {
                                $config['chosehtml'].='<option value="'.$channel['id'].'">'.$channel['ex'].''.$channel['channelname'].'</option>';
                            }
                        }
                        $config['chosehtml'].='</select>';
                    }
                }
                $othercolumns=array_filter(explode(';',$config['columns']));
                $config['maxwidth']=500;
                if(count($othercolumns)){
                    $config['maxwidth']=0;
                }
                $config['colspan']=2+count($othercolumns);
                $values=explode(';',trim($config['value'],';'));
                $config['articles']=array();
                foreach($values as $val) {
                    if(!empty($val)) {
                        $thisvalue=explode(':',$val);
                        if(isset($thisvalue[1])) {
                            $article_query=array();
                            $article_query['cid']=$thisvalue[0];
                            $article_query['where']['id']=$thisvalue[1];
                            if($article=C('cms:article:getOne',$article_query)) {
                                if(!isset($article[$config['titlecolumn']])) {
                                    $article[$config['titlecolumn']]='[无标题]';
                                }
                                $config['articles'][]=array($article['cid'],$article['id'],$article[$config['titlecolumn']]);
                            }else {
                                $config['articles'][]=array(0,0,'[已删除]');
                            }
                        }
                    }
                }
                V('input/article',$config);
                Return '';
            case 'view':
                if(!$config['channel'] && !$config['module']) {$config['savetype']=2;}
                if($config['savetype']==1) {
                    $values=explode(';',$config['value']);
                    $article_query=array();
                    if(!empty($config['channel'])) {
                        $article_query['cid']=$config['channel'];
                    }elseif(!empty($config['module'])) {
                        $module=explode(':',$config['module']);
                        $article_query['classhash']=$module[0];
                        $article_query['modulehash']=$module[1];
                    }
                    $titles=array();
                    foreach($values as $thisvalue) {
                        if(!empty($thisvalue)) {
                            $article_query['where']['id']=$thisvalue;
                            if($article=C('cms:article:getOne',$article_query)) {
                                if(!isset($article[$config['titlecolumn']])) {
                                    $article[$config['titlecolumn']]='[无标题];';
                                }
                                $titles[]=$article[$config['titlecolumn']];
                            }else {
                                $titles[]='[已删除]';
                            }
                        }
                    }
                    $html='';
                    foreach ($titles as $title) {
                        if($title=='[已删除]'){
                            $html.='<button type="button" class="layui-btn layui-btn-disabled layui-btn-xs">'.$title.'</button> ';
                        }else{
                            $html.='<button type="button" class="layui-btn cms-btn layui-btn-xs">'.$title.'</button> ';
                        }
                    }
                    return $html;
                }else {
                    $values=explode(';',$config['value']);
                    $article_query=array();
                    $titles=array();
                    foreach($values as $thisvalue) {
                        $thisvalues=explode(':',$thisvalue);
                        if(count($thisvalues)==2) {
                            $article_query['cid']=$thisvalues[0];
                            $article_query['where']['id']=$thisvalues[1];
                            if($article=C('cms:article:getOne',$article_query)) {
                                if(!isset($article[$config['titlecolumn']])) {
                                    $article[$config['titlecolumn']]='[无标题];';
                                }
                                $titles[]=$article[$config['titlecolumn']];
                            }else {
                                $titles[]='[已删除]';
                            }
                        }
                    }
                    $html='';
                    foreach ($titles as $title) {
                        if($title=='[已删除]'){
                            $html.='<button type="button" class="layui-btn layui-btn-disabled layui-btn-xs">'.$title.'</button> ';
                        }else{
                            $html.='<button type="button" class="layui-btn cms-btn layui-btn-xs">'.$title.'</button> ';
                        }
                    }
                    return $html;
                }
                Return '';
            case 'post':
                if(!isset($_POST[$config['name']])) {Return '';}
                if(!$config['channel'] && !$config['module']) {$config['savetype']=2;}
                $values=explode(';',trim($_POST[$config['name']],';'));
                $articles=array();
                foreach($values as $val) {
                    if(!empty($val)) {
                        $thisvalue=explode(':',$val);
                        $thiserror=0;
                        if($config['channel']) {
                            if($config['channel']!=$thisvalue[0]) {$thiserror=1;}
                        }elseif($config['class']) {
                            if($channel=C('cms:channel:get',$thisvalue[0])) {
                                if($channel['classhash']!=$config['class']) {$thiserror=1;}
                            }else {
                                $thiserror=1;
                            }
                        }elseif($config['module']) {
                            if($channel=C('cms:channel:get',$thisvalue[0])) {
                                if($config['module']!=$channel['classhash'].':'.$channel['modulehash']) {$thiserror=1;}
                            }else {
                                $thiserror=1;
                            }
                        }
                        if($thiserror) {Return false;}
                        if(isset($thisvalue[1]) && !$thiserror) {
                            $article_query=array();
                            $article_query['cid']=$thisvalue[0];
                            $article_query['where']['id']=$thisvalue[1];
                            if($config['limit']) {
                                $article_query['where']['uid']=C('this:nowUser');
                            }
                            if($article=C('cms:article:getOne',$article_query)) {
                                $articles[]=$article;
                            }
                        }
                    }
                }
                $values=array();
                foreach($articles as $article) {
                    if($config['multiple'] || !count($values)) {
                        if($config['savetype']==1) {
                            $values[]=$article['id'];
                        }else {
                            $values[]=$article['cid'].':'.$article['id'];
                        }
                    }
                }
                if(isset($config['nonull']) && $config['nonull'] && !count($values)) {
                    Return false;
                }
                if(empty($config['mincheck'])) {$config['mincheck']=0;}
                if(empty($config['maxcheck'])) {$config['maxcheck']=9999;}
                if($config['multiple'] && count($values)<$config['mincheck']) {
                    Return array('error'=>'至少选择'.$config['mincheck'].'项');
                }
                if($config['multiple'] && count($values)>$config['maxcheck']) {
                    Return array('error'=>'最多选择'.$config['maxcheck'].'项');
                }
                if(!$config['multiple'] && $config['savetype']==1 && isset($values[0])) {
                    Return $values[0];
                }
                if($config['multiple']) {
                    Return ';'.implode(';',$values).';';
                }else {
                    if($config['savetype']==1 && !count($values)){
                        return 0;
                    }
                    Return implode(';',$values);
                }
            case 'auth':
                Return array('nolimit'=>'关闭鉴权');
            case 'config':
                Return array(
                        array('configname'=>'来源栏目','hash'=>'channel','inputhash'=>'classchannel','tips'=>'文章来源栏目,不选择来源栏目则手动选择.更改来源会丢失数据,请提前选择'),
                        array('configname'=>'来源应用','hash'=>'class','inputhash'=>'classselect','tips'=>'如未选择来源栏目,则手动选择来源应用内的栏目','module'=>1),
                        array('configname'=>'来源模型','hash'=>'module','inputhash'=>'classmodule','tips'=>'如未选择来源栏目,则手动选择来源模型内的文章'),
                        array('configname'=>'多选','hash'=>'multiple','inputhash'=>'switch','tips'=>'更改单选多选会丢失数据,请提前确认保存类型'),
                        array('configname'=>'保存类型','hash'=>'savetype','inputhash'=>'radio','tips'=>'切换类型会丢失信息,请提前确认保存类型.如您未选择来源栏目或来源模型,则只能保存为CID+ID格式','defaultvalue'=>'1','values'=>"1:ID\n2:CID+ID",'savetype'=>1),
                        array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'标题字段,默认为title,请确保来源栏目中拥有此字段','defaultvalue'=>'title'),
                        array('configname'=>'列表字段','hash'=>'columns','inputhash'=>'text','tips'=>'需要显示在文章选择的字段,多个字段是用;分隔,如:pic;datetime','defaultvalue'=>''),
                        array('configname'=>'鉴权','hash'=>'limit','inputhash'=>'switch','tips'=>'开启后,用户只能选择自己创建的文章.管理员不受限','defaultvalue'=>'0'),
                        array('configname'=>'显示数量','hash'=>'pagesize','inputhash'=>'number','tips'=>'文章选项每页显示的数量','defaultvalue'=>'10'),
                        array('configname'=>'最少勾选','hash'=>'mincheck','inputhash'=>'number','tips'=>'最少必须勾选几项,仅在开启多选后有效.','placeholder'=>'如:1,则此表单必须勾选1项'),
                        array('configname'=>'最多勾选','hash'=>'maxcheck','inputhash'=>'number','tips'=>'最多勾选几项,仅在开启多选后有效.','placeholder'=>'如:5,则此表单最多勾选5项'),
                        
                    );
        }
        Return false;
    }
    function articleradio($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(!($config['cid'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            $channel=C('cms:channel:get',$config['cid']);
            if(!$module=C('cms:module:get',$channel['modulehash'],$channel['classhash'])) {
                Return false;
            }
            if(!($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '文章表不存在';
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $article_query=array();
            $article_query['cid']=$config['cid'];
            $article_query['pagesize']=9999;
            if(isset($config['auth']['nolimit']) && $config['auth']['nolimit']) {$config['limit']=0;}
            if($config['limit']) {
                $article_query['where']['uid']=C('this:nowUser');
            }
            $articles=C('cms:article:get',$article_query);
            $config['values']=array();
            foreach($articles as $article) {
                if($config['savetype']==2) {
                    $config['values'][]=implode(':',array($article[$config['titlecolumn']]));
                }else {
                    $article[$config['titlecolumn']]=str_replace(':','\:',$article[$config['titlecolumn']]);
                    $config['values'][]=implode(':',array($article['id'],$article[$config['titlecolumn']]));
                }
            }
        }
        switch($action) {
            case 'name':
                Return '文章单选框';
            case 'hash':
                Return 'articleradio';
            case 'group':
                Return '文章';
            case 'sql':
                if($config['savetype']==1) {Return 'int(9)';}
                Return 'varchar(255)';
            case 'form':
                $config['inputhash']='radio';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='radio';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='radio';
                $postvalue=C('cms:input:post',$config);
                if(!strlen($postvalue) && $config['savetype']==1){
                    $postvalue=0;
                }
                Return $postvalue;
            case 'auth':
                Return array('nolimit'=>'关闭鉴权');
            case 'config':
                Return array(
                        array('configname'=>'来源栏目','hash'=>'cid','inputhash'=>'classchannel','tips'=>'选项来源栏目'),
                        array('configname'=>'鉴权','hash'=>'limit','inputhash'=>'switch','tips'=>'开启后,用户只能选择自己创建的文章.管理员不受限','defaultvalue'=>'0'),
                        array('configname'=>'保存类型','hash'=>'savetype','inputhash'=>'radio','tips'=>'切换保存类型会丢失信息,请提前确认好保存类型','defaultvalue'=>'1','values'=>"1:文章ID\n2:标题",'savetype'=>1),
                        array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'标题字段,默认为title,请确保来源栏目中拥有此字段','defaultvalue'=>'title'),
                    );
        }
        Return false;
    }
    function articlecheckbox($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(!isset($config['cid']) || empty($config['cid'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            $channel=C('cms:channel:get',$config['cid']);
            if(!$module=C('cms:module:get',$channel['modulehash'],$channel['classhash'])) {
                Return false;
            }
            if(!($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '文章表不存在';
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $article_query=array();
            $article_query['cid']=$config['cid'];
            $article_query['pagesize']=9999;
            if(isset($config['auth']['nolimit']) && $config['auth']['nolimit']) {$config['limit']=0;}
            if($config['limit']) {
                $article_query['where']['uid']=C('this:nowUser');
            }
            $articles=C('cms:article:get',$article_query);
            $config['values']=array();
            foreach($articles as $article) {
                if($config['savetype']==2) {
                    $config['values'][]=implode(':',array($article[$config['titlecolumn']]));
                }else {
                    $article[$config['titlecolumn']]=str_replace(':','\:',$article[$config['titlecolumn']]);
                    $config['values'][]=implode(':',array($article['id'],$article[$config['titlecolumn']]));
                }
            }
        }
        switch($action) {
            case 'name':
                Return '文章多选框';
            case 'hash':
                Return 'articlecheckbox';
            case 'group':
                Return '文章';
            case 'sql':
                Return 'text';
            case 'form':
                $config['inputhash']='checkbox';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='checkbox';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='checkbox';
                Return C('cms:input:post',$config);
            case 'auth':
                Return array('nolimit'=>'关闭鉴权');
            case 'config':
            Return array(
                        array('configname'=>'来源栏目','hash'=>'cid','inputhash'=>'classchannel','tips'=>'选项来源栏目'),
                        array('configname'=>'鉴权','hash'=>'limit','inputhash'=>'switch','tips'=>'开启后,用户只能选择自己创建的文章.管理员不受限','defaultvalue'=>'0'),
                        array('configname'=>'保存类型','hash'=>'savetype','inputhash'=>'radio','tips'=>'切换保存类型会丢失信息,请提前确认好保存类型','defaultvalue'=>'1','values'=>"1:文章ID\n2:标题",'savetype'=>1),
                        array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'标题字段,默认为title,请确保来源栏目中拥有此字段','defaultvalue'=>'title'),
                        array('configname'=>'最少勾选','hash'=>'mincheck','inputhash'=>'number','tips'=>'最少必须勾选几项','placeholder'=>'如:1,则此表单必须勾选1项'),
                        array('configname'=>'最多勾选','hash'=>'maxcheck','inputhash'=>'number','tips'=>'最多勾选几项','placeholder'=>'如:5,则此表单最多勾选5项'),
                    );
        }
        Return false;
    }
    function articleselect($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(!isset($config['cid']) || empty($config['cid'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            $channel=C('cms:channel:get',$config['cid']);
            if(!$module=C('cms:module:get',$channel['modulehash'],$channel['classhash'])) {
                Return false;
            }
            if(!($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '文章表不存在';
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $article_query=array();
            $article_query['cid']=$config['cid'];
            $article_query['pagesize']=9999;
            if(isset($config['auth']['nolimit']) && $config['auth']['nolimit']) {$config['limit']=0;}
            if($config['limit']) {
                $article_query['where']['uid']=C('this:nowUser');
            }
            $articles=C('cms:article:get',$article_query);
            $config['values']=array();
            foreach($articles as $article) {
                if($config['savetype']==2) {
                    $config['values'][]=implode(':',array($article[$config['titlecolumn']]));
                }else {
                    $article[$config['titlecolumn']]=str_replace(':','\:',$article[$config['titlecolumn']]);
                    $config['values'][]=implode(':',array($article['id'],$article[$config['titlecolumn']]));
                }
            }
        }
        switch($action) {
            case 'name':
                Return '文章列表框';
            case 'hash':
                Return 'articleselect';
            case 'group':
                Return '文章';
            case 'sql':
                if($config['savetype']==1) {Return 'int(9)';}
                Return 'varchar(255)';
            case 'form':
                $config['inputhash']='select';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='select';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='select';
                $postvalue=C('cms:input:post',$config);
                if(!strlen($postvalue) && $config['savetype']==1){
                    $postvalue=0;
                }
                Return $postvalue;
            case 'auth':
                Return array('nolimit'=>'关闭鉴权');
            case 'config':
            Return array(
                        array('configname'=>'来源栏目','hash'=>'cid','inputhash'=>'classchannel','tips'=>'选项来源栏目'),
                        array('configname'=>'鉴权','hash'=>'limit','inputhash'=>'switch','tips'=>'开启后,用户只能选择自己创建的文章.管理员不受限','defaultvalue'=>'0'),
                        array('configname'=>'保存类型','hash'=>'savetype','inputhash'=>'radio','tips'=>'切换保存类型会丢失信息,请提前确认好保存类型','defaultvalue'=>'1','values'=>"1:文章ID\n2:标题",'savetype'=>1),
                        array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'标题字段,默认为title,请确保来源栏目中拥有此字段','defaultvalue'=>'title'),
                        array('configname'=>'默认文字','hash'=>'selecttitle','inputhash'=>'text','tips'=>'未选择时列表框的默认文字,不填则不显示','defaultvalue'=>'请选择'),
                        array('configname'=>'默认值','hash'=>'selectvalue','inputhash'=>'text','tips'=>'未选择时列表框的默认值','defaultvalue'=>'0'),
                        array('configname'=>'搜索','hash'=>'search','inputhash'=>'switch','tips'=>'当选项太多时,开启搜索功能可以快速找到对应的选项'),
                    );
        }
        Return false;
    }
    function articletransfer($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(!isset($config['cid']) || empty($config['cid'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            $channel=C('cms:channel:get',$config['cid']);
            if(!$module=C('cms:module:get',$channel['modulehash'],$channel['classhash'])) {
                Return false;
            }
            if(!($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '文章表不存在';
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $article_query=array();
            $article_query['cid']=$config['cid'];
            $article_query['pagesize']=9999;
            if(isset($config['auth']['nolimit']) && $config['auth']['nolimit']) {$config['limit']=0;}
            if($config['limit']) {
                $article_query['where']['uid']=C('this:nowUser');
            }
            $articles=C('cms:article:get',$article_query);
            $config['values']=array();
            foreach($articles as $article) {
                if($config['savetype']==2) {
                    $config['values'][]=implode(':',array($article[$config['titlecolumn']]));
                }else {
                    $article[$config['titlecolumn']]=str_replace(':','\:',$article[$config['titlecolumn']]);
                    $config['values'][]=implode(':',array($article['id'],$article[$config['titlecolumn']]));
                }
            }
        }
        switch($action) {
            case 'name':
                Return '文章穿梭框';
            case 'hash':
                Return 'articletransfer';
            case 'group':
                Return '文章';
            case 'sql':
                Return 'text';
            case 'form':
                $config['inputhash']='transfer';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='transfer';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='transfer';
                Return C('cms:input:post',$config);
            case 'auth':
                Return array('nolimit'=>'关闭鉴权');
            case 'config':
            Return array(
                        array('configname'=>'来源栏目','hash'=>'cid','inputhash'=>'classchannel','tips'=>'选项来源栏目'),
                        array('configname'=>'鉴权','hash'=>'limit','inputhash'=>'switch','tips'=>'开启后,用户只能选择自己创建的文章.管理员不受限','defaultvalue'=>'0'),
                        array('configname'=>'保存类型','hash'=>'savetype','inputhash'=>'radio','tips'=>'切换保存类型会丢失信息,请提前确认好保存类型','defaultvalue'=>'1','values'=>"1:文章ID\n2:标题",'savetype'=>1),
                        array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'标题字段,默认为title,请确保来源栏目中拥有此字段','defaultvalue'=>'title'),
                        array('configname'=>'最少勾选','hash'=>'mincheck','inputhash'=>'number','tips'=>'最少必须勾选几项','placeholder'=>'如:1,则此表单必须勾选1项'),
                        array('configname'=>'最多勾选','hash'=>'maxcheck','inputhash'=>'number','tips'=>'最多勾选几项','placeholder'=>'如:5,则此表单最多勾选5项'),
                        array('configname'=>'搜索','hash'=>'search','inputhash'=>'switch','tips'=>'当选项太多时,开启搜索功能可以快速找到对应的选项'),
                        array('configname'=>'穿梭框宽度','hash'=>'width','inputhash'=>'number','tips'=>'穿梭框的宽度,单位px,默认为200','placeholder'=>'200'),
                        array('configname'=>'穿梭框高度','hash'=>'height','inputhash'=>'number','tips'=>'穿梭框的高度,单位px,默认为340','placeholder'=>'340'),
                        array('configname'=>'待选区标题','hash'=>'title_left','inputhash'=>'text','tips'=>'','defaultvalue'=>'待选','placeholder'=>''),
                        array('configname'=>'已选区标题','hash'=>'title_right','inputhash'=>'text','tips'=>'','defaultvalue'=>'已选','placeholder'=>''),
                    );
        }
        Return false;
    }
    function articleunlimit($action,$config=array()) {
        if($action=='disabledArticles') {
            if($config['fidvalue']==0){
                return array();
            }else{
                if(!isset($config['times'])) {
                    $treearticles=array($config['fidvalue']);
                }else{
                    $treearticles=array();
                }
            }
            if(!isset($config['times'])) {$config['times']=0;}
            $articles=C('cms:article:get',array('cid'=>$config['cid'],'pagesize'=>9999,'where'=>array($config['fidcolumn']=>$config['fidvalue'])));
            foreach($articles as $article) {
                if($article[$config['fidcolumn']]==$config['fidvalue']) {
                    $treearticles[]=$article['id'];
                    $config['times']++;
                    $oldfid=$config['fidvalue'];
                    $config['fidvalue']=$article['id'];
                    $sonarticles=C('this:input:articleunlimit','disabledArticles',$config);
                    if(count($sonarticles)) {
                        foreach($sonarticles as $sonarticle) {
                            $treearticles[]=$sonarticle;
                        }
                    }
                    $config['times']--;
                    $config['fidvalue']=$oldfid;
                }
            }
            Return $treearticles;
        }
        if($action=='form' || $action=='view' || $action=='post' || $action=='ajax') {
            if(!$channel=C('cms:channel:get',$config['cid'])){
                if($action=='post') {Return false;}
                Return '尚未配置来源栏目';
            }
            if(!$module=C('cms:module:get',$channel['modulehash'],$channel['classhash'])){
                if($action=='post') {Return false;}
                Return '配置有误';
            }
            if(empty($config['fidcolumn'])) {$config['fidcolumn']='fid';}
            if(empty($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '来源栏目文章表不存在';
            }
            if(!isset($tablefields[$config['fidcolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有分类字段:'.htmlspecialchars($config['fidcolumn']);
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $config['disabledArticles']=array();
            if($action=='ajax' && isset($_POST['source_cid']) && isset($_POST['source_id'])){$config['source_cid']=$_POST['source_cid'];$config['source_id']=$_POST['source_id'];}
            if(isset($config['id']) && isset($config['kind']) && $config['kind']=='column' && $config['hash']==$config['fidcolumn'] && isset($config['source_cid']) && $config['source_cid']==$config['cid']  && isset($config['source_id'])){
                $config['fidvalue']=$config['source_id'];
                $config['disabledArticles']=C('this:input:articleunlimit','disabledArticles',$config);
            }
        }
        switch($action) {
            case 'name':
                Return '文章无限联动框';
            case 'hash':
                Return 'articleunlimit';
            case 'group':
                Return '文章';
            case 'sql':
                Return 'bigint(11)';
            case 'form':
                $config['selecthtml']=C('this:input:articleunlimit','selecthtml',$config);
                if(!$config['selecthtml']) {$config['selecthtml']='配置错误';}
                V('input/articleunlimit',$config);
                Return '';
             case "ajax":
                if(isset($config['disabled']) && $config['disabled']) {Return array('error'=>1,'msg'=>'无权限');}
                $config['value']=intval(@$_POST['value']);
                if($html=C('this:input:articleunlimit','selecthtml',$config)) {
                    Return array('error'=>0,'html'=>$html);
                }
                Return array('error'=>1);
            case 'view':
                if($config['value']) {
                    if($article=C('cms:article:getOne',array('cid'=>$config['cid'],'where'=>array('id'=>intval($config['value']))))) {
                        echo($article[$config['titlecolumn']]);
                    }else{
                        echo('[不存在]');
                    }
                }else {
                    echo('[未选]');
                }
                Return '';
            case 'post':
                if(isset($config['nonull']) && $config['nonull']) {
                    if(!isset($_POST[$config['name']]) || empty($_POST[$config['name']])) {
                        Return false;
                    }
                }
                if(isset($_POST[$config['name']])) {
                    if(!$thisarticle=C('cms:article:getOne',array('cid'=>$config['cid'],'where'=>array('id'=>intval($_POST[$config['name']]))))) {
                        Return false;
                    }
                    Return intval(@$_POST[$config['name']]);
                }else{
                    Return 0;
                }
            case 'config':
                Return array(
                        array('configname'=>'来源栏目','hash'=>'cid','inputhash'=>'classchannel','tips'=>'选项来源栏目'),
                        array('configname'=>'上级字段','hash'=>'fidcolumn','inputhash'=>'text','tips'=>'来源栏目中文章的上级字段','defaultvalue'=>'fid'),
                        array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'来源栏目中文章的标题字段','defaultvalue'=>'title')
                    );
            case 'selecthtml':
                if($config['value']) {
                    $thisarticle=C('cms:article:getOne',array('cid'=>$config['cid'],'where'=>array('id'=>$config['value'])));
                    if($thisarticle) {
                        $fid=$thisarticle[$config['fidcolumn']];
                    }else {
                        if(isset($config['son']) && !$config['son']) {Return false;}
                        $config['value']=0;
                        Return '请重新选择 '.C('this:input:articleunlimit','selecthtml',$config);
                    }
                }else {
                    $fid=0;
                }
                if(!isset($config['son'])) {
                    $config['son']=1;
                }
                $articles=C('cms:article:get',array('cid'=>$config['cid'],'pagesize'=>9999,'where'=>array($config['fidcolumn']=>$fid)));
                $html='<div class="layui-inline"><select lay-filter="articleunlimit_'.$config['name'].'">';
                if(count($articles)) {
                    $html.='<option value="">请选择</option>';
                    foreach($articles as $article) {
                        if(!isset($article[$config['titlecolumn']])) {
                            $article[$config['titlecolumn']]='字段不存在['.$config['titlecolumn'].']';
                        }
                        $disabledHtml='';
                        if(in_array($article['id'],$config['disabledArticles'])){$disabledHtml=' disabled';}
                        if($article['id']==$config['value']) {
                            $html.='<option value="'.$article['id'].'" selected'.$disabledHtml.'>'.$article[$config['titlecolumn']].'</option>';
                        }else {
                            $html.='<option value="'.$article['id'].'"'.$disabledHtml.'>'.$article[$config['titlecolumn']].'</option>';
                        }
                    }
                }else {
                    $html.='<option value="">无选项</option>';
                    $config['son']=0;
                }
                $html.='</select></div>';
                if($fid>0) {
                    $fidvalue=$config['value'];
                    $son=$config['son'];
                    $config['value']=$fid;
                    $config['son']=0;
                    $uplevelhtml=C('this:input:articleunlimit','selecthtml',$config);
                    if(!$uplevelhtml) {
                        if($fidvalue && $son) {
                            $config['value']=0;
                            Return '请重新选择 '.C('this:input:articleunlimit','selecthtml',$config);
                        }
                        Return false;
                    }
                    $html=$uplevelhtml.$html;
                    $config['value']=$fidvalue;
                    $config['son']=$son;
                }
                if($config['value'] && $config['son']) {
                    $html.=C('this:input:articleunlimit','sonhtml',$config);
                }
                Return $html;
            case 'sonhtml':
                $articles=C('cms:article:get',array('cid'=>$config['cid'],'pagesize'=>9999,'where'=>array($config['fidcolumn']=>$config['value'])));
                $html='<div class="layui-inline"><select lay-filter="articleunlimit_'.$config['name'].'">';
                if(count($articles)) {
                    $html.='<option value="">请选择</option>';
                    foreach($articles as $article) {
                        if(!isset($article[$config['titlecolumn']])) {
                            $article[$config['titlecolumn']]='字段不存在['.$config['titlecolumn'].']';
                        }
                        $disabledHtml='';
                        if(in_array($article['id'],$config['disabledArticles'])){$disabledHtml=' disabled';}
                        $html.='<option value="'.$article['id'].'"'.$disabledHtml.'>'.$article[$config['titlecolumn']].'</option>';
                    }
                }else {
                    Return '';
                }
                $html.='</select></div>';
                Return $html;
        }
        Return false;
    }
    function articletree($action,$config=array()) {
        if($action=='tree') {
            if(!isset($config['fidvalue'])) {$config['fidvalue']=0;}
            if(!isset($config['times'])) {$config['times']=0;}
            $treearticles=array();
            foreach($config['articles'] as $article) {
                if($article[$config['fidcolumn']]==$config['fidvalue']) {
                    $article['_ex']='|--'.str_repeat('----',$config['times']*2);
                    $treearticles[]=$article;
                    $config['times']++;
                    $oldfid=$config['fidvalue'];
                    $config['fidvalue']=$article['id'];
                    $sonarticles=C('this:input:articletree','tree',$config);
                    if(count($sonarticles)) {
                        foreach($sonarticles as $sonarticle) {
                            $treearticles[]=$sonarticle;
                        }
                    }
                    $config['times']--;
                    $config['fidvalue']=$oldfid;
                }
            }
            Return $treearticles;
        }
        if($action=='form' || $action=='view' || $action=='post') {
            if(!$channel=C('cms:channel:get',$config['cid'])){
                if($action=='post') {Return false;}
                Return '尚未配置来源栏目';
            }
            if(!$module=C('cms:module:get',$channel['modulehash'],$channel['classhash'])){
                if($action=='post') {Return false;}
                Return '配置有误';
            }
            if(empty($config['fidcolumn'])) {$config['fidcolumn']='fid';}
            if(empty($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$module['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '来源栏目文章表不存在';
            }
            if(!isset($tablefields[$config['fidcolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有分类字段:'.htmlspecialchars($config['fidcolumn']);
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $articles=C('cms:article:get',array('cid'=>$config['cid'],'pagesize'=>9999));
            $config['values']=array();
            $config['articles']=$articles;
            $articles=C('this:input:articletree','tree',$config);
            if(!count($articles)) {
                $config['values'][]='0:暂无选项:disabled';
            }
            $disabledArticles=array();
            if(isset($config['id']) && isset($config['kind']) && $config['kind']=='column' && $config['hash']==$config['fidcolumn']  && isset($config['source_cid']) && $config['source_cid']==$config['cid']  && isset($config['source_id'])){
                $disabledArticles[]=$config['source_id'];
                $config['fidvalue']=$config['source_id'];
                $sonarticles=C('this:input:articletree','tree',$config);
                foreach ($sonarticles as $sonarticle) {
                    $disabledArticles[]=$sonarticle['id'];
                }
            }
            foreach($articles as $article) {
                if($action=='view') {$article['_ex']='';}
                $article[$config['titlecolumn']]=str_replace(':','\\:',$article[$config['titlecolumn']]);
                if(in_array($article['id'],$disabledArticles)){
                    $config['values'][]=implode(':',array($article['id'],$article['_ex'].$article[$config['titlecolumn']],'disabled'));
                }else{
                    $config['values'][]=implode(':',array($article['id'],$article['_ex'].$article[$config['titlecolumn']]));
                }
            }
        }
        switch($action) {
            case 'name':
                Return '文章树形列表框';
            case 'hash':
                Return 'articletree';
            case 'group':
                Return '文章';
            case 'sql':
                Return 'bigint(11)';
            case 'form':
                $config['inputhash']='select';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='select';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='select';
                $postvalue=C('cms:input:post',$config);
                if($postvalue===false){
                    Return false;
                }
                if(!strlen($postvalue)){$postvalue=0;}
                Return $postvalue;
            case 'config':
                Return array(
                    array('configname'=>'来源栏目','hash'=>'cid','inputhash'=>'classchannel','tips'=>'选项来源栏目'),
                    array('configname'=>'上级字段','hash'=>'fidcolumn','inputhash'=>'text','tips'=>'来源栏目中文章的上级字段','defaultvalue'=>'fid'),
                    array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'来源栏目中文章的标题字段','defaultvalue'=>'title'),
                    array('configname'=>'默认文字','hash'=>'selecttitle','inputhash'=>'text','tips'=>'未选择时列表框的默认文字,不填则不显示','defaultvalue'=>'请选择'),
                    array('configname'=>'默认值','hash'=>'selectvalue','inputhash'=>'text','tips'=>'未选择时列表框的默认值','defaultvalue'=>'0'),
                    array('configname'=>'搜索','hash'=>'search','inputhash'=>'switch','tips'=>'当选项太多时,开启搜索功能可以快速找到对应的选项'),
                );
        }
        Return false;
    }
    function classSelect($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            $classes=C('cms:class:all');
            $config['selecttitle']='请选择应用';
            $config['selectvalue']='';
            $config['values']=array();
            foreach($classes as $class) {
                if(!$config['module'] || ($config['module'] && $class['module'])){
                    if(isset($config['showhash']) && $config['showhash']) {
                        $config['values'][]=implode(':',array($class['hash'],$class['classname'].'['.$class['hash'].']'));
                    }else {
                        $config['values'][]=implode(':',array($class['hash'],$class['classname']));
                    }
                }
            }
        }
        switch($action) {
            case 'name':
                Return '应用选择框';
            case 'hash':
                Return 'classselect';
            case 'group':
                Return '系统';
            case 'sql':
                if($config['multiple']) {Return 'text';}
                Return 'varchar(32)';
            case 'form':
                if($config['multiple']) {$config['inputhash']='checkbox';}else {$config['inputhash']='select';}
                Return C('cms:input:form',$config);
            case 'view':
                if($config['multiple']) {$config['inputhash']='checkbox';}else {$config['inputhash']='select';}
                Return C('cms:input:view',$config);
            case 'post':
                if($config['multiple']) {$config['inputhash']='checkbox';}else {$config['inputhash']='select';}
                Return C('cms:input:post',$config);
            case 'config':
                Return array(
                        array('configname'=>'多选','hash'=>'multiple','inputhash'=>'switch','tips'=>'允许选择多个应用,切换多选项会丢失信息,请提前确认'),
                        array('configname'=>'模型','hash'=>'module','inputhash'=>'switch','tips'=>'仅显示有模型栏目功能的应用'),
                        array('configname'=>'标识','hash'=>'showhash','inputhash'=>'switch','tips'=>'显示应用标识,选项标题中显示为:应用名[应用标识]')
                    );
        }
        Return false;
    }
    function moduleSelect($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if($config['otherclasshash']) {
                $config['classhash']=$config['otherclasshash'];
            }
            if(!isset($config['classhash'])) {Return '';}
            $modules=C('cms:module:all',$config['classhash']);
            $config['selecttitle']='请选择';
            $config['selectvalue']='';
            $config['values']=array();
            if(!count($modules)) {
                $config['values'][]=':未增加模型:disabled';
            }
            foreach($modules as $module) {
                $module['modulename']=htmlspecialchars_decode($module['modulename']);
                if(isset($config['showhash']) && $config['showhash']) {
                    $config['values'][]=implode(':',array($module['hash'],$module['modulename'].'['.$module['hash'].']'));
                }else {
                    $config['values'][]=implode(':',array($module['hash'],$module['modulename']));
                }
            }
        }
        switch($action) {
            case 'name':
                Return '模型选择框';
            case 'hash':
                Return 'moduleselect';
            case 'group':
                Return '系统';
            case 'sql':
                if($config['multiple']) {Return 'text';}
                Return 'varchar(32)';
            case 'form':
                if($config['multiple']) {$config['inputhash']='checkbox';}else {$config['inputhash']='select';}
                Return C('cms:input:form',$config);
            case 'view':
                if($config['multiple']) {$config['inputhash']='checkbox';}else {$config['inputhash']='select';}
                Return C('cms:input:view',$config);
            case 'post':
                if($config['multiple']) {$config['inputhash']='checkbox';}else {$config['inputhash']='select';}
                Return C('cms:input:post',$config);
            case 'config':
                Return array(
                            array('configname'=>'应用','hash'=>'otherclasshash','inputhash'=>'classselect','tips'=>'显示此应用下的模型','module'=>1),
                            array('configname'=>'多选','hash'=>'multiple','inputhash'=>'switch','tips'=>'允许选择多个模型,切换多选项会丢失信息,请提前确认'),
                            array('configname'=>'标识','hash'=>'showhash','inputhash'=>'switch','tips'=>'显示模型标识,选项标题中显示为:模型名[模型标识]')
                        );
        }
        Return false;
    }
    function classModule($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '应用-模型联动框';
            case 'hash':
                Return 'classmodule';
            case 'group':
                Return '系统';
            case 'sql':
                Return 'varchar(65)';
            case 'ajax':
                if($config['disabled']) {Return array('error'=>1,'message'=>'无权限');}
                $modules=C('cms:module:all',$_POST['classhash']);
                $html='<select lay-filter="'.$config['name'].'_modulechose">';
                if(count($modules)) {
                    $html.='<option value="">请选择模型</option>';
                }else {
                    $html.='<option value="">此应用下无模型</option>';
                }
                foreach($modules as $module) {
                    $html.='<option value="'.$module['hash'].'">'.$module['modulename'].'</option>';
                }
                $html.='</select>';
                Return array('error'=>0,'html'=>$html);
            case 'form':
                $config['chosemodule']='';
                if($config['value']) {
                    $classmodule=explode(':',$config['value']);
                    if(count($classmodule)==2) {
                        $config['value_class']=$classmodule[0];
                        $config['value_module']=$classmodule[1];
                    }else {
                        $config['value_class']='';
                        $config['value_module']='';
                    }
                }else {
                    $config['value_class']='';
                    $config['value_module']='';
                }
                if($config['value_class']) {
                    $modules=C('cms:module:all',$config['value_class']);
                    $config['chosemodule']='<select lay-filter="'.$config['name'].'_modulechose">';
                    if(C('cms:module:get',$config['value_module'],$config['value_class'])) {
                        $config['chosemodule'].='<option value="">请选择模型</option>';
                    }else {
                        $config['chosemodule'].='<option value="">模型['.htmlspecialchars($config['value_module']).']不存在</option>';
                    }
                    foreach($modules as $module) {
                        if($config['value_module']==$module['hash']) {
                            $config['chosemodule'].='<option value="'.$module['hash'].'" selected>'.$module['modulename'].'</option>';
                        }else {
                            $config['chosemodule'].='<option value="'.$module['hash'].'">'.$module['modulename'].'</option>';
                        }
                    }
                    $config['chosemodule'].='</select>';
                }
                $config['classinput']=array('inputhash'=>'classselect','module'=>1,'name'=>$config['name'].'_classselect','value'=>$config['value_class']);
                V('input/classmodule',$config);
                Return '';
            case 'view':
                if($config['value']) {
                    $classmodule=explode(':',$config['value']);
                    $class=C('cms:class:get',$classmodule[0]);
                    $module=C('cms:module:get',$classmodule[1],$classmodule[0]);
                    if($class && $module) {
                        Return $class['classname'].':'.$module['modulename'];
                    }else {
                        Return $config['value'];
                    }
                }
                Return '';
            case 'post':
                if(!isset($_POST[$config['name']]) || empty($_POST[$config['name']])) {
                    Return '';
                }
                $classmodule=explode(':',$_POST[$config['name']]);
                if(C('cms:class:get',$classmodule[0]) && C('cms:module:get',$classmodule[1],$classmodule[0])) {
                    Return $_POST[$config['name']];
                }
                Return false;
        }
        Return false;
    }
    function channelSelect($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(isset($config['otherclasshash']) && $config['otherclasshash']) {
                $config['classhash']=$config['otherclasshash'];
            }
            if(!isset($config['classhash'])) {Return '';}
            $channels=C('cms:channel:tree',0,$config['classhash']);
            $config['selecttitle']='请选择';
            $config['selectvalue']='';
            if(!count($channels)) {
                $config['values'][]='0:暂无栏目:disabled';
            }
            foreach($channels as $channel) {
                if($action=='view') {$channel['ex']='';}
                $config['values'][]=implode(':',array($channel['id'],$channel['ex'].$channel['channelname']));
            }
        }
        switch($action) {
            case 'name':
                Return '栏目列表框';
            case 'hash':
                Return 'channelselect';
            case 'group':
                Return '系统';
            case 'sql':
                Return 'int(9)';
            case 'form':
                $config['inputhash']='select';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='select';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='select';
                $postvalue=C('cms:input:post',$config);
                if(!strlen($postvalue)){
                    $postvalue=0;
                }
                Return $postvalue;
            case 'config':
                Return array(
                            array('configname'=>'应用','hash'=>'otherclasshash','inputhash'=>'classselect','tips'=>'显示此应用下的栏目','module'=>1)
                        );
        }
        Return false;
    }
    function channelCheckbox($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            $config['table']='channel';
            $config['idcolumn']='id';
            $config['fidcolumn']='fid';
            $config['titlecolumn']='channelname';
            if(!$config['allowdisabledchannel']){
                $config['disabledcolumn']='enabled';
            }
            if(empty($config['class'])){
                $config['values']=array_filter(explode(';',$config['value']));
                $classes=C('cms:class:all');
                $config['treearticles']=array();
                $allarticles=array();
                $classcount=0;
                foreach($classes as $class) {
                    if($class['module'] && $class['enabled']){
                        $classcount++;
                        $list_query=array();
                        $list_query['table']=$config['table'];
                        $config['articles']=all(array('table'=>'channel','order'=>'channelorder asc,id asc','where'=>array('classhash'=>$class['hash'])));
                        if(count($config['articles'])){
                            $allarticles=array_merge($allarticles,$config['articles']);
                            $thistreearticle=array('value'=>rand(1,99),'disabled'=>true,'name'=>$class['classname']);
                            $thistreearticle['children']=C('this:input:databasetreeselects','tree',$config);
                            $config['treearticles'][]=$thistreearticle;
                        }
                        
                    }
                }
                if($classcount===1 && isset($config['treearticles'][0]['children'])){
                    $config['treearticles']=$config['treearticles'][0]['children'];
                }else{
                    $config['expanded']='1';
                    $config['search']='1';
                }
                $config['articles']=$allarticles;
            }else{
                $config['where']='classhash|'.$config['class'];
            }
        }
        switch($action) {
            case 'name':
                Return '栏目多选框';
            case 'hash':
                Return 'channelcheckbox';
            case 'group':
                Return '系统';
            case 'sql':
                Return 'text';
            case 'form':
                $config['inputhash']='databasetreeselects';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='databasetreeselects';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='databasetreeselects';
                Return C('cms:input:post',$config);
            case 'config':
            Return array(
                        array('configname'=>'来源','hash'=>'class','inputhash'=>'classselect','tips'=>'栏目来源,如不选择来源,则将显示全部应用下的栏目','module'=>1),
                        array('configname'=>'停用栏目','hash'=>'allowdisabledchannel','inputhash'=>'switch','tips'=>'是否允许勾选已停用的栏目','defaultvalue'=>1),
                        array('configname'=>'单选','hash'=>'radio','inputhash'=>'switch','tips'=>'是否开启单选模式,开启单选后只能选择一条数据','defaultvalue'=>0),
                    );
        }
        Return false;
    }
    function classChannel($action,$config=array()) {
        switch($action) {
            case 'name':
                Return '应用-栏目联动框';
            case 'hash':
                Return 'classchannel';
            case 'group':
                Return '系统';
            case 'sql':
                Return 'int(9)';
            case 'ajax':
                if($config['disabled']) {Return array('error'=>1,'message'=>'无权限');}
                $channels=C('cms:channel:tree',0,$_POST['classhash']);
                $html='<select lay-filter="'.$config['name'].'_channelchose">';
                if(count($channels)) {
                    $html.='<option value="">请选择栏目</option>';
                }else {
                    $html.='<option value="">此应用下无栏目</option>';
                }
                foreach($channels as $channel) {
                    $html.='<option value="'.$channel['id'].'">'.$channel['ex'].''.$channel['channelname'].'</option>';
                }
                $html.='</select>';
                Return array('error'=>0,'html'=>$html);
            case 'form':
                $config['chosechannel']='';
                if(!isset($config['classhash'])) {$config['classhash']='';}
                if(isset($config['defaultclasshash']) && $config['defaultclasshash']) {$config['classhash']=$config['defaultclasshash'];}
                if($config['value']) {
                    if($channel=C('cms:channel:get',$config['value'])) {
                        $config['classhash']=$channel['classhash'];
                    }else {
                        $config['chosechannel']='选择的栏目已不存在';
                        $config['classhash']='';
                        $config['value']='';
                    }
                }
                if($config['value'] || $config['classhash']) {
                    $channels=C('cms:channel:tree',0,$config['classhash']);
                    $config['chosechannel'].='<select lay-filter="'.$config['name'].'_channelchose">';
                    $config['chosechannel'].='<option value="">请选择栏目</option>';
                    foreach($channels as $channel) {
                        if($config['value']==$channel['id']) {
                            $config['chosechannel'].='<option value="'.$channel['id'].'" selected>'.$channel['ex'].''.$channel['channelname'].'</option>';
                        }else {
                            $config['chosechannel'].='<option value="'.$channel['id'].'">'.$channel['ex'].''.$channel['channelname'].'</option>';
                        }
                    }
                    $config['chosechannel'].='</select>';
                }
                $config['classinput']=array('inputhash'=>'classselect','module'=>1,'name'=>$config['name'].'_classselect','value'=>$config['classhash']);
                V('input/classchannel',$config);
                Return '';
            case 'view':
                if($config['value']) {
                    if($channel=C('cms:channel:get',$config['value'])) {
                        Return $channel['channelname'];
                    }else {
                        Return '栏目不存在';
                    }
                }
                Return '';
            case 'post':
                if(empty($_POST[$config['name']])) {
                    Return 0;
                }
                if(!$channel=C('cms:channel:get',intval(@$_POST[$config['name']]))) {
                    Return false;
                }
                Return $channel['id'];
            case 'config':
                Return array(
                            array('configname'=>'默认应用','module'=>1,'hash'=>'defaultclasshash','inputhash'=>'classselect','tips'=>'默认显示此应用下的栏目')
                        );
        }
        Return false;
    }
    function inputSelect($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            $inputs=C('cms:input:tree');
            $config['selecttitle']='请选择';
            $config['selectvalue']='';
            $config['values']=array();
            foreach($inputs as $input) {
                $thisrole=array();
                if(isset($input['hash'])) {
                    $thisrole=array($input['hash'],$input['inputname']);
                }else {
                    $thisrole=array($input['inputname'],$input['inputname'].'============','disabled');
                }
                $config['values'][]=implode(':',$thisrole);
            }
        }
        switch($action) {
            case 'name':
                Return '表单列表框';
            case 'hash':
                Return 'inputselect';
            case 'group':
                Return '系统';
            case 'sql':
                Return 'varchar(32)';
            case 'form':
                $config['inputhash']='select';
                $config['search']=1;
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='select';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='select';
                Return C('cms:input:post',$config);
        }
        Return false;
    }
    function user($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post' || $action=='ajax') {
            $config['table']='user';
            $config['idcolumn']='id';
            $config['idtype']=1;
            $config['titlecolumn']='hash';
            if($config['showname']){
                $config['titlecolumns']='username;'.$config['titlecolumns'];
            }
            if($config['showhash']){
                $config['titlecolumns']='hash;'.$config['titlecolumns'];
            }
            $config['titlecolumns']=rtrim($config['titlecolumns'],';');
            if(isset($config['roles']) && $config['roles']){
                $roles=array_filter(explode(';',$config['roles']));
                foreach ($roles as $role) {
                    $config['where']['520;'][]=array('rolehash'=>$role);
                    $config['where']['520;'][]=array('rolehash%'=>$role.';');
                    $config['where']['520;'][]=array('rolehash%'=>';'.$role.';');
                    $config['where']['520;'][]=array('rolehash%'=>';'.$role);
                }
            }
        }
        switch($action) {
            case 'name':
                Return '用户选择';
            case 'hash':
                Return 'user';
            case 'group':
                Return '用户';
            case 'sql':
                if($config['multiple']) {Return 'text';}
                Return 'int(9)';
            case 'form':
                $config['inputhash']='database';
                Return C('cms:input:form',$config);
            case 'ajax':
                $config['inputhash']='database';
                Return C('cms:input:ajax',$config);
            case 'view':
                $config['inputhash']='database';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='database';
                $postvalue=C('cms:input:post',$config);
                if(!strlen($postvalue) && !$config['multiple']){
                    $postvalue=0;
                }
                if(isset($config['nonull']) && $config['nonull'] && !$postvalue) {return false;}
                Return $postvalue;
            case 'config':
                $infos=C('cms:form:all','info');
                $infos_values=array();
                foreach($infos as $info) {
                    $infos_values[]=$info['hash'].':'.$info['formname'];
                }
                Return array(
                        array('configname'=>'多选','hash'=>'multiple','inputhash'=>'switch','tips'=>'切换多选会丢失数据,请提前确认'),
                        array('configname'=>'角色','hash'=>'roles','inputhash'=>'rolecheckbox','tips'=>'需要显示的账号角色类型','defaultvalue'=>''),
                        array('configname'=>'数量','hash'=>'pagesize','inputhash'=>'number','tips'=>'选择页显示的用户数量','defaultvalue'=>'10'),
                        array('configname'=>'账号','hash'=>'showhash','inputhash'=>'switch','tips'=>'显示账号','defaultvalue'=>1),
                        array('configname'=>'昵称','hash'=>'showname','inputhash'=>'switch','tips'=>'显示昵称','defaultvalue'=>1),
                        array('configname'=>'属性','hash'=>'titlecolumns','inputhash'=>'checkbox','tips'=>'在选择页显示的其它用户属性,如邮箱,qq等信息','defaultvalue'=>'','values'=>$infos_values),
                    );
        }
        Return false;
    }
    function userSelect($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(!isset($config['showtype'])) {$config['showtype']=1;}
            if(!isset($config['showdisabled'])) {$config['showdisabled']=0;}
            $config['selecttitle']='请选择';
            $config['selectvalue']='';
            $config['values']=array();
            $user_query=array();
            $user_query['table']='user';
            $user_query['column']='id,username,hash,enabled';
            if(!$config['showdisabled']) {$user_query['where']=array('enabled'=>1);}
            $users=all($user_query);
            foreach($users as $user) {
                $thisuser=array();
                $thisuser[0]=$user['id'];
                if($config['showtype']==2) {
                    $thisuser[1]=$user['hash'];
                }elseif($config['showtype']==3) {
                    $thisuser[1]=$user['username'].'['.$user['hash'].']';
                }else {
                    $thisuser[1]=$user['username'];
                }
                if($config['showdisabled'] && !$user['enabled']) {$thisuser[1].='[禁用]';}
                $config['values'][]=implode(':',$thisuser);
            }
        }
        switch($action) {
            case 'name':
                Return '用户列表框';
            case 'hash':
                Return 'userselect';
            case 'group':
                Return '用户';
            case 'sql':
                Return 'int(9)';
            case 'form':
                $config['inputhash']='select';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='select';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='select';
                $postvalue=C('cms:input:post',$config);
                if(!strlen($postvalue)){
                    $postvalue=0;
                }
                Return $postvalue;
            case 'config':
                Return array(
                            array('configname'=>'显示格式','hash'=>'showtype','inputhash'=>'radio','tips'=>'','defaultvalue'=>'1','values'=>"1:昵称\n2:账号\n3:昵称+账号",'savetype'=>1),
                            array('configname'=>'显示禁用','hash'=>'showdisabled','inputhash'=>'switch','tips'=>'显示禁用的用户'),
                        );
        }
        Return false;
    }
    function userCheckbox($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            if(!isset($config['showtype'])) {$config['showtype']=1;}
            if(!isset($config['showdisabled'])) {$config['showdisabled']=0;}
            $config['selecttitle']='请选择';
            $config['selectvalue']='';
            $config['values']=array();
            $user_query=array();
            $user_query['table']='user';
            $user_query['column']='id,username,hash,enabled';
            if(!$config['showdisabled']) {$user_query['where']=array('enabled'=>1);}
            $users=all($user_query);
            foreach($users as $user) {
                $thisuser=array();
                $thisuser[0]=$user['id'];
                if($config['showtype']==2) {
                    $thisuser[1]=$user['hash'];
                }elseif($config['showtype']==3) {
                    $thisuser[1]=$user['username'].'['.$user['hash'].']';
                }else {
                    $thisuser[1]=$user['username'];
                }
                if($config['showdisabled'] && !$user['enabled']) {$thisuser[1].='[禁用]';}
                $config['values'][]=implode(':',$thisuser);
            }
        }
        switch($action) {
            case 'name':
                Return '用户多选框';
            case 'hash':
                Return 'usercheckbox';
            case 'group':
                Return '用户';
            case 'sql':
                Return 'text';
            case 'form':
                $config['inputhash']='checkbox';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='checkbox';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='checkbox';
                Return C('cms:input:post',$config);
            case 'config':
                Return array(
                            array('configname'=>'显示格式','hash'=>'showtype','inputhash'=>'radio','tips'=>'','defaultvalue'=>'1','values'=>"1:昵称\n2:账号\n3:昵称+账号",'savetype'=>1),
                            array('configname'=>'显示禁用','hash'=>'showdisabled','inputhash'=>'switch','tips'=>'显示禁用的用户'),
                            array('configname'=>'最少勾选','hash'=>'mincheck','inputhash'=>'number','tips'=>'最少必须勾选几项','placeholder'=>'如:1,则此表单必须勾选1项'),
                            array('configname'=>'最多勾选','hash'=>'maxcheck','inputhash'=>'number','tips'=>'最多勾选几项','placeholder'=>'如:5,则此表单最多勾选5项'),
                        );
        }
        Return false;
    }
    function roleSelect($action,$config=array()) {
        $config['savetype']=1;
        if($action=='form' || $action=='view' || $action=='post') {
            if(isset($config['rolehash']) && !empty($config['rolehash'])) {
                $roles=array();
                $rolehash_array=explode(';',$config['rolehash']);
                foreach($rolehash_array as $rolehash) {
                    $role=C('cms:user:roleGet',$rolehash);
                    if($role) {
                        $roles[]=$role;
                    }
                }
            }else {
                $roles=C('cms:user:roleAll');
            }
            $config['selecttitle']='请选择';
            $config['selectvalue']='';
            $config['values']=array();
            foreach($roles as $role) {
                $thisrole=array();
                $thisrole[0]=$role['hash'];
                if(!$role['enabled']) {
                    $role['rolename']=$role['rolename'].' [已禁用]';
                }
                $thisrole[1]=$role['rolename'];
                if(!isset($config['showdisabled'])) {$config['showdisabled']=0;}
                if(!isset($config['allowdisabled'])) {$config['allowdisabled']=0;}
                if(!$role['enabled'] && !$config['showdisabled']) {
                    unset($thisrole);
                }
                if(isset($thisrole)) {
                    $config['values'][]=implode(':',$thisrole);
                }
            }
        }
        switch($action) {
            case 'name':
                Return '角色列表框';
            case 'hash':
                Return 'roleselect';
            case 'group':
                Return '用户';
            case 'sql':
                Return 'varchar(32)';
            case 'form':
                $config['inputhash']='select';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='select';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='select';
                Return C('cms:input:post',$config);
            case 'config':
                Return array(
                            array('configname'=>'显示禁用','hash'=>'showdisabled','inputhash'=>'switch','tips'=>'显示禁用的角色'),
                        );
        }
        Return false;
    }
    function roleCheckbox($action,$config=array()) {
        $config['savetype']=1;
        if($action=='form' || $action=='view' || $action=='post') {
            if(isset($config['rolehash']) && !empty($config['rolehash'])) {
                $roles=array();
                $rolehash_array=explode(';',$config['rolehash']);
                foreach($rolehash_array as $rolehash) {
                    $role=C('cms:user:roleGet',$rolehash);
                    if($role) {
                        $roles[]=$role;
                    }
                }
            }else {
                $roles=C('cms:user:roleAll');
            }
            $config['values']=array();
            foreach($roles as $role) {
                $thisrole=array();
                $thisrole[0]=$role['hash'];
                if(!$role['enabled']) {
                    $role['rolename']=$role['rolename'].' [已禁用]';
                }
                $thisrole[1]=$role['rolename'];
                if(!isset($config['showdisabled'])) {$config['showdisabled']=0;}
                if(!isset($config['allowdisabled'])) {$config['allowdisabled']=0;}
                if(!$role['enabled'] && !$config['showdisabled']) {
                    unset($thisrole);
                }
                if(isset($thisrole)) {
                    $config['values'][]=implode(':',$thisrole);
                }
            }
        }
        switch($action) {
            case 'name':
                Return '角色多选框';
            case 'hash':
                Return 'rolecheckbox';
            case 'group':
                Return '用户';
            case 'sql':
                Return 'varchar(255)';
            case 'form':
                $config['inputhash']='checkbox';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='checkbox';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='checkbox';
                Return C('cms:input:post',$config);
            case 'config':
                Return array(
                            array('configname'=>'显示禁用','hash'=>'showdisabled','inputhash'=>'switch','tips'=>'显示禁用的角色'),
                        );
        }
        Return false;
    }
    function database($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post' || $action=='ajax') {
            if(!isset($config['idcolumn']) || empty($config['idcolumn'])) {$config['idcolumn']='id';}
            if(!isset($config['titlecolumns']) || empty($config['titlecolumns'])) {$config['titlecolumns']='title';}
            $config['titlecolumns']=array_filter(explode(';',$config['titlecolumns']));
            if(is_array($config['where'])){
                $sqlwhere=$config['where'];
            }else{
                $wheres=array_filter(explode(';',$config['where']));
                $sqlwhere=array();
                foreach ($wheres as $where) {
                    $thiswhere=explode('|',$where);
                    $sqlwhere[$thiswhere[0]]=$thiswhere[1];
                }
            }
        }
        switch($action) {
            case 'name':
                Return '数据选择';
            case 'hash':
                Return 'database';
            case 'group':
                Return '数据库';
            case 'sql':
                if($config['multiple']) {Return 'text';}
                if($config['idtype']==1) {Return 'bigint(11)';}
                Return 'varchar(255)';
            case 'ajax':
                if($config['disabled']) {Return array('error'=>1,'message'=>'无权限');}
                if(isset($_POST['ajaxdo']) && $_POST['ajaxdo']=='showvalue') {
                    if(empty($config['table'])) {
                        Return array('error'=>0,'html'=>'<tr><td>尚未配置</td></tr>');
                    }
                    $html='';
                    $values=explode(';',trim($_POST['value'],';'));
                    $articles=array();
                    foreach($values as $val) {
                        $article_query=array();
                        $article_query['table']=$config['table'];
                        if(!empty($config['order'])) {$article_query['order']=$config['order'];}
                        if(!empty($val)) {
                            if(count($sqlwhere)){
                                $article_query['where']=$sqlwhere;
                            }
                            $article_query['where'][$config['idcolumn']]=$val;
                            if($article=one($article_query)) {
                                $article['id_va1ue_classcms']=str_replace(';','\\;',$article[$config['idcolumn']]);
                                $articles[]=$article;
                            }
                        }
                    }
                    foreach($articles as $article) {
                        $html.='<tr data-id="'.$article['id_va1ue_classcms'].'">';
                        foreach($config['titlecolumns'] as $key=>$thistitle) {
                            $html.='<td>';
                            if(!$key && $config['multiple']) {
                                $html.='<i class="layui-icon layui-icon-find-fill sortable-color"></i> ';
                            }
                            $html.=$article[$thistitle];
                            $html.='</td>';
                        }
                        $html.='<td style="width:20px;text-align:right"><i class="layui-icon close">&#x1006;</i></td></tr>';
                    }
                    if(!count($articles)) {
                        $html.='<tr><td colspan=2>未选</td></tr>';
                    }
                    Return array('error'=>0,'html'=>$html);
                }
                if(isset($_POST['ajaxdo']) && $_POST['ajaxdo']=='articlelist') {
                    if(empty($config['table'])) {
                        Return array('error'=>0,'pagecount'=>0,'html'=>'<tr><td>尚未配置</td></tr>');
                    }
                    $html='';
                    $article_query=array();
                    $article_query['table']=$config['table'];
                    if(count($sqlwhere)){
                        $article_query['where']=$sqlwhere;
                    }
                    if(!empty($config['order'])) {$article_query['order']=$config['order'];}
                    if(isset($_POST['keyword']) && !empty($_POST['keyword'])) {
                        $keywordwhere=array();
                        foreach($config['titlecolumns'] as $thistitle) {
                            $keywordwhere[$thistitle.'%']=$_POST['keyword'];
                        }
                        if(count($keywordwhere)) {
                            $article_query['where']['1;']=$keywordwhere;
                        }
                    }
                    $article_query['page']=page('pagesize',$config['pagesize'],'page',intval($_POST['page']));
                    $articles=all($article_query);
                    $pagecount=0;
                    if(count($articles)==0) {
                        $html.='<tr><td colspan='.(count($config['titlecolumns'])+1).'>没有文章</td></tr>';
                    }else {
                        pagelist();
                        $pageinfo=pageinfo();
                        if(isset($pageinfo['pagecount'])) {
                            $pagecount=$pageinfo['pagecount'];
                        }
                        foreach($config['titlecolumns'] as $thistitle) {
                            if(!isset($articles[0][$thistitle])) {
                                $html.='<tr><td colspan='.(count($config['titlecolumns'])+1).'>[无字段:'.htmlspecialchars($thistitle).']</td></tr>';
                                $articles=array();
                            }
                        }
                        if($config['multiple']){
                            $_POST['value']=str_replace('\\;','---colon---',$_POST['value']);
                        }
                        $values=explode(';',$_POST['value']);
                        if($config['multiple']){
                            foreach($values as $key=>$thisval) {
                                $values[$key]=str_replace('---colon---','\\;',$thisval);
                            }
                        }
                    }
                    foreach($articles as $article) {
                        $article['id_va1ue_classcms']=str_replace(';','\\;',$article[$config['idcolumn']]);
                        if($config['multiple']) {
                            $html.='<tr>';
                            foreach($config['titlecolumns'] as $key=>$thistitle) {
                                if(!$key) {
                                    $html.='<td colspan=2><input type="checkbox"';
                                    if(in_array($article[$config['idcolumn']],$values)) {
                                        $html.=' checked';
                                    }
                                    $html.=' lay-filter="'.$_POST['name'].'_article"  title='.$article[$thistitle].' data-id="'.$article['id_va1ue_classcms'].'" value="" lay-skin="primary"  name="'.$_POST['name'].'-c1asscms"></td>';
                                }else {
                                    $html.='<td>'.$article[$thistitle].'</td>';
                                }
                            }
                            $html.='</tr>';
                        }else {
                            $html.='<tr>';
                            foreach($config['titlecolumns'] as $key=>$thistitle) {
                                if(!$key) {
                                    $html.='<td colspan=2><input type="radio"';
                                    if(in_array($article[$config['idcolumn']],$values)) {
                                        $html.=' checked';
                                    }
                                    $html.=' lay-filter="'.$_POST['name'].'_article"  title='.$article[$thistitle].' data-id="'.$article['id_va1ue_classcms'].'" value="" lay-skin="primary"  name="'.$_POST['name'].'-c1asscms"></td>';
                                }else {
                                    $html.='<td>'.$article[$thistitle].'</td>';
                                }
                            }
                            $html.='</tr>';
                        }
                    }
                    Return array('error'=>0,'pagecount'=>$pagecount,'html'=>$html);
                }
                Return '';
            case 'form':
                if(empty($config['table']) || empty($config['idcolumn'])) {
                    Return '尚未配置';
                }
                if(!count(C($GLOBALS['C']['DbClass'].':getfields',$config['table']))) {
                    Return '数据表不存在';
                }
                if($config['multiple']) {
                    $config['value']=str_replace('\\;','---colon---',$config['value']);
                    $values=explode(';',trim($config['value'],';'));
                }else {
                    $values=array($config['value']);
                }
                $config['articles']=array();
                foreach($values as $key=>$val) {
                    if(!empty($val) && (!$key || $config['multiple'])) {
                        $list_query=array();
                        $list_query['table']=$config['table'];
                        if(!empty($config['order'])) {$list_query['order']=$config['order'];}
                        $list_query['where']=where($config['idcolumn'],str_replace('---colon---',';',$val));
                        if($article=one($list_query)) {
                            $article['id_va1ue_classcms']=str_replace(';','\\;',$article[$config['idcolumn']]);
                            foreach($config['titlecolumns'] as $thistitle) {
                                if(!isset($article[$thistitle])) {
                                    $article[$thistitle]='[无字段 '.htmlspecialchars($thistitle).']';
                                }
                            }
                            $config['articles'][]=$article;
                        }
                    }
                }
                V('input/database',$config);
                Return '';
            case 'view':
                $config['value']=str_replace('\\;','---colon---',$config['value']);
                $values=explode(';',trim($config['value'],';'));
                $titles=array();
                foreach($values as $key=>$val) {
                    if(!empty($val) && (!$key || $config['multiple'])) {
                        $list_query=array();
                        $list_query['table']=$config['table'];
                        if(!empty($config['order'])) {$list_query['order']=$config['order'];}
                        $list_query['where']=where($config['idcolumn'],str_replace('---colon---',';',$val));
                        if($article=one($list_query)) {
                            if(!isset($article[$config['titlecolumn']])) {
                                $article[$config['titlecolumn']]='[标题字段不存在]';
                            }
                            $titles[]=$article[$config['titlecolumn']];
                        }else {
                            $titles[]='[文章不存在]';
                        }
                    }
                }
                echo(implode(';',$titles));
                Return '';
            case 'post':
                if(!isset($_POST[$config['name']])) {
                    Return '';
                }
                $_POST[$config['name']]=str_replace('\\;','---colon---',$_POST[$config['name']]);
                $values=explode(';',trim($_POST[$config['name']],';'));
                $articles=array();
                foreach($values as $val) {
                    if(!empty($val)) {
                        $val=str_replace('---colon---',';',$val);
                        $list_query=array();
                        $list_query['table']=$config['table'];
                        if(!empty($config['order'])) {$list_query['order']=$config['order'];}
                        if(count($sqlwhere)){
                            $article_query['where']=$sqlwhere;
                        }
                        $list_query['where'][$config['idcolumn']]=$val;
                        if($article=one($list_query)) {
                            $articles[]=$article;
                        }else {
                            Return array('error'=>'不存在的数据');
                        }
                    }
                }
                $values=array();
                foreach($articles as $article) {
                    if($config['multiple']) {
                        $values[]=str_replace(';','\\;',$article[$config['idcolumn']]);
                    }elseif(!count($values)) {
                        $values[]=$article[$config['idcolumn']];
                    }
                }
                if(isset($config['nonull']) && $config['nonull'] && !count($values)) {
                    Return false;
                }
                if(empty($config['mincheck'])) {$config['mincheck']=0;}
                if(empty($config['maxcheck'])) {$config['maxcheck']=9999;}
                if($config['multiple'] && count($values)<$config['mincheck']) {
                    Return array('error'=>'至少选择'.$config['mincheck'].'项');
                }
                if($config['multiple'] && count($values)>$config['maxcheck']) {
                    Return array('error'=>'最多选择'.$config['maxcheck'].'项');
                }
                if(!$config['multiple'] && isset($values[0])) {
                    if($config['idtype']==1 && !is_numeric($values[0])) {
                        Return array('error'=>'数据类型有误,请重新配置');
                    }
                    Return $values[0];
                }elseif($config['multiple']) {
                    Return implode(';',$values);
                }
                if($config['idtype']==1 && !$config['multiple']){
                    Return 0;
                }
                Return '';
            case 'config':
                Return array(
                        array('configname'=>'表名','hash'=>'table','inputhash'=>'text','tips'=>'选项来源的数据库表名,系统会自动加表名前缀.如不需要加前缀,则使用no_perfix_表名','nonull'=>1),
                        array('configname'=>'数据字段','hash'=>'idcolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段,修改数据字段会丢失数据,请提前确认好字段','defaultvalue'=>'id','nonull'=>1),
                        array('configname'=>'数据类型','hash'=>'idtype','inputhash'=>'radio','tips'=>'数据字段在数据库中的类型.切换类型会丢失信息,请提前确认好保存类型.','defaultvalue'=>'1','values'=>"1:数字\n2:文字",'savetype'=>1),
                        array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'对应数据所显示的标题,请确保数据库表中拥有此字段','defaultvalue'=>'title','nonull'=>1),
                        array('configname'=>'显示字段','hash'=>'titlecolumns','inputhash'=>'tags','tips'=>'数据选择页显示的字段,请确保数据库表中拥有这些字段','defaultvalue'=>'title','min'=>1),
                        array('configname'=>'条件','hash'=>'where','inputhash'=>'tags','tips'=>'数据查询条件','defaultvalue'=>'','column'=>2,'columntips'=>'字段,如:status status> status%;值:如 1'),
                        array('configname'=>'排序','hash'=>'order','inputhash'=>'text','tips'=>'如:id asc','defaultvalue'=>''),
                        array('configname'=>'多选','hash'=>'multiple','inputhash'=>'switch','tips'=>'更改单选多选会丢失数据,请提前确认保存类型'),
                        array('configname'=>'显示数量','hash'=>'pagesize','inputhash'=>'number','tips'=>'每页显示的数据数量','defaultvalue'=>'10'),
                        array('configname'=>'最少勾选','hash'=>'mincheck','inputhash'=>'number','tips'=>'最少必须勾选几项,仅在开启多选后有效.','placeholder'=>'如:1,则此表单必须勾选1项'),
                        array('configname'=>'最多勾选','hash'=>'maxcheck','inputhash'=>'number','tips'=>'最多勾选几项,仅在开启多选后有效.','placeholder'=>'如:5,则此表单最多勾选5项'),
                    );
        }
        Return false;
    }
    function databaseradio($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            $config['savetype']=1;
            if(empty($config['table'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            if(empty($config['idcolumn'])) {$config['idcolumn']='id';}
            if(empty($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$config['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '数据表不存在';
            }
            if(!isset($tablefields[$config['idcolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有数据字段:'.htmlspecialchars($config['idcolumn']);
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $wheres=array_filter(explode(';',$config['where']));
            $sqlwhere=array();
            foreach ($wheres as $where) {
                $thiswhere=explode('|',$where);
                $sqlwhere[$thiswhere[0]]=$thiswhere[1];
            }
            $list_query=array();
            $list_query['table']=$config['table'];
            if(count($sqlwhere)){
                $list_query['where']=$sqlwhere;
            }
            if(!empty($config['order'])) {$list_query['order']=$config['order'];}
            $articles=all($list_query);
            $config['values']=array();
            foreach($articles as $article) {
                if(!is_numeric($article[$config['idcolumn']]) && $config['idtype']==1) {
                    if($action=='post') {
                        Return false;
                    }else {
                        Return '数据字段 '.htmlspecialchars($config['titlecolumn']).' 类型为文字,请修改配置';
                    }
                }
                $article[$config['idcolumn']]=str_replace(':','\\:',$article[$config['idcolumn']]);
                $article[$config['titlecolumn']]=str_replace(':','\\:',$article[$config['titlecolumn']]);
                $config['values'][]=implode(':',array($article[$config['idcolumn']],$article[$config['titlecolumn']]));
            }
        }
        switch($action) {
            case 'name':
                Return '数据单选框';
            case 'hash':
                Return 'databaseradio';
            case 'group':
                Return '数据库';
            case 'sql':
                if($config['idtype']==1) {Return 'bigint(11)';}
                Return 'varchar(255)';
            case 'form':
                if(!count(C($GLOBALS['C']['DbClass'].':getfields',$config['table']))) {
                    Return '数据表不存在';
                }
                $config['inputhash']='radio';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='radio';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='radio';
                $postvalue=C('cms:input:post',$config);
                if(!strlen($postvalue) && $config['idtype']==1){
                    $postvalue=0;
                }
                Return $postvalue;
            case 'config':
                Return array(
                        array('configname'=>'表名','hash'=>'table','inputhash'=>'text','tips'=>'选项来源的数据库表名,系统会自动加表名前缀.如不需要加前缀,则使用no_perfix_表名'),
                        array('configname'=>'数据字段','hash'=>'idcolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段,修改数据字段会丢失数据,请提前确认好字段','defaultvalue'=>'id'),
                        array('configname'=>'数据类型','hash'=>'idtype','inputhash'=>'radio','tips'=>'数据字段在数据库中的类型.切换类型会丢失信息,请提前确认好保存类型.','defaultvalue'=>'1','values'=>"1:数字\n2:文字",'savetype'=>1),
                        array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段','defaultvalue'=>'title'),
                        array('configname'=>'条件','hash'=>'where','inputhash'=>'tags','tips'=>'数据查询条件','defaultvalue'=>'','column'=>2,'columntips'=>'字段,如:status status> status%;值:如 1'),
                        array('configname'=>'排序','hash'=>'order','inputhash'=>'text','tips'=>'如:id asc','defaultvalue'=>''),
                    );
        }
        Return false;
    }
    function databasecheckbox($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            $config['savetype']=1;
            if(empty($config['table'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            if(empty($config['idcolumn'])) {$config['idcolumn']='id';}
            if(empty($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$config['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '数据表不存在';
            }
            if(!isset($tablefields[$config['idcolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有数据字段:'.htmlspecialchars($config['idcolumn']);
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $wheres=array_filter(explode(';',$config['where']));
            $sqlwhere=array();
            foreach ($wheres as $where) {
                $thiswhere=explode('|',$where);
                $sqlwhere[$thiswhere[0]]=$thiswhere[1];
            }
            $list_query=array();
            $list_query['table']=$config['table'];
            if(count($sqlwhere)){
                $list_query['where']=$sqlwhere;
            }
            if(!empty($config['order'])) {$list_query['order']=$config['order'];}
            $articles=all($list_query);
            $config['values']=array();
            foreach($articles as $article) {
                $article[$config['idcolumn']]=str_replace(':','\\:',$article[$config['idcolumn']]);
                $article[$config['titlecolumn']]=str_replace(':','\\:',$article[$config['titlecolumn']]);
                $config['values'][]=implode(':',array($article[$config['idcolumn']],$article[$config['titlecolumn']]));
            }
        }
        switch($action) {
            case 'name':
                Return '数据多选框';
            case 'hash':
                Return 'databasecheckbox';
            case 'group':
                Return '数据库';
            case 'sql':
                Return 'text';
            case 'form':
                if(!count(C($GLOBALS['C']['DbClass'].':getfields',$config['table']))) {
                    Return '数据表不存在';
                }
                $config['inputhash']='checkbox';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='checkbox';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='checkbox';
                Return C('cms:input:post',$config);
            case 'config':
                Return array(
                    array('configname'=>'表名','hash'=>'table','inputhash'=>'text','tips'=>'选项来源的数据库表名,系统会自动加表名前缀.如不需要加前缀,则使用no_perfix_表名'),
                    array('configname'=>'数据字段','hash'=>'idcolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段,修改数据字段会丢失数据,请提前确认好字段','defaultvalue'=>'id'),
                    array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段','defaultvalue'=>'title'),
                    array('configname'=>'条件','hash'=>'where','inputhash'=>'tags','tips'=>'数据查询条件','defaultvalue'=>'','column'=>2,'columntips'=>'字段,如:status status> status%;值:如 1'),
                    array('configname'=>'排序','hash'=>'order','inputhash'=>'text','tips'=>'如:id asc','defaultvalue'=>''),
                    array('configname'=>'样式','hash'=>'style','inputhash'=>'radio','tips'=>'','defaultvalue'=>'1','values'=>"1:原始风格\n2:按钮风格",'savetype'=>1),
                    array('configname'=>'最少勾选','hash'=>'mincheck','inputhash'=>'number','tips'=>'最少必须勾选几项','placeholder'=>'如:1,则此表单必须勾选1项'),
                    array('configname'=>'最多勾选','hash'=>'maxcheck','inputhash'=>'number','tips'=>'最多勾选几项','placeholder'=>'如:5,则此表单最多勾选5项'),
                );
        }
        Return false;
    }
    function databaseselect($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            $config['savetype']=1;
            if(empty($config['table'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            if(empty($config['idcolumn'])) {$config['idcolumn']='id';}
            if(empty($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$config['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '数据表不存在';
            }
            if(!isset($tablefields[$config['idcolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有数据字段:'.htmlspecialchars($config['idcolumn']);
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $wheres=array_filter(explode(';',$config['where']));
            $sqlwhere=array();
            foreach ($wheres as $where) {
                $thiswhere=explode('|',$where);
                $sqlwhere[$thiswhere[0]]=$thiswhere[1];
            }
            $list_query=array();
            $list_query['table']=$config['table'];
            if(count($sqlwhere)){
                $list_query['where']=$sqlwhere;
            }
            if(!empty($config['order'])) {$list_query['order']=$config['order'];}
            $articles=all($list_query);
            $config['values']=array();
            foreach($articles as $article) {
                if(!is_numeric($article[$config['idcolumn']]) && $config['idtype']==1) {
                    if($action=='post') {
                        Return false;
                    }else {
                        Return '数据字段 '.htmlspecialchars($config['titlecolumn']).' 类型为文字,请修改配置';
                    }
                }
                $article[$config['idcolumn']]=str_replace(':','\\:',$article[$config['idcolumn']]);
                $article[$config['titlecolumn']]=str_replace(':','\\:',$article[$config['titlecolumn']]);
                $config['values'][]=implode(':',array($article[$config['idcolumn']],$article[$config['titlecolumn']]));
            }
        }
        switch($action) {
            case 'name':
                Return '数据列表框';
            case 'hash':
                Return 'databaseselect';
            case 'group':
                Return '数据库';
            case 'sql':
                if($config['idtype']==1) {Return 'bigint(11)';}
                Return 'varchar(255)';
            case 'form':
                if(!count(C($GLOBALS['C']['DbClass'].':getfields',$config['table']))) {
                    Return '数据表不存在';
                }
                $config['inputhash']='select';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='select';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='select';
                $postvalue=C('cms:input:post',$config);
                if(!strlen($postvalue) && $config['idtype']==1){
                    $postvalue=0;
                }
                Return $postvalue;
            case 'config':
                Return array(
                    array('configname'=>'表名','hash'=>'table','inputhash'=>'text','tips'=>'选项来源的数据库表名,系统会自动加表名前缀.如不需要加前缀,则使用no_perfix_表名'),
                    array('configname'=>'数据字段','hash'=>'idcolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段,修改数据字段会丢失数据,请提前确认好字段','defaultvalue'=>'id'),
                    array('configname'=>'数据类型','hash'=>'idtype','inputhash'=>'radio','tips'=>'数据字段在数据库中的类型.切换类型会丢失信息,请提前确认好保存类型.','defaultvalue'=>'1','values'=>"1:数字\n2:文字",'savetype'=>1),
                    array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段','defaultvalue'=>'title'),
                    array('configname'=>'条件','hash'=>'where','inputhash'=>'tags','tips'=>'数据查询条件','defaultvalue'=>'','column'=>2,'columntips'=>'字段,如:status status> status%;值:如 1'),
                    array('configname'=>'排序','hash'=>'order','inputhash'=>'text','tips'=>'如:id asc','defaultvalue'=>''),
                    array('configname'=>'默认文字','hash'=>'selecttitle','inputhash'=>'text','tips'=>'未选择时列表框的默认文字,不填则不显示','defaultvalue'=>'请选择'),
                    array('configname'=>'默认值','hash'=>'selectvalue','inputhash'=>'text','tips'=>'未选择时列表框的默认值','defaultvalue'=>'0'),
                    array('configname'=>'搜索','hash'=>'search','inputhash'=>'switch','tips'=>'当选项太多时,开启搜索功能可以快速找到对应的选项'),
                );
        }
        Return false;
    }
    function databasetransfer($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post') {
            $config['savetype']=1;
            if(empty($config['table'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            if(empty($config['idcolumn'])) {$config['idcolumn']='id';}
            if(empty($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$config['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '数据表不存在';
            }
            if(!isset($tablefields[$config['idcolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有数据字段:'.htmlspecialchars($config['idcolumn']);
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $wheres=array_filter(explode(';',$config['where']));
            $sqlwhere=array();
            foreach ($wheres as $where) {
                $thiswhere=explode('|',$where);
                $sqlwhere[$thiswhere[0]]=$thiswhere[1];
            }
            $list_query=array();
            $list_query['table']=$config['table'];
            if(count($sqlwhere)){
                $list_query['where']=$sqlwhere;
            }
            if(!empty($config['order'])) {$list_query['order']=$config['order'];}
            $articles=all($list_query);
            $config['values']=array();
            foreach($articles as $article) {
                $article[$config['idcolumn']]=str_replace(':','\\:',$article[$config['idcolumn']]);
                $article[$config['titlecolumn']]=str_replace(':','\\:',$article[$config['titlecolumn']]);
                $config['values'][]=implode(':',array($article[$config['idcolumn']],$article[$config['titlecolumn']]));
            }
        }
        switch($action) {
            case 'name':
                Return '数据穿梭框';
            case 'hash':
                Return 'databasetransfer';
            case 'group':
                Return '数据库';
            case 'sql':
                Return 'text';
            case 'form':
                if(!count(C($GLOBALS['C']['DbClass'].':getfields',$config['table']))) {
                    Return '数据表不存在';
                }
                $config['inputhash']='transfer';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='transfer';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='transfer';
                Return C('cms:input:post',$config);
            case 'config':
                Return array(
                    array('configname'=>'表名','hash'=>'table','inputhash'=>'text','tips'=>'选项来源的数据库表名,系统会自动加表名前缀.如不需要加前缀,则使用no_perfix_表名'),
                    array('configname'=>'数据字段','hash'=>'idcolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段,修改数据字段会丢失数据,请提前确认好字段','defaultvalue'=>'id'),
                    array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段','defaultvalue'=>'title'),
                    array('configname'=>'条件','hash'=>'where','inputhash'=>'tags','tips'=>'数据查询条件','defaultvalue'=>'','column'=>2,'columntips'=>'字段,如:status status> status%;值:如 1'),
                    array('configname'=>'排序','hash'=>'order','inputhash'=>'text','tips'=>'如:id asc','defaultvalue'=>''),
                    array('configname'=>'最少勾选','hash'=>'mincheck','inputhash'=>'number','tips'=>'最少必须勾选几项','placeholder'=>'如:1,则此表单必须勾选1项'),
                    array('configname'=>'最多勾选','hash'=>'maxcheck','inputhash'=>'number','tips'=>'最多勾选几项','placeholder'=>'如:5,则此表单最多勾选5项'),
                    array('configname'=>'搜索','hash'=>'search','inputhash'=>'switch','tips'=>'当选项太多时,开启搜索功能可以快速找到对应的选项'),
                    array('configname'=>'穿梭框宽度','hash'=>'width','inputhash'=>'number','tips'=>'穿梭框的宽度,单位px,默认为200','placeholder'=>'200'),
                    array('configname'=>'穿梭框高度','hash'=>'height','inputhash'=>'number','tips'=>'穿梭框的高度,单位px,默认为340','placeholder'=>'340'),
                    array('configname'=>'待选区标题','hash'=>'title_left','inputhash'=>'text','tips'=>'','defaultvalue'=>'待选','placeholder'=>''),
                    array('configname'=>'已选区标题','hash'=>'title_right','inputhash'=>'text','tips'=>'','defaultvalue'=>'已选','placeholder'=>''),
                    );
        }
        Return false;
    }
    function databaseselects($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post' || $action=='ajax') {
            $config['tableset']=explode(';',$config['tableset']);
            $config['tables']=array();
            foreach($config['tableset'] as $key=>$table) {
                $thistable=explode('|',$table);
                if(empty($thistable[0])) {
                    if($action=='post') {Return false;}else {Return '配置错误,表名不能为空';}
                }
                if(empty($thistable[1]) && $key) {
                    if($action=='post') {Return false;}else {Return '配置错误,上级ID字段不能为空';}
                }
                if(empty($thistable[2])) {$thistable[2]='title';}
                if(empty($thistable[3])) {$thistable[3]='id asc';}
                $config['tables'][]=$thistable;
            }
        }
        switch($action) {
            case 'name':
                Return '数据多级联动框';
            case 'hash':
                Return 'databaseselects';
            case 'group':
                Return '数据库';
            case 'sql':
                Return 'bigint(11)';
            case 'form':
                $config['selecthtml']=C('this:input:databaseselects','selecthtml',$config);
                if(!$config['selecthtml']) {$config['selecthtml']='配置错误';}
                if($config['value']) {
                    $config['level']=count($config['tables'])-1;
                }else {
                    $config['level']=0;
                }
                V('input/databaseselects',$config);
                Return '';
             case "ajax":
                if(isset($config['disabled']) && $config['disabled']) {Return array('error'=>1,'msg'=>'无权限');}
                $config['value']=intval(@$_POST['value']);
                $config['level']=intval(@$_POST['level']);
                if($html=C('this:input:databaseselects','selecthtml',$config)) {
                    Return array('error'=>0,'html'=>$html);
                }
                Return array('error'=>1);
            case 'view':
                if($config['value']) {
                    $thislevel=$config['tables'][count($config['tables'])-1];
                    $article=one('table',$thislevel[0],'where',where('id',intval($config['value'])));
                    if($article) {
                        echo($article[$thislevel[2]]);
                        Return '';
                    }else {
                        echo('[数据不存在]');
                        Return '';
                    }
                }else {
                     echo('[未选]');
                     Return '';
                }
            case 'post':
                if(isset($config['nonull']) && $config['nonull']) {
                    if(!isset($_POST[$config['name']]) || empty($_POST[$config['name']])) {
                        Return array('error'=>'尚未选择');
                    }
                }
                if(@$_POST[$config['name']] || @$_POST[$config['name'].'_level']) {
                    if(@$_POST[$config['name'].'_level']!=count($config['tables'])-1) {
                        Return array('error'=>'未选择完整');
                    }
                    $thislevel=$config['tables'][count($config['tables'])-1];
                    if(!one('table',$thislevel[0],'where',where('id',intval(@$_POST[$config['name']])))) {
                        Return array('error'=>'数据不存在');
                    }
                }
                if(!@$_POST[$config['name']]) {
                    Return 0;
                }
                Return intval(@$_POST[$config['name']]);
            case 'config':
                Return array(
                        array('configname'=>'数据来源','hash'=>'tableset','inputhash'=>'tags','column'=>4,'columntips'=>'表名;上级ID字段名;标题字段名;排序','defaultvalue'=>'|||','tips'=>'各级数据来源,表名无需填写前缀,<br>第一级上级ID字段名可不填写(如填写则只取出字段名=0的数据),<br>标题字段默认为title,排序默认为id asc,<br>用户选择数据时需要筛选到最后一级数据,保存为最后一级数据的ID,<br>修改数据来源可能会造成现存的数据丢失或混乱,请谨慎操作'),
                    );
            case 'selecthtml':
                if(!isset($config['level'])) {$config['level']=count($config['tables'])-1;}
                if(!$config['value']) {$config['level']=0;}
                if(!isset($config['tables'][$config['level']])) {Return false;}
                $thislevel=$config['tables'][$config['level']];
                $list_query=array();
                $list_query['table']=$thislevel[0];
                $list_query['order']=$thislevel[3];
                if($config['level']) {
                    $thisarticle=one('table',$thislevel[0],'where',where('id',$config['value']));
                    if(!$thisarticle) {
                        $config['value']=0;
                        $config['level']=0;
                        Return '请重新选择 '.C('this:input:databaseselects','selecthtml',$config);
                    }
                    $list_query['where']=where($thislevel[1],$thisarticle[$thislevel[1]]);
                }elseif(!empty($thislevel[1])) {
                    $list_query['where']=where($thislevel[1],0);
                }
                $articles=all($list_query);
                $html='<div class="layui-inline"><select rel="'.$config['level'].'" lay-filter="databaseselects_'.$config['name'].'">';
                if(count($articles)) {
                    $html.='<option value="">请选择</option>';
                    foreach($articles as $article) {
                        if(!isset($article[$thislevel[2]])) {
                            $article[$thislevel[2]]='字段不存在['.$thislevel[2].']';
                        }
                        if($article['id']==$config['value']) {
                            $html.='<option value="'.$article['id'].'" selected>'.$article[$thislevel[2]].'</option>';
                        }else {
                            $html.='<option value="'.$article['id'].'">'.$article[$thislevel[2]].'</option>';
                        }
                    }
                }else {
                    if(isset($thisarticle[$thislevel[1]])) {
                        $html.='<option value="">请选择</option>';
                    }else {
                        $html.='<option value="">无选项</option>';
                        $config['son']=1;
                    }
                }
                $html.='</select></div>';
                if(isset($config['son']) || !$config['value']) {
                    $son=0;
                }else {
                    $son=1;
                    $config['son']=1;
                    $fidvalue=$config['value'];
                }
                $config['level']--;
                if($config['level']>=0 && $config['level']<count($config['tables'])) {
                    $config['value']=$thisarticle[$thislevel[1]];
                    $uplevelhtml=C('this:input:databaseselects','selecthtml',$config);
                    if(!$uplevelhtml) {Return false;}
                    $html=$uplevelhtml.$html;
                }
                if($son) {
                    $config['level']++;
                    $config['value']=$fidvalue;
                    $html.=C('this:input:databaseselects','sonhtml',$config);
                }
                Return $html;
            case 'sonhtml':
                $config['level']++;
                if(!isset($config['tables'][$config['level']])) {Return false;}
                $thislevel=$config['tables'][$config['level']];
                $list_query=array();
                $list_query['table']=$thislevel[0];
                $list_query['order']=$thislevel[3];
                $list_query['where']=where($thislevel[1],$config['value']);
                $articles=all($list_query);
                $html='<div class="layui-inline"><select rel="'.$config['level'].'" lay-filter="databaseselects_'.$config['name'].'">';
                if(count($articles)) {
                    $html.='<option value="">请选择</option>';
                    foreach($articles as $article) {
                        if(!isset($article[$thislevel[2]])) {
                            $article[$thislevel[2]]='字段不存在['.$thislevel[2].']';
                        }
                        $html.='<option value="'.$article['id'].'">'.$article[$thislevel[2]].'</option>';
                    }
                }else {
                    $html.='<option value="">无选项</option>';
                }
                $html.='</select></div>';
                Return $html;
        }
        Return false;
    }
    function databaseunlimit($action,$config=array()) {
        if($action=='form' || $action=='view' || $action=='post' || $action=='ajax') {
            if(empty($config['table'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            if(empty($config['fidcolumn'])) {$config['fidcolumn']='fid';}
            if(empty($config['titlecolumn'])) {$config['titlecolumn']='title';}
            if(empty($config['order'])) {$config['order']='id asc';}
        }
        switch($action) {
            case 'name':
                Return '数据无限联动框';
            case 'hash':
                Return 'databaseunlimit';
            case 'group':
                Return '数据库';
            case 'sql':
                Return 'bigint(11)';
            case 'form':
                $config['selecthtml']=C('this:input:databaseunlimit','selecthtml',$config);
                if(!$config['selecthtml']) {$config['selecthtml']='配置错误';}
                V('input/databaseunlimit',$config);
                Return '';
             case "ajax":
                if(isset($config['disabled']) && $config['disabled']) {Return array('error'=>1,'msg'=>'无权限');}
                $config['value']=intval(@$_POST['value']);
                if($html=C('this:input:databaseunlimit','selecthtml',$config)) {
                    Return array('error'=>0,'html'=>$html);
                }
                Return array('error'=>1);
            case 'view':
                if($config['value']) {
                    $article=one('table',$config['table'],'where',where('id',intval($config['value'])));
                    if($article) {
                        echo($article[$config['titlecolumn']]);
                    }else {
                        echo('[数据不存在]');
                    }
                }else {
                    echo('[未选]');
                }
                Return '';
            case 'post':
                if(isset($config['nonull']) && $config['nonull']) {
                    if(!isset($_POST[$config['name']]) || empty($_POST[$config['name']])) {
                        Return false;
                    }
                }
                if(@$_POST[$config['name']]) {
                    if(!one('table',$config['table'],'where',where('id',intval(@$_POST[$config['name']])))) {
                        Return array('error'=>'数据不存在');
                    }
                }
                if(!@$_POST[$config['name']]) {
                    Return 0;
                }
                Return intval(@$_POST[$config['name']]);
            case 'config':
                Return array(
                    array('configname'=>'表名','hash'=>'table','inputhash'=>'text','tips'=>'选项来源的数据库表名,系统会自动加表名前缀.如不需要加前缀,则使用no_perfix_表名'),
                    array('configname'=>'父字段','hash'=>'fidcolumn','inputhash'=>'text','tips'=>'上级ID字段名','defaultvalue'=>'fid'),
                    array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段','defaultvalue'=>'title'),
                    array('configname'=>'排序','hash'=>'order','inputhash'=>'text','tips'=>'如:id asc','defaultvalue'=>'')
                    );
            case 'selecthtml':
                if($config['value']) {
                    $thisarticle=one('table',$config['table'],'where',where('id',intval($config['value'])));
                    if($thisarticle) {
                        $fid=$thisarticle[$config['fidcolumn']];
                    }else {
                        if(isset($config['son']) && !$config['son']) {
                            Return false;
                        }
                        $config['value']=0;
                        Return '请重新选择 '.C('this:input:databaseunlimit','selecthtml',$config);
                    }
                }else {
                    $fid=0;
                }
                if(!isset($config['son'])) {
                    $config['son']=1;
                }
                $list_query=array();
                $list_query['table']=$config['table'];
                $list_query['order']=$config['order'];
                $list_query['where']=where($config['fidcolumn'],$fid);
                $articles=all($list_query);
                $html='<div class="layui-inline"><select lay-filter="databaseunlimit_'.$config['name'].'">';
                if(count($articles)) {
                    $html.='<option value="">请选择</option>';
                    foreach($articles as $article) {
                        if(!isset($article[$config['titlecolumn']])) {
                            $article[$config['titlecolumn']]='字段不存在['.$config['titlecolumn'].']';
                        }
                        if($article['id']==$config['value']) {
                            $html.='<option value="'.$article['id'].'" selected>'.$article[$config['titlecolumn']].'</option>';
                        }else {
                            $html.='<option value="'.$article['id'].'">'.$article[$config['titlecolumn']].'</option>';
                        }
                    }
                }else {
                    $html.='<option value="">无选项</option>';
                    $config['son']=0;
                }
                $html.='</select></div>';
                if($fid>0) {
                    $fidvalue=$config['value'];
                    $son=$config['son'];
                    $config['value']=$fid;
                    $config['son']=0;
                    $uplevelhtml=C('this:input:databaseunlimit','selecthtml',$config);
                    if(!$uplevelhtml) {
                        if($fidvalue && $son) {
                            $config['value']=0;
                            Return '请重新选择 '.C('this:input:databaseunlimit','selecthtml',$config);
                        }
                        Return false;
                    }
                    $html=$uplevelhtml.$html;
                    $config['value']=$fidvalue;
                    $config['son']=$son;
                }
                if($config['value'] && $config['son']) {
                    $html.=C('this:input:databaseunlimit','sonhtml',$config);
                }
                Return $html;
            case 'sonhtml':
                $list_query=array();
                $list_query['table']=$config['table'];
                $list_query['order']=$config['order'];
                $list_query['where']=where($config['fidcolumn'],$config['value']);
                $articles=all($list_query);
                $html='<div class="layui-inline"><select lay-filter="databaseunlimit_'.$config['name'].'">';
                if(count($articles)) {
                    $html.='<option value="">请选择</option>';
                    foreach($articles as $article) {
                        if(!isset($article[$config['titlecolumn']])) {
                            $article[$config['titlecolumn']]='字段不存在['.$config['titlecolumn'].']';
                        }
                        $html.='<option value="'.$article['id'].'">'.$article[$config['titlecolumn']].'</option>';
                    }
                }else {
                    Return '';
                }
                $html.='</select></div>';
                Return $html;
        }
        Return false;
    }
    function databasetree($action,$config=array()) {
        if($action=='tree') {
            if(!isset($config['fidvalue'])) {$config['fidvalue']=0;}
            if(!isset($config['times'])) {$config['times']=0;}
            $treearticles=array();
            foreach($config['articles'] as $article) {
                if($article[$config['fidcolumn']]==$config['fidvalue']) {
                    $article['_ex']='|--'.str_repeat('----',$config['times']*2);
                    $treearticles[]=$article;
                    $config['times']++;
                    $oldfid=$config['fidvalue'];
                    $config['fidvalue']=$article[$config['idcolumn']];
                    $sonarticles=C('this:input:databasetree','tree',$config);
                    if(count($sonarticles)) {
                        foreach($sonarticles as $sonarticle) {
                            $treearticles[]=$sonarticle;
                        }
                    }
                    $config['times']--;
                    $config['fidvalue']=$oldfid;
                }
            }
            Return $treearticles;
        }
        if($action=='form' || $action=='view' || $action=='post') {
            $config['savetype']=1;
            if(empty($config['table'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            if(empty($config['idcolumn'])) {$config['idcolumn']='id';}
            if(empty($config['fidcolumn'])) {$config['fidcolumn']='fid';}
            if(empty($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$config['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '数据表不存在';
            }
            if(!isset($tablefields[$config['idcolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有数据字段:'.htmlspecialchars($config['idcolumn']);
            }
            if(!isset($tablefields[$config['fidcolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有分类字段:'.htmlspecialchars($config['fidcolumn']);
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            $list_query=array();
            $list_query['table']=$config['table'];
            if(!empty($config['order'])) {$list_query['order']=$config['order'];}
            $articles=all($list_query);
            $config['values']=array();
            if(isset($articles[0])) {
                if(!is_numeric($articles[0][$config['idcolumn']]) && $config['idtype']==1) {
                    if($action=='post') {
                        Return false;
                    }else {
                        Return '数据字段 '.htmlspecialchars($config['titlecolumn']).' 类型为文字,请修改配置';
                    }
                }
            }
            $config['articles']=$articles;
            $articles=C('this:input:databasetree','tree',$config);
            if(!count($articles)) {
                $config['values'][]='0:暂无选项:disabled';
            }
            foreach($articles as $article) {
                if($action=='view') {$article['_ex']='';}
                $article[$config['idcolumn']]=str_replace(':','\\:',$article[$config['idcolumn']]);
                $article[$config['titlecolumn']]=str_replace(':','\\:',$article[$config['titlecolumn']]);
                $config['values'][]=implode(':',array($article[$config['idcolumn']],$article['_ex'].$article[$config['titlecolumn']]));
            }
        }
        switch($action) {
            case 'name':
                Return '数据树形列表框';
            case 'hash':
                Return 'databasetree';
            case 'group':
                Return '数据库';
            case 'sql':
                if($config['idtype']==1) {Return 'bigint(11)';}
                Return 'varchar(255)';
            case 'form':
                if(!count(C($GLOBALS['C']['DbClass'].':getfields',$config['table']))) {
                    Return '数据表不存在';
                }
                $config['inputhash']='select';
                Return C('cms:input:form',$config);
            case 'view':
                $config['inputhash']='select';
                Return C('cms:input:view',$config);
            case 'post':
                $config['inputhash']='select';
                $postvalue=C('cms:input:post',$config);
                if(!strlen($postvalue) && $config['idtype']==1){
                    $postvalue=0;
                }
                Return $postvalue;
            case 'config':
                Return array(
                    array('configname'=>'表名','hash'=>'table','inputhash'=>'text','tips'=>'选项来源的数据库表名,系统会自动加表名前缀.如不需要加前缀,则使用no_perfix_表名'),
                    array('configname'=>'数据字段','hash'=>'idcolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段,修改数据字段会丢失数据,请提前确认好字段','defaultvalue'=>'id'),
                    array('configname'=>'数据类型','hash'=>'idtype','inputhash'=>'radio','tips'=>'数据字段在数据库中的类型.切换类型会丢失信息,请提前确认好保存类型.','defaultvalue'=>'1','values'=>"1:数字\n2:文字",'savetype'=>1),
                    array('configname'=>'父字段','hash'=>'fidcolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段','defaultvalue'=>'fid'),
                    array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段','defaultvalue'=>'title'),
                    array('configname'=>'排序','hash'=>'order','inputhash'=>'text','tips'=>'如:id asc','defaultvalue'=>''),
                    array('configname'=>'默认文字','hash'=>'selecttitle','inputhash'=>'text','tips'=>'未选择时列表框的默认文字,不填则不显示','defaultvalue'=>'请选择'),
                    array('configname'=>'默认值','hash'=>'selectvalue','inputhash'=>'text','tips'=>'未选择时列表框的默认值','defaultvalue'=>'0'),
                    array('configname'=>'搜索','hash'=>'search','inputhash'=>'switch','tips'=>'当选项太多时,开启搜索功能可以快速找到对应的选项'),
                );
        }
        Return false;
    }
    function databasetreeselects($action,$config=array()) {
        if($action=='tree') {
            if(!isset($config['fidvalue'])) {$config['fidvalue']=0;}
            $treearticles=array();
            foreach($config['articles'] as $article) {
                if(!$config['fidcolumn'] || ($article[$config['fidcolumn']]==$config['fidvalue'])) {
                    $thisarticle=array('value'=>$article[$config['idcolumn']],'name'=>$article[$config['titlecolumn']]);
                    $oldfid=$config['fidvalue'];
                    $config['fidvalue']=$article[$config['idcolumn']];
                    if($config['fidcolumn']){
                        $sonarticles=C('this:input:databasetreeselects','tree',$config);
                        if($sonarticles){
                            $thisarticle['children']=$sonarticles;
                        }
                    }
                    if(isset($config['disabledcolumn']) && $config['disabledcolumn'] && !$article[$config['disabledcolumn']]){
                        $thisarticle['disabled']=true;
                    }
                    if(in_array($article[$config['idcolumn']],$config['values'])){
                        $thisarticle['selected']=true;
                    }
                    $treearticles[]=$thisarticle;
                    $config['fidvalue']=$oldfid;
                }
            }
            Return $treearticles;
        }
        if($action=='form' || $action=='view' || $action=='post') {
            if(empty($config['table'])) {
                if($action=='post') {
                    Return false;
                }else {
                    Return '尚未配置';
                }
            }
            if(empty($config['idcolumn'])) {$config['idcolumn']='id';}
            if(empty($config['fidcolumn'])) {$config['fidcolumn']='';}
            if(empty($config['titlecolumn'])) {$config['titlecolumn']='title';}
            $tablefields=C($GLOBALS['C']['DbClass'].':getfields',$config['table']);
            if(!count($tablefields)) {
                if($action=='post') {Return false;}
                Return '数据表不存在';
            }
            if(!isset($tablefields[$config['idcolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有数据字段:'.htmlspecialchars($config['idcolumn']);
            }
            if(!isset($tablefields[$config['titlecolumn']])){
                if($action=='post') {Return false;}
                Return '未拥有标题字段:'.htmlspecialchars($config['titlecolumn']);
            }
            if(!isset($config['articles'])){
                $list_query=array();
                $list_query['table']=$config['table'];
                $wheres=array_filter(explode(';',$config['where']));
                $sqlwhere=array();
                foreach ($wheres as $where) {
                    $thiswhere=explode('|',$where);
                    $sqlwhere[$thiswhere[0]]=$thiswhere[1];
                }
                if(count($sqlwhere)){
                    $list_query['where']=$sqlwhere;
                }
                if(!empty($config['order'])) {$list_query['order']=$config['order'];}
                $config['articles']=all($list_query);
            }
            if(count($config['articles'])>$config['expanded']){
                $config['expanded']=0;
            }else{
                $config['expanded']=1;
            }
            if(count($config['articles'])>$config['search']){
                $config['search']=1;
            }else{
                $config['search']=0;
            }
            $config['values']=array_filter(explode(';',$config['value']));
            if(!isset($config['treearticles'])){
                $config['treearticles']=C('this:input:databasetreeselects','tree',$config);
            }
        }
        switch($action) {
            case 'name':
                Return '数据树形多选框';
            case 'hash':
                Return 'databasetreeselects';
            case 'group':
                Return '数据库';
            case 'sql':
                Return 'text';
            case 'form':
                V('input/databasetreeselects',$config);
                Return '';
            case 'view':
                $html='';
                foreach ($config['values'] as $value) {
                    foreach($config['articles'] as $article) {
                        if($article[$config['idcolumn']]==$value){
                            $html.='<button type="button" class="layui-btn cms-btn layui-btn-xs">'.$article[$config['titlecolumn']].'</button> ';
                            break ;
                        }
                    }
                }
                Return $html;
            case 'post':
                if(isset($_POST[$config['name']])){
                    $postvalues=array_filter(explode(',',$_POST[$config['name']]));
                }else{
                    $postvalues=array();
                }
                $values=array();
                foreach ($postvalues as $postvalue) {
                    foreach($config['articles'] as $article) {
                        if($article[$config['idcolumn']]==$postvalue){
                            if(!$config['disabledcolumn'] || ($config['disabledcolumn'] && $article[$config['disabledcolumn']])){
                                $values[]=$article[$config['idcolumn']];
                            }
                        }
                    }
                }
                if(isset($config['nonull']) && $config['nonull'] && !count($values)) {
                    Return array('error'=>'不能为空');
                }
                if($config['radio'] && count($values)){
                    return false;
                }
                return implode(';',$values);
            case 'config':
                Return array(
                    array('configname'=>'表名','hash'=>'table','inputhash'=>'text','tips'=>'选项来源的数据库表名,系统会自动加表名前缀.如不需要加前缀,则使用no_perfix_表名'),
                    array('configname'=>'数据字段','hash'=>'idcolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段,修改数据字段会丢失数据,请提前确认好字段','defaultvalue'=>'id'),
                    array('configname'=>'标题字段','hash'=>'titlecolumn','inputhash'=>'text','tips'=>'请确保数据库表中拥有此字段','defaultvalue'=>'title'),
                    array('configname'=>'父字段','hash'=>'fidcolumn','inputhash'=>'text','tips'=>'数据的上级数据字段名,如fid,如不填写,则只展示列表框','defaultvalue'=>''),
                    array('configname'=>'禁止勾选字段','hash'=>'disabledcolumn','inputhash'=>'text','tips'=>'禁止勾选某些数据,如填写:enabled,则数据库数据中字段enabled=0的禁止勾选','defaultvalue'=>''),
                    array('configname'=>'条件','hash'=>'where','inputhash'=>'tags','tips'=>'数据查询条件','defaultvalue'=>'','column'=>2,'columntips'=>'字段,如:status;值:如 1'),
                    array('configname'=>'排序','hash'=>'order','inputhash'=>'text','tips'=>'如:id asc','defaultvalue'=>''),
                    array('configname'=>'模式','hash'=>'strict','inputhash'=>'switch','tips'=>'是否严格遵守父子模式,开启后,只可勾选末级数据','defaultvalue'=>0),
                    array('configname'=>'单选','hash'=>'radio','inputhash'=>'switch','tips'=>'是否开启单选模式,开启单选后只能选择一条数据','defaultvalue'=>0),
                    array('configname'=>'展开数据量','hash'=>'expanded','inputhash'=>'number','tips'=>'数据量小于此选项时,展开所有选项','defaultvalue'=>50),
                    array('configname'=>'搜索数据量','hash'=>'search','inputhash'=>'number','tips'=>'数据量大于此选项时,显示搜索框,方便找到对应的选项','defaultvalue'=>50),
                );
        }
        Return false;
    }
}