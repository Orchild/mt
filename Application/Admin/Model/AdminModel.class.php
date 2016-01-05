<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class AdminModel extends RelationModel{
    protected $_auto = array(
        array('password','md5',self::MODEL_BOTH,'function'),
        array('register_ip','get_client_ip',self::MODEL_INSERT,'function',1),
        array('register_time','time',self::MODEL_INSERT,'function'),
        array('updated','time',self::MODEL_BOTH,'function'),
    );
    protected $_validate    = array(
        array('username','require','用户名称不能为空！'),
        array('username','/^[a-z]\w{3,}/i','用户名格式错误！'),
        array('username','','用户名称已经存在！',self::VALUE_VALIDATE,'unique',self::MODEL_BOTH),
        array('password','require','密码不能为空！',self::MUST_VALIDATE,'',self::MODEL_INSERT),
        // array('repassword','password','确认密码不一致',self::VALUE_VALIDATE,'confirm'),
        array('role_id','require','请选择角色组！',self::EXISTS_VALIDATE),
    );
    protected $_link = array(
        array(
            'mapping_type'  => self::BELONGS_TO,
            'class_name'    => 'role',
            // 'foreign_key'   => 'role_id',
            // 'parent_key' => 'id',
            'mapping_name'  => 'item',
            'mapping_fields'=> 'name,issys,status',
            'as_fields'     => 'name:role_name,issys:role_issys,status:role_status',
        ),
    );
    protected function _before_update(&$data,$options){
        if(empty($data['password'])){
            unset($data['password']);
        }
    }
    public function delete(){
        $res    = array_map(create_function('$v','return $v["id"];'),D('Role')->where('issys<>1')->select());
        $map['role_id'] = array('in',$res);
        $this->where($map);
        return parent::delete();
    }
    public function modify_password($id,$password){
        $data       = array(
            'id'        => $id,
            'password'  => md5($password),
            'updated'   => NOW_TIME,
        );
        $this->save($data);
    }
	public function lists($status = 1, $order = 'id DESC', $field = true){
        $map = array('status' => $status);
        return $this->field($field)->where($map)->order($order)->select();
    }
    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($id){
        /* 检测是否在当前应用注册 */
        $user = $this->field(true)->find($id);
        if(!$user || 1 != $user['status']) {
            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
            return false;
        }
        //记录行为
        // action_log('user_login', 'member', $uid, $uid);
        /* 登录用户 */
        $this->autoLogin($user);
        return true;
    }
    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
    }
    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
            'id'				=> $user['id'],
            'login_count'		=> array('exp', '`login_count`+1'),
            'last_login_time'	=> NOW_TIME,
            'last_login_ip'   	=> get_client_ip(1),
        );
        $this->save($data);
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'id'             	=> $user['id'],
            'username'			=> $user['username'],
            'last_login_time'	=> $user['last_login_time'],
        );
        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
    }
    public function getUserName($id){
        return $this->where(array('id'=>(int)$id))->getField('username');
    }
}