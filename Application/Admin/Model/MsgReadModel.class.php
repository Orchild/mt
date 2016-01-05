<?php
namespace Admin\Model;
use Think\Model;
class MessageModel extends Model {
    protected $_validate    = array(
    );
	protected $_auto = array(
        array('created','time',self::MODEL_INSERT,'function'),
    );
}