<?php
namespace Api\Controller;
use Think\Controller\HproseController;
class UpdateApiController extends HproseController{
	protected $crossDomain	= true;
	protected $P3P			= true;
	protected $get			= true;
	protected $debug		= true;
	private $_code			= '4107cf680a10bbb43149b1d3a0c6a33c';
	/**
	 * 获取密匙
	 * @param  string $name
	 * @return string
	 */
	private function md5($time,$md5){
		return !empty($time) && !empty($md5) && md5($this->_code.$time) == $md5;
	}
	/**
	 * 服务器状态
	 * @return [type] [description]
	 */
	public function status(){
		return true;
	}
	/**
	 * 更新文件
	 */
	public function update($time,$md5,$path,$data){
		wlog("更新文件",$time.' '.$md5.' '.$path);
		if($this->md5($time,$md5)){
			if($data){
				$paths	= dirname($path);
				if(!is_dir($paths)){
					mkdirs($paths);
				}
				file_put_contents($path,$data);
			}
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 删除
	 */
	public function delete($time,$md5,$path){
		if($this->md5($time,$md5)){
			if($path){
				return rmdirs($path);
			}
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 查看日志
	 */
	public function log($time,$md5,$delete){
		if($this->md5($time,$md5)){
			$filename 	= 'log.txt';
			if($delete){
				return unlink($filename);
			}elseif(is_file($filename)){
				return file_get_contents($filename);
			}else{
				return true;
			}
		}else{
			return false;
		}
	}
	/**
	 * 上传文件
	 */
	public function upload($time,$md5,$path,$data,$range,$size,$chunk,$chunks){
		wlog("上传文件",$path.' '.json_encode($range).' '.$size);
		if($this->md5($time,$md5)){
			if($data){
				$paths	= dirname($path);
				if(!is_dir($paths)){
					mkdirs($paths);
				}
				// $fp = fopen($path,"wb");
				// fseek($fp,$range[0]);
				// fwrite($fp,$data,$range[1] - $range[0]);
				// fclose($fp);
			}
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 下载文件
	 */
	public function download($code,$file){
		// if($code && $code == $this->_code){
		// 	if($file){
		// 		header("Content-type:text/html;charset=utf-8");
		// 		$file_name=iconv("utf-8","gb2312",$file);
		// 		$file_path	= "Uploads/$file_name";
		// 		if(!file_exists($file_path)){
		// 			return -3;
		// 		}
		// 		$file_size	= filesize($file_path);
		// 		header("Content-type: application/octet-stream");
		// 		header("Accept-Ranges: bytes");
		// 		header("Accept-Length:".$file_size);
		// 		readfile($file_path);
		// 		return 1;
		// 	}
		// 	return -2;
		// }else{
		// 	return -1;
		// }
	}
}