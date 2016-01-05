<?php
namespace Api\Controller;
use Common\Controller\RestFulController;
class CategoryController extends RestFulController {
	private $model;
    public function _initialize(){
        $this->model        = D('Category');
    }
    public function list_post(){
        $result     		= $this->model->field('id,title')->where('status=1')->select();
        $count 				= sizeof($result);
        $result[$count] 	= array('id'=>'0','title'=>'未分类');
        $this->success('获取用户/消息列表成功',$result);
    }
}