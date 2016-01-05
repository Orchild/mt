angular.module("directive",[])
/**
 * 计时器
 * 例子：
 * 		<timer format="y-M-d h:mm:ss"></timer>
 * 		<span timer="y-M-d h:mm:ss"></span>
 */
.directive('timer', ['$interval','dateFilter', function($interval,dateFilter){
	return {
		restrict: 'EA', // E = Element, A = Attribute, C = Class, M = Comment
		link: function($scope, iElm, iAttrs) {
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
.directive('ngLazyload',function($compile){
	return {
		restrict 	: 'A',
        terminal 	: true,
        priority 	: 2000,
		link  		: function($scope, iElm, iAttrs){
			var tpl 		= '<div oc-lazy-load="{json}"><div ng-controller="{controller}">{content}</div></div>';
			tpl 			= format(tpl,{
				json 		: "{'type':'js','path':'"+$JS+iAttrs.ngLazyload+".js'}",
				controller 	: iAttrs.ngController,
				content 	: iElm.html()
			});
			var html		= $compile(tpl)($scope);
			iElm.replaceWith(html);
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
.service('$Compiler',function($q,$http,$injector,$compile,$controller,$templateCache){
	var fetchTemplate 		= function(options){
		if(options.templateUrl){
			return $http.get(options.templateUrl,{cache:$templateCache}).then(function(res){
				return res.data;
			});
		}else{
			return $q.when(options.template);
		}
	}
	this.compile 			= function(options){
		var controller 		= options.controller;
		var transformTemplate = options.transformTemplate || angular.identity;
		var resolve 		= angular.copy(options.resolve || {});
		angular.forEach(resolve,function(value,kkey){
			if(angular.isString(value)){
				resolve[k]	= $injector.get(value);
			}else{
				resolve[k]	= $injector.invoke(value);
			}
		});
		resolve.$template 	= fetchTemplate(options);
		return $q.all(resolve).then(function(locals){
			var template 	= transformTemplate(locals.$template);
			var element 	= angular.element('<div></div>').html(template.trim()).contents();
			var linkFn 		= $compile(element);
			return {
				locals 		: locals,
				element 	: element,
				link 		: function(scope){
					locals.$scope 	= scope;
					if(controller){
						$controller(controller,locals);
					}
					return linkFn.apply(null,arguments);
				}
			};
		});
	}
})
.provider('overlay',function(){
	var defaults 			= {
		templateUrl 		: '',
		template 			: '',
		container 			: false,
		backdrop 			: true,
		keyboard 			: true,
		show 				: true
	};
	this.$get				= function($window,$rootScope,$Compiler,$sce,$animate){
		var forEach 		= angular.forEach;
		var trim 			= String.prototype.trim;
		var bodyElement 	= angular.element($window.document.body);
		var findElement 	= function(query, element) {
			return angular.element((element || document).querySelectorAll(query));
		}
		var Factory 		=  function(config){
			var $modal 		= {};
			var options 	= $modal.$options = angular.extend({},defaults,config);
			var promise 	= $modal.$promise = $Compiler.compile(options);
			var scope 		= $modal.$scope = options.scope && options.scope.$new() || $rootScope.$new();
			forEach(['title','content'],function(key){
				if(options[key]) scope[key] = $sce.trustAsHtml(options[key]);
			});
			scope.$hide 	= function(){
				scope.$$postDigest(function(){
					$modal.hide();
				});
			};
			scope.$show 	= function(){
				scope.$$postDigest(function(){
					$modal.show();
				});
			};
			scope.$toggle 	= function(){
				scope.$$postDigest(function(){
					$modal.toggle();
				});
			};
			var compileData, modalElement, modalScope;
			var backdrop  	= angular.element('<div class="dialog-overlay"></div>');
			promise.then(function(data){
				compileData = data;
				$modal.init();
			});
			var resize		= function(){
				modalElement.css({
					'top'		: angular.element(window).height() / 2 - modalElement.height() / 2,
					'left'		: angular.element(window).width() / 2 - modalElement.width() / 2,
				});
			}
			var show 		= function(){
				modalScope 	= $modal.$scope.$new();
				modalElement= compileData.link(modalScope,function(clonedElement,scope){});
				$animate.enter(modalElement,bodyElement,null).then(function(){
					modalElement[0].focus();
					resize();
				});
				$animate.enter(backdrop,bodyElement,null);
			}
			var hide 		= function(){
				$animate.leave(modalElement)
				$animate.leave(backdrop);
			}
			$modal.init 	= function(){
				if(options.show){
					scope.$$postDigest(function(){
						$modal.show();
					});
				}
				return this;
			}
			$modal.destroy 	= function(){
				scope.$destroy();
				return this;
			}
			$modal.resize 	= function(){
				promise.then(resize);
				return this;
			}
			$modal.show 	= function(){
				promise.then(show);
				return this;
			}
			$modal.hide 	= function(){
				promise.then(hide);
				return this;
			}
			return $modal;
		}
		return Factory;
	}
})
.provider('dialog',function(){
	this.$get				= function($window,$rootScope,$Compiler,$sce,$animate){
		var bodyElement 	= angular.element($window.document.body);
		function findElement(query, element) {
			return angular.element((element || document).querySelectorAll(query));
		}
		var Factory 		=  function(config){
			var $modal 		= {};
			var options 	= $modal.$options = angular.extend({},config);
			var dlgTpl 				= '<div class="dialog" tabindex="-1">'+
				'	<div class="dialog-header">'+
				'		<div class="dialog-title">{{title}}</div>'+
				'		<button type="button" class="close" ng-click="$hide()"><span>&times;</span></button>'+
				'	</div>'+
				'	<div class="dialog-body">'+
				'		<div class="dialog-body-content">'+options.content+
				'		</div>'+
				'	</div>'+
				'	<div class="dialog-buttons">'+
				'		<div class="btn-group">'+
				'			<button type="button" class="btn btn-primary" ng-click="$confirm()">确定</button>'+
				'			<button type="button" class="btn btn-default" ng-click="$hide()">关闭</button>'+
				'		</div>'+
				'	</div>'+
				'</div>';
			options.template= dlgTpl;
			options.container = 'body';
			var promise 	= $modal.$promise = $Compiler.compile(options);
			var scope 		= $modal.$scope = options.scope && options.scope.$new() || $rootScope.$new();
			angular.forEach(['title','content'],function(key){
				if(options[key]) scope[key] = $sce.trustAsHtml(options[key]);
			});
			var compileData, modalElement, modalScope;
			var backdrop  	= angular.element('<div class="dialog-overlay"></div>');
			promise.then(function(data){
				compileData = data;
				// $modal.init();
			});
			scope.$hide 	= function(){
				scope.$$postDigest(function(){
					$modal.hide();
				});
			};
			scope.$show 	= function(){
				scope.$$postDigest(function(){
					$modal.show();
				});
			};
			scope.$confirm 	= function(){
				scope.$$postDigest(function(){
					promise.then(hide);
					if(angular.isFunction(options.confirm))
						options.confirm(modalScope);
				});
			}
			var size 			= function(){
				var h			= angular.element(window).height();
				// var head 		= angular.element(modalElement).find('.dialog-header').outerHeight(),
				// 	foot 		= angular.element(modalElement).find('.dialog-buttons').outerHeight();
				modalElement.find('.dialog-body').css({
					'max-height' 	: Math.round(h * 0.8) - 41 - 54
				});
			}
			var resize		= function(){
				modalElement.css({
					'top'		: angular.element(window).height() / 2 - modalElement.height() / 2,
					'left'		: angular.element(window).width() / 2 - modalElement.width() / 2,
				});
			}
			var show 		= function(){
				modalScope 	= $modal.$scope.$new();
				modalElement = compileData.link(modalScope,function(clonedElement,scope){});
				$animate.enter(modalElement,bodyElement,null).then(function(){
					modalElement[0].focus();
					size();
					resize();
				});
				$animate.enter(backdrop,bodyElement,null);
			}
			var hide 		= function(){
				$animate.leave(modalElement)
				$animate.leave(backdrop);
			}
			// promise.then(size);
			$modal.cfg 		= function(){
				return this;
			}
			$modal.show 	= function(){
				promise.then(show);
				return this;
			}
			$modal.hide 	= function(){
				promise.then(hide);
				return this;
			}
			return $modal;
		}
		return Factory;
	}
})
.provider('dialog1',function(){
	var dlgTpl 				= '<div class="dialog" tabindex="-1">'+
				'	<div class="dialog-header">'+
				'		<div class="dialog-title">标题</div>'+
				'		<button type="button" class="close"><span>&times;</span></button>'+
				'	</div>'+
				'	<div class="dialog-body">'+
				'		<div class="dialog-body-content">'+
				'		</div>'+
				'	</div>'+
				'	<div class="dialog-buttons">'+
				'		<div class="btn-group">'+
				'			<button type="button" class="btn btn-primary" ng-click="$confirm()">确定</button>'+
				'			<button type="button" class="btn btn-default closed">关闭</button>'+
				'		</div>'+
				'	</div>'+
				'</div>';
	this.$get				= function($rootScope,$compile,$controller){
		var dlg				= angular.element(dlgTpl);
		dlg.appendTo('body');
		dlg.find('.close,.closed').click(function(){
			hide();
		});
		var dlgOverlay		= angular.element('<div class="dialog-overlay"></div>');
		dlgOverlay.appendTo('body').hide();
		dlgOverlay.click(function(){
			hide();
		});
		var hide 			= function(){
			dlg.hide();
			dlgOverlay.hide();
		}
		var size 			= function(){
			var w			= angular.element(window).width(),
				h			= angular.element(window).height();
			var head 		= dlg.find('.dialog-header').outerHeight(),
				foot 		= dlg.find('.dialog-buttons').outerHeight();
			dlg.css({
				'min-height'	: head + foot + 40,
				'max-width'		: Math.round(w * 0.8),
				'max-height'	: Math.round(h * 0.8)
			});
			body.css({
				'min-height'	: 40,
				'max-height' 	: Math.round(h * 0.8) - head - foot
			});
		}
		var resize 			= function(){
			dlg.css({
				'top'		: angular.element(window).height() / 2 - dlg.height() / 2,
				'left'		: angular.element(window).width() / 2 - dlg.width() / 2,
			});
		}
		var title 			= dlg.find('.dialog-title');
		var body 			= dlg.find('.dialog-body');
		size();
		dlg.hide();
		angular.element(window).resize(function(){
			size();
			resize();
		});
		var factory 		=  function(cfg){
			var options		= {
				title 		: '标题',
				scope		: $rootScope.$new(),
				data		: null,
				content 	: '',
				controller 	: null
			}
			angular.forEach(cfg,function(value, key){
				if(value)
					options[key]		= value;
			});
			title.text(options.title);
			body.find('.dialog-body-content').html(options.content);
			options.scope.$confirm	= function(){
				if(angular.isFunction(options.confirm))
					options.confirm(options.scope);
				hide();
			}
			var modalDomEl	= $compile(dlg)(options.scope);
			options.scope.$apply();
			this.cfg 		= function(name,value){
				options[name]	= value;
				return this;
			}
			this.show		= function(){
				if(angular.isFunction(options.controller)){
					var ctrlLocals		= {};
					options.scope.$data	= options.data;
					ctrlLocals.$scope 	= options.scope;
					$controller(options.controller,ctrlLocals);
				}
				dlg.show();
				dlgOverlay.show();
				resize();
				return this;
			}
			this.hide		= function(){
				hide();
				return this;
			}
		};
		var cache 			= null;
		return function(cfg){
			if(!cache){
				cache 		= new factory(cfg);
			}
			return cache;
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
					'保存'			: 'floppy-save',
					'向上'			: 'arrow-up',
					'向下'			: 'arrow-down',
					'编辑'			: 'edit',
					'修改'			: 'pencil',
					'删除'			: 'trash',
					'移除'			: 'remove',
					'选择'			: 'ok',
					'查看'			: 'list-alt',
					'通知'			: 'send',
					'通过'			: 'ok',
					'拒绝'			: 'remove',
					'短信通知'			: 'envelope',
					'重置'			: 'repeat'
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
			if(!$scope.value){
				$scope.$watch('$index',function(newValue){
					$scope.value= 1 << newValue - 1;
				});
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
					res 		= $scope.disable(item,name,$scope.value);
				else
					res 		= $scope.disable;
				return (res || this.disabled);
			}
			$scope.$click		= function(item){
				var self 		= this;
				self.update 		= function($update){
		    		self.disabled 	= !self.disabled;
		    		self.loader		= !self.loader;
		    		if(!isset($update) || $update)
		    			item[name]	^= $scope.value;
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
			// $scope.label 		= $scope.label || iAttrs['title'];
			// alert($scope.label)
			// iAttrs.$attr('title','');
			iElm.removeAttr('title');
			// iAttrs['title']	= "";
			// alert(iElm[0].outerHTML);
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
/**
 * 例子
 * <example title="标题">内容</example>
 */
.directive('example', function(){
	return {
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		template 	: '<div class="example">'
			+'	<div class="example-tag">{{title}}</div>'
			+'	<div ng-transclude></div>'
			+'</div>',
		scope		: {
			title 	: '@'
		},
		link		: function($scope,iElm){
			$scope.title 	= $scope.title || 'Example';
		}
	};
})
/**
 * 表格
<grid name="mm" cols="[{label:'名称',width:50,type:'number',align:'center'},{label:'名称',width:50,name:'name',type:'key',align:'center'},{label:'名称',name:'name',type:'input'},{label:'名称',name:'name',type:'select',items:[{key:'1',val:'m1'},{key:'2',val:'m2'}]},{label:'名称1',name:'name',type:'status'},{label:'ID',name:'id'},{label:'名称',name:'name'},{label:'操作',width:150,align:'center',name:'id',type:'action'}]" items="[{},{},{},{},{}]" status="$status" />
 */
.directive('grid',function($compile,$rootScope,$filter,dialog){
	return {
		restrict			:'E',
		replace				: true,
		scope				: {
			name 			: '@',
			param 			: '=?',
			cols 			: '=?',
			items 			: '=?',
			action 			: '=?',
			empty 			: '@',
			keyword			: '@',
			page 			: '=',
			goto 			: '=',
			disable			: '=',
			status			: '=',
			button			: '=',
			click			: '=',
			browse			: '=',
			edit 			: '=',
			remove 			: '=',
			up 				: '=',
			down 			: '=',
			sort 			: '=',
			notify			: '=',
			pass			: '=',
			reject			: '=',
			check			: '=',
			modeldif		: '=',
			checkv			: '=',
			master			: '=',
			sms 			: '=',
			reset			: '='
		},
		controller 			: function($scope){
			$scope.master	= $scope.master || {};
			$scope.checkv	= $scope.checkv || {};
			$scope.items	= $scope.items || [];
			$scope.empty	= $scope.empty || "没有数据";
			$scope.$click	= function(item){
	    		if(angular.isFunction($scope.click))
					$scope.click.call($scope,item);
			}
			$scope.$check   = function(item){
	    		if(angular.isFunction($scope.check))
					$scope.check.call($scope,item);
			}
			$scope.$modeldif= function(item){
	    		if(angular.isFunction($scope.modeldif))
					$scope.modeldif.call($scope,item);
			}
			$scope.$notify	= function(item){
	    		if(angular.isFunction($scope.notify))
					$scope.notify.call($scope,item);
			}
			$scope.$sms	= function(item){
	    		if(angular.isFunction($scope.sms))
					$scope.sms.call($scope,item);
			}
			$scope.$reset	= function(item){
	    		if(angular.isFunction($scope.reset))
					$scope.reset.call($scope,item);
			}
			$scope.$browse	= function(item){
	    		if(angular.isFunction($scope.browse))
					$scope.browse.call($scope,item);
			}
			$scope.$edit		= function(item){
		    	var idx 		= this.$parent.$parent.$index;
				if($scope.edit)
					$scope.edit(item,idx);
			}
			$scope.$pass	= function(item){
				if(angular.isFunction($scope.pass))
					$scope.pass.call($scope,item);
				var idx 	= this.$parent.$parent.$index;
				item.remove	= function(){
					$scope.items.splice(idx,1);
				}
		    	if($scope.remove)
		    		$scope.remove(item,idx);
		    	else
		        	item.remove();
		    }
			$scope.$reject	= function(item){
				if(angular.isFunction($scope.reject))
					$scope.reject.call($scope,item);
				var idx 	= this.$parent.$parent.$index;
				item.remove	= function(){
					$scope.items.splice(idx,1);
				}
		    	if($scope.remove)
		    		$scope.remove(item,idx);
		    	else
		        	item.remove();
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
		    $scope.$sortdisable	= false;
		    $scope.$up 		= function(item){
		    	var idx 	= this.$parent.$parent.$index;
		    	item.update	= function($update){
				    if(!isset($update) || $update&1){
			    		$scope.items.splice(idx,1);
				        $scope.items.splice(idx-1,0,item);
				        $scope.$sortdisable	= false;
				    }
				    if(isset($update) && $update){
				    	if($update&2){
				    		angular.forEach($scope.items,function(value,key){
					        	value['sort']	= key + 1;
					        });
				    	}
				    	if($update&4){
				    		item['sort']--;
							$scope.items[idx-1]['sort']++;
							return [{
								id 		: item.id,
								sort 	: item.sort,
							},{
								id 		: $scope.items[idx-1].id,
								sort 	: $scope.items[idx-1].sort,
							}];
				    	}
				    }
		    	}
		    	$scope.$sortdisable	= true;
		    	if(angular.isFunction($scope.up)){
		    		$scope.up(item,idx);
		    	}else{
			        item.update();
		    	}
		    }
		    $scope.$down	= function(item){
		    	var idx 	= this.$parent.$parent.$index;
		    	item.update	= function($update){
				    if(!isset($update) || $update&1){
			    		$scope.items.splice(idx,1);
			        	$scope.items.splice(idx+1,0,item);
			        	$scope.$sortdisable	= false;
				    }
				    if(isset($update) && $update){
				    	if($update&2){
				    		angular.forEach($scope.items,function(value,key){
					        	value['sort']	= key + 1;
					        });
				    	}
				    	if($update&4){
				    		item['sort']++;
							$scope.items[idx+1]['sort']--;
							return [{
								id 		: item.id,
								sort 	: item.sort,
							},{
								id 		: $scope.items[idx+1].id,
								sort 	: $scope.items[idx+1].sort,
							}];
				    	}
				    }
		    	}
		    	$scope.$sortdisable	= true;
		    	if(angular.isFunction($scope.down))
		    		$scope.down(item,idx);
		    	else{
			        item.update();
			    }
		    }
		    $scope.$sort        = function(item,field){
		    	item.update 	= function($sort){
		    		item['_loader_'+field]	= !item['_loader_'+field];
		    		item['_backup_'+field]	= item[field];
			        if($sort)
			        	$scope.items	= $filter("orderBy")($scope.items,field);
		    	}
		    	item['_loader_'+field]	= !item['_loader_'+field];
		    	if($scope.sort){
		    		$scope.sort(item,field);
		    	}else{
			        item.update(true);
		    	}
		    }
			$scope.$disable		= function(item,name,value){
				if(angular.isFunction($scope.disable)){
					return $scope.disable(item,name,value);
				}else if(name && value){
					return item[name] & value;
				}
			}
			$scope.$rowid		= function($index){
				var index 		= $index + 1;
				if($scope.page){
					return ($scope.page.page - 1) * $scope.page.size + index;
				}
				return index;
			}
			$scope.$char		= function($index,item,$key,$chars){
				var chars 		= $chars.split(',');
				item[$key] 		= chars[$index%chars.length];
				return item[$key];
			}
			$scope.$text		= function(item,$key,$text){
				var text 		= $text.split('|');
				return text[item[$key]];
			}
			$scope.$button		= function(item,$key,$title,$text){
				var text 		= $text.split('|');
				var tpl 		= '<radio value="{key}">{value}</radio>';
				var res 		= '';
				angular.forEach(text,function(value, key){
					res 		+= format(tpl,{key:key,value:value});
				});
				dialog({
					title 		: $title,
					content 	: '<radio-group name="'+$key+'" item="item">'+res+'</radio-group>',
					controller 	: function($scope){
						$scope.item 		= angular.copy(item);
					},
					confirm 	: function(scope){
						var val 	= scope.item[$key];
						item.update	= function($update){
							item.loader 		= false;
				    		if(!isset($update) || $update)
				    			item[$key] 	= val;
				    	}
				    	if(item[$key] == val){
				    		return false;
				    	}
						if(angular.isFunction($scope.button)){
							item.loader 		= true;
		    				$scope.button(item,$key,val);
						}else
							item[$key] 	= val;
					}
				}).show();
			}
		},
		link 				: function($scope, iElm, iAttrs){
			var dataIndex			= function(val,row,plugin){
				$result 			= "";
				row.plugin			= plugin;
				switch(row.type){
					case 'number':
						$result		+= '{{$rowid($index)}}';
						break;
					case 'check':
						var i =1;
						$result		+= '<input class="form-control" type="checkbox" ng-checked="master.value" ng-model="checkv.value[$index]" ng-click="$check(item)" ng-true-value="1" ng-false-value="0" />';
						break;
					case 'key':
						$result		+= 'item.'+val;
						break;
					case 'char':
						$result		+= '{{$char($index,item,"'+val+'","'+row.chars+'")}}';
						break;
					case 'keyword':
						$result		+= val?'<span ng-template="item.'+val+'|keywords:keyword"></span>':'';
						break;
					case 'tpl':
						$result		+= row.tpl;
						break;
					case 'name':
						$result		+= nameArrayFn(val,row);
						break;
					case 'badge':
						$result		+= format('{0}<span class="badge pull-right">{1}</span>','{{item.'+val+'}}','{{item.'+row.items+'.length}}');
						break;
					case 'currency':
						$result		+= '{{item.'+val+'|price:[\''+row.prefix+'\',\''+row.suffix+'\']}}'
						break;
					case 'time':
						$result		+= '{{item.'+val+'|timeFormat}}'
						break;
					case 'ip':
						$result		+= '{{item.'+val+'|ip}}';
						break;
					case 'image':
						$result		+= format('<img ng-if="item.{key}" src="{root}/{{item.{key}}}" class="img-thumbnail" />',{
							root 	: $ROOT,
							key 	: val
						});
						break;
					case 'thumbnail':
						$result		+= format('<thumbnail size="{0}" src="{{item.{1}}}"></thumbnail>',row.size||'',val);
						break;
					case 'upload':
						$result		+= format('<ext-form-item-upload name="{name}" item="item" empty="请上传{label}"{btn|attr="btn"}{url|attr="url"}{size|attr="size"} />',row);
						break;
					case 'hidden':
						$result		+= format('<input type="hidden"{name|this.nameFn} ng-value="item.{name}" />',row)+'{{item.'+row.name+'}}';
						break;
					case 'read':
						$result		+= format('<input class="form-control" type="text"{name|this.nameFn} ng-model="item.{name}" readonly />',row);
						break;
					case 'input':
						angular.forEach(row.hide, function(value, key){
							$result += format('<input type="hidden"{name|this.nameFn} ng-value="item.{name}" />',{
								name 	: value,
								plugin 	: plugin
							});
						});
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
					case 'browse':
						$result		+= browseFn(val,row);
						break;
					case 'sort':
						$result		+= sortFn(val,row);
						break;
					case 'status':
						$result		+= statusFn(val,row);
						break;
					case 'button':
						$result		+= buttonFn(val,row);
						break;
					case 'check':
						$result		+= checkFn(val,row);
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
			var nameArrayFn			= function(val,row){
				var result 			= [];
				if(!angular.isArray(val)){
					val 			= [val];
				}
				angular.forEach(val, function(value, key){
					result.push('{{item.'+value+'}}');
				});
				return result.join(row.separator||'<br/>');
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
			var browseFn			= function(val,row){
				row.btn 	= row.btn || '浏览';
				var tpl 	= '<div class="input-group">'
					+'	<input class="form-control" type="text"{name|this.nameFn} ng-model="item.{name}" placeholder="请输入{label}" />'
					+'	<span class="input-group-btn">'
					+'		<button type="button" class="btn btn-default" ng-click="$browse(item)">{btn}</button>'
					+'	</span>'
					+'</div>';
				return format(tpl,row);
			}
			var sortFn				= function(val,row){
				var tplGroup 		= '<div class="input-group">{0}{1}</div>';
				var tplInput 		= '<input class="form-control" type="text"{name|this.nameFn} ng-model="item.{name}" ng-init="item._backup_{name} = item.{name}" placeholder="请输入{label}" style="text-align:center;padding:0 5px" />';
				var tplSave			= '<span class="input-group-btn">'
					+'<div class="btn btn-default" ng-disabled="item._backup_{name} == item.{name}" ng-click="$sort(item,\'{name}\')" style="width:45px">'
					+'<span ng-loading="item._loader_{name}"><span class="glyphicon glyphicon-floppy-save"</span></span>'
					+'</div>'
					+'</span>';
				return format(tplGroup,format(tplInput,row),format(tplSave,row));
			}
			var statusFn			= function(name,row){
				var aryAction		= row.items || ['启用|禁用','状态'];
				var tplBtnGroup		= '<btn-group item="item" click="status">{items|this.filter|map=tplBtn}</btn-group>';
				var tplBtn 			= '<btn-status name="{name}"{value|attr="value"}{disable|attr="disable"}>{label}</btn-status>';
				var tplBtns 		= format(tplBtnGroup,{
					items			: aryAction,
					tplBtn			: tplBtn,
					plugin			: {
						filter 		: function(val){
							var res 		= [];
							angular.forEach(val,function(value,i){
								var label 	= '';
								var val 	= '';
								if(angular.isString(value))
									label 	= value
								else if(angular.isArray(value)){
									val 	= value[0];
									label 	= value[1];
								}
								res.push({
									disable	: row.disable & (1 << i)?'$disable':'',
									name 	: name,
									value 	: val,
									label 	: label
								});
							});
							return res;
						}
					}
				});
				return tplBtns;
			}
			var buttonFn			= function(val,row){
				return '<button type="button" class="btn btn-default" style="width:100%" ng-click="$button(item,\''+val+'\',\''+row.label+'\',\''+row.text+'\')" ng-loading="item.loader">{{$text(item,"'+val+'","'+row.text+'")}}</button>';
			}
			var actionFn			= function(val,row){
				// $scope.action		= $scope.action || [{key:'edit',col:3},{key:'delete',col:3}];
				var aryAction		= row.items || $scope.action || parse_name('up&down&edit&delete');
				var actions 		= {
					'up'			: {text:'向上',action:'$up',disabled:'$sortdisable || $first'},
					'down'			: {text:'向下',action:'$down',disabled:'$sortdisable || $last'},
					'edit'			: {text:'编辑',action:'$edit'},
					'delete'		: {text:'删除',action:'$remove'},
					'view'			: {text:'查看',action:'$click'},
					'select'		: {text:'选择',action:'$click'},
					'notify'		: {text:'通知',action:'$notify'},
					'sms'			: {text:'短信通知',action:'$sms'},
					'pass'			: {text:'通过',action:'$pass'},
					'reject'		: {text:'拒绝',action:'$reject'},
					'reset'			: {text:'重置',action:'$reset'}
				}
				var tplBtnGroup		= '<btn-group item="item">{btns}</btn-group>';
				var tplBtn 		 	= '<btn-text click="{action}"{disabled|attr="disable"}{col|attr="col"}>{text}</btn-text>';
				var tplBtnIco	 	= '<btn-icon click="{action}"{disabled|attr="disable"}{col|attr="col"}>{text}</btn-icon>';
				var result 			= "";
				var i 				= 0;
				angular.forEach(aryAction,function(value,key){
					var res 		= actions[key];
					if(/^\d+$/.test(key)){
						if(angular.isString(value)){
							res 	= actions[value];
						}else if(value.key){
							res 	= actions[value.key];
							delete value['key'];
							angular.forEach(value, function(value, key){
								res[key]	= value;
							});
						}
					}
					if(row.disable & (1 << i)){
						var disable = '$disable';
					}
					if(res){
						if(res['disabled'])
							disable 	= res['disabled']+' || '+disable
						res['disabled'] = disable;
						result		+= format(tplBtnIco,res);
					}else if(angular.isString(value)){
						result		+= format(tplBtn,{text:value,action:'$click',disabled:disable});
					}else{
						value['disabled']	= disable;
						value['action']		= '$click';
						result		+= format(tplBtn,value);
					}
					i++;
				});
				var tplBtns 		= format(tplBtnGroup,{
					btns 			: result
				});
				return tplBtns;
				// var iconAction		= {
				// 	'向上'			: {icon:'arrow-up',action:'$up',disabled:'$first'},
				// 	'向下'			: {icon:'arrow-down',action:'$down',disabled:'$last'},
				// 	'编辑'			: {icon:'edit',action:'$edit'},
				// 	'修改'			: {icon:'pencil',action:'$modify'},
				// 	'删除'			: {icon:'trash',action:'$remove'},
				// 	'选择'			: {icon:'ok',action:'$select'},
				// 	'放入回收站'	: {icon:'remove',action:'$recycle'},
				// 	'恢复'			: {icon:'ok',action:'$restore'},
				// };
			}
			 // class="table table-bordered"
			var tpl			= '<table{style|cls="table-bordered"}>'
				+'	<thead>'
				+'		<tr>{th|each=cols}</tr>'
				+'	</thead>'
				+'	<tbody>'
				+'		<tr ng-repeat="item in items"{itemcontroller|attr="ng-controller"}>{td|each=cols,plugin}</tr>'
				+'		{emptyTpl|format=empty}'
				+'		{pageTpl|format=empty|IFF=page}'
				+'	</tbody>'
				+'</table>';
			tpl 			= format(tpl,{
				itemcontroller : iAttrs['itemcontroller']||'',
				style		: iAttrs['class']||'table',
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
			'page'	: '=?',
			'size'	: '=?',
			'count'	: '=?',
			'goto'	: '=?'
		},
		template	: '<div class="page">'
			+'<div class="page-left">'
			+'从 {{firstRow}} 到 {{lastRow}} /共 {{count}} 条数据'
			// +' 第 {{page}} 页 / 共 {{pagecount}} 页 / 每页 {{pagesize}} 条数据'
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
			$scope.$watch('count', function(newValue, oldValue, scope){
				if(newValue != oldValue){
					// alert('count='+newValue+' '+oldValue)
					$scope.pagecount	= Math.ceil($scope.count / $scope.pagesize);
					$scope.page  		= 1;
					setPages($scope.page);
				}
			});
			$scope.$watch('size', function(newValue, oldValue){
				if(newValue>0){
					// alert('size='+newValue+' '+oldValue)
					$scope.pagesize		= parseInt(newValue);
					$scope.pagecount	= Math.ceil($scope.count / $scope.pagesize);
					if($scope.page>$scope.pagecount)
						$scope.$goto($scope.pagecount);
					else if($scope.page>1)
						$scope.$goto($scope.page);
					else if(newValue != oldValue)
						$scope.$goto($scope.page);
					setPages($scope.page);
				}
			});
			$scope.$watch('page', function(newValue, oldValue, scope){
				if(newValue != oldValue){
					// alert('page='+newValue+' '+oldValue)
					setPages(newValue);
				}
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
					$scope.goto(number,$scope.pagesize);
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

.directive('extForm',function($resource,$location,$timeout,$parse){
	return {
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		scope 		: {
			action 	: '@',
			cols 	: '@',
			pk 		: '@',
			val 	: '@',
			item	: '=?',
			pkpost	: '=?',
			route	: '=?',
			jump	: '=?',
			debug	: '=?'
		},
		template 	: '<div class="ajaxform">'
			+'<div class="alert" ng-show="alert" ng-class="alert.cls">'
	        +'<span class="glyphicon glyphicon-exclamation-sign"></span>'
	        +'<span class="sr-only">Error:</span>'
	        +'<span class="text">{{alert.msg}}</span>'
	    	+'</div>'
			+'<form class="form-horizontal" name="extForm" method="POST"'
			+' ng-submit="$submit(extForm.$valid)" novalidate'
			// +' action="{{action}}"'
			+' ng-transclude>'
			+'</form>'
			+'<div ng-if="debug">{{item}}{{postData}}{{result}}</div>'
			+'</div>',
		controller 	: function($scope){
			var items 		= $scope.items 	= [];
			this.add 		= function(item){
          		items.push(item);
			}
		},
		link  		: function($scope, iElm, iAttrs){
			$scope.$watch('cols',function(newValue,oldValue){
				// alert('cols='+newValue+' '+oldValue)
				angular.forEach($scope.items,function(value, key){
					value.$cols 	= newValue;
				});
			});
			$scope.item		= $scope.item || {};
			$scope.pk 		= $scope.pk || '';
			$scope.pkpost	= $scope.pkpost || false;
			$scope.route	= $scope.route || false;
			$scope.debug	= $scope.debug || false;
			$scope.alert 	= false;
			if($scope.pk){
				// $scope.action 		= $scope.action.replace(/(.+\/)(.+)(\..+)$/,'$1$2/'+$scope.pk+'/:'+$scope.pk+'$3');
				var pkVal 			= $scope.val;
			}
			if($scope.debug) $scope.result	= $scope.action;
			var model 		= $resource($scope.action);
			if(pkVal){
				var postData 		= {};
				postData[$scope.pk]	= pkVal;
				if($scope.debug){
					$scope.postData = postData;
				}
				$.overlay();
				model.get(postData,function(data){
					$scope.item	= data;
					// $parse('formData').assign($scope,data);
					$.unoverlay();
				});
			}
			$scope.$watch('item',function(newValue){
				angular.forEach($scope.items,function(value, key){
					value.item 		= newValue;
				});
				if($scope.pkpost) $scope.formData[$scope.pk]	= pkVal;
			});
			$scope.$submit			= function($valid){
				var postData 		= {};
				postData[$scope.pk]	= pkVal;
				if($scope.debug){
					$scope.postData = postData;
				}
				$.overlay();
				model.save(postData,$scope.item,function(data){
					if($scope.debug)
						$scope.result	= data;
					if(data && data.status == true){
						$scope.$broadcast("save",true);
						$scope.alert 	= {
							cls 		: 'alert-info',
							msg 		: data.info
						};
						if($scope.route){
							$timeout(function(){
								$location.path("/"+($scope.jump||""));
							},1000);
						}
					}else if(data.status == false){
						$scope.alert	= {
							cls 		: 'alert-danger',
							msg 		: data.info
						};
					}else{
						alert(data);
						$scope.alert	= {
							cls 		: 'alert-danger',
							msg 		: data
						};
					}
					$.unoverlay();
				});
			}
		}
	};
})
.directive('extFormItem',function(){
	return {
		require 	: '^extForm',
		restrict 	: 'E',
		replace 	: true,
		transclude 	: true,
		scope 		: {
			label 	: '@',
			cols 	: '@',
			show 	: '=?'
		},
		template 	: '<div class="form-group" ng-show="show">'
		    +'    <label class="control-label col-md-{{$_cols(0)}}">{{label}}</label>'
		    +'    <div class="col-md-{{$_cols(1)}}" ng-transclude>'
		    +'    </div>'
		    +'</div>',
		controller 	: function($scope){
			var items 		= $scope.items 	= [];
			$scope.show 	= $scope.show || true;
			this.add 		= function(item){
          		items.push(item);
			}
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
	};
})
.directive('extFormItemStatic',function($compile){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@'
		},
		link  		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			var tpl 	= '<p class="form-control-static">{model}</p>';
			tpl 		= format(tpl,{
				model 	: "{{item."+$scope.name+"}}",
				value 	: "{{value}}"
			});
			// alert(tpl);
			var html	= $compile(tpl)($scope);
			iElm.replaceWith(html);
		}
	};
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
			var tpl 	= '<input class="form-control"{type|attr="type"}{name|attr="name"}{model|attr="ng-model"}{list|attr="ng-list"}{value|attr="value"}{empty|attr="placeholder"} />';
			tpl 		= format(tpl,{
				type 	: iAttrs.type || 'text',
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
.directive('extFormItemUnit',function($compile){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			unit 	: '@',
			empty 	: '@'
		},
		link  		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			var tpl 	= '<div class="input-group">'+
				'<input class="form-control" type="text" {name|attr="name"}{model|attr="ng-model"}{empty|attr="placeholder"} />'+
				'<span class="input-group-addon">{unit}</span>'+
				'</div>';
			tpl 		= format(tpl,{
				name 	: $scope.name,
				model 	: 'item.'+$scope.name,
				unit 	: $scope.unit,
				empty 	: $scope.empty
			});
			// alert(tpl)
			var html	= $compile(tpl)($scope);
			iElm.replaceWith(html);
		}
	};
})
.directive('extFormItemDate',function($compile){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			min 	: '@',
			max 	: '@',
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
			var tpl 	= '<input class="form-control"{type|attr="type"}{name|attr="name"}{model|attr="ng-model"}{list|attr="ng-list"}{value|attr="value"}{empty|attr="placeholder"}{min|attr="data-min-date"}{max|attr="max-date"} bs-datepicker date-format="yyyy-MM-dd" date-type="string" />';
			tpl 		= format(tpl,{
				type 	: iAttrs.type || 'text',
				name 	: $scope.name,
				model 	: "item."+model,
				min 	: '{{min}}',
				max 	: "{{max}}",
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
.directive('extFormItemDay',function($compile){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			min 	: '@',
			max 	: '@',
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
			var tpl 	= '<input class="form-control"{type|attr="type"}{name|attr="name"}{model|attr="ng-model"}{list|attr="ng-list"}{value|attr="value"}{empty|attr="placeholder"}{min|attr="data-min-date"}{max|attr="max-date"} />';
			tpl 		= format(tpl,{
				type 	: iAttrs.type || 'date',
				name 	: $scope.name,
				model 	: "item."+model,
				min 	: '{{min}}',
				max 	: "{{max}}",
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
.directive('extFormItemDates',function($compile){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			min 	: '@',
			max 	: '@',
			empty 	: '@'
		},
		link  		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			var model 	= $scope.name.split(',');
			var empty 	= $scope.empty.split(',');
			var tpl 	= '<div class="input-group">'+
				'<span class="input-group-addon">开始日期</span>'+
				'<input class="form-control" type="text" {name1|attr="name"}{model1|attr="ng-model"}{empty1|attr="placeholder"}{max|attr="max-date"} bs-datepicker date-format="yyyy-MM-dd" date-type="string"/>'+
				'<span class="input-group-addon">结束日期</span>'+
				'<input class="form-control" type="text" {name2|attr="name"}{model2|attr="ng-model"}{empty2|attr="placeholder"}{min|attr="min-date"} bs-datepicker date-format="yyyy-MM-dd" date-type="string" placement="bottom-right"/>'+
				'</div>';
			tpl 		= format(tpl,{
				name1 	: model[0],
				name2 	: model[1],
				model1 	: 'item.'+model[0],
				model2 	: 'item.'+model[1],
				empty1 	: empty[0],
				empty2 	: empty[1],
				min 	: '{{item.'+model[0]+'}}',
				max 	: '{{item.'+model[1]+'}}'
			});
			// alert(tpl)
			var html	= $compile(tpl)($scope);
			iElm.replaceWith(html);
		}
	};
})
.directive('extFormItemGeocoding',function($compile,$http,$parse){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			empty 	: '@',
			fields 	: '@',
			debug 	: '=?'
		},
		controller 	: function($scope){
			$scope.$Geocoding 	= function(){
				var val 		= $scope.item[$scope.name];
				if(val){
					var data 		= {
						'ak'		: $BAIDU_MAP_KEY,
						'callback' 	: 'JSON_CALLBACK',
						'output'	: 'json',
						'address'	: val
					};
					$http.jsonp('http://api.map.baidu.com/geocoder/v2/?'+parseQuery(data)).success(function(data, status, header, config){
			            if($scope.fields&&data.status==0&&data.result&&data.result.location){
			            	var fields 	= $scope.fields.split(',');
							$parse('item.'+fields[0]).assign($scope,data.result.location.lng);
							$parse('item.'+fields[1]).assign($scope,data.result.location.lat);
			            }else{
			            	alert(data.msg);
			            }
			            if($scope.debug)
			            	$scope.result = data;
			        }).error(function(data){
			            alert("error");
			        });
			    }else{
			    	alert("请输入地址");
			    }
			}
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
			var tpl 	= '<div class="input-group">'+
				'<input class="form-control"{type|attr="type"}{name|attr="name"}{model|attr="ng-model"}{value|attr="value"}{empty|attr="placeholder"} />'+
				'<span class="input-group-btn">'+
				'<button class="btn btn-default" type="button" ng-click="$Geocoding()">获取经纬度</button>'+
				'</div>'+
				'{debug}'+
				'</div>';
			tpl 		= format(tpl,{
				type 	: iAttrs.type || 'text',
				name 	: $scope.name,
				model 	: "item."+model,
				value 	: "{{value}}",
				empty 	: $scope.empty,
				debug 	: '<div ng-if="debug">{{result}}</div>'
			});
			// alert(tpl)
			var html	= $compile(tpl)($scope);
			iElm.replaceWith(html);
		}
	};
})
.provider('BMap',function(){
	var dlgTpl 			= '<div class="modal BMapDlg" tabindex="-1">'+
				'	<div class="modal-dialog">'+
				'		<div class="modal-content">'+
				'			<div class="modal-header">'+
				'				<button type="button" class="close">'+
				'					<span aria-hidden="true">&times;</span>'+
				'				</button>'+
				'				<h4 class="modal-title">百度地图</h4>'+
				'			</div>'+
				'			<div class="modal-body">'+
				'				<div id="BMap" style="display:block;height:500px;"></div>'+
				'			</div>'+
				'			<div class="modal-footer">'+
				'				<div class="col-md-9">'+
				'					<div class="input-group">'+
				'						<span class="input-group-addon">地址</span>'+
				'						<input class="form-control" id="addr" type="text" readonly="true" />'+
				'						<span class="input-group-addon">经度</span>'+
				'						<input class="form-control" id="lng" type="text" readonly="true" />'+
				'						<span class="input-group-addon">纬度</span>'+
				'						<input class="form-control" id="lat" type="text" readonly="true" />'+
				'					</div>'+
				'				</div>'+
				'				<div class="col-md-3">'+
				'					<div class="btn-group">'+
				'						<button type="button" class="btn btn-primary">确定</button>'+
				'						<button type="button" class="btn btn-default closed">关闭</button>'+
				'					</div>'+
				'				</div>'+
				'			</div>'+
				'		</div>'+
				'	</div>'+
				'</div>';
	this.$get	= function(){
		var mapOptions 		= {
			point 			: {
				lng : '121.487899',
				lat : '31.249162'
			},
			center 			: '',
			zoom 			: 17
		};
		$BMapDlg			= $(dlgTpl);
		$BMapDlgOverlay		= $('<div class="modal-backdrop"></div>');
		$BMapDlg.appendTo('body').hide();
		$BMapDlgOverlay.appendTo('body').hide();
		$BMapDlg.find("#BMap").height($(window).height() - 220);
		var map				= null;
		var geoc 			= null;
		var marker 			= null;
		var addr 			= $BMapDlg.find('#addr');//.width(220);
		var lng 			= $BMapDlg.find('#lng');
		var lat 			= $BMapDlg.find('#lat');
		$BMapDlg.find('.close,.closed').click(function(){
			that.hide();
		});
		$BMapDlg.find('.btn-primary').click(function(){
			var p 	= {
				lng : lng.val(),
				lat : lat.val()
			}
			// alert("marker的位置是" + p.lng + "," + p.lat);
			if(angular.isFunction(mapOptions.callback))
				mapOptions.callback(p,addr.val());
			that.hide();
		});
		var setValue 		= function(point){
			lng.val(point.lng);
			lat.val(point.lat);
			getLocation(point);
		}
		var setAddress 		= function(address){
			addr.val(address);
		}
		var addMarker 		= function(point){
			if(!marker){
				marker 		= new BMap.Marker(point);
				map.addOverlay(marker);
				marker.enableDragging();
				marker.setAnimation(BMAP_ANIMATION_BOUNCE);
				marker.addEventListener("dragstart",function(e){
					marker.setAnimation();
				});
				marker.addEventListener("dragging",function(e){
					setValue(e.point);
				});
				marker.addEventListener("dragend",function(e){
					// marker.setAnimation(BMAP_ANIMATION_DROP);
					marker.setAnimation(BMAP_ANIMATION_BOUNCE);
				});
			}else{
				marker.setPosition(point);
			}
			setValue(point);
			// map.setCenter(point);
		}
		var getLocation 	= function(point){
			geoc.getLocation(point,function(rs){
				setAddress(rs.address);
			});
		}
		var getPoint 		= function(address,callback){
			geoc.getPoint(address,function(point){
				addMarker(point);
				if(angular.isFunction(callback))
					callback(point);
			});
		}
		var that 			= {
			cfg 	: function(options){
				angular.forEach(options,function(value, key){
					if(value)
						mapOptions[key]		= value;
				});
			},
			show 	: function(){
				$BMapDlg.show(function(){
					if(!map){
						map			= new BMap.Map($BMapDlg.find("#BMap")[0]);
						map.addControl(new BMap.NavigationControl());
						map.addControl(new BMap.ScaleControl({anchor:BMAP_ANCHOR_TOP_LEFT}));
						geoc 		= new BMap.Geocoder();
						var point 	= new BMap.Point(mapOptions.point.lng,mapOptions.point.lat);
						map.centerAndZoom(point,mapOptions.zoom);
						if(!mapOptions.center){
							(new BMap.LocalCity()).get(function(result){
								addMarker(result.center);
								map.setCenter(result.center);
							});
						}
						// getPoint(mapOptions.center,function(point){
						// 	map.setCenter(point);
						// });
						// addMarker(point);
						map.addEventListener("click",function(e){
							addMarker(e.point);
						});
					}else{
						var point 	= new BMap.Point(lng.val(),lat.val());
						map.setCenter(point);
					}
					if(mapOptions.center){
						map.setZoom(mapOptions.zoom);
						// map.setCurrentCity(mapOptions.center);
						getPoint(mapOptions.center,function(point){
							map.setCenter(point);
						});
					}
				});
				$BMapDlgOverlay.show(function(){
					$BMapDlgOverlay.removeClass('fade').addClass('in');
				});
			},
			hide 	: function(){
				$BMapDlg.hide();
				$BMapDlgOverlay.hide(function(){
					$BMapDlgOverlay.removeClass('in').addClass('fade');
				});
			}
		};
		return that;
	}
})
.directive('extFormItemLnglat',function($compile,$parse,BMap){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			empty 	: '@',
			center 	: '@',
			debug 	: '=?'
		},
		controller 	: function($scope){
			$scope.map 	= "";
			var fields 			= $scope.name.split(',');
			$scope.$watch('item', function(newValue, oldValue){
				if(newValue){
					var addr 	= newValue[$scope.center];
					BMap.cfg({
						center 			: addr,
						zoom 			: 17,
						callback 		: function(p,addr){
							$parse('item.'+fields[0]).assign($scope,p.lng);
							$parse('item.'+fields[1]).assign($scope,p.lat);
							$scope.item[$scope.center] = addr;
							$scope.$apply();
						}
					});
				}
			});
			$scope.$BMap 		= function(){
				BMap.show();
			}
		},
		link  		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			var model 	= $scope.name.split(',');
			var empty 	= $scope.empty.split(',');
			var tpl 	= '<div class="input-group">'+
				'<span class="input-group-addon">经度</span>'+
				'<input class="form-control" type="text" {name1|attr="name"}{model1|attr="ng-model"}{empty1|attr="placeholder"} />'+
				'<span class="input-group-addon">纬度</span>'+
				'<input class="form-control" type="text" {name2|attr="name"}{model2|attr="ng-model"}{empty2|attr="placeholder"} />'+
				'<span class="input-group-btn">'+
				'<button class="btn btn-default" type="button" ng-click="$BMap()">获取经纬度</button>'+
				'</span>'+
				'{debug}'+
				'</div>';
			tpl 		= format(tpl,{
				addr 	: '{{map}}',
				name1 	: model[0],
				name2 	: model[1],
				model1 	: "item."+model[0],
				model2 	: "item."+model[1],
				empty1 	: empty[0],
				empty2 	: empty[1],
				debug 	: '<div ng-if="debug">{{result}}</div>'
			});
			// alert(tpl)
			var html	= $compile(tpl)($scope);
			iElm.replaceWith(html);
		}
	};
})
.directive('bMap',function($compile){
	return {
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			'options'	: '=?'
		},
		template 	: '<div>ssss<div id="BMap" style="display:block;height:500px;"></div>{{res}}</div>',
		compile		: function(iElm){
			var map 	= null;
			// var html 	= '<div>ssss<div id="BMap" style="display:block;height:500px;"></div></div>';
			// iElm.replaceWith(html);
			return function($scope, iElm, iAttrs){
				alert(map)
				if(!map)
					map		= new BMap.Map(iElm.find("#BMap")[0]);
				map.centerAndZoom("上海",17);
				map.enableScrollWheelZoom(); 
				// alert(map.getSize())
				$scope.res = map.getSize();
			}
		},
		link  		: function($scope, iElm, iAttrs){alert(2)
			$scope.options 	= $scope.options || {};
			var tpl 	= '<div>ssss<div id="BMap" style="display:block;height:500px;"></div></div>';
			var html	= $compile(tpl)($scope);
			iElm.replaceWith(html);
			alert(map)
			var map = new BMap.Map(html.find("#BMap")[0]);
			map.centerAndZoom($scope.options.center||"上海",$scope.options.zoom||17);
			if($scope.options.click){
				map.addEventListener("click",function(e){
					if(angular.isFunction($scope.options.click))
						$scope.options.click(e.point);
					alert(e.point.lng + "," + e.point.lat);
				});
			}
			$scope.$watch('options.show',function(newValue, oldValue){
				if(newValue){
					alert(newValue)
					map.centerAndZoom($scope.options.center||"上海",$scope.options.zoom||17);
				}
			});
		}
	};
})
.directive('extFormItemUpload',function($compile,Upload,$parse,$http,$timeout){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			empty 	: '@',
			item 	: '=?'
		},
		controller 	: function($scope){
			$scope.$percentage	= 0;
			$scope.$flag		= false;
			$scope.show			= false;
			var saved 	= false;
			var changed = false;
			$scope.$on("save",function(event,flag){
				saved 	= true;
			});
			$scope.$on("$destroy",function(){
				if(saved === false && changed){
					$http.post($CONTROLLER+$scope.url,{file:$scope.$eval('item.'+$scope.name)});
				}
			});
			$scope.$watch('$file',function(newValue,oldValue){
				if(newValue != null)
		    		$scope.upload(newValue);
		    });
		    $scope.upload 		= function(file){
		     	$scope.$flag 				= true;
		     	$scope.$percentage 			= 0;
		     	var data 		= {
		     		'upload'	: true,
		     		'file'		: $scope.$eval('item.'+$scope.name),
		     		'w'			: $scope.size[0],
		     		'h'			: $scope.size[1]
		     	};
		     	if($scope.filename){
		     		data['filename']		= $scope.filename;
		     	}
		     	Upload.upload({
					url		: $CONTROLLER+$scope.url,
					method 	: 'POST',
					fields  : data,
					file 	: file
				}).progress(function(evt){
					var progressPercentage 	= parseInt(100.0 * evt.loaded / evt.total);
					$scope.$percentage 		= progressPercentage;
				}).success(function (data, status, headers, config){
					if($scope.debug)
						$scope.debug = data;
					if(data.status){
						changed 	= true;
						$parse("item."+$scope.name).assign($scope,data.info);
					}else{
						alert(data.info);
					}
					$timeout(function(){
						$scope.$flag		= false;
					},1000);
				}).error(function(data, status, headers, config){
					alert('Err:'+data);
				});
			}
		},
		link  		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
        	$scope.url	= iAttrs.url || '/upload';
        	$scope.size	= (iAttrs.size||"0x0").split('x');
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
			var tpl 	= '<div>'+
			(isset(iAttrs.size)?'<thumbnail size="{size}" rate="{rate}" src="{{{model}}}"></thumbnail>':'')+
			'<div class="progress" ng-if="$flag&&$percentage>0">'+
            '    <div class="progress-bar" style="width:{{$percentage}}%;">{{$percentage}}%</div>'+
            '</div>'+
			'<div class="input-group">'+
	        '    <input class="form-control" type="text"{name|attr="name"}{model|attr="ng-model"}{value|attr="value"}{empty|attr="placeholder"} />'+
	        '    <span class="input-group-btn">'+
	        '        <button type="button" class="btn btn-default" ngf-select ng-model="$file" ng-disabled="$flag">{btn}</button>'+
	        '    </span>'+
	        '</div>'+
	        '</div>';
			tpl 		= format(tpl,{
				name 	: $scope.name,
				model 	: "item."+model,
				value 	: "{{value}}",
				empty 	: $scope.empty,
				btn 	: iAttrs.btn || '上传',
				size 	: iAttrs.size || '',
				rate 	: iAttrs.rate || 1
			});
			var html	= $compile(tpl)($scope);
			iElm.replaceWith(html);
		}
	};
})
.directive('extFormItemTextarea',function($compile){
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
			var tpl 	= '<textarea class="form-control"{name|attr="name"}{model|attr="ng-model"}{list|attr="ng-list"}{empty|attr="placeholder"}>{value}</textarea>';
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
.directive('extFormItemUeditor',function($compile){
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
			var id 		= "_editor" + (Date.now())+'_'+Math.random();
			var tpl 	= '<textarea {id|attr="id"}{name|attr="name"}{model|attr="ng-model"}></textarea>';
			tpl 		= format(tpl,{
				id 		: id,
				name 	: $scope.name,
				model 	: "item."+$scope.name,
				value 	: "{{value}}",
				empty 	: $scope.empty
			});
			// alert(tpl)
			var html	= $compile(tpl)($scope);
			iElm.replaceWith(html);
			if(typeof UE !== 'undefined'){
				var _updateByRender 	= true;
				var editor = UE.getEditor(id,{
					// imageUrl			: ,
					imagePath			: 'Uploads/images/',
					initialFrameWidth 	: 600,
					initialFrameHeight 	: 300,
        			autoFloatEnabled 	: false
				});
				editor.ready(function(){
					editor.addListener('contentChange',function(){
						if(editor.undoManger && editor.undoManger.list.length>0){
							$scope.item[$scope.name] = editor.getContent();
							$scope.$apply();
						}
					});
					$scope.$watch('item.'+$scope.name,function(newValue,oldValue){
						if(_updateByRender){
							editor.setContent(newValue);
							_updateByRender 	= false;
						}
					});
					$scope.$on("$destroy",function(){
						editor.destroy();
            			editor = null;
					});
				});
			}
		}
	};
})
.directive('extFormItemDropdown',function($compile){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			empty 	: '@',
			level	: '=?',
			items	: '=?'
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			$scope.items	= $scope.items || [];
			$scope.level	= $scope.level || 1;
			$scope.$watch('item.'+$scope.name,function(newValue){
				if(newValue == "")
					delete $scope.item[$scope.name];
			});
		 //    var tpl			= '<select class="form-control" {model|attr="ng-model"}>'
		 //    	+'<option value="{emptyVal}">{empty}</option>'
		 //    	+'<option ng-repeat="n in items" value="{{n.id}}">{{n.name}}</option>'
		 //    	+'</select>';
		 //    // var tpl			= '<select class="form-control" {model|attr="ng-model"} '
		 //    // 	+'ng-options="node.key as node.val for node in items">'
		 //    // 	+'<option value="{emptyVal}">{empty}</option>'
		 //    // 	+'</select>';
			// var tmp			= format(tpl,{
			// 	model 		: "item."+$scope.name,
			// 	emptyVal 	: typeof(iAttrs.emptyVal) !== "undefined"?iAttrs.emptyVal:'',
			// 	empty 		: $scope.empty
			// });
			// var html		= $compile(tmp)($scope);
			// iElm.replaceWith(html);
			var key 	= iAttrs.key || 'id';
			var val 	= iAttrs.val || 'name';
			$scope.$watch('items',function(newValue){
				if(newValue.length>0){
				var tpl			= '<select class="form-control" {model|attr="ng-model"}>'
			    	+'<option value="{emptyVal}">{empty}</option>'
			    	+'{dropdown}'
			    	+'</select>';
			    var result 		= (function(data,level){
			    	var self 		= arguments.callee;
			    	var separator 	= str_repeat('│&nbsp;&nbsp;',level-1);
			    	var dropdown 	= '<option value="{'+key+'}">{separator}{last|IIF="└─","├─"} {'+val+'}</option>';
					var result 		= "";
				    angular.forEach(data,function(value, key){
				    	value['last']		= data.length-1 == key;
				    	value['separator']	= separator;
				    	result 		+= format(dropdown,value);
				    	if(level < $scope.level && value['childs'].length>0){
				    		result	+= self(value['childs'],level+1);
				    	}
				    });
				    return result;
				})(newValue,1);
			    var tmp			= format(tpl,{
					model 		: "item."+$scope.name,
					dropdown 	: result,
					emptyVal 	: typeof(iAttrs.emptyVal) !== "undefined"?iAttrs.emptyVal:'',
					empty 		: $scope.empty
				});
				var html		= $compile(tmp)($scope);
				iElm.replaceWith(html);
				}
			});
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
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			empty 	: '@',
			key 	: '@',
			items	: '=?'
		},
		template 	: '<div>'
			+'<button class="btn btn-default" type="button" ng-click="$add()" ng-if="items.length<=0">'
			+'	<span class="glyphicon glyphicon-plus"></span>'
			+'</button>'
			+'<div class="col-sm-12" ng-repeat="n in items" style="margin:0;padding:0" ng-style="{paddingBottom:$last?0:5}">'
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
			if(ctrl) ctrl.add($scope);
			$scope.items	= $scope.items || [];
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
			// $scope.$watchCollection('items',function(newCollection, oldCollection){
			// 	alert(newCollection)
			// });
		}
	};
})
/**
 * 表格
 * 例子：
 * 		<ext-form-item-array-dropdown name="items" items="[{key:ID,val:'分类名'}]" key="text" empty="请选择分类名称" />
 */
.directive('extFormItemArrayDropdown',function($compile,$parse){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			name 	: '@',
			value 	: '@',
			empty 	: '@',
			size	: '=?',
			items	: '=?'
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			$scope.rows		= [];
			$scope.items	= $scope.items || [];
			var key 	= iAttrs.key	|| 'id';
			var val 	= iAttrs.val	|| 'name';
			var tip 	= iAttrs.tip	|| "您最多只能添加{0}条数据";
			$scope.$add		= function(){
				if($scope.size>-1 && $scope.rows.length >= $scope.size){
					alert(format(tip,$scope.size));return;
				}
				var json	= angular.fromJson('{"'+(iAttrs.itemkey||key)+'":""}');
				$scope.rows.push(json);
			}
			$scope.$remove	= function(idx){
		        $scope.rows.splice(idx,1);
		    }
		    $scope.$watch('item.'+$scope.name,function(newValue){
		    	if(!newValue){
		    		newValue		= [];
		    		$parse('item.'+$scope.name).assign($scope,newValue);
		    	}
		    	$scope.rows			= newValue;
			});
		    var tpl			= '<div>'
				+'<button class="btn btn-default" type="button" ng-click="$add()" ng-if="rows.length<=0">'
				+'	<span class="glyphicon glyphicon-plus"></span>'
				+'</button>'
				+'<div class="col-sm-12" ng-repeat="n in rows" style="margin:0;padding:0" ng-style="{paddingBottom:$last?0:5}">'
				+'	<div class="input-group">'
				+'		<select class="form-control" ng-model="n[\'{key}\']"><option value="">{empty}</option>{dropdown|each=items}</select>'
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
			$scope.$watch('items',function(newValue){
				if(newValue.length>0){
					temp 		= format(tpl,{
						key 	: iAttrs.itemkey||key,
						dropdown: '<option value="{'+key+'}">{'+val+'}</option>',
						items	: $scope.items,
						empty 	: $scope.empty
					});
					var html		= $compile(temp)($scope);
					iElm.replaceWith(html);
				}
			});
		}
	};
})
/**
 * 表格
 * 例子：
 * 		<ext-form-item-array-grid title="子级分类" name="items" cols="[{label:'名称',name:'name',type:'input'},{label:'操作',width:150,type:'action'}]" />
 */
.directive('extFormItemArrayGrid', function(){
	return {
		require 	: '?^extFormItem',
		restrict 	: 'E',
		replace 	: true,
		scope 		: {
			cols	: '=',
			items	: '=',
			addfn	: '=',
			title	: '@',
			name	: '@',
			size	: '=?',
			action	: '=?'
		},
		template 	: '<div>'+
					'<button type="button" class="btn btn-default" ng-click="$add()" style="margin-bottom:5px">添加{{title}}</button>'+
					'<grid name="{{name}}" cols="cols" items="items" empty="没有{{title}}" action="action" />'+
					'</div>',
		controller 	: ['$scope','$attrs',function($scope,iAttrs){
			$scope.size 	= $scope.size || -1;
			$scope.action 	= $scope.action || ['up','down','delete'];
			$scope.$watch('item',function(newValue){
		    	if(!newValue[$scope.name]){
		    		newValue[$scope.name]	= [];
		    	}
		    	$scope.items				= newValue[$scope.name];
			});
			var tip 	= iAttrs.tip	|| "您最多只能添加{0}条数据";
			var fields		= [];
			$scope.$add		= function(){
				if($scope.size>-1 && $scope.items.length >= $scope.size){
					alert(format(tip,$scope.size));return;
				}
				var item 	= {};
				if(angular.isFunction($scope.addfn)){
					item 	= $scope.addfn($scope.items);
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
		}],
		link 		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			iElm.removeAttr('title');
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
		    +'    <div class="col-md-offset-{{$_cols(0)}} col-md-{{$_cols(1)}}">'
		    +'    <div class="btn-group" ng-transclude></div>'
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
 * 单选组
	<radio-group name="名称" index="默认选项" value="已选值">
        <radio value="选项值">选项名称</radio>
        <radio value="选项值">选项名称</radio>
    </radio-group>
 */
.directive('radioGroup', function($parse){
	return {
		require 	: '?^extFormItem',
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
		controller 	: function($scope){
			var items 		= $scope.items 	= [];
			this.add 		= function(item){
				if((!$scope.value && (items.length === 0 || items.length === $scope.index-1 || item.active)) || item.value == $scope.value){
					this.select(item);
				}
          		items.push(item);
			}
			this.select 	= function(item){
				// alert(item.checked)
				angular.forEach(items,function(item){
					item.checked 	= false;
				});
				item.checked 	= true;
			}
			$scope.$watch('item',function(newValue){
				if(newValue){
					angular.forEach(items,function(item){
						item.item 		= newValue;
						item.name 		= $scope.name;
					});
				}
			});
			// $scope.$watch('items.length',function(newValue){
			// 	if(newValue){
			// 		var val 			= $scope.item[$scope.name];
			// 		angular.forEach(items,function(item){
			// 			item.item 		= $scope.item;
			// 			item.name 		= $scope.name;
			// 			alert(item.value)
			// 			// item.checked 	= item.checked || val == item.value;
			// 		});
			// 	}
			// });
			$scope.$watchCollection('items',function(newCollection, oldCollection, scope){
				if(newCollection != oldCollection){
					angular.forEach(newCollection,function(item){
						item.item 		= $scope.item;
						item.name 		= $scope.name;
						alert(item.value)
					});
				}
			});
			$scope.$watch('item.'+$scope.name,function(newValue){
				if(!angular.isUndefined(newValue)){
					angular.forEach(items,function(item){
						if(newValue == item.value){
							item.checked 	= true;
						}else{
							item.checked 	= false;
						}
					});
				}
			});
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			$scope.fit		= $scope.fit || false;
			if($scope.fit){
				iElm.css("width","100%");
			}
			$scope.$watchCollection('items',function(newCollection, oldCollection){
				angular.forEach(newCollection,function(value, key){
					if($scope.fit){
						if(value.col<=0) value.col = 12 / newCollection.length;
						if($scope.disabled) value.disable = $scope.disabled;
					}
					if($scope.size){
						value.size 	= $scope.size - 1;
					}
				});
			});
		}
	};
})
.directive('radio',function($parse){
	return {
		require 	: '?^radioGroup',
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
			$scope.$watch('checked',function(newValue){
				if(newValue){
					$parse("item."+$scope.name).assign($scope,$scope.value);
				}
			});
			$scope.$watch('value',function(newValue){
				// alert($parse("item."+$scope.name))
				if(newValue && $scope.checked){
					$parse("item."+$scope.name).assign($scope,newValue);
				}
			});
		},
		link 		: function($scope, iElm, iAttrs, ctrl){
			if(ctrl) ctrl.add($scope);
			$scope.$click 	= function(){
				ctrl.select($scope);
			}
		}
	};
})
// .directive('checkbox',function($parse){
// 	return {
// 		require 	: '?^ngModel',
// 		restrict 	: 'A',
// 		link 		: function($scope, iElm, iAttrs, ctrl){
// 			ctrl.$render	= function(){

// 			}
// 		}
// 	};
// })
.directive('thumbnail',function($compile){
	return {
		restrict		:'EA',
		replace			: true,
		scope			: {
			src			: '@'
		},
		link			: function($scope, iElm, iAttrs){
			$scope.flag	= false;
			var size	= iAttrs.size && iAttrs.size != 'undefined' ? iAttrs.size : '100x100';
			var rate	= iAttrs.rate && iAttrs.rate != 'undefined' ? iAttrs.rate : 1;
			var ret		= size.split('x');
			var w 		= parseInt(ret[0] * rate);
			var h 		= parseInt(ret[1] * rate);
			var tpl		= '<div class="thumbnail" style="width:{w}px;">'
				+'<div class="thumbnail-image" style="width:{w}px;height:{h}px;line-height:{h}px">'
				+'<span ng-show="!flag">{empty}</span>'
				+'<img ng-show="flag" width="{w}" height="{h}" />'
				+'</div>'
				+'</div>';
			tpl 		= format(tpl,{
				w 		: w,
				h 		: h,
				empty 	: iAttrs.empty || size
			});
			// alert(tpl)
			var html		= $compile(tpl)($scope);
			iElm.replaceWith(html);
			function imgAuto(img,image){
				var width   = img.width;
                var height  = img.height;
                var pwidth  = w;
                var pheight = h;
                if(width>0 && height>0){
                    var rate = (pwidth/width < pheight/height) ? pwidth/width : pheight/height;
                    if(rate <= 1){
                        width  *=  rate;
                        height *=  rate;
                    }
                    var left = (pwidth - width) * 0.5;
                    var top  = (pheight - height) * 0.5;
                    image.css({
                        "margin-left" : left + "px",
                        "margin-top"  : top + "px",
                        "width"       : width + "px",
                        "height"      : height + "px"
                    });
                    image.attr("src",img.src);
                    $scope.flag	= true;
                    $scope.$apply();
                }
			}
			$scope.$watch('src', function(newValue,oldValue,scope){
				if(newValue){
					var image 	= html.find('img');
					var img     = new Image();
	            	img.src     = $ROOT+'/'+newValue;
	            	if(img.complete){
		                image.attr("src",img.src);
		                imgAuto(img,image);
		            }else{
		            	img.onerror = function(){
		            		// image.hide();
		            		// $scope.src = false;
		            		// $scope.$apply();
		            	}
		            	$(img).load(function(){
		            		imgAuto(img,image);
		            	});
		            }
		        }else{
		        	$scope.flag	= false;
		        }
			});
		}
	};
})
.directive('uploadOne',function($compile,Upload,$parse,$timeout){
	return {
		restrict 	: 'C',
		replace 	: true,
		scope 		: {
			value 	: '@',
			data 	: '=?',
			debug 	: '=?'
		},
		controller 	: function($scope){
			$scope.$percentage	= 0;
			$scope.$flag		= false;
		    $scope.$watch('file',function(){
		        if($scope.file != null){
		            $scope.upload($scope.file);
		        }
		    });
			$scope.upload 		= function(file){
		     	$scope.$flag 				= true;
		     	$scope.$percentage 			= 0;
		     	var data 		= {
		     		'file'		: $scope.value
		     	};
		     	if($scope.filename){
		     		data['filename']		= $scope.filename;
		     	}
		     	Upload.upload({
					url		: $CONTROLLER+$scope.url,
					method 	: 'POST',
					fields  : data,
					file 	: file
				}).progress(function(evt){
					var progressPercentage 	= parseInt(100.0 * evt.loaded / evt.total);
					$scope.$percentage 		= progressPercentage;
				}).success(function (data, status, headers, config){
					if($scope.debug)
						$scope.debug = data;
					if(data.status){
						$scope.value 		= data.info;
						$scope.data 		= data;
					}else{
						alert(data.info);
					}
					$timeout(function(){
						$scope.$flag		= false;
					},1000);
				}).error(function(data, status, headers, config){
					alert('Err:'+data);
				});
			}
		},
		link  		: function($scope, iElm, iAttrs){
        	$scope.url		= iAttrs.url 		|| '/upload';
        	$scope.filename	= iAttrs.filename 	|| false;
			var tpl 	= '<div>'+
			(isset(iAttrs.size)?'<thumbnail size="{size}" rate="{rate}" src="{{{model}}}"></thumbnail>':'')+
			'<div class="progress" ng-if="$flag">'+
            '    <div class="progress-bar" style="width:{{$percentage}}%;">{{$percentage}}%</div>'+
            '</div>'+
			'<div class="input-group">'+
	        '    <input class="form-control" type="text"{name|attr="name"}{value|attr="value"}{empty|attr="placeholder"} />'+
	        '    <span class="input-group-btn">'+
	        '        <button type="button" class="btn btn-default" ng-disabled="$flag"'+
	        ' ngf-select ng-model="file"'+
	        ''+
	        ' >{btn}</button>'+
	        '    </span>'+
	        '</div>{debug}'+
	        '</div>';
			tpl 		= format(tpl,{
				name 	: iAttrs.name,
				value 	: "{{value}}",
				empty 	: iAttrs.empty,
				btn 	: iAttrs.btn || '上传',
				size 	: iAttrs.size || '',
				rate 	: iAttrs.rate || 1,
				debug 	: '<div if="debug">{{debug}}</div>'
			});
			var html	= $compile(tpl)($scope);
			iElm.replaceWith(html);
		}
	};
})
.directive('upload1', function(Upload,$parse){
	return {
		restrict 	: 'C',
    	replace 	: true,
    	template 	: '<div>'+
            '<img ng-if="$image && $path" src="'+($ROOT||'/')+'{{$path}}" class="img-thumbnail" style="margin-bottom:5px;" />'+
    		'<div class="progress" ng-if="$flag&&$percentage>0">'+
            '    <div class="progress-bar" style="width:{{$percentage}}%;">{{$percentage}}%</div>'+
            '</div>'+
    		'<div class="input-group">'+
	        '    <input class="form-control" type="text" name="path" ng-model="$path" placeholder="{{$placeholder}}" />'+
	        '    <span class="input-group-btn">'+
	        '        <button type="button" class="btn btn-default" ngf-select ng-model="file" ngf-multiple="false" ng-disabled="$flag">{{$btn}}</button>'+
	        '    </span>'+
	        '</div>'+
	        '</div>',
	    controller 	: function($scope){
	    	$scope.$files		= [];
			$scope.$percentage	= 0;
			$scope.$flag		= false;
		    $scope.$watch('file',function(){
		        if($scope.file != null){
		            $scope.upload($scope.file);
		        }
		    });
			$scope.$upload 		= function(file){
				$scope.$flag 	= true;
				Upload.upload({
					url		: $CONTROLLER+$scope.$url,
					fields  : {
						'file': "$scope.username"
                    },
					file 	: file
				}).progress(function(evt){
					var progressPercentage 	= parseInt(100.0 * evt.loaded / evt.total);
					$scope.$percentage 		= progressPercentage;
				}).success(function (data, status, headers, config){
					if(data.code == 201){
						alert(data.msg)
					}else{
						$scope.result = data;
						$scope.$flag 			= false;
						$scope.$percentage 		= 0;
						$scope.$path			= data.path;
						$scope.$success(data);
						$scope.row 				= data.row;
						window.setTimeout(function() {
							$scope.$flag 		= false;
							$scope.$percentage 	= 0;
						}, 1000);
					}
				});
			}
	    },
	    link  		: function($scope, iElm, iAttrs){
	    	$scope.$placeholder 	= iAttrs.placeholder || '请上传文件';
        	$scope.$btn 			= iAttrs.btn || '上传';
        	$scope.$url 			= iAttrs.url || '/upload';
        	$scope.$image			= iAttrs.image || false;
        	iElm.removeClass('form-control');
      //   	if(iAttrs['value']){
      //   		$scope.$path 		= iAttrs['value'];
      //   	}
      //   	if(iAttrs['ngModel']){
	     //    	$scope.$watch(iAttrs['ngModel'],function(newValue, oldValue){
	     //    		$scope.$path = newValue;
	     //    	});
	     //    	$scope.$watch('$path',function(newValue, oldValue, scope) {
	     //    		$parse(iAttrs['ngModel']).assign($scope,newValue);
			   //  });
		    // }
		    // $scope.$success 	= function(data){
		    // 	if(iAttrs.success)
		    // 		$scope[iAttrs.success](data);
		    // }
	    }
	};
})
.directive('ueditor',function($timeout){
	return {
		restrict 	: 'C',
    	scope 		: {},
    	link  		: function($scope, iElm, iAttrs){
    		var id		= iAttrs.id ? iAttrs.id : "_editor" + (Date.now())+Math.random();
    		iElm.attr('id',id);
    		if(typeof UE !== 'undefined'){
				var _updateByRender 	= true;
				var editor = UE.getEditor(id,{
					// initialFrameWidth 	: 600,
					initialFrameHeight 	: 300,
        			autoFloatEnabled 	: false
				});
				editor.ready(function(){
					$scope.$on("$destroy",function(){
						editor.destroy();
            			editor = null;
					});
				});
			}
    	}
	};
})
.directive('checkboxGroupArray',function($parse){
	return {
		require 	: 'ngModel',
		restrict 	: 'A',
		compile 	: function(iElm, iAttrs,$scope){
			iElm.removeAttr('ng-model');
			var children = iElm[0].querySelectorAll('input[type="checkbox"]');
			angular.forEach(children,function(child){
				var childEl = angular.element(child);
				childEl.attr('checkbox',iAttrs.ngModel);
			});
			return function($scope, iElem, iAttrs, ngModel){
				$scope.$watch(iAttrs.ngModel,function(newValue){
					if(isset(newValue)){
						switch(gettype(newValue)){
							case 'string':
								$parse(iAttrs.ngModel).assign($scope,newValue.split(','));
								break;
						}
					}
				});
			}
		}
	};
})
.directive('checkbox',function($parse){
	var constantValueRegExp = /^(true|false)$/;
	return {
		require 	: 'ngModel',
		restrict 	: 'A',
		link 		: function($scope, iElm, iAttrs, ngModel){
			var items 	= $scope.$eval(iAttrs.checkbox);
			var value 	= iAttrs.value;
			if(constantValueRegExp.test(value)){
				value	= $scope.$eval(value);
			}
			var index 	= items.indexOf(value);
			if(index > -1){
				iElm[0].checked = true;
				ngModel.$setViewValue(true);
			}
			$scope.$watch(iAttrs.ngModel,function(newValue){
				iElm.parent()[newValue?'addClass':'removeClass']('active');
				if(newValue){
					if(items.indexOf(value) == -1)
						items.push(value);
				}else{
					var index = items.indexOf(value);
					if(index>-1)
						items.splice(index,1)
				}
			});
		}
	};
})
/*
<input checkbox-all="list.checked" />
 */
.directive('checkboxAll',function($parse){
	return {
		require 	: 'ngModel',
		restrict 	: 'A',
		link 		: function($scope, iElm, iAttrs, ngModel){
			var parts		= iAttrs.checkboxAll.match(/(.+)\.(.+)/).slice(1);
			var items		= $scope.$eval(parts[0]);
			iElm.bind('click',function(){
				angular.forEach(items,function(value){
					value[parts[1]]	= ngModel.$modelValue;
				});
				$scope.$apply();
			});
			if(items.length>0){
				var flag = false;
				$scope.$watch(parts[0],function(newValue){
					if(flag){
						var hasChecked		= true;
						angular.forEach(newValue,function(value){
							if(!value[parts[1]]){
								value[parts[1]] = false;
								hasChecked 		= false;
							}else{
								value[parts[1]] = true;
							}
						});
						$parse(iAttrs['ngModel']).assign($scope,hasChecked);
					}else
						flag = true;
				},true);
			}
		}
	};
})

// .directive('bsCheckbox1',function($parse){
// 	var constantValueRegExp = /^(true|false|\d+)$/;
// 	return {
// 		require 	: "ngModel",
// 		restrict 	: 'A',
// 		link 		: function($scope, iElm, iAttrs, ngModel){
// 			var trueValue 	= angular.isDefined(iAttrs.trueValue) ? iAttrs.trueValue : true;
// 			var falseValue 	= angular.isDefined(iAttrs.falseValue) ? iAttrs.falseValue : false;
// 			if(constantValueRegExp.test(iAttrs.trueValue)){
// 				trueValue 	= $scope.$eval(iAttrs.trueValue);
// 			}
// 			if(constantValueRegExp.test(iAttrs.trueValue)){
// 				falseValue 	= $scope.$eval(iAttrs.falseValue);
// 			}
// 			// ngModel.$parsers.push(function(viewValue) {
// 			// 	return viewValue ? trueValue : falseValue;
// 			// });
// 			// ngModel.$formatters.push(function(modelValue) {
// 			// 	return angular.equals(modelValue, trueValue);
// 			// });
// 			// $scope.$watch(iAttrs.ngModel, function(newValue, oldValue) {
// 			// 	alert(newValue)
// 			// 	ngModel.$render();
// 			// });
// 			ngModel.$render = function(){
// 				var isActive = angular.equals(controller.$modelValue, trueValue);
// 			}
// 			iElm.bind('click',function(){
// 				$scope.$apply(function(){
// 					alert(ngModel.$modelValue)
// 					// ngModel.$setViewValue(!ngModel.$modelValue);
// 				});
// 			});
// 		}
// 	};
// })
/*
<input checkbox-all="list.checked" />
 */
// .directive('checkboxAll',function($parse){
// 	return {
// 		require 	: "ngModel",
// 		link 		: function($scope, iElm, iAttrs, ngModel){
// 			var parts		= iAttrs.checkboxAll.match(/(.+)\.(.+)/).slice(1);
// 			var items		= $scope.$eval(parts[0]);
// 			// alert(parts)
// 			// alert(items)
// 			iElm.bind('change',function(){
// 				$scope.$apply(function(){
// 					iElm[ngModel.$modelValue?'addClass':'removeClass']('active');
// 					angular.forEach(items,function(value){
// 						value[parts[1]]	= ngModel.$modelValue;
// 					});
// 				});
// 			});
// 			// $scope.$watch(function(){
// 			// 	return ngModel.$modelValue;
// 			// },function(newValue, oldValue){
// 			// 	if(isset(newValue)){
// 			// 		iElm[ngModel.$modelValue?'addClass':'removeClass']('active');
// 			// 		angular.forEach(items,function(value){
// 			// 			value[parts[1]]	= newValue;
// 			// 		});
// 			// 	}
// 			// });
// 			if(items.length>0){
// 				$scope.$watch(parts[0],function(newValue){
// 					var hasChecked		= true;
// 					angular.forEach(newValue,function(value){
// 						if(!value[parts[1]]){
// 							hasChecked 	= false;
// 						}
// 					});
// 					$parse(iAttrs['ngModel']).assign($scope,hasChecked);
// 	                iElm[hasChecked?'addClass':'removeClass']('active');
// 				},true);
// 			}
// 		}
// 	};
// })
/*
<state-checkbox name="{{v1.name}}" checked="v1.checked" checkboxes="v1.items"></state-checkbox>
 */
// directive('stateCheckbox',function($filter){
// 	return {
// 		restrict 	: 'E',
// 		replace 	: true,
// 		scope		: {
// 			name 		: '@',
// 			checked 	: '=?',
// 			checkboxes 	: '=?',
// 			filter 		: '=?',
// 			checkedes	: '=?'
// 		},
// 		template 	: '<div>'+
// 			'<label>'+
// 			'<input type="checkbox" ng-model="checked" ng-change="masterChange()" />'+
// 			'{{name}}</label>'+
// 			'<ul>'+
// 			'<li ng-repeat="item in checkboxes">'+
// 			'<label>'+
// 			'<input type="checkbox" ng-model="item.checked" />'+
// 			'{{item.name}}'+
// 			'</label>'+
// 			'</li>'+
// 			'</ul>'+
// 			'</div>',
// 		controller 	: function($scope,$element){
// 			var one_run			= true;
// 			if($scope.filter){
// 				$scope.checkboxes 	= $filter('itemsfilter')($scope.checkboxes,$scope.filter);
// 			}
// 			$scope.masterChange = function(){
// 				angular.forEach($scope.checkboxes,function(value,key){
// 					value.checked	= $scope.checked;
// 				});
// 			}
// 			$scope.$watch('checkboxes',function(){
// 				var hasChecked 		= true;
// 				angular.forEach($scope.checkboxes,function(value,key){
// 					if(one_run && $scope.checkedes){
// 						value.checked	= $scope.checkedes.indexOf(value.id)>-1;
// 					}
// 					if(!value.checked)
// 						hasChecked	= false;
// 				});
// 				one_run 			= false;
// 				$scope.checked 		= hasChecked;
// 			},true);
// 		}
// 	};
// })
/*

.directive('bsCheckboxGroup', function() {
    return {
      restrict: 'A',
      require: 'ngModel',
      compile: function postLink(element, attr) {
        element.attr('data-toggle', 'buttons');
        element.removeAttr('ng-model');
        var children = element[0].querySelectorAll('input[type="checkbox"]');
        angular.forEach(children, function(child) {
          var childEl = angular.element(child);
          childEl.attr('bs-checkbox', '');
          childEl.attr('ng-model', attr.ngModel + '.' + childEl.attr('value'));
        });
      }
    };
  })
.directive('bsCheckbox', [ '$button', '$$rAF', function($button, $$rAF) {
    var defaults = $button.defaults;
    var constantValueRegExp = /^(true|false|\d+)$/;
    return {
      restrict: 'A',
      require: 'ngModel',
      link: function postLink(scope, element, attr, controller) {
        var options = defaults;
        var isInput = element[0].nodeName === 'INPUT';
        var activeElement = isInput ? element.parent() : element;
        var trueValue = angular.isDefined(attr.trueValue) ? attr.trueValue : true;
        if (constantValueRegExp.test(attr.trueValue)) {
          trueValue = scope.$eval(attr.trueValue);
        }
        var falseValue = angular.isDefined(attr.falseValue) ? attr.falseValue : false;
        if (constantValueRegExp.test(attr.falseValue)) {
          falseValue = scope.$eval(attr.falseValue);
        }
        var hasExoticValues = typeof trueValue !== 'boolean' || typeof falseValue !== 'boolean';
        if (hasExoticValues) {
          controller.$parsers.push(function(viewValue) {
            return viewValue ? trueValue : falseValue;
          });
          controller.$formatters.push(function(modelValue) {
            return angular.equals(modelValue, trueValue);
          });
          scope.$watch(attr.ngModel, function(newValue, oldValue) {
            controller.$render();
          });
        }
        controller.$render = function() {
          var isActive = angular.equals(controller.$modelValue, trueValue);
          $$rAF(function() {
            if (isInput) element[0].checked = isActive;
            activeElement.toggleClass(options.activeClass, isActive);
          });
        };
        element.bind(options.toggleEvent, function() {
          scope.$apply(function() {
            if (!isInput) {
              controller.$setViewValue(!activeElement.hasClass('active'));
            }
            if (!hasExoticValues) {
              controller.$render();
            }
          });
        });
      }
    };
  } ])
 */