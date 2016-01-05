App.config(function($routeProvider){
    $routeProvider.
    when('/lists',{
        controller  : 'ListController',
        templateUrl : $CONTROLLER+'/lists.html'
    }).
    otherwise({
        redirectTo: '/lists'
    });
});
App.factory('Model',['$resource',function($resource){
    return $resource($CONTROLLER+'/:act',{act:'find'},{
        query   : {method:'POST',params:{act:'lists'}},
        category: {method:'POST',params:{act:'category'}},
        status  : {method:'POST',params:{act:'status'}},
        remove  : {method:'POST',params:{act:'delete'}},
        reset   : {method:'POST',params:{act:'reset'}},
    });
}]);
App.controller('ResetController',function($scope,$location){
    $scope.$add         = function(){
        $location.path('/add');
    }
    $scope.$lists       = function(){
        $location.path('/');
    }
});
App.controller('ListController',function($scope,Model,$location){
    $scope.rows             = [];
    $scope.page             = {};
    $scope.$reload          = function(p,size){
        $.overlay();
        Model.query(angular.extend({p:p||1,size:size||$scope.page.size},$scope.s),function(data){
            $scope.rows     = data.items;
            $scope.page     = {
                count       : data.count,
                size        : data.size,
                page        : data.page
            };
            $.unoverlay();
        });;
    }
    $scope.$reload();
    $scope.$refresh         = function(){
        $scope.$reload($scope.page.page);
    }
    $scope.s                = {};
    $scope.$search          = function(){
        $scope.$reload(1,0);
    }
    $scope.$reset            = function(item){
    	if(confirm('您确定要重置该用户的密码吗？')){    		
    		Model.reset({id:item.id});
    	}    	
    }
});