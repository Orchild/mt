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
    when('/permissions/:id',{
        controller  : 'PermissionsController',
        templateUrl : $CONTROLLER+'/permissions.html',
        publicAccess: true
    }).
    otherwise({
        redirectTo: '/lists'
    });
});
App.factory('Model',['$resource',function($resource){
    return $resource($CONTROLLER+'/:act',{act:'find'},{
        query   : {method:'POST',params:{act:'lists'}},
        status  : {method:'POST',params:{act:'status'}},
        remove  : {method:'POST',params:{act:'delete'}},
        rules   : {method:'POST',params:{act:'permissions'}}
    });
}]);
App.controller('RoleController',function($scope,$location){
    $scope.$add         = function(){
        $location.path('/add');
    }
    $scope.$lists       = function(){
        $location.path('/');
    }
});
App.controller('ListController',function($scope,Model,$location){
    $scope.title            = '角色';
    $scope.rows             = [];
    $scope.page             = {};
    $scope.$reload          = function(p,size){
        $.overlay();
        Model.query({p:p||1,size:size||$scope.page.size},function(data){
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
    $scope.$sort            = function(item,field){
        Model.sort({id:item.id,field:field,value:item[field]},function(data){
            if(!data.status)
                alert(data.info);
            else
                item.update();
        });
    }
    $scope.$disable        = function(item,name){
        return item.issys==1;
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
    $scope.$click           = function(item){
        // alert(item.id)
        // $location.path('/edit/'+item.id);
        $location.path('permissions/'+item.id);
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
App.controller('PermissionsController',function($scope,$location,$routeParams,Model){
    $scope.row      = {id:$routeParams['id']};
    $scope.rules    = [];
    Model.rules(function(data){
        $scope.rules = data.items;
    });
    Model.rules({action:'get',id:$routeParams['id']},function(data){
        $scope.row  = data.info;
    });
    $scope.save     = function(){
        $.overlay();
        $scope.row['action']    = 'update';
        Model.rules($scope.row,function(data){
            if(data && data.status == true){
                $scope.alert    = {
                    cls         : 'alert-info',
                    msg         : data.info
                };
                $location.path('/');
            }else if(data.status == false){
                $scope.alert    = {
                    cls         : 'alert-danger',
                    msg         : data.info
                };
            }else{
                alert(data);
            }
            $.unoverlay();
        });
    }
});