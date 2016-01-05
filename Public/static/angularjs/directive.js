try{angular.module('directive',[])
/**
 * 计时器
 * 例子：
 * 		<timer format="y-M-d h:mm:ss"></timer>
 * 		<span timer="y-M-d h:mm:ss"></span>
 */
.directive('timer', ['$interval','dateFilter', function($interval,dateFilter){
	return {
		restrict: 'EA', // E = Element, A = Attribute, C = Class, M = Comment
		link: function($scope, iElm, iAttrs, controller) {
			var format		= iAttrs.timer || iAttrs.format || "M/d/yy h:mm:ss a";
			var interval 	= null;
			function updateTime(){
				iElm.text(dateFilter(new Date(),format));
			}
			interval 		= $interval(updateTime,1000);
			$scope.$on('$destroy', function(e) {
				$interval.cancel(interval);
			});
		}
	};
}])
/**
 * 倒计数
 * 例子：
 * 		<wait:second seconds="10"></wait:second>
 * 		<span wait:second seconds="10"></span>
 * 		<span wait:second="10"></span>
 * 		<span wait-second seconds="10"></span>
 * 		<span wait-second="10"></span>
 * 		<span class="waitSecond" seconds="10"></span>
 */
.directive('waitSecond', ['$timeout','$location',function($timeout,$location){
	return {
		restrict: 'EAC', // E = Element, A = Attribute, C = Class, M = Comment
		link: function($scope, iElm, iAttrs, controller) {
			var seconds		= iAttrs.waitSecond  || iAttrs.seconds  || 5;
			var jump		= iAttrs.jump;
			var timeout 	= null;
			(function waitSecond(){
				iElm.text(seconds--);
				if(seconds>=0){
					timeout = $timeout(waitSecond,1000);
				}else if(jump){
					$location.path(jump);
				}else{
					iElm.hide();
				}
			})();
			$scope.$on('$destroy', function(e) {
				$timeout.cancel(timeout);
			});
		}
	};
}])
/**
 * 等待字符串
 * 例子：
 * 		<wait:loading num="10"></wait:loading>
 * 		<span wait:loading num="10"></span>
 * 		<span wait:loading="10"></span>
 * 		<span wait-loading seconds="10"></span>
 * 		<span wait-loading="10"></span>
 */
.directive('waitLoading', ['$interval',function($interval){
	return {
		restrict: 'EA', // E = Element, A = Attribute, C = Class, M = Comment
		link: function($scope, iElm, iAttrs, controller) {
			var num			= (iAttrs.num || 3) * 1 + 1;
			var mask		= iAttrs.waitLoading || '.';
			var interval 	= null;
			var i 			= 0;
			function updateTime(){
				i++;
				iElm.text(Array(i % num + 1).join(mask));
			}
			interval 		= $interval(updateTime,500);
			$scope.$on('$destroy', function(e) {
				$interval.cancel(interval);
			});
		}
	};
}])
/**
 * tabs
 * 例子：
 	<tabs>
		<tab title="1">1</tab>
		<tab title="2">2</tab>
		<tab title="3">3</tab>
	</tabs>
 	<tabs index="2">
		<tab title="1">1</tab>
		<tab title="2">2</tab>
		<tab title="3">3</tab>
	</tabs>
	<tabs>
		<tab title="1">1</tab>
		<tab title="2">2</tab>
		<tab title="3" active="true">3</tab>
	</tabs>
 */
.directive('tabs',function(){
	return {
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		scope 		: {
			index 	: '='
		},
		template 	: '<div class="pane tabs">'+
			'<ul class="nav-tabs">'+
			'	<li ng-repeat="pane in panes" ng-class="{active:pane.selected}" ng-click="select(pane)">'+
			'		{{pane.title}}'+
			'	</li>'+
			'</ul>'+
			'<div class="pane-content" ng-transclude></div>'+
			'</div>',
		controller 	: function($scope){
			var panes 		= $scope.panes 	= [];
			$scope.select 	= function(pane){
				angular.forEach(panes,function(pane){
					pane.selected 	= false;
				});
				pane.selected 	= true;
			}
			this.addPane 	= function(pane){
				if(panes.length === 0 || panes.length === $scope.index-1 || pane.active){
					$scope.select(pane);
				}
          		panes.push(pane);
			}
		}
	};
})
.directive('tab',function(){
	return {
		require 	: '^tabs',
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		scope 		: {
			active 	: '=',
			title 	: '@'
		},
		template 	: '<div class="tab-pane" ng-class="{active:selected}" ng-transclude></div>',
		link 		: function($scope, iElm, iAttrs, ctrl){
			iElm.removeAttr('title');
			ctrl.addPane($scope);
		}
	};
})
/**
 * 面板
 * <pane title="标题" glyp="图标">内容</pane>
 */
.directive('pane', function(){
	return {
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		template 	: '<div class="pane">'
			+'<div class="pane-header">'
			+'<div class="pane-icon">'
			+'<span class="glyphicon glyphicon-{{icon()}}"></span>'
			+'</div>'
			+'<div class="pane-title">{{title}}</div>'
			+'<div class="pane-control" ng-if="control"><span class="glyphicon" ng-class="arrow()" ng-click="$toggle()"></span></div>'
			+'</div>'
			+'<div class="pane-content" ng-show="toggle" ng-transclude>'
			+'</div>'
			+'</div>',
		scope		: {
			title 	: '@',
			control : '=?',
			glyp 	: '@'
		},
		link		: function($scope,iElm,iAttrs){
			iElm.removeAttr('title');
			$scope.toggle 		= true;
			$scope.icon 		= function(){
				return $scope.glyp || 'th';
			}
			$scope.arrow 		= function(){
				return $scope.toggle ? 'glyphicon-chevron-down' : 'glyphicon-chevron-up';
			}
			$scope.$toggle 		= function(){
				$scope.toggle 	= !$scope.toggle;
			}
		}
	};
})
.directive('tag', function(){
	return {
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		template 	: '<div class="node">'
			+'	<div class="node-tag">{{title}}</div>'
			+'	<div ng-transclude></div>'
			+'</div>',
		scope		: {
			title 	: '@',
			control : '=?',
			glyp 	: '@'
		},
		link		: function($scope,iElm){
		}
	};
})
/**
 * 上传
 */
.directive('upload', function($parse){
	return {
    	replace 	: true,
    	template 	: '<div>'+
            '<img ng-if="$image && $path" src="'+($ROOT||'/')+'{{$path}}" class="img-thumbnail" style="margin-bottom:5px;" />'+
    		'<div class="progress" ng-if="$flag&&$percentage>0">'+
            '    <div class="progress-bar" style="width:{{$percentage}}%;">{{$percentage}}%</div>'+
            '</div>'+
    		'<div class="input-group">'+
	        '    <input class="form-control" type="text" name="path" ng-model="$path" placeholder="{{$placeholder}}" />'+
	        '    <span class="input-group-btn">'+
	        '        <button type="button" class="btn btn-default" ngf-select ng-model="$files" ng-disabled="$flag">{{$btn}}</button>'+
	        '    </span>'+
	        '</div>'+
	        '</div>',
	    controller 	: function($scope){
	    },
	    link  		: function($scope, iElm, iAttrs){
	    	$scope.$placeholder 	= iAttrs.placeholder || '请上传文件';
        	$scope.$btn 			= iAttrs.btn || '上传';
        	$scope.$url 			= iAttrs.url || '/upload';
        	$scope.$image			= iAttrs.image || false;
	    }
	};
})

.directive('extForm',function($resource,$location){
	return {
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		scope 		: {
			action 	: '@',
			cols 	: '@',
			pk 		: '@',
			route	: '=?'
		},
		template 	: '<div>'
			+'<div class="alert" ng-show="alert" ng-class="alert.cls">'
	        +'<span class="glyphicon glyphicon-exclamation-sign"></span>'
	        +'<span class="sr-only">Error:</span>'
	        +'<span class="text">{{alert.msg}}</span>'
	    	+'</div>'
			+'<form role="form" class="form-horizontal" name="extForm" method="POST" ng-submit="$submit(extForm.$valid)" novalidate ng-transclude>'
			+'</form>'
			+'</div>',
		controller 	: function($scope,$routeParams){
			$scope.pk 		= $scope.pk || '';
			$scope.route	= $scope.route || false;
			$scope.alert 	= false;
			// $scope.action 	= $scope.action.replace(/[^\/]+?$/,'read.html')
			if($scope.pk){
				$scope.action 		= $scope.action.replace(/(.+\/)(.+)(\..+)$/,'$1$2/'+$scope.pk+'/:'+$scope.pk+'$3');
				var pkVal 			= $routeParams[$scope.pk];
			}
			$scope.formData	= {};
			var model 		= $resource($scope.action);
			if(pkVal>-1){
				var postData 		= {};
				postData[$scope.pk]	= pkVal;
				model.get(postData,function(data){
					$scope.formData	= data.result;
				});
			}
			var items 		= $scope.items 	= [];
			this.add 		= function(item){
          		items.push(item);
			}
			$scope.$watch('items.length',function(newValue){
				angular.forEach(items,function(value, key){
					value.$cols 	= $scope.cols;
				});
			});
			$scope.$watch('formData',function(newValue){
				angular.forEach(items,function(value, key){
					value.item 		= newValue;
				});
			});
			$scope.$submit	= function($valid){
				var postData 		= {};
				postData[$scope.pk]	= pkVal;
				model.save(postData,$scope.formData,function(data){
					// $scope.result	= data;
					if(data && data.success == true){
						$scope.alert 	= {
							cls 		: 'alert-info',
							msg 		: data.msg
						};
						if($scope.route)
							$location.path("/");
					}else if(data.success == false){
						$scope.alert	= {
							cls 		: 'alert-danger',
							msg 		: data.msg
						};
					}else{
						alert(data);
					}
				});
			}
		},
		link  		: function($scope, iElm, iAttrs){
		}
	}
})

.directive('extFormItem',function(){
	return {
		require 	: '^extForm',
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		scope 		: {
			label 	: '@',
			cols 	: '@'
		},
		template 	: '<div class="form-group">'
		    +'    <label class="col-md-{{$_cols(0)}} control-label">{{label}}</label>'
		    +'    <div class="col-md-{{$_cols(1)}}" ng-transclude>'
		    +'    </div>'
		    +'</div>',
		controller 	: function($scope){
			var items 		= $scope.items 	= [];
			this.add 		= function(item){
          		items.push(item);
			}
			$scope.$watch('items.length',function(newValue){
				angular.forEach(items,function(value, key){
					value.$cols 	= $scope.cols;
				});
			});
			$scope.$watch('item',function(newValue){
				angular.forEach(items,function(value, key){
					value.item 		= newValue;
				});
			});
			$scope.$_cols	= function(idx){
				return ($scope.cols || $scope.$cols).split(',')[idx];
			}
		},
		link  		: function($scope, iElm, iAttrs, ctrl){
			ctrl.add($scope);
		}
	}
})
.directive('extFormItemText',function($compile){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			empty 	: '@'
		},
		link  		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			var model 	= $scope.name.replace(/\[([^\[\]]+?)?\]/g,function(m,n){
				if(angular.isUndefined(n)){
					return "[]";
				}else if(/^\d+$/.test(n)){
					return "["+n+"]";
				}else{
					return "."+n;
				}
			}).replace(/\[\]$/g,'');
			// $parse("item."+model.replace(/\[\d+\]$/g,'')).assign($scope,[]);
			var tpl 	= '<input class="form-control" type="text"{name|attr="name"}{model|attr="ng-model"}{list|attr="ng-list"}{value|attr="value"}{empty|attr="placeholder"} />';
			tpl 		= format(tpl,{
				name 	: $scope.name,
				model 	: "item."+model,
				value 	: "{{value}}",
				empty 	: $scope.empty,
				list 	: /\[\]$/.test($scope.name)?",":""
			});
			// alert(tpl)
			var html	= $compile(tpl)($scope);
			iElm.replaceWith(html);
		}
	};
})
/**
 * 表格
 * 例子：
 * 		<ext-form-item-array name="items" key="text" empty="请输入子级分类名称" />
 */
