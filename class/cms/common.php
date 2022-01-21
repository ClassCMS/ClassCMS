<?php
if(!defined('ClassCms')) {exit();}
class cms_common {
    function addLog($msg,$time='') {
        if(empty($time)) {
            $time=date('Y-m-d H:i:s');
        }
        $file = fopen('log_'.md5($GLOBALS['C']['SiteHash']).'.txt', "a");
        fwrite($file, $time.' '.$msg.PHP_EOL);
        fclose($file);
        Return true;
    }
    function jump($url='',$time=0) {
        if(empty($url) && isset($_SERVER['HTTP_REFERER'])) {$url=htmlspecialchars($_SERVER['HTTP_REFERER']);}
	    echo("<meta http-equiv=refresh content='".$time."; url=".$url."'>");
    }
    function ip() {
        if(@$_SERVER["REMOTE_ADDR"]=='::1') {$_SERVER["REMOTE_ADDR"]='127.0.0.1';}
        Return @$_SERVER["REMOTE_ADDR"];
    }
    function echoJson($array=array()) {
        echo(json_encode($array));
        Return true;
    }
    function text($html,$length=false,$ellipsis='') {
        if(!$length) {$length=strlen($html)*10;}
        $html=preg_replace("/(\<(.*)>)/Ui",'',$html);
        $html=str_replace('<','&lt;',$html);
        $html=str_replace('>','&gt;',$html);
        $html=str_replace('&emsp;','',$html);
        $html=str_replace('&nbsp;','',$html);
        $html=str_replace("\t",'',$html);
        $html=str_replace("\r\n",' ',$html);
        $html=str_replace("\n",' ',$html);
        while(1==1) {
            if(stripos($html,'  ')===false) {
                break;
            }else {
                $html=str_replace('  ',' ',$html);
            }
        }
        if(function_exists("mb_substr"))
        {
            if(mb_strlen($html, 'utf-8') <= $length){Return $html;}
            Return mb_substr($html, 0, $length, 'utf-8').$ellipsis;
        }
        else{
            preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $html, $match);
            if(count($match[0]) <= $length) {return $html;}
            return join("",array_slice($match[0], 0, $length)).$ellipsis;
        }
    }
    function isAjax() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            Return true;
        }
        Return false;
    }
    function markdown($code) {
        return false;
    }
    function safeHtml($html) {
        Return $html;
    }
    function session($hash,$value=null) {
        if(!isset($_SESSION)) { session_start(); }
        if($value===null) {
            if(!isset($_SESSION[$hash])) {
                Return false;
            }
            Return $_SESSION[$hash];
        }else {
            $_SESSION[$hash]=$value;
        }
        Return true;
    }
    function filesizeString($size=0,$precision=0) {
        if($size<1024){return $size.'Byte';}
        if($size<1048576){return round($size/1024,$precision).'KB';}
        if($size<1073741824){return round($size/1048576,$precision).'MB';}
        if($size<1099511627776) {return round($size/1073741824,$precision).'GB';}
        return round($size/1099511627776,$precision).'TB';
    }
    function randStr($length,$str='') {
        if(empty($str)) {
            $str='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        }
        $len=strlen($str)-1;
        $randstr='';
        for($i=0;$i<$length;$i++){
            $num=mt_rand(0,$len);
            $randstr.= $str[$num];
        }
        return $randstr;
    }
    function opcacheReset() {
        if(function_exists('opcache_reset')) {
            Return @opcache_reset();
        }
        Return true;
    }
    function serverName() {
        if(isset($_SERVER['HTTP_HOST'])){
            if(stripos($_SERVER['HTTP_HOST'],']')) {
                $thisserver_names=explode(']',$_SERVER['HTTP_HOST']);
                $name=$thisserver_names[0].']';
            }else {
                $thisserver_names=explode(':',$_SERVER['HTTP_HOST']);
                $name=$thisserver_names[0];
            }
        }else {
            if(!isset($_SERVER['SERVER_NAME'])) {$_SERVER['SERVER_NAME']='';}
            $name=$_SERVER['SERVER_NAME'];
        }
        Return strtolower($name);
    }
    function serverPort($colon=true) {
        if(isset($_SERVER['HTTP_HOST'])){
            if(stripos($_SERVER['HTTP_HOST'],']')) {
                $thisserver_port=explode(']:',$_SERVER['HTTP_HOST']);
            }else {
                $thisserver_port=explode(':',$_SERVER['HTTP_HOST']);
            }
            if(isset($thisserver_port[1]) && is_numeric($thisserver_port[1])) {
                $port=$thisserver_port[1];
            }else {
                $port='80';
            }
        }elseif(isset($_SERVER['SERVER_PORT']) && is_numeric($_SERVER['SERVER_PORT'])) {
            $port=$_SERVER['SERVER_PORT'];
        }else {
            $port='80';
        }
        if($port=='80') {
            Return '';
        }elseif($colon) {
            Return ':'.$port;
        }else{
            Return $port;
        }
    }
    function createDir($path){
        if (!file_exists($path)){
            if(!C('this:common:createDir',dirname($path))) {
                Return false;
            }
            if(!@mkdir($path, 0777)) {
                Return false;
            }
        }
        Return true;
    }
    function upload($name,$path='',$filename='') {
        if(!isset($_FILES[$name])) {Return array('error'=>1,'message'=>'no '.$name);}
        $path=trim($path);
        $filename=trim($filename);
        if(empty($path)) {$path='/'.@$GLOBALS['C']['UploadDir'].'/(Y)(m)(d)/';}
        if(empty($filename)) {$filename='(rand).(ext)';}
        $allfiles=array();
        if(is_array($_FILES[$name]['name'])) {
            foreach($_FILES[$name]['name'] as $key=>$val) {
                if(!isset($_FILES[$name]['url'][$key])) {$_FILES[$name]['url'][$key]='';}
                $allfiles[]=array('name'=>$_FILES[$name]['name'][$key],'type'=>$_FILES[$name]['type'][$key],'url'=>$_FILES[$name]['url'][$key],'tmp_name'=>$_FILES[$name]['tmp_name'][$key],'error'=>$_FILES[$name]['error'][$key],'size'=>$_FILES[$name]['size'][$key]);
            }
        }else {
            $allfiles[]=$_FILES[$name];
        }
        if(count($allfiles)==0) {Return array('error'=>1,'message'=>'no '.$name);}
        $uploadExt=C('this:common:uploadExt');
        $uploadSize=C('this:common:uploadSize');
        $replace_key=array('(ext)','(rand)','(filename)','(Y)','(y)','(m)','(d)','(H)','(h)','(i)','(s)');
        foreach($allfiles as $filekey=>$file) {
            $allfiles[$filekey]['message']='';
            $allfiles[$filekey]['url']='';
            $file['name']=htmlspecialchars($file['name']);
            if(isset($file['size']) && $file['size']>$uploadSize) {$file['error']=1;}
            if(empty($file['name'])) {
                $allfiles[$filekey]['message']='未知文件名.';
                $allfiles[$filekey]['error']=7;
            }elseif($file['error']==1 || $file['error']==2) {
                $allfiles[$filekey]['message']=$file['name'].' 文件太大.';
            }elseif($file['error']==3 || $file['error']==4 || $file['error']==5) {
                $allfiles[$filekey]['message']=$file['name'].' 上传失败.';
            }elseif($file['error']==0) {
                
            }else {
                $allfiles[$filekey]['message']=$file['name'].' 未知错误.';
                $allfiles[$filekey]['error']=8;
            }
            $temp_arr=explode(".", $file['name']);
            $file_ext=strtolower(array_pop($temp_arr));
            if(!in_array($file_ext,$uploadExt)) {
                $allfiles[$filekey]['message']=$file['name'].' 不允许的文件类型.';
                $allfiles[$filekey]['error']=9;
            }
            $replace_value=array($file_ext,substr(md5(time().rand(1000000, 9999999).@$allfiles[$filekey]['tmp_name']),0,14),implode('.',$temp_arr),date('Y'),date('y'),date('m'),date('d'),date('H'),date('h'),date('i'),date('s'));
            $allfiles[$filekey]['save_path']=str_replace($replace_key,$replace_value,$path);
            $allfiles[$filekey]['save_name']=str_replace($replace_key,$replace_value,$filename);
            if(empty($allfiles[$filekey]['message'])) {
                if (!$allfiles[$filekey]['url']=C('this:common:uploadMove',$file['tmp_name'],$allfiles[$filekey]['save_path'],$allfiles[$filekey]['save_name'])) {
                    $allfiles[$filekey]['message']=$file['name'].' 保存文件失败.';
                    $allfiles[$filekey]['error']=6;
                    $allfiles[$filekey]['url']='';
                }
            }
        }
        $allmessage='';
        $error=0;
        $urls=array();
        foreach($allfiles as $file) {
            if($file['error']) {
                $allmessage.=$file['message'].' ';
                $error=1;
            }else {
                $urls[]=$file['url'];
            }
        }
        Return array('error'=>$error,'message'=>$allmessage,'file'=>$allfiles,'url'=>$urls);
    }
    function uploadSize() {
        if($upload_max_filesize=@ini_get('upload_max_filesize')) {
            if(strtolower(substr($upload_max_filesize,-1))=='m') {Return intval($upload_max_filesize)*1024*1024;}
            if(strtolower(substr($upload_max_filesize,-1))=='g') {Return intval($upload_max_filesize)*1024*1024*1024;}
        }
        Return 104857600;
    }
    function uploadExt() {
        Return array('gif','jpg','jpeg','png','bmp','blob','psd','webp','doc','docx','xls','xlsx','ppt','txt','zip','7z','gz','bz2','pdf','rar','tar','torrent','exe','apk','ipa','swf','flv','mp3','mp4','wav','wma','wmv','mid','avi','mpg','asf');
    }
    function uploadMove($tempfile,$path='',$filename='') {
        if(!is_file($tempfile)) {
            if(isset($_FILES[$tempfile]['tmp_name']) && is_file($_FILES[$tempfile]['tmp_name'])) {
                $tempfile=$_FILES[$tempfile]['tmp_name'];
            }else {
                Return false;
            }
        }
        if(empty($filename)) {
            Return false;
        }else {
            $temp_arr = explode(".", $filename);
            $file_ext = strtolower(array_pop($temp_arr));
            $uploadExt=C('this:common:uploadExt');
            if(!in_array($file_ext,$uploadExt)) {
                Return false;
            }
        }
        if(empty($path)) {
            $path=$GLOBALS['C']['UploadDir'].DIRECTORY_SEPARATOR.date("Ymd");
        }
        $allpath=$GLOBALS['C']['SystemRoot'].$path;
        if(DIRECTORY_SEPARATOR=='/') {
            $allpath=str_replace('\\','/',$allpath);
            $allpath=str_replace(array('///','//'),'/',$allpath);
        }else {
            $allpath=str_replace('/','\\',$allpath);
            $allpath=str_replace(array('\\\\\\','\\\\'),'\\',$allpath);
            $allpath =iconv('UTF-8', 'GBK//IGNORE', $allpath);
            $filename =iconv('UTF-8', 'GBK//IGNORE', $filename);
        }
        if(!file_exists($allpath) && !cms_createdir($allpath)) {
            Return false;
        }
        $allpath=rtrim($allpath,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename;
        if(version_compare(PHP_VERSION,'5.3.4','<') && stripos($allpath,chr(0))){Return false;}
        if(@copy($tempfile,$allpath)) {
            @unlink($tempfile);
            $url=$GLOBALS['C']['SystemDir'].str_replace('\\','/',$path).'/'.$filename;
            $url=str_replace(array('///','//'),'/',$url);
            if(DIRECTORY_SEPARATOR=='\\') {
                $url =iconv('GBK', 'UTF-8//IGNORE', $url);
            }
            Return $url;
        }
        Return false;
    }
    function uploadRemove($uploadfile){
        $filepath=rtrim($GLOBALS['C']['SystemRoot'],DIRECTORY_SEPARATOR).str_replace('/',DIRECTORY_SEPARATOR,$uploadfile);
        if(is_file($filepath) && unlink($filepath)) {
            Return true;
        }
        Return false;
    }
    function verify($str,$kind=''){
        if(empty($str)){return false;}
        if($kind=='id') {
            Return preg_match("/^[1-9][0-9]*$/",$str);
        }
        if($kind=='username') {
            Return preg_match("/^[A-Za-z0-9_\x{4e00}-\x{9fa5}]{2,32}$/u",$str);
        }
        if($kind=='password') {
            Return preg_match('/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{6,32}$/',$str);
        }
        if($kind=='email') {
            Return preg_match('/^[\w\d]+[\w\d\-.]*@[\w\d\-.]+\.[\w\d]{2,10}$/i',$str);
        }
        if($kind=='hash') {
            Return is_hash($str);
        }
        if($kind=='phone') {
            Return preg_match('/^(1\d{10})$/',$str);
        }
        if($kind=='ip') {
            if(preg_match('/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/',$str)) {
                Return true;
            }
            Return preg_match('/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/',$str);
        }
        Return false;
    }
    function send($url,$data=array(),$post=false,$timeout=0,$args=array()){
        if(empty($url)) {Return false;}
        if(!$post && is_array($data) && count($data)) {
            if(stripos($url,'?')===false) {$url.='?';}
            foreach($data as $key=>$val) {
                $url.='&'.$key.'='.$val;
            }
            $data=array();
        }
        if (function_exists("curl_init")){
            $curl=curl_init();
            curl_setopt($curl,CURLOPT_URL,$url);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
            if($post) {
                curl_setopt($curl,CURLOPT_POST,1);
                curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
            }
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
            curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
            curl_setopt($curl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
            curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,$timeout);
            curl_setopt($curl,CURLOPT_TIMEOUT,$timeout);
            foreach ($args as $key=>$arg) {
                curl_setopt($curl,constant($key),$arg);
            }
            $content=curl_exec($curl);
            $httpinfo=curl_getinfo($curl);
            curl_close($curl);
            if($httpinfo['http_code']>=300) {$content=false;}
        }else{
            if(count($args)){
                return false;
            }
            if($post) {
                $options['http']=array('timeout'=>$timeout,'method' => 'POST','header' => 'Content-type:application/x-www-form-urlencoded','content' =>http_build_query($data));
            }else {
                $options['http']=array('timeout'=>$timeout,'method' =>'GET');
            }
            $content=@file_get_contents($url, false, stream_context_create($options));
        }
        Return $content;
    }
    function download($url,$path,$timeout=999){
        $removefile=!@is_file($path);
        if(!$fp = @fopen($path, "w+")){
            return false;
        }
        if(C('this:common:send',$url,array(),false,$timeout,array('CURLOPT_FILE'=>$fp))){
            @fclose($fp);
            return true;
        }
        @fclose($fp);
        if($removefile){
            @unlink($path);
        }
        return false;
    }
    function pinyin($_String){
        $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
        "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
        "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
        "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
        "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
        "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
        "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
        "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
        "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
        "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
        "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
        "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
        "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
        "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
        "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
        "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
        $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
        "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
        "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
        "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
        "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
        "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
        "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
        "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
        "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
        "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
        "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
        "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
        "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
        "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
        "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
        "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
        "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
        "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
        "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
        "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
        "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
        "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
        "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
        "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
        "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
        "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
        "|-10270|-10262|-10260|-10256|-10254";
        $_TDataKey = explode('|', $_DataKey);
        $_TDataValue = explode('|', $_DataValue);
        $_Data = array_combine($_TDataKey, $_TDataValue);
        arsort($_Data);
        reset($_Data);
        $gbkstring='';
        if($_String < 0x80 || version_compare(PHP_VERSION,'8.0','>=')) {
            $gbkstring .= $_String;
        }elseif($_String < 0x800){
            $gbkstring .= chr(0xC0 | $_String>>6);
            $gbkstring .= chr(0x80 | $_String & 0x3F);
        }elseif($_String < 0x10000){
            $gbkstring .= chr(0xE0 | $_String>>12);
            $gbkstring .= chr(0x80 | $_String>>6 & 0x3F);
            $gbkstring .= chr(0x80 | $_String & 0x3F);
        } elseif($_String < 0x200000) {
            $gbkstring .= chr(0xF0 | $_String>>18);
            $gbkstring .= chr(0x80 | $_String>>12 & 0x3F);
            $gbkstring .= chr(0x80 | $_String>>6 & 0x3F);
            $gbkstring .= chr(0x80 | $_String & 0x3F);
        }
        $_String=@iconv('UTF-8', 'GB2312', $gbkstring);
        $_Res = '';
        for($i=0; $i<strlen($_String); $i++)
        {
            $_P = ord(substr($_String, $i, 1));
            if($_P>160) { $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536; }
            if ($_P>0 && $_P<160 ){ 
                $_Res .= chr($_P);
            }elseif($_P<-20319 || $_P>-10247) {
            }else {
                foreach($_Data as $k=>$v){ if($v<=$_P) break; }
                $_Res .= $k;
            }
        }
        return preg_replace("/[^a-z0-9]*/", '', $_Res);
    }
}