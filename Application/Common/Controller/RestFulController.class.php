<?php
namespace Common\Controller;
use Think\Controller\RestController;
class RestFulController extends RestController{
    protected $allowType        = array('json','xml');
	protected $defaultType		= 'json';
    private $flag               = true;
    public function _initialize(){
    }
    protected function truncate($tableName){
        $sql        = 'TRUNCATE `__'.strtoupper(parse_name($tableName)).'__`';
        M()->execute($sql);
        return $sql;
    }
	/**
     * 成功信息
     * @param  string $msg 信息
     */
    protected function success($msg,$items,$key = 'result'){
        $result             = array();
        $result['code']     = 200;
        $result['msg']      = $msg;
        if(isset($items))
            $result[$key]   = $items;
        $this->_echo($result);
    }
    /**
     * 错误信息
     * @param  string $msg 信息
     */
    protected function error($msg,$items,$key = 'result'){
        $result             = array();
        $result['code']     = 201;
        $result['msg']      = $msg;
        if(isset($items))
            $result[$key]   = $items;
        $this->_echo($result);
    }
    private function _echo($result){
        // file_put_contents('log.txt',json_encode(IS_AJAX));
        // file_put_contents('log.txt',json_encode(PUT('debug')==true),FILE_APPEND);
        if($this->flag && PUT('dump') == true){
            $filename   = strtolower(CONTROLLER_NAME.ACTION_NAME);
            F($filename,json_encode_format($result));
            dump_json_format($result);exit;
        }elseif($this->flag && PUT('debug') == true){
            $this->flag = false;
            $filename   = strtolower(CONTROLLER_NAME.ACTION_NAME);
            $string     = F($filename);
            if(empty($string)){
                $string = json_encode_format($result);
            }
            $this->success('AJAX',$string);
        }else{
            $this->response($result,$this->_type);
        }
    }
}