<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
/**
 * 播放器管理
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-7-9
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: PlayerController.php 332 2012-11-12 03:46:18Z xutongle $
 */
class PlayerController extends admin {

    public $db;
    public function __construct() {
        $this->db = Loader::model ( 'player_model' );
        parent::__construct ();
    }

    public function init() {
        $page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
        $infos = $this->db->order('playerid DESC')->listinfo ( $page, 20 );
        $pages = $this->db->pages;
        $big_menu = big_menu ( '?app=admin&controller=player&action=add', 'add', L ( 'add_player' ), 550, 400 );
        include $this->admin_tpl ( 'player_list' );
    }

    /**
     * 验证数据有效性
     */
    public function public_name() {
        $subject = isset ( $_GET ['subject'] ) && trim ( $_GET ['subject'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['subject'] ) ) : trim ( $_GET ['subject'] )) : exit ( '0' );
        if (isset($_GET ['playerid'])) {
            $data = $this->db->where ( array ('subject' => $subject ))->field( 'playerid,subject' )->find();
            if (! $data)
                exit ( '1' );
            if ($data ['playerid'] == intval($_GET ['playerid']))
                exit ( '1' );
            exit ( '0' );
        } else {
            if ($this->db->where ( array ('subject' => $subject ))->field('playerid' )->find()) {
                exit ( '0' );
            } else {
                exit ( '1' );
            }
        }
    }

    public function add() {
        if (isset ( $_POST ['dosubmit'] )) {
            $this->db->insert ( $_POST ['info'] );
            showmessage ( L ( 'operation_success' ), U ( 'admin/player/add' ), '', 'add' );
        } else {
            $show_validator = $show_scroll = $show_header = true;
            include $this->admin_tpl ( 'player_add' );
        }
    }

    public function edit() {
        $playerid = intval ( $_GET ['playerid'] ? $_GET ['playerid'] : $_POST ['playerid'] );
        if (isset ( $_POST ['dosubmit'] )) {
            $this->db->where(array ('playerid' => $playerid ))->update ( $_POST ['info'] );
            showmessage ( L ( 'operation_success' ), U ( 'admin/player/init' ), '', 'edit' );
        } else {
            $show_validator = $show_scroll = $show_header = true;
            $info = $this->db->getby_playerid ($playerid );
            if (! $info)
                showmessage ( L ( 'player_not_exist' ) );
            include $this->admin_tpl ( 'player_edit' );
        }

    }

    /**
     * 删除播放器
     */
    function delete() {
        if (isset ( $_POST ['playerid'] ) && is_array ( $_POST ['playerid'] )) {
            foreach ( $_POST ['playerid'] as $keylinkid_arr ) {
                $this->db->where(array ('playerid' => $keylinkid_arr ))->delete (  );
            }
            showmessage ( L ( 'operation_success' ), U ( 'admin/player' ) );
        } else {
            $keylinkid = isset ( $_GET ['playerid'] ) ? intval ( $_GET ['playerid'] ) : showmessage ( L ( "operation_failure" ), U ( 'admin/player' ) );
            $result = $this->db->where(array ('playerid' => $keylinkid ))->delete (  );
            if ($result) {
                showmessage ( L ( 'operation_success' ), U ( 'admin/player' ) );
            } else {
                showmessage ( L ( "operation_failure" ), U ( 'admin/player' ) );
            }
        }
    }

    /**
     * 禁用播放器
     */
    function changestatus() {
        $playerid = intval ( $_GET ['playerid'] );
        $status = intval ( $_GET ['status'] );
        if (! $playerid)
            showmessage ( L ( 'please_secect_player' ), U ( 'admin/player' ) );
        if (! isset ( $status ))
            showmessage ( L ( "operation_failure" ), U ( 'admin/player' ) );
        $issuccess = $this->db->where(array ('playerid' => $playerid ))->update ( array ('disabled' => $status ? 0 : 1 ) );
        $issuccess ? showmessage ( L ( 'operation_success' ), U ( 'admin/player' ) ) : showmessage ( L ( "operation_failure" ), U ( 'admin/player' ) );
    }
}