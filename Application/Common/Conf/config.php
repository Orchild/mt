<?php
return array(
	/* URL设置 */
	'URL_MODEL' 			=> 3,
    /* 调试配置 */
    'SHOW_PAGE_TRACE'       => true,
	/* 模块配置 */
	'MODULE_ALLOW_LIST'     => array('Admin','Api','Tools'),
    'DEFAULT_MODULE'        => 'Admin',
    /* 扩展配置 */
    'LOAD_EXT_CONFIG' 		=> 'db',
    'URL_ROUTER_ON'         => true,
    'URL_MAP_RULES'			=> array(
        // 'news'                 => 'api/news/details',
    	// 'qrcode'           => array('api/commend/qrcode',null,array('method'=>'get','ext'=>'png')),
    ),
    'URL_ROUTE_RULES'       => array(
        'image/:file'               => array('api/index/image',null,array('method'=>'get','ext'=>'jpg')),
        'qrcode'                    => array('api/commend/qrcode',null,array('method'=>'get','ext'=>'png')),
        '/^coupons(\d+)$/'          => 'api/coupons/lists?id=:1',
        '/^coupons(\d+)-(\d+)$/'    => 'api/coupons/getcoupons?uid=:1&id=:2',
        '/^obtaincoupons(\d+)-(\d+)$/'    => 'api/coupons/obtaincoupons?uid=:1&id=:2',
        '/^stores(\d+)$/'           => 'api/stores/details?id=:1',
        '/^activity(\d+)-(\d+)$/'         => 'api/activity/details?id=:1&uid=:2',
        '/^tosignup(\d+)$/'         => 'api/activity/tosignup?id=:1',
        '/^message(\d+)$/'             => 'api/message/details?id=:1',
        '/^notice(\d+)$/'           => 'api/notice/details?id=:1',
        '/^hotactivities(\d+)$/'    => 'api/hotactivities/details?id=:1',
        '/^school(\d+)$/'           => 'api/school/details?id=:1',
        '/^training(\d+)$/'         => 'api/training/details?id=:1',
        'downloads/:file'           => array('Api/Index/download','ext=apk',array('method'=>'get','ext'=>'apk')),
        ':name$'                    => 'api/text/details',
    ),
);