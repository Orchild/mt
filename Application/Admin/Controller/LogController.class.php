<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
class LogController extends ExtendController {
    protected $model;
    public function _initialize(){
        parent::_initialize();
        $this->model  = D('ActionLog');
    }
    /**
     * 主页面
     */
    public function index(){wlog('put', PUT());
    	//获取列表数据
    	$map['status'] 		=   array('gt', -1);
    	$list   			=   $this->pagination('ActionLog', $map);
    	$this->assign('_list', $list);
    	$this->display();
    }
    /**
     * 跳转
     */
    public function jump(){
    	$p 	 = I('p');
    	$url = U('Log/jump','',false);wlog('url', $url);
    	$this->redirect($url,array('p'=>$p));
    }
    /**
     * 数据列表
     */
    public function lists(){
    	if(IS_POST){
            $username           = PUT('username');
            $role_id            = PUT('role_id');
            $map                = array();
            $map['username']    = array('like',"%$username%");
            if($role_id)
                $map['role_id'] = $role_id;
            parent::page(array(
                'size'      => PUT('size',10),
                'page'      => PUT('p'),
                'rel'		=> true,
                'where'     => $map,
                'orderby'   => 'id',
            ));
    	}else{
        	$this->display();
    	}
    }
    /**
     * 删除数据
     */
    public function delete(){
        if(IS_GET){
            $map        = array();
            $map['id']  = PUT('id');
            $result     = $this->__delete($map);
            if($result['success'])
                $this->success('删除成功',U('index'));
            else
                $this->error('删除失败');
        }
    }
}