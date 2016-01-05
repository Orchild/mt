<?php
namespace Admin\Controller;
use Common\Controller\ExtendController;
use Common\Util\Auth;
use Think\Hook;
class IndexController extends ExtendController{
    public function index(){
//     	wlog('session', $_SESSION);
        if(is_login()){
            $this->display();
        }else{
            $this->redirect('login');
        }
    }
    /**
     * 验证码
     */
    public function verify(){
        ob_end_clean();
        $config         = array(
            'length'    => 4,
            'useImgBg'  => false,
            'imageW'    => 120,
            'imageH'    => 32,
            'fontSize'  => 18,
            'expire'    => 180,
        );
        $verify         = new \Think\Verify($config);
        $verify->entry(1);
    }
    /**
     * 管理员登陆
     */
    public function login(){
        if(IS_POST){
            $username   = PUT('username');
            $password   = PUT('password');
            $verify     = PUT('verify');
            $result     = array();
//             if(!check_verify($verify)){
            //     $this->error('验证码输入错误！');
            // }else
            $err = Auth::login($username,$password);            
//             wlog('err', $err);
            if($err < 5){
                switch ($err) {
                    case 1:
                        $this->error('用户不存在');
                        break;
                    case 2:
                        $this->error('用户已禁用');
                        break;
                    case 3:
                        $this->error('角色已禁用');
                        break;
                    case 4:
                        $this->error('密码错误');
                        break;
                }
            }else{
            	$user = $_SESSION['user_auth'];
            	action_log('user_login','Admin',$user['id'],$user['id']);wlog('PUT', PUT());
            	$this->success('登陆成功！',U('index'));
//                 $this->redirect('Index/index','','','登陆成功！');
            }
        }else{
            layout('Common/layout');
            $this->display();
        }
    }
    /**
     * 管理员忘记密码
     */
    public function forget($email = null, $verify = null){
        if(IS_POST){
            $result     = array();
            if(!check_verify($verify)){
                $this->error('验证码输入错误！');
            }else{
                $this->success('验证码输入正确！');
            }
        }else{
            $this->display();
        }
    }
    /**
     * 管理员退出
     */
    public function logout(){
        Auth::logout();
//         $this->success('退出登陆！',U('login'));
		$this->redirect('Index/login');
    }
    /**
     * 获取管理员信息
     */
    public function info(){
        $info = Auth::getUserInfo();
        $this->ajaxReturn($info);
    }
    /**
     * 读取菜单
     */
    public function menu(){
        $menu = F('menu', '',APP_PATH. 'Admin/Conf/');
        foreach ($menu as &$value){
            $value['url']   = U($value['module'].'/'.$value['controller'][0].'/index');
            foreach ($value['items'] as &$v) {
                $v['url']   = U($value['module'].'/'.$v['url']);
            }
        }
        $this->ajaxReturn($menu);
    }
    public function downloadWord(){
        // echo COMMON_PATH . 'Org/PhpWord/Autoloader.php';exit;
        require_once COMMON_PATH . 'Org/PhpWord/Autoloader.php';

        \PhpOffice\PhpWord\Autoloader::register();
        \PhpOffice\PhpWord\Settings::loadConfig();
        echo date('H:i:s') , ' Create new PhpWord object' , EOL;
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $header         = array('size' => 16, 'bold' => true);
        $styleTable     = array('borderSize'=>6,'borderColor'=>'000000','cellMargin'=>80,'width'=>'100%');
        $styleFirstRow  = array('borderBottomSize'=>18,'borderBottomColor'=>'0000FF','bgColor'=>'66BBFF');
        $styleCell      = array('align' => 'center');
        $fontStyle      = array('bold' => true, 'align' => 'center');

        $section->addText("用户相关", $header);

        $phpWord->addTableStyle('Table',$styleTable);
        $table          = $section->addTable('Table');
        $table->addRow(30);
        $table->addCell(2000, $styleCell)->addText("字段", $fontStyle);
        $table->addCell(1000, $styleCell)->addText("类型", $fontStyle);
        $table->addCell(800, $styleCell)->addText("必选", $fontStyle);
        $table->addCell()->addText("说明", $fontStyle);

        for ($i = 1; $i <= 8; $i++) {
            $table->addRow();
            $table->addCell(2000)->addText("userTel");
            $table->addCell(1000)->addText("字符串");
            $table->addCell(800)->addText("是");
            $table->addCell()->addText(htmlspecialchars("手机号码"));
        }
        $section->addPageBreak();


        $fileName = "word报表".date("YmdHis").".docx";
        // header("Content-type: application/vnd.ms-word");
        // header("Content-Disposition:attachment;filename=".$fileName.".docx");
        // header('Cache-Control: max-age=0');
        $phpWord->save($fileName,'Word2007');
    }
}