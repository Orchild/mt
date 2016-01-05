$.fn.verify 		= function(){
	return this.each(function(){
		var self	= $(this);
		var src 	= self.attr("src");
		self.click(function(){
			if(src.indexOf('?')>0){
				self.attr("src",src+'&random='+Math.random());
			}else{
				self.attr("src",src.replace(/\?.*$/,'')+'?'+Math.random());
			}
		});
	});
}
function params(ary){
	var json 	= {};
	$.each(ary,function(i,n){
		json[n.name]	= n.value;
	});
	return json;
}
var App = angular.module("APP",[],function($httpProvider){
	$httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
});
App.controller('main',function($scope,$http,$element,$timeout){
	$element.find('.verify').verify();
	$element.find('form').submit(function(){
		var self 	= angular.element(this);
		$http.post(self.attr("action"),params(self.serializeArray())).success(function(data){
			console.log('login',data);
			if(data && data.status == true){
				$scope.second	= 1;
				$scope.alert 	= {
					cls 		: 'alert-info',
					msg 		: data.info
				};
			}else if(data.status == false){
				$scope.second	= 11;
				$scope.alert	= {
					cls 		: 'alert-danger',
					msg 		: data.info
				};
				$element.find('.verify').click();
			}else{
				alert(data);
			}
			$timeout(function(){
				$scope.alert 	= false;
				if(data.url){
					window.location.replace(data.url);
				}
			},1000 * $scope.second);
		});
		return false;
	});
});