.directive('extFormItemArray', function(){
	return {
		require 	: '^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			empty 	: '@',
			key 	: '@',
			items	: '=?',
		},
		template 	: '<div>'
			+'<button class="btn btn-default" type="button" ng-click="$add()" ng-if="items.length<=0">'
			+'	<span class="glyphicon glyphicon-plus"></span>'
			+'</button>'
			+'<div class="col-sm-12" ng-repeat="n in items" style="margin:0;padding:0 0px 5px 0">'
			+'	<div class="input-group">'
			+'		<input class="form-control" type="text" ng-model="n[key]" placeholder="{{empty}}" />'
			+'		<span class="input-group-btn">'
			+'			<button class="btn btn-default" type="button" ng-click="$add()" ng-if="$last">'
			+'				<span class="glyphicon glyphicon-plus"></span>'
			+'			</button>'
			+'			<button class="btn btn-default" type="button" ng-click="$remove($index)">'
			+'				<span class="glyphicon glyphicon-minus"></span>'
			+'			</button>'
			+'		</span>'
			+'	</div>'
			+'</div>'
			+'</div>',
		link 		: function($scope, iElm, iAttrs, ctrl){
			$scope.items	= $scope.items || [];
			ctrl.add($scope);
			$scope.$add		= function(){
				var json	= angular.fromJson('{"'+$scope.key+'":""}');
				$scope.items.push(json);
			}
			$scope.$remove	= function(idx){
		        $scope.items.splice(idx,1);
		    }
		    $scope.$watch('key',function(newValue){
		    	$scope.key	= $scope.key || 'name';
		    });
		    $scope.$watch('item',function(newValue){
		    	if(!newValue[$scope.name]){
		    		newValue[$scope.name]	= [];
		    	}
		    	$scope.items				= newValue[$scope.name];
			});
		}
	};
})
/**
 * 表格
 * 例子：
 * 		<ext-form-item-array-dropdown name="items" items="[{key:ID,val:'分类名'}]" key="text" empty="请选择分类名称" />
 */
