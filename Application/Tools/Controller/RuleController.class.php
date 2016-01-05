<?php
namespace Tools\Controller;
class IndexController extends ExtendController {
	protected $model;
	public function _initialize(){
        parent::_initialize();
        $this->model  = D('Rule');
        $val    = get_class_methods('Tools\Controller\IndexController');
        dump($val);
    }
    /**
     * 数据列表
     */
    public function lists(){
    	if(IS_POST){
            $title          	= PUT('title');
            $map            	= array();
            $map['parent_id']   = 0;
            parent::page(array(
                'size'      => PUT('size',10),
                'page'      => PUT('p'),
                'where'     => $map,
                'orderby'   => 'sort desc,id desc',
            ));
    	}else{
        	$this->display();
    	}
    }
}