<?php
namespace Api\Controller;
use Common\Controller\RestFulController;
class TextController extends RestFulController {
    private $model;
    public function _initialize(){
        $this->model         = D('Basic');
    }
    public function lists_post(){
        $result     = $this->model->field('key,name')->select();
        foreach ($result as &$value) {
        	$value['url']   = URI_ROUTE($value['key'],true,true);
        }
        $this->success('获取网址列表成功',$result);
    }
    public function details_get(){
        $key        = PUT('name');
        if(!empty($key)){
            $result = $this->model->find($key);
            $this->assign('item',$result);
            $this->display('html:text');
        }else{
            $this->error('失败');
        }
    }
    /**
     * 添加数据
     */
    public function add_POST(){
        if(C('ADD')){
            if(IS_AJAX){
                if(PUT('clear')){
                    $this->success('数据删除成功'.$this->truncate($this->model->getModelName()));
                }
                $key        = PUT('key');
                $name       = PUT('name');
                $content    = PUT('content');
                if(!$key)
                    $this->error('关键字不能为空');
                if(!$name)
                    $this->error('名称不能为空');
                $data       = array();
                $result     = array();
                $data['key']        = $key;
                $data['name']       = $name;
                $data['content']    = $content;
                if($this->model->add($data)){
                    $result['key']  = $key;
                    $result['name'] = $name;
                    $this->success('添加成功',$result);
                }else{
                    $this->error('添加失败');
                }
            }else{
                $this->lists_post();
            }
        }
    }
}