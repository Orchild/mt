App.config(function($routeProvider){
    $routeProvider.
    when('/lists',{
        controller  : 'ListController',
        templateUrl : $CONTROLLER+'/lists.html'
    }).
    when('/add',{
        controller  : 'AddController',
        templateUrl : $CONTROLLER+'/add.html',
        publicAccess: true
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
        sort    : {method:'POST',params:{act:'sort'}},
        status  : {method:'POST',params:{act:'status'}},
        remove  : {method:'POST',params:{act:'delete'}}
    });
}]);
App.controller('ActionController',function($scope,$location){
    $scope.$add         = function(){
        $location.path('/add');
    }
    $scope.$lists       = function(){
        $location.path('/');
    }
});
App.controller('ListController',function($scope,Model,$location){
    $scope.title            = '行为';
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
    $scope.$sort            = function(item,field){
        Model.sort({id:item.id,field:field,value:item[field]},function(data){
            if(!data.status)
                alert(data.info);
            else
                item.update();
        });
    }
    $scope.$initsort        = function(item,field){
        if(confirm('您确定要重置排序吗？')){
            $.overlay();
            Model.sort({},function(data){
                $.unoverlay();
                if(!data.status)
                    alert(data.info);
                else
                    $scope.$reload(1,0);
            });
        }
    }
    $scope.$status          = function(item,index,value,name){
        var self            = this;
        Model.status({id:item.id,field:name,value:value},function(data){
            if(!data.status)
                alert(data.info)
            self.update();
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
App.controller('AddController',function($scope){
    $scope.action   = '添加';
});
App.controller('EditController',function($scope,$routeParams,Model){
    $scope.action   = '修改';
    $scope.id       = $routeParams['id'];
});