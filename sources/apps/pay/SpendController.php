<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
/**
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: SpendController.php 61 2012-11-05 12:48:43Z xutongle $
 */

class SpendController extends admin {
    private $db;

    public function __construct() {
        $this->db = Loader::model('pay_spend_model');
        parent::__construct();
    }

    public function init() {
        $page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
        $sql =  "";
        if (isset($_GET['dosubmit'])) {
            $username = isset($_GET['username']) && trim($_GET['username']) ? trim($_GET['username']) : '';
            $op = isset($_GET['op']) && trim($_GET['op']) ? trim($_GET['op']) : '';
            $user_type = isset($_GET['user_type']) && intval($_GET['user_type']) ? intval($_GET['user_type']) : '';
            $op_type = isset($_GET['op_type']) && intval($_GET['op_type']) ? intval($_GET['op_type']) : '';
            $type = isset($_GET['type']) && intval($_GET['type']) ? intval($_GET['type']) : '';
            $endtime = isset($_GET['endtime'])  &&  trim($_GET['endtime']) ? strtotime(trim($_GET['endtime'])) : '';
            $starttime = isset($_GET['starttime']) && trim($_GET['starttime']) ? strtotime(trim($_GET['starttime'])) : '';
            if (!empty($starttime) && empty($endtime)) {
                $endtime = TIME;
            }
            if (!empty($starttime) && !empty($endtime) && $endtime < $starttime) {
                showmessage(L('wrong_time_over_time_to_time_less_than'));
            }
            if (!empty($username) && $user_type == 1) {
                $sql .= $sql ? " AND `username` = '$username'" : " `username` = '$username'";
            }
            if (!empty($username) && $user_type == 2) {
                $sql .= $sql ? " AND `userid` = '$username'" : " `userid` = '$username'";
            }
            if (!empty($starttime)) {
                $sql .= $sql ? " AND `creat_at` BETWEEN '$starttime' AND '$endtime' " : " `creat_at` BETWEEN '$starttime' AND '$endtime' ";
            }
            if (!empty($op) && $op_type == 1) {
                $sql .= $sql ? " AND `op_username` = '$op' " : " `op_username` = '$op' ";
            } elseif (!empty($op) && $op_type == 2) {
                $sql .= $sql ? " AND `op_userid` = '$op' " : " `op_userid` = '$op' ";
            }
            if (!empty($type)) {
                $sql .= $sql ? " AND `type` = '$type' " : " `type` = '$type'";
            }
        }
        $list = $this->db->listinfo($sql, '`id` desc', $page);
        $pages = $this->db->pages;
        include $this->admin_tpl('spend_list');
    }
}