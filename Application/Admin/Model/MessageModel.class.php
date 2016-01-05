<?php
namespace Admin\Model;
use Think\Model;
class MessageModel extends Model {
    protected $_validate    = array(
        array('title','require','消息标题不能为空！',self::MUST_VALIDATE),
        array('title','','消息已经存在！',self::VALUE_VALIDATE,'unique',self::MODEL_BOTH),
    );
	protected $_auto = array(
        array('created','time',self::MODEL_INSERT,'function'),
        array('updated','time',self::MODEL_BOTH,'function'),
        array('content','SaveRemoteImage',self::MODEL_BOTH,'function'),
    );
}