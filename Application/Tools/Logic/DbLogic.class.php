<?php
namespace Tools\Logic;
use Think\Db;
class DbLogic{
	private function query($sql){
		$db = Db::getInstance();
		return $db->query($sql);
	}
	/**
     * 为模型创建数据表
     * 自动生成created_at、updated_at字段
     * @param  string  $tableName 数据表名称
     * @param  boolean $hasPk     是否含有主键
     * @param  string  $engine    引擎类型
     * @return boolean            是否创建成功
     */
    public function createTable($tableName,
                                $hasPk = true,
                                $engine = 'InnoDB',
                                $comment = '') {
        if (empty($tableName)) {
            return false;
        }

        $pkSql = '';
        if ($hasPk) {
            // id主键的sql
            $pkSql = "`id` int PRIMARY KEY NOT NULL "
                     . "AUTO_INCREMENT COMMENT '表主键',";
        }

        $sql = "CREATE TABLE `{$tableName}` ("
                . $pkSql
                . "`created_at` int NOT NULL COMMENT '创建时间',"
                . "`updated_at` int NOT NULL COMMENT '更新时间'"
                . ") ENGINE={$engine} CHARSET=utf8 COMMENT='{$comment}'";
        // 创建数据表
        if (false === $this->query($sql)) {
            return false;
        }

        return true;
    }
    /**
     * 删除数据表
     * @param  string  $tableName 表名
     * @return boolean
     */
    public function dropTable($tableName) {
        $sql = "DROP TABLE IF EXISTS `{$tableName}`";
        if (false === $this->query($sql)) {
            return false;
        }

        return true;
    }
    /**
     * 修改表名
     * @param  string  $tableName    需要修改的表名
     * @param  string  $newTableName 新表名
     * @return boolean
     */
    public function updateTableName($tableName, $newTableName) {
        $sql = "ALTER TABLE `{$tableName}` RENAME TO `{$newTableName}`";
        return $this->query($sql);
    }
    /**
     * 修改表注释
     * @param  string  $tableName 需要修改的表名
     * @param  string  $comment   注释
     * @return boolean
     */
    public function updateTableComment($tableName, $comment) {
        $sql = "ALTER TABLE `{$tableName}` COMMENT '{$comment}'";
        return $this->query($sql);
    }
    /**
     * 优化数据表
     * @param  string $tableName 数据表名称
     * @return boolean           是否优化成功
     */
    public function optimizeTables($tableName) {
        if (!isset($tableName)) {
            return false;
        }
        $this->query("OPTIMIZE TABLE {$tableName}");
        return true;
    }
    /**
     * 修复数据表
     * @param  string $tableName 数据表名称
     * @return boolean           是否修复成功
     */
    public function repairTables($tableName) {
        if (!isset($tableName)) {
            return false;
        }
        $this->query("REPAIR TABLE {$tableName}");
        return true;
    }
	/**
     * 得到数据表表信息
     * @param $tableName 数据表名称
     * @return array
     */
    public function getTablesInfo($tableName) {
        if (!isset($tableName)) {
            return $this->query('SHOW TABLE STATUS');
        }
        $tableInfo = $this->query("SHOW TABLE STATUS LIKE '{$tableName}'");
        return $tableInfo[0];
    }
    /**
     * 得到数据表所有字段
     * @param $tableName 数据表名称
     * @return array
     */
	public function getColumns($tableName){
        return $this->query("SHOW FULL COLUMNS FROM $tableName");
    }
    /**
     * 得到数据表的行数
     * @param  string $tableName 数据表名称
     * @return int               行数
     */
    public function getTableRows($tableName) {
        if (!isset($tableName)) {
            return 0;
        }

        $sql = "SELECT COUNT(*) FROM {$tableName}";
        $result = $this->query($sql);
        return $result[0]['COUNT(*)'];
    }
    /**
     * 数据表是否有记录
     * @param  string  $tableName
     * @return boolean
     */
    public function hasRecord($tableName) {
        $result = $this->query("SELECT COUNT(*) FROM {$tableName}");
        if ($result[0]['COUNT(*)']) {
            return true;
        }
        return false;
    }
    /**
     * 得到建表信息
     * @param  string $tableName
     * @return string
     */
    public function getCreateTableSql($tableName) {
        if (!isset($tableName) || empty($tableName)) {
        	return '';
        }
        // 设置字段名加上`
        // $this->query('SET SQL_QUOTE_SHOW_CREATE = 1');
        $createTableSql = $this->query("SHOW CREATE TABLE `{$tableName}`");
        return $createTableSql[0]['create table'] . ";";
    }
    /**
     * 得到删除数据库的sql
     * @param  string $tableName
     * @return string
     */
    private function getDropTableSql($tableName) {
        return "DROP TABLE IF EXISTS `{$tableName}`;";
    }
    /**
     * 添加字段到数据表
     * @param string $tn      数据表名称
     * @param string $cn      字段名称
     * @param string $type    字段类型
     * @param int    $length  字段长度
     * @param mixed  $value   字段默认值
     * @param string $comment 字段注释
     * @return mixed
     */
    public function addColumn($tn, $cn, $type, $length, $value, $comment) {
        // 添加字段的sql
        $sql = "ALTER TABLE `{$tn}` ADD COLUMN `{$cn}` {$type}";

        // 类型长度
        if (isset($length) && 0 != $length) {
            $sql .= "({$length}) ";
        }

        // 默认值
        if (isset($value) && '' != $value) {
            $text = array('CHAR', 'VARCHAR', 'TEXT',
                          'TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT');

            if (in_array($type, $text)) {
                // 字符默认值
                $sql .= " NOT NULL DEFAULT '{$value}' ";
            } else {
                // 数值型
                $sql .= " NOT NULL DEFAULT {$value} ";
            }
        }

        // 字段注释
        if (isset($comment) && '' != $comment) {
            $sql .= " COMMENT '{$comment}' ";
        }

        return $this->query($sql);
    }

