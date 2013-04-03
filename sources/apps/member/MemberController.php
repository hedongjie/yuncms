<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'member' . DIRECTORY_SEPARATOR );
Loader::helper ( 'member:global' );
/**
 * 会员管理
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: MemberController.php 213 2013-03-30 00:00:02Z 85825770@qq.com $
 */
class MemberController extends admin {
    private $db, $verify_db;

    public function __construct() {
        parent::__construct ();
        $this->db = Loader::model ( 'member_model' );
    }

    /**
     * 会员管理首页
     */
    public function init() {
        $show_header = $show_scroll = true;
        $this->verify_db = Loader::model ( 'member_verify_model' );
        // 搜索框
        $keyword = isset ( $_GET ['keyword'] ) ? $_GET ['keyword'] : '';
        $type = isset ( $_GET ['type'] ) ? $_GET ['type'] : '';
        $groupid = isset ( $_GET ['groupid'] ) ? $_GET ['groupid'] : '';
        $start_time = isset ( $_GET ['start_time'] ) ? $_GET ['start_time'] : date ( 'Y-m-d', TIME - date ( 't', TIME ) * 86400 );
        $end_time = isset ( $_GET ['end_time'] ) ? $_GET ['end_time'] : date ( 'Y-m-d', TIME );
        $grouplist = S ( 'member/grouplist' );
        foreach ( $grouplist as $k => $v ) {
            $grouplist [$k] = $v ['name'];
        }
        $memberinfo ['totalnum'] = $this->db->count ();
        $memberinfo ['vipnum'] = $this->db->where(array ('vip' => 1 ))->count (  );
        $memberinfo ['verifynum'] = $this->verify_db->where(array ('status' => 0 ))->count (  );
        $todaytime = strtotime ( date ( 'Y-m-d', TIME ) );
        $memberinfo ['today_member'] = $this->db->count ( "`regdate` > '$todaytime'" );
        include $this->admin_tpl ( 'member_init' );
    }

    /**
     * 会员搜索
     */
    function search() {
        // 搜索框
        $keyword = isset ( $_GET ['keyword'] ) ? $_GET ['keyword'] : '';
        $type = isset ( $_GET ['type'] ) ? $_GET ['type'] : '';
        $groupid = isset ( $_GET ['groupid'] ) ? $_GET ['groupid'] : '';
        $modelid = isset ( $_GET ['modelid'] ) ? $_GET ['modelid'] : '';
        $status = isset ( $_GET ['status'] ) ? $_GET ['status'] : '';
        $amount_from = isset ( $_GET ['amount_from'] ) ? $_GET ['amount_from'] : '';
        $amount_to = isset ( $_GET ['amount_to'] ) ? $_GET ['amount_to'] : '';
        $point_from = isset ( $_GET ['point_from'] ) ? $_GET ['point_from'] : '';
        $point_to = isset ( $_GET ['point_to'] ) ? $_GET ['point_to'] : '';
        $start_time = isset ( $_GET ['start_time'] ) ? $_GET ['start_time'] : '';
        $end_time = isset ( $_GET ['end_time'] ) ? $_GET ['end_time'] : date ( 'Y-m-d', TIME );
        $grouplist = S ( 'member/grouplist' );
        foreach ( $grouplist as $k => $v ) {
            $grouplist [$k] = $v ['name'];
        }
        // 会员所属模型
        $modellistarr = S ( 'common/member_model' );
        foreach ( $modellistarr as $k => $v ) {
            $modellist [$k] = $v ['name'];
        }

        if (isset ( $_GET ['search'] )) {
            // 默认选取一个月内的用户，防止用户量过大给数据造成灾难
            $where_start_time = strtotime ( $start_time ) ? strtotime ( $start_time ) : 0;
            $where_end_time = strtotime ( $end_time ) + 86400;
            // 开始时间大于结束时间，置换变量
            if ($where_start_time > $where_end_time) {
                list ( $where_start_time, $where_end_time ) = array ($where_end_time,$where_start_time );
            }

            $where = '';

            if ($status) {
                $islock = $status == 1 ? 1 : 0;
                $where .= "`islock` = '$islock' AND ";
            }
            if ($groupid) {
                $where .= "`groupid` = '$groupid' AND ";
            }
            if ($modelid) {
                $where .= "`modelid` = '$modelid' AND ";
            }
            $where .= "`regdate` BETWEEN '$where_start_time' AND '$where_end_time' AND ";
            // 资金范围
            if ($amount_from) {
                if ($amount_to) {
                    if ($amount_from > $amount_to) {
                        $tmp = $amount_from;
                        $amount_from = $amount_to;
                        $amount_to = $tmp;
                        unset ( $tmp );
                    }
                    $where .= "`amount` BETWEEN '$amount_from' AND '$amount_to' AND ";
                } else {
                    $where .= "`amount` > '$amount_from' AND ";
                }
            }
            // 点数范围
            if ($point_from) {
                if ($point_to) {
                    if ($point_from > $point_to) {
                        $tmp = $amount_from;
                        $point_from = $point_to;
                        $point_to = $tmp;
                        unset ( $tmp );
                    }
                    $where .= "`point` BETWEEN '$point_from' AND '$point_to' AND ";
                } else {
                    $where .= "`point` > '$point_from' AND ";
                }
            }

            if ($keyword) {
                if ($type == '1') {
                    $where .= "`username` LIKE '%$keyword%'";
                } elseif ($type == '2') {
                    $where .= "`userid` = '$keyword'";
                } elseif ($type == '3') {
                    $where .= "`email` like '%$keyword%'";
                } elseif ($type == '4') {
                    $where .= "`regip` = '$keyword'";
                } elseif ($type == '5') {
                    $where .= "`nickname` LIKE '%$keyword%'";
                } else {
                    $where .= "`username` like '%$keyword%'";
                }
            } else {
                $where .= '1';
            }

        } else {
            $where = '';
        }
        $page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
        $memberlist_arr = $this->db->listinfo ( $where, 'userid DESC', $page, 15 );
        // 查询会员头像
        $memberlist = array ();
        foreach ( $memberlist_arr as $k => $v ) {
            $memberlist [$k] = $v;
            $memberlist [$k] ['avatar'] = get_memberavatar ( $v ['userid'], 30 );
        }
        $pages = $this->db->pages;
        $big_menu = array ('?app=member&controller=member&action=manage&menuid=40',L ( 'member_research' ) );
        include $this->admin_tpl ( 'member_list' );
    }

