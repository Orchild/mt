<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
use Common\Util\Jpush;
use Common\Util\Luosimao;
class MsgReadController extends ExtendController {
	protected $model;
    public function _initialize(){
        parent::_initialize();
        $this->model  = D('Message');
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
            $map['type']	= 1;
            $map['title']   = array('like',"%$title%");
            parent::page(array(
            	'after_select'  => 'after_select',
                'size'      	=> PUT('size',10),
                'page'      	=> PUT('p'),
                'where'     	=> $map,
                'orderby'   	=> 'sort desc,id desc',
            ));
    	}else{
        	$this->display();
    	}
    }
    protected function after_select(&$items){
    	foreach ($items as &$value){
    		$msg_id	 			= $value['id'];
    		$map['status']  	= array('in','1,3,5,7,9,11,13,15');
    		if ($value['msg_cat']==0){ 
	    		$total			= M('User')->where($map)->count();
    		}else{
    			$map['cat_id']	= $value['msg_cat'];
	    		$total			= M('UserInfo')->where($map)->count();
    		}
    		$where['status']	= 1;
    		$where['msg_id']	= $msg_id;
    		$read				= M('Msg_read')->where($where)->select();
    		$read_count			= 0;
    		if ($read){    			
	    		foreach ($read as $v){
	    			unset($map);
	    			$map			= array();
	    			$map['user_id']	= $v['user_id'];
	    			$map['cat_id']	= $value['msg_cat'];
	    			$res 			= M('UserInfo')->where($map)->find();
	    			if ($res){
	    				++$read_count;wlog('count', $read_count);
	    			}
	    		}
    		}
    		$value['read']		= $read_count;
    		$value['unread']	= $total - $read_count;
    		if ($value['unread']==0){
    			$value['disable']=true;
    		}
    	}
    }
    /**
     * 查看详情
     */
    public function view(){
    	if (IS_POST){
	    	$user 				= D('User');
	    	$id 				= PUT('id');
		    $where['status'] 	= array('in','1,3');
		    $where['msg_id']	= $id;
		    $read 				= M('Msg_read')->where($where)->select();
		    if ($read){	
			    foreach ($read as $value){
			    	$read_user_id  .= $value['user_id'].',';
			    }
		    }else {
		    	$read_user_id 	= '';
		    }	    
     		$map['status'] 		= array('in','1,3,5,7,9,11,13,15');
     		unset($where['msg_id']);
     		$where['id']		= $id;
     		$cat_id				= $this->model->where($where)->getField('msg_cat');
     		if ($cat_id==0){
     			$map_cat 		= '1=1';
     		}else {
     			$map_cat 		= 'b.cat_id='.$cat_id;
     		}
    		$result['size']     = PUT('size', 10);
    		$result['page']     = PUT('p', 1);
     		$map['a.id'] 		= array('not in',$read_user_id);
     		$result['count']    = $user ->alias('a')
     									->join('ds_user_info b ON a.id = b.user_id')
    									->where($map_cat)
    									->where($map)
    									->count();
    		$result['items']	= $user ->alias('a')
    									->join('ds_user_info b ON a.id = b.user_id')
    									->field('a.id,realname,username,company')
     									->page($result['page'],$result['size'])
    									->where($map_cat)
    									->where($map)
    									->select();
    		//wlog('$result[items]', $result['items']);
    		 
    		$this->ajaxReturn($result);
    	}else{
    		$this->display();
    	}
    }
    /**
     * 通知
     */
    public function notify(){
    	if (IS_POST){    		
	    	$id 							= PUT('id');		//必读消息id
	    	$where['msg_id']				= $id;
	    	$read_user 						= M('MsgRead')->where($where)->field('user_id')->select();
	    	$read_user_id 					= array();
	    	foreach ($read_user as $value){
	    		$read_user_id[] 			= $value['user_id'];	//已读人员列表
	    	}
	    	$message = $this->__find($id);
	    	$map['status'] 					= array('in','1,3,5,7,9,11,13,15');
	    	if ($message['msg_cat']==0){
	    		$user 						= D('user')->field('id')->where($map)->select();
	    		foreach ($user as &$value){
	    			if (!in_array($value['id'], $read_user_id)){
	    				$unread_user_id[] 	= $value['id'];
	    			}
	    		}
	    	}else {
	    		$map['cat_id'] 				= $message['msg_cat'];
	    		$user 						= M('UserInfo')->field('user_id')->where($map)->select();
	    		foreach ($user as $value){
	    			if (!in_array($value['user_id'], $read_user_id)){
	    				$unread_user_id[] 	= $value['user_id'];
	    			}
	    		}
	    	}
	    	//wlog('id', $id);
	    	//wlog('$read_user_id', $read_user_id);
	    	//wlog('$unread_user_id', $unread_user_id);
	    	$msg							= array();
	    	$msg['url'] 					= URI_ROUTE('message'.$message['id'],true,true);
	    	$msg['remark']					= $message['remark'];
        	$msg['content'] 				= $message['title'];
        	$result 						= Jpush::tag($msg, $unread_user_id);
        	if ($result['success']){
        		$this->success('推送成功！');
        	}else {
        		$this->success('推送失败！');
        	}
    	}
    }
    /**
     * 短信通知
     */
    public function smsNotify(){
    	if (IS_POST){
    		$user_id 			= PUT('user_id');
    		$msg_id 			= PUT('msg_id');
    		$map				= array();
    		$map['id'] 			= $user_id;
    		$user 				= D('User')->where($map)->find();
    		$where['id'] 		= $msg_id;
    		$message 			= D('Message')->where($where)->find();
    		$title 				= $message['title'];//wlog('$title', $title);
    		$telephone 			= $user['username'];//wlog('$username', $telephone);
    		$msg='111';
    		$msg 				= "温馨提醒：请尽快完成《".$title."》消息的阅读。【洞头人才】";
     		$res 				= Luosimao::send(3,$telephone,$msg);//wlog('sms', $res);
    		if ($res['error']==0){
    			$this->success('短信通知完成');
    		}else {
    			$this->error($res['msg']);
    		}
    	}
    }
    /**
     * 消息类别
     */
	public function category(){
        $result         = array();
        $model          = D('Category');
        $map['status']  = true;
        $result['items']= $model->where($map)->field(array('id'=>'key','title'=>'val'))->select();
        $this->ajaxReturn($result);
    }
}