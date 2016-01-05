<?php
namespace Common\TagLib;
use Think\Template\TagLib;
Class Hform extends TagLib{
	// 标签定义
    protected $tags	=  array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'btngroup'     => array('attr'=>'col','level'=>1,'close'=>1),
        'b1'            => array('attr'=>'type','level'=>1,'close'=>1),
        'b2'            => array('attr'=>'type','level'=>1,'close'=>1),
        'b3'            => array('attr'=>'type','level'=>1,'close'=>1),
        'b4'            => array('attr'=>'type','level'=>1,'close'=>1),
        'b5'            => array('attr'=>'type','level'=>1,'close'=>1),
        'b6'            => array('attr'=>'type','level'=>1,'close'=>1),
        
        'form'          => array('attr'=>'action','level'=>1,'close'=>1),
        'formerror'     => array('close'=>0),
        'formnode'      => array('attr'=>'col','level'=>1,'close'=>1),
        'formitem'      => array('attr'=>'label,col,cls','level'=>1,'close'=>1),
        'formsubmit'    => array('attr'=>'col,offset','close'=>0),
        'formcol'       => array('attr'=>'offset,col','level'=>1,'close'=>1),
        'static'        => array('attr'=>'value','close'=>0),
        'inputex'       => array('attr'=>'name,value,text','close'=>0),
        'checkbox'      => array('attr'=>'name,value','close'=>0),
        'checkboxaddon' => array('attr'=>'name,value,addon,align','close'=>0),
        'inputaddon'    => array('attr'=>'name,value,text,addon,align','close'=>0),
        'inputbtn'      => array('attr'=>'name,value,text,btn,align','close'=>0),
        'password'      => array('attr'=>'name,value,text','close'=>0),
        'submit'        => array('attr'=>'value','close'=>0),
        'btn'           => array('attr'=>'value','close'=>0),
        'linklib'       => array('attr'=>'file','close'=>0),
        'linkcss'       => array('attr'=>'file','close'=>0),
        'linkjs'        => array('attr'=>'file','close'=>0),
        'less'          => array('attr'=>'file','close'=>0),
        'jspath'        => array('attr'=>'file','close'=>0),
	);
    private function attr($tag){
        $attrs              = array(
            'type',
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
    private function cls($tag,$default){
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
    private function btn($tag,$content,$default = 'btn-default'){
        $tag['type']    = IIF($tag['type'],'button');
        return sprintf('<button%s%s>%s</button>',$this->cls($tag,'btn '.$default),$this->attr($tag),$content);
    }
    public function _btngroup($tag,$content){
        $tag['col']     = !empty($tag['col'])?$tag['col']:12;
        return sprintf('<div%s>%s</div>',$this->cls($tag,'btn-group btn-group-justified'),$content);
    }
    public function _b1($tag,$content){
        return $this->btn($tag,$content);
    }
    public function _b2($tag,$content){
        $tag['type']    = IIF($tag['type'],'submit');
        return $this->btn($tag,$content,'btn-primary');
    }
    public function _b3($tag,$content){
        return $this->btn($tag,$content,'btn-success');
    }
    public function _b4($tag,$content){
        return $this->btn($tag,$content,'btn-info');
    }
    public function _b5($tag,$content){
        return $this->btn($tag,$content,'btn-warning');
    }
    public function _b6($tag,$content){
        return $this->btn($tag,$content,'btn-danger');
    }




    public function _form($tag,$content){
        $action         = !empty($tag['action'])?$tag['action']:'';
        $method         = !empty($tag['method'])?$tag['method']:'POST';
        $enctype        = !empty($tag['enctype'])?sprintf(' enctype="%s"',$tag['enctype']):'';
        $ajax           = !empty($tag['ajax'])?sprintf(' ng-controller="%s"',$tag['ajax']):'';
        return sprintf('<form class="form-horizontal" method="%s" action="%s"%s%s>%s</form>',$method,$action,$enctype,$ajax,$content);
    }
    public function _formerror($tag){
        return '<div class="alert alert-danger col-md-10 col-md-offset-1" ng-show="second>-1"><span class="glyphicon glyphicon-exclamation-sign"></span><span class="sr-only">Error:</span><span class="text">提示信息</span><span class="wait" ng-show="second>-1">{{second}}秒</span></div>';
    }
    public function _formnode($tag,$content){
        $col        = !empty($tag['col'])?$tag['col']:12;
        $offset     = !empty($tag['offset'])?$tag['offset']:0;
        $cls        = array("col-md-$col");
        if($offset){
            array_push($cls,"col-md-offset-$offset");
        }
        if($tag['class']){
            array_push($cls,$tag['class']);
        }
        return sprintf('<div class="form-group"><div class="%s">%s</div></div>',implode(' ',$cls),$content);
    }
    public function _formitem($tag,$content){
        $label      = !empty($tag['label'])?$tag['label']:'';
        $cls        = !empty($tag['cls'])?explode('|',$tag['cls']):array(3,8);
        $col        = !empty($tag['col'])?$tag['col']:'true';
        $parseStr   .= '<div class="form-group">';
        if($label){
            $parseStr   .= '<label class="col-md-'.$cls[0].' control-label">'.$label.'</label>';
        }
        if($col == 'true'){
            $parseStr   .= '<div class="col-md-'.$cls[1].'">';
            $parseStr   .= $content;
            $parseStr   .= '</div>';
        }else{
            $parseStr   .= $content;
        }
        $parseStr   .= '</div>';
        return $parseStr;
    }
    public function _formsubmit($tag){
        $label      = $tag['label'];
        $offset     = !empty($tag['offset'])?$tag['offset']:3;
        $col        = !empty($tag['col'])?$tag['col']:3;
        $parseStr   .= '<div class="form-group form-buttons">';
        $parseStr   .= $this->_formcol(array(
            'col'       => 12 - $offset,
            'offset'    => $offset,
        ),$this->_submit(array(
            'col'       => $col,
            'value'     => $tag['value'],
        )));
        $parseStr   .= '</div>';
        return $parseStr;
    }
    public function _formcol($tag,$content){
        $col        = !empty($tag['col'])?$tag['col']:12;
        $offset     = !empty($tag['offset'])?$tag['offset']:0;
        $cls        = array("col-md-$col");
        if($offset){
            array_push($cls,"col-md-offset-$offset");
        }
        return sprintf('<div class="%s">%s</div>',implode(' ',$cls),$content);
    }
    public function _static($tag){
        $value      = $tag['value'];
        return sprintf('<p class="form-control-static">%s</p>',$value);
    }
    private function input($type,$name,$value,$text){
        //<span class="help-block">辅助文本</span>
        return sprintf('<input class="form-control" type="%s" name="%s" value="%s" placeholder="%s" />',$type,$name,$value?$value:'',$text);
    }
    public function _inputex($tag){
        $name       = $tag['name'];
        $value      = $tag['value'];
        $type       = !empty($tag['type'])?$tag['type']:'text';
        $text       = !empty($tag['text'])?$tag['text']:'';
        return $this->input($type,$name,$value,$text);
    }
    private function input_type($type,$name,$value){
        //<span class="help-block">辅助文本</span>
        return sprintf('<input type="%s" name="%s" value="%s" />',$type,$name,$value?$value:'');
    }
    public function _checkbox($tag){
        $name       = $tag['name'];
        $value      = $tag['value'];
        $type       = !empty($tag['type'])?$tag['type']:'checkbox';
        return $this->input_type($type,$name,$value);
    }
    public function _checkboxaddon($tag){
        $name       = $tag['name'];
        $value      = $tag['value'];
        $type       = !empty($tag['type'])?$tag['type']:'checkbox';
        $help       = !empty($tag['help'])?sprintf('<span class="help-block">%s</span>',$tag['help']):'';
        $addon      = explode('|',$tag['addon']);
        $glyph      = !empty($tag['glyph'])?explode('|',$tag['glyph']):array();
        $align      = !empty($tag['align'])?$tag['align']:'left';
        $_input     = $this->input_type($type,$name,$value,$text);
        foreach($glyph as $k => $v){
            if($v)
                $addon[$k]  = sprintf('<span class="glyphicon glyphicon-%s"></span>',$v);
        }
        if(count($addon)>1){
            return sprintf('<div class="input-group"><span class="input-group-addon">%s</span><div class="form-control">%s</div><span class="input-group-addon">%s</span></div>%s',$addon[0],$_input,$addon[1],$help);
        }elseif($align == 'left'){
            return sprintf('<div class="input-group"><span class="input-group-addon">%s</span><div class="form-control">%s</div></div>%s',$addon[0],$_input,$help);
        }elseif($align == 'right'){
            return sprintf('<div class="input-group"><div class="form-control">%s</div><span class="input-group-addon">%s</span></div>%s',$_input,$addon[0],$help);
        }
    }
    public function _inputaddon($tag){
        $name       = $tag['name'];
        $value      = $tag['value'];
        $type       = !empty($tag['type'])?$tag['type']:'text';
        $text       = !empty($tag['text'])?$tag['text']:'';
        $help       = !empty($tag['help'])?sprintf('<span class="help-block">%s</span>',$tag['help']):'';
        $addon      = explode('|',$tag['addon']);
        $glyph      = !empty($tag['glyph'])?explode('|',$tag['glyph']):array();
        $align      = !empty($tag['align'])?$tag['align']:'left';
        $_input     = $this->input($type,$name,$value,$text);
        foreach($glyph as $k => $v){
            if($v)
                $addon[$k]  = sprintf('<span class="glyphicon glyphicon-%s"></span>',$v);
        }
        if(count($addon)>1){
            return sprintf('<div class="input-group"><span class="input-group-addon">%s</span>%s<span class="input-group-addon">%s</span></div>%s',$addon[0],$_input,$addon[1],$help);
        }elseif($align == 'left'){
            return sprintf('<div class="input-group"><span class="input-group-addon">%s</span>%s</div>%s',$addon[0],$_input,$help);
        }elseif($align == 'right'){
            return sprintf('<div class="input-group">%s<span class="input-group-addon">%s</span></div>%s',$_input,$addon[0],$help);
        }
    }
    public function _inputbtn($tag){
        $name       = $tag['name'];
        $value      = $tag['value'];
        $text       = !empty($tag['text'])?$tag['text']:'';
        $help       = !empty($tag['help'])?sprintf('<span class="help-block">%s</span>',$tag['help']):'';
        $btn        = $tag['btn'];
        $glyph      = !empty($tag['glyph'])?sprintf('<span class="glyphicon glyphicon-%s"></span>',$tag['glyph']):'';
        $align      = !empty($tag['align'])?$tag['align']:'right';
        $_input     = $this->input('text',$name,$value,$text);
        $_button    = sprintf('<span class="input-group-btn"><button type="button" class="btn btn-default">%s%s</button></span>',$glyph,$btn);
        if($align == 'left'){
            return sprintf('<div class="input-group">%s%s</div>%s',$_button,$_input,$help);
        }elseif($align == 'right'){
            return sprintf('<div class="input-group">%s%s</div>%s',$_input,$_button,$help);
        }
    }
    public function _password($tag){
        $name       = $tag['name'];
        $value      = $tag['value'];
        $text       = !empty($tag['text'])?$tag['text']:'';
        return $this->input('password',$name,$value,$text);
    }
    private function _button($tag){
        $cls        = array("btn");
        if($tag['class']){
            array_push($cls,$tag['class']);
        }else{
            array_push($cls,"btn-default");
        }
        $attrs      = array();
        if($tag['attrs']){
            array_push($attrs,$tag['attrs']);
        }
        return sprintf('<button type="%s" class="%s"%s>%s</button>',$tag['type'],implode(' ',$cls),$attrs?' '.implode(' ',$attrs):'',$tag['value']);
    }
    public function _submit($tag){
        $cls            = array("btn-primary");
        if($tag['col']){
            array_push($cls,"col-md-$tag[col]");
        }
        if($tag['cls']){
            array_push($cls,$tag['cls']);
        }
        if($tag['class']){
            array_push($cls,$tag['class']);
        }
        $attrs          = array();
        if($tag['ng-click']){
            array_push($attrs,'ng-click="'.$tag['ng-click'].'"');
        }
        return $this->_button(array(
            'type'      => $tag['type']?$tag['type']:'submit',
            'class'     => implode(' ',$cls),
            'attrs'     => implode(' ',$attrs),
            'value'     => $tag['value'],
        ));
    }
    public function _btn($tag){
        $cls            = array("btn-default");
        if($tag['col']){
            array_push($cls,"col-md-$tag[col]");
        }
        if($tag['cls']){
            array_push($cls,$tag['cls']);
        }
        if($tag['class']){
            array_push($cls,$tag['class']);
        }
        $attrs          = array();
        if($tag['ng-click']){
            array_push($attrs,'ng-click="'.$tag['ng-click'].'"');
        }
        return $this->_button(array(
            'type'      => $tag['type']?$tag['type']:'button',
            'class'     => implode(' ',$cls),
            'attrs'     => implode(' ',$attrs),
            'value'     => $tag['value'],
        ));
    }
    public function _linklib($tag){
        $file           = $tag['file'];
        if(C('LESS')){
            return sprintf('<link rel="stylesheet/less" type="text/css" href="%s/%s/%s.%s">',dirname(C('TMPL_PARSE_STRING.__LESS__')),'static',$file,'less');
        }else{
            return sprintf('<link rel="stylesheet" type="text/css" href="%s/%s.%s" />',C('TMPL_PARSE_STRING.__STATIC__'),$file,'css');
        }
    }
    public function _linkcss($tag){
        $file           = $tag['file'];
        if(C('LESS')){
            return sprintf('<link rel="stylesheet/less" type="text/css" href="%s/%s.%s">',C('TMPL_PARSE_STRING.__LESS__'),$file,'less');
        }else{
            return sprintf('<link rel="stylesheet" type="text/css" href="%s/%s.%s" />',C('TMPL_PARSE_STRING.__CSS__'),$file,'css');
        }
    }
    public function _linkjs($tag){
        $file           = $tag['file'];
        if(C('LESS')){
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
            return sprintf('"%s/"',C('TMPL_PARSE_STRING.__JAVASCRIPT__'));
        }else{
            return sprintf('"%s/"',C('TMPL_PARSE_STRING.__JS__'));
        }
    }
}