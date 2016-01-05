<?php
// wlog("时间",NOW_TIME);
// $res = M('Consumption')->field(array(
// 	'id',
// 	'UNIX_TIMESTAMP(now())-created-600>0'=>'n',
// 	// 'unix_timestamp(now())-created'=>'n',
// 	// 'TIMEDIFF(FROM_UNIXTIME(created,"%Y-%m-%d %H:%i:%S"),now())'	=> 'time',
// 	'TIMEDIFF(now(),DATE_ADD(FROM_UNIXTIME(created,"%Y-%m-%d %H:%i:%S"),interval 10 minute))>0'	=> 'time',
// ))->where(array(
// 	'status'	=> 0,
// ))->find();
// wlog("支付记录",$res['id']." = ".$res['n']." = ".$res['time']);
// M('Consumption')->where(array(
// 	'status'	=> 0,
// 	'TIMEDIFF(now(),DATE_ADD(FROM_UNIXTIME(created,"%Y-%m-%d %H:%i:%S"),interval 10 minute))'	=> array('gt',0),
// ))->setField('status',1);
// wlog("SQL",M('Consumption')->_sql());