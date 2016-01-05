<?php
/**
 * @todo  记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的模型名
 * @param int $record_id 触发行为的记录id
 * @param int $user_id 执行行为的用户id
 * @return boolean
 * @author huajie <banhuajie@163.com>
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null){
	//参数检查
	if(empty($action) || empty($model) || empty($record_id)){
		return '参数不能为空';
	}
	if(empty($user_id)){
		$user_id = is_login();
	}

	//查询行为,判断是否执行
	$action_info = M('Action')->getByName($action);
	if($action_info['status'] != 1){
		return '该行为被禁用或删除';
	}

	//插入行为日志
	$data['action_id']      =   $action_info['id'];
	$data['user_id']        =   $user_id;
	$data['action_ip']      =   ip2long(get_client_ip());
	$data['model']          =   $model;
	$data['record_id']      =   $record_id;
	$data['create_time']    =   NOW_TIME;
	//解析日志规则,生成日志备注
	if(!empty($action_info['log'])){
		if(preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)){
			$log['user']    =   $user_id;
			$log['record']  =   $record_id;
			$log['model']   =   $model;
			$log['time']    =   NOW_TIME;
			$log['data']    =   array('user'=>$user_id,'model'=>$model,'record'=>$record_id,'time'=>NOW_TIME);
			foreach ($match[1] as $value){
				$param = explode('|', $value);
				if(isset($param[1])){
					$replace[] = call_user_func($param[1],$log[$param[0]]);
				}else{
					$replace[] = $log[$param[0]];
				}
			}
			$data['remark'] =   str_replace($match[0], $replace, $action_info['log']);
		}else{
			$data['remark'] =   $action_info['log'];
		}
	}else{
		//未定义日志规则，记录操作url
		$data['remark']     =   '操作url：'.$_SERVER['REQUEST_URI'];
	}

	M('ActionLog')->add($data);

}

/**
 * 根据用户ID获取用户昵称
 * @param  integer $uid 用户ID
 * @return string       用户昵称
 */
function get_nickname($uid = 0){
	static $list;
	if(!($uid && is_numeric($uid))){ //获取当前登录用户名
		return session('user_auth.username');
	}
	/* 获取缓存数据 */
	if(empty($list)){
		$list = S('sys_user_nickname_list');
	}
	
	/* 查找用户信息 */
	$key = "u{$uid}";
	if(isset($list[$key])){ //已缓存，直接使用
		$name = $list[$key];
	} else { //调用接口获取用户信息
		$info = M('Admin')->field('username')->find($uid);
		if($info !== false && $info['username'] ){
			$nickname = $info['username'];
			$name = $list[$key] = $nickname;
			/* 缓存用户 */
			$count = count($list);
			$max   = C('USER_MAX_CACHE');
			while ($count-- > $max) {
				array_shift($list);
			}
			S('sys_user_nickname_list', $list);
		} else {
			$name = '';
		}
	}
	return $name;
}

/**
 * select返回的数组进行整数映射转换
 *
 * @param array $map  映射关系二维数组  array(
 *                                          '字段名1'=>array(映射关系数组),
 *                                          '字段名2'=>array(映射关系数组),
 *                                           ......
 *                                       )
 * @author 朱亚杰 <zhuyajie@topthink.net>
 * @return array
 *
 *  array(
 *      array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *      ....
 *  )
 *
 */
function int_to_string(&$data,$map=array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用',2=>'未审核',3=>'草稿'))) {
	if($data === false || $data === null ){
		return $data;
	}
	$data = (array)$data;
	foreach ($data as $key => $row){
		foreach ($map as $col=>$pair){
			if(isset($row[$col]) && isset($pair[$row[$col]])){
				$data[$key][$col.'_text'] = $pair[$row[$col]];
			}
		}
	}
	return $data;
}

/**
 * 根据条件字段获取数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 * @author huajie <banhuajie@163.com>
 */
function get_document_field($value = null, $condition = 'id', $field = null){
	if(empty($value)){
		return false;
	}

	//拼接参数
	$map[$condition] = $value;
	$info = M('Model')->where($map);
	if(empty($field)){
		$info = $info->field(true)->find();
	}else{
		$info = $info->getField($field);
	}
	return $info;
}

/**
 * 获取行为数据
 * @param string $id 行为id
 * @param string $field 需要获取的字段
 * @author huajie <banhuajie@163.com>
 */
