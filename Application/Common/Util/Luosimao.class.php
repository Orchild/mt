<?php
namespace Common\Util;
class Luosimao{
	// 短信接口
	private $sms 	= array(
		'http://sms-api.luosimao.com/v1/send.json',
		'http://sms-api.luosimao.com/v1/status.json',
	);
	// 语音验证
	private $voice 	= array(
		'http://voice-api.luosimao.com/v1/verify.json',
		'http://voice-api.luosimao.com/v1/status.json',
	);
	private $sms_errors 	= array(
		'0'			=> '短信发送成功',
		'-10'		=> '验证信息失败',
		'-20'		=> '短信余额不足',
		'-30'		=> '短信内容为空',
		'-31'		=> '短信内容存在敏感词',
		'-32'		=> '短信内容缺少签名信息',
		'-40'		=> '错误的手机号',
		'-41'		=> '号码在黑名单中',
		'-42'		=> '验证码类短信发送频率过快',
		'-50'		=> '请求发送IP不在白名单内',
	);
	private $voice_errors 	= array(
		'0'			=> '短信发送成功',
		'-10'		=> '验证信息失败',
		'-20'		=> '余额不足',
		'-30'		=> '验证码内容为空',
		'-40'		=> '错误的手机号',
	);
	private $auth 	= '';
	public function __construct(){
		$this->auth = C('SMS.sms_key');
	}
	private function curl($url,$data){
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, FALSE);
	    curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
	    curl_setopt($ch, CURLOPT_USERPWD,$this->auth);
	    if(is_array($data)){
		    curl_setopt($ch, CURLOPT_POST, TRUE);
		    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
		}
	    $res = curl_exec($ch);
	    curl_close($ch);
	    return json_decode($res,true);
	}
	private function set_auth($type = 'sms'){
// 		if ($type=='voice'){			
// 			$this->auth = C('SMS.'.$type.'_key');
// 		}else {
// 			$this->auth = C('SMS.sms_key');
// 		}
		switch ($type){
			case 'sms':
				$this->auth = C('SMS.'.$type.'_key');
				break;
			case 'voice':
				$this->auth = C('SMS.'.$type.'_key');
				break;
			case 'sms_self':
				$this->auth = C('SMS.sms_key');
				break;
			default:
				$this->auth = C('SMS.sms_key');
		}
	}
	public function statuscurl($type = 'sms'){
		$this->set_auth($type);
		switch ($type){
			case 'sms':
				$temp 		= $this->$type;
				break;
			case 'voice':
				$temp 		= $this->$type;
				break;
			case 'sms_self':
				$temp 		= $this->sms;
				break;
			default:
				$temp 		= $this->sms;
		}
		$res 			= $this->curl($temp[1]);
		if($res['error']<0){
			$res['msg']	= $this->sms_errors[$res['error']];
		}
		return $res;
	}
	public function sendcurl($type = 'sms',$mobile,$code){
		$this->set_auth($type);
		switch ($type){
			case 'sms':
				$temp 	= $this->$type;
				$data 	= array(
					'mobile' 	=> $mobile,
					'message' 	=> preg_replace('/{code}/i',$code,C('SMS.sms_tpl')),
				);
				break;
			case 'voice':
				$temp 	= $this->$type;
				$data 	= array(
					'mobile' 	=> $mobile,
					'code' 		=> $code,
				);
				break;
			case 'sms_self':
				$temp 	= $this->sms;
				$data 	= array(
					'mobile' 	=> $mobile,
					'message' 	=> $code,
				);
				break;
			default:
				$temp 	= $this->sms;
				$data 	= array(
					'mobile' 	=> $mobile,
					'message' 	=> preg_replace('/{code}/i',$code,C('SMS.sms_tpl')),
				);
		}
		$res 			= $this->curl($temp[0],$data);//wlog('$temp', $temp[0]);
		if($res['error']<0){
			$error 		= $type.'_errors';
			$errors 	= $this->$error;
			$res['msg']	= $errors[$res['error']];
		}
		return $res;
	}
	final public function status($type){
		$that 	= new self();
		switch ($type){
			case 1:
				$type='sms';
				break;
			case 2:
				$type='voice';
				break;
			case 3:
				$type='sms_self';
				break;
			default:
				$type='sms';
		}
		return $that->statuscurl($type);
	}
	/**
	 * 验证码或推送内容发送
	 * @param int $type 验证码类型：等于2时为语音验证码
	 * @param string $mobile 接收验证码手机号
	 * @param string $code 验证码内容
	 * @return array
	 */
	final public function send($type,$mobile,$code){
		$that 	= new self();
		switch ($type){
			case 1: 
				$type='sms';
				break;
			case 2:
				$type='voice';
				break;
			case 3:
				$type='sms_self';
				break;
			default:
				$type='sms';
		}//wlog('type', $type);
		return $that->sendcurl($type,$mobile,$code);
	}
}