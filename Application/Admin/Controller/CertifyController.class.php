<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
use Common\Util\Jpush;
class CertifyController extends ExtendController {
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
            $where				= array();
            $where['_complex']	= $map;                       
            $where['name_cert']	= 0;            
            $result = array();
            $result['size'] 	= PUT('size',10);
            $result['page'] 	= PUT('p',1);
            $result['count']    = $this->model	->alias('a')
									            ->join('ds_user_info b ON a.id = b.user_id')
									            ->where($where)
									            ->count();
            $result['items']	= $this->model	->alias('a')
									            ->join('ds_user_info b ON a.id = b.user_id')
		 							            ->field('a.id,realname,username,id_number,register_time,updated')
									            ->page($result['page'],$result['size'])
									            ->where($where)
									            ->select();wlog('$result', $result);
			$this->ajaxReturn($result);
    	}else{
        	$this->display();
    	}
    }
    /**
     * 实名认证
     */
    public function certify(){
    	if (IS_POST){
    		$id 			= PUT('id');//wlog('cert_name', $id);
    		$map 			= array();
    		$map['user_id'] = $id;
    		$result 		= M('UserInfo')->where($map)->setField('name_cert',1);
    		if ($result){
    			$this->success('认证成功！');
    		}else{
    			$this->error('认证失败！');
    		}
    	}
    }
    public function pass(){
    	if (IS_POST){
    		$id 					= PUT('id');//wlog('cert_name', $id);
    		$audience				= array($id);
    		$map 					= array();
    		$map['user_id'] 		= $id;
    		$result 				= M('UserInfo')->where($map)->setField('name_cert',1);
    		if ($result){
    			$msg 				= array();
    			$msg['url']			= '';
    			$msg['content'] 	= '实名认证成功！';
    			$msg['remark'] 		= '恭喜您，您已经通过实名认证！';
    			Jpush::tag($msg, $audience);
    			$this->success('认证成功！');    			
    		}else{
    			$msg 				= array();
    			$msg['url']			= '';
    			$msg['content'] 	= '实名认证失败！';
    			$msg['remark'] 		= '抱歉，您未能通过实名认证！';
    			Jpush::tag($msg, $audience);
    			$this->error('认证失败！');
    		}
    	}
    }
    public function reject(){
    	if (IS_POST){
    		$id 					= PUT('id');//wlog('cert_name', $id);
    		$audience				= array($id);
    		$map 					= array();
    		$map['user_id'] 		= $id;
    		$result 				= M('UserInfo')->where($map)->setField('name_cert',2);
    		if ($result){
    			$msg 				= array();
    			$msg['url']			= '';
    			$msg['content'] 	= '实名认证失败！';
    			$msg['remark'] 		= '抱歉，您未能通过实名认证！请核对您提交的信息！';
    			Jpush::tag($msg, $audience);
    			$this->success('认证失败！');
    		}else{
    			$msg 				= array();
    			$msg['url']			= '';
    			$msg['content'] 	= '实名认证失败！';
    			$msg['remark'] 		= '抱歉，您未能通过实名认证！请核对您提交的信息！';
    			Jpush::tag($msg, $audience);
    			$this->error('认证失败！');
    		}
    	}
    }
    public function check(){
    	if (IS_POS){
    		wlog('check', PUT());
    	}    		
    }
}