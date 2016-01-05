<?php
namespace Tools\Controller;
use Common\Controller\ExtendController;
use Common\Db\DBDictionary;
class DbController extends ExtendController {
	private $model;
	public function _initialize(){
        parent::_initialize();
        $this->model	= D('Db','Logic');
    }
    /**
     * 菜单管理列表
     */
    public function index(){
        $items		= $this->model->getTablesInfo();
        // dump($items);
        $this->assign('items',$items);
        $this->display();
    }
    public function dictionary(){
    	$header 	= $this->getHeader();
        $items		= $this->model->getTablesInfo();
        foreach ($items as $k=>&$v) {
            $v         		= array(
                'name'      => "$v[comment]($v[name])",
                'items'     => $this->model->getColumns($v["name"]),
            );
        }
        $this->assign('items',$items);
        $this->display();
    }
    /**
     * 导出数据字典
     */
    public function exporttext(){
    	$filename 	= "音乐宝数据库字典";
    	$date		= date("Y_m_d",time());
        $filename 	.="_{$date}.txt";
        $filename 	= iconv("utf-8", "gb2312", $filename);
        header('Content-Type: application/text');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');
		// $db	= new DataDictionary();
    	// $db->export('php://output');
        DBDictionary::export(array("ds_admin","ds_admin_role","ds_admin_rule","ds_session","ds_config","ds_basic"));
    	exit;
    }
    /**
     * 导出数据字典
     */
    public function export(){
    	$filename="音乐宝数据库字典";
        $header = $this->getHeader();
        $result = array_filter(M()->query('SHOW TABLE STATUS'),create_function('$v','return in_array($v["name"],array("ds_admin","ds_admin_role","ds_admin_rule","ds_session","ds_config","ds_basic"))?false:true;'));
        foreach ($result as $k=>$v) {
            $data[]         = array(
                'name'      => "$v[comment]($v[name])",
                'items'     => M()->query("SHOW FULL COLUMNS FROM $v[name]"),
            );
        }
        $this->model->export_xls($filename,$header,$data);
    }
    private function getHeader(){
    	return array(
            array(
                'label'     => '编号',
                'name'      => '$index',
                'width'     => 5,
            ),
            array(
                'label'     => '字段名',
                'name'      => 'field',
                'width'     => 22,
            ),
            array(
                'label'     => '数据类型',
                'name'      => 'type',
                'width'     => 22,
            ),
            array(
                'label'     => '允许非空',
                'name'      => 'null',
                'width'     => 10,
                'align'     => 'center',
                'filter'    => create_function('$v','return $v=="YES"?"是":"否";'),
            ),
            array(
                'label'     => '主键',
                'name'      => 'key',
                'align'     => 'center',
                'filter'    => create_function('$v','return $v=="PRI"?"是":"";'),
            ),
            array(
                'label'     => '默认值',
                'name'      => 'default',
                'align'     => 'center',
            ),
            array(
                'label'     => '自动递增',
                'name'      => 'extra',
                'width'     => 10,
                'align'     => 'center',
                'filter'    => create_function('$v','return $v=="auto_increment"?"是":"";'),
            ),
            array(
                'label'     => '注释',
                'name'      => 'comment',
                'width'     => 40,
            ),
        );
    }
}