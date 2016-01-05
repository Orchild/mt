<?php
namespace Common\Controller;
use Think\Controller;
class CommonController extends Controller {
    protected $outputType       = array(
        'json'      => 'application/json',
        'html'      => 'text/html',
    );
    /**
     * 全局初始化
     */
    public function _initialize(){
        header('Content-Type: '.$this->outputType[IS_AJAX?'json':'html'].'; charset='.C('DEFAULT_CHARSET'));
    }
    /**
     * 空操作
     */
    public function _empty(){
        $this->error('亲，您访问的页面不存在！');
    }
}