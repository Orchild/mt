<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
use Org\Util\String;
class UserController extends ExtendController {
    protected $model;
    public function _initialize(){
        parent::_initialize();
        $this->model  = D('User');
    }
    /**
     * 主页面
     */
    public function index(){
        $this->display();
    }
    /**
     * 数据列表
     */
    public function lists(){
    	if(IS_POST){
            $username			= PUT('username');
            $map                = array();
            $map['username']	= array('like',"%$username%");
            $map['b.realname']	= array('like',"%$username%");
            $map['b.nickname']	= array('like',"%$username%");
            $map['b.id_number']	= array('like',"%$username%");
            $map['b.company']	= array('like',"%$username%");
            $map['_logic']		= 'or';
            $size  = PUT('size',10);
            $page  = PUT('p');
            $count = $this	->model
				            ->alias('a')
				            ->join('__USER_INFO__ b on a.id=b.user_id','LEFT')->where($map)
				            ->where($map)
				            ->count();
            $items = $this	->model
            	 		  	->alias('a')
            	 		  	->join('__USER_INFO__ b on a.id=b.user_id','LEFT')
            	 		  	->field('a.id,username,login_count,last_login_time,register_time,updated,status,portrait,realname,nickname,cat_id,name_cert,sex,age,birthday,id_number,email,company,work_status,alternative_phone')
            	 		  	->where($map)
            	 		  	->page($page,$size)
            	 		  	->order('id desc')
            	 		  	->select();            
            $result['status']   = true;
            $result['count']    = $count;
            $result['size']     = $size;
            $result['page']		= $page;
            $result['items']	= $items;
            $this->ajaxReturn($result);
    	}else{
        	$this->display();
    	}
    }
    /**
     * 添加数据
     */
    public function add(){
    	if(IS_POST){
            $result     = $this->__validate(PUT());
            if(!$result['success'])
                $this->error($result['msg']);
            elseif($this->__create())
                $this->success('添加成功');
            else
                $this->error('添加失败');
    	}else{
        	$this->display('edit');
    	}
    }
    /**
     * 编辑数据
     */
    public function edit(){
    	if(IS_POST){
    		$data 		= PUT();
            $result     = $this->__validate($data,'info');
            if(!$result['success'])
                $this->error($result['msg']);
            elseif($this->__update())
                $this->success('更新成功');
            else
                $this->error('更新失败');
    	}elseif(IS_GET && PUT('get.id')){
            $res    = $this->__find(PUT('get.id'),true);
            $this->ajaxReturn($res);
        }else{
        	$this->display();
    	}
    }
    /**
     * 修改状态
     */
    public function status(){
        if(IS_POST){
            if($this->__status(PUT('id'),PUT('value'),PUT('field')))
                $this->success('状态修改成功');
            else
                $this->error('状态修改失败');
        }
    }
    /**
     * 删除数据
     */
    public function delete(){
        if(IS_POST){
            $map        = array();
            $map['id']  = PUT('id');
            $result     = $this->__delete($map,true,'info.portrait');
            if($result['success']){
                $this->success('删除成功');
            }else
                $this->error('删除失败');
        }
    }
    
    /**
     * 上传图片
     */
    public function upload(){
        $this->uploadImage(array('thumb'=>array(100,100)));
    }
    /**
     * 员工分类
     */
    public function category(){
    	$result         = array();
    	$model          = D('Category');
    	$map['status']  = true;
    	$result['items']= $model->where($map)->field(array('id'=>'key','title'=>'val'))->order('sort desc,updated desc')->select();
    	$cat_none 		= array('key'=>0,'val'=>'无分类');
    	$result['items'][sizeof($result['items'])] = $cat_none;
//     	//wlog('$result[items]', $result['items']);
    	$this->ajaxReturn($result);
    }    
    /**
     * 数据导出
     */
    public function allUser(){
    	$items = $this->model
            	 		->alias('a')
            	 		->join('__USER_INFO__ b on a.id=b.user_id','LEFT')
            	 		->field('realname,age,sex,username,id_number,name_cert,email,work_status,company,alternative_phone,register_time')
            	 		->order('a.id asc')
            	 		->select();
		$filename		= urldecode('温州洞头人才员工表').'_'.date('Y-m-dhis');
		self::dataExport($items,$filename);
    }
    public function dataExport($data,$filename){
    	import("ORG.PHPExcel.PHPExcel");
		$PHPExcel		= new \PHPExcel();
		$PHPExcel->getProperties()->setCreator('Dongtou')
									 ->setLastModifiedBy('Dongtou')
									 ->setTitle('Office 2007 XLSX Document')
									 ->setSubject('Office 2007 XLSX Document')
									 ->setDescription('Document for Office 2007 XLSX, generated using PHP classes.')
									 ->setKeywords('office 2007 openxml php')
									 ->setCategory('Result file');
		$PHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A1','姓名')
		            ->setCellValue('B1','年龄')
		            ->setCellValue('C1','性别')
		            ->setCellValue('D1','电话')
		            ->setCellValue('E1','身份证号码')
		            ->setCellValue('F1','认证状态')
		            ->setCellValue('G1','邮件')
		            ->setCellValue('H1','工作状态')
		            ->setCellValue('I1','部门')
		            ->setCellValue('J1','备用电话')
		            ->setCellValue('K1','注册时间');
		$PHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
		$PHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
		$PHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$PHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
		$PHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
		$PHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
		$PHPSheet 		= $PHPExcel->getActiveSheet();
		
		
		
		$abc						= array();
		$abc						= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		foreach($data as $k=>$v){
			$v['realname'] 			= empty($v['realname'])?'':$v['realname'];
			$v['age'] 				= empty($v['age'])?'':$v['age'];
			if (empty($v['sex'])){
				$v['sex']			= '';
			}else {				
				$v['sex'] 			= $v['sex']==0?'男':'女';
			}
			(string)$v['id_number'] = empty($v['id_number'])?'':$v['id_number'];
			if (empty($v['name_cert'])){
				$v['name_cert']		= '';
			}else {				
				$v['name_cert'] 	= $v['name_cert']==0?'未认证':'已认证';
			}
			$v['email'] 			= empty($v['email'])?'':$v['email'];
			if (empty($v['work_status'])){
				$v['work_status']	= '';
			}else {				
				$v['work_status'] 	= $v['work_status']==0?'离职':'在职';
			}
			$v['company'] 			= empty($v['company'])?'':$v['company'];		
			$v['alternative_phone'] = empty($v['alternative_phone'])?'':$v['alternative_phone'];
			$v['register_time']		= date('Y-m-d H:i:s',$v['register_time']);
			$k 					   += 2; 
			$j						= 0;
			foreach ($v as $value){
				$coordinate 		= $abc[$j].$k;
				$PHPExcel->setActiveSheetIndex(0)
						->setCellValue($coordinate,$value);
				if ($abc[$j]=='E'){
					$PHPSheet->setCellValueExplicit('E'.$k,$value,\PHPExcel_Cell_DataType::TYPE_STRING);
				}
				$j++;
			}		
		}
		$PHPExcel->getActiveSheet()->setTitle('温州洞头人才员工表');
		$PHPExcel->setActiveSheetIndex(0);
		
		//生成xlsx文件
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter=\PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
		
		//生成xls文件
// 		header('Content-Type: application/vnd.ms-excel');
// 		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
// 		header('Cache-Control: max-age=0');
// 		$objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
// 		//wlog('excel', $objWriter);
		$objWriter->save('php://output');
		exit;
	}
}