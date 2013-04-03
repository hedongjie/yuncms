<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
/**
 * 会员审核
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Member_verifyController.php 863 2012-06-08 12:29:43Z
 *          85825770@qq.com $
 */
class Member_verifyController extends admin {
    private $db, $member_db;

    public function __construct() {
        parent::__construct ();
        $this->db = Loader::model ( 'member_verify_model' );
    }

    /**
     * defalut
     */
    public function init() {
        include $this->admin_tpl ( 'member_init' );
    }

    /**
     * member list
     */
    public  function manage() {
        $status = isset ( $_GET ['s'] ) && ! empty ( $_GET ['s'] ) ? $_GET ['s'] : 0;
        $page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
        $memberlist = $this->db->where(array ('status' => $status ))->order('regdate DESC')->listinfo ( $page, 10 );
        $pages = $this->db->pages;
        $member_model = S ( 'common/member_model' );
        include $this->admin_tpl ( 'member_verify' );
    }

    public function modelinfo() {
        $userid = ! empty ( $_GET ['userid'] ) ? intval ( $_GET ['userid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
        $modelid = ! empty ( $_GET ['modelid'] ) ? intval ( $_GET ['modelid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
        $memberinfo = $this->db->getby_userid ( $userid );
        // 模型字段名称
        $this->member_field_db = Loader::model ( 'member_model_field_model' );
        $model_fieldinfo = $this->member_field_db->where(array ('modelid' => $modelid ))->select ();
        // 用户模型字段信息
        $member_fieldinfo = string2array ( $memberinfo ['modelinfo'] );
        // 交换数组key值
        foreach ( $model_fieldinfo as $v ) {
            if (array_key_exists ( $v ['field'], $member_fieldinfo )) {
                $tmp = $member_fieldinfo [$v ['field']];
                unset ( $member_fieldinfo [$v ['field']] );
                $member_fieldinfo [$v ['name']] = $tmp;
                unset ( $tmp );
            }
        }
        include $this->admin_tpl ( 'member_verify_modelinfo' );
    }

    /**
     * pass member
     */
    function pass() {
        if (isset ( $_POST ['userid'] )) {
            $this->member_db = Loader::model ( 'member_model' );
            $uidarr = isset ( $_POST ['userid'] ) ? $_POST ['userid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            $where = to_sqls ( $uidarr, '', 'userid' );
            $userarr = $this->db->listinfo ( $where );
            $success_uids = $info = array ();
            foreach ( $userarr as $v ) {
                $info ['password'] = $v ['password'];
                $info ['encrypt'] = $v ['encrypt'];
                $info ['regdate'] = $info ['lastdate'] = $v ['regdate'];
                $info ['username'] = $v ['username'];
                $info ['nickname'] = $v ['nickname'];
                $info ['email'] = $v ['email'];
                $info ['regip'] = $v ['regip'];
                $info ['point'] = $v ['point'];
                $info ['groupid'] = $this->member_db->_get_usergroup_bypoint ( $v ['point'] );
                $info ['amount'] = $v ['amount'];
                $info ['encrypt'] = $v ['encrypt'];
                $info ['modelid'] = $v ['modelid'] ? $v ['modelid'] : 1;
                $userid = $this->member_db->register ( $info);
                if ($v ['modelinfo']) {
                    // 如果数据模型不为空
                    // 插入会员模型数据
                    $user_model_info = string2array ( $v ['modelinfo'] );
                    $user_model_info ['userid'] = $userid;
                    $this->member_db->set_model ( $info ['modelid'] );
                    $this->member_db->insert ( $user_model_info );
                }
                if ($userid) {
                    $success_uids [] = $v ['userid'];
                }
            }
            $where = to_sqls ( $success_uids, '', 'userid' );
            $this->db->update ( array ('status' => 1,'message' => $_POST ['message'] ), $where );

            $fail_uids = array_diff ( $uidarr, $success_uids );
            if (! empty ( $fail_uids )) {
                $where = to_sqls ( $fail_uids, '', 'userid' );
                $this->db->update ( array ('status' => 5,'message' => $_POST ['message'] ), $where );
            }
            // 发送 email通知
            if ($_POST ['sendemail']) {
                $memberinfo = $this->db->select ( $where );
                foreach ( $memberinfo as $v ) {
                    sendmail ( $v ['email'], L ( 'reg_pass' ), $_POST ['message'] );
                }
            }
            showmessage ( L ( 'pass' ) . L ( 'operation_success' ), HTTP_REFERER );
        } else {
            showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
        }
    }

    /**
     * delete member
     */
    function delete() {
        if (isset ( $_POST ['userid'] )) {
            $uidarr = isset ( $_POST ['userid'] ) ? $_POST ['userid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            $message = stripslashes ( $_POST ['message'] );
            $where = to_sqls ( $uidarr, '', 'userid' );
            $this->db->delete ( $where );
            showmessage ( L ( 'delete' ) . L ( 'operation_success' ), HTTP_REFERER );
        } else {
            showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
        }
    }

    /**
     * reject member
     */
    function reject() {
        if (isset ( $_POST ['userid'] )) {
            $uidarr = isset ( $_POST ['userid'] ) ? $_POST ['userid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            $where = to_sqls ( $uidarr, '', 'userid' );
            $res = $this->db->update ( array ('status' => 4,'message' => $_POST ['message'] ), $where );
            if ($res) {
                if ($_POST ['sendemail']) {
                    $memberinfo = $this->db->select ( $where );
                    foreach ( $memberinfo as $v ) {
                        sendmail ( $v ['email'], L ( 'reg_pass' ), $_POST ['message'] );
                    }
                }
            }
            showmessage ( L ( 'reject' ) . L ( 'operation_success' ), HTTP_REFERER );
        } else {
            showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
        }
    }

    /**
     * ignore member
     */
    function ignore() {
        if (isset ( $_POST ['userid'] )) {
            $uidarr = isset ( $_POST ['userid'] ) ? $_POST ['userid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            $where = to_sqls ( $uidarr, '', 'userid' );
            $res = $this->db->update ( array ('status' => 2,'message' => $_POST ['message'] ), $where );
            // 发送 email通知
            if ($res) {
                if ($_POST ['sendemail']) {
                    $memberinfo = $this->db->select ( $where );
                    foreach ( $memberinfo as $v ) {
                        sendmail ( $v ['email'], L ( 'reg_pass' ), $_POST ['message'] );
                    }
                }
            }
            showmessage ( L ( 'ignore' ) . L ( 'operation_success' ), HTTP_REFERER );
        } else {
            showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
        }
    }
}