.directive('extFormItemArrayDropdown',function($compile){
	return {
		require 	: '^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			empty 	: '@',
			key 	: '@',
			items	: '=?',
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			$scope.rows		= [];
			$scope.items	= $scope.items || [];
			ctrl.add($scope);
			$scope.$add		= function(){
				var json	= angular.fromJson('{"'+$scope.key+'":""}');
				$scope.rows.push(json);
			}
			$scope.$remove	= function(idx){
		        $scope.rows.splice(idx,1);
		    }
		    $scope.$watch('key',function(newValue){
		    	$scope.key	= $scope.key || 'name';
		    });
		    $scope.$watch('item',function(newValue){
		    	if(!newValue[$scope.name]){
		    		newValue[$scope.name]	= [];
		    	}
		    	$scope.rows					= newValue[$scope.name];
			});
		    var tpl			= '<div>'
				+'<button class="btn btn-default" type="button" ng-click="$add()" ng-if="rows.length<=0">'
				+'	<span class="glyphicon glyphicon-plus"></span>'
				+'</button>'
				+'<div class="col-sm-12" ng-repeat="n in rows" style="margin:0;padding:0 0px 5px 0">'
				+'	<div class="input-group">'
				+'		<select class="form-control" ng-model="n[key]"><option value="">{empty}</option>{dropdown|each=items}</select>'
				+'		<span class="input-group-btn">'
				+'			<button class="btn btn-default" type="button" ng-click="$add()" ng-if="$last">'
				+'				<span class="glyphicon glyphicon-plus"></span>'
				+'			</button>'
				+'			<button class="btn btn-default" type="button" ng-click="$remove($index)">'
				+'				<span class="glyphicon glyphicon-minus"></span>'
				+'			</button>'
				+'		</span>'
				+'	</div>'
				+'</div>'
				+'</div>';
			tpl 		= format(tpl,{
				dropdown: '<option value="{key}">{val}</option>',
				items	: $scope.items,
				empty 	: $scope.empty
			});
			var html		= $compile(tpl)($scope);
			iElm.replaceWith(html);
		}
	};
})
/**
 * 表格
 * 例子：
 * 		<ext-form-item-array-grid name="items" cols="[{label:'名称',name:'name',type:'input'},{label:'操作',width:130,name:'id',type:'action'}]" empty="请输入控制器名称" />
 */
.directive('extFormItemArrayGrid', function(){
	return {
		require 	: '^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			cols 		: '=',
			items 		: '=',
			addfn 		: '=',
			title 		: '@',
			name 		: '@',
			action 		: '=?'
		},
		template 	: '<div>'+
					'<button type="button" class="btn btn-default" ng-click="$add()" style="margin-bottom:5px">添加{{title}}</button>'+
					'<grid name="{{name}}" cols="cols" items="items" empty="没有{{title}}" action="action" />'+
					'</div>',
		controller 	: function($scope){
			$scope.action 	= $scope.action || [{label:'向上'},{label:'向下'},{label:'删除'}];
			$scope.$watch('item',function(newValue){
		    	if(!newValue[$scope.name]){
		    		newValue[$scope.name]	= [];
		    	}
		    	$scope.items				= newValue[$scope.name];
			});
			var fields		= [];
			$scope.$add		= function(){
				var item 	= {};
				if(angular.isFunction($scope.addfn)){
					item 	= $scope.addfn();
				}else if(angular.isObject($scope.addfn)){
					item 	= angular.copy($scope.addfn);
				}else{
					if(fields.length == 0){
						angular.forEach($scope.cols,function(v){
							if(v.name)
								fields.push('"'+v.name+'":"'+(v.default||'')+'"');
						});
					}
					item 	= angular.fromJson('{'+fields.join(',')+'}');
				}
				$scope.items.push(item);
			}
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			ctrl.add($scope);
			iElm.removeAttr('title');
		}
	};
})
/**
 * 单选组
	<radio-group name="名称" index="默认选项" value="已选值">
        <radio value="选项值">选项名称</radio>
        <radio value="选项值">选项名称</radio>
    </radio-group>
 */
