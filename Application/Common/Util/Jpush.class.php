<?php
namespace Common\Util;
// use JPush\Model as M;
// use JPush\JPushClient;
// use JPush\Exception\APIConnectionException;
// use JPush\Exception\APIRequestException;
// class Jpush{
//     public function __construct(){
//         vendor('jpush/autoload',APP_PATH.'/Common/Vendor/');
//     }
//     private function pushMessage(array $msg,$audience,$msg_type = 0){
//         $content    = $msg['content'];
//         unset($msg['content']);
//         $client     = new JPushClient(C('JPUSH.app_key'),C('JPUSH.master_secret'));
//         $client     = $client->push()->setPlatform(M\all);
//         // dump_json_format(M\tag($audience));exit;
//         $client     = $client->setAudience(M\tag($audience));
//         if($msg_type){
//             $client = $client->setNotification(M\notification($content));
//         }else{
//             $client = $client->setMessage(M\message($content, null, null, $msg));
//         }
//         // dump_json_format(M\options(time(), null, null, C('JPUSH.apns_production')?true:false, 0));exit;
//         $client     = $client->setOptions(M\options(time(), null, null, C('JPUSH.apns_production')?true:false, 0));
//         $result     = $client->printJSON()->send();
//         return $result;
//     }
//     static public function push(array $msg,$audience,$msg_type = 0){
//         $push       = new self();
//         $res        = $push->pushMessage($msg,$audience,$msg_type);
//         return $res;
//     }
//     /**
//      * 广播
//      */
//     static public function pushAll(array $msg){
//         return self::push($msg,M\all);
//     }
//     /**
//      * 标签并集
//      */
//     static public function tag(array $msg,array $audience){
//         return self::push($msg,$audience);
//     }
//     /**
//      * 标签交集
//      */
//     static public function tag_and(array $msg,array $audience){
//         return self::push($msg,M\tag_and($audience));
//     }
//     /**
//      * 别名
//      */
//     static public function alias(array $msg,array $audience){
//         return self::push($msg,M\alias($audience));
//     }
//     /**
//      * 注册ID
//      */
//     static public function registration_id(array $msg,array $audience){
//         return self::push($msg,M\registration_id($audience));
//     }
// }

class Jpush{
    private function push_curl($param,$header){
        $postUrl    = "https://api.jpush.cn/v3/push";
        // $postUrl    = "https://api.jpush.cn";
        $curlPost   = $param;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$postUrl);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data,true);
    }
    private function pushMessage(array $msg,$receiver,$msg_type = 0,$platform = 'all',$m_time = 86400){
        $content                = $msg['content'];
        unset($msg['content']);
        $extras                 = $msg;
        $app_key                = C('JPUSH.app_key');
        $master_secret          = C('JPUSH.master_secret');
        $base64                 = base64_encode("$app_key:$master_secret");
        $header                 = array("Authorization:Basic $base64","Content-Type:application/json");
        $data                   = array();
        /**
         * 推送平台设置
         * 推送到所有平台：{ "platform" : "all" }
         * 指定特定推送平台：{ "platform" : ["android","ios","winphone"] }
         */
        $data['platform']       = $platform;
        /**
         * 推送设备指定
         * 多种方式:别名、标签、注册ID、分群、广播
         * 广播:“all”
         */
        $data['audience']       = empty($receiver)?'all':$receiver;
        if($msg_type){
            $data['notification']   = array(
                //统一的模式--标准模式
//                 "alert"             => $content,
//                 //安卓自定义
//                 "android"           => array(
//                     "alert"         => $content,
//                     // "title"         => "",
//                     "builder_id"    => 1,
//                     "extras"        => $extras,
//                 ),
//                 //ios的自定义
                "ios"               => array(
                    "alert"         => $content,
                    "badge"         => "1",
                    "sound"         => "default",
                    "extras"        => $extras,
                ),
            );
        }else{
        //苹果自定义---为了弹出值方便调测
            $data['message']        = array(
                // "title"             => ,
                // "content_type"      => ,
                "msg_content"       => $content,
                "extras"            => $extras,
            );
        }
        //附加选项
        $data['options']        = array(
            "sendno"            => time(),
            "time_to_live"      => $m_time, //保存离线时间的秒数默认为一天
            "apns_production"   => C('JPUSH.apns_production'),        //指定 APNS 通知发送环境：0开发环境，1生产环境。
        );
        // dump_json_format($data);
        $param                  = json_encode($data);
        $res                    = self::push_curl($param,$header);
        $result                 = array();
        $result['msg_id']       = $res['msg_id'];
        if($res['error']){
            $result['success']  = false;
            $result['code']     = $res['error']['code'];
            $result['msg']      = $res['error']['message'];
        }else{
            $result['success']  = true;
            $result['sendno']   = $res['sendno'];
        }
        return $result;
    }
    static public function push(array $msg,$audience,$msg_type = 0,$platform = 'all'){
        $res        = self::pushMessage($msg,$audience,$msg_type,$platform);
        return $res;
    }
    /**
     * 广播
     */
    static public function pushAll(array $msg,$msg_type = 0){
        return self::push($msg,'all',$msg_type);
    }
    /**
     * 标签并集
     */
    static public function tag(array $msg,array $receiver,$msg_type = 0,$platform = 'all'){
        $map            = array();
        $map['tag']     = $receiver;
        return self::push($msg,$map,$msg_type,$platform);
    }
    /**
     * 标签交集
     */
    static public function tag_and(array $msg,array $receiver,$msg_type = 0){
        $map            = array();
        $map['tag_and'] = $receiver;
        return self::push($msg,$map,$msg_type);
    }
    /**
     * 别名
     */
    static public function alias(array $msg,array $receiver,$msg_type = 0){
        $map            = array();
        $map['alias']   = $receiver;
        return self::push($msg,$map,$msg_type);
    }
    /**
     * 注册ID
     */
    static public function registration_id(array $msg,array $receiver,$msg_type = 0){
        $map                    = array();
        $map['registration_id'] = $receiver;
        return self::push($msg,$map,$msg_type);
    }
}