    /**
     * 修改列基本属性
     * @param  string $tn   数据表名
     * @param  string $cn   需要修改的列名
     * @param  string $ncn  新列名
     * @param  string $type 列的类型
     * @param  int    $len  字段长度
     * @param  string $cm   字段注释
     * @return mixed
     */
    public function alterColumnAttr($tn, $cn, $ncn, $type, $len, $cm) {
        $sql = "ALTER TABLE {$tn} CHANGE COLUMN {$cn} {$ncn} {$type} ";

        if (isset($len) && 0 !== $len) {
            $sql .= "({$len}) ";
        }

        if (isset($cm)) {
            $sql .= "COMMENT '{$cm}'";
        }

        return $this->query($sql);
    }

    /**
     * 删除列默认值
     * @param  string $tn   数据表名
     * @param  string $cn   需要修改的列名
     * @return mixed
     */
    public function dropColumnDefault($tn, $cn) {
        $sql = "ALTER TABLE {$tn} ALTER COLUMN {$cn} DROP DEFAULT";

        return $this->query($sql);
    }

    /**
     * 设置列默认值
     * @param  string $tn    数据表名
     * @param  string $cn    需要修改的列名
     * @param  string $value 字段默认值
     * @return mixed
     */
    public function setColumnDefault($tn, $cn, $value) {
        $sql = "ALTER TABLE {$tn} ALTER COLUMN {$cn} set DEFAULT '{$value}'";

        return $this->query($sql);
    }

    /**
     * 修改列默认值
     * @param  string $tn    数据表名
     * @param  string $cn    需要修改的列名
     * @param  string $value 字段默认值
     * @return mixed
     */
    public function alterColumnValue($tn, $cn, $value) {
        $this->dropColumnDefault($tn, $cn);

        return $this->setColumnDefault($tn, $cn, $value);
    }

    /**
     * 从数据表中删除字段
     * @param string $tn      数据表名称
     * @param string $cn      字段名称
     * @return mixed
     */
    public function dropColumn($tn, $cn) {
        $sql = "ALTER TABLE `{$tn}` DROP `{$cn}`";

        return $this->query($sql);
    }
    /**
     * 添加索引
     * @param string $tn   数据表名称
     * @param string $cn   字段名称
     * @param string $idxn 索引名称
     * @return mixed
     */
    public function addIndex($tn, $cn, $idxn) {
        $sql = "CREATE INDEX {$idxn} ON `{$tn}`(`{$cn}`)";

        return $this->query($sql);
    }

