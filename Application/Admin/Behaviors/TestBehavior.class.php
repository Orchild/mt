<?php
// +----------------------------------------------------------------------
// | PHP@妖孽 [三十年河东三十年河西,莫欺少年穷.!]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.yaonies.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: PHP@妖孽 <msj@yaonies.com>
// +----------------------------------------------------------------------
/**
 +------------------------------------------------------------------------------
 * 基于用户的操作记录验证类
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Msj
 * @author    PHP@妖孽 <msj@yaonies.com>
 * @version   1.0
 +------------------------------------------------------------------------------
 */
// 配置文件增加设置
//  'OPERATION_ON'=>true,// 开启用户记录日志
//     'OPERATION_MEMBER'=>'learn_member',
//     'OPERATION_TYPE'=>'web',//分别为web,interface也就是网站,和接口
//     'OPERATION_MEMBER_ID'=>'member_id', //如果后台就取session,如果接口就直接取get,post请求的值
/*
 -- --------------------------------------------------------
 CREATE TABLE IF NOT EXISTS `ds_action` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '行为唯一标识',
  `title` char(80) NOT NULL DEFAULT '' COMMENT '行为说明',
  `remark` char(140) NOT NULL DEFAULT '' COMMENT '行为描述',
  `rule` text NOT NULL COMMENT '行为规则',
  `log` text NOT NULL COMMENT '日志规则',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统行为表';
 -- --------------------------------------------------------
 CREATE TABLE IF NOT EXISTS `ds_action_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `action_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '行为id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `action_ip` bigint(20) NOT NULL COMMENT '执行行为者ip',
  `model` varchar(50) NOT NULL DEFAULT '' COMMENT '触发行为的表',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '触发行为的数据id',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行行为的时间',
  PRIMARY KEY (`id`),
  KEY `action_ip_ix` (`action_ip`),
  KEY `action_id_ix` (`action_id`),
  KEY `user_id_ix` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='行为日志表';
 */
use Think\Behavior;
class testBehavior extends Behavior{
	public function run(&$params){
		
	}
	
	private $operation_on;//操作记录开关
	public  $error;//错误信息
	
	/**
	 * @todo  验证是否开启记录
	 */
	public function __construct(){
		$this->operation_on = C('OPERATION_ON');
		if($this->operation_on === false){
			return false;
		}
	}
	
	/**
	 * @todo获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @return mixed
	 */
	private function getClientIp($type=0){
		$type       =  $type ? 1 : 0;
		static $ip  =   NULL;
		if ($ip !== NULL) return $ip[$type];
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos    =   array_search('unknown',$arr);
			if(false !== $pos) unset($arr[$pos]);
			$ip     =   trim($arr[0]);
		}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip     =   $_SERVER['HTTP_CLIENT_IP'];
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip     =   $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$long = sprintf("%u",ip2long($ip));
		$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}
	
	/**
	 * @todo 检测表是否存在,如果不存在则创建新表
	 */
	static public function checkTableIsExist(){
		$db     =   Db::getInstance(C('RBAC_DB_DSN'));
		$table_prefix = C('DB_PREFIX');
		$sql    =   "CREATE TABLE IF NOT EXISTS `{$table_prefix}msj_operation_log` (
		`operation_log` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '操作记录主键',
		`operation_uid` mediumint(4) NOT NULL DEFAULT '0' COMMENT '操作人/如果是接口返回-1暂不记录接口请求人',
		`operation_node` char(50) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '操作节点',
		`operation_ip` mediumtext COLLATE utf8_bin NOT NULL COMMENT '记录操作IP,省市,等信息',
		`operation_time` int(10) NOT NULL DEFAULT '0' COMMENT '操作时间',
		PRIMARY KEY (`operation_log`),
		KEY `index_uid_node` (`operation_uid`,`operation_node`,`operation_log`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
		$db->execute($sql);
	}
	
	/**
	 * @todo 写入操作日志
	 */
	public function writeLog(){
		(defined('NOW_TIME'))?$time = NOW_TIME: $time=time();
	
		switch (C('OPERATION_TYPE')){
			case 'web':
				$uid = session(C('OPERATION_MEMBER_ID'));
				$uid = ($uid)?$uid:0;
				break;
			case 'interface'://预留
				$uid = -1;//接口的操作日志暂时不记录操作人
				break;
			default:
				$uid = -2;
				break;
		}
	
		$db_name =C('DB_NAME') ;
		$table_prefix = C('DB_PREFIX');
		import('@.ORG.Msj.IpLocation');// 导入IpLocation类
		$Ip = new IpLocation(); // 实例化类
		$ip_info = $Ip->getlocation($this->getClientIp()); // 获取某个IP地址所在的位置
		$ip_info['country'] = iconv('gbk', 'utf-8', $ip_info['country']);
		$db     =   Db::getInstance(C('RBAC_DB_DSN'));
		$sql    =    "INSERT INTO `{$db_name}`.`{$table_prefix}msj_operation_log` (`operation_uid`, `operation_node`, `operation_ip`, `operation_time`) VALUES ('".$uid."','".$_SERVER['REQUEST_URI']."','".serialize($ip_info)."','".$time."');";
		if($db->execute($sql) === false ){
			//插入失败写日志
			Log::write("uid:{$uid},".'node:'.$_SERVER['REQUEST_URI'].',operation_ip:'.serialize($ip_info).',time:'.date('Y-m-d H:i:s',$time));
		}
	
	}
	
	/**
	 * @todo 查询操作日志
	 * @param array $map 目前只支持用户id的查询.
	 */
	public function logList($map=array()){
		$db     =   Db::getInstance(C('RBAC_DB_DSN'));
		$member_table_name = C('OPERATION_MEMBER');
		$operation_table_name =C('DB_PREFIX').'msj_operation_log';
		$member_id = implode(',',$map);
		$sql = "(SELECT
		msj_operation_log.operation_log AS operation_log,
		msj_operation_log.operation_uid AS operation_uid,
		msj_operation_log.operation_node AS operation_node,
		msj_operation_log.operation_ip AS operation_ip,
		msj_operation_log.operation_time AS operation_time,
		Member.member_name AS member_name
		FROM
		{$operation_table_name} msj_operation_log
		JOIN {$member_table_name} Member
		ON msj_operation_log.operation_uid = Member.member_id
		WHERE (`member_id` IN('{$member_id}')))";
		$log_list = $db->query($sql);
		$Ip = new IpLocation(); // 实例化类
		$ip_info = $Ip->getlocation($this->getClientIp()); // 获取某个IP地址所在的位置
		if(!empty($log_list)){
			foreach($log_list as $key=>$val){
				$log_list[$key]['operation_time'] = date('Y-m-d H:i:s',$val['operation_time']);
				$info = unserialize($val['operation_ip']);
				$log_list[$key]['operation_ip'] = "地区:".$info['area'].',城市:'.$info['country'].',IP:'.$info['ip'];
			}
			return $log_list;
		}else{
			return false;
		}
	}
	
	public function __destruct(){
		$this->operation_on=false;
		$this->error ='';
	}
}	