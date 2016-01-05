var App = angular.module("APP");
App.factory('Model',['$resource',function($resource){
    return $resource($CONTROLLER+'/:act',{act:'find'},{
        query   : {method:'POST',params:{act:'index'}},
        status  : {method:'POST',params:{act:'status'}},
        remove  : {method:'POST',params:{act:'delete'}}
    });
}]);
App.controller('FeedbackController',function($scope,Model){
	$scope.rows         = [];
    $scope.page         = {};
    $scope.$reload      = function(p,size){
        $('.tables').parent().overlay();
        Model.query({p:p||1,size:size},function(data){
            $scope.rows     = data.items;
            $scope.page     = data;
            $('.tables').parent().overlay('hide');
        });
    }
    $scope.$reload();
    $scope.$status          = function(item,index,value,name){
        var self            = this;
        Model.status({id:item.id,field:name,value:value},function(data){
            if(!data.status)
                alert(data.info)
            self.update();
        });
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