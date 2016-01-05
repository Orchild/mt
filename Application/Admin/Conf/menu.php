<?php
return array(
	array(
		"name"		=> "系统设置",
		"module"	=> "Admin",
		"controller"	=> array(
			"Config"
		),
		"url"		=> "/mt_metronic/index.php?s=/Admin/Config/index.html",
		"items"		=> array(
			array(
				"name"	=> "接口设置",
				"uri"	=> "Config/index",
				"icon"	=> "glyphicon glyphicon-wrench",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			)
		),
		"icon"		=> "glyphicon glyphicon-cog"
	),
	array(
		"name"		=> "文本单页",
		"module"	=> "Admin",
		"controller"	=> array(
			"Basic"
		),
		"url"		=> "/mt_metronic/index.php?s=/Admin/Basic/index.html",
		"items"		=> array(
			array(
				"name"	=> "关于我们",
				"uri"	=> "Basic/index",
				"icon"	=> "glyphicon glyphicon-wrench",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			)
		),
		"icon"		=> "glyphicon glyphicon-file"
	),
	array(
		"module"	=> "Admin",
		"items"		=> array(
			array(
				"name"	=> "管理分类",
				"uri"	=> "Category/index",
				"icon"	=> "glyphicon glyphicon-wrench",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			)
		),
		"controller"	=> array(
			"Category"
		),
		"url"		=> "/mt_metronic/index.php?s=/Admin/Category/index.html",
		"name"		=> "分类管理",
		"icon"		=> "glyphicon glyphicon-tags"
	),
	array(
		"name"		=> "消息管理",
		"module"	=> "Admin",
		"controller"	=> array(
			"Message",
			"MsgRead"
		),
		"url"		=> "/mt_metronic/index.php?s=/Admin/Message/index.html",
		"items"		=> array(
			array(
				"name"	=> "管理消息",
				"uri"	=> "Message/index",
				"icon"	=> "glyphicon glyphicon-edit",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			),
			array(
				"name"	=> "置顶消息",
				"uri"	=> "Message/headmsg",
				"icon"	=> "glyphicon glyphicon-pushpin",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			),
			array(
				"name"	=> "消息阅读",
				"uri"	=> "MsgRead/index",
				"icon"	=> "icon-graph",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			)
		),
		"icon"		=> "glyphicon glyphicon-list"
	),
	array(
		"name"		=> "员工管理",
		"module"	=> "Admin",
		"controller"	=> array(
			"User",
			"Certify",
			"Reset"
		),
		"url"		=> "/mt_metronic/index.php?s=/Admin/User/index.html",
		"items"		=> array(
			array(
				"name"	=> "管理员工",
				"uri"	=> "User/index",
				"icon"	=> "glyphicon glyphicon-wrench",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			),
			array(
				"name"	=> "实名认证",
				"uri"	=> "Certify/index",
				"icon"	=> "glyphicon glyphicon-check",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			),
			array(
				"name"	=> "密码重置",
				"uri"	=> "Reset/index",
				"icon"	=> "glyphicon glyphicon-repeat",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			)
		),
		"icon"		=> "glyphicon glyphicon-user"
	),
	array(
		"module"	=> "Admin",
		"items"		=> array(
			array(
				"name"	=> "宝贵建议",
				"uri"	=> "Feedback/index",
				"icon"	=> "glyphicon glyphicon-envelope",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			)
		),
		"name"		=> "建议反馈",
		"url"		=> "/mt_metronic/index.php?s=/Admin/Feedback/index.html",
		"controller"	=> array(
			"Feedback"
		),
		"icon"		=> "glyphicon glyphicon-envelope"
	),
	array(
		"name"		=> "管理员管理",
		"module"	=> "Admin",
		"controller"	=> array(
			"Admins",
			"Role"
		),
		"url"		=> "/mt_metronic/index.php?s=/Admin/Admins/index.html",
		"items"		=> array(
			array(
				"name"	=> "管理管理员",
				"uri"	=> "Admins/index",
				"icon"	=> "glyphicon glyphicon-wrench",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			)
		),
		"icon"		=> "glyphicon glyphicon-wrench"
	),
	array(
		"module"	=> "Admin",
		"items"		=> array(
			array(
				"icon"	=> "glyphicon glyphicon-font",
				"name"	=> "用户行为",
				"uri"	=> "Action/index",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			),
			array(
				"icon"	=> "glyphicon glyphicon-font",
				"name"	=> "行为日志",
				"uri"	=> "Log/index",
				"url"	=> "/mt_metronic/index.php?s=/Tools/Index/Admin.html"
			)
		),
		"name"		=> "行为管理",
		"url"		=> "/mt_metronic/index.php?s=/Admin/Action/index.html",
		"icon"		=> "glyphicon glyphicon-font",
		"controller"	=> array(
			"Action",
			"Log"
		)
	)
);