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
        stores  : {method:'POST',params:{act:'stores'}},
        status  : {method:'POST',params:{act:'status'}},
        remove  : {method:'POST',params:{act:'delete'}}
    });
}]);
App.controller('ActivityController',function($scope,$location){
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
        $scope.s            = {};
        $scope.$reload($scope.page.page);
    }
    $scope.s                = {};
    $scope.$search          = function(){
        $scope.$reload(1,0);
    }
    $scope.$status1          = function(item,index,value,name){
        var self            = this;
        Model.status({id:item.id,field:name,value:value},function(data){
            if(!data.status)
                alert(data.info)
            self.update();
        });
    }
    $scope.$status          = function(item,name,value){
        var self            = this;
        Model.status({id:item.id,field:name,value:value},function(data){
            if(!data.status)
                alert(data.info)
            item.update(data.status);
        });
    }
    $scope.$edit            = function(item){
        $location.path('/edit/'+item.id);
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
});
App.controller('EditController',function($scope,$routeParams,Model){
    $scope.action   = '修改';
    $scope.id       = $routeParams['id'];
    $scope.stores       = [];
    Model.stores(function(data){
        $scope.stores = data.items;
    });
});