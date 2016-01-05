<?php
namespace Common\Util;
class Payment{
    /**
     * 支付宝回调
     */
    static public function alipayNotify(){
        vendor('Alipay.Notify',COMMON_PATH.'Vendor');
        $alipay_config  = C('ALIPAY');
        $AlipayNotify   = new \AlipayNotify($alipay_config);
        return $AlipayNotify->verifyNotify();
    }
}