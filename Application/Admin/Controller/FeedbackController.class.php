<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
class FeedbackController extends ExtendController {
	protected $model;
    public function _initialize(){
        parent::_initialize();
        $this->model  = D('Feedback');
    }
    public function index(){
    	if(IS_POST){
    		parent::page(array(
	            'size'      => PUT('size',10),
                'page'      => PUT('p'),
	            // 'where'     => array('status'=>I('get.status',1)),
	            'orderby'   => 'id desc',
	        ));
    	}else{
        	$this->display();
    	}
    }
    /**
     * 修改状态
     */
    public function status(){
        if(IS_POST){
            if($this->__status(PUT('id'),PUT('value'),PUT('field')))
                $this->success('状态修改成功');
            else
                $this->error('状态修改失败');
        }
    }
    /**
     * 删除数据
     */
    public function delete(){
        if(IS_POST){
            $map        = array();
            $map['id']  = PUT('id');
            $result     = $this->__delete($map);
            if($result['success'])
                $this->success('删除成功');
            else
                $this->error('删除失败');
        }
    }
}