function get_action($id = null, $field = null){
	if(empty($id) && !is_numeric($id)){
		return false;
	}
	$list = S('action_list');
	if(empty($list[$id])){
		$map = array('status'=>array('gt', -1), 'id'=>$id);
		$list[$id] = M('Action')->where($map)->field(true)->find();
	}
	return empty($field) ? $list[$id] : $list[$id][$field];
}

function IFF($exp,$val1,$val2=''){
    return !empty($exp)?$val1:$val2;
}

function IIF($exp,$val){
    return IFF($exp,$exp,$val);
}

function show($var) {
    header('Content-Type: text/html; charset=utf-8');
    if (is_bool($var)) {
        var_dump($var);
    } elseif (is_null($var)) {
        var_dump(NULL);
    } elseif(is_array($var)) {
        $val = print_r($var, true);
        echo "<pre style='padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;'>" . $val . "</pre>";
    }elseif(is_string($var)){
        echo "<pre style='padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;'>" . $var . "</pre>";
    }
}

function array_encode_format($value){
    return sprintf('%s',new \Common\Db\ArrayRender($value));
}
function dump_array_format($value) {
    return show(array_encode_format($value));
}
function json_encode_format($value){
    return sprintf('%s',new \Common\Db\jsonRender($value));
}
function dump_json_format($value) {
    show(json_encode_format($value));
}
function Qrcode($data,$size = 4){
    import('Common.Util.QRcode');
    \QRcode::png($data, false, 'H', $size, 2);
}
/**
 +----------------------------------------------------------
 * 将一个字符串部分字符用*替代隐藏
 +----------------------------------------------------------
 * @param string    $string   待转换的字符串
 * @param int       $bengin   起始位置，从0开始计数，当$type=4时，表示左侧保留长度
 * @param int       $len      需要转换成*的字符个数，当$type=4时，表示右侧保留长度
 * @param int       $type     转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
 * @param string    $glue     分割符
 +----------------------------------------------------------
 * @return string   处理后的字符串
 +----------------------------------------------------------
 */
function hideStr($string, $bengin=0, $len = 4, $type = 0, $glue = "@") {
	if (empty($string))
		return false;
		$array = array();
		if ($type == 0 || $type == 1 || $type == 4) {
			$strlen = $length = mb_strlen($string);
			while ($strlen) {
				$array[] = mb_substr($string, 0, 1, "utf8");
				$string = mb_substr($string, 1, $strlen, "utf8");
				$strlen = mb_strlen($string);
			}
		}
		if ($type == 0) {
			for ($i = $bengin; $i < ($bengin + $len); $i++) {
				if (isset($array[$i]))
					$array[$i] = "*";
			}
			$string = implode("", $array);
		}else if ($type == 1) {
			$array = array_reverse($array);
			for ($i = $bengin; $i < ($bengin + $len); $i++) {
				if (isset($array[$i]))
					$array[$i] = "*";
			}
			$string = implode("", array_reverse($array));
		}else if ($type == 2) {
			$array = explode($glue, $string);
			$array[0] = hideStr($array[0], $bengin, $len, 1);
			$string = implode($glue, $array);
		} else if ($type == 3) {
			$array = explode($glue, $string);
			$array[1] = hideStr($array[1], $bengin, $len, 0);
			$string = implode($glue, $array);
		} else if ($type == 4) {
			$left = $bengin;
			$right = $len;
			$tem = array();
			for ($i = 0; $i < ($length - $right); $i++) {
				if (isset($array[$i]))
					$tem[] = $i >= $left ? "*" : $array[$i];
			}
			$array = array_chunk(array_reverse($array), $right);
			$array = array_reverse($array[0]);
			for ($i = 0; $i < $right; $i++) {
				$tem[] = $array[$i];
			}
			$string = implode("", $tem);
		}
		return $string;
}
/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data) {
    //数据类型检测
    if(!is_array($data)){
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}
/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login(){
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['id'] : 0;
    }
}
/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}
/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
function time_format($time = NULL,$format='Y-m-d H:i:s'){
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}
/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function bytesFormat($bytes){
    $units      = array("B","KB","MB","GB","TB","PB","EB","ZB","YB");
    $pow        = floor(log($bytes)/log(1024));
    $unit       = $units[$pow];
    $value      = round($bytes/pow(1024,$pow),2);
    return sprintf('%s %s',$value,$unit);
}
/**
 * 文件路径
 * @param  string $file 文件路径
 * @return string
 */
