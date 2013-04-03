<?php
/**
 * 社会化登陆
 * @author Tongle Xu <xutongle@gmail.com> 2012-7-10
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: SocialController.php 205 2013-03-29 23:26:40Z 85825770@qq.com $
 */
class SocialController {
    public function __construct() {
    }

    public function go() {
        $vendor = isset($_GET['vendor']) ? trim($_GET['vendor']) : '';
        $connector = Loader::connector($vendor);
        $connector->goto_loginpage ();
    }

    /**
     * 回调
     */
    public function callback() {
        $vendor = isset($_GET['vendor']) ? trim($_GET['vendor']) : '';
        $connector = Loader::connector($vendor);
        $token = $connector->get_accesstoken ();
        $userinfo = $connector->get_userinfo ();
        //print_r($userinfo);
        //$_SESSION['social_info'] = $userinfo;
        $this->social_info = $userinfo;
        $this->bind ();
    }

    public function bind(){
        // 检查connect会员是否绑定，已绑定直接登录，未绑定提示注册/绑定页面
        $member_bind = Loader::model ( 'member_bind_model' )->get_one ( array ('connectid' => $_SESSION['social_info'] ["uid"],'vendor' => $_SESSION['social_info'] ["vendor"] ) );
        if (! empty ( $member_bind )) { // connect用户已经绑定本站用户
            $r = $this->db->get_one ( array ('userid' => $member_bind ['userid'] ) );
            // 读取本站用户信息，执行登录操作
        }
    }
}