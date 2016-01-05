App.config(function($routeProvider){
    $routeProvider.
    when('/lists',{
        controller  : 'ListController',
        templateUrl : $CONTROLLER+'/lists.html'
    }).
    when('/edit/:id',{
        controller  : 'EditController',
        templateUrl : $CONTROLLER+'/edit.html',
        publicAccess: true
    }).
    otherwise({
        redirectTo: '/lists'
    });
});
App.factory('Model',['$resource',function($resource){
    return $resource($CONTROLLER+'/:act',{act:'find'},{
        query   : {method:'POST',params:{act:'lists'}},
        pass    : {method:'POST',params:{act:'pass'}},
        reject  : {method:'POST',params:{act:'reject'}},
        check   : {method:'POST',params:{act:'check'}}
    });
}]);
App.controller('CertifyController',function($scope,$location,Model){	
    $scope.$lists       = function(){
        $location.path('/');
    }
});
App.controller('ListController',function($scope,Model,$location){
    $scope.rows             = [];
    $scope.page             = {};
    $scope.master			= {};
    $scope.checkv			= [];
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
    $scope.c                = {};
    $scope.$pass	        = function(item){
   	 if(confirm('您确定要认证该申请吗？')){    		 
   		 Model.pass({id:item.id});
   	  }
    }    
    $scope.$reject	        = function(item){
  	 if(confirm('您确定要拒绝该申请吗？')){    		 
  		 Model.reject({id:item.id});
  	  }
    }
    $scope.$remove          = function(item){
        if(confirm('您确定要删除该条记录吗？')){
            Model.remove({id:item.id},function(data){
                if(!data.status)
                    alert(data.info)
                item.remove();
                Model.query({p:$scope.page.page,size:$scope.page.size},function(data){
                    $scope.rows     = data.items;
                    $scope.page     = data;
                });
            });
        }
    }
    $scope.master.value	= 0;    
    $scope.$allCheck	= function(){
    	if($scope.master.value	== 1){
    		for(var i=0;i<$scope.rows.length;i++){
    			$scope.checkv.value[i] = 1;
    		}
    	}    	
    }
    $scope.$check		= function(item){
    	console.log($scope.checkv.value);
    	Model.check({id:item.id},function(data){
    		
    	});
    	
    }
});