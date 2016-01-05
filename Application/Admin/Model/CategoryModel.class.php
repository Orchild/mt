<?php
namespace Admin\Model;
use Think\Model;
class CategoryModel extends Model {
    protected $_validate    = array(
        array('title','require','分类名称不能为空！',self::MUST_VALIDATE),
        array('title','','该分类已经存在！',self::VALUE_VALIDATE,'unique',self::MODEL_BOTH),
    );
	protected $_auto = array(
        array('created','time',self::MODEL_INSERT,'function'),
        array('updated','time',self::MODEL_BOTH,'function'),
    );
}