    /**
     * 会员列表
     */
    function manage() {
        $groupid = isset ( $_GET ['groupid'] ) ? intval ( $_GET ['groupid'] ) : '';
        $page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
        $memberlist_arr = $this->db->listinfo ( '', 'userid DESC', $page, 15 );
        $pages = $this->db->pages;
        // 搜索框
        $keyword = isset ( $_GET ['keyword'] ) ? $_GET ['keyword'] : '';
        $type = isset ( $_GET ['type'] ) ? $_GET ['type'] : '';
        $start_time = isset ( $_GET ['start_time'] ) ? $_GET ['start_time'] : '';
        $end_time = isset ( $_GET ['end_time'] ) ? $_GET ['end_time'] : date ( 'Y-m-d', TIME );
        $grouplist = S ( 'member/grouplist' );
        foreach ( $grouplist as $k => $v ) {
            $grouplist [$k] = $v ['name'];
        }
        // 会员所属模型
        $modellistarr = S ( 'common/member_model' );
        foreach ( $modellistarr as $k => $v ) {
            $modellist [$k] = $v ['name'];
        }

        // 查询会员头像
        foreach ( $memberlist_arr as $k => $v ) {
            $memberlist [$k] = $v;
            $memberlist [$k] ['avatar'] = get_memberavatar ( $v ['userid'], 30 );
        }
        $big_menu = big_menu ( '?app=member&controller=member&action=add', 'add', L ( 'member_add' ), 700, 500 );
        include $this->admin_tpl ( 'member_list' );
    }

