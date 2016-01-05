<?php
namespace Admin\Model;
use Common\Model\RelationModel;
class UserModel extends RelationModel {
	protected $_auto = array(
        array('password','md5',self::MODEL_INSERT,'function'),
        array('created','time',self::MODEL_INSERT,'function'),
        array('updated','time',self::MODEL_BOTH,'function'),
    );
    protected $_validate	= array(
		array('role_id','require','请选择用户类型！',self::MUST_VALIDATE),
	);
	protected $_link = array(
		array(
            'mapping_type'  => self::HAS_ONE,
            'class_name'    => 'UserInfo',
            'mapping_name'  => 'info'
// 			'as_fields'		=> 'portrait,realname,nickname,cat_id,name_cert,sex,age,birthday,id_number,email,company,work_status,alternative_phone'
        ),
    );
}