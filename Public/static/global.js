function format(tpl,json){try{
	var args			= Array.prototype.slice.call(arguments,1);
	return tpl.replace(/\{(\d+|\w+?)(?:\|(.+?))?\}/ig,function(m,n,fn){
		if(/^\d+$/.test(n)){
			var values	= args;
			var $value 	= args[n];
		}else{
			var values	= json;
			var $value 	= json[n];
		}
		if(fn){
			angular.forEach(fn.split('|'),function(n,i){
				var f	= n.split('=');
				if(/^this/.test(f[0])){
					f[0]	= f[0].replace(/^this/,'values.plugin');
				}
				with(values){
					$value	= eval(f[0]+'($value'+(f[1]?','+f[1]:'')+')');
				}
			});
		}
		return $value;
	});}catch(e){alert("format:"+e)}
}
var IIF					= function(val1,val2,val3){
	return val1?val2:val3;
}
var IFF					= function(val1,val2){
	return val2?val1:'';
}
var def					= function(val1,val2){
	return val1?val1:val2||"";
}
var attr				= function($val,$key){
	return $val?' '+$key+'="'+$val+'"':'';
}
var cls					= function(){
	var args			= Array.prototype.slice.call(arguments,0);
	return args.length>0?' class="'+args.join(' ')+'"':'';
}
var cols				= function($val){
	return cls($val?"col-md-"+$val:"");
}
var each 				= function($val,items,plugin){
	var res 			= "";
	angular.forEach(items,function(n,i){
		n.plugin		= plugin;
		res 			+= format($val,n);
	});
	return res;
}
var map 				= function($val,tpl,plugin){
	return each(tpl,$val,plugin);
}
var iterator			= function($val,tpl,plugin){
	return each(tpl,$val,plugin);
}
function params(ary){
	var json 	= {};
	ary.map(function(n,i){
		if(/\[\]$/.test(n.name)){
			var key 		= n.name.replace(/\[\]$/,'');
			if(empty(json[key])){
				json[key]	= [];
			}
			json[key].push(n.value);
		}else if(/\[.+?\]$/.test(n.name)){
			var key 		= n.name.replace(/\[.+$/,'');
			var res 		= [];
			var result 		= 'json["'+key+'"]';
			n.name.replace(/\[([^\]]+)\]/ig,function(m,i){
				if(/^\d+$/.test(i)){
					res.push('if(empty('+result+'))'+result+'=[];');
					result += m;
				}else{
					res.push('if(empty('+result+'))'+result+'={};');
					result += '["'+i+'"]';
				}
			});
			result += '=n.value;';
			try{
				eval(res.join('')+result);
			}catch(e){alert(e)}
		}else
			json[n.name]	= n.value;
	});
	return json;
}
function parseQuery(json){
	return angular.toJson(json).replace(/\"|\{|\}/g,'').replace(/\,/g,'&').replace(/\:/g,'=');
}
function gettype(variable){
	return Object.prototype.toString.call(variable).slice(8,-1).toLowerCase();
}
function empty(variable){
	var emptyValues	= [undefined,null,false,0,'','0'];
	for(var i = 0; i < emptyValues.length; i++){
		if(variable === emptyValues[i])
			return true;
	}
	if(typeof(variable) === 'object'){
		for(key in variable){
			return false;
		}
		return true;
	}
	return false;
}
function isset(variable){
	return variable === undefined || variable == null ? false : true;
}
function long2str(iplong){
    return new Function('return 0x'+sprintf('%X',iplong).replace(/\B(?=(?:.{2})+(?!.))/g,'+"."+0x'))();
}
function str2long(ipstr){
    return sprintf('%d','0x'+ipstr.replace(/\d+\.?/g,function(m){return sprintf('%02X',m);}));
}
function parse_name(str){
	var re 		= /([^&=]+?)(?:&|$|=([^&]*))/ig;
	var result 	= {};
	while((arr 	= re.exec(str)) !=null){
		// result.push(arr.slice(1));
		result[arr[1]]	= arr[2];
	}
	return result;
}
var str_repeat			= function(str,len){
	return len>0?Array(len+1).join(str.replace(/^'/,'')):'';
}
function sprintf(format){
	var get_type			= function(variable){
		return Object.prototype.toString.call(variable).slice(8,-1).toLowerCase();
	}
	var str_repeat			= function(str,len){
		return len>0?Array(len+1).join(str.replace(/^'/,'')):'';
	}
	var pad = function (str, len, chr, leftJustify) {
        if (!chr) {
            chr = ' ';
        }
        var padding = (str.length >= len) ? '' : Array(1 + len - str.length >>> 0).join(chr);
		return leftJustify ? str + padding : padding + str;
    };
	var params				= Array.prototype.slice.call(arguments,1);
	var idx					= 0;
	return format.replace(/\x25(?:(\d+)\$)?(-)?(?:(0|'[\s\S])?(\d+))?(?:\.(\d+))?([b-fosuxX])/g,function(match,cite,align,pad_character,m,n,type){
		var 	$val,$pad_length;
		$val				= cite?params[cite-1]:params[idx++];
		switch(type){
			case 'b':$val	= parseInt($val,10).toString(2);							break;
			case 'c':$val	= String.fromCharCode($val);								break;
			case 'd':$val	= parseInt(Math.round($val),10);							break;
			case 'e':$val	= n?$val.toExponential(n):$val.toExponential();				break;
			case 'f':$val	= n?parseFloat($val).toFixed(n):parseFloat($val);			break;
			case 'o':$val	= parseInt($val,10).toString(8);							break;
			case 's':$val	= $val&&n?$val.substring(0,n):$val;							break;
			case 'u':$val	= Math.abs(parseInt($val,10));								break;
			case 'x':$val	= parseInt($val,10).toString(16);							break;
			case 'X':$val	= parseInt($val,10).toString(16).toUpperCase();				break;
		}
		$pad_length			= parseInt(m||0,10) - String($val).length;
		$pad_character		= str_repeat(pad_character||' ',$pad_length);
		return align?$val+$pad_character:$pad_character+$val;
	});
}
function base64_decode(data){
	var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
    ac 		= 0,
    enc 	= '',
    tmp_arr = [];
    do{
		h1 	= b64.indexOf(data.charAt(i++));
		h2 	= b64.indexOf(data.charAt(i++));
		h3 	= b64.indexOf(data.charAt(i++));
		h4 	= b64.indexOf(data.charAt(i++));
		bits= h1 << 18 | h2 << 12 | h3 << 6 | h4;
		o1 	= bits >> 16 & 0xff;
		o2 	= bits >> 8 & 0xff;
		o3 	= bits & 0xff;
		if (h3 == 64) {
			tmp_arr[ac++] 	= String.fromCharCode(o1);
		} else if (h4 == 64) {
			tmp_arr[ac++] 	= String.fromCharCode(o1, o2);
		} else {
			tmp_arr[ac++] 	= String.fromCharCode(o1, o2, o3);
		}
    }while(i < data.length);
	dec = tmp_arr.join('');
	return dec.replace(/\0+$/, '');
}
function base64_encode(data){
	var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
    ac 		= 0,
    enc 	= '',
    tmp_arr = [];
    do{
		o1 	= data.charCodeAt(i++);
		o2 	= data.charCodeAt(i++);
		o3 	= data.charCodeAt(i++);
		bits= o1 << 16 | o2 << 8 | o3;
		h1 	= bits >> 18 & 0x3f;
		h2 	= bits >> 12 & 0x3f;
		h3 	= bits >> 6 & 0x3f;
		h4 	= bits & 0x3f;
		tmp_arr[ac++]	= b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    }while(i < data.length);
    enc 	= tmp_arr.join('');
    var r 	= data.length % 3;
    return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
}
if(!Array.prototype.map){
    Array.prototype.map     = function(iterator,scope){
        iterator            = iterator || new Function("n","i","l","return n;");
        var len             = this.length >>> 0;
        var result          = Array(len);
        for(var i=0;i<len;i++){
            if(i in this)
                result[i]   = iterator.apply(scope||this,[this[i],i,len]);
        }
        return result;
    };
}
if(!Array.prototype.filter){
    Array.prototype.filter  = function (iterator,scope){
        iterator            = iterator || new Function("n","i","l","return true;");
        var result          = [];
        var len             = this.length >> 0;
        for(var i=0;i<len;i++){
            if(iterator.apply(scope||this,[this[i],i,len]))
                result.push(this[i]);
        }
        return result;
    };
}
if(!Array.prototype.indexOf){
	Array.prototype.indexOf	= function(iterator){
		var _re			= new RegExp(","+iterator+",","ig");
		return ((","+this.toString()+",").replace(_re,"|").replace(/[^,|]/g,"")).indexOf("|");
	}
}