.directive('radioGroup', function($parse){
	return {
		require 	: '^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		template 	: '<div class="btn-group radioGroup" ng-transclude></div>',
		scope		: {
			index 	: '=?',
			name 	: '@',
			value 	: '@',
			fit 	: '=?',
			size 	: '=?',
			disabled: '=',
			item 	: '=?'
		},
		controller 	: function($scope,$element){
			$scope.fit		= $scope.fit || false;
			var items 		= $scope.items 	= [];
			this.add 		= function(item){
				if((!$scope.value && (items.length === 0 || items.length === $scope.index-1 || item.active)) || item.value == $scope.value){
					this.select(item);
				}
          		items.push(item);
			}
			this.select 	= function(item){
				angular.forEach(items,function(item){
					item.checked 	= false;
				});
				item.checked 	= true;
				$parse("item."+item.name).assign($scope,item.value);
			}
			$scope.$watch('item',function(newValue){
				var value 	= newValue[$scope.name];
				angular.forEach(items,function(item){
					if(!value && item.checked || value == item.value){
						item.checked 	= true;
						$parse("item."+$scope.name).assign($scope,item.value);
					}else{
						item.checked 	= false;
					}
				});
			});
			if($scope.fit){
				$element.css("width","100%");
			}
			$scope.$watch('items.length',function(newValue){
				angular.forEach(items,function(value, key){
					if($scope.fit){
						if(value.col<=0) value.col = 12 / newValue;
						if($scope.disabled) value.disable = $scope.disabled;
					}
					if($scope.name){
						value.name 	= $scope.name;
					}
					if($scope.size){
						value.size 	= $scope.size - 1;
					}
				});
			});
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			ctrl.add($scope);
		}
	};
})
.directive('radio',function($parse){
	return {
		require 	: '^radioGroup',
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			disabled: '=',
			col		: '=?'
		},
		template 	: '<div class="btn btn-default" ng-class="$cls()" ng-disabled="$disabled(item)" ng-click="$click()">'
			+'<input type="radio" name="{{name}}" value="{{value}}" ng-checked="checked" ng-hide="true" />'
			+'<span class="icon" ng-class="{active:checked}"></span>'
			+'<span ng-transclude></span>'
			+'</div>',
		controller 	: function($scope){
			$scope.item			= $scope.item || {};
			$scope.col 			= $scope.col || 0;
			$scope.size			= ($scope.size || 3) - 1;
			$scope.$cls			= function(){
				var res 		= [];
				if($scope.checked){
					res.push("active");
				}
				if($scope.col > 0){
					res.push("col-md-"+$scope.col);
				}
				if($scope.size > -1){
					res.push(['btn-xs','btn-sm','','btn-lg'][$scope.size]);
				}
				return res.join(' ');
			}
			$scope.$disabled	= function(item,type){
				if($scope.disabled)
					return $scope.disabled(item);
				else if($scope.disable)
					return $scope.disable(item);
				return false;
			}
			$scope.$glyphicon	= function(){
				return ['glyphicon glyphicon-remove-circle','glyphicon glyphicon-ok-circle'][$scope.checked?1:0];
			}
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			ctrl.add($scope);
			$scope.$click 	= function(){
				ctrl.select($scope);
			}
		}
	};
})
.directive('extFormButtons',function(){
	return {
		require 	: '^extForm',
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		scope 		: {
			label 	: '@',
			cols 	: '@'
		},
		template 	: '<div class="form-group">'
		    +'    <div class="btn-group col-md-offset-{{$_cols(0)}} col-md-{{$_cols(1)}}" ng-transclude>'
		    +'    </div>'
		    +'</div>',
		controller 	: function($scope){
			var items 		= $scope.items 	= [];
			this.add 		= function(item){
          		items.push(item);
			}
			$scope.$watch('items.length',function(newValue){
				angular.forEach(items,function(value, key){
					value.$cols 	= $scope.cols;
					value.item 		= $scope.item;
				});
			});
			$scope.$_cols	= function(idx){
				return ($scope.cols || $scope.$cols).split(',')[idx];
			}
		},
		link  		: function($scope, iElm, iAttrs, ctrl){
			ctrl.add($scope);
		}
	}
})

/**
 * 表格
<grid name="mm" cols="[{label:'名称',width:50,type:'number',align:'center'},{label:'名称',width:50,name:'name',type:'key',align:'center'},{label:'名称',name:'name',type:'input'},{label:'名称',name:'name',type:'select',items:[{key:'1',val:'m1'},{key:'2',val:'m2'}]},{label:'名称1',name:'name',type:'status'},{label:'ID',name:'id'},{label:'名称',name:'name'},{label:'操作',width:130,align:'center',name:'id',type:'action'}]" items="[{},{},{},{},{}]" status="$status" />
 */
