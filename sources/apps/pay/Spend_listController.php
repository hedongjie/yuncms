<?php
defined('IN_YUNCMS') or exit('No permission resources.');
Loader::lib('member:foreground',false);
/**
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Spend_listController.php 61 2012-11-05 12:48:43Z xutongle $
 */
class Spend_listController extends foreground {
    private $spend_db;

    function __construct() {
        if (!application_exists(ROUTE_APP)) showmessage(L('application_not_exists'));
        $this->spend_db = Loader::model('pay_spend_model');
        parent::__construct();
    }

    public function init() {
        $page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
        $userid  = cookie('_userid');
        $sql =  " `userid` = '$userid'";
        if (isset($_GET['dosubmit'])) {
            $type = isset($_GET['type']) && intval($_GET['type']) ? intval($_GET['type']) : '';
            $endtime = isset($_GET['endtime'])  &&  trim($_GET['endtime']) ? strtotime(trim($_GET['endtime'])) : '';
            $starttime = isset($_GET['starttime']) && trim($_GET['starttime']) ? strtotime(trim($_GET['starttime'])) : '';
            if (!empty($starttime) && empty($endtime)) {
                $endtime = TIME;
            }
            if (!empty($starttime) && !empty($endtime) && $endtime < $starttime) {
                showmessage(L('wrong_time_over_time_to_time_less_than'));
            }
            if (!empty($starttime)) {
                $sql .= $sql ? " AND `creat_at` BETWEEN '$starttime' AND '$endtime' " : " `creat_at` BETWEEN '$starttime' AND '$endtime' ";
            }
            if (!empty($type)) {
                $sql .= $sql ? " AND `type` = '$type' " : " `type` = '$type'";
            }
        }
        $list = $this->spend_db->listinfo($sql, '`id` desc', $page);
        $pages = $this->spend_db->pages;
        include template('pay', 'spend_list');
    }
}