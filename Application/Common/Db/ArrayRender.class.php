<?php
namespace Common\Db;
class ArrayRender{
	private $_data      = array();
	private $_result	= "";
	public function __construct($data){
        $this->_data    = $data;
        $this->_result	= $this->encode($data);
    }
    private function encode($value,$level = 1){
    	$result         = "";
    	switch (gettype($value)){
    		case 'string':
                $result = "\"$value\"";
                break;
            case 'integer':
                $result     = $value;
                break;
            case 'boolean':
                $result     = $value?"true":"false";
                break;
            case 'array':
            	$keys       = array_keys($value);
                $flag       = is_string($keys[0]);
                $res        = array();
                if($flag){
                	$max    = $this->getColumnWidth($value);
                	foreach($value as $k => $v){
                        $res[]  = $this->str_pad($level).$this->encode($k).$this->getCellWidth($k,$max,$level)."=> ".$this->encode($v,$level+1);
                    }
                    $result     = sprintf("array(\r\n%s\r\n%s)",join(",\r\n",$res),$this->str_pad($level-1));
                }else{
                	foreach($value as $k => $v){
                        $res[]  = $this->encode($v,$level+1);
                    }
                    $pad        = $this->str_pad($level);
                    $result     = sprintf("array(\r\n$pad%s\r\n".$this->str_pad($level-1).")",join(",\r\n".$pad,$res));
                }
            	break;
            default:
                return gettype($value);
    	}
    	return $result;
    }
    private function getColumnWidth($value){
        $lengths        = array(0);
        foreach($value as $k => $v){
            $k          = $this->encode($k);
            $lengths[]  = $this->strlen($k);
        }
        return max($lengths);
    }
    private function getCellWidth($string,$max,$level){
        $string         = $this->encode($string);
        $len            = $this->strlen($string);
        // $max            = $max % 8 == 0 ? ceil(($max+1)/8) : ceil($max/8);
        $max            = ceil($max/8);
        $sum            = floor($len/8);
        // return $this->str_pad($max - $sum + $this->_level - $level - 1);
        return $this->str_pad($max - $sum);
    }
    private function str_pad($pad_length){
        return str_repeat("\t", $pad_length);
    }
    private function strlen($string) {
        return (strlen($string) + mb_strlen($string,'UTF8')) / 2;
    }
    protected function __toString(){
        return $this->_result;
    }
}