.directive('grid',function($compile,$rootScope,$filter){
	return {
		restrict			:'E',
		replace				: true,
		scope				: {
			name 			: '@',
			cols 			: '=?',
			items 			: '=?',
			action 			: '=?',
			empty 			: '@',
			page 			: '=',
			goto 			: '=',
			status			: '=',
			edit 			: '=',
			remove 			: '=',
			up 				: '=',
			down 			: '=',
			sort 			: '='
		},
		controller 			: function($scope){
			$scope.items	= $scope.items || [];
			$scope.action	= $scope.action || [{label:'向上'},{label:'向下'},{label:'删除'}];
			$scope.empty	= $scope.empty || "没有数据";
			$scope.$edit		= function(item){
				if($scope.edit)
					$scope.edit(item);
			}
			$scope.$remove	= function(item){
				var idx 	= this.$parent.$parent.$index;
				item.remove	= function(){
					$scope.items.splice(idx,1);
				}
		    	if($scope.remove)
		    		$scope.remove(item,idx);
		    	else
		        	item.remove();
		    }
		    $scope.$up 		= function(item){
		    	var idx 	= this.$parent.$parent.$index;
		    	item.update	= function(){
		    		$scope.items.splice(idx,1);
			        $scope.items.splice(idx-1,0,item);
			        // angular.forEach($scope.items,function(value,key){
			        // 	value['sort']	= key + 1;
			        // });
		    	}
		    	if($scope.up){
		    		$scope.up(item,idx);
		    	}else{
			        item.update();
		    	}
		    }
		    $scope.$down	= function(item){
		    	var idx 	= this.$parent.$parent.$index;
		    	item.update	= function(){
		    		$scope.items.splice(idx,1);
			        $scope.items.splice(idx+1,0,item);
			        // angular.forEach($scope.items,function(value,key){
			        // 	value['sort']	= key + 1;
			        // });
		    	}
		    	if($scope.down)
		    		$scope.down(item,idx);
		    	else{
			        item.update();
			    }
		    }
		    $scope.$sort        = function(item,field){
		    	item.update 	= function(){
		    		item.disabled	= !item.disabled;
		    		item['_backup_'+field]	= item[field];
			        $scope.items 	= $filter("orderBy")($scope.items,field);
		    	}
		    	item.disabled 	= !item.disabled;
		    	if($scope.sort){
		    		$scope.sort(item);
		    	}else{
			        item.update();
		    	}
		    }
		    $scope.$edit		= function(item){
		    	var idx 		= this.$parent.$parent.$index;
				if($scope.edit)
					$scope.edit(item,idx);
			}
		},
		link 				: function($scope, iElm, iAttrs){
			var dataIndex			= function(val,row,plugin){
				$result 			= "";
				row.plugin			= plugin;
				switch(row.type){
					case 'number':
						$result		+= '{{$index+1}}';
						break;
					case 'key':
						$result		+= 'item.'+val;
						break;
					case 'badge':
						$result		+= format('{0}<span class="badge pull-right">{1}</span>','{{item.'+val+'}}','{{item.'+row.items+'.length}}');
						break;
					case 'time':
						$result		+= '{{item.'+val+'|timeFormat}}'
						break;
					case 'ip':
						$result		+= '{{item.'+val+'|ip}}';
						break;
					case 'image':
						$result		+= '<img src="'+($ROOT || '/')+'{{item.'+val+'}}" class="img-thumbnail" />';
						break;
					case 'hidden':
						$result		+= format('<input type="hidden"{name|this.nameFn} ng-value="item.{name}" />',row)+'{{item.'+row.name+'}}';
						break;
					case 'read':
						$result		+= format('<input class="form-control" type="text"{name|this.nameFn} ng-model="item.{name}" readonly />',row);
						break;
					case 'input':
						$result		+= format('<input class="form-control" type="text"{name|this.nameFn} ng-model="item.{name}" placeholder="请输入{label}" />',row);
						break;
					case 'date':
						$result		+= dateFn(val,row);
						break;
					case 'array':
						$result		+= arrayFn(val,row);
						break;
					case 'select':
						$result		+= selectFn(val,row);
						break;
					case 'sort':
						$result		+= sortFn(val,row);
						break;
					case 'status':
						$result		+= statusFn(val,row);
						break;
					case 'action':
						$result		+= actionFn(val,row);
						break;
					default:
						$result		+= val?'{{item.'+val+'}}':'';
						break;
				}
				return $result;
			}
			var nameFn				= function(val){
				if($scope.name){
					return format(' name="{0}[{1}][{2}]"',$scope.name,'{{$index}}',val);
				}
				return '';
			}
			var dateFn				= function(val,row){
				var result 			= [];
				if(!angular.isArray(val)){
					val 			= [val];
				}
				angular.forEach(val, function(value, key){
					result.push('{{item.'+value+'*1000|dateFormat}}');
				});
				return result.join(row.separator||'<br/>');
			}
			var arrayFn				= function(val,row){
				if(row.sep){
					return '{{item.'+val+'.join("'+row.sep+'")}}'
				}else{
					return format('<ol class="array" ng-if="item.{0}.length>0"><li ng-repeat="n in item.{0}">{1}</li></ol>',val,'{{n}}');
				}
			}
			var selectFn			= function(val,row){
				var tpl 			= '<select class="form-control"{name|this.nameFn} ng-model="item.{name}">{empty|map=option}{items|map=option}</select>';
				return format(tpl,{
					name 	: row.name,
					option 	: '<option value="{key}">{val}</option>',
					empty 	: [{key:'',val:'请选择'+row.label}],
					items 	: row.items,
					plugin	: row.plugin
				});
			}
			var sortFn				= function(val,row){
				var tplGroup 		= '<div class="input-group">{0}{1}</div>';
				var tplInput 		= '<input class="form-control" type="text"{name|this.nameFn} ng-model="item.{name}" ng-init="item._backup_{name} = item.{name}" placeholder="请输入{label}" style="text-align:center;" />';
				var tplSave			= '<span class="input-group-btn"><button class="btn btn-default" type="button" ng-disabled="item.disabled || item._backup_{name} == item.{name}" ng-click="$sort(item,\'{name}\')"><span class="glyphicon glyphicon-floppy-save"></span></button></span>';
				return format(tplGroup,format(tplInput,row),format(tplSave,row));
			}
			var statusFn			= function(name,row){
				var aryAction		= row.items || ['启用|禁用','状态'];
				var len				= 12/aryAction.length;
				var tplBtnGroup		= '<btn-group item="item" click="status">{items|this.filter|map=tplBtn}</btn-group>';
				var tplBtn 			= '<btn-status name="{name}">{label}</btn-status>';
				var tplBtns 		= format(tplBtnGroup,{
					items			: aryAction,
					tplBtn			: tplBtn,
					plugin			: {
						filter 		: function(val){
							var res 		= [];
							angular.forEach(val,function(value){
								res.push({
									col 	: len,
									name 	: name,
									label 	: value,
								});
							});
							return res;
						}
					}
				});
				return tplBtns;
			}
			var actionFn			= function(val,row){
				var aryAction		= row.items || $scope.action || [];
				var btns 			= 'up&down&edit&delete&';
				var iconAction		= {
					'向上'			: {icon:'arrow-up',action:'$up',disabled:'$first'},
					'向下'			: {icon:'arrow-down',action:'$down',disabled:'$last'},
					'编辑'			: {icon:'edit',action:'$edit'},
					'修改'			: {icon:'pencil',action:'$modify'},
					'删除'			: {icon:'trash',action:'$remove'},
					'选择'			: {icon:'ok',action:'$select'},
					'放入回收站'	: {icon:'remove',action:'$recycle'},
					'恢复'			: {icon:'ok',action:'$restore'},
				};
				var tplBtn 		 	= '<btn-icon click="{action}"{disabled|attr="disable"}>{label}</btn-icon>';
				var tplBtnGroup		= '<btn-group item="item">{items|this.filter=action|map=tplBtn}</btn-group>';
				var tplBtns 		= format(tplBtnGroup,{
					items	: aryAction,
					action 	: iconAction,
					tplBtn	: tplBtn,
					plugin		: {
						filter 	: function(val,action){
							var res 		= [];
							angular.forEach(val,function(value){
								var act 	= action[value.label];
								res.push({
									label 	: value.label,
									action 	: act.action,
									disabled: act.disabled
								});
							});
							return res;
						}
					}
				});
				return tplBtns;
			}
			var tpl			= '<table class="table table-bordered" style="margin:0">'
				+'	<thead>'
				+'		<tr>{th|each=cols}</tr>'
				+'	</thead>'
				+'	<tbody>'
				+'		<tr ng-repeat="item in items">{td|each=cols,plugin}</tr>'
				+'		{emptyTpl|format=empty}'
				+'		{pageTpl|format=empty|IFF=page}'
				+'	</tbody>'
				+'</table>';
			tpl 			= format(tpl,{
				cols 		: $scope.cols,
				th 			: '<th{col|cols}{width|attr="width"}{align|attr="align"}>{label}</th>',
				td 			: '<td{align|attr="align"}{class|attr="class"}>{name|this.dataIndex=values,plugin}</td>',
				emptyTpl	: '<tr ng-if="items.length<=0"><td colspan="{colspan}" align="center">{empty}</td></tr>',
				pageTpl		: '<tr ng-if="items.length>0"><td colspan="{colspan}" align="center"><pagination count="page.count" size="page.size" goto="goto" /></td></tr>',
				empty 		: {colspan:$scope.cols.length,empty:$scope.empty},
				page 		: $scope.page,
				plugin		: {
					dataIndex 	: dataIndex,
					nameFn		: nameFn
				}
			});
			// alert(tpl)
			var html		= $compile(tpl)($scope);
			iElm.replaceWith(html);
		}
	};
})
.directive('pagination',function(){
	return {
		restrict	:'EA',
		transclude	: false,
		replace		: true,
		scope		: {
			'size'	: '=?',
			'count'	: '=?',
			'goto'	: '=?'
		},
		template	: '<div class="page">'
			+'<div class="page-left">'
			+'从 {{firstRow}} 到 {{lastRow}} /共 {{count}} 条数据'
			// +'第 {{page}} 页 / 共 {{pagecount}} 页 / 每页 {{pagesize}} 条数据'
			+'</div>'
			+'<select class="form-control pagesize" ng-model="size" ng-options="node as node for node in sizes">'
			+'</select>'
			+'	<div class="input-group">'
			+'		<input class="form-control" type="text" ng-model="p" />'
			+'		<span class="input-group-btn">'
			+'			<span class="btn btn-default" type="button" ng-click="$go(p)">GO</span>'
			+'		</span>'
			+'	</div>'
			+'<div class="page-right">'
			+'	<div class="btn-group">'
			+' 		<span class="btn btn-default" ng-disabled="$first" ng-click="$gofirst()"><b class="glyphicon glyphicon-fast-backward"></b></span>'
			+' 		<span class="btn btn-default" ng-disabled="$first" ng-click="$goprev()"><b class="glyphicon glyphicon-step-backward"></b></span>'
			+' 		<span class="btn btn-default" ng-repeat="i in pages" ng-class="$active(i)" ng-click="$goto(i)">{{i}}</span>'
			+' 		<span class="btn btn-default" ng-disabled="$last" ng-click="$gonext()"><b class="glyphicon glyphicon-step-forward"></b></span>'
			+' 		<span class="btn btn-default" ng-disabled="$last" ng-click="$golast()"><b class="glyphicon glyphicon-fast-forward"></b></span>'
			+'	</div>'
			+'</div>'
			+'</div>',
		controller 	: function($scope){
			$scope.page 		= 1;
			$scope.pagesize		= 10;
			$scope.size			= $scope.pagesize;
			$scope.pagecount	= Math.ceil($scope.count / $scope.pagesize);
			$scope.sizes 		= [10,20,50,80,100];
			var limit 			= 5;
			$scope.$watch('size', function(newValue, oldValue, scope){
				if(newValue>0){
					$scope.pagesize		= parseInt(newValue);
					$scope.pagecount	= Math.ceil($scope.count / $scope.pagesize);
					if($scope.page>$scope.pagecount)
						$scope.$goto($scope.pagecount);
					setPages($scope.page);
				}
			});
			$scope.$watch('page', function(newValue, oldValue, scope){
				setPages(newValue);
			});
			var setPages		= function(newValue){
				$scope.p 		= newValue;
				$scope.firstRow	= (newValue - 1) * $scope.pagesize + 1;
				$scope.lastRow	= Math.min($scope.firstRow + $scope.pagesize - 1,$scope.count);
				$scope.$first	= $scope.page === 1;
				$scope.$last	= $scope.page === $scope.pagecount;
				$scope.pages 	= getPages(newValue);
			}
			$scope.$gofirst		= function(){
				$scope.$goto(1);
			};
			$scope.$goprev		= function(){
				$scope.$goto($scope.page-1);
			};
			$scope.$active		= function(number){
				return $scope.page === number?'active disabled':'';
			};
			$scope.$goto		= function(number){
				$scope.page  	= parseInt(number);
				if(angular.isFunction($scope.goto))
					$scope.goto(number);
			};
			$scope.$gonext		= function(){
				$scope.$goto($scope.page+1);
			};
			$scope.$golast		= function(){
				$scope.$goto($scope.pagecount);
			};
			$scope.$go			= function(number){
				var page  		= parseInt(number);
				if(page>$scope.pagecount){
					page 		= $scope.pagecount;
					$scope.p 	= page;
				}
				$scope.$goto(page);
			};
			function getPages(page){
				var p			= Math.floor(limit / 2);
				var startPage	= 1,
					endPage		= limit;
				startPage		= Math.max(Math.min(page - p,$scope.pagecount - limit + 1),1);
				endPage			= Math.min(Math.max(page + p,limit),$scope.pagecount);
				var pages		= [];
				for(var number = startPage; number <= endPage; number++){
					pages.push(number);
				}
				return pages;
			}
		},
		link 		: function($scope, iElm, iAttrs){

		}
	};
})
.directive('ngLoading',function($compile,$rootScope){
	return {
		restrict 	: 'A',
		link 		: function($scope, iElm, iAttrs){
			var tpl 	= iElm.html();
			$scope.$watch(iAttrs.ngLoading, function(newValue, oldValue){
				if(newValue){
					iElm.html('<span class="loader"></span>');
				}else{
					iElm.html($compile('<span>'+tpl+'</span>')($scope));
				}
			});
		}
	};
})
.directive('ngTemplate',function($compile,$parse){
	return {
		restrict 	: 'A',
		link 		: function($scope, iElm, iAttrs){
			$scope.$watch(iAttrs.ngTemplate, function(newValue, oldValue){
				if(/^<([^>\s]+)[^>]*>(.*?<\/\1>)?$/i.test(newValue)){
					var html	= angular.element(newValue);
				}else{
					var html	= angular.element('<span>'+newValue+'</span>');
				}
				var content		= $compile(html)($scope);
				iElm.html(content);
			});
		}
	};
})
/**
 * 按钮组
    <btn-group item="$index">
        <btn-icon glyp="向上" disable="$first" click="$up">向前排一位</btn-icon>
        <btn-icon glyp="向下" disable="$last" click="$down">向后排一位</btn-icon>
        <btn-icon click="$edit">编辑</btn-icon>
        <btn-icon click="$remove">删除</btn-icon>
    </btn-group>
 */
