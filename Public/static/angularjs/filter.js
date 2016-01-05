angular.module('filter',[])
.filter('read',function(){
	return function(val){
		return val==1?'必读':'非必读';
	};
})
.filter('name_cert',function(){
	return function(val){
		return val==1?'已认证':'未认证';
	};
})
.filter('sex',function(){
	return function(val){
		return val==1?'女':'男';
	};
})
.filter('status',function(){
	return function(val){
		return val>0?'已启用':'已禁用';
	};
})
.filter('default',function(){
	return function(val,def){
		return val||def;
	};
})
.filter('price',function($filter){
	var emptyRe = /^(undefined|null|\s+)$/
	return function(val,cfg){
		cfg 		= cfg || [];
		var prefix	= cfg[0];
		var suffix	= cfg[1];
		var value 	= null;
		if(!empty(prefix) && !emptyRe.test(prefix)){
			value 	= $filter('currency')(val,prefix);
		}
		if(!empty(suffix) && !emptyRe.test(suffix)){
			value 	= (value||val)+suffix;
		}
		if(empty(value)){
			value 	= $filter('currency')(val,'￥ ');
		}
		return value;
	};
})
.filter('keywords',function(){
	return function(val,word){
		if(word){
			var re = new RegExp("("+word.replace(/[(){}.+*?^$|\\\[\]]/g,"\\$&")+")","ig");
			return val.replace(re,'<b style="color:red;">$1</b>');
		}else
			return val;
	};
})
.filter('sizeFormat',function(){
	return function(val){
		if(!val) return val;
		var unitArr = new Array("Bytes","KB","MB","GB","TB","PB","EB","ZB","YB");
    	var srcsize = parseInt(val);
    	var index 	= Math.floor(Math.log(srcsize)/Math.log(1024));
    	var size 	= Math.round(srcsize/Math.pow(1024,index)*100)/100;
    	return size+unitArr[index];
	};
})
.filter('ip',function(){
	return function(val){
		if(!val) return val;
		return /^\d+$/.test(val)?long2str(val):str2long(val);
	};
})
.filter('dateFormat',function($filter){
	return function(val){
		if(!val) return '';
		return $filter('date')(val,'y-MM-dd hh:mm:ss');
	};
})
.filter('timeFormat',function(){
	return function(val){
		if(!val) return val;
		var unitArr = new Array("刚刚","分钟前","小时前","一天前","一周前","一月前","一年前","更早前");
		var now 	= new Date();
		var old 	= new Date(val * 1000);
		var second 	= (Date.parse(now) - Date.parse(old)) / 1000;
		var result 	= '';
		if(second < 60){
			result 	= unitArr[0];
		}else if(second >= 60 && second < 180){
			result 	= 1+unitArr[1];
		}else if(second >= 180 && second < 3600){
			result 	= 3+unitArr[1];
		}else if(second >= 3600 && second < 10800){
			result 	= 1+unitArr[2];
		}else if(second >= 10800 && second < 86400){
			result 	= 3+unitArr[2];
		}else if(second >= 86400 && second < 604800){
			result 	= unitArr[3];
		}else if(second >= 604800){
			var year	= now.getFullYear() - old.getFullYear();
			var month	= year * 12 + now.getMonth() - old.getMonth();
			if(month<1){
				result 	= unitArr[4];
			}else if(month>=1){
				result 	= unitArr[5];
			}else if(year>=1 && year<5){
				result 	= unitArr[6];
			}else{
				result 	= unitArr[7];
			}
		}
		return result;
	};
})
.filter('search', function(){
	return function(items,val){
		var result 	= items;
		if(val){
			result 	= items.filter(function(n,i){
				var flag		= true;
		        for(var i in val){
		        	var value 	= val[i];
		        	flag 		= value?n[i].indexOf(value)>-1:true;
					if(flag) break;
				}
		        return flag;
			});
		}
		return result;
	};
})
.filter('itemsfilter',function(){
	return function(items,json){
		return items.filter(function(v){
			return v[json.key] == json.val;
		});
	};
})