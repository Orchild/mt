App.factory('Model',['$resource',function($resource){
    return $resource($CONTROLLER+'/:act',{act:'find'},{
        query   : {method:'POST',params:{act:'lists'}},
        sort    : {method:'POST',params:{act:'sort'}},
        hot     : {method:'POST',params:{act:'headmsg'}}
    });
}]);
App.controller('HotNewsController',function($scope,$location,$modal,Model){
    $scope.rows             = [];
    $scope.$reload          = function(){
        $.overlay();
        Model.hot({action:'lists'},function(data){
            if(!data.status){
                alert(data.info)
            }else{
                $scope.rows     = data.items;
            }
            $.unoverlay();
        });
    };
    $scope.$reload();
    var $dlgbrowse      = $modal({
        scope           : $scope,
        title           : '提示',
        contentTemplate : 'browse.html',
        controller      : 'ListController',
        show            : false
    });
    $scope.$add             = function(){
        if($scope.rows.length >= 5){
            alert(format("您最多只能添加{0}条数据",5));
        }else{
            var NOW_TIME    = Math.round((new Date().getTime())/1000);
            // $scope.rows.push({
            //     created     : NOW_TIME,
            //     updated     : NOW_TIME,
            //     sort        : $scope.rows.length+1,
            //     status      : 1
            // });
            $.overlay();
            Model.hot({action:'add',sort:$scope.rows.length+1},function(data){
                if(!data.status){
                    alert(data.info)
                }else{
                    $scope.rows.push(data.item);
                }
                $.unoverlay();
            });
        }
    }
    $scope.$browse          = function(item){
        $scope.item         = item;
        $dlgbrowse.$promise.then($dlgbrowse.show);
    }
    $scope.$status          = function(item,index,value,name){
        var self            = this;
        Model.hot({action:'status',id:item.id,field:name,value:value},function(data){
            if(!data.status)
                alert(data.info)
            self.update();
        });
    }
    $scope.$up              = function(item){
        Model.hot({action:'sort',items:item.update(4)},function(data){
            if(data.status)
                item.update();
            else
                alert(data.info)
        });
    }
    $scope.$down            = function(item){
        Model.hot({action:'sort',items:item.update(4)},function(data){
            if(data.status)
                item.update();
            else
                alert(data.info)
        });
    }
    $scope.$remove          = function(item){
        if(confirm('您确定要删除该条记录吗？')){
            Model.hot({action:'delete',id:item.id},function(data){
                if(data.status)
                    item.remove();
                else
                    alert(data.info)
            });
        }
    }
});
App.controller('ListController',function($scope,Model,$location){
    $scope.title            = '消息';
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
        });
    }
    $scope.$reload();
    $scope.$refresh         = function(){
        $scope.$reload($scope.page.page);
    }
    $scope.s                = {};
    $scope.$search          = function(){
        $scope.$reload(1,0);
    }
    $scope.$click           = function(item){
        $scope.item.msg_id = item.id;
        $scope.item.title   = item.title;
        Model.hot(angular.extend({action:'edit'},$scope.item),function(data){
            if(!data.status)
                alert(data.info)
        });
        $scope.$hide();
    }
    $scope.$status          = function(item,index,value,name){
        var self            = this;
        Model.status({id:item.id,field:name,value:value},function(data){
            if(!data.status)
                alert(data.info)
            self.update();
        });
    }
});
App.controller('itemcontroller',function($scope,$element,Model){
    $scope.$update      = function(key,val){
        var data        = {};
        data['action']  = 'edit';
        data['id']      = $scope.item.id;
        data[key]       = val;
        Model.hot(data,function(data){
            if(!data.status)
                alert(data.info)
        });
    }
    // $scope.$watch('item.news_id',function(newValue, oldValue) {
    //     if(newValue!==oldValue){
    //         $scope.$update('news_id',newValue);
    //     }
    // });
    // $scope.$watch('item.title',function(newValue, oldValue) {
    //     if(newValue!==oldValue){
    //         $scope.$update('title',newValue);
    //     }
    // });
    $scope.$watch('item.image',function(newValue, oldValue) {
        if(newValue!==oldValue){
            $scope.$update('image',newValue);
        }
    });
    // $scope.$watch('item.sort',function(newValue, oldValue){
    //     if(newValue!==oldValue){
    //         $scope.$update('sort',newValue);
    //     }
    // });
});