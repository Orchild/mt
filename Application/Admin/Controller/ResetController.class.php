<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
class ResetController extends ExtendController {
    protected $model;
    public function _initialize(){
        parent::_initialize();
        $this->model  = D('User');
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
            $username			= PUT('username');
            $map                = array();
            $map['username']	= array('like',"%$username%");
            $map['b.realname']	= array('like',"%$username%");
            $map['b.nickname']	= array('like',"%$username%");
            $map['b.id_number']	= array('like',"%$username%");
            $map['b.company']	= array('like',"%$username%");
            $map['_logic']		= 'or';
            $size  				= PUT('size',10);
            $page  				= PUT('p');
            $count 				= $this	->model
            							->alias('a')
							            ->join('__USER_INFO__ b on a.id=b.user_id','LEFT')->where($map)
							            ->where($map)
							            ->count();
            $items 				= $this ->model
							            ->alias('a')
							            ->join('__USER_INFO__ b on a.id=b.user_id','LEFT')
							            ->field('a.id,username,login_count,last_login_time,register_time,updated,status,portrait,realname,nickname,cat_id,name_cert,sex,age,birthday,id_number,email,company,work_status,alternative_phone')
							            ->where($map)
							            ->page($page,$size)
							            ->order('id desc')
							            ->select();
            $result['status']   = true;
            $result['count']    = $count;
            $result['size']     = $size;
            $result['page']		= $page;
            $result['items']	= $items;wlog('user', $result);
            $this->ajaxReturn($result);
    	}else{
        	$this->display();
    	}
    }
    /**
     * 密码重置
     */
    public function reset(){
    	if (IS_POST){    		
	    	$id 		= PUT('id');		//User_id
	    	$map 		= array();
	    	$map['id'] 	= $id;
	    	$user 		= $this->model->where($map)->find();
	    	$username 	= $user['username'];
 	    	$password 	= md5(substr($username, -6));
	    	$res 		= $this->model->where($map)->setField('password',$password);
	    	if ($res){
	    		$this->success('密码重置成功！');
	    	}else {
	    		$this->error('密码重置失败！');
	    	}
    	}
    }
}