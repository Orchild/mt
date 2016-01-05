<?php
namespace Tools\Service;
class MenuService{
    private $CACHE_PATH = '';
    private $ADMIN_PATH = '';
    private $CACHE_KEY  = 'menu';
    private $_BUFFER    = null;
    public function __construct(){
        $this->CACHE_PATH   = 'Tools/Conf/';
        $this->ADMIN_PATH   = 'Admin/Conf/';
        $this->_BUFFER      = $this->read();
    }
    /**
     * 写出菜单配置
     * @param  array  $menu 菜单数组
     * @return boolean
     */
    private function write(){
        return config($this->CACHE_KEY,$this->_BUFFER,$this->CACHE_PATH);
    }
    /**
     * 读取菜单配置
     * @return array
     */
    private function read(){
        return config($this->CACHE_KEY,'',$this->CACHE_PATH);
    }
    /**
     * 添加菜单项
     * @param array $menu 菜单配置
     * @return boolean
     */
    public function add(array $menu,$index = 0){
        $_menu          = &$this->_BUFFER[$index];
        $menu['status'] = 0;
        // $_menu[]        = $menu;
        array_unshift($_menu,$menu);
        $this->write();
        return true;
    }
    /**
     * 更新菜单项
     * @param  array  $menu 菜单配置
     * @param  int    $idx  菜单位置索引
     * @return boolean
     */
    public function update(array $menu,int $idx,$index = 0){
        if($idx<0) return false;
        $_menu          = &$this->_BUFFER[$index];
        $_menu[$idx]    = array_merge($_menu[$idx],$menu);
        $this->write();
        return true;
    }
    /**
     * 删除菜单项
     * @param  int    $idx 菜单位置索引
     * @return boolean
     */
    public function delete(int $idx,$index = 0){
        if($idx<0) return false;
        $_menu          = &$this->_BUFFER[$index];
        array_splice($_menu,$idx,1);
        $this->write();
        return true;
    }
    /**
     * 查找菜单项
     * @param  int    $idx 菜单位置索引
     * @return array
     */
    public function find(int $idx,$index = 0){
        if($idx<0) return false;
        $_menu          = $this->_BUFFER[$index];
        return $_menu[$idx];
    }
    /**
     * 查找菜单列表
     * @param  function $fn 过滤函数
     * @return array
     */
    private function orderby($index = 0){
        $_menu          = &$this->_BUFFER[$index];
        $keys1          = array('name','module','status','controller','items');
        $keys2          = array('name','uri','status');
        foreach ($_menu as &$value){
            extract($value);
            $value      = compact($keys1);
            foreach ($value['items'] as &$v){
                extract($v);
                $v      = compact($keys2);
            }
        }
        $this->write();
    }
    public function query($index = 0){
        $_menu          = $this->_BUFFER[$index];
        return $_menu;
    }
    /**
     * 菜单项向上移一位
     * @param  int    $idx 菜单位置索引
     * @return boolean
     */
    public function up(int $idx,$index = 0){
        if($idx<0) return false;
        $_menu          = &$this->_BUFFER[$index];
        $_menu          = array_move($_menu,$idx,-1);
        $this->write();
        return true;
    }
    /**
     * 菜单项向下移一位
     * @param  int    $idx 菜单位置索引
     * @return boolean
     */
    public function down(int $idx,$index = 0){
        if($idx<0) return false;
        $_menu          = &$this->_BUFFER[$index];
        $_menu          = array_move($_menu,$idx);
        $this->write();
        return false;
    }
    /**
     * 删除生成的菜单
     * @return boolean
     */
    public function removeAll(){
        return config($this->CACHE_KEY,null,$this->ADMIN_PATH);
    }
    /**
     * 生成菜单
     * @return boolean
     */
    public function build(){
        $module         = array('Admin');
        $map            = array();
        $map['status']  = array('&',1);
        $_menu          = $this->query(0);
        $result         = array();
        foreach ($_menu as $value){
            if(!in_array($value['module'],$module))
                continue;
            if(!($value['status'] & 1))
                continue;
            unset($value['status']);
            $value['url']   = U($value['module'].'/'.$value['controller'][0].'/index');
            foreach($value['items'] as $k => &$v) {
                if(!($v['status'] & 1)){
                    unset($value['items'][$k]);
                    continue;
                }
                $v['url']   = U($value['module'].'/'.$v['url']);
                unset($v['status']);
            }
            $result[]       = array_move_item($value,'url','UP',2);
        }
        return config($this->CACHE_KEY,$result,$this->ADMIN_PATH);
    }
}