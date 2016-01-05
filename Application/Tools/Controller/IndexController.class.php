<?php
namespace Tools\Controller;
class IndexController extends CommonController {
	protected $model;
	public function _initialize(){
        parent::_initialize();
        $this->model  = D('Menu','Service');
    }
    /**
     * 菜单列表
     */
    public function lists(){
    	if(IS_POST){
            $result = $this->model->query(PUT('put.index',0));
    		$this->ajaxReturn($result);
    	}else{
        	$this->display();
    	}
    }
    /**
     * 修改状态
     */
    public function status(){
        if(IS_POST){
            $data                   = array();
            $data[PUT('name')]      = PUT('value');
            $this->model->update($data,PUT('index',-1));
            $this->success('修改状态成功');
        }
    }
    /**
     * 向上修改排序
     */
    public function up(){
        if(IS_POST){
            $this->model->up(PUT('index',-1));
            $this->success('向上修改排序成功');
        }
    }
    /**
     * 向下修改排序
     */
    public function down(){
        if(IS_POST){
            $this->model->down(PUT('index',-1));
            $this->success('向下修改排序成功');
        }
    }
    /**
     * 删除菜单项
     */
    public function delete(){
        if(IS_POST){
            $this->model->delete(PUT('index',-1));
            $this->success('删除菜单项成功'.json_encode(PUT('index',-1)));
        }
    }
    /**
     * 添加菜单
     */
    public function add(){
    	if(IS_POST){
            $name   = PUT('name');
            if(!$name)
                $this->error('主菜单名称不能为空');
            $this->model->add(PUT());
            $this->success('添加菜单成功');
    	}else{
    		$this->display('edit');
    	}
    }
    /**
     * 修改菜单
     */
    public function edit(){
        $index  = I('get.idx',-1);
        if(IS_POST){
            $this->model->update(PUT(),$index);
            $this->success('修改菜单成功');
        }elseif($index > -1){
            $result = $this->model->find($index);
            $this->success('读取成功',$result);
        }else{
            $this->display('edit');
        }
    }
    /**
     * 生成菜单
     */
    public function build(){
        if(IS_POST){
            $this->model->build();
            $this->success('生成菜单成功');
        }
    }
    /**
     * 清除菜单
     */
    public function clear(){
        if(IS_POST){
            $this->model->removeAll();
            $this->success('清除菜单成功');
        }
    }
    public function buildController(){
        if(IS_POST){
            $name   = PUT('name');
            $module = PUT('module');
            if(!$name)
                $this->error('控制器名称不能为空');
            if(!$module)
                $this->error('模块名称不能为空');
            \Think\Build::buildController($module,$name);
            $this->success('控制器生成成功',$name);
        }
    }
    public function buildModel(){
        if(IS_POST){
            $name   = PUT('name');
            $module = PUT('module');
            if(!$name)
                $this->error('模型名称不能为空');
            if(!$module)
                $this->error('模块名称不能为空');
            \Think\Build::buildModel($module,$name);
            $this->success('模型生成成功',$name);
        }
    }
}