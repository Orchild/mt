<?php
namespace Api\Controller;
use Common\Controller\RestFulController;
class FeedbackController extends RestFulController {
    public function index_post(){
        $content = PUT('content');
        if(!empty($content)){
            $model              = M('Feedback');
        	$data['content']   = $content;
        	$data['created']   = NOW_TIME;
        	$data['status']    = 1;
        	$result = $model->add($data);
        }else{
            $this->error('反馈内容不能为空！');
        }
        if($result){
           	$this->success('反馈提交成功！');
        } else {
            $this->error('反馈提交失败！');
        }
    }
}