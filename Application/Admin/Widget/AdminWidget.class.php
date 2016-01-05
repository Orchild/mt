<?php
namespace Admin\Widget;
use Think\Controller;
use Common\Util\Auth;
class AdminWidget extends Controller {
    private function cls($tag,$default){
        $result             = array();
        if($default){
            $result[]       = $default;
        }
        if($tag['col']){
            $result[]       = "col-md-$tag[col]";
        }
        if($tag['offset']){
            $result[]       = "col-md-offset-$tag[offset]";
        }
        if($tag['class']){
            $result[]       = $tag['class'];
        }
        return count($result)>0?sprintf(' class="%s"',join(' ',$result)):'';
    }
	private function ul($items){
        $result         	= array();
        if (count($items)){        	
	        foreach($items as $key => $val) {	            
		    	$result[]   = $this->li($val);
	        }
        }
        return sprintf('<ul%s>%s</ul>',$this->cls(null,'sub-menu'),join("",$result));
    }
    private function li($value){
        if($v['active'])
            return sprintf('<li%s><a><i%s></i>%s</a></li>',$this->cls(null,$value['icon']),$this->cls(null,$value['active']),$value['name']);
        else
            return sprintf('<li%s><a href="%s"><i%s></i>%s</a></li>',$this->cls(null,$value['active']),$value['url'],$this->cls(null,$value['icon']),$value['name']);
    }
    /**
     * 后台菜单
     * @return html
     */
    public function menu(){
	    $menu 					= config('menu','','Admin/Conf/');
	    foreach ($menu as &$value){	    	
          	$value['active']   	= in_array(CONTROLLER_NAME,$value['controller'])?"active open":"";
	        $value['url']   	= U($value['module'].'/'.$value['controller'][0].'/index');
	        foreach ($value['items'] as &$v) {	        	
                $v['active']	= $v['uri'] == CONTROLLER_NAME.'/'.ACTION_NAME?"active":"";
	            $v['url']   	= U($value['module'].'/'.$v['uri']);
	        }	        
		    $submenu			= $this->ul($value['items']);
		    if (!empty($value['active'])){
		    	$value['arrow']	= "arrow open";
		    	$res[] = sprintf('<li%s><a href="javascript:;"><i%s></i><span class="title">%s</span><span%s></span></a>%s</li>',$this->cls(null,$value['active']),$this->cls(null,$value['icon']),$value['name'],$this->cls(null,$value['arrow']),$submenu);
		    }else {
		    	$value['arrow']	= "arrow";
		    	$res[] = sprintf('<li%s><a href="javascript:;"><i%s></i><span class="title">%s</span><span%s></span></a>%s</li>',$this->cls(null,$value['active']),$this->cls(null,$value['icon']),$value['name'],$this->cls(null,$value['arrow']),$submenu);	    	 
		    }
	    }
	    $result	= join(' ', $res);
        return $result;
    }
    public function getLocation(){
    	$action        = U(MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME);
    	$menu          = config('menu');
    	$result        = array();
    	foreach ($menu as $value) {
    		if(in_array(CONTROLLER_NAME,$value['controller'])){
    			$result[]	= array(
    				'name'	=> $value['name'],
    				'url'	=> U($value['module'].'/'.$value['controller'][0].'/index'),
    			);
    			foreach ($value['items'] as $v){
    				if($v['uri'] == CONTROLLER_NAME.'/'.ACTION_NAME){
    					$result[]	= array(
		    				'name'	=> $v['name'],
		    				'url'	=> U($value['module'].'/'.$v["uri"]),
		    			);
    				}
	            }
    		}
    	}
    	// dump_json_format($result);
    	$res = array();
    	$count	= count($result) - 1;
    	foreach ($result as $key => $value) {
    		if($key<$count){
    			$a 	= sprintf('<a href="%s">%s</a><i class="fa fa-angle-right"></i>',$value['url'],$value['name']);
    		}else{
    			$a 	= $value['name'];
    			$cls= ' class="active"';
    		}
    		$res[] 	= sprintf('<li%s>%s</li>',$cls,$a);
    	}
    	return join('',$res);
    }
    /**
     * 管理员登陆信息
     * @return array
     */
    public function info(){
    	$info = Auth::getUserInfo();
    	// dump_array_format($info);
    	return $info;
    }
    /**
     * 读取配置
     * @param  string $key 文件名
     * @return array
     */
    public function config($key){
    	$res 	= config($key);
    	// dump_json_format($res);
    	return $res;
    }
    /**
     * 读取文本
     * @param  string $key 关键字
     * @return array
     */
    public function basic($key){
        $row    = M('Basic')->find($key);
        return $row;
    }
}
?>