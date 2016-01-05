<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
class BasicController extends ExtendController {
	protected $model;
    public function _initialize(){
        parent::_initialize();
        $this->model  = D('Basic');
    }
    public function __call($method,$args){
        $ctrs   = array('about','share');
        $this->assign('index',array_search($method,$ctrs) + 1);
        $this->index();
    }
    protected function display(){
        parent::display('index');
    }
    public function index(){
    	if(IS_POST){
    		// $this->error(PUT());
    		$this->__saveOne();
    	}else{
	        // $this->redirect('about');
            $this->display();
	    }
    }
}