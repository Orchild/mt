<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
class RoleController extends ExtendController {
    protected $model;
    public function _initialize(){
        parent::_initialize();
        $this->model  = D('Role');
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
            // $map['title']   = array('like',"%$title%");
            parent::page(array(
                'size'      => PUT('size',10),
                'page'      => PUT('p'),
                'where'     => $map,
                'orderby'   => 'id asc',
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
     * 分配权限
     */
    public function permissions(){
        if(IS_POST){
            $action             = PUT('action');
            $result             = array();
            switch ($action) {
                case 'get':
                    $result['status']   = true;
                    $res                = $this->__find(PUT('id'));
                    $result['info']     = array(
                        'id'            => $res['id'],
                        'rules'         => $res['rules'],
                    );
                    break;
                case 'update':
                    $data           = PUT();
                    $data['rules']  = join(',',$data['rules']);
                    // $this->error($data);
                    if($this->__update($data)){
                        $result['status']   = true;
                        $result['msg']      = '权限数据更新成功';
                    }else{
                        $result['status']   = false;
                        $result['msg']      = $this->model->getError();
                    }
                    break;
                default:
                    $result['status']   = true;
                    $result['items']    = D('Rule')->where('parent_id=0')->relation(true)->order('sort,id')->field('id,name,rule')->select();
                    break;
            }
            // dump_json_format($result);
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