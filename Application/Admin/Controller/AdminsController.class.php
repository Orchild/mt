<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
class AdminsController extends ExtendController {
    protected $model;
    public function _initialize(){
        parent::_initialize();
        $this->model  = D('Admin');
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
     * 添加数据
     */
    public function add(){
        if(IS_POST){
            $data               = PUT();
            $data['role_id']    = PUT('role_id','');
            $result     = $this->__validate($data);
            if(!$result['success'])
                $this->error($result['msg']);
            elseif($this->__create())
                $this->success('添加成功');
            else
                $this->error('添加失败');
        }else{
            $this->display();
        }
    }
    /**
     * 编辑数据
     */
    public function edit(){
        if(IS_POST){
            $data               = PUT();
            $data['role_id']    = PUT('role_id','');
            $result     = $this->__validate($data);
            if(!$result['success'])
                $this->error($result['msg']);
            elseif($this->__update())
                $this->success('更新成功');
            else
                $this->error('更新失败');
        }elseif(IS_GET && PUT('get.id')){
            $this->model->field('id,username,role_id');
            $res    = $this->__find(PUT('get.id'));
            unset($res['password']);
            $this->ajaxReturn($res);
        }else{
            $this->display();
        }
    }
    public function role(){
        $model          = D('Role');
        $map['status']  = true;
        $map['issys']   = array('neq',1);
        $this->ajaxReturn($model->where($map)->field('id,name')->select());
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
    /**
     * 修改密码
     */
    public function modify(){
        if(IS_POST){
            $oldpassword    = PUT('oldpassword');
            $password       = PUT('password');
            if(empty($password)){
                $this->error('新密码不能为空');
            }
            if($password == $oldpassword){
                $this->error('新密码不能和旧密码相同');
            }
            if($password != PUT('repassword')){
                $this->error('确认密码不一致');
            }
            $user           = $this->model->field('id,username,password')->find(PUT('id'));
            if($user && $user['password'] == md5(PUT('oldpassword'))){
                $data               = array();
                $data['id']         = PUT('id');
                $data['password']   = $password;
                $result     = $this->__validate($data);
                if(!$result['success'])
                    $this->error($result['msg']);
                elseif($this->__update())
                    $this->success('新密码设置成功');
                else
                    $this->error('新密码设置失败');
            }else{
                $this->error('旧密码错误');
            }
        }elseif(IS_GET && PUT('get.id')){
            $this->model->field('id,username,role_id');
            $res    = $this->__find(PUT('get.id'));
            $this->ajaxReturn($res);
        }else{
            $this->display();
        }
    }
}