<?php
return array(
	array(
		array(
			"name"		=> "系统设置",
			"module"	=> "Admin",
			"status"	=> "1",
			"controller"	=> array(
				"Config"
			),
			"items"		=> array(
				array(
					"name"	=> "接口设置",
					"uri"	=> "Config/index",
					"status"=> 1,
					"icon"	=> "glyphicon glyphicon-wrench"
				),
				array(
					"name"	=> "版本信息",
					"uri"	=> "Config/version",
					"status"=> 0,
					"icon"	=> "icon-home"
				)
			),
			"icon"		=> "glyphicon glyphicon-cog"
		),
		array(
			"name"		=> "文本单页",
			"module"	=> "Admin",
			"status"	=> "1",
			"controller"	=> array(
				"Basic"
			),
			"items"		=> array(
				array(
					"name"	=> "关于我们",
					"uri"	=> "Basic/index",
					"status"=> 1,
					"icon"	=> "glyphicon glyphicon-wrench"
				),
				array(
					"name"	=> "联系我们",
					"uri"	=> "Basic/contact",
					"status"=> 0
				),
				array(
					"name"	=> "关于自然医学",
					"uri"	=> "Basic/medicine",
					"status"=> 0
				),
				array(
					"name"	=> "使用说明",
					"uri"	=> "Basic/directions",
					"status"=> 0
				),
				array(
					"name"	=> "警示与免责声明",
					"uri"	=> "Basic/responsible",
					"status"=> 0
				),
				array(
					"name"	=> "领奖说明",
					"uri"	=> "Basic/prize",
					"status"=> 0
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
					"status"=> "1",
					"icon"	=> "glyphicon glyphicon-wrench"
				)
			),
			"controller"	=> array(
				"Category"
			),
			"name"		=> "分类管理",
			"status"	=> "1",
			"icon"		=> "glyphicon glyphicon-tags"
		),
		array(
			"name"		=> "消息管理",
			"module"	=> "Admin",
			"status"	=> "1",
			"controller"	=> array(
				"Message",
				"MsgRead"
			),
			"items"		=> array(
				array(
					"name"	=> "管理消息",
					"uri"	=> "Message/index",
					"status"=> 1,
					"icon"	=> "glyphicon glyphicon-edit"
				),
				array(
					"name"	=> "置顶消息",
					"uri"	=> "Message/headmsg",
					"status"=> "1",
					"icon"	=> "glyphicon glyphicon-pushpin"
				),
				array(
					"name"	=> "消息阅读",
					"uri"	=> "MsgRead/index",
					"status"=> "1",
					"icon"	=> "icon-graph"
				)
			),
			"icon"		=> "glyphicon glyphicon-list"
		),
		array(
			"name"		=> "员工管理",
			"module"	=> "Admin",
			"status"	=> "1",
			"controller"	=> array(
				"User",
				"Certify",
				"Reset"
			),
			"items"		=> array(
				array(
					"name"	=> "管理员工",
					"uri"	=> "User/index",
					"status"=> 1,
					"icon"	=> "glyphicon glyphicon-wrench"
				),
				array(
					"name"	=> "实名认证",
					"uri"	=> "Certify/index",
					"status"=> "1",
					"icon"	=> "glyphicon glyphicon-check"
				),
				array(
					"name"	=> "密码重置",
					"uri"	=> "Reset/index",
					"status"=> "1",
					"icon"	=> "glyphicon glyphicon-repeat"
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
					"status"=> "1",
					"icon"	=> "glyphicon glyphicon-envelope"
				)
			),
			"name"		=> "建议反馈",
			"controller"	=> array(
				"Feedback"
			),
			"status"	=> "1",
			"icon"		=> "glyphicon glyphicon-envelope"
		),
		array(
			"name"		=> "管理员管理",
			"module"	=> "Admin",
			"status"	=> "1",
			"controller"	=> array(
				"Admins",
				"Role"
			),
			"items"		=> array(
				array(
					"name"	=> "管理管理员",
					"uri"	=> "Admins/index",
					"status"=> 1,
					"icon"	=> "glyphicon glyphicon-wrench"
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
					"status"=> "1"
				),
				array(
					"icon"	=> "glyphicon glyphicon-font",
					"name"	=> "行为日志",
					"uri"	=> "Log/index",
					"status"=> "1"
				)
			),
			"name"		=> "行为管理",
			"icon"		=> "glyphicon glyphicon-font",
			"controller"	=> array(
				"Action",
				"Log"
			),
			"status"	=> "1"
		)
	)
);