<?php
namespace Common\TagLib;
use Think\Template\TagLib;
Class Common extends TagLib{
	protected function attr($tag){
        $attrs              = array(
            'method',
            'action',
            'enctype',
            'type',
            'name',
            'value',
            'url',
            'placeholder',
            'ng-controller',
            'ng-init',
            'ng-repeat',
            'ng-submit',
            'ng-click',
            'ng-model',
            'ng-class',
            'ng-disabled',
            'ng-show',
            'ng-hide',
            'ng-list',
            'ng-if',
            'ng-options',
        );
        $result             = array();
        foreach ($attrs as $value) {
            if(isset($tag[$value])){
                $result[]   = sprintf('%s="%s"',$value,$tag[$value]);
            }
        }
        return count($result)>0?sprintf(' %s',join(' ',$result)):'';
    }
    protected function cls($tag,$default){
        $result             = array();
        if($default){
            $result[]       = $default;
        }
        if($tag['col']){
            $result[]       = "col-md-$tag[col]";
        }
        if($tag['offset']){
            $result[]       = "col-md-offset-$tag[offset]";
        }
        if($tag['class']){
            $result[]       = $tag['class'];
        }
        return count($result)>0?sprintf(' class="%s"',join(' ',$result)):'';
    }
    private function jsPath(){
        if(C('LESS')){
            return C('TMPL_PARSE_STRING.__JAVASCRIPT__');
        }else{
            return C('TMPL_PARSE_STRING.__JS__');
        }
    }
    public function _linklib($tag){
        $file           = $tag['file'];
        if(C('LESS')){
            return sprintf('<link rel="stylesheet/less" type="text/css" href="%s/%s/%s.%s" />',dirname(C('TMPL_PARSE_STRING.__LESS__')),'static',$file,'less');
        }else{
            return sprintf('<link rel="stylesheet" type="text/css" href="%s/%s.%s" />',C('TMPL_PARSE_STRING.__STATIC__'),$file,'css');
        }
    }
    public function _linkcss($tag){
        $file           = $tag['file'];
        if(C('LESS')){
            return sprintf('<link rel="stylesheet/less" type="text/css" href="%s/%s.%s" />',C('TMPL_PARSE_STRING.__LESS__'),$file,'less');
        }else{
            return sprintf('<link rel="stylesheet" type="text/css" href="%s/%s.%s" />',C('TMPL_PARSE_STRING.__CSS__'),$file,'css');
        }
    }
    public function _linkjs($tag){
        $file           = $tag['file'];
        $static         = !!$tag['static'];
        $data           = $tag['data-main'];
        if($data){
            $data       = sprintf(' data-main="%s/%s.js"',$this->jsPath(),$data);
        }
        if($static){
            return sprintf('<script type="text/javascript" src="%s/%s%s"%s></script>',C('TMPL_PARSE_STRING.__STATIC__'),$file,'.js',$data);
        }elseif(C('LESS')){
            return sprintf('<script type="text/javascript" src="%s/%s%s"></script>',C('TMPL_PARSE_STRING.__JAVASCRIPT__'),$file,'.js');
        }else{
            return sprintf('<script type="text/javascript" src="%s/%s%s"></script>',C('TMPL_PARSE_STRING.__JS__'),$file,'.js');
        }
    }
    public function _less($tag){
        $file           = $tag['file'];
        if(C('LESS')){
            return sprintf('<script type="text/javascript" src="%s/%s/%s"></script>',dirname(C('TMPL_PARSE_STRING.__LESS__')),'js',$file);
        }else{
            return '';
        }
    }
    public function _jspath($tag){
        if(C('LESS')){
            return sprintf('"%s/%s"',C('TMPL_PARSE_STRING.__JAVASCRIPT__'),$tag['file']);
        }else{
            return sprintf('"%s/%s"',C('TMPL_PARSE_STRING.__JS__'),$tag['file']);
        }
    }
}