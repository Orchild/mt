<?php
namespace Common\Db;
class DBDictionary{
    public function __construct($data){
        $this->ltChar                   = '┌';
        $this->ctChar                   = '┬';
        $this->rtChar                   = '┐';
        $this->lmChar                   = '├';
        $this->cmChar                   = '┼';
        $this->rmChar                   = '┤';
        $this->lbChar                   = '└';
        $this->cbChar                   = '┴';
        $this->rbChar                   = '┘';
        $this->horizontalBorderChar     = '─';
        $this->verticalBorderChar       = '│';
        $this->borderFormat             = '%s';
        $this->paddingChar              = ' ';
        $this->padType                  = 1;
    }
    private function renderHeader(){
        return $this->renderRowSeparator($this->ltChar,$this->ctChar,$this->rtChar);
    }
    private function renderFooter(){
        return $this->renderRowSeparator($this->lbChar,$this->cbChar,$this->rbChar);
    }
    private function renderSpace($count){
        return $this->renderRowSeparator($this->verticalBorderChar,$this->verticalBorderChar,$this->verticalBorderChar,'　');
    }
    /**
     * 渲染表格行起始分割行
     * @return string
     */
    private function renderRowSeparator($left,$center,$right,$horizontal,$count){
        if(!$left)
            $left       = $this->lmChar;
        if(!$center)
            $center     = $this->cmChar;
        if(!$right)
            $right      = $this->rmChar;
        if(!$horizontal)
            $horizontal = $this->horizontalBorderChar;
        if($count<=0){
            if (0 === $count = $this->getNumberOfColumns()) {
                return;
            }
        }
        $markup         = array();
        for ($column = 0; $column < $count; $column++) {
            $markup[]   = str_repeat($horizontal,$this->getColumnWidth($column)/2);
        }
        return sprintf($this->borderFormat,$left.join($center,$markup).$right).PHP_EOL;
    }
    private function renderRowCell(array $row){
    }
    /**
     * 渲染表格行
     *
     * Example: | 9971-5-0210-0 | A Tale of Two Cities  | Charles Dickens  |
     *
     * @param  array  $row        [description]
     * @return string
     */
    private function renderRow(array $row){
        if(empty($row)) {
            return;
        }
        $output         = array();
        for ($column = 0, $count = $this->getNumberOfColumns(); $column < $count; $column++) {
            $output[]   = $this->renderCell($row, $column);
        }
        // return sprintf('%s',$this->verticalBorderChar);
        return sprintf('%s%s%1$s',$this->verticalBorderChar,join($this->verticalBorderChar,$output)).PHP_EOL;
    }
    /**
     * 带边距的渲染单元格
     * @param  array  $row    [description]
     * @param  integer $column [description]
     * @return string
     */
    private function renderCell(array $row, $column){
        $cell = isset($row[$column]) ? $row[$column] : '';
        return sprintf(
            '%s',
            $this->str_pad(
                $this->paddingChar.$cell.$this->paddingChar,
                $this->getColumnWidth($column),
                $this->paddingChar,
                $this->aligns[$column]
            )
        );
    }
    /**
     * 渲染水平列分隔符
     * @return string
     */
    private function renderColumnSeparator(){
        return(sprintf($this->borderFormat, $this->verticalBorderChar));
    }
    private function strlen($string) {
        return (strlen($string) + mb_strlen($string,'UTF8')) / 2;
    }
    private function str_pad($input , $pad_length ,$pad_string , $pad_type){
        $strlen         = $this->strlen($input);
        if($strlen < $pad_length){
            $difference = $pad_length - $strlen;
            // $difference = $pad_length + strlen($input) - $strlen;
            // $difference += $difference % 2; 
            switch($pad_type){
                case 2:
                    $left   = ceil($difference / 2);
                    $right  = $difference - $left;
                    return str_repeat($pad_string, $left) . $input . str_repeat($pad_string, $right);
                case 3:
                    return str_repeat($pad_string, $difference) . $input;
                default:
                    $difference = $pad_length + strlen($input) - $strlen;
                    return sprintf('%- '.$difference.'s',$input);
                    // return sprintf('%0'.$pad_length.'s',$input.sprintf('=%02s',);
                    // return $input .sprintf('=%02s',$strlen). str_repeat($pad_string, $difference);
            }
        }else{
            return $input;
        }
    }
    private function getNumberOfColumns() {
        if (null !== $this->numberOfColumns) {
            return $this->numberOfColumns;
        }
        $columns = array(0);
        $columns[] = count($this->headers);
        foreach ($this->rows as $row) {
            $columns[] = count($row);
        }
        return $this->numberOfColumns = max($columns);
    }
    private function getColumnWidth($column){
        if(isset($this->columnWidths[$column])){
            return $this->columnWidths[$column];
        }
        $lengths = array(0);
        $lengths[] = $this->getCellWidth($this->headers, $column);
        foreach ($this->rows as $row) {
            $lengths[] = $this->getCellWidth($row, $column);
        }
        $max    = max($lengths) + 2;
        return $this->columnWidths[$column] = $max + $max % 2;
    }
    private function getCellWidth(array $row, $column){
        if($column < 0){
            return 0;
        }
        if(isset($row[$column])){
            return $this->strlen($row[$column]);
        }
        return $this->getCellWidth($row, $column - 1);
    }
    private function cleanup(){
        $this->columnWidths = array();
        $this->numberOfColumns = null;
    }
    public function generate(){
        $result     = array();
        $output .= $this->renderHeader();
        $output .= $this->renderRow($this->headers);
        $output .= $this->renderRowSeparator();
        foreach ($this->rows as $row) {
            $result[] = $this->renderRow($row);
        }
        $output .= join($this->renderRowSeparator(),$result);
        $output .= $this->renderFooter();
        return $output;
    }
    protected function __toString(){
        return $this->generate();
    }
    private function generateTable($tableName){
        $fields     = M()->query('SHOW FULL COLUMNS FROM '.$tableName);
        foreach ($fields as $key => $value) {
            $rows[] = array(
                $value['field'],
                $value['type'],
                IFF($value["null"]=="YES","是","否"),
                IFF($value["key"]=="PRI","是"),
                $value['default'],
                IFF($value["extra"]=="auto_increment","是"),
                $value['comment'],
            );
        }
        $this->headers  = array('字段','类型','允许空值','主键','默认值','自动递增','注释');
        $this->aligns   = array(1,1,2,2,2,2,1);
        $this->rows     = $rows;
        return $this->generate();
    }
    public function generateAll($filter){
        $tables     = M()->query('SHOW TABLE STATUS;');
        $rows       = array();
        $res        = array();
        $tableses   = array();
        foreach ($tables as $key => $value){
            if(in_array($value['name'],$filter)) continue;
            $tableses[]   = $value;
        }
        foreach($tableses as $k => $value){
            $rows[] = array(
                $value['comment'],
                substr($value['name'], strlen(C('DB_PREFIX'))),
                $value['rows'],
                bytesFormat($value['data_length']),
                $value['create_time'],
            );
            $res[]  = " ".($k+1)."、".$value['comment']."(".substr($value['name'], strlen(C('DB_PREFIX'))).")";
            $res[]  = $this->generateTable($value['name']);
            $this->cleanup();
        }
        // header("Content-type:text/html;charset=utf-8");
        $this->headers      = array('注释','表名','数据量','数据大小','创建时间');
        $this->aligns       = array(1,1,2,3,2);
        $this->rows         = $rows;
        $output             .= $this->generate().PHP_EOL;
        $output             .= join(PHP_EOL,$res);
        return ($output);
    }
    static public function export($filter,$filename = 'php://output'){
        $DB     = new self();
        file_put_contents($filename,$DB->generateAll($filter));
    }
}