<?php
/**
 * 获取经纬度
 * @param  string $addr 地址
 * @return 经纬度
 */
function geocoder($addr='上海市'){
     $vars   = array(
        'ak'        => C('BAIDU.app_key'),
        'output'    => 'json',
        'address'   => $addr,
    );
    $url        = 'http://api.map.baidu.com/geocoder/v2/?'.http_build_query($vars);
    $str_json   = file_get_contents($url);
    $json       = json_decode($str_json,true);
    if($json['status']==0){
        return $json['result']['location'];
    }else{
        return false;
    }
}
/**
 * 获取表字段随机值
 * @param  string $table   表名
 * @param  string $field   字段名
 * @param  string $default 默认值
 * @return 字段值
 */
function get_table_field_rand_value($table,$field,$default=""){
    $res    = M($table)->getField($field,true);
    $val    = $res[mt_rand(0,count($res)-1)];
    switch(gettype($default)){
        case 'integer':
            $val        = (int)$val;
            break;
        case 'double':
            $val        = (float)$val;
            break;
        case 'boolean':
            $val        = (boolean)$val;
            break;
        case 'array':
            $val        = (array)$val;
            break;
        case 'string':
        default:
            $val        = trim((string)$val);
            break;
    }
    return $val?$val:$default;
}
/**
 * 获取表ID
 * @param  string $table 表名
 * @return ID
 */
function get_table_id(string $table){
    return (int)get_table_field_rand_value($table,'id',1);
}
/**
 * 获取用户ID
 */
function get_user_id(){
    return (int)get_table_id('User');
}
/**
 * 生成随机码
 * @param  integer $length 生成随机码的长度
 * @param  integer $type   生成随机码的类型
 * @return string          返回随机码
 */
function generate_code($length = 6,$type = 1){
    $characters      = "";
    if($type & 1){
        $characters .= "0123456789";
    }
    if($type & 2){
        $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }
    if($type & 4){
        $characters .= "abcdefghijklmnopqrstuvwxyz";
    }
    $code            = "";
    $len             = strlen($characters)-1;
    for($i=0; $i < $length; $i++){
        $code       .= $characters[mt_rand(0,$len)];
    }
    // $code            = str_repeat($characters,$type & 2 || $type & 4?5:$length);
    // $code            = str_shuffle($code);
    // $code            = substr($code,0,$length);
    return $code;
}
/**
 * 生成促销代码
 * @param  int      $count          生成多少个优惠码
 * @param  int      $length         生成优惠码的长度
 * @param  array    $exist_array    排除指定数组中的优惠码
 * @param  string   $prefix         指定前缀
 * @return array
 */
