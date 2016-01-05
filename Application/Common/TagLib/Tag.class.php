<?php
namespace Common\TagLib;
Class Tag extends Common{
    protected $tags =  array(
        'panel'         => array('attr'=>'title','level'=>1,'close'=>1),
        /* 按钮 */
        'btngroup'      => array('attr'=>'col','level'=>1,'close'=>1),
        'button'        => array('attr'=>'type','level'=>1,'close'=>1),
        'submit'        => array('attr'=>'value','close'=>0),
        /* 输入框 */
        'label'         => array('attr'=>'value','close'=>0),
        'text'          => array('attr'=>'name,value','close'=>0),
        'upload'        => array('attr'=>'name,value','close'=>0),
        // 'button'        => array('attr'=>'type','level'=>1,'close'=>1),
        /* 表单 */
        'form'          => array('attr'=>'action','level'=>1,'close'=>1),
        'item'          => array('attr'=>'label','level'=>1,'close'=>1),
        'col'           => array('attr'=>'col','level'=>1,'close'=>1),
        'buttons'       => array('attr'=>'col,offset','level'=>1,'close'=>1),

        'image'         => array('attr'=>'src','close'=>0),

        'linklib'       => array('attr'=>'file','close'=>0),
        'linkcss'       => array('attr'=>'file','close'=>0),
        'linkjs'        => array('attr'=>'file','close'=>0),
        'less'          => array('attr'=>'file','close'=>0),
        'jspath'        => array('attr'=>'file','close'=>0),
    );
    private function btn($tag,$content,$default = 'btn-default'){
        $tag['type']    = IIF($tag['type'],'button');
        switch($tag['size']){
            case 1:
                $default .= ' btn-xs';
                break;
            case 2:
                $default .= ' btn-sm';
                break;
            case 4:
                $default .= ' btn-lg';
                break;
        }
        return sprintf('<button%s%s>%s</button>',$this->cls($tag,'btn '.$default),$this->attr($tag),$content);
    }
    private function md($tag,$content,$cls){
        $tagName    = !empty($tag['tag'])?$tag['tag']:'div';
        return sprintf('<%s%s>%s</%1$s>',$tagName,$this->cls($tag,$cls),$content);
    }
    public function _panel($tag,$content){
        $icon       = !empty($tag['icon'])?$tag['icon']:'list';
        return $this->md(array(),
            $this->md(array(),
                $this->md(array(),
                    $this->md(array('class'=>'glyphicon-'.$icon),'','glyphicon')
                ,'pane-icon')
                .$this->md(array(),$tag['title'],'pane-title')
            ,'pane-header')
            .$this->md(array(),$content,'pane-content')
        ,'pane');
    }
    /* 按钮 */
    public function _btngroup($tag,$content){
        $tag['col']     = !empty($tag['col'])?$tag['col']:12;
        return sprintf('<div%s>%s</div>',$this->cls($tag,'btn-group'),$content);
    }
    public function _button($tag,$content){
        return $this->btn($tag,$content);
    }
    public function _submit($tag){
        $tag['type']    = IIF($tag['type'],'submit');
        return $this->btn($tag,$tag['text'],'btn-primary');
    }
    /* 表单 */
    public function _form($tag,$content){
        $tag['method']  = !empty($tag['method'])?$tag['method']:'POST';
        $error  = '<div class="col-md-12" ng-if="alert" ng-class="alert.cls" style="padding:8px 10px;">'
            .'<span class="glyphicon glyphicon-exclamation-sign"></span>'
            .'<span class="sr-only">Error:</span>'
            .'<span class="text">{{alert.msg}}</span></div>';
        return sprintf('<form%s%s>'.$error.'%s</form>',$this->cls($tag,'form-horizontal'),$this->attr($tag),$content);
    }
    public function _item($tag,$content){
        $cols           = !empty($tag['cols'])?explode('|',$tag['cols']):array(3,8);
        $tag['col']     = $cols[0];
        if(preg_match("/<col[^>]*?>/i",$content)){
            $val        = $content;
        }else{
            $val        = $this->md(array('col'=>$cols[1]),$content);
        }
        return sprintf('<div class="form-group"><label%s>%s</label>%s</div>',$this->cls($tag,'control-label'),$tag['label'],$val);
    }
    public function _col($tag,$content){
        return $this->md($tag,$content);
    }
    public function _buttons($tag,$content){
        $cls            = array();
        $cls['offset']  = !empty($tag['offset'])?$tag['offset']:0;
        $cls['col']     = !empty($tag['col'])?$tag['col']:12 - $tag['offset'];
        return $this->md(array('class'=>$tag['class']),$this->md($cls,$content),"form-group");
        // return sprintf('<div class="form-group">%s</div>',$this->md($tag,$content));
    }
    /* 输入框 */
    public function _label($tag){
        $value      = $tag['value'];
        return sprintf('<p%s>%s</p>',$this->cls($tag,'form-control-static'),$value);
    }
    public function _text($tag){
        if(isset($tag['empty'])){
            $tag['placeholder']  = $tag['empty'];
        }
        return sprintf('<input%s%s />',$this->cls($tag,'form-control'),$this->attr($tag));
    }
    private function group($tag,$content){
        $prefix     = "";
        $suffix     = "";
        $icons      = array();
        $addon      = !empty($tag['addon'])?explode('|',$tag['addon']):array();
        $glyph      = !empty($tag['glyph'])?explode('|',$tag['glyph']):array();
        $btns       = !empty($tag['btns'])?explode('|',$tag['btns']):array();
        if($tag['glyph']){
            foreach($glyph as $v){
                if($v)
                    $icons[]  = sprintf('<span class="input-group-addon"><span class="glyphicon glyphicon-%s"></span></span>',$v);
            }
        }
        if($tag['addon']){
            foreach($addon as $v){
                if($v)
                    $icons[]  = sprintf('<span class="input-group-addon">%s</span>',$v);
            }
        }
        if(count($icons)>0){
            $prefix     = $icons[0];
            $suffix     = $icons[1];
        }
        if($tag['btns']){
            foreach($btns as $k=>$v){
                if($v)
                    $btns[$k]  = $this->btn($tag,$tag['icon']?sprintf('<span class="glyphicon glyphicon-%s"></span>',$v):$v);
            }
            $suffix     = sprintf('<span class="input-group-btn">%s</span>', join('',$btns));
        }
        if($tag['btn']){
            $suffix     = sprintf('<span class="input-group-btn">%s</span>',$this->btn($tag,$tag['btn']));
        }
        // <span class="input-group-addon">%s</span>
        // <span class="input-group-btn">%s</span>
        return sprintf('<div class="input-group">%s%s%s</div>',$prefix,$content,$suffix);
    }
    public function _upload($tag){
        return $this->group($tag,$this->_text($tag));
    }
    public function _image($tag){
        return sprintf('<img%s%s src="%s" alt="%s" title="%4$s" />',$this->cls($tag),$this->attr($tag),$tag['src'],$tag['title']);
    }
}