.directive('btnGroup', function(){
	return {
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		template 	: '<div class="btn-group col-md-12" ng-transclude></div>',
		scope		: {
			size	: '=?',
			item 	: '=?',
			key 	: '@',
			val 	: '=',
			click 	: '=',
			disable : '='
		},
		controller 	: function($scope){
			var items 		= $scope.items 	= [];
			this.add 		= function(item){
          		items.push(item);
			}
			$scope.$watchCollection("items",function(newValue,oldValue){
				angular.forEach(newValue,function(value, key){
					value.$index = key + 1;
					if($scope.size>-1) value.size = $scope.size - 1;
					if(!value.col) value.col = 12 / newValue.length;
					if($scope.click) value.click = $scope.click;
					if($scope.disable) value.disable = $scope.disable;
					if(!value.item) value.item = $scope.item;
				});
			});
			// $scope.$watch("items",function(newValue,oldValue){
			// 	alert("watch="+newValue)
			// });
			// $scope.$watch("items.length",function(newValue,oldValue){
			// 	alert("watch1="+newValue)
			// });
			// $scope.$watch('items.length',function(newValue){alert(newValue)
			// 	angular.forEach(items,function(value, key){
			// 		if(value.col<=0) value.col = 12 / newValue;
			// 		if($scope.disabled) value.disable = $scope.disabled;
			// 		value.item 	= $scope.item;
			// 	});
			// });
		}
	};
})

