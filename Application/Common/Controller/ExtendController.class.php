<?php
namespace Common\Controller;
use Think\Controller;
use Common\Util\Auth;
class ExtendController extends CommonController{
	public function _initialize(){
        parent::_initialize();
        switch (Auth::checkAuth()){
            case 1:
                $this->error('没有登陆',U('Index/index'));
                break;
            case 2:
                $this->error('没有权限',U('Index/index'));
                break;
        }
        if(IS_AJAX) layout(false);
    }
    protected function __find($id,$rel){
        if($rel) $this->model->relation($rel);
        return $this->model->find($id);
    }
	/**
	 * 创建数据
	 * @param  array  $data    数据
	 * @param  array  $options 配置
	 * @return boolean
	 */
	protected function __create(array $data,$options = array()){
        if(method_exists($this,$options['before_create'])) {
            call_user_method($event,$options['before_create'],$this->model);
        }elseif(function_exists($options['before_create'])){
            call_user_func($options['before_create'],$this->model,$data);
        }
        $result     = $this->model->add($data);
        if($result){
            if(method_exists($this,$options['after_create'])) {
                call_user_method($event,$options['after_create'],$this->model);
            }elseif(function_exists($options['after_create'])){
                call_user_func($options['after_create'],$this->model,$data);
            }
            return $result;
        }
        return false;
    }
    /**
     * 更新数据
     * @param  array  $data    数据
     * @param  array  $options 配置
     * @return boolean
     */
    protected function __update(array $data,$options = array()){
        if(method_exists($this,$options['before_update'])) {
            call_user_method($event,$options['before_update'],$this->model,$data);
        }elseif(function_exists($options['before_update'])){
            call_user_func($options['before_update'],$this->model,$data);
        }
        $result     = $this->model->save($data);
        if($result){
            if(method_exists($this,$options['after_update'])) {
                call_user_method($event,$options['after_update'],$this->model,$data);
            }elseif(function_exists($options['after_update'])){
                call_user_func($options['after_update'],$this->model,$data);
            }
            return $result;
        }
        return false;
    }
    /**
     * 验证字段
     * @param  array $data 数据
     * @return array
     */
    protected function __validate($data,$relation){
        $result                 = array();
        $res                    = $this->model->create($data);
        if($res){
            $result['success']  = true;
            $result['msg']      = $this->model->getError();
            $result['data']     = $res;
            if($relation){
                $this->model->relation($relation);
                $this->model->data($data);
            }
        }else{
            $result['success']  = false;
            $result['msg']      = $this->model->getError();
        }
        return $result;
    }
    /**
     * 保存数据
     * @param  array  $data    数据
     * @param  array  $options 配置
     * @return array
     */
    protected function __save(array $data,$options = array()){
        $result             = array();
        if(IS_POST){
            $data           = $data?$data:PUT();
            $pk             = $this->model->getPk();
            if($data[$pk]){
                $value      = $this->model->find($data[$pk]);
                $action     = $value?'__update':'__create';
            }else{
                $action     = '__create';
            }
            if($this->model->create($data)){
                if(method_exists($this,$options['before_save'])) {
                    call_user_method($event,$options['before_save'],$this->model,$data);
                }elseif(function_exists($options['before_save'])){
                    call_user_func($options['before_save'],$this->model,$data);
                }
                if($options['rel']){
                    $this->model->relation($options['rel']);
                    $this->model->data($data);
                }
                if($this->$action('',$options)){
                    $result['success']  = true;
                    $result['msg']      = '数据'.($data[$pk]?'更新':'新增').'成功';
                    if(method_exists($this,$options['after_save'])) {
                        call_user_method($event,$options['after_save'],$this->model,$data);
                    }elseif(function_exists($options['after_save'])){
                        call_user_func($options['after_save'],$this->model,$data);
                    }
                }else{
                    $result['status']  = false;
                    $result['code']     = 1;
                    $result['msg']      = $this->model->getDbError();
                }
            }else{
                $result['status']  = false;
                $result['code']     = 2;
                $result['msg']      = $this->model->getError();
            }
        }else{
            $result['status']  = false;
            $result['code']     = 3;
            $result['msg']      = '表单提交错误';
        }
        return $result;
    }
    protected function __saveOne(){
        if(IS_POST){
            $data           = $data?$data:PUT();
            $pk             = $this->model->getPk();
            $update         = false;
            $flag           = false;
            if($data[$pk]){
                $value      = $this->model->find($data[$pk]);
                if($value)
                    $update = true;
            }
            if($update){
                $flag       = $this->__update($data);
            }else{
                $flag       = $this->__create($data);
            }
            if($flag){
                $this->success('更新成功');
            }else{
                $this->error('更新失败');
            }
        }
    }
    /**
     * 修改状态
     * @param  int $id       关键字段
     * @param  int $val      状态数值
     * @param  string $field 字段名
     * @return boolean
     */
    protected function __status($id,$val,$field="status"){
        $pk             = $this->model->getPk();
        if(is_array($id)){
            $where      = " WHERE $pk IN (".implode($id,',').")";
        }elseif($id>0){
            $where      = " WHERE $pk = $id";
        }
        if($this->model->execute("UPDATE __TABLE__ SET $field=IF($field&$val,$field^$val,$field|$val)$where")){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 初始化排序
     * @param  int $id       关键字段
     * @param  int $val      状态数值
     * @param  string $field 字段名
     * @return boolean
     */
    protected function __init_sort($map,$sort="id asc",$field="sort",$ppk="parent_id"){
        $pk             = $this->model->getPk();
        $this->model->order($sort);
        if(!empty($map)){
            $this->model->where($map);
        }
        $res            = $this->model->select();
        foreach ($res as $key => $value) {
            $this->model->execute("UPDATE __TABLE__ SET $field=$key+1 WHERE $pk = $value[$pk]");
            if(isset($map[$ppk])){
                $map[$ppk]   = $value[$pk];
                $this->__init_sort($map,$sort,$field,$ppk);
            }
        }
    }
    protected function __setField($id,$val,$field="sort"){
        $pk             = $this->model->getPk();
        $map            = array();
        if(is_array($id)){
            $map[$pk]   = array('in',implode($id,','));
        }elseif($id>0){
            $map[$pk]   = $id;
        }
        if($this->model->where($map)->setField($field,$val)){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 修改排序
     * @param  int $id       关键字段
     * @param  int $val      状态数值
     * @param  string $field 字段名
     * @return boolean
     */
    protected function __sort($id,$val,$field="sort"){
        $pk             = $this->model->getPk();
        if(is_array($id)){
            $where      = " WHERE $pk IN (".implode($id,',').")";
        }elseif($id>0){
            $where      = " WHERE $pk = $id";
        }
        if($this->model->execute("UPDATE __TABLE__ SET $field=$val$where")){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 删除数据
     * @param  string or array $condition 条件
     */
    protected function __delete($condition,$rel = false,$delfile = false){
        $row                    = array();
        if(!empty($condition)){
            $this->model->where($condition);
        }
        if($delfile){
            $pk                 = $this->model->getPk();
            $res                = $this->__find($condition[$pk],$rel);
            $files              = array();
            $fields             = split(',',$delfile);
            foreach($fields as $v){
                $k              = split('\.',$v);
                if(count($k)==2){
                    $node       = $res[$k[0]][$k[1]];
                }else{
                    $node       = $res[$k[0]];
                }
                $files[]        = $node;
            }
        }
        if($rel) $this->model->relation($rel);
        if($this->model->delete() == true){
            foreach($files as $file) {
                if(is_file($file)){
                    unlink($file);
                }
            }
            $row['success']     = true;
        }else{
            $row['success']     = false;
            $row['msg']         = '删除失败';
        }
        return $row;
    }
    /**
     * 分页列表
     * @param  array  $options 配置
     */
    protected function page(array $options){
        $result             = array();
        if($options['where']){
            $this->model->where($options['where']);
        }
        $result['status']   = true;
        $result['count']    = $this->model->count();
        $result['size']     = IIF($options['size'],10);
        $result['page']     = IIF($options['page'],1);
        // $result['url']      = IIF($options['url'],'#');
        if($options['rel']) $this->model->relation($options['rel']);
        if($options['alias']) $this->model->alias($options['alias']);
        if($options['field']){
            $this->model->field($options['field']);
        }
        if($options['orderby']){
            $this->model->order($options['orderby']);
        }
        if($options['where']){
            $this->model->where($options['where']);
        }
//      file_put_contents('log.txt',$this->model->_sql()."\r\n".$this->model->buildSql());
        $result['items']    = $this->model->page($result['page'],$result['size'])->select();
        if(method_exists($this,$options['after_select'])){
            $this->$options['after_select']($result['items']);
        }
        //$this->fetchSql(true)
        // echo $this->model->_sql();
        // file_put_contents('log.txt',$value."\r\n".$this->model->_sql()."\r\n".json_encode($options['where']));
        // $value  = file_get_contents('log.txt');
        //$result['count']    = sizeof($result['items']);
        $this->ajaxReturn($result);
    }
    /**
     * TODO Bootstrap分页 
     *  可以通过url参数传递where条件,例如:  index.html?name=asdfasdfasdfddds
     *  可以通过url空值排序字段和方式,例如: index.html?_field=id&_order=asc
     *  可以通过url参数r指定每页数据条数,例如: index.html?r=5
     *
     * @param sting|Model  $model   模型名或模型实例
     * @param array        $where   where查询条件(优先级: $where>$_REQUEST>模型设定)
     * @param array|string $order   排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
     *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
     *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
     *
     * @param array        $base    基本的查询条件
     * @param boolean      $field   单表模型用不到该参数,要用在多表join时为field()方法指定参数
     * @return array|false 返回数据集
     */
    protected function pagination ($model,$where=array(),$order='',$base = array('status'=>array('egt',0)),$field=true){
    	$options    =   array();
    	$REQUEST    =   (array)PUT('request.');wlog('request', $REQUEST);
    	if(is_string($model)){
    		$model  =   M($model);
    	}
    
    	$OPT        =   new \ReflectionProperty($model,'options');
    	$OPT->setAccessible(true);
    
    	$pk         =   $model->getPk();
    	if($order===null){
    		//order置空
    	}else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
    		$options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
    	}elseif( $order==='' && empty($options['order']) && !empty($pk) ){
    		$options['order'] = $pk.' desc';
    	}elseif($order){
    		$options['order'] = $order;
    	}
    	unset($REQUEST['_order'],$REQUEST['_field']);
    
    	$options['where'] = array_filter(array_merge( (array)$base, /*$REQUEST,*/ (array)$where ),function($val){
    		if($val===''||$val===null){
    			return false;
    		}else{
    			return true;
    		}
    	});
    		if( empty($options['where'])){
    			unset($options['where']);
    		}
    		$options      =   array_merge( (array)$OPT->getValue($model), $options );
    		$total        =   $model->where($options['where'])->count();
    
    		if( isset($REQUEST['r']) ){
    			$listRows = (int)$REQUEST['r'];
    		}else{
    			$listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
    		}
    		$page = new \Think\Page($total, $listRows, $REQUEST);
    		if($total>$listRows){
    			$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
    		}
    		$p =$page->show();
    		$this->assign('_page', $p? $p: '');
    		$this->assign('_total',$total);
    		$options['limit'] = $page->firstRow.','.$page->listRows;
    
    		$model->setProperty('options',$options);
    
    		return $model->field($field)->select();
    }
    protected function uploadImage($options = array()){
        if(IS_POST){
            $file   = PUT('file');
            if(!empty($file) && is_file($file)){
                unlink($file);
            }
            if(PUT('upload',false)){
                $cfg        = array_merge(array(
                    'path'  => strtolower(CONTROLLER_NAME),
                    'key'   => 'file',
                    'size'  => 3,
                    'thumb' => false,
                ),$options);
                $res    = upload_image($cfg['path'],$cfg['key'],$cfg['size']);
                if($res['success']){
                    if($cfg['thumb']){
                        $image              = new \Think\Image();
                        $image->open($res['image']);
                        $w      = $data['w'];
                        $h      = $data['h'];
                        if(is_array($cfg['thumb'])){
                            $w      = $cfg['thumb'][0];
                            $h      = $cfg['thumb'][1];
                        }
                        $image->thumb($w,$h,$image::IMAGE_THUMB_CENTER)->save($res['image']);
                    }
                    $this->success($res['image']);
                }else{
                    $this->error($res['msg']);
                }
            }
        }
    }
}