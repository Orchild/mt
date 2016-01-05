<?php
namespace Tools\Controller;
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
    /**
     * 管理主界面
     */
    public function index(){
        $this->display();
    }
    /**
     * 成功信息
     * @param  string $msg 信息
     */
    protected function success($msg,$items,$key = 'result'){
        if(IS_AJAX){
            $result             = array();
            $result['success']  = true;
            $result['msg']      = $msg;
            if(isset($items))
                $result[$key]  = $items;
            $this->ajaxReturn($result);
        }else{
            parent::success($msg);
        }
    }
    /**
     * 错误信息
     * @param  string $msg 信息
     */
    protected function error($msg){
        $result             = array();
        $result['success']  = false;
        $result['msg']      = $msg;
        $this->ajaxReturn($result);
    }
}
?>