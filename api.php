<?php
// 应用入口文件
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
//安全文件定义为index.html
define('BUILD_DIR_SECURE',false);
define('MODE_NAME','api');
// 绑定Home模块到当前入口文件
define('BIND_MODULE','Api');
//绑定Index控制器到当前入口文件
//define('BIND_CONTROLLER','Index');

//生成多个控制器类
// define('BUILD_CONTROLLER_LIST','Empty,Auth,Index,Config,Role,Admin');
//生成多个模型类
//define('BUILD_MODEL_LIST','User,Menu');

// 定义应用目录
define('APP_PATH','./Application/');
// 定义运行时目录
define('RUNTIME_PATH','./Runtime/');
//定义静态页面的路径
define('HTML_PATH','./Html/');
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);

// 引入ThinkPHP入口文件
require 'ThinkPHP/ThinkPHP.php';