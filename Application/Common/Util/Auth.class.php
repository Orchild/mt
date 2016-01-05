<?php
namespace Common\Util;
use Think\Session\Driver;
class Auth{
	protected $_config		= array(
		'AUTH_ON'			=> true,				//认证开关
		'AUTH_TYPE'			=> 1, 					//认证方式，1为时时认证；2为登录认证。
		'AUTH_USER'			=> '__ADMIN__',			//用户信息表
		'AUTH_ROLE'			=> '__ADMIN_ROLE__',	//角色数据表名
		// 'AUTH_ROLE_USER'	=> 'ds_role_user',		//角色用户明细表
		'AUTH_RULE'			=> '__ADMIN_RULE__',	//权限规则表
		// 'AUTH_ROLE_RULE'	=> 'ds_role_rule',		//角色权限规则明细表
		'AUTH_CONTROLLER'	=> '',					//需要认证的控制器
		'AUTH_NO_CONTROLLER'=> '',					//不需要认证的控制器
		'AUTH_ACTION'		=> '',					//需要认证的操作
		'AUTH_NO_ACTION'	=> '',					//不需要认证的操作
	);
	/**
	 * 用户登陆
	 * @param  string $userName 用户名
	 * @param  string $passWord 密码
	 * @return int          是否登陆成功
	 *         1 	= 用户不存在
	 *         2 	= 用户已禁用
	 *         3 	= 角色已禁用
	 *         4 	= 密码错误
	 *         5 	= 登陆成功
	 */
	static public function login(string $userName,string $passWord){
		$map['username']	= $userName;
		$pwd 				= md5($passWord);
		$model 				= M()->table(C('USER_AUTH_TABLE'));
		$roleTableName		= C('USER_AUTH_ROLE');
		$model->alias('A')->join("LEFT JOIN $roleTableName B ON B.id = A.role_id");
        $model->field('A.*,B.name as role_name,B.issys as role_issys,B.status as role_status');
		$model->where($map);
		$user 				= $model->find();
		if(empty($user)){
			return 1;
		}
		if($user['status']==false){
			return 2;
		}
		if($user['role_status']==false){
			return 3;
		}
		if($user['password'] != $pwd){
			return 4;
		}
		self::saveLoginInfo($user['id']);
		$auth = array(
            'id'             	=> $user['id'],
            'username'			=> $user['username'],
            'last_login_time'	=> $user['last_login_time'],
        );
// 		wlog(auth, $auth);
        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
        //wlog('session', $_SESSION);
		return 5;
	}
	static private function saveLoginInfo($userId){
		$model 						= M()->table(C('USER_AUTH_TABLE'));
		$data						= array();
		$data['id']					= $userId;
		$data['login_count']		= array('exp','login_count+1');
		$data['last_login_time']	= NOW_TIME;
		$data['last_login_ip']		= get_client_ip(1);
		$model->save($data);
	}
	static public function logout(){
		session('user_auth', null);
        session('user_auth_sign', null);
		return treu;
	}
	/**
	 * 获取用户信息
	 * @return array
	 */
	static public function getUserInfo(){
		$user 				= session('user_auth');
		$model 				= M()->table(C('USER_AUTH_TABLE'));
		$roleTableName		= C('USER_AUTH_ROLE');
		$model->alias('A')->join("LEFT JOIN $roleTableName B ON B.id = A.role_id");
        $model->field('A.*,B.name as role_name,B.issys as role_issys,B.status as role_status');
		$map['A.id']		= $user['id'];
		$model->where($map);
		return $model->find();
	}
	public function getUserName($id){
        return $this->where(array('id'=>(int)$id))->getField('username');
    }
	/**
	 * 检查控制器和操作
	 * @return boolean          是否需要认证
	 */
	static private function checkAccess(){
		$_ctrl					= array();
		$_action				= array();
		$contoller 				= strtoupper(CONTROLLER_NAME);
		if("" != C('USER_AUTH_CONTROLLER')){
			$_ctrl['yes']		= explode(',',strtoupper(C('USER_AUTH_CONTROLLER')));
		}else{
			$_ctrl['no']		= explode(',',strtoupper(C('USER_AUTH_NO_CONTROLLER')));
		}
		if(!empty($_ctrl['no']) && !in_array($contoller,$_ctrl['no']) || !empty($_ctrl['yes']) && in_array($contoller,$_ctrl['yes'])){
			$action 			= $contoller.'/'.strtoupper(ACTION_NAME);
			if("" != C('USER_AUTH_ACTION')){
				$_action['yes']	= explode(',',strtoupper(C('USER_AUTH_ACTION')));
			}else{
				$_action['no']	= explode(',',strtoupper(C('USER_AUTH_NO_ACTION')));
			}
			if(!empty($_action['no']) && !in_array($action,$_action['no']) || !empty($_action['yes']) && in_array($action,$_action['yes'])){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}

		// $flag					= false;
		// $str_ctrl				= C('USER_AUTH_CONTROLLER');
		// $str_no_ctrl			= C('USER_AUTH_NO_CONTROLLER');
		// if($str_no_ctrl){

		// }
		// if($str_ctrl){
		// 	$arr_ctrl			= explode(',',strtoupper($str_ctrl));
		// 	$flag				= in_array(strtoupper(CONTROLLER_NAME),$arr_ctrl);
		// }elseif($str_no_ctrl){
		// 	$arr_no_ctrl		= explode(',',strtoupper($str_no_ctrl));
		// 	$flag				= !in_array(strtoupper(CONTROLLER_NAME),$arr_no_ctrl);
		// }
		// if($flag){
		// 	$str_no_action		= C('USER_AUTH_NO_ACTION');
		// 	if($str_no_action){
		// 		$arr_no_action	= explode(',',strtoupper($str_no_action));
		// 		$flag			= !in_array(strtoupper(CONTROLLER_NAME.'/'.ACTION_NAME),$arr_no_action);
		// 	}
		// }else{
		// 	$str_action			= C('USER_AUTH_ACTION');
		// 	if($str_action){
		// 		$arr_action		= explode(',',strtoupper($str_action));
		// 		$flag			= in_array(strtoupper(CONTROLLER_NAME.'/'.ACTION_NAME),$arr_action);
		// 	}
		// }
		// return $flag;
	}
	/**
	 * 登录检查
	 * @return boolean          是否登陆
	 */
	static private function checkLogin(){
		return is_login();
	}
	/**
	 * 检查当前操作是否需要认证
	 * @return [type] [description]
	 */
	static public function checkAuth(){
		// return self::checkAccess();
		if(C('USER_AUTH_ON') && self::checkAccess()){
			if(!self::checkLogin()){
				return 1;//没有登陆
			}elseif(!self::checkRules()){
				return 2;
			}
		}
		return 0;
	}
	/**
	 * 获取角色
	 * @return [type] [description]
	 */
	static function getRole(){

	}
	/**
	 * 认证当前操作
	 * @return [type] [description]
	 */
	static public function checkRules(){
		static $_rules	= array();
		if(!$_rules){
			$_rules		= self::getAuthList();
		}
		if($_rules['issys'] == 1){
			return true;
		}else{
			$action   = strtolower(implode('/',array(MODULE_NAME,CONTROLLER_NAME,ACTION_NAME)));
			return in_array($action,$_rules['rules']);
		}
	}
	/**
	 * 取得当前认证号的所有权限列表
	 * @param  int    $authId 用户ID
	 * @return array          权限列表
	 */
	static public function getAuthList(int $authId){
		$user 	= self::getUserInfo();
		$model	= M()->table(C('USER_AUTH_ROLE'));
		$map 			= array();
		$map['id']		= $user['role_id'];
		$model->where($map);
		$role	= $model->find();
		$rules	= array();
		if($role['issys']>1){
			$rules[]			= 'admin/index/main';
			$rules[]			= 'admin/index/info';
			$model				= M()->table(C('USER_AUTH_RULE'));
			$map 				= array();
			$map['id']			= array('in',$role['rules']);
			$model->where($map);
			$result				= $model->select();
			foreach($result as $value) {
				$res 			= explode(',',$value['rule']);
				foreach ($res as $v) {
					$rules[]	= strtolower($value['module'].'/'.$v);
				}
			}
		}
		//$rules	= $model->field('id,concat(module,rule) as rule')->getField('id,rule');
		// echo $model->_sql();
		//$action   = strtolower(implode('/',array(MODULE_NAME,CONTROLLER_NAME,ACTION_NAME)));
		// echo $action;
		//echo in_array($action,$rules);
		//dump(in_array($action,$rules));
		// dump($rules);
		return array(
			'issys'	=> $role['issys'],
			'rules'	=> $rules,
		);
	}
}