function uri_file($file){
    $domain = $_SERVER['HTTP_HOST'];
    if(!$domain){
        $domain = $_SERVER['SERVER_NAME'];
        $port   = $_SERVER['SERVER_PORT'];
        $port   = $port == 80 ?'':':'.$port;
        $domain = $domain.$port;
    }
    $_url   = __ROOT__.'/';
    return (is_ssl()?'https://':'http://').$domain.$_url.$file;
}
/**
 * 路由
 * @param string  $url    路由
 * @param boolean $suffix 后缀
 * @param boolean $domain 域名
 */
function URI_ROUTE($url='',$suffix=true,$domain=false){
    $_url   = '';
    if($domain === true){
        $domain = $_SERVER['HTTP_HOST'];
        $_url   = $domain;
    }
    $_url   .= __ROOT__.'/'.$url;
    if($suffix) {
        $suffix   =  $suffix===true?C('URL_HTML_SUFFIX'):$suffix;
        if($pos = strpos($suffix, '|')){
            $suffix = substr($suffix, 0, $pos);
        }
        if($suffix && '/' != substr($url,-1)){
            $_url  .=  '.'.ltrim($suffix,'.');
        }
    }
    $_url   = strtolower($_url);
    if($domain){
       return (is_ssl()?'https://':'http://').$_url;
    }
    return $_url;
}
/**
 * 路由
 * @param string  $url    路由
 * @param string  $vars   参数
 * @param boolean $suffix 后缀
 * @param boolean $domain 域名
 */
function ROUTE($url='',$vars='',$suffix=true,$domain=false){
    $_url   = '';
    if($domain === true){
        $domain = $_SERVER['HTTP_HOST'];
        $_url   = $domain;
    }
    $_url   .= __ROOT__.'/';
    $path   = split('/',$url);
    $count  = count($path);
    switch($count){
        case 1:
            array_unshift($path,CONTROLLER_NAME);
        case 2:
            array_unshift($path,MODULE_NAME);
    }
    $_url   .= join($path,'/');
    if($suffix) {
        $suffix   =  $suffix===true?C('URL_HTML_SUFFIX'):$suffix;
        if($pos = strpos($suffix, '|')){
            $suffix = substr($suffix, 0, $pos);
        }
        if($suffix && '/' != substr($url,-1)){
            $_url  .=  '.'.ltrim($suffix,'.');
        }
    }
    if(!empty($vars)) {
        $vars   = http_build_query($vars);
        $_url   .= '?'.$vars;
    }
    $_url   = strtolower($_url);
    if($domain){
       return (is_ssl()?'https://':'http://').$_url;
    }
    return $_url;
}
/**
 * 获取数据
 * @param string $name    名称
 * @param [type] $default 默认值
 */
