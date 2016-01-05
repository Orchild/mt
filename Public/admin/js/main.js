var App = angular.module('APP', ['ngRoute','ngResource','directive','filter','ngFileUpload','mgcrea.ngStrap.modal','mgcrea.ngStrap.datepicker'],function($httpProvider){
	$httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
});
App.config(function($modalProvider){
	angular.extend($modalProvider.defaults, {
		animation: 'am-flip-x'
	});
})
// App.config(['$ocLazyLoadProvider',function($ocLazyLoadProvider){
//     $ocLazyLoadProvider.config({
//         debug 		: true
//     });
// }]);

App.controller('main',function($scope){
	// $ocLazyLoad.load([
	// 	$JS + 'login.js'
	// ]).then(function(){

	// });
});
App.controller('save',function($scope,$http,$element,$timeout){
	// $scope.row 		= {};
	var $timeoutID 	= null;
	$scope.second 	= -1;
	$element.find('select').each(function(){
		var self 	= $(this);
		self.val(self.attr('value'));
	});
	$element.find('.verify').verify();
	$scope.show 	= function(){
		if($timeoutID) $timeout.cancel($timeoutID);
		$element.parent().parent().overlay();
	}
	$scope.hide 	= function(){
		$element.parent().parent().overlay('hide');
	}
	$element.submit(function(){
		var self 	= angular.element(this);
		$scope.show();
		// alert(self.attr("action"))
		$http.post(self.attr("action"),params(self.serializeArray())).success(function(data){
			if(data && data.status == true){
				$scope.second	= 0;
				$scope.alert 	= {
					cls 		: 'alert-info',
					msg 		: data.info
				};
			}else if(data.status == false){
				$scope.second		= 10;
				$scope.alert	= {
					cls 		: 'alert-danger',
					msg 		: data.info
				};
				$element.find('.verify').click();
			}else{
				alert(data);
			}
			$scope.hide();
			$timeoutID = $timeout(function(){
				$scope.alert 	= false;
				if(data.url){
					window.location.replace(data.url);
				}
			},1000 * $scope.second);
		}).error(function(err){
			console.log("Error while posting to Request Bin");
        	console.log("Error Info : " + err);
			$scope.hide();
		});
		return false;
	});
});