<?php
namespace Admin\Model;
use Think\Model;
class RoleModel extends Model{
	protected $tableName	= 'admin_role';
	protected $_auto = array(
        array('created','time',self::MODEL_INSERT,'function'),
        array('updated','time',self::MODEL_BOTH,'function'),
    );
    protected $_validate	= array(
		array('name','require','角色名称不能为空！',self::MUST_VALIDATE),
		array('name','','角色名称已经存在！',self::VALUE_VALIDATE,'unique',self::MODEL_BOTH),
	);
}