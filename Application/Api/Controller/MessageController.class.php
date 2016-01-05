<?php
namespace Api\Controller;
use Common\Controller\RestFulController;
class MessageController extends RestFulController {
    private $model;
    public function _initialize(){
        $this->model         = D('Message');
    }
    /**
     * 已读消息
     */
    public function read($user_id){
    	$map['user_id'] 		= $user_id;
    	$read_msg 				= M('MsgRead')->where($map)->field('msg_id')->select();
    	foreach ($read_msg as $value){
    		$read_msg_id[] 		= $value['msg_id'];
    	}
    	return $read_msg_id;
    }
    /**
     * 热门新闻
     */
    public function headmsg_post(){
    	$user_id 		= PUT('user_id');
        $model          = M('Headmsg');
        $result         = array();
        $result 		= $model->field('msg_id,title,image')->where('status=true')->order('sort,id')->select();
        $read_msg_id 	= self::read($user_id);//wlog('$read_msg_id', $read_msg_id);
        foreach ($result as &$value) {
            if($value['image'])
                $value['image'] 	= uri_file($value['image']);
            $value['url']       	= URI_ROUTE('message'.$value['msg_id'],true,true);
            $map 					= array();
            $map['id'] 				= $value['msg_id'];
            $value['type'] 			= D('Message')->where($map)->getField('type');
            if (in_array($value['msg_id'], $read_msg_id)){
            	$value['is_read'] 	= 1;
            }else {
            	$value['is_read'] 	= 0;
            }
        }
        $this->success('获取热门新闻列表成功',$result);
    }
    /**
     * 消息列表
     * @return array
     */
    public function lists_post(){
    	$user_id 			= PUT('user_id');
        $page               = PUT('page',1);
        $type 				= PUT('type');
        $pagesize           = 10;
        $where				= array();
        $where['user_id']	= $user_id;
        $cat_id				= M('UserInfo')->where($where)->getField('cat_id');
        $cat_id				= empty($cat_id)?0:$cat_id;      	
        $map                = array();
        $map['status']      = array('in','1,3');
        if ($cat_id==0&&$type==1){
        	$map['msg_cat'] = 0;
        }elseif ($type==1) {
        	$map['msg_cat'] = array('in','0,'.$cat_id);
        }
        if($page){
            $this->model->page($page,$pagesize);
        }
        $read_msg_id 		= self::read($user_id);
        $map['type'] 		= $type;
        $result     		= $this->model->field('id,title,image,video,remark,type,updated as time')->where($map)->order('sort desc,time desc')->select();
        foreach ($result as &$value) {
        	if ($value['msg_cat']==0){	        		
	            if($value['image'])
	                $value['image'] 	= uri_file($value['image']);
	            if ($type==1){            	
		            if (in_array($value['id'], $read_msg_id)){
		            	$value['is_read'] 	= 1;
		            }else {
		            	$value['is_read'] 	= 0;
		            }
	            }
	            $value['url']       	= URI_ROUTE('message'.$value['id'],true,true);
	            $value['video']     	= $value['video']?1:0;
	            $value['time']      	= time_format($value['time']);
        	}else{
        		unset($value);
        	}
        }
        $this->success('获取消息列表成功',$result);
    }
    /**
     * 详细
     * @return html
     */
    public function details_get(){
        $id     = I('get.id');
        if($id){
            $result     = $this->model->field('id,title,image,video,content,updated')->find($id);
            $this->assign('item',$result);
            $this->display('html:message');
        }
    }
    /**
     * 消息已读
     */
    public function hasRead_post() {
    	$model 				= M('MsgRead');
    	$data 				= array();
    	$data['user_id'] 	= PUT('user_id');
    	$data['msg_id'] 	= PUT('msg_id');
    	$data['status']		= 1;
    	if ($model->where($data)->select()){
    		$this->error('已读，勿重复提交');
    	}    	
    	$data['created']	= NOW_TIME;		
	    $result 			= $model->add($data);
    	if ($result){
    		$this->success('数据提交成功！');
    	}else{
    		$this->error('数据提交失败！');
    	}
    }
}