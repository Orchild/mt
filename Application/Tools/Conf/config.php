<?php
return array(
	/* 调试配置 */
    'SHOW_PAGE_TRACE'           => true,
    'LESS'                      => false,
    /* 模板相关配置 */
    'TMPL_PARSE_STRING'         =>  array(
        '__LESS__'              =>  __ROOT__ . '/Less/tools',
        '__JAVASCRIPT__'        =>  __ROOT__ . '/JavaScript/tools',
        '__STATIC__'            =>  __ROOT__ . '/Public/static',
        '__CSS__'          	    =>  __ROOT__ . '/Public/tools/css',
        '__JS__'                =>  __ROOT__ . '/Public/tools/js',
    ),
    'TAGLIB_BUILD_IN'           => 'cx,Common\TagLib\Tag',
);