<?php
namespace Common\Db;
class jsonRender{
    private $_levels    = array();
    private $_level     = 1;
    private $_data      = array();
    private $_json      = "";
    public function __construct($data){
        $this->_data    = $data;
        $this->level($data);
        $this->_level   = max($this->_levels);
        $this->_json    = $this->encode($data);
    }
    private function encode($value,$level = 1){
        $result         = "";
        switch (gettype($value)){
            case 'string':
                $result = "\"$value\"";
                break;
            case 'double':
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
                        $res[]  = $this->str_pad($level).$this->encode($k).$this->getCellWidth($k,$max,$level).": ".$this->encode($v,$level+1);
                    }
                    $result     = sprintf("{\n%s\n%s}",join(",\n",$res),$this->str_pad($level-1));
                }else{
                    foreach($value as $k => $v){
                        $res[]  = $this->encode($v,$level);
                    }
                    $result     = sprintf("[%s]",join(",",$res));
                }
                break;
            default:
                return gettype($value);
        }
        return $result;
    }
    private function level($value,$level = 1){
        $this->_levels[]    = $level;
        if(is_array($value)){
            $keys       = array_keys($value);
            $flag       = is_string($keys[0]);
            foreach($value as $k => $v){
                $this->level($v,$flag?$level+1:$level);
            }
        }
    }
    private function str_pad($pad_length){
        return str_repeat("\t", $pad_length);
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
    private function strlen($string) {
        return (strlen($string) + mb_strlen($string,'UTF8')) / 2;
    }
    protected function __toString(){
        return $this->_json;
    }
}