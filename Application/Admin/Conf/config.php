<?php
return array(
    'LAYOUT_ON'                 => true,
    'LAYOUT_NAME'               => 'Common/main',
    'SESSION_OPTIONS'           => array(
//      'type'                  => 'Db',
        'expire'                => 86400,
    ),	
    'LOAD_EXT_CONFIG'           => array(
        'SMS'                   => 'sms',
        'JPUSH'                 => 'jpush',
        'BAIDU'                 => 'baidu',
    ),
	/* 调试配置 */
    'SHOW_PAGE_TRACE'           => false,
    'LESS'                      => false,
	/* 用户设置 */
	'USER_MAX_CACHE'     		=> 1000, //最大缓存用户数
    /* 模板相关配置 */
    'TMPL_PARSE_STRING'         =>  array(
        '__LESS__'              =>  __ROOT__ . '/Less/admin',
        '__JAVASCRIPT__'        =>  __ROOT__ . '/JavaScript/admin',
        '__STATIC__'            =>  __ROOT__ . '/Public/static',
        '__CSS__'          	    =>  __ROOT__ . '/Public/admin/css',
        '__JS__'                =>  __ROOT__ . '/Public/admin/js',
    ),
    // 'TAGLIB_PRE_LOAD'		=> 'html,Common\TagLib\Hform',
    'TAGLIB_BUILD_IN'           => 'cx,Common\TagLib\Tag' ,
    'USER_AUTH_ON'              => true,    //认证开关
    'USER_AUTH_TYPE'            => 1,       //认证方式，1为时时认证；2为登录认证。
    'USER_AUTH_TABLE'           => '__ADMIN__',             //用户信息表
    'USER_AUTH_ROLE'            => '__ADMIN_ROLE__',        //角色数据表名
    'USER_AUTH_RULE'            => '__ADMIN_RULE__',        //角色数据表名
//     'USER_AUTH_CONTROLLER'      => 'Config,Admins',                 //需要认证的控制器
    // 'USER_AUTH_NO_CONTROLLER'    => 'Index',                 //不需要认证的控制器
    // 'USER_AUTH_ACTION'           => 'Admin/index',               //需要认证的操作
    'USER_AUTH_NO_ACTION'       => 'Index/index,Index/verify,Index/login,Index/logout',                  //不需要认证的操作
);