function PUT($name,$default){
    static $_PUT            = null;
    if(strpos($name,'/')){
        list($name,$type)   = explode('/',$name,2);
    }
    if(strpos($name,'.')) {
        list($method,$name) = explode('.',$name,2);
    }
    switch(strtolower($method)){
        case 'get':
            $input          = $_GET;
            break;
        case 'post':
            $input          = $_POST;
            break;
        case 'put':
            if(is_null($_PUT))
                $_PUT       = json_decode(file_get_contents('php://input'),true);
            $input          = $_PUT;
            break;
        case 'param':
            switch(strtolower($_SERVER['REQUEST_METHOD'])){
                case 'post':
                    $input  = $_POST;
                    break;
                case 'put':
                    $input  = PUT('put.');
                    break;
                default:
                    $input  = $_GET;
            }
            break;
        default:
            $input          = $_POST;
            if(empty($input))
                $input      = PUT('put.');
            if(empty($input))
                $input      = $_GET;
    }
    if($name == ''){
        $data               = $input;
        foreach($data as &$value){
            if(is_string($value))
                $value      = trim($value);
        }
    }elseif(isset($input[$name])){
        $data               = $input[$name];
        $type               = $type?$type:gettype($default);
        switch(strtolower($type)){
            case 'd':
            case 'integer':
                $data       = (int)$data;
                break;
            case 'f':
            case 'double':
                $data       = (float)$data;
                break;
            case 'b':
            case 'boolean':
                $data       = (boolean)$data;
                break;
            case 'a':
            case 'array':
                $data       = (array)$data;
                break;
            case 's':
            case 'string':
            default:
                $data       = trim((string)$data);
                break;
        }
    }else{
        $data               = isset($default)?$default:null;
    }
    return $data;
}
if(!function_exists('array_column')){
    function array_column(array $input, $columnKey, $indexKey = null) {
        $result = array();
        if (null === $indexKey) {
            if (null === $columnKey) {
                $result = array_values($input);
            } else {
                $result = array_map(create_function('$v','return $v['.$columnKey.'];'),$input);
            }
        } else {
            if (null === $columnKey) {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row;
                }
            } else {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
        return $result;
    }
}
/**
 * 数组移动
 * @param  array   $ary   数组
 * @param  int     $index 数组索引
 * @param  integer $num   移动位数
 * @return array          返回新的数组
 */
function array_move(array $ary,int $index,$num=1){
    $val    = array_splice($ary,$index,abs($num));
    array_splice($ary,$index+$num,0,$val);
    return $ary;
}
/**
 * 数组向上或向下移动一位
 * @param  array  $ary     数组
 * @param  string $key     数组索引
 * @param  string $orderby 向上或向下移动
 * @return array           返回新的数组
 */
function array_move_item(array $ary,string $key,$orderby = 'UP',$num = 1){
    $keys       = array_keys($ary);
    $index      = array_search($key,$keys);
    $count      = count($keys);
    if(strtoupper($orderby) == 'UP' && $index > 0){
        $v      = -$num;
    }elseif(strtoupper($orderby) == 'DOWN' && $index < $count){
        $v      = $num;
    }
    if($v){
        $keys   = array_move($keys,$index,$v);
        extract($ary);
        $ary    = compact($keys);
    }
    return $ary;
}
/**
 * 安数组排序
 * @param  array $data      数组
 * @param  array $ary       排序数组
 * @param  string $field    要排序数组字段
 * @return array            返回新的数组
 */
function array_sort($data,$ary,$field = 'name'){
    $result                 = array();
    foreach ($data as $val){
        $index              = array_search($val[$field],$ary);
        $result[$index]     = $value;
    }
    ksort($result);
    return $result;
}
/**
* 对查询结果集进行排序
* @access public
* @param array $list 查询结果
* @param string $field 排序的字段名
* @param string $sortby 排序类型
* asc正向排序 desc逆向排序 nat自然排序
* @return array
*/
function list_sort_by(&$list,$field,$sortingorder='asc',$sortingtype=0){
   if(is_array($list)){
        switch($sortingorder){
             case 'asc':
                //默认，按升序排列。(A-Z)
                $order  = SORT_ASC;
                break;
            case 'desc':
                //按降序排列。(Z-A)
                $order  = SORT_DESC;
                break;
        }
        switch($sortingtype){
             case 1:
                // 将每一项按数字顺序排列。
                $type   = SORT_NUMERIC;
                break;
            case 2:
                // 将每一项按字母顺序排列。
                $type   = SORT_STRING;
                break;
            default:
                //默认。将每一项按常规顺序排列。
                $type   = SORT_REGULAR;
        }
        $sorts          = array();
        foreach($list as $v){
            $sorts[]    = $v[$field];
        }
        array_multisort($sorts,$order,$type,$list);
   }
   return false;
}
/**
 * 读写配置文件
 * @param  string $name  名称
 * @param  string $value 内容
 * @param  string $path  路径
 */
function config($name, $value='', $path='Admin/Conf/'){
    $filename       =   APP_PATH . $path . $name . '.php';
    if('' !== $value){
        if(is_null($value)){
            Think\Storage::unlink($filename);
            return true;
        }else{
            Think\Storage::put($filename,"<?php\r\nreturn ".array_encode_format($value).";");
        }
    }
    if(Think\Storage::has($filename)){
        return require($filename);
    }else{
        return false;
    }
}
/**
 * 状态
 * @return array            返回新的数组
 */
function status($status,$data,$put = 1){
    switch ($put) {
        case 1:
            $result     = array();
            foreach ($data as $key => $val){
                $v      = 1 << $key + 1;
                $result[$val]   = ($status & $v) > 0 ? true : false;
            }
            return $result;
        case 2:
            $result     = array();
            foreach ($data as $key => $val){
                $v      = 1 << $key + 1;
                $result[]   = sprintf('%08b,%d,%s,%s',$v,$v,$status & $v > 0,$val);
            }
            return $result;
        default:
            foreach ($data as $key => $val){
                if(isset($put[$val])){
                    $v      = 1 << $key + 1;
                    if(!empty($put[$val])){
                        $status |= $v;
                    }elseif($status & $v){
                        $status ^= $v;
                    }
                }
            }
            return $status;
    }
}
/**
 * 循环创建目录
 * @param  string  $dir  目录
 * @param  integer $mode
 * @return boolean
 */
function mkdirs($dir, $mode = 0777) {
    return mkdir(iconv("UTF-8","GBK",$dir),$mode,true);
}
/**
 * 删除目录
 * @param  string $dir 目录
 * @return boolean
 */
function rm_dir($dir){
    if(is_dir($dir) && !@rmdir($dir)){
        foreach(scandir($dir) as $key=>$val){
            if($val!=='.' && $val!=='..' && is_dir("$dir/$val"))
                rm_dir("$dir/$val");
            elseif($val!=='.' && $val!=='..' && is_file("$dir/$val"))
                unlink("$dir/$val");
        }
        return rmdir($dir);
    }
    return true;
}
function rmdirs($dir){
    if(is_dir($dir)){
        $objects = scandir($dir);
        foreach($objects as $object){
            if($object != "." && $object != ".."){
                if(filetype("$dir/$object") == "dir")
                    rmdirs("$dir/$object");
                else
                    unlink("$dir/$object");
            }
        }
        reset($objects);
        return @rmdir($dir);
    }
    return true;
}
function wlog($name,$data){
    if(APP_DEBUG){
        if(is_array($data)){
            $data   = json_encode($data);
        }
        file_put_contents('log.txt','['.date('Y-m-d H:i:s',NOW_TIME).']['.$name.']'.$data."\r\n",FILE_APPEND);
    }
}
/**
 * 上传图片
 */
function upload_image($dir = 'images',$key = 'image',$size = 3){
    $upload             = new \Think\Upload();
    $upload->maxSize    = $size * 1048576;
    // $upload->mimes      = array('image/jpeg','image/gif','image/png');
    $upload->exts       = array('jpg','gif','png','jpeg');
    $upload->rootPath   = './';
    $upload->savePath   = 'Uploads/';
    $upload->autoSub    = true;
    $upload->subName    = $dir;
    $info = $upload->uploadOne($_FILES[$key]);
    if(empty($info)){
        $result['success']  = false;
        $result['msg']      = '图片上传失败,错误:'.$upload->getError();
    }else{
        $result['success']  = true;
        $result['msg']      = '图片上传成功';
        $result['image']    = $info['savepath'].$info['savename'];
    }
    return $result;
}
/**
 * TODO 文件上传
 * @param  array  $options 配置
 * @return array
 */
function uploadOne($options = array()){
    $cfg        = array_merge(array(
        'dir'   => strtolower(CONTROLLER_NAME),
        'key'   => 'file',
        'exts'  => 'jpg,gif,png,jpeg',
        'size'  => 5,
        'thumb' => false,
        'save'  => false,
        'ext'   => false,
    ),$options);
    $file       = PUT('file');
    if(!empty($file) && is_file($file)){
        unlink($file);
    }
    $upload             = new \Think\Upload();
    $upload->maxSize    = $cfg['size'] * 1048576;
    // $upload->mimes      = array('image/jpeg','image/gif','image/png');
    $upload->exts       = split(',',$cfg['exts']);
    $upload->rootPath   = './';
    $upload->savePath   = 'Uploads/';
    if($cfg['save'] == 'time'){
        $upload->saveName   = 'time';
    }elseif($cfg['save']){
        $upload->saveName   = $cfg['save'];
        $upload->replace    = true;
    }
    if($cfg['ext']){
        $upload->saveExt    = $cfg['ext'];
    }
    if(empty($cfg['dir'])){
        $upload->autoSub    = flase;
        $upload->subName    = '';
    }elseif($cfg['dir'] == 'date'){
        $upload->autoSub    = true;
        $upload->subName     = array('date','Y-m-d');
    }else{
        $upload->autoSub    = true;
        $upload->subName    = $cfg['dir'];
    }
    $info = $upload->uploadOne($_FILES[$cfg['key']]);
    if(empty($info)){
        $result['success']  = false;
        $result['msg']      = '上传失败,错误:'.$upload->getError();
    }else{
        $result['success']  = true;
        $result['msg']      = '上传成功';
        $result['size']     = $info['size'];
        $result[$cfg['key']]= $info['savepath'].$info['savename'];
        if(is_array($cfg['thumb'])){
            $image          = new \Think\Image();
            $image->open($result[$cfg['key']]);
            $w              = $cfg['thumb'][0];
            $h              = $cfg['thumb'][1];
            $image->thumb($w,$h)->save($result[$cfg['key']]);
        }
    }
    return $result;
}



/**
 * 图片路径处理
 * @param string $val HTML代码
 */
function ImagePath($html){
    import('Common.Org.phpQuery.phpQuery','','.php');
    $hobj       = phpQuery::newDocumentHTML($html);
    $robj       = pq($hobj)->find('img');
    foreach($robj as $item){
        $src    = pq($item)->attr('src');
        pq($item)->attr('src','');
        // pq($item)->attr('width',getImageWidth($src));
        pq($item)->attr('dynsrc',base64_encode($src));
    }
    return pq($hobj)->html();
}

function getImageWidth($url){
    if(__ROOT__ != "" && substr($url,0,1) == "/"){
        $url    = "../$url";
    }
    if(!preg_match('/^(http|https)/',$url) && !is_file($url)){
        return 0;
    }
    $image  = new \Common\Util\GD($url);
    return $image->width();
}
function img_output(){
    preg_match("/([^_]+)_(\d+)x(\d+)/",PUT('file'),$res);
    $url        = base64_decode($res[1]);
    $w          = (int)$res[2];
    $h          = (int)$res[3];
    $path       = pathinfo($url);
    // $dir        = sprintf('Uploads/_thumb/%s/w%s',date('Y-m-d'),$w);
    $dir        = sprintf('Uploads/_thumb/w%s',$w);
    // $name       = md5($path['filename']);
    $name       = md5_file($url);
    $filename   = sprintf('%s/%s_%dx%d.%s',$dir,$name,$w,$h,$path['extension']);
    if(!is_file($filename)){
        if(!is_dir($dir)){
            @mkdir($dir,0777,true);
        }
        $image  = new \Common\Util\GD($url);
        $image->thumb($w,$h);
        // $image->text('乐乐派');
        $image->save($filename);
    }
    return $filename;
    // header("Content-Disposition: Attachment;filename=".$path['filename'].'_'.$w.'.'.$path['extension']);
    // header("Content-Type: image/$path[extension]");
    // $fp = fopen($filename,'rb');
    // fpassthru($fp);
}
function SaveRemoteImage($html){
    import('Common.Org.phpQuery.phpQuery','','.php');
    $obj        = phpQuery::newDocumentHTML($html);
    $images     = pq($hobj)->find('img');
    foreach($images as $item){
        $src    = save_image(pq($item)->attr('src'));
        if($src){
            pq($item)->attr('src',$src);
        }
    }
    return html_encode(pq($obj)->html());
}
function save_image($url){
    $root       = 'Uploads/images/';
    if(substr($url,0,15) == $root){
        return false;
    }
    if(__ROOT__ != "" && substr($url,0,1) == "/"){
        $url    = "../$url";
    }
    $path       = pathinfo($url);
    $dir        = sprintf('%s%s',$root,date('Y-m-d'));
    $filename   = sprintf('%s/%s.%s',$dir,md5($path['filename']),$path['extension']);
    if(!is_file($filename)){
        if(!is_dir($dir)){
            @mkdir($dir,0777,true);
        }
        $image  = new \Common\Util\GD($url);
        $image->save($filename);
        if(!preg_match('/^(http|https)/',$url)){
            unlink($url);
        }
    }
    return $filename;
}
function html_encode($html){
    $patterns       = array(
        '/\r\n/',
        '/\n/',
        '/\t/',
        '/\s{2,}/',
    );
    $replace        = array(
        '\r\n',
        '',
        '\t',
        '',
    );
    return preg_replace($patterns,$replace,$html);
}
/**
 * TODO 记录操作日志
 * @param string $message 操作内容：操作时间，操作人，所做动作
 * @return 以log文件返回
 */
function Alog($message){
	$destination = './action_log/'.date('y_m_d').'.log';
	$record = new Think\Log\Driver\File();
	$record->write($message,$destination);
}