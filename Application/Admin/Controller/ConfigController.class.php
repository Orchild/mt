<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
use Common\Util\ApkParser;
class ConfigController extends ExtendController {
    public function index(){
    	if(IS_POST){
	    	$data	= PUT();
	    	if($data){
	    		// $this->success(PUT());
		    	$key 	= $data['key'];
		    	unset($data['key']);
		    	$res 	= config($key,$data);
	    		$this->success('保存成功');
	    	}else{
	    		$this->error('表单提交错误');
	    	}
    	}elseif(IS_GET && I('get.key')){
    		$res 	= config(I('get.key'));
    		$this->ajaxReturn($res);
    	}else{
        	$this->display();
    	}
    }
    public function version(){
    	if(IS_POST){
	    	$data	= PUT();
	    	if($data){
		    	$key 	= $data['key'];
		    	unset($data['key']);
		    	$data['updated']	= NOW_TIME;
		    	$data['description']	= addcslashes($data['description'],"\r\n");
		    	$res 	= config($key,$data);
	    		$this->success('保存成功');
	    	}else{
	    		$this->error('表单提交错误');
	    	}
    	}else{
        	$this->display();
    	}
    }
    public function upload(){
    	if(IS_POST){
    		$res    			= uploadOne(array(
    			'dir'			=> '',
    			'exts'			=> 'apk',
    			'save'			=> 'WeiLang',
    		));
    		if($res['success']){
    			$result 			= array();
	            $result['status']	= true;
	            $result['info']		= $res['file'];
    			$appObj     		= new ApkParser();
	            if($appObj->open($res['file'])){
		            $re['package']		= $appObj->getPackage();
		            $re['versionname']	= $appObj->getVersionName();
		            $re['versioncode']	= $appObj->getVersionCode();
		            $re['appname']		= '洞头人才'.' v'.$re['versionname'];
		        }
	            $re['size']			= $res['size'];
	            $data               = config('version');
	            $result				= array_merge($result,$data,$re);
                $this->ajaxReturn($result);
            }else{
                $this->error($res['msg']);
            }
    	}
    }
}