App.controller('ServiceController',function($scope,$location,$element){
	$scope.rows 			= [];
	var str_json 			= $element.find('textarea').val();
	if(str_json){
		$scope.rows			= new Function('return '+str_json)();
	}
	$scope.$add             = function(items){
		var row 			= {};
		row['id']			= items.length + 1;
		row['name']			= '';
		row['phone']		= '';
		return row;
    }
});