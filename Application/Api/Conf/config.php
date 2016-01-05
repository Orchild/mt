<?php
return array(
    /* URL设置 */
    'URL_MODEL'                 => 3,
    'LOAD_EXT_CONFIG'           => array(
        'SMS'                   => APP_PATH.'Admin/Conf/sms.php',
        'JPUSH'                 => APP_PATH.'Admin/Conf/jpush.php',
        'BAIDU'                 => APP_PATH.'Admin/Conf/baidu.php',
        'ALIPAY'                => APP_PATH.'Admin/Conf/alipay.php',
    ),
	/* 调试配置 */
    'SHOW_PAGE_TRACE'           => false,
    'LESS'                      => false,
    'ADD'                       => true,
    /* 模板相关配置 */
    'TMPL_PARSE_STRING'         =>  array(
        '__LESS__'              =>  __ROOT__ . '/Less/api',
        '__JAVASCRIPT__'        =>  __ROOT__ . '/JavaScript/api',
        '__STATIC__'            =>  __ROOT__ . '/Public/static',
        '__CSS__'          	    =>  __ROOT__ . '/Public/api/css',
        '__JS__'                =>  __ROOT__ . '/Public/api/js',
    ),
    'TAGLIB_BUILD_IN'           => 'cx,Common\TagLib\Hform' ,
);