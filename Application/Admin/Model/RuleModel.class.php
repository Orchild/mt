<?php
namespace Admin\Model;
use Common\Util\RelationModel;
class RuleModel extends RelationModel{
	protected $tableName	= 'admin_rule';
	protected $_validate    = array(
        array('name','require','规则名称不能为空！',self::MUST_VALIDATE),
        array('rule','require','规则不能为空！',self::MUST_VALIDATE),
        array('name','','规则名称已经存在！',self::VALUE_VALIDATE,'unique',self::MODEL_BOTH),
    );
	protected $_link = array(
        array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'Rule',
            'mapping_name'  => 'items',
            'mapping_fields'=> 'id,name,rule',
            'mapping_order' => 'sort asc,id asc',
        ),
    );
}