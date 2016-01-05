<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
class CategoryController extends ExtendController {
	protected $model;
    public function _initialize(){
        parent::_initialize();
        $this->model  = D('Category');
    }
    /**
     * 主页面
     */
    public function index(){
        $this->display();
    }
    /**
     * 数据列表
     */
    public function lists(){
    	if(IS_POST){
            $title          = PUT('title');
            $map            = array();
            $map['title']   = array('like',"%$title%");
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
    /**
     * 添加数据
     */
    public function add(){
    	if(IS_POST){
            $result     = $this->__validate(PUT());
            if(!$result['success'])
                $this->error($result['msg']);
            elseif($this->__create())
                $this->success('添加成功');
            else
                $this->error('添加失败');
    	}else{
        	$this->display('edit');
    	}
    }
    /**
     * 编辑数据
     */
    public function edit(){
    	if(IS_POST){
            $result     = $this->__validate(PUT());
            if(!$result['success'])
                $this->error($result['msg']);
            elseif($this->__update())
                $this->success('更新成功');
            else
                $this->error('更新失败');
    	}elseif(IS_GET && PUT('get.id')){
            $res    = $this->__find(PUT('get.id'));
            $this->ajaxReturn($res);
        }else{
        	$this->display();
    	}
    }
    /**
     * 修改排序
     */
    public function sort(){
        if(IS_POST){
            $id     = PUT('id');
            if($id>0){
                if($this->__sort($id,PUT('value'),PUT('field')))
                    $this->success('修改排序成功');
                else
                    $this->error('修改排序失败');
            }else{
                $this->__init_sort();
                $this->success('修改排序失败');
            }
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
            $result     = $this->__delete($map,false,'image');
            if($result['success']){
                $this->success('删除成功');
            }else
                $this->error('删除失败');
        }
    }
}