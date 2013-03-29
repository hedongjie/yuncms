<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
/**
 * 会员组管理
 * @author		YUNCMS Dev Team
 * @copyright	Copyright (c) 2008 - 2011, NewsTeng, Inc.
 * @license	http://www.yuncms.net/about/license
 * @link		http://www.yuncms.net
 * $Id: Member_groupController.php 74 2012-11-05 13:01:18Z xutongle $
 */
class Member_groupController extends admin {

    private $db;

    public function __construct() {
        parent::__construct ();
        $this->db = Loader::model ( 'member_group_model' );
    }

    /**
     * 会员组首页
     */
    function init() {
        $page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
        $member_group_list = $this->db->order('sort ASC')->listinfo ($page, 15 );
        $pages = $this->db->pages;
        $big_menu = big_menu ( '?app=member&controller=member_group&action=add', 'add', L ( 'member_group_add' ), 700, 500 );
        include $this->admin_tpl ( 'member_group_list' );
    }

    /**
     * 添加会员组
     */
    function add() {
        if (isset ( $_POST ['dosubmit'] )) {
            $info = array ();
            if (! $this->_checkname ( $_POST ['info'] ['name'] )) {
                showmessage ( '会员组名称已经存在' );
            }
            $info = $_POST ['info'];
            $info ['allowpost'] = isset ( $info ['allowpost'] ) ? 1 : 0;
            $info ['allowupgrade'] = isset ( $info ['allowupgrade'] ) ? 1 : 0;
            $info ['allowpostverify'] = isset ( $info ['allowpostverify'] ) ? 1 : 0;
            $info ['allowsendmessage'] = isset ( $info ['allowsendmessage'] ) ? 1 : 0;
            $info ['allowattachment'] = isset ( $info ['allowattachment'] ) ? 1 : 0;
            $info ['allowsearch'] = isset ( $info ['allowsearch'] ) ? 1 : 0;
            $info ['allowvisit'] = isset ( $info ['allowvisit'] ) ? 1 : 0;

            $this->db->insert ( $info );
            if ($this->db->insert_id ()) {
                $this->_updatecache ();
                showmessage ( L ( 'operation_success' ), '?app=member&controller=member_group&action=init', '', 'add' );
            }
        } else {
            $show_header = $show_scroll = $show_validator = true;
            include $this->admin_tpl ( 'member_group_add' );
        }

    }

    /**
     * 修改会员组
     */
    function edit() {
        if (isset ( $_POST ['dosubmit'] )) {
            $info = array ();
            $info = $_POST ['info'];
            $info ['allowpost'] = isset ( $info ['allowpost'] ) ? 1 : 0;
            $info ['allowupgrade'] = isset ( $info ['allowupgrade'] ) ? 1 : 0;
            $info ['allowpostverify'] = isset ( $info ['allowpostverify'] ) ? 1 : 0;
            $info ['allowsendmessage'] = isset ( $info ['allowsendmessage'] ) ? 1 : 0;
            $info ['allowattachment'] = isset ( $info ['allowattachment'] ) ? 1 : 0;
            $info ['allowsearch'] = isset ( $info ['allowsearch'] ) ? 1 : 0;
            $info ['allowvisit'] = isset ( $info ['allowvisit'] ) ? 1 : 0;
            $this->db->where(array ('groupid' => $_POST ['groupid'] ))->update ( $info );
            $this->_updatecache ();
            showmessage ( L ( 'operation_success' ), '?app=member&controller=member_group&action=init', '', 'edit' );
        } else {
            $show_header = $show_scroll = $show_validator = true;
            $groupid = isset ( $_GET ['groupid'] ) ? $_GET ['groupid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            $groupinfo = $this->db->get_one ( array ('groupid' => $groupid ) );
            include $this->admin_tpl ( 'member_group_edit' );
        }
    }

    /**
     * 排序会员组
     */
    function sort() {
        if (isset ( $_POST ['sort'] )) {
            foreach ( $_POST ['sort'] as $k => $v ) {
                $this->db->where(array ('groupid' => $k ))->update ( array ('sort' => $v ) );
            }
            $this->_updatecache ();
            showmessage ( L ( 'operation_success' ), HTTP_REFERER );
        } else {
            showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
        }
    }
    /**
     * 删除会员组
     */
    function delete() {
        $groupidarr = isset ( $_POST ['groupid'] ) ? $_POST ['groupid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
        $where = to_sqls ( $groupidarr, '', 'groupid' );
        if ($this->db->delete ( $where )) {
            $this->_updatecache ();
            showmessage ( L ( 'operation_success' ), HTTP_REFERER );
        } else {
            showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
        }
    }

    /**
     * 检查用户名是否合法
     *
     * @param string $name
     */
    private function _checkname($name = NULL) {
        if (empty ( $name ))
            return false;
        if ($this->db->where ( array ('name' => $name ) )->field('groupid')->find()) {
            return false;
        }
        return true;
    }

    /**
     * 更新会员组列表缓存
     */
    private function _updatecache() {
        $grouplist = $this->db->key('groupid')->listinfo ();
        S ( 'member/grouplist', $grouplist );
    }

    public function public_checkname_ajax() {
        $name = isset ( $_GET ['name'] ) && trim ( $_GET ['name'] ) ? trim ( $_GET ['name'] ) : exit ( '0' );
        $name = iconv ( 'utf-8', CHARSET, $name );
        if ($this->db->where ( array ('name' => $name ) )->field('groupid')->find()) {
            exit ( '0' );
        } else {
            exit ( '1' );
        }
    }

}