function generate_promotion_code($count = 10,$length = 6,$exist_array = array(),$prefix = ""){
    $promotion_codes    = array();
    for($i=0; $i < $count; $i++){
        $code   = generate_code($length,1|2);
        if(!in_array($code,$promotion_codes) && !in_array($code,$exist_array)){
            $promotion_codes[]  = $prefix.$code;
        }else{
            $i--;
        }
    }
    return $promotion_codes;
}
function generate_bonus_money($total = 100,$min = 1,$max = 10){
    $result             = array();
    for($i=1; $total > 0; $i++){
        $money          = $total>$max?mt_rand($min,$max):$total;
        $total          = round($total-$money,2);
        $result[]       = $money;
        echo '第'.$i.'个红包：'.$money.' 元，余额：'.$total.' 元 <br/>';
    }
    return $result;
}
function generate_promotion_price($total = 10,$count = 10,$min = 0.01){
    $bonus_items        = array();
    for($i=0; $i < $count - 1; $i++){
        // $safe_total     = ($total-($count-$i)*$min)/($count-$i+1);//随机安全上限
        // $money          = mt_rand($min*100,$safe_total*2*100)/100;
        $avg            = round(($total-$min)/($count-$i), 2);
        $money          = mt_rand($min,$avg*2);
        $total          = round($total-$money,2);
        $bonus_items[]  = $money;
        echo '第'.$i.'个红包：'.$money.' 元，余额：'.$total.' 元 <br/>';
    }
    $bonus_items[]      = $total;
    echo '第'.$i.'个红包：'.$total.' 元，余额：0 元 <br/>';
    echo array_sum($bonus_items);
    return $bonus_items;
}
function randBonus($bonus_total=0, $bonus_count=3, $bonus_type=1){
    $bonus_items    = array(); // 将要瓜分的结果
    $bonus_balance  = $bonus_total; // 每次分完之后的余额
    $bonus_avg      = number_format($bonus_total/$bonus_count, 2); // 平均每个红包多少钱
    $i              = 0;
    while($i<$bonus_count){
        if($i<$bonus_count-1){
            $rand           = $bonus_type?(rand(1, $bonus_balance*100-1)/100):$bonus_avg; // 根据红包类型计算当前红包的金额
            $bonus_items[]  = $rand;
            $bonus_balance  -= $rand;
        }else{
            $bonus_items[]  = $bonus_balance; // 最后一个红包直接承包最后所有的金额，保证发出的总金额正确
        }
        $i++;
    }
    return $bonus_items;
}
function sendRandBonus($total=0, $count=3, $type=1){
    if($type==1){
        $input          = range(0.01, $total, 0.01);
        dump_json_format($input);
        if($count>1){
            $rand_keys  = (array) array_rand($input,  $count-1);
            $last       = 0;
            foreach($rand_keys as $i=>$key){
                $current    = $input[$key]-$last;
                $items[]    = $current;
                $last       = $input[$key];
            }
        }
        $items[]        = $total-array_sum($items);
    }else{
        $avg            = number_format($total/$count, 2);
        $i              = 0;
        while($i<$count){
            $items[]    = $i<$count-1?$avg:($total-array_sum($items));
            $i++;
        }
    }
    return $items;
}
function create_password($pw_length = 6){
    $randpwd = '';
    for ($i = 0; $i < $pw_length; $i++){
        $randpwd .= mt_rand(1,9);
    }
    return $randpwd;
}
function make_coupon_card(){
    $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $rand = $code[rand(0,25)]
        .strtoupper(dechex(date('m')))
        .date('d').substr(time(),-5)
        .substr(microtime(),2,5)
        .sprintf('%02d',rand(0,99));
    for(
        $a = md5( $rand, true ),
        $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
        $d = '',
        $f = 0;
        $f < 8;
        $g = ord( $a[ $f ] ),
        $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
        $f++
    );
    return $d;
}
/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}
/**
 +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function rand_string($len=6,$type='',$addChars='') {
    $str ='';
    switch($type) {
        case 0:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 1:
            $chars= str_repeat('0123456789',3);
            break;
        case 2:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
            break;
        case 3:
            $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借".$addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
            break;
    }
    if($len>10 ) {//位数过长重复字符串一定次数
        $chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
    }
    if($type!=4) {
        echo $chars."<br/>";
        $chars   =   str_shuffle($chars);
        echo $chars;
        $str     =   substr($chars,0,$len);
    }else{
        // 中文随机字
        for($i=0;$i<$len;$i++){
          $str.= msubstr($chars, floor(mt_rand(0,mb_strlen($chars,'utf-8')-1)),1);
        }
    }
    return $str;
}
function curl($url,$userpwd,$postfields,$header){
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD,$userpwd);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postfields);
    if(!empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    }
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}
function isBrowser(){
    $Agent      = $_SERVER['HTTP_USER_AGENT'];
    return preg_match("/(MSIE|Opera|Firefox|Chrome|Safari)/i",$Agent);
}
/**
 * 获取访问用户的浏览器的信息
 * @return string
 */
function getBrowser(){
    $Agent      = $_SERVER['HTTP_USER_AGENT'];
    $browseragent="";   //浏览器
    $browserversion=""; //浏览器的版本
    if (ereg('MSIE ([0-9].[0-9]{1,2})',$Agent,$version)) {
        $browserversion=$version[1];
        $browseragent="Internet Explorer";
    }elseif (ereg( 'Opera/([0-9]{1,2}.[0-9]{1,2})',$Agent,$version)) {
        $browserversion=$version[1];
        $browseragent="Opera";
    }elseif (ereg( 'Firefox/([0-9.]{1,5})',$Agent,$version)) {
        $browserversion=$version[1];
        $browseragent="Firefox";
    }elseif (ereg( 'Chrome/([0-9.]{1,3})',$Agent,$version)) {
        $browserversion=$version[1];
        $browseragent="Chrome";
    }elseif (ereg( 'Safari/([0-9.]{1,3})',$Agent,$version)) {
        $browseragent="Safari";
        $browserversion="";
    }else {
        $browserversion="";
        $browseragent="Unknown";
    }
    return $browseragent." ".$browserversion;
}
/**
 * 获取访问用户的操作系统的信息
 * @return string
 */
