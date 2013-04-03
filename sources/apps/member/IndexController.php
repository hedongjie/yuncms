<?php
defined('IN_YUNCMS') or exit('No permission resources.');
Loader::lib('member:foreground');
/**
 * 会员首页
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-25
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: IndexController.php 294 2013-04-02 09:24:57Z 85825770@qq.com $
 */

class IndexController extends foreground{
    private $times_db;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 会员中心首页
     */
    public function init() {
        $memberinfo = $this->memberinfo;
        //获取头像数组
        $avatar = $this->avatar;
        $grouplist = S('member/grouplist');
        $memberinfo['groupname'] = $grouplist[$memberinfo['groupid']]['name'];
        include template('member', 'index');
    }

    /**
     * 我的收藏
     *
     */
    public function favorite() {
        $this->favorite_db = Loader::model('favorite_model');
        $memberinfo = $this->memberinfo;
        if(isset($_GET['id']) && trim($_GET['id'])) {
            $this->favorite_db->where(array('userid'=>$memberinfo['userid'], 'id'=>intval($_GET['id'])))->delete();
            showmessage(L('operation_success'), HTTP_REFERER);
        } else {
            $page = isset($_GET['page']) && trim($_GET['page']) ? intval($_GET['page']) : 1;
            $favoritelist = $this->favorite_db->where(array('userid'=>$memberinfo['userid']))->order('id DESC')->listinfo($page, 10);
            $pages = $this->favorite_db->pages;
            include template('member', 'favorite_list');
        }
    }

    /**
     * 积分兑换
     */
    public function change_credit() {
        $memberinfo = $this->memberinfo;
        //加载用户模块配置
        $member_setting = S('member/member_setting');
        $outcredit = S('member/creditchange');
        $applist = S('member/applist');

        if(isset($_POST['dosubmit'])) {
            //本系统积分兑换数
            $fromvalue = intval($_POST['fromvalue']);
            //本系统积分类型
            $from = $_POST['from'];
            $toappid_to = explode('_', $_POST['to']);
            //目标系统appid
            $toappid = $toappid_to[0];
            //目标系统积分类型
            $to = $toappid_to[1];
            if($from == 1) {
                if($memberinfo['point'] < $fromvalue) {
                    showmessage(L('need_more_point'), HTTP_REFERER);
                }
            } elseif($from == 2) {
                if($memberinfo['amount'] < $fromvalue) {
                    showmessage(L('need_more_amount'), HTTP_REFERER);
                }
            } else {
                showmessage(L('credit_setting_error'), HTTP_REFERER);
            }
            //UCenter应用间积分兑换
            $status = Loader::lib ( 'Ucenter' )->uc_credit_exchange_request($memberinfo['ucuserid'], $from, $to, $toappid, $fromvalue);
            if($status == 1) {
                if($from == 1) {
                    $this->db->update(array('point'=>"-=$fromvalue"), array('userid'=>$memberinfo['userid']));
                } elseif($from == 2) {
                    $this->db->where(array('userid'=>$memberinfo['userid']))->update(array('amount'=>"-=$fromvalue"));
                }
                showmessage(L('operation_success'), HTTP_REFERER);
            } else {
                showmessage(L('operation_failure'), HTTP_REFERER);
            }
        } elseif(isset($_POST['buy'])) {
            if(!is_numeric($_POST['money']) || $_POST['money'] < 0) {
                showmessage(L('money_error'), HTTP_REFERER);
            } else {
                $money = intval($_POST['money']);
            }

            if($memberinfo['amount'] < $money) {
                showmessage(L('short_of_money'), HTTP_REFERER);
            }
            //此处比率读取用户配置
            $point = $money * $member_setting['rmb_point_rate'];
            $this->db->update(array('point'=>"+=$point"), array('userid'=>$memberinfo['userid']));
            //加入消费记录，同时扣除金钱
            Loader::lib('pay:spend',false);
            spend::amount($money, L('buy_point'), $memberinfo['userid'], $memberinfo['username']);
            showmessage(L('operation_success'), HTTP_REFERER);
        } else {
            $credit_list = C('credit');
            include template('member', 'change_credit');
        }
    }
}