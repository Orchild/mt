<?php
namespace Api\Controller;
use Common\Controller\RestFulController;
use Common\Util\Luosimao;
class UserController extends RestFulController {
	private $model;
	private $statusArr  = array('isNotify','isSound','isVibration');
	public function _initialize(){
        $this->model	= D('User');
    }
    /**
     * 获取验证码
     * @param string username 手机号码
     * @param int forget 忘记密码的手机号码
     */
    public function verifily_post(){
        $telphone       = PUT('username');
        $forget         = PUT('forget');
        $type 			= PUT('type');
        if($forget){
            if(empty($forget)){
               $this->error('手机号码不能为空');
            }
            if(!$this->model->checkMobile($forget))
               $this->error('该手机号码没有注册');
            $telphone 	= $forget;

        }else{
            if(empty($telphone)){
               $this->error('手机号码不能为空');
            }
            if($this->model->checkMobile($telphone))
    	       $this->error('该手机号码已经注册');
        }
        $verifily       = create_password();
        // session('verifily',$verifily);
        // $this->success('验证码发送成功',$verifily);
        // exit;
        //$result         = curl(C('SMS.sms_url'),C('SMS.sms_key'),array('mobile'=>$telphone,'message'=>'验证码：'.$verifily.C('SMS.sms_company')));
        $msg 			= '验证码：'.$verifily.C('SMS.sms_company');        
        $result 		= Luosimao::send($type,$telphone,$verifily);
        if ($result['error']==0){
        	$this->success('验证码发送成功', $verifily);
        }else {
        	$this->error($result['msg']);
        }
//         $res            = json_decode($result,true);
//         if($res['error']==0){
//             session('verifily',$verifily);
//     	   $this->success('验证码发送成功',$verifily);
//         }else{
//             $this->error('验证码发送失败');
//         }
    }
    /**
     * 用户注册
     * @param string username 手机号码
     * @param string password 用户密码
     * @param int app_os 用户系统：0、安卓；1苹果。 
     */
    public function register_post(){
    	$username       = PUT('username');
        $password       = PUT('password');
        $uid            = $this->model->register($username,$password);
        switch($uid){
        	case -1:
        		$this->error('手机号码不能为空');
        		break;
        	case -2:
        		$this->error('密码不能为空');
        		break;
        	case -3:
        		$this->error('手机号码已经注册');
        		break;
        	case 0:
        		$this->error('注册失败');
        		break;
        	default:
        		$this->success('注册成功');
        		break;
        }
    }
    /**
     * 用户登陆
     * @param string username 手机号码
     * @param string password 用户密码
     * @param int app_os 用户系统：0、安卓；1苹果。 
     */
    public function login_post(){
        $username       = PUT('username');
        $password       = PUT('password');
        $res 			= $this->model->login($username,$password);
        switch ($res){
        	case -1:
        		$this->error('手机号码不能为空');
        		break;
        	case -2:
        		$this->error('密码不能为空');
        		break;
        	case -3:
        		$this->error('手机号码不存在');
        		break;
        	case -4:
        		$this->error('手机号码被禁用');
        		break;
        	case 0:
        		$this->error('密码错误');
        		break;
        	default:
        		$result 			= $this->model->info($res);//wlog('result', $result);
        		$map 				= array();
        		$map['id'] 			= $result['cat_id'];
        		if ($map['id']==0){
        			$result['cat_name'] = '未分类';
        		}else {        			
	        		$cat 				= M('Category')->where($map)->find();//wlog('cat', $cat);
	        		$result['cat_name'] = $cat['title'];
        		}
        		$this->success('登录成功',$result);
        		break;
        }
    }
    /**
     * 用户忘记密码
     * @param string username 手机号码
     * @param string password 用户密码
     */
    public function forget_post(){
    	$username       = PUT('username');
        $password       = PUT('password');
        if(empty($username))
            $this->error('手机号码不能为空');
        if(empty($password))
            $this->error('密码不能为空');
        $res 			= $this->model->checkField($username);
        if(!$res)
			$this->error('该手机号码没有注册');
		else{
			$data 		= array(
				'id'		=> $res['id'],
				'password'	=> $password,
			);
            $this->model->save($data);
			$this->success('密码重置成功');
		}
    }
    /**
     * 用户修改密码
     * @param string username 手机号码
     * @param string password 旧密码
     * @param string newpassword 新密码
     */
    public function modifypassword_post(){
        $username       = PUT('username');
        $password       = PUT('password');
        $newpassword    = PUT('newpassword');
        if(empty($username))
            $this->error('手机号码不能为空');
        if(empty($password))
            $this->error('旧密码不能为空');
        if(empty($newpassword))
            $this->error('新密码不能为空');
        $res 			= $this->model->checkField(array('username'=>$username,'password'=>$password));
        if(!$res)
			$this->error('旧密码错误');
		else{
			$data 		= array(
				'id'		=> $res['id'],
				'password'	=> $newpassword,
			);
			if($this->model->save($data))
				$this->success('密码修改成功');
			else
				$this->error('密码修改失败');
		}
    }
    /**
     * 用户修改手机绑定
     * @param string username 手机号码
     * @param string password 用户密码
     * @param string telphone 新手机号码
     */
    public function modifybinding_post(){
        $username       = PUT('username');
        $password       = PUT('password');
        $telphone       = PUT('telphone');
        if(empty($username))
            $this->error('手机号码不能为空');
        if(empty($password))
            $this->error('密码不能为空');
        if(empty($telphone))
            $this->error('新手机号码不能为空');
        if(!is_null($this->model->checkField($telphone)))
            $this->error('该手机号码已注册');
       $res 			= $this->model->checkField(array('username'=>$username,'password'=>$password));
        if(!$res)
			$this->error('手机号码或密码错误');
		else{
			$data 		= array(
				'id'		=> $res['id'],
				'username'	=> $telphone,
			);
			if($this->model->save($data))
				$this->success('手机号码修改成功');
			else
                $this->error('手机号码修改失败');
        }
    }
    /**
     * 用户信息修改
     * @param string username 手机号码
     * @param string password 用户密码
     * @param string 
     * @param string sex 性别 0：先生 ；1：女士 
     * @param string age 年龄
     * @param string email 电子邮箱
     */
//     public function modify_post(){
//         $username       = PUT('username');
//         $password       = PUT('password');
//         if(empty($username))
//             $this->error('手机号码不能为空');
//         if(empty($password))
//             $this->error('密码不能为空');
//         $res 			= $this->model->checkField(array('username'=>$username,'password'=>$password));
//         if(!$res)
// 			$this->error('手机号码或密码错误');
// 		else{
// 			$data			= PUT();
// 			$data['id']		= $res['id'];
// 			$data['status']	= $res['status'];
// 			if($this->model->updateInfo($data)){
// 				$info 	= $this->model->info($res['id']);
// 				$this->success('用户信息修改成功',$info);
// 			}else{
// 				$this->error('用户信息修改失败');
// 			}
// 		}
//     }

