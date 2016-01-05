angular.module("providers",[])
.provider('Common',function($http,$q){
	this.$get	= function(){
		return {
			loadScript	: function(url,callback){
				var head 	= document.getElementsByTagName("head")[0];
				var script 	= document.createElement("script");
				script.setAttribute("type","text/javascript");
				script.setAttribute("src",url);
				script.setAttribute("async",true);
				script.setAttribute("defer",true);
				head.appendChild(script);
				if(document.all){
					script.onreadystatechange = function(){
						var state = this.readyState;
						if(state === 'loaded' || state === 'complete'){
							callback && callback();
						}
					}
				}else{
					script.onload = function() {
						callback && callback();
					}
				}
			},
			loadCss		: function(url){
				var ele = document.createElement('link');
				ele.href = url;
				ele.rel = 'stylesheet';
				if (ele.onload == null) {
					ele.onload = function() {
					};
				}else {
					ele.onreadystatechange = function() {
					};
				}
				angular.element(document.querySelector('body')).prepend(ele);
			}
		};
	}
})
.provider('dialog',function($rootScope,$compile,$controller){
	var dlgTpl 			= '<div class="dialog" tabindex="-1">'+
				'	<div class="modal-dialog">'+
				'	</div>'+
				'</div>';
	this.$get	= function(){
		var options 		= {
			scope 			: $rootScope.$new();
		};
		var angularDomEl	= angular.element(dlgTpl);
		var scope			= options.scope;
		var modalDomEl		= $compile(angularDomEl)(scope);
		angularDomEl.show();

		// var dlg				= $(dlgTpl);
		// dlgOverlay			= $('<div class="modal-backdrop"></div>');
		// dlg.appendTo('body').hide();
		// dlgOverlay.appendTo('body').hide();
		// dlg.find("#BMap").height($(window).height() - 220);
		// dlg.find('.close,.closed').click(function(){
		// 	that.hide();
		// });
		// dlg.find('.btn-primary').click(function(){
		// 	if(angular.isFunction(mapOptions.callback))
		// 		mapOptions.callback(p,addr.val());
		// 	that.hide();
		// });
		var that 			= {
			cfg 	: function(options){
				angular.forEach(options,function(value, key){
					if(value)
						mapOptions[key]		= value;
				});
			},
			show 	: function(){
				$BMapDlg.show();
				$BMapDlgOverlay.show();
			},
			hide 	: function(){
				$BMapDlg.hide();
				$BMapDlgOverlay.hide();
			}
		};
		return that;
	}
})
.provider('overlay',function(){
	this.$get				= function($rootScope,$compile,$controller){
		var Factory 		=  function(cfg){
			this.on 		= function(selector,eventType,fun){
				return this;
			}
			this.resize 	= function(){
				return this;
			}
			this.show 		= function(){
				return this;
			}
			this.hide 		= function(){
				return this;
			}
		}
		var cache 			= {};
		return function(id,cfg){
			if(!cache[id]){
				cache[id]	= new Factory(cfg);
			}
			return cache[id];
		}
	}
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