    /**
     * add member
     */
    function add() {
        header ( "Cache-control: private" );
        if (isset ( $_POST ['dosubmit'] )) {
            $info = array ();
            if (check_username ( $_POST ['info'] ['username'] ) != 1) {
                showmessage ( L ( 'member_exist' ) );
            }
            $info = $this->_checkuserinfo ( $_POST ['info'] );
            if (! is_password ( $info ['password'] )) {
                showmessage ( L ( 'password_format_incorrect' ) );
            }
            $info ['encrypt'] = random ( 6 );
            $info ['regip'] = IP;
            $info ['overduedate'] = isset ( $info ['overduedate'] ) && ! empty ( $info ['overduedate'] ) ? strtotime ( $info ['overduedate'] ) : 0;
            unset ( $info ['pwdconfirm'] );
            $info ['regdate'] = $info ['lastdate'] = TIME;
            $userid = $this->db->register ( $info );
            if ($userid > 0)
                showmessage ( L ( 'operation_success' ), U ( 'member/member/add' ), '', 'add' );
            else {
                switch ($userid) {
                    case '-1' :
                        showmessage ( L ( 'username_illegal' ), HTTP_REFERER ); // 用户名不合法
                        break;
                    case '-2' :
                        showmessage ( L ( 'username_deny' ), HTTP_REFERER ); // 用户名包含不允许注册的词语
                        break;
                    case '-3' :
                        showmessage ( L ( 'member_exist' ), HTTP_REFERER ); // 用户名已存在
                        break;
                    case '-4' :
                        showmessage ( L ( 'email_illegal' ), HTTP_REFERER ); // E-mail不合法
                        break;
                    case '-5' :
                        showmessage ( L ( 'email_deny' ), HTTP_REFERER ); // E-mail不允许注册
                        break;
                    case '-6' :
                        showmessage ( L ( 'email_already_exist' ), HTTP_REFERER ); // 该Email已经被注册
                        break;
                    case '-7' :
                        showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
                        break;
                }
            }
        } else {
            $show_header = $show_scroll = true;
            // 会员组缓存
            $group_cache = S ( 'member/grouplist' );
            foreach ( $group_cache as $_key => $_value ) {
                $grouplist [$_key] = $_value ['name'];
            }
            // 会员模型缓存
            $member_model_cache = S ( 'common/member_model' );
            foreach ( $member_model_cache as $_key => $_value ) {
                $modellist [$_key] = $_value ['name'];
            }
            include $this->admin_tpl ( 'member_add' );
        }

    }