	public function modify_post(){
		$username       = PUT('username');
		$password       = PUT('password');
		if(empty($username))
		$this->error('手机号码不能为空');
		if(empty($password))
			$this->error('密码不能为空');
		$res 			= $this->model->checkField(array('username'=>$username,'password'=>$password),'',true);
// 		wlog('res', $res);
		if(!$res)
			$this->error('手机号码或密码错误');
		else{
			$data			= PUT();//wlog('modify_data', $data);
			$data['id']		= $res['id'];
			$data['status']	= $res['status'];
			if ($res['info']['name_cert']==1){
				unset($data['realname']);
				unset($data['id_number']);
			}else {
				$data['name_cert'] = 0;
			}
			
			if($this->model->updateInfo($data)){
				$info 	= $this->model->info($res['id']);
				$this->success('用户信息修改成功',$info);
			}else{
				$this->error('用户信息修改失败');
			}
		}
	}	

    /**
     * 实名认证
     * @param string realname 真实姓名
     * @param string id_number 身份证号码
     */
//     public function certify_post(){
//     	$username       = PUT('username');
//     	$password       = PUT('password');
//     	if(empty($username))
//     		$this->error('手机号码不能为空');
//     	if(empty($password))
//     		$this->error('密码不能为空');
//     	$res 			= $this->model->checkField(array('username'=>$username,'password'=>$password));
//     	if(!$res)
//     		$this->error('手机号码或密码错误');
//     	else{
//     		$data		= PUT();
//     		$result 	= $this->model->save($data);
//     		if($result){
//     			$this->success('数据写入成功！');
//     		}else{
//     			$this->error('数据写入出错！');
//     		}
//     	}
//     }
    /**
     * 上传头像
     * @param string username 手机号码
     * @param string password 用户密码
     * @param string portrait 图片
     */
    public function portrait_post(){
        $username       = PUT('username');
        $password       = PUT('password');
        if(empty($username))
            $this->error('手机号码不能为空');
        if(empty($password))
            $this->error('密码不能为空');
        $res 			= $this->model->checkField(array('username'=>$username,'password'=>$password));
        if(!$res)
			$this->error('手机号码或密码错误');
		else{
			$result 	= $this->model->portrait($res['id']);
			if($result['success']){
				// $this->success($result['msg'],uri_file($result['file']));
                $info   = $this->model->info($res['id']);
                $this->success($result['msg'],$info);
			}else{
				$this->error($result['msg']);
			}
		}
    }
    /**
     * 上传照片
     * @param string username 手机号码
     * @param string password 用户密码
     * @param string portrait 图片
     */
    public function photo_post(){
        $username       = PUT('username');
        $password       = PUT('password');
        if(empty($username))
            $this->error('手机号码不能为空');
        if(empty($password))
            $this->error('密码不能为空');
        $res            = $this->model->checkField(array('username'=>$username,'password'=>$password),'',true);
        if(!$res)
            $this->error('手机号码或密码错误');
        elseif(empty($res['info']['photo'])){
            $result     = $this->model->photo($res['id']);
            if($result['success']){
                $info   = $this->model->info($res['id']);
                $this->success($result['msg'],$info);
            }else{
                $this->error($result['msg']);
            }
        }else{
            $this->error('该照片仅可上传一次，不可重复上传！');
        }
    }
    /**
     * 版本更新
     * @param string versionCode 版本代码
     */
    public function version_post(){
        $data    = config("version");
        $result                 = array();
        //  || $data['versionname'] != PUT('versionName')
        if($data['versioncode'] > PUT('versionCode')){
            $result                 = array(
                'title'             => $data['appname'],
                'updateDescription' => $data['description'],
                'updateTime'        => date ('Y-m-d',$data['updated']),
                'downloadURL'       => uri_file($data['path']),
            );
            $this->success('需要更新',$result);
        }else{
            $this->error('无需更新');
        }
    }
    /**
     * 删除数据
     */
    public function delete_POST(){
        if(C('ADD')){
            $name       = PUT('username');
            if(!$name)
                $this->error('手机号码不能为空');
            $result     = array();
            if($this->model->delete($name)){
                $result['phone'] = $name;
                $this->success('删除成功',$result);
            }else{
                $this->error('删除失败');
            }
        }
    }
}