    /**
     * 添加唯一索引
     * @param string $tn   数据表名称
     * @param string $cn   字段名称
     * @param string $idxn 索引名称
     * @return mixed
     */
    public function addUnique($tn, $cn, $idxn) {
        $sql = "CREATE UNIQUE INDEX {$idxn} ON `{$tn}`(`{$cn}`)";

        return $this->query($sql);
    }
    /**
     * 删除索引
     * @param string $tn   数据表名称
     * @param string $idxn 索引名称
     * @return mixed
     */
    public function dropIndex($tn, $idxn) {
        $sql = "DROP INDEX {$idxn} ON `{$tn}`";

        return $this->query($sql);
    }
    public function export_xls(string $fileName,array $head,array $data,$row1 = 'B'){
    	import("Common.Org.PHPExcel");
        import("Common.Org.PHPExcel.Writer.Excel5");
        import("Common.Org.PHPExcel.IOFactory.php");

        $objPHPExcel    = new \PHPExcel();
        $objProps       = $objPHPExcel->getProperties();

        $objActSheet    = $objPHPExcel->getActiveSheet();
        $objActSheet->getStyle()->getFont()->setName('微软雅黑');//设置字体
        $objActSheet->getDefaultRowDimension()->setRowHeight(25);//设置默认高度
        $row            = ord($row1);
        $row2           = chr($row+count($head)-1);
        foreach ($head as $v) {
            if($v['width']>0)
                $objActSheet->getColumnDimension(chr($row))->setWidth($v['width']);//设置列宽
            $row++;
        }
        //设置边框
        $sharedStyle1=new \PHPExcel_Style();
        $sharedStyle1->applyFromArray(array('borders'=>array('allborders'=>array('style'=>\PHPExcel_Style_Border::BORDER_THIN))));
        $column         = 2;
        foreach ($data as $idx=>$value){
            $objActSheet->setSharedStyle($sharedStyle1, "$row1{$column}:$row2{$column}");//设置边框
            $objActSheet->mergeCells("$row1{$column}:$row2{$column}");//合并单元格
            $objActSheet->getStyle("$row1{$column}:$row2{$column}")->getFont()->setSize(12);//字体
            $objActSheet->getStyle("$row1{$column}:$row2{$column}")->getFont()->setBold(true);//粗体

            //背景色填充
            $objActSheet->getStyle("$row1{$column}:$row2{$column}")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objActSheet->getStyle("$row1{$column}:$row2{$column}")->getFill()->getStartColor()->setARGB('FFB8CCE4');
            $objActSheet->getStyle("$row1{$column}:$row2{$column}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
            $objActSheet->setCellValue($row1.$column,($idx+1).". ".$value['name']);
            $column++;
            $objActSheet->setSharedStyle($sharedStyle1, "$row1{$column}:$row2{$column}");//设置边框
            $objActSheet->getStyle("$row1{$column}:$row2{$column}")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objActSheet->getStyle("$row1{$column}:$row2{$column}")->getFill()->getStartColor()->setARGB('FF4F81BD');
            $objActSheet->getStyle("$row1{$column}:$row2{$column}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
            $row            = ord($row1);
            foreach ($head as $k=>$v) {
                $row_temp   = chr($row++);
                $objActSheet->setCellValue($row_temp.$column,$v['label']);
                if($v['align']=='center')
                    $objActSheet->getStyle("$row_temp{$column}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中
            }
            foreach ($value['items'] as $index=>$val) {
                $column++;
                $objActSheet->setSharedStyle($sharedStyle1, "$row1{$column}:$row2{$column}");//设置边框
                $objActSheet->getStyle("$row1{$column}:$row2{$column}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
                $objPHPExcel->getActiveSheet()->getStyle("B{$column}:I{$column}")->getAlignment()->setWrapText(true);//换行
                //行写入
                $row            = ord($row1);
                foreach ($head as $k=>$v) {
                    $row_temp   = chr($row++);
                    $_value     = $v['name']=='$index'?$index+1:$val[$v['name']];
                    if($v['filter']){
                        $_value = $v['filter']($_value);
                    }
                    $objActSheet->setCellValue($row_temp.$column,$_value);
                    if($v['align']=='center')
                        $objActSheet->getStyle("$row_temp{$column}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中
                }
            }
            $column++;
            $column++;
        }

        $date=date("Y_m_d",time());
        $fileName.="_{$date}.xls";
        $fileName = iconv("utf-8", "gb2312", $fileName);
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
    }
}