    /**
     * edit member
     */
    function edit() {
        if (isset ( $_POST ['dosubmit'] )) {
            $memberinfo = $info = array ();
            $basicinfo ['userid'] = $_POST ['info'] ['userid'];
            $basicinfo ['username'] = $_POST ['info'] ['username'];
            $basicinfo ['mobile'] = $_POST ['info'] ['mobile'];
            $basicinfo ['nickname'] = $_POST ['info'] ['nickname'];
            $basicinfo ['email'] = $_POST ['info'] ['email'];
            $basicinfo ['point'] = $_POST ['info'] ['point'];
            $basicinfo ['password'] = $_POST ['info'] ['password'];
            $basicinfo ['groupid'] = $_POST ['info'] ['groupid'];
            $basicinfo ['modelid'] = $_POST ['info'] ['modelid'];
            $basicinfo ['vip'] = isset ( $_POST ['info'] ['vip'] ) ? intval ( $_POST ['info'] ['vip'] ) : 0;
            $basicinfo ['overduedate'] = isset ( $_POST ['info'] ['overduedate'] ) && ! empty ( $_POST ['info'] ['overduedate'] ) ? strtotime ( $_POST ['info'] ['overduedate'] ) : 0;
            // 会员基本信息
            $info = $this->_checkuserinfo ( $basicinfo, 1 );
            // 会员模型信息
            $modelinfo = array_diff ( $_POST ['info'], $info );
            // 过滤vip过期时间
            unset ( $modelinfo ['overduedate'] );
            unset ( $modelinfo ['pwdconfirm'] );
            $userid = $info ['userid'];
            $where = array ('userid' => $userid );
            $userinfo = $this->db->get_one ( $where );
            if (empty ( $userinfo )) {
                showmessage ( L ( 'user_not_exist' ) . L ( 'or' ) . L ( 'no_permission' ), HTTP_REFERER );
            }
            // 删除用户头像
            if (! empty ( $_POST ['delavatar'] )) {
                if (ucenter_exists ()) {
                    Loader::lib ( 'member:uc_client' )->uc_user_deleteavatar ( $userinfo ['ucenterid'] );
                } else {
                    $dir1 = ceil ( $userinfo ['userid'] / 10000 );
                    $dir2 = ceil ( $userinfo ['userid'] % 10000 / 1000 );
                    // 图片存储文件夹
                    $avatarfile = C ( 'attachment', 'upload_path' ) . 'avatar/';
                    $dir = $avatarfile . $dir1 . '/' . $dir2 . '/' . $userinfo ['userid'] . '/';
                    $this->db->update ( array ('avatar' => 0 ), array ('userid' => $userinfo ['userid'] ) );
                    if (file_exists ( $dir )) {
                        if ($handle = opendir ( $dir )) {
                            while ( false !== ($file = readdir ( $handle )) ) {
                                if ($file !== '.' && $file !== '..')
                                    @unlink ( $dir . $file );
                            }
                            closedir ( $handle );
                            @rmdir ( $dir );
                        }
                    }
                }
            }
            if (ucenter_exists ()) {
                $res = Loader::lib ( 'member:uc_client' )->uc_user_edit ( $info ['username'], '', $info ['password'], $info ['email'], $userinfo ['encrypt'], 1 );
                if ($res < 0)
                    showmessage ( L ( 'ucenter_operation_failure' ), HTTP_REFERER );
            }

            unset ( $info ['userid'] );
            unset ( $info ['username'] );

            // 如果密码不为空，修改用户密码。
            if (isset ( $info ['password'] ) && ! empty ( $info ['password'] )) {
                $info ['password'] = password ( $info ['password'], $userinfo ['encrypt'] );
            } else {
                unset ( $info ['password'] );
            }
            $this->db->update ( $info, array ('userid' => $userid ) );
            require_once CACHE_MODEL_PATH . 'member_input.php';
            require_once CACHE_MODEL_PATH . 'member_update.php';
            $member_input = new member_input ( $basicinfo ['modelid'] );
            $modelinfo = $member_input->get ( $modelinfo );
            // 更新模型表，方法更新了$this->table
            $this->db->set_model ( $info ['modelid'] );
            $userinfo = $this->db->get_one ( array ('userid' => $userid ) );
            if ($userinfo) {
                $this->db->update ( $modelinfo, array ('userid' => $userid ) );
            } else {
                $modelinfo ['userid'] = $userid;
                $this->db->insert ( $modelinfo );
            }
            showmessage ( L ( 'operation_success' ), U ( 'member/member/manage' ), '', 'edit' );

        } else {
            $show_header = $show_scroll = true;
            $userid = isset ( $_GET ['userid'] ) ? $_GET ['userid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            // 会员组缓存
            $group_cache = S ( 'member/grouplist' );
            foreach ( $group_cache as $_key => $_value ) {
                $grouplist [$_key] = $_value ['name'];
            }
            // 会员模型缓存
            $member_model_cache = S ( 'common/member_model' );
            foreach ( $member_model_cache as $_key => $_value ) {
                $modellist [$_key] = $_value ['name'];
            }
            $where = array ('userid' => $userid );

            $memberinfo = $this->db->get_one ( $where );

            if (empty ( $memberinfo )) {
                showmessage ( L ( 'user_not_exist' ) . L ( 'or' ) . L ( 'no_permission' ), HTTP_REFERER );
            }
            $memberinfo ['avatar'] = get_memberavatar ( $memberinfo ['userid'], 90 );
            $modelid = isset ( $_GET ['modelid'] ) ? $_GET ['modelid'] : $memberinfo ['modelid'];
            // 获取会员模型表单
            require CACHE_MODEL_PATH . 'member_form.php';
            $member_form = new member_form ( $modelid );
            $form_overdudate = Form::date ( 'info[overduedate]', (isset ( $memberinfo ['overduedate'] ) && ! empty ( $memberinfo ['overduedate'] ) ? date ( 'Y-m-d H:i:s', $memberinfo ['overduedate'] ) : ''), 1 );
            $this->db->set_model ( $modelid );
            $membermodelinfo = $this->db->get_one ( array ('userid' => $userid ) );
            $forminfos = $forminfos_arr = $member_form->get ( $membermodelinfo );
            // 万能字段过滤
            foreach ( $forminfos as $field => $info ) {
                if ($info ['isomnipotent']) {
                    unset ( $forminfos [$field] );
                } else {
                    if ($info ['formtype'] == 'omnipotent') {
                        foreach ( $forminfos_arr as $_fm => $_fm_value ) {
                            if ($_fm_value ['isomnipotent']) {
                                $info ['form'] = str_replace ( '{' . $_fm . '}', $_fm_value ['form'], $info ['form'] );
                            }
                        }
                        $forminfos [$field] ['form'] = $info ['form'];
                    }
                }
            }
            $show_dialog = 1;
            include $this->admin_tpl ( 'member_edit' );
        }
    }

    /**
     * delete member
     */
    function delete() {
        $uidarr = isset ( $_POST ['userid'] ) ? $_POST ['userid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
        $where = to_sqls ( $uidarr, '', 'userid' );
        if (ucenter_exists ()) {
            $uc_client = Loader::lib ( 'member:uc_client' );
            $userinfo = $this->db->listinfo ( $where );
            if (is_array ( $userinfo )) {
                foreach ( $userinfo as $v ) {
                    $status = $uc_client->uc_user_delete ( $v ['ucenterid'] );
                    if ($status < 1)
                        showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
                }
            }
        }
        if ($this->db->delete ( $where )) {
            Loader::model ( 'member_bind_model' )->delete ( $where );
            showmessage ( L ( 'operation_success' ), HTTP_REFERER );
        } else {
            showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
        }
    }

    /**
     * lock member
     */
    function lock() {
        if (isset ( $_POST ['userid'] )) {
            $uidarr = isset ( $_POST ['userid'] ) ? $_POST ['userid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            $this->db->lock ( $uidarr );
            showmessage ( L ( 'member_lock' ) . L ( 'operation_success' ), HTTP_REFERER );
        } else {
            showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
        }
    }

    /**
     * unlock member
     */
    function unlock() {
        if (isset ( $_POST ['userid'] )) {
            $uidarr = isset ( $_POST ['userid'] ) ? $_POST ['userid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            $this->db->unlock ( $uidarr );
            showmessage ( L ( 'member_unlock' ) . L ( 'operation_success' ), HTTP_REFERER );
        } else {
            showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
        }
    }

    /**
     * move member
     */
    function move() {
        if (isset ( $_POST ['dosubmit'] )) {
            $uidarr = isset ( $_POST ['userid'] ) ? $_POST ['userid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            $groupid = isset ( $_POST ['groupid'] ) && ! empty ( $_POST ['groupid'] ) ? $_POST ['groupid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            $where = to_sqls ( $uidarr, '', 'userid' );
            $this->db->update ( array ('groupid' => $groupid ), $where );
            showmessage ( L ( 'member_move' ) . L ( 'operation_success' ), HTTP_REFERER, '', 'move' );
        } else {
            $show_header = $show_scroll = true;
            $grouplist = S ( 'grouplist' );
            foreach ( $grouplist as $k => $v ) {
                $grouplist [$k] = $v ['name'];
            }
            $ids = isset ( $_GET ['ids'] ) ? explode ( ',', $_GET ['ids'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
            array_pop ( $ids );
            if (! empty ( $ids )) {
                $where = to_sqls ( $ids, '', 'userid' );
                $userarr = $this->db->listinfo ( $where );
            } else {
                showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER, '', 'move' );
            }
            include $this->admin_tpl ( 'member_move' );
        }
    }

    /**
     * 查看会员详细资料
     */
    function memberinfo() {
        $show_header = false;
        $userid = ! empty ( $_GET ['userid'] ) ? intval ( $_GET ['userid'] ) : '';
        $username = ! empty ( $_GET ['username'] ) ? trim ( $_GET ['username'] ) : '';
        if (! empty ( $userid )) {
            $memberinfo = $this->db->get_one ( array ('userid' => $userid ) );
        } elseif (! empty ( $username )) {
            $memberinfo = $this->db->get_one ( array ('username' => $username ) );
        } else {
            showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
        }

        if (empty ( $memberinfo )) {
            showmessage ( L ( 'user' ) . L ( 'not_exists' ), HTTP_REFERER );
        }
        $memberinfo ['avatar'] = get_memberavatar ( $memberinfo ['userid'], 90 );
        $grouplist = S ( 'member/grouplist' );
        // 会员模型缓存
        $modellist = S ( 'common/member_model' );
        $modelid = isset ( $_GET ['modelid'] ) ? intval ( $_GET ['modelid'] ) : $memberinfo ['modelid'];
        $this->db->set_model ( $modelid );
        $member_modelinfo = $this->db->get_one ( array ('userid' => $userid ) );
        // 模型字段名称
        $model_fieldinfo = S ( 'member/model_field_' . $modelid );
        // 图片字段显示图片
        if (is_array ( $model_fieldinfo )) {
            foreach ( $model_fieldinfo as $k => $v ) {
                if ($v ['formtype'] == 'image') {
                    $member_modelinfo [$k] = "<a href='.$member_modelinfo[$k].' target='_blank'><img src='.$member_modelinfo[$k].' height='40' widht='40' onerror=\"this.src='" . IMG_PATH . "member/nophoto.gif'\"></a>";
                } elseif ($v ['formtype'] == 'images') {
                    $tmp = string2array ( $member_modelinfo [$k] );
                    $member_modelinfo [$k] = '';
                    if (is_array ( $tmp )) {
                        foreach ( $tmp as $tv ) {
                            $member_modelinfo [$k] .= " <a href='$tv[url]' target='_blank'><img src='$tv[url]' height='40' widht='40' onerror=\"this.src='" . IMG_PATH . "member/nophoto.gif'\"></a>";
                        }
                        unset ( $tmp );
                    }
                } elseif ($v ['formtype'] == 'box') { // box字段，获取字段名称和值的数组
                    $tmp = explode ( "\n", $v ['options'] );
                    if (is_array ( $tmp )) {
                        foreach ( $tmp as $boxv ) {
                            $box_tmp_arr = explode ( '|', trim ( $boxv ) );
                            if (is_array ( $box_tmp_arr ) && isset ( $box_tmp_arr [1] ) && isset ( $box_tmp_arr [0] )) {
                                $box_tmp [$box_tmp_arr [1]] = $box_tmp_arr [0];
                                $tmp_key = intval ( $member_modelinfo [$k] );
                            }
                        }
                    }
                    if (isset ( $box_tmp [$tmp_key] )) {
                        $member_modelinfo [$k] = $box_tmp [$tmp_key];
                    } else {
                        $member_modelinfo [$k] = $member_modelinfo_arr [$k];
                    }
                    unset ( $tmp, $tmp_key, $box_tmp, $box_tmp_arr );
                } elseif ($v ['formtype'] == 'linkage') { // 如果为联动菜单
                    $tmp = string2array ( $v ['setting'] );
                    $tmpid = $tmp ['linageid'];
                    $linkagelist = S ( 'linkage/' . $tmpid );
                    $fullname = $this->_get_linkage_fullname ( $member_modelinfo [$k], $linkagelist );

                    $member_modelinfo [$v ['name']] = substr ( $fullname, 0, - 1 );
                    unset ( $tmp, $tmpid, $linkagelist, $fullname );
                } else {
                    $member_modelinfo [$k] = $member_modelinfo [$k];
                }
            }
        }

        $member_fieldinfo = array ();
        // 交换数组key值
        if (is_array ( $model_fieldinfo )) {
            foreach ( $model_fieldinfo as $v ) {
                if (! empty ( $member_modelinfo ) && array_key_exists ( $v ['field'], $member_modelinfo )) {
                    $tmp = $member_modelinfo [$v ['field']];
                    unset ( $member_modelinfo [$v ['field']] );
                    $member_fieldinfo [$v ['name']] = $tmp;
                    unset ( $tmp );
                } else {
                    $member_fieldinfo [$v ['name']] = '';
                }
            }
        }
        include $this->admin_tpl ( 'member_moreinfo' );
    }

    /**
     * 通过linkageid获取名字路径
     */
    private function _get_linkage_fullname($linkageid, $linkagelist) {
        $fullname = '';
        if ($linkagelist ['data'] [$linkageid] ['parentid'] != 0) {
            $fullname = $this->_get_linkage_fullname ( $linkagelist ['data'] [$linkageid] ['parentid'], $linkagelist );
        }
        // 所在地区名称
        $return = $fullname . $linkagelist ['data'] [$linkageid] ['name'] . '>';
        return $return;
    }

    private function _checkuserinfo($data, $is_edit = 0) {
        if (! is_array ( $data )) {
            showmessage ( L ( 'need_more_param' ) );
            return false;
        } elseif (! is_username ( $data ['username'] ) && ! $is_edit) {
            showmessage ( L ( 'username_format_incorrect' ) );
            return false;
        } elseif (! isset ( $data ['userid'] ) && $is_edit) {
            showmessage ( L ( 'username_format_incorrect' ) );
            return false;
        } elseif (empty ( $data ['email'] ) || ! is_email ( $data ['email'] )) {
            showmessage ( L ( 'email_format_incorrect' ) );
            return false;
        }
        return $data;
    }
}