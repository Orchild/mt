<?php
namespace Api\Controller;
use Common\Controller\RestFulController;
use Org\Net\Http;
class IndexController extends RestFulController {
    public function index(){
    	// \Think\Build::buildController('Api','User');
    	// \Think\Build::buildModel('Api','User');
        // echo rand_string();
        $this->display();
    }
    public function user(){
        $statusArr = array(
        	'isNotify',
        	'isSound',
        	'isVibration',
        );
        $this->assign('status',$statusArr);
        $this->display();
    }
    public function testdata_get(){
        $this->error('GET数据',$_GET);
    }
    public function testdata_POST(){
        $this->error('POST数据',$_POST);
    }
    public function testdata_put(){
        $_PUT   = PUT();
        $this->error('PUT数据',$_PUT);
    }
    public function buildController_POST(){
        $name   = PUT('name');
        if(!$name)
            $this->error('控制器名称不能为空');
        \Think\Build::buildController('Api',$name);
        $this->success('控制器生成成功',$name);
    }
    public function buildModel_POST(){
        $name   = PUT('name');
        if(!$name)
            $this->error('模型名称不能为空');
        \Think\Build::buildModel('Api',$name);
        $this->success('模型生成成功',$name);
    }
    public function image_get(){
        echo img_output();
    }
}