.directive('btnText',function($compile){
	return {
		require 	: '?^btnGroup',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			type 	: '@',
			title 	: '@',
			size	: '=?',
			col 	: '=?',
			disable : '=?',
			load 	: '=?',
			click 	: '=',
			item 	: '=?',
		},
		controller 	: function($scope){
			$scope.size			= ($scope.size || 3) - 1;
			$scope.$watch('load',function(newValue){
				if(angular.isFunction(newValue))
					newValue($scope);
			});
			$scope.$col		= function(){
				return $scope.col?" col-md-"+$scope.col:"";
			}
			$scope.$disabled	= function(item){
				if(angular.isFunction($scope.disable))
					return $scope.disable(item);
				else
					return $scope.disable;
				return false;
			}
			$scope.$click		= function(item){
				if($scope.click)
					$scope.click.call(this,item);
			}
			$scope.$size		= function(){
				return [' btn-xs',' btn-sm','',' btn-lg'][$scope.size];
			}
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			var tpl 		= '<div><button class="btn btn-default{size}{col}"'
				+'{type|attr="type"}{disabled|attr="ng-disabled"}{click|attr="ng-click"}{title|attr="title"}'
				+' ng-template="{text}">'
				+'</button></div>';
			$scope.text 	= iElm.html();
			$scope.title 	= $scope.title || iElm.text(),
			tpl				= format(tpl,{
				size 		: '{{$size()}}',
				col 		: '{{$col()}}',
				type		: $scope.type || 'button',
				title		: '{{title}}',
				disabled 	: '$disabled(item)',
				click 		: '$click(item)',
				text 		: 'text'
			});
			var html		= $compile(tpl)($scope);
			iElm.replaceWith(html.find('button'));
		}
	};
})
.directive('btnIcon',function($compile){
	return {
		require 	: '?^btnGroup',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			type 	: '@',
			title 	: '@',
			glyp 	: '@',
			size	: '=?',
			col 	: '=?',
			disable : '=?',
			load 	: '=?',
			click 	: '=',
			item 	: '=?',
		},
		controller 	: function($scope){
			$scope.$load		= function(scope){
				scope.$icon 	= function(item){
					return $scope.$glyphicon();
				}
				if(angular.isFunction($scope.load))
					$scope.load(scope);
			}
			$scope.$glyphicon	= function(){
				var icons 		=  {
					'向上'			: 'arrow-up',
					'向下'			: 'arrow-down',
					'编辑'			: 'edit',
					'修改'			: 'pencil',
					'删除'			: 'trash',
					'移除'			: 'remove',
					'选择'			: 'ok'
				}
				return icons[$scope.text] || icons[$scope.glyp] || $scope.glyp || icons['删除'];
			}
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			var tpl 		= '<div><btn-text load="$load" size="size" col="col" disable="disable" click="click" item="item"'
				+'{type|attr="type"}{title|attr="title"}>'
				+'<span class="glyphicon glyphicon-{icon}"></span></btn-text></div>';
			$scope.text 	= $scope.title || iElm.text();
			var tpl			= format(tpl,{
				type		: $scope.type,
				title		: $scope.text,
				icon 		: '{{$icon()}}'
			});
			var html		= $compile(tpl)($scope);
			iElm.replaceWith(html.find('button'));
		}
	};
})
.directive('btnStatus',function($compile){
	return {
		require 	: '?^btnGroup',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '=?',
			title 	: '@',
			size	: '=?',
			col 	: '=?',
			disable : '=?',
			click 	: '=',
			item 	: '=?',
		},
		controller 	: function($scope){
			var name			= $scope.name || 'status';
			$scope.$watch('$index',function(newValue){
				$scope.value 	= 1 << newValue - 1;
			});
			$scope.$glyphicon	= function(){
				var icons 		=  {
					'向上'			: 'arrow-up',
					'向下'			: 'arrow-down',
					'编辑'			: 'edit',
					'修改'			: 'pencil',
					'删除'			: 'trash',
					'移除'			: 'remove',
					'选择'			: 'ok'
				}
				return icons[$scope.text] || icons[$scope.glyp] || $scope.glyp;
			}
			$scope.$load		= function(scope){
				scope.tpl		= '{{$status(item)}}';
				scope.$status 	= function(item){
					scope.title = item&&item[name]&$scope.value?$scope.text[1]:$scope.text[0];
					return scope.title;
				}
				scope.$icon		= function(item){
					return item&&item[name]&$scope.value?'glyphicon-ok':'glyphicon-remove';
				}
			}
			$scope.$disable		= function(item){
				var res 		= false;
				if(angular.isFunction($scope.disable))
					res 		= $scope.disable(item);
				else
					res 		= $scope.disable;
				return (res || this.disabled);
			}
			$scope.$click		= function(item){
				var self 		= this;
				self.update 		= function(){
		    		self.disabled 	= !self.disabled;
		    		self.loader		= !self.loader;
		    		item[name]		^= $scope.value;
		    	}
		    	self.loader 		= !self.loader;
		    	self.disabled 		= !self.disabled;
				if($scope.click)
					$scope.click.call(this,item,this.$parent.$parent.$index,$scope.value,name);
				else{
					self.update();
				}
			}
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			$scope.text 	= ($scope.title || iElm.text()).split('|');
			$scope.title 	= $scope.text[0];
			if($scope.text.length>1){
				var tpl 	= '<div><btn-text size="size" col="col" disable="$disable" load="$load" click="$click" item="item"'
					+'{type|attr="type"}>'
					+'<span ng-loading="loader" item="item">{{$status(item)}}</span>'
					+'</btn-text></div>';
			}else{
				var tpl 	= '<div><btn-text size="size" col="col" disable="$disable" load="$load" click="$click" item="item"'
					+'{type|attr="type"}{title|attr="title"}>'
					+'<span ng-loading="loader" item="item"><span class="glyphicon" ng-class="$icon(item)"></span></span>'
					+'</btn-text></div>';
			}
			var tpl			= format(tpl,{
				type		: 'button',
				title		: $scope.title,
			});
			var html		= $compile(tpl)($scope);
			iElm.replaceWith(html.find('button'));
			iElm.removeAttr('title');
		}
	};
})


.directive('tree',function($compile){
	return {
		restrict		:'E',
		replace			: true,
		scope			: {
			data		: '='
		},
		link			: function($scope,$element,attrs){
			var data	= $scope.data;
			var tpl		= '<ul ng-if="data&&data.length>0">'
						+'<li ng-repeat="n in data">'
						+'<a href="{{n.name}}">{{n.name}}</a>'
						+'<tree data="n.items"></tree>'
						+'</li>'
						+'</ul>';
			var html	= $compile(tpl)($scope);
			$element.replaceWith(html);
		}
	};
})
}catch(e){alert(e)}