function getOs(){
    $Agent          = $_SERVER['HTTP_USER_AGENT'];
    $browserplatform=='';
    if(eregi('win',$Agent) && strpos($Agent, '95')) {
        $browserplatform="Windows 95";
    }elseif (eregi('win 9x',$Agent) && strpos($Agent, '4.90')) {
        $browserplatform="Windows ME";
    }elseif (eregi('win',$Agent) && ereg('98',$Agent)) {
        $browserplatform="Windows 98";
    }elseif (eregi('win',$Agent) && eregi('nt 5.0',$Agent)) {
        $browserplatform="Windows 2000";
    }elseif (eregi('win',$Agent) && eregi('nt 5.1',$Agent)) {
        $browserplatform="Windows XP";
    }elseif (eregi('win',$Agent) && eregi('nt 6.0',$Agent)) {
        $browserplatform="Windows Vista";
    }elseif (eregi('win',$Agent) && eregi('nt 6.1',$Agent)) {
        $browserplatform="Windows 7";
    }elseif (eregi('win',$Agent) && ereg('32',$Agent)) {
        $browserplatform="Windows 32";
    }elseif (eregi('win',$Agent) && eregi('nt',$Agent)) {
        $browserplatform="Windows NT";
    }elseif (eregi('Mac OS',$Agent)) {
        $browserplatform="Mac OS";
    }elseif (eregi('linux',$Agent)) {
        $browserplatform="Linux";
    }elseif (eregi('unix',$Agent)) {
        $browserplatform="Unix";
    }elseif (eregi('sun',$Agent) && eregi('os',$Agent)) {
        $browserplatform="SunOS";
    }elseif (eregi('ibm',$Agent) && eregi('os',$Agent)) {
        $browserplatform="IBM OS/2";
    }elseif (eregi('Mac',$Agent) && eregi('PC',$Agent)) {
        $browserplatform="Macintosh";
    }elseif (eregi('PowerPC',$Agent)) {
        $browserplatform="PowerPC";
    }elseif (eregi('AIX',$Agent)) {
        $browserplatform="AIX";
    }elseif (eregi('HPUX',$Agent)) {
        $browserplatform="HPUX";
    }elseif (eregi('NetBSD',$Agent)) {
        $browserplatform="NetBSD";
    }elseif (eregi('BSD',$Agent)) {
        $browserplatform="BSD";
    }elseif (ereg('OSF1',$Agent)) {
        $browserplatform="OSF1";
    }elseif (ereg('IRIX',$Agent)) {
        $browserplatform="IRIX";
    }elseif (eregi('FreeBSD',$Agent)) {
        $browserplatform="FreeBSD";
    }else{
        $browserplatform = "Unknown";
    }
    return $browserplatform;
}

//地球半径 6371.137
//地球半径，平均半径为6371km
define(EARTH_RADIUS,6371);
define(EARTH_RADIAN,rad2deg(EARTH_RADIUS));
/**
 * 计算某个经纬度的周围某段距离的正方形的四个点
 * @param  float  $lng      经度
 * @param  float  $lat      纬度
 * @param  float  $distance 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
 * @return array 正方形的四个点的经纬度坐标
 */
function getAroundPoint($lng, $lat,$distance = 0.5){
    $dlng   = 2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
    $dlng   = rad2deg($dlng);
    $dlat   = $distance/EARTH_RADIUS;
    $dlat   = rad2deg($dlat);
    return array(
        'left-top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
        'right-top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
        'left-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
        'right-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
    );
}
function getAround1($latitude,$longitude,$raidus){
    // $PI         = 3.14159265;
    $PI         = M_PI;
    $degree     = (24901*1609)/360.0;
    $dpmLat     = 1/$degree;
    $radiusLat  = $dpmLat*$raidus;
    $minLat     = $latitude - $radiusLat;
    $maxLat     = $latitude + $radiusLat;
    $mpdLng     = $degree*cos($latitude * ($PI/180));
    $dpmLng     = 1 / $mpdLng;
    $radiusLng  = $dpmLng*$raidus;
    $minLng     = $longitude - $radiusLng;
    $maxLng     = $longitude + $radiusLng;
    return array(minLat=>$minLat, maxLat=>$maxLat, minLng=>$minLng, maxLng=>$maxLng);
}
/**
 * 计算两个坐标之间的距离(米)
 * @param double $lng1 起点(经度)
 * @param double $lat1 起点(纬度)
 * @param double $lng2 终点(经度)
 * @param double $lat2 终点(纬度)
 * @param string $type 米/千米/英里
 */
function GetDistanceBetween(double $lng1,double $lat1,double $lng2,double $lat2,$type = 'm'){
    //角度换算成弧度
    $radLng1    = deg2rad($lng1);
    $radLng2    = deg2rad($lng2);
    $radLat1    = deg2rad($lat1);
    $radLat2    = deg2rad($lat2);
    //计算经纬度的差值
    $a          = abs($radLng1 - $radLng2);
    $b          = abs($radLat1 - $radLat2);
    //距离计算
    $s          = 2 * asin(sqrt(pow(sin($b / 2),2) + cos($radLat1) * cos($radLat2) * pow(sin($a / 2),2)));
    $s          = $s * EARTH_RADIUS;
    if($type == 'm'){
        $s      = round($s * 1000,2);
    }elseif($type == 'miles'){
        $s      = $s * 0.621371192;
    }else{
        $s      = round($s,5);
    }
    return $s;
}