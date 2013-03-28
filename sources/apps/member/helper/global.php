<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );


/**
 * 判断帐户是否被禁止
 *
 * @param string $username
 * @return boolean
 */
function check_denyusername($username) {
    $member_setting = S ( 'member/member_setting' );
    // 判断是否禁止
    $denyusername = $member_setting ['denyusername'];
    if (is_array ( $denyusername )) {
        $denyusername = implode ( "|", $denyusername );
        $pattern = '/^(' . str_replace ( array ('\\*',' ',"\|" ), array ('.*','','|' ), preg_quote ( $denyusername, '/' ) ) . ')$/i';
        if (preg_match ( $pattern, $username ))
            return false;
    }
    return true;
}

/**
 * 判断邮箱是否被禁止
 */
function check_denyemail($email) {
    $member_setting = S ( 'member/member_setting' );
    $denyemail = $member_setting ['denyemail']; // 是否禁止
    if (is_array ( $denyemail )) {
        $denyemail = implode ( "|", $denyemail );
        $pattern = '/^(' . str_replace ( array ('\\*',' ',"\|" ), array ('.*','','|' ), preg_quote ( $denyemail, '/' ) ) . ')$/i';
        if (preg_match ( $pattern, $email ))
            return false;
    }
    return true;
}

/**
 * 检查会员名称是否可用
 *
 * @param string $username
 * @return string {-1:用户名不合法，-2：包含不允许注册的词语，-3：用户名已经存在，1:用户名可用}
 */
function check_username($username) {
    if (! Validate::is_username ( $username )) // 合法性
        return - 1;
    if (! check_denyusername ( $username )) // 是否禁止
        return - 2;
    if (!Loader::model ( 'member_model' )->checkname ( $username  )) // 判断是否已经注册
        return - 3;
    if (!Loader::model ( 'member_verify_model' )->checkname ( $username )) // 判断是否待审核
        return - 3;
    if (ucenter_exists ()) {
        $rs = Loader::lib ( 'Ucenter' )->uc_checkname ( $username ); // 返回Ucenter结果
        if ($rs != 1)
            return $rs;
    }
    return 1;
}

/**
 * 检查电子邮件地址是否可用
 *
 * @param string $email
 *            邮箱
 * @param int $userid
 *            用户ID
 * @return 1:成功 -4:Email 格式有误 -5:Email 不允许注册 -6:该 Email 已经被注册
 */
function check_email($email, $userid = null) {
    if (! Validate::is_email ( $email ))
        return - 4; // 检查格式
    if (! check_denyemail ( $email ))
        return - 5; // 检查禁用
    if (! is_null ( $userid )) { // ID不为空
        $r = Loader::model ( 'member_model' )->get_one ( array ('email' => $email ) );//检查会员表是否存在
        if ($r && $r ['userid'] != $userid) return -6;
        $r = Loader::model ( 'member_verify_model' )->get_one ( array ('email' => $email ) );//检查会员表是否存在
        if ($r && $r ['userid'] != $userid) return -6;
    } else { // ID为空
        if (Loader::model ( 'member_model' )->get_one ( array ('email' => $email ) ))
            return - 6;
        if (Loader::model ( 'member_verify_model' )->get_one ( array ('email' => $email ) ))
            return - 6;
        if (ucenter_exists ()) {
            $rs =  Loader::lib ( 'Ucenter' )->uc_checkemail ( $email );
            if ($rs < 0)
                return $rs;
        }
    }
    return 1;
}