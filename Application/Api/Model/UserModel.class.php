<?php
namespace Api\Model;
use Common\Model\RelationModel;
class UserModel extends RelationModel {
    protected $_auto = array(
        array('created','time',self::MODEL_INSERT,'function'),
        array('updated','time',self::MODEL_BOTH,'function'),
    );
	protected $_link = array(
		// array(
  //           'mapping_type'  => self::BELONGS_TO,
  //           'class_name'    => 'UserRole',
  //           'foreign_key'   => 'role_id',
  //           'mapping_name'  => 'item',
  //           'mapping_fields'=> 'name',
  //           'as_fields'     => 'name:type_name',
  //       ),
        array(
            'mapping_type'  => self::HAS_ONE,
            'class_name'    => 'UserInfo',
            'mapping_name'  => 'info',
        ),
    );
    /**
     * 验证字段
     * @param  array  $data 数据集
     * @return int 			错误编号
     */
    private function validate(array $data){
    	if(isset($data['username']) && empty($data['username'])) return -1;
    	if(isset($data['password']) && empty($data['password'])) return -2;
    	return 0;
    }
    /**
     * 检测字段
     * @param  string $name  字段值
     * @param  string $field 字段名
     * @return array
     */
    public function checkField($name,$field = "username",$rel = false){
    	if(is_array($name))
    		$map 		= $name;
    	else{
    		$map		= array();
    		$map[$field]= $name;
    	}
        if($rel){
            $this->relation($rel);
        }
    	$res 			= $this->where($map)->find();
    	// echo $this->_sql();
    	// dump(!is_null($res));
    	return $res;
    }
    /**
     * 更新用户登录信息
     * @param  int    $uid 用户ID
     */
    private function updateLogin(int $uid){
    	$data = array(
			'id'              	=> $uid,
    		'app_os'			=> PUT('app_os'),
			'login_count'		=> array('exp','login_count+1'),
			'last_login_time' 	=> NOW_TIME,
			'last_login_ip'   	=> get_client_ip(1),
		);
		$this->save($data);
    }
    /**
     * 检测手机是不是已经注册
     * @param  string $phone 手机
     * @return boolean       ture - 已注册，false - 未注册
     */
    public function checkMobile(string $phone){
    	return !is_null($this->checkField($phone));
    }
    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     *	       错误编号:
     *	       		-1:手机号码不能为空
     *	       		-2:密码不能为空
     *	       		-3:手机号码已经注册
     *	       		 0:注册失败
     *	       		>0:注册成功
     */
    public function register(string $username,string $password){
    	if(empty($username)) return -1;
    	if(empty($password)) return -2;
    	if($this->checkMobile($username)) return -3;
    	$data['username']		= $username;
    	$data['password']		= $password;
    	$data['app_os']			= PUT('app_os');
    	$data['register_ip']	= get_client_ip(1);
    	$data['register_time']	= NOW_TIME;
    	$data['updated']		= NOW_TIME;
    	return IIF($this->add($data),0);
    }
    /**
     * 用户登录认证
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     *	       错误编号:
     *	       		-1:手机号码不能为空
     *	       		-2:密码不能为空
     *	       		-3:手机号码不存在
     *	       		-4:手机号码被禁用
     *	       		 0:密码错误
     *	       		>0:登录成功，返回用户ID
     */
    public function login(string $username,string $password){
    	if(empty($username)) return -1;
    	if(empty($password)) return -2;
    	$res	= $this->checkField($username);
    	if(empty($res)) return -3;
    	if(!($res['status'] & 1)) return -4;
    	if($res['password'] != $password) return 0;
    	$this->updateLogin($res['id']);
    	return $res['id'];
    }
    /**
     * 获取用户信息
     * @param  int    $uid  用户ID
     * @return array 		用户信息
     */
    private $infoArr  	= array(
    	'userId'		=> array('id','int'),
    );
    private $statusArr  = array('isNotify','isSound','isVibration');
    public function info(int $uid){
        $res        = $this->relation(true)->find($uid);
        if(empty($res['info'])){
            $res['info']= array();
            $model      = M('UserInfo');
            $fields     = $model->getDbFields();
            foreach($fields as $v){
                $res['info'][$v] = "";
            }
        }
    	unset($res['info']['id']);
    	unset($res['info']['user_id']);
    	$data 		= array(
    		'uid'		=> $res['id'],
    		'username'	=> $res['username'],
    	);
    	$result     = array_merge($data,$res['info'],status($res['status'],$this->statusArr));
        if(empty($result['sex'])){
            $result['sex']  = 0;
        }else{
            $result['sex']  = (int)$result['sex'];
        }
        if (!empty($result['id_number'])){
        	$result['id_number'] = hideStr($result['id_number'],4,6,1);
        }
        if(!empty($result['portrait'])){
            $result['portrait'] = uri_file($result['portrait']);
        }
        if(!empty($result['photo'])){
            $result['photo']    = uri_file($result['photo']);
        }
    	return $result;
    }
    public function updateInfo(array $inputArr){
    	$inputArr['status'] = status($inputArr['status'],$this->statusArr,$inputArr);
    	$inputArr['info']   = $inputArr;
    	$this->create($inputArr);
    	unset($inputArr['info']['id']);
//     	wlog('info', $inputArr);
    	return $this->relation('info')->data($inputArr)->save();
    }  
    public function updateField(array $data,$field = "password"){
    	return $this->where('id='.$data['id'])->setField($field,$data[$field]);
    }
    private function saveInfo($uid,$info = array()){
        $data               = array();
        $data['id']         = $uid;
        $data['updated']    = NOW_TIME;
        $data['info']       = $info;
        return $this->relation('info')->save($data);
    }
    public function photo(int $uid){
        $res    = uploadOne(array(
            'dir'   => 'users/photos',
            'key'   => 'image',
            'thumb' => array(300,300),
            'save'  => 'photo_'.$uid,
        ));
        if($res['success']){
            $this->saveInfo($uid,array(
                'photo'         => $res['image'],
            ));
        }
        return $res;
    }
    public function portrait(int $uid){
         $res    = uploadOne(array(
            'dir'   => 'users/portrait',
            'key'   => 'image',
            'thumb' => array(100,100),
            'save'  => 'portrait_'.$uid,
            'ext'   => 'png',
        ));
        if($res['success']){
            $this->saveInfo($uid,array(
                'portrait'      => $res['image'],
            ));
        }
        return $res;
    }
    public function delete($phone){
        $res    = $this->checkField($phone);
        $flag   = parent::delete($res['id']);
        if($flag){
            M('UserInfo')->where('user_id='.$res['id'])->delete();
        }
        return $flag;
    }
}