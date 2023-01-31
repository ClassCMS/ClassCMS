<?php
if(!defined('ClassCms')) {exit();}
class template {
    function install() {
        C($GLOBALS['C']['DbClass'].':addIndex','article_'.I().'_article','recommend');
    }
    function pagelist() {
        $pagelist=pagelist();
        $pageinfo=pageinfo();
        $pagehtml='';
        if(!isset($pageinfo['pagecount']) || $pageinfo['pagecount']==1) {
            Return ;
        }
        $firstshow=true;
        $lastshow=true;
        foreach($pagelist as $thispage) {
            if($thispage['page']==1) {$firstshow=false;}
            if($thispage['page']==$pageinfo['pagecount']) {$lastshow=false;}
        }
        if($firstshow) {
            $pagehtml.='<a href="'.$pageinfo['first']['link'].'" class="'.$pageinfo['first']['class'].'">1</a>';
            if($pagelist[0]['page']-1>1) {
                $pagehtml.='<a href="javascript:;" class="">...</a>';
            }
        }
        foreach($pagelist as $key=>$page) {
            $pagehtml.='<a href="'.$page['link'].'" class="'.$page['class'].'">'.$page['title'].'</a>';
        }
        if($lastshow) {
            if($pageinfo['pagecount']-$pagelist[count($pagelist)-1]['page']>1) {
                $pagehtml.='<a href="javascript:;" class="">....</a>';
            }
            $pagehtml.='<a href="'.$pageinfo['last']['link'].'" class="'.$pageinfo['last']['class'].'">'.$pageinfo['last']['page'].'</a>';
        }
        